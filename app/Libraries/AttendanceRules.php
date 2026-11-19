<?php

namespace App\Libraries;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * Aturan status kehadiran: tepat waktu atau terlambat, dan durasi kerja.
 */
class AttendanceRules
{
    public const PRESENT = 'present';
    public const LATE    = 'late';

    public function __construct(private string $workStart = '08:00', private int $toleranceMinutes = 15)
    {
    }

    public static function fromConfig(?\Config\Attendance $config = null): self
    {
        $config ??= config('Attendance');

        return new self($config->workStart, $config->lateToleranceMinutes);
    }

    /** Batas akhir check-in tepat waktu pada tanggal check-in. */
    public function deadline(DateTimeInterface $checkIn): DateTimeImmutable
    {
        [$h, $m] = array_map('intval', explode(':', $this->workStart));

        return DateTimeImmutable::createFromInterface($checkIn)
            ->setTime($h, $m)
            ->modify("+{$this->toleranceMinutes} minutes");
    }

    public function status(DateTimeInterface $checkIn): string
    {
        return $checkIn > $this->deadline($checkIn) ? self::LATE : self::PRESENT;
    }

    /** Menit keterlambatan dihitung dari jam masuk (bukan dari batas toleransi). 0 jika tepat waktu. */
    public function lateMinutes(DateTimeInterface $checkIn): int
    {
        if ($this->status($checkIn) === self::PRESENT) {
            return 0;
        }
        $start = $this->deadline($checkIn)->modify("-{$this->toleranceMinutes} minutes");

        return intdiv($checkIn->getTimestamp() - $start->getTimestamp(), 60);
    }

    public static function workedMinutes(DateTimeInterface $checkIn, ?DateTimeInterface $checkOut): int
    {
        if ($checkOut === null || $checkOut <= $checkIn) {
            return 0;
        }

        return intdiv($checkOut->getTimestamp() - $checkIn->getTimestamp(), 60);
    }

    public static function formatMinutes(int $minutes): string
    {
        return sprintf('%dj %02dm', intdiv($minutes, 60), $minutes % 60);
    }
}
