<?php

namespace App\Models;

use CodeIgniter\Model;

class LeaveRequestModel extends Model
{
    protected $table         = 'leave_requests';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'employee_id', 'type', 'start_date', 'end_date', 'days', 'reason',
        'status', 'reviewed_by', 'reviewed_at', 'review_note',
    ];

    public const TYPES = [
        'annual' => 'Cuti tahunan',
        'sick'   => 'Sakit',
        'permit' => 'Izin',
    ];

    public const STATUSES = [
        'pending'  => 'Menunggu',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];

    /** Jumlah hari cuti tahunan yang sudah disetujui pada tahun tertentu. */
    public function annualDaysUsed(int $employeeId, int $year): int
    {
        $row = $this->selectSum('days', 'total')
            ->where('employee_id', $employeeId)
            ->where('type', 'annual')
            ->where('status', 'approved')
            ->where('start_date >=', "{$year}-01-01")
            ->where('start_date <=', "{$year}-12-31")
            ->first();

        return (int) ($row['total'] ?? 0);
    }

    /** Apakah ada pengajuan lain (menunggu/disetujui) yang tanggalnya bertabrakan. */
    public function hasOverlap(int $employeeId, string $start, string $end, ?int $ignoreId = null): bool
    {
        $builder = $this->where('employee_id', $employeeId)
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_date <=', $end)
            ->where('end_date >=', $start);
        if ($ignoreId !== null) {
            $builder->where('id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }

    /** @return list<array<string, mixed>> */
    public function approvedInRange(string $start, string $end): array
    {
        return $this->where('status', 'approved')
            ->where('start_date <=', $end)
            ->where('end_date >=', $start)
            ->findAll();
    }
}
