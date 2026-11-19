<?php

namespace Tests\Unit;

use App\Libraries\AttendanceRules;
use CodeIgniter\Test\CIUnitTestCase;
use DateTimeImmutable;

final class AttendanceRulesTest extends CIUnitTestCase
{
    public function testStatusBerdasarkanToleransi(): void
    {
        $rules = new AttendanceRules('08:00', 15);

        $this->assertSame(AttendanceRules::PRESENT, $rules->status(new DateTimeImmutable('2026-11-10 07:30:00')));
        $this->assertSame(AttendanceRules::PRESENT, $rules->status(new DateTimeImmutable('2026-11-10 08:15:00')));
        $this->assertSame(AttendanceRules::LATE, $rules->status(new DateTimeImmutable('2026-11-10 08:15:01')));
        $this->assertSame('08:15', $rules->deadline(new DateTimeImmutable('2026-11-10 06:00:00'))->format('H:i'));
    }

    public function testMenitKeterlambatanDihitungDariJamMasuk(): void
    {
        $rules = new AttendanceRules('09:00', 10);

        $this->assertSame(0, $rules->lateMinutes(new DateTimeImmutable('2026-11-10 09:05:00')));
        $this->assertSame(25, $rules->lateMinutes(new DateTimeImmutable('2026-11-10 09:25:00')));
    }

    public function testDurasiKerja(): void
    {
        $in = new DateTimeImmutable('2026-11-10 08:00:00');

        $this->assertSame(545, AttendanceRules::workedMinutes($in, new DateTimeImmutable('2026-11-10 17:05:00')));
        $this->assertSame(0, AttendanceRules::workedMinutes($in, null));
        $this->assertSame(0, AttendanceRules::workedMinutes($in, new DateTimeImmutable('2026-11-10 07:00:00')));
        $this->assertSame('9j 05m', AttendanceRules::formatMinutes(545));
    }
}
