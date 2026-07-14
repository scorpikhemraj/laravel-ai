<?php
require 'vendor/autoload.php';

use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialParameters;

$serializer = (new WebauthnSerializerFactory())->create();

$rp = PublicKeyCredentialRpEntity::create('Test RP', 'localhost');
$user = PublicKeyCredentialUserEntity::create('test@example.com', '123', 'Test User');
$challenge = random_bytes(32);
$pubKeyCredParams = [
    new PublicKeyCredentialParameters('public-key', -7),
];

$authenticatorSelection = new AuthenticatorSelectionCriteria(
    authenticatorAttachment: AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_NO_PREFERENCE,
    userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
    residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED
);

$options = new PublicKeyCredentialCreationOptions(
    rp: $rp,
    user: $user,
    challenge: $challenge,
    pubKeyCredParams: $pubKeyCredParams,
    authenticatorSelection: $authenticatorSelection
);

$json = $serializer->serialize($options, 'json');
echo $json;
