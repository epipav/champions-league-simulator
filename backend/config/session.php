<?php

// Minimal session config - in-memory only, no persistence
return [
    'driver' => 'array',
    'lifetime' => 120,
    'expire_on_close' => false,
    'encrypt' => false,
    'lottery' => [2, 100],
    'cookie' => 'laravel_session',
    'path' => '/',
    'domain' => null,
    'secure' => false,
    'http_only' => true,
    'same_site' => 'lax',
];
