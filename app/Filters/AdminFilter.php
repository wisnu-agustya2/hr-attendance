<?php

namespace App\Filters;

use App\Models\EmployeeModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Halaman hanya untuk admin/HR. Dipasang setelah filter auth. */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session('user')) {
            return redirect()->to(site_url('login'))->with('error', 'Silakan login terlebih dahulu.');
        }
        if (session('user')['role'] !== EmployeeModel::ROLE_ADMIN) {
            return service('response')->setStatusCode(403)->setBody(view('errors/forbidden'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
