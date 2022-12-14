<?php

return [
    'jwt' => [
        'ALG' => 'ES256',
        'ISSUER' => 'officewave-api',
        'KEY_PUBLIC' => 'keys/jwtES256.key.pem',
        'KEY_PRIVATE' => 'keys/jwtES256.key',
    ],
    'cache' => [
        'LOGIN_USER' => 'web_login_user_cache:%s',
    ]
];