<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSO (Single Sign-On) Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para la autenticación externa vía SSO con tokens JWT/JWE.
    |
    | SSO_JWT_SECRET : Clave secreta para verificar la firma HMAC-SHA256 del JWS.
    | SSO_JWE_KEY    : Clave de encriptación para desencriptar tokens JWE (A256KW).
    |
    */

    'jwt_secret' => env('SSO_JWT_SECRET'),
    'jwe_key'    => env('SSO_JWE_KEY'),
];
