<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class EmployeeController extends BaseController
{
    private const LABELS = [
        'name'     => 'Nama',
        'email'    => 'Email',
        'position' => 'Jabatan',
        'role'     => 'Peran',
        'password' => 'Password',
    ];

    public function index(): string
    {
        return view('admin/employees/index', [
            'employees' => model(EmployeeModel::class)->orderBy('is_active', 'DESC')->orderBy('name')->findAll(),
        ]);
    }

    public function new(): string
    {
        return view('admin/employees/form', ['employee' => null]);
    }

    public function create(): RedirectResponse
    {
        $rules = [
            'name'     => 'required|max_length[100]',
            'email'    => 'required|valid_email|max_length[150]|is_unique[employees.email]',
            'position' => 'permit_empty|max_length[100]',
            'role'     => 'required|in_list[admin,employee]',
            'password' => 'required|min_length[8]',
        ];
        $post = $this->request->getPost();
        $post['email'] = strtolower(trim((string) ($post['email'] ?? '')));
        if (! $this->validateData($post, $this->withLabels($rules, self::LABELS))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $data = $this->validator->getValidated();

        model(EmployeeModel::class)->insert([
            'name'          => trim($data['name']),
            'email'         => $data['email'],
            'position'      => trim((string) ($data['position'] ?? '')) ?: null,
            'role'          => $data['role'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'is_active'     => 1,
        ]);

        return redirect()->to(site_url('admin/employees'))->with('success', "Karyawan {$data['name']} ditambahkan.");
    }

    public function edit(int $id): string
    {
        return view('admin/employees/form', ['employee' => $this->find($id)]);
    }

    public function update(int $id): RedirectResponse
    {
        $employee = $this->find($id);
        $rules    = [
            'name'     => 'required|max_length[100]',
            'email'    => "required|valid_email|max_length[150]|is_unique[employees.email,id,{$id}]",
            'position' => 'permit_empty|max_length[100]',
            'role'     => 'required|in_list[admin,employee]',
            'password' => 'permit_empty|min_length[8]',
        ];
        $post = $this->request->getPost();
        $post['email'] = strtolower(trim((string) ($post['email'] ?? '')));
        if (! $this->validateData($post, $this->withLabels($rules, self::LABELS))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $data     = $this->validator->getValidated();
        $isActive = $this->request->getPost('is_active') ? 1 : 0;

        // Admin tidak boleh menonaktifkan atau menurunkan perannya sendiri, supaya tidak terkunci dari panel admin
        if ($id === $this->userId() && (! $isActive || $data['role'] !== EmployeeModel::ROLE_ADMIN)) {
            return redirect()->back()->withInput()->with('errors', ['role' => 'Anda tidak bisa menonaktifkan atau menurunkan peran akun sendiri.']);
        }

        $update = [
            'name'      => trim($data['name']),
            'email'     => $data['email'],
            'position'  => trim((string) ($data['position'] ?? '')) ?: null,
            'role'      => $data['role'],
            'is_active' => $isActive,
        ];
        if (! empty($data['password'])) {
            $update['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        model(EmployeeModel::class)->update($employee['id'], $update);

        return redirect()->to(site_url('admin/employees'))->with('success', "Data {$update['name']} diperbarui.");
    }

    private function find(int $id): array
    {
        return model(EmployeeModel::class)->find($id) ?? throw PageNotFoundException::forPageNotFound();
    }
}
