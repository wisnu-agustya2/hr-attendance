<?php

namespace App\Libraries;

use App\Models\LeaveRequestModel;

/**
 * Aturan pengajuan cuti, dipakai saat karyawan mengajukan dan saat HR menyetujui.
 */
class LeavePolicy
{
    public function __construct(
        private LeaveRequestModel $leaves,
        private WorkCalendar $calendar,
        private int $annualQuota,
    ) {
    }

    public static function make(): self
    {
        return new self(model(LeaveRequestModel::class), WorkCalendar::fromConfig(), config('Attendance')->annualLeaveQuota);
    }

    public function workdays(string $start, string $end): int
    {
        return $this->calendar->countWorkdays($start, $end);
    }

    public function remainingAnnual(int $employeeId, int $year): int
    {
        return max(0, $this->annualQuota - $this->leaves->annualDaysUsed($employeeId, $year));
    }

    /**
     * Mengembalikan pesan error, atau null jika pengajuan boleh diproses.
     */
    public function check(int $employeeId, string $type, string $start, string $end, ?int $ignoreId = null): ?string
    {
        if ($end < $start) {
            return 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
        }
        if (substr($start, 0, 4) !== substr($end, 0, 4)) {
            return 'Pengajuan tidak boleh melewati pergantian tahun. Pisahkan menjadi dua pengajuan.';
        }
        $days = $this->workdays($start, $end);
        if ($days === 0) {
            return 'Rentang tanggal tidak berisi hari kerja.';
        }
        if ($this->leaves->hasOverlap($employeeId, $start, $end, $ignoreId)) {
            return 'Sudah ada pengajuan lain pada tanggal tersebut.';
        }
        if ($type === 'annual') {
            $left = $this->remainingAnnual($employeeId, (int) substr($start, 0, 4));
            if ($days > $left) {
                return "Sisa cuti tahunan tidak cukup: butuh {$days} hari, sisa {$left} hari.";
            }
        }

        return null;
    }
}
