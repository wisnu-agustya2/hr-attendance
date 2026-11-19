<?php

namespace Tests\Feature;

final class AuthTest extends HrTestCase
{
    public function testTamuDiarahkanKeLogin(): void
    {
        foreach (['/', 'attendance/history', 'leaves', 'admin', 'admin/recap'] as $url) {
            $this->get($url)->assertRedirectTo(site_url('login'));
        }
        $this->get('login')->assertOK()->assertSee('Masuk');
    }

    public function testLoginKaryawanDanAdmin(): void
    {
        $this->post('login', ['email' => 'ANDI@example.com', 'password' => 'password123'])
            ->assertRedirectTo(site_url('/'))
            ->assertSessionHas('user');

        $this->post('login', ['email' => 'hr@example.com', 'password' => 'password123'])
            ->assertRedirectTo(site_url('admin'));
    }

    public function testLoginGagal(): void
    {
        $this->makeEmployee('Lama', 'lama@example.com', 'employee', 0);

        foreach ([['andi@example.com', 'salah123'], ['tidakada@example.com', 'password123'], ['lama@example.com', 'password123']] as [$email, $pass]) {
            $this->post('login', ['email' => $email, 'password' => $pass])
                ->assertSessionHas('error', 'Email atau password salah, atau akun nonaktif.')
                ->assertSessionMissing('user');
        }
    }

    public function testPercobaanLoginDibatasi(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('login', ['email' => 'andi@example.com', 'password' => 'salah123'])
                ->assertSessionHas('error', 'Email atau password salah, atau akun nonaktif.');
        }
        // Percobaan ke-6 ditolak walau password benar
        $this->post('login', ['email' => 'andi@example.com', 'password' => 'password123'])
            ->assertSessionHas('error', 'Terlalu banyak percobaan. Coba lagi dalam 1 menit.')
            ->assertSessionMissing('user');
    }

    public function testKaryawanTidakBisaMembukaPanelHr(): void
    {
        foreach (['admin', 'admin/employees', 'admin/leaves', 'admin/recap', 'admin/recap/export'] as $url) {
            $this->as($this->employee)->get($url)->assertStatus(403);
        }
        $this->as($this->employee)->post('admin/employees', ['name' => 'X'])->assertStatus(403);
    }
}
