<?php

namespace App\Models;

use App\Libraries\AttendanceRules;
use CodeIgniter\Model;
use DateTimeImmutable;

class AttendanceModel extends Model
{
    protected $table         = 'attendances';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['employee_id', 'work_date', 'check_in', 'check_out', 'status', 'note'];

    public function forDay(int $employeeId, string $date): ?array
    {
        return $this->where(['employee_id' => $employeeId, 'work_date' => $date])->first();
    }

    /**
     * Absensi dalam satu bulan, ditambah kolom worked_minutes.
     *
     * @return list<array<string, mixed>>
     */
    public function forMonth(int $year, int $month, ?int $employeeId = null): array
    {
        $first = sprintf('%04d-%02d-01', $year, $month);
        $last  = (new DateTimeImmutable($first))->modify('last day of this month')->format('Y-m-d');

        $builder = $this->where('work_date >=', $first)->where('work_date <=', $last);
        if ($employeeId !== null) {
            $builder->where('employee_id', $employeeId);
        }

        return array_map(static function (array $row) {
            $row['worked_minutes'] = AttendanceRules::workedMinutes(
                new DateTimeImmutable($row['check_in']),
                $row['check_out'] ? new DateTimeImmutable($row['check_out']) : null,
            );

            return $row;
        }, $builder->orderBy('work_date', 'DESC')->findAll());
    }
}
