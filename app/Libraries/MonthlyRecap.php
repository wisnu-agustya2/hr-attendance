<?php

namespace App\Libraries;

use DateTimeImmutable;

/**
 * Menyusun rekap absensi bulanan per karyawan dari data mentah.
 * Tidak menyentuh database, jadi mudah diuji.
 */
class MonthlyRecap
{
    public function __construct(private WorkCalendar $calendar)
    {
    }

    /**
     * @param list<array{id:int|string, name:string}>                                                   $employees
     * @param list<array{employee_id:int|string, work_date:string, status:string, worked_minutes:int}> $attendances
     * @param list<array{employee_id:int|string, type:string, start_date:string, end_date:string}>      $approvedLeaves
     * @param string                                                                                     $today       Y-m-d; hanya hari sebelum ini yang dihitung alpa
     *
     * @return list<array<string, int|string>>
     */
    public function build(int $year, int $month, array $employees, array $attendances, array $approvedLeaves, string $today): array
    {
        $first = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $last  = $first->modify('last day of this month')->format('Y-m-d');
        $first = $first->format('Y-m-d');

        // Alpa hanya dihitung untuk hari kerja yang sudah lewat. Hari ini belum dihitung
        // karena karyawan mungkin belum check-in.
        $yesterday   = (new DateTimeImmutable($today))->modify('-1 day')->format('Y-m-d');
        $countUntil  = min($last, $yesterday);
        $pastDays    = $countUntil < $first ? [] : $this->calendar->workdaysBetween($first, $countUntil);
        $allWorkdays = $this->calendar->workdaysBetween($first, $last);

        $attendanceByEmployee = [];
        foreach ($attendances as $a) {
            $attendanceByEmployee[$a['employee_id']][$a['work_date']] = $a;
        }

        $leaveByEmployee = [];
        foreach ($approvedLeaves as $leave) {
            $start = max($leave['start_date'], $first);
            $end   = min($leave['end_date'], $last);
            foreach ($this->calendar->workdaysBetween($start, $end) as $day) {
                $leaveByEmployee[$leave['employee_id']][$day] = $leave['type'];
            }
        }

        $rows = [];
        foreach ($employees as $employee) {
            $id       = $employee['id'];
            $attended = $attendanceByEmployee[$id] ?? [];
            $leaves   = $leaveByEmployee[$id] ?? [];

            $row = [
                'employee_id'    => $id,
                'name'           => $employee['name'],
                'workdays'       => count($allWorkdays),
                'present'        => 0,
                'late'           => 0,
                'annual'         => 0,
                'sick'           => 0,
                'permit'         => 0,
                'absent'         => 0,
                'worked_minutes' => 0,
            ];

            foreach ($attended as $a) {
                $row['present']++;
                if ($a['status'] === AttendanceRules::LATE) {
                    $row['late']++;
                }
                $row['worked_minutes'] += (int) $a['worked_minutes'];
            }

            foreach ($leaves as $day => $type) {
                // Jika tetap masuk saat cuti, dihitung hadir (bukan cuti)
                if (! isset($attended[$day]) && isset($row[$type])) {
                    $row[$type]++;
                }
            }

            foreach ($pastDays as $day) {
                if (! isset($attended[$day]) && ! isset($leaves[$day])) {
                    $row['absent']++;
                }
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
