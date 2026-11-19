<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\MonthlyRecap;
use App\Libraries\WorkCalendar;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;
use App\Models\LeaveRequestModel;
use CodeIgniter\HTTP\ResponseInterface;

class RecapController extends BaseController
{
    public function index(): string
    {
        [$year, $month] = $this->selectedMonth();

        return view('admin/recap', [
            'rows'       => $this->buildRecap($year, $month),
            'month'      => sprintf('%04d-%02d', $year, $month),
            'monthLabel' => bulan($year, $month),
        ]);
    }

    public function export(): ResponseInterface
    {
        [$year, $month] = $this->selectedMonth();
        $rows = $this->buildRecap($year, $month);

        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8 dengan benar
        fputcsv($out, ['Nama', 'Hari Kerja', 'Hadir', 'Terlambat', 'Cuti Tahunan', 'Sakit', 'Izin', 'Alpa', 'Total Jam Kerja'], ',', '"', '');
        foreach ($rows as $r) {
            fputcsv($out, [
                // Cegah CSV/formula injection di Excel
                preg_match('/^[=+\-@\t\r]/', $r['name']) ? "'" . $r['name'] : $r['name'],
                $r['workdays'], $r['present'], $r['late'], $r['annual'], $r['sick'], $r['permit'], $r['absent'],
                round($r['worked_minutes'] / 60, 2),
            ], ',', '"', '');
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', sprintf('attachment; filename="rekap-absensi-%04d-%02d.csv"', $year, $month))
            ->setBody($csv);
    }

    /** @return list<array<string, int|string>> */
    private function buildRecap(int $year, int $month): array
    {
        $first = sprintf('%04d-%02d-01', $year, $month);
        $last  = date('Y-m-t', strtotime($first));

        return (new MonthlyRecap(WorkCalendar::fromConfig()))->build(
            $year,
            $month,
            model(EmployeeModel::class)->activeEmployees(),
            model(AttendanceModel::class)->forMonth($year, $month),
            model(LeaveRequestModel::class)->approvedInRange($first, $last),
            $this->now()->toDateString(),
        );
    }
}
