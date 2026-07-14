<?php
declare(strict_types=1);

namespace App\Skills;

use App\Models\User;
use App\Models\WebauthnCredential;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\CredentialRecord;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\CollectedClientData;
use Webauthn\AuthenticatorDataLoader;
use Webauthn\Util\Base64;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\EmptyTrustPath;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Str;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\SerializerInterface;

class WebAuthnSkill
{
    private string $rpName;
    private string $rpId;
    private CeremonyStepManagerFactory $ceremonyStepManagerFactory;
    private SerializerInterface $serializer;

    public function __construct()
    {
        $this->rpName = config('app.name', 'Laravel AI');
        $this->rpId = parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST);

        $attestationStatementSupportManager = new AttestationStatementSupportManager();
        $attestationStatementSupportManager->add(new NoneAttestationStatementSupport());

        $this->serializer = (new WebauthnSerializerFactory($attestationStatementSupportManager))->create();
        $this->ceremonyStepManagerFactory = new CeremonyStepManagerFactory();
        
        // Setup allowed origins dynamically to include the port (e.g., localhost:8000)
        $this->ceremonyStepManagerFactory->setAllowedOrigins([
            config('app.url'),
            request()->getSchemeAndHttpHost()
        ]);
    }


    public function generateRegistrationOptions(User $user): array
    {
        $rp = new PublicKeyCredentialRpEntity($this->rpName, $this->rpId);
        
        $userEntity = new PublicKeyCredentialUserEntity(
            $user->email,
            (string) $user->id,
            $user->name
        );

        $challenge = random_bytes(32);
        Session::put('webauthn_registration_challenge', base64_encode($challenge));

        $pubKeyCredParams = [
            new PublicKeyCredentialParameters('public-key', -7), // ES256
            new PublicKeyCredentialParameters('public-key', -257), // RS256
        ];

        // Exclude existing credentials
        $excludeCredentials = $user->webauthnCredentials->map(function ($cred) {
            return new PublicKeyCredentialDescriptor(
                PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY, 
                Base64::decode($cred->credential_id)
            );
        })->toArray();

        $authenticatorSelection = new AuthenticatorSelectionCriteria(
            authenticatorAttachment: AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_NO_PREFERENCE,
            userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED
        );

        $options = new PublicKeyCredentialCreationOptions(
            rp: $rp,
            user: $userEntity,
            challenge: $challenge,
            pubKeyCredParams: $pubKeyCredParams,
            authenticatorSelection: $authenticatorSelection,
            excludeCredentials: $excludeCredentials
        );

        return json_decode($this->serializer->serialize($options, 'json'), true);
    }

    public function verifyRegistration(User $user, array $response, Request $request): WebauthnCredential
    {
        $challenge = Session::get('webauthn_registration_challenge');
        if (!$challenge) {
            throw new Exception('Challenge not found in session.');
        }

        $clientDataJSON = CollectedClientData::createFormJson($response['response']['clientDataJSON']);
        $attestationObjectLoader = AttestationObjectLoader::create(new AttestationStatementSupportManager([
            new NoneAttestationStatementSupport(),
        ]));
        $attestationObject = $attestationObjectLoader->load(Base64::decode($response['response']['attestationObject']));
        
        $authenticatorResponse = AuthenticatorAttestationResponse::create(
            $clientDataJSON,
            $attestationObject,
            $response['response']['transports'] ?? []
        );

        $creationOptions = new PublicKeyCredentialCreationOptions(
            rp: new PublicKeyCredentialRpEntity($this->rpName, $this->rpId),
            user: new PublicKeyCredentialUserEntity($user->email, (string) $user->id, $user->name),
            challenge: base64_decode($challenge),
            pubKeyCredParams: []
        );

        $creationCeremony = $this->ceremonyStepManagerFactory->creationCeremony();
        $validator = new AuthenticatorAttestationResponseValidator($creationCeremony);

        try {
            $credentialRecord = $validator->check(
                $authenticatorResponse,
                $creationOptions,
                $request->getHost()
            );

            $credential = WebauthnCredential::create([
                'user_id' => $user->id,
                'name' => $request->input('name', 'New Device'),
                'type' => $credentialRecord->type,
                'transports' => $credentialRecord->transports,
                'attestation_type' => $credentialRecord->attestationType,
                'trust_path' => $credentialRecord->trustPath->jsonSerialize(),
                'aaguid' => $credentialRecord->aaguid->toString(),
                'credential_id' => base64_encode($credentialRecord->publicKeyCredentialId),
                'public_key' => base64_encode($credentialRecord->credentialPublicKey),
                'user_handle' => base64_encode($credentialRecord->userHandle),
                'counter' => $credentialRecord->counter,
            ]);

            Session::forget('webauthn_registration_challenge');

            return $credential;
        } catch (Exception $e) {
            throw new Exception('WebAuthn verification failed: ' . $e->getMessage());
        }
    }

    public function generateAuthenticationOptions(?User $user = null): array
    {
        $challenge = random_bytes(32);
        Session::put('webauthn_authentication_challenge', base64_encode($challenge));

        $allowedCredentials = [];
        if ($user) {
            $allowedCredentials = $user->webauthnCredentials->map(function ($cred) {
                return new PublicKeyCredentialDescriptor(
                    PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY, 
                    Base64::decode($cred->credential_id)
                );
            })->toArray();
        }

        $options = new PublicKeyCredentialRequestOptions(
            challenge: $challenge,
            rpId: $this->rpId,
            allowCredentials: $allowedCredentials,
            userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            timeout: 60000
        );

        return json_decode($this->serializer->serialize($options, 'json'), true);
    }

    public function verifyAuthentication(array $response, Request $request): WebauthnCredential
    {
        $challenge = Session::get('webauthn_authentication_challenge');
        if (!$challenge) {
            throw new Exception('Challenge not found in session.');
        }

        $clientDataJSON = CollectedClientData::createFormJson($response['response']['clientDataJSON']);
        $authDataLoader = AuthenticatorDataLoader::create();
        $authenticatorData = $authDataLoader->load(Base64::decode($response['response']['authenticatorData']));
        
        $authenticatorResponse = AuthenticatorAssertionResponse::create(
            $clientDataJSON,
            $authenticatorData,
            Base64::decode($response['response']['signature']),
            $response['response']['userHandle'] ?? null
        );

        $credentialId = base64_encode(Base64::decode($response['rawId']));
        $credential = WebauthnCredential::where('credential_id', $credentialId)->firstOrFail();
        
        $credentialRecord = new CredentialRecord(
            publicKeyCredentialId: Base64::decode($credential->credential_id),
            type: $credential->type,
            transports: $credential->transports,
            attestationType: $credential->attestation_type,
            trustPath: new EmptyTrustPath(),
            aaguid: Uuid::fromString($credential->aaguid),
            credentialPublicKey: Base64::decode($credential->public_key),
            userHandle: Base64::decode($credential->user_handle),
            counter: (int) $credential->counter
        );

        $requestOptions = new PublicKeyCredentialRequestOptions(
            challenge: base64_decode($challenge),
            rpId: $this->rpId,
            allowCredentials: [],
            timeout: 60000
        );

        $requestCeremony = $this->ceremonyStepManagerFactory->requestCeremony();
        $validator = new AuthenticatorAssertionResponseValidator($requestCeremony);

        try {
            $validator->check(
                $credentialRecord,
                $authenticatorResponse,
                $requestOptions,
                $request->getHost(),
                $response['response']['userHandle'] ?? null ? Base64::decode($response['response']['userHandle']) : null
            );

            // Update counter
            $credential->update([
                'counter' => $authenticatorResponse->authenticatorData->signCount,
                'last_used_at' => now(),
            ]);

            Session::forget('webauthn_authentication_challenge');

            return $credential;
        } catch (Exception $e) {
            throw new Exception('WebAuthn authentication failed: ' . $e->getMessage());
        }
    }
}
