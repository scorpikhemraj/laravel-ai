<?php

use App\Models\User;
use App\Skills\WebAuthnSkill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\AuthenticatorSelectionCriteria;

uses(RefreshDatabase::class);

it('generates valid registration options with biometric preference', function () {
    $user = User::factory()->create();
    $skill = new WebAuthnSkill();
    
    $options = $skill->generateRegistrationOptions($user);
    
    expect($options)->toBeArray()
        ->toHaveKey('rp')
        ->toHaveKey('user')
        ->toHaveKey('challenge')
        ->toHaveKey('pubKeyCredParams')
        ->toHaveKey('authenticatorSelection');

    expect($options['authenticatorSelection']['userVerification'])
        ->toBe(AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED);
        
    expect($options['authenticatorSelection']['residentKey'])
        ->toBe(AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED);
});

it('generates valid authentication options', function () {
    $skill = new WebAuthnSkill();
    
    $options = $skill->generateAuthenticationOptions();
    
    expect($options)->toBeArray()
        ->toHaveKey('challenge')
        ->toHaveKey('rpId')
        ->toHaveKey('userVerification');

    expect($options['userVerification'])
        ->toBe(AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED);
});
