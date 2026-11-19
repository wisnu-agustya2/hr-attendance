<?php

namespace App\Libraries;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Menentukan hari kerja berdasarkan hari dalam seminggu dan daftar hari libur.
 */
class WorkCalendar
{
    /** @var array<string, true> */
    private array $holidays;

    /**
     * @param list<int>    $workDays ISO-8601: 1 = Senin ... 7 = Minggu
     * @param list<string> $holidays tanggal Y-m-d
     */
    public function __construct(private array $workDays = [1, 2, 3, 4, 5], array $holidays = [])
    {
        $this->holidays = array_fill_keys($holidays, true);
    }

    public static function fromConfig(?\Config\Attendance $config = null): self
    {
        $config ??= config('Attendance');

        return new self($config->workDays, $config->holidays);
    }

    public function isWorkday(string $date): bool
    {
        $d = self::parse($date);

        return in_array((int) $d->format('N'), $this->workDays, true) && ! isset($this->holidays[$d->format('Y-m-d')]);
    }

    /**
     * Daftar hari kerja dari $start sampai $end (inklusif).
     *
     * @return list<string>
     */
    public function workdaysBetween(string $start, string $end): array
    {
        $from = self::parse($start);
        $to   = self::parse($end);
        if ($to < $from) {
            return [];
        }

        $days = [];
        foreach (new DatePeriod($from, new DateInterval('P1D'), $to->modify('+1 day')) as $day) {
            $date = $day->format('Y-m-d');
            if ($this->isWorkday($date)) {
                $days[] = $date;
            }
        }

        return $days;
    }

    public function countWorkdays(string $start, string $end): int
    {
        return count($this->workdaysBetween($start, $end));
    }

    private static function parse(string $date): DateTimeImmutable
    {
        $d = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if ($d === false || $d->format('Y-m-d') !== $date) {
            throw new InvalidArgumentException("Tanggal tidak valid: {$date}");
        }

        return $d;
    }
}
