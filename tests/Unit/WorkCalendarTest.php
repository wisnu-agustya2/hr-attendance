<?php

namespace Tests\Unit;

use App\Libraries\WorkCalendar;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

final class WorkCalendarTest extends CIUnitTestCase
{
    public function testWeekendDanHariLiburBukanHariKerja(): void
    {
        $calendar = new WorkCalendar([1, 2, 3, 4, 5], ['2026-11-11']);

        $this->assertTrue($calendar->isWorkday('2026-11-10'));  // Selasa
        $this->assertFalse($calendar->isWorkday('2026-11-11')); // libur
        $this->assertFalse($calendar->isWorkday('2026-11-14')); // Sabtu
        $this->assertFalse($calendar->isWorkday('2026-11-15')); // Minggu
    }

    public function testHariKerjaDalamRentang(): void
    {
        $calendar = new WorkCalendar();

        $this->assertSame(['2026-11-13', '2026-11-16'], $calendar->workdaysBetween('2026-11-13', '2026-11-16'));
        $this->assertSame(21, $calendar->countWorkdays('2026-11-01', '2026-11-30'));
        $this->assertSame([], $calendar->workdaysBetween('2026-11-16', '2026-11-13'));
        $this->assertSame(0, $calendar->countWorkdays('2026-11-14', '2026-11-15'));
    }

    public function testTanggalTidakValidDitolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new WorkCalendar())->isWorkday('2026-02-30');
    }
}
