<?php

namespace App\Controllers;

use App\Models\EmployeeModel;
use CodeIgniter\HTTP\RedirectResponse;

class AuthController extends BaseController
{
    public function login(): string|RedirectResponse
    {
        if (session('user')) {
            return redirect()->to(site_url('/'));
        }

        return view('auth/login');
    }

    public function attempt(): RedirectResponse
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Email dan password wajib diisi dengan benar.');
        }

        // Batasi percobaan login: 5 kali per menit per IP
        $throttler = service('throttler');
        if ($throttler->check(md5('login-' . $this->request->getIPAddress()), 5, MINUTE) === false) {
            return redirect()->back()->withInput()->with('error', 'Terlalu banyak percobaan. Coba lagi dalam 1 menit.');
        }

        $employee = model(EmployeeModel::class)->attempt(
            (string) $this->request->getPost('email'),
            (string) $this->request->getPost('password'),
        );
        if ($employee === null) {
            return redirect()->back()->withInput()->with('error', 'Email atau password salah, atau akun nonaktif.');
        }

        session()->regenerate(true);
        session()->set('user', [
            'id'    => (int) $employee['id'],
            'name'  => $employee['name'],
            'email' => $employee['email'],
            'role'  => $employee['role'],
        ]);

        $target = $employee['role'] === EmployeeModel::ROLE_ADMIN ? 'admin' : '/';

        return redirect()->to(site_url($target));
    }

    public function logout(): RedirectResponse
    {
        session()->destroy();

        return redirect()->to(site_url('login'));
    }
}
