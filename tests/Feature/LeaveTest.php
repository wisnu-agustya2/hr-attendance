<?php

namespace Tests\Feature;

use App\Models\LeaveRequestModel;

final class LeaveTest extends HrTestCase
{
    private function apply(array $employee, string $start, string $end, string $type = 'annual')
    {
        return $this->as($employee)->post('leaves', [
            'type' => $type, 'start_date' => $start, 'end_date' => $end, 'reason' => 'Keperluan keluarga',
        ]);
    }

    public function testPengajuanValidDisimpanDenganJumlahHariKerja(): void
    {
        // Jumat 20 s.d. Selasa 24 Nov = 3 hari kerja
        $this->apply($this->employee, '2026-11-20', '2026-11-24')
            ->assertRedirectTo(site_url('leaves'))
            ->assertSessionHas('success');

        $this->seeInDatabase('leave_requests', ['employee_id' => $this->employee['id'], 'days' => 3, 'status' => 'pending']);
        // Pengajuan yang belum disetujui belum memotong kuota
        $this->as($this->employee)->get('leaves')->assertSee('12 dari 12 hari');
    }

    public function testValidasiPengajuan(): void
    {
        $this->as($this->employee)->post('leaves', ['type' => 'liburan', 'start_date' => '2026-13-01', 'end_date' => '', 'reason' => ''])
            ->assertSessionHas('errors');

        $cases = [
            ['2026-11-24', '2026-11-20', 'Tanggal selesai tidak boleh sebelum tanggal mulai.'],
            ['2026-11-14', '2026-11-15', 'Rentang tanggal tidak berisi hari kerja.'],
            ['2026-12-30', '2027-01-04', 'Pengajuan tidak boleh melewati pergantian tahun. Pisahkan menjadi dua pengajuan.'],
            ['2026-11-02', '2026-11-18', 'Sisa cuti tahunan tidak cukup: butuh 13 hari, sisa 12 hari.'],
        ];
        foreach ($cases as [$start, $end, $message]) {
            $this->apply($this->employee, $start, $end)->assertSessionHas('errors', ['end_date' => $message]);
        }
        $this->seeNumRecords(0, 'leave_requests', []);
    }

    public function testTanggalBertabrakanDitolak(): void
    {
        $this->apply($this->employee, '2026-11-20', '2026-11-24');
        $this->apply($this->employee, '2026-11-24', '2026-11-24', 'sick')
            ->assertSessionHas('errors', ['end_date' => 'Sudah ada pengajuan lain pada tanggal tersebut.']);
    }

    public function testBatalkanPengajuan(): void
    {
        $this->apply($this->employee, '2026-11-20', '2026-11-20');
        $id = model(LeaveRequestModel::class)->first()['id'];

        // Karyawan lain tidak bisa membatalkan
        $other = $this->makeEmployee('Budi', 'budi@example.com');
        $this->assertPageNotFound(fn () => $this->as($other)->post("leaves/{$id}/cancel"));

        $this->as($this->employee)->post("leaves/{$id}/cancel")->assertSessionHas('success', 'Pengajuan dibatalkan.');
        $this->seeNumRecords(0, 'leave_requests', []);
    }

    public function testHrMenyetujuiDanKuotaBerkurang(): void
    {
        $this->apply($this->employee, '2026-11-16', '2026-11-20'); // 5 hari
        $id = model(LeaveRequestModel::class)->first()['id'];

        $this->as($this->admin)->get('admin/leaves')->assertOK()->assertSee('Andi')->assertSee('Keperluan keluarga');
        $this->as($this->admin)->post("admin/leaves/{$id}/approve", ['review_note' => 'OK'])
            ->assertSessionHas('success', 'Pengajuan disetujui.');

        $this->seeInDatabase('leave_requests', ['id' => $id, 'status' => 'approved', 'reviewed_by' => $this->admin['id'], 'review_note' => 'OK']);
        $this->as($this->employee)->get('leaves')->assertSee('7 dari 12 hari')->assertSee('HR: OK');

        // Pengajuan yang sudah diproses tidak bisa diproses lagi
        $this->assertPageNotFound(fn () => $this->as($this->admin)->post("admin/leaves/{$id}/reject"));
        // Yang sudah disetujui tidak bisa dibatalkan karyawan
        $this->as($this->employee)->post("leaves/{$id}/cancel")
            ->assertSessionHas('error', 'Hanya pengajuan yang masih menunggu yang bisa dibatalkan.');
    }

    public function testPersetujuanMengecekUlangKuota(): void
    {
        // Dua pengajuan yang masing-masing masih muat, tapi jika digabung melebihi kuota 12 hari
        $this->apply($this->employee, '2026-11-02', '2026-11-13');  // 10 hari
        $this->apply($this->employee, '2026-11-23', '2026-11-27');  // 5 hari
        [$first, $second] = model(LeaveRequestModel::class)->orderBy('id')->findAll();

        $this->as($this->admin)->post("admin/leaves/{$first['id']}/approve")->assertSessionHas('success');
        $this->as($this->admin)->post("admin/leaves/{$second['id']}/approve")
            ->assertSessionHas('error', 'Tidak bisa disetujui: Sisa cuti tahunan tidak cukup: butuh 5 hari, sisa 2 hari.');
        $this->seeInDatabase('leave_requests', ['id' => $second['id'], 'status' => 'pending']);

        $this->as($this->admin)->post("admin/leaves/{$second['id']}/reject", ['review_note' => 'Kuota habis'])
            ->assertSessionHas('success', 'Pengajuan ditolak.');
        $this->seeInDatabase('leave_requests', ['id' => $second['id'], 'status' => 'rejected']);
    }

    public function testCutiSakitTidakMemotongKuota(): void
    {
        $this->apply($this->employee, '2026-11-16', '2026-11-27', 'sick');
        $this->as($this->admin)->post('admin/leaves/' . model(LeaveRequestModel::class)->first()['id'] . '/approve');

        $this->as($this->employee)->get('leaves')->assertSee('12 dari 12 hari');
    }
}
