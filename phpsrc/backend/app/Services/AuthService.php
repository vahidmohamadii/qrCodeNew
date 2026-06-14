<?php

declare(strict_types=1);

namespace QrCatalog\Services;

use PDO;
use QrCatalog\Auth\Jwt;

final class AuthService
{
    public function __construct(private PDO $db)
    {
    }

    /** @return array<string, mixed>|null */
    public function login(string $email, string $password): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM app_users WHERE email = :email AND is_active = 1 LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        if (!$user || !$this->verifyPassword($password, (string) $user['password_hash'])) {
            return null;
        }

        $jwt = Jwt::create((string) $user['email'], (string) $user['full_name'], (string) $user['role']);

        return [
            'accessToken' => $jwt['token'],
            'fullName' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
    }

    /** @return array<string, string>|null */
    public function currentUser(string $email): ?array
    {
        $statement = $this->db->prepare('SELECT full_name, email, role FROM app_users WHERE email = :email AND is_active = 1 LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        if (!$user) {
            return null;
        }

        return [
            'fullName' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
    }

    private function verifyPassword(string $password, string $hash): bool
    {
        if (password_get_info($hash)['algo'] !== null && password_verify($password, $hash)) {
            return true;
        }

        return $this->verifyDotNetPbkdf2($password, $hash);
    }

    private function verifyDotNetPbkdf2(string $password, string $hash): bool
    {
        $parts = explode('.', $hash);
        if (count($parts) !== 3) {
            return false;
        }

        [$salt, $expectedKey, $iterations] = $parts;
        $saltBytes = base64_decode($salt, true);
        $expectedBytes = base64_decode($expectedKey, true);
        $iterationCount = (int) $iterations;

        if ($saltBytes === false || $expectedBytes === false || $iterationCount <= 0) {
            return false;
        }

        $actualBytes = hash_pbkdf2('sha256', $password, $saltBytes, $iterationCount, strlen($expectedBytes), true);

        return hash_equals($expectedBytes, $actualBytes);
    }
}
