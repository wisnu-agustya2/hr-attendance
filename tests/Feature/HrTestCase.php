<?php

namespace Tests\Feature;

use App\Models\EmployeeModel;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Filters;
use Config\Services;

abstract class HrTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait {
        get as private featureGet;
        post as private featurePost;
    }

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = 'App';

    protected array $admin;
    protected array $employee;

    protected function setUp(): void
    {
        parent::setUp();

        // CSRF sudah diuji manual; di feature test dimatikan agar fokus ke logika aplikasi
        $filters = config(Filters::class);
        $filters->globals['before'] = array_values(array_diff($filters->globals['before'], ['csrf']));

        // Rate limiter login dibuat ulang agar hitungan percobaan tidak terbawa dari test lain
        Services::resetSingle('throttler');

        // Selasa, 10 November 2026 pukul 07:55 WIB
        Time::setTestNow('2026-11-10 07:55:00', 'Asia/Jakarta');

        $this->admin    = $this->makeEmployee('Dewi HR', 'hr@example.com', EmployeeModel::ROLE_ADMIN);
        $this->employee = $this->makeEmployee('Andi', 'andi@example.com');
    }

    protected function tearDown(): void
    {
        Time::setTestNow();
        parent::tearDown();
    }

    protected function makeEmployee(string $name, string $email, string $role = EmployeeModel::ROLE_EMPLOYEE, int $active = 1): array
    {
        $model = model(EmployeeModel::class);
        $id    = $model->insert([
            'name' => $name, 'email' => $email, 'role' => $role, 'is_active' => $active,
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
        ]);

        return $model->find($id);
    }

    protected function as(array $employee): static
    {
        return $this->withSession(['user' => [
            'id' => (int) $employee['id'], 'name' => $employee['name'],
            'email' => $employee['email'], 'role' => $employee['role'],
        ]]);
    }

    /** Sama seperti FeatureTestTrait::get(), tapi assertion bisa dirangkai. */
    public function get(string $path, ?array $params = null)
    {
        return new FluentResponse($this->featureGet($path, $params));
    }

    public function post(string $path, ?array $params = null)
    {
        return new FluentResponse($this->featurePost($path, $params));
    }

    /** Di feature test, 404 dilempar sebagai exception (bukan dirender). */
    protected function assertPageNotFound(callable $request): void
    {
        try {
            $request();
        } catch (PageNotFoundException) {
            $this->addToAssertionCount(1);

            return;
        }
        $this->fail('Seharusnya menghasilkan 404.');
    }

    /** Hapus session yang tersimpan dari request sebelumnya di test yang sama. */
    protected function asGuest(): static
    {
        $this->session = [];

        return $this;
    }

    protected function at(string $datetime): void
    {
        Time::setTestNow($datetime, 'Asia/Jakarta');
    }
}
