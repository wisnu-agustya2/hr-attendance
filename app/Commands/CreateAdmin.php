<?php

namespace App\Commands;

use App\Models\EmployeeModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateAdmin extends BaseCommand
{
    protected $group       = 'HR';
    protected $name        = 'hr:admin';
    protected $description = 'Buat akun HR/admin baru, atau ganti password akun yang sudah ada.';
    protected $usage       = 'hr:admin <email> [nama]';

    public function run(array $params)
    {
        $email = strtolower(trim($params[0] ?? CLI::prompt('Email')));
        $name  = $params[1] ?? 'Administrator';
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            CLI::error('Email tidak valid.');

            return EXIT_ERROR;
        }

        $password = CLI::prompt('Password (min. 8 karakter)', null, 'required|min_length[8]');
        $model    = model(EmployeeModel::class);
        $existing = $model->where('email', $email)->first();
        $data     = ['password_hash' => password_hash($password, PASSWORD_DEFAULT), 'role' => EmployeeModel::ROLE_ADMIN, 'is_active' => 1];

        if ($existing) {
            $model->update($existing['id'], $data);
            CLI::write("Akun {$email} diperbarui menjadi admin dengan password baru.", 'green');
        } else {
            $model->insert($data + ['name' => $name, 'email' => $email]);
            CLI::write("Admin {$email} dibuat.", 'green');
        }

        return EXIT_SUCCESS;
    }
}
