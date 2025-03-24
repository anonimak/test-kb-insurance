<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Generate a new JWT token
 * 
 * @param array $userData User data to encode in the token
 * @param int $expiration Token expiration time in seconds (default: 3600 = 1 hour)
 * @return string The JWT token
 */
function generateJWT($userData, $expiration = 3600)
{
    $key = getenv('JWT_SECRET') ?: 'your_default_secret_key';
    $issuedAt = time();
    $expirationTime = $issuedAt + $expiration;

    $payload = [
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'data' => $userData
    ];

    return JWT::encode($payload, $key, 'HS256');
}

/**
 * Validate a JWT token
 * 
 * @param string $token The JWT token to validate
 * @return object|false The decoded token data or false if invalid
 */
function validateJWT($token)
{
    try {
        $key = getenv('JWT_SECRET') ?: 'your_default_secret_key';
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        return false;
    }
}
