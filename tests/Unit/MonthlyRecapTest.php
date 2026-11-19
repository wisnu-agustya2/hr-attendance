<?php

namespace Tests\Unit;

use App\Libraries\MonthlyRecap;
use App\Libraries\WorkCalendar;
use CodeIgniter\Test\CIUnitTestCase;

final class MonthlyRecapTest extends CIUnitTestCase
{
    public function testRekapBulanan(): void
    {
        // November 2026: hari kerja 2-6, 9-13, 16-20, 23-27, 30 (21 hari). "Hari ini" = Selasa 10 Nov,
        // jadi hari yang dihitung alpa adalah 2, 3, 4, 5, 6, dan 9 November.
        $recap = new MonthlyRecap(new WorkCalendar());

        $att = static fn (int $id, string $date, string $status, int $minutes = 480) => [
            'employee_id' => $id, 'work_date' => $date, 'status' => $status, 'worked_minutes' => $minutes,
        ];

        $rows = $recap->build(2026, 11, [
            ['id' => 1, 'name' => 'Andi'],
            ['id' => 2, 'name' => 'Budi'],
        ], [
            $att(1, '2026-11-02', 'present'),
            $att(1, '2026-11-03', 'late', 450),
            $att(1, '2026-11-04', 'present'),
            $att(1, '2026-11-06', 'present'), // tetap masuk di hari pertama cuti
            $att(2, '2026-11-09', 'late', 300),
        ], [
            ['employee_id' => 1, 'type' => 'sick', 'start_date' => '2026-11-05', 'end_date' => '2026-11-05'],
            ['employee_id' => 1, 'type' => 'annual', 'start_date' => '2026-11-06', 'end_date' => '2026-11-09'],
            // Cuti yang dimulai bulan lalu hanya dihitung bagian November-nya
            ['employee_id' => 2, 'type' => 'permit', 'start_date' => '2026-10-29', 'end_date' => '2026-11-03'],
        ], '2026-11-10');

        [$andi, $budi] = $rows;

        $this->assertSame(21, $andi['workdays']);
        $this->assertSame(4, $andi['present']);
        $this->assertSame(1, $andi['late']);
        $this->assertSame(1, $andi['sick']);
        $this->assertSame(1, $andi['annual']); // hanya 9 Nov; 6 Nov dihitung hadir, 7-8 akhir pekan
        $this->assertSame(0, $andi['absent']);
        $this->assertSame(480 * 3 + 450, $andi['worked_minutes']);

        $this->assertSame(1, $budi['present']);
        $this->assertSame(2, $budi['permit']);  // 2 dan 3 Nov
        $this->assertSame(3, $budi['absent']);  // 4, 5, 6 Nov
    }

    public function testBulanDepanBelumAdaAlpa(): void
    {
        $rows = (new MonthlyRecap(new WorkCalendar()))->build(2026, 12, [['id' => 1, 'name' => 'Andi']], [], [], '2026-11-10');

        $this->assertSame(0, $rows[0]['absent']);
        $this->assertSame(23, $rows[0]['workdays']);
    }
}
