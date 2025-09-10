<?php

$ssoPublicKey = env('SSO_PUBLIC_KEY');
if (!str_contains($ssoPublicKey, 'BEGIN PUBLIC KEY')) {
    $ssoPublicKey = "-----BEGIN PUBLIC KEY-----\n"
        . chunk_split($ssoPublicKey, 64, "\n")
        . "-----END PUBLIC KEY-----\n";
}

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'sso' => [
        'client_id'     => env('SSO_CLIENT_ID'),
        'client_secret' => env('SSO_CLIENT_SECRET'),
        'redirect'      => env('SSO_REDIRECT_URI'),
        'base_url'      => env('SSO_BASE_URL'),
        'realms'        => env('SSO_REALM'),
        'jwk_url'       => env('SSO_JWK_URL'),
        'public_key'    => $ssoPublicKey,
        'verify_url'    => env('SSO_VERIFY_URL'),
        'ca_file'       => storage_path(env('SSO_CA_FILE')),
        'ip'            => env('SSO_IP', '127.0.0.1'),
        'idle_seconds'  => env('SSO_IDLE_SECONDS', 300),
        'issuer'        => env('SSO_ISSUER'),
        'role'          => env('SSO_ROLE'),
    ],
];
