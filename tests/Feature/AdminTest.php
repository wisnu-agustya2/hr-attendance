<?php

namespace Tests\Feature;

use App\Models\AttendanceModel;
use App\Models\LeaveRequestModel;

final class AdminTest extends HrTestCase
{
    public function testTambahDanUbahKaryawan(): void
    {
        $this->as($this->admin)->get('admin/employees/new')->assertOK();

        $this->as($this->admin)->post('admin/employees', [
            'name' => 'Siti', 'email' => '  SITI@Example.com ', 'position' => 'Designer', 'role' => 'employee', 'password' => 'password123',
        ])->assertRedirectTo(site_url('admin/employees'));
        $this->seeInDatabase('employees', ['email' => 'siti@example.com', 'position' => 'Designer', 'is_active' => 1]);

        $this->as($this->admin)->post('admin/employees', [
            'name' => 'Siti 2', 'email' => 'siti@example.com', 'role' => 'employee', 'password' => 'pendek',
        ])->assertSessionHas('errors', ['email' => 'Email sudah digunakan.', 'password' => 'Password minimal 8 karakter.']);

        // Nonaktifkan Andi: tidak bisa login lagi
        $id = $this->employee['id'];
        $this->as($this->admin)->get("admin/employees/{$id}/edit")->assertOK()->assertSee('andi@example.com');
        $this->as($this->admin)->post("admin/employees/{$id}", [
            'name' => 'Andi Pratama', 'email' => 'andi@example.com', 'role' => 'employee', 'password' => '',
        ])->assertSessionHas('success');
        $this->seeInDatabase('employees', ['id' => $id, 'name' => 'Andi Pratama', 'is_active' => 0]);
        $this->asGuest()->post('login', ['email' => 'andi@example.com', 'password' => 'password123'])
            ->assertSessionHas('error', 'Email atau password salah, atau akun nonaktif.')
            ->assertSessionMissing('user');
    }

    public function testAdminTidakBisaMenonaktifkanDirinyaSendiri(): void
    {
        $id = $this->admin['id'];
        $this->as($this->admin)->post("admin/employees/{$id}", [
            'name' => 'Dewi HR', 'email' => 'hr@example.com', 'role' => 'admin',
        ])->assertSessionHas('errors', ['role' => 'Anda tidak bisa menonaktifkan atau menurunkan peran akun sendiri.']);
        $this->seeInDatabase('employees', ['id' => $id, 'is_active' => 1, 'role' => 'admin']);
    }

    public function testRekapDanExportCsv(): void
    {
        $attendance = model(AttendanceModel::class);
        $attendance->insert(['employee_id' => $this->employee['id'], 'work_date' => '2026-11-02', 'check_in' => '2026-11-02 08:00:00', 'check_out' => '2026-11-02 17:00:00', 'status' => 'present']);
        $attendance->insert(['employee_id' => $this->employee['id'], 'work_date' => '2026-11-03', 'check_in' => '2026-11-03 08:40:00', 'check_out' => '2026-11-03 17:10:00', 'status' => 'late']);
        model(LeaveRequestModel::class)->insert(['employee_id' => $this->employee['id'], 'type' => 'sick', 'start_date' => '2026-11-04', 'end_date' => '2026-11-04', 'days' => 1, 'reason' => 'Demam', 'status' => 'approved']);
        $this->makeEmployee('=HYPERLINK("http://x")', 'jahat@example.com');

        $this->as($this->admin)->get('admin/recap?month=2026-11')
            ->assertOK()->assertSee('November 2026')->assertSee('17j 30m');

        $result = $this->as($this->admin)->get('admin/recap/export?month=2026-11');
        $result->assertOK()->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $csv = $result->getBody();

        $this->assertStringContainsString('Nama,"Hari Kerja",Hadir,Terlambat,"Cuti Tahunan",Sakit,Izin,Alpa,"Total Jam Kerja"', $csv);
        // Andi: 21 hari kerja, hadir 2, terlambat 1, sakit 1, alpa 3 (5, 6 & 9 Nov), 17,5 jam
        $this->assertStringContainsString('Andi,21,2,1,0,1,0,3,17.5', $csv);
        // Nama yang diawali "=" diberi tanda kutip agar tidak dieksekusi sebagai formula di Excel
        $this->assertStringContainsString("\"'=HYPERLINK(\"\"http://x\"\")\"", $csv);
    }
}
