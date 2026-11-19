<?php

use App\Libraries\AttendanceRules;
use App\Models\LeaveRequestModel;

if (! function_exists('tanggal')) {
    /** "2026-11-10" -> "Sel, 10 Nov 2026" */
    function tanggal(?string $date, bool $withDay = true): string
    {
        if (! $date) {
            return '-';
        }
        $days   = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $ts     = strtotime($date);

        return ($withDay ? $days[(int) date('w', $ts)] . ', ' : '')
            . date('j', $ts) . ' ' . $months[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
    }
}

if (! function_exists('jam')) {
    function jam(?string $datetime): string
    {
        return $datetime ? date('H:i', strtotime($datetime)) : '-';
    }
}

if (! function_exists('durasi')) {
    function durasi(int $minutes): string
    {
        return AttendanceRules::formatMinutes($minutes);
    }
}

if (! function_exists('bulan')) {
    /** 2026, 11 -> "November 2026" */
    function bulan(int $year, int $month): string
    {
        $names = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return $names[$month - 1] . ' ' . $year;
    }
}

if (! function_exists('status_badge')) {
    function status_badge(string $status): string
    {
        $labels = [
            'present' => ['Tepat waktu', 'ok'],
            'late'    => ['Terlambat', 'warn'],
        ] + array_map(static fn ($label) => [$label, ''], LeaveRequestModel::STATUSES);
        $classes = ['approved' => 'ok', 'rejected' => 'danger', 'pending' => 'warn'];
        [$label, $class] = $labels[$status] ?? [$status, ''];
        $class = $classes[$status] ?? $class;

        return '<span class="badge ' . esc($class, 'attr') . '">' . esc($label) . '</span>';
    }
}
