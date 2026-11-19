<?php

namespace Tests\Feature;

final class AttendanceTest extends HrTestCase
{
    public function testCheckInTepatWaktuLaluCheckOut(): void
    {
        $this->as($this->employee)->get('/')->assertOK()->assertSee('Belum check-in');

        $this->as($this->employee)->post('attendance/check-in', ['note' => 'WFH'])
            ->assertRedirectTo(site_url('/'))
            ->assertSessionHas('success', 'Check-in tercatat 07:55. Selamat bekerja!');

        $this->seeInDatabase('attendances', [
            'employee_id' => $this->employee['id'], 'work_date' => '2026-11-10',
            'check_in' => '2026-11-10 07:55:00', 'status' => 'present', 'note' => 'WFH', 'check_out' => null,
        ]);

        $this->as($this->employee)->post('attendance/check-in')
            ->assertSessionHas('error', 'Anda sudah check-in hari ini.');
        $this->seeNumRecords(1, 'attendances', []);

        $this->at('2026-11-10 17:05:00');
        $this->as($this->employee)->post('attendance/check-out')
            ->assertSessionHas('success', 'Check-out tercatat 17:05. Total kerja hari ini 9j 10m.');
        $this->seeInDatabase('attendances', ['employee_id' => $this->employee['id'], 'check_out' => '2026-11-10 17:05:00']);

        $this->as($this->employee)->post('attendance/check-out')
            ->assertSessionHas('error', 'Anda sudah check-out hari ini.');

        $this->as($this->employee)->get('attendance/history?month=2026-11')
            ->assertOK()->assertSee('Sel, 10 Nov 2026')->assertSee('9j 10m')->assertSee('Tepat waktu');
    }

    public function testCheckInTerlambat(): void
    {
        $this->at('2026-11-10 08:30:00');

        $this->as($this->employee)->post('attendance/check-in')
            ->assertSessionHas('success', 'Check-in tercatat 08:30. Anda terlambat 30 menit.');
        $this->seeInDatabase('attendances', ['employee_id' => $this->employee['id'], 'status' => 'late']);
    }

    public function testCheckOutTanpaCheckIn(): void
    {
        $this->as($this->employee)->post('attendance/check-out')
            ->assertSessionHas('error', 'Anda belum check-in hari ini.');
    }

    public function testDashboardHrMenampilkanKehadiranHariIni(): void
    {
        $this->makeEmployee('Budi', 'budi@example.com');
        $this->as($this->employee)->post('attendance/check-in');

        $this->as($this->admin)->get('admin')
            ->assertOK()
            ->assertSee('Andi')
            ->assertSee('Budi')
            ->assertSee('Belum hadir');
    }
}
