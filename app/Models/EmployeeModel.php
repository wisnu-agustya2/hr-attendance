<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table          = 'employees';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $allowedFields  = ['name', 'email', 'password_hash', 'role', 'position', 'is_active'];
    protected $beforeInsert   = ['normalizeEmail'];
    protected $beforeUpdate   = ['normalizeEmail'];

    public const ROLE_ADMIN    = 'admin';
    public const ROLE_EMPLOYEE = 'employee';

    protected function normalizeEmail(array $data): array
    {
        if (isset($data['data']['email'])) {
            $data['data']['email'] = strtolower(trim($data['data']['email']));
        }

        return $data;
    }

    /** Cek email + password. Mengembalikan data karyawan aktif, atau null. */
    public function attempt(string $email, string $password): ?array
    {
        $employee = $this->where('email', strtolower(trim($email)))->first();
        // Tetap jalankan password_verify walau email tidak ada, supaya waktu respons serupa
        // dan penyerang tidak bisa menebak email mana yang terdaftar.
        $hash = $employee['password_hash'] ?? self::dummyHash();
        if (! password_verify($password, $hash) || $employee === null || ! (int) $employee['is_active']) {
            return null;
        }

        return $employee;
    }

    private static function dummyHash(): string
    {
        static $hash = null;

        return $hash ??= password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
    }

    /** @return list<array<string, mixed>> */
    public function activeEmployees(): array
    {
        return $this->where('is_active', 1)->orderBy('name')->findAll();
    }
}
