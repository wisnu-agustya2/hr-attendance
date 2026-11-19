<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Aturan absensi. Bisa diubah lewat .env, misalnya:
 *   attendance.workStart = 09:00
 *   attendance.lateToleranceMinutes = 10
 */
class Attendance extends BaseConfig
{
    /** Jam masuk kerja (HH:MM). */
    public string $workStart = '08:00';

    /** Toleransi keterlambatan dalam menit. Check-in setelah workStart + toleransi = terlambat. */
    public int $lateToleranceMinutes = 15;

    /** Jatah cuti tahunan (hari kerja). */
    public int $annualLeaveQuota = 12;

    /** Hari kerja, format ISO-8601 (1 = Senin ... 7 = Minggu). */
    public array $workDays = [1, 2, 3, 4, 5];

    /** Hari libur nasional/perusahaan (Y-m-d) yang tidak dihitung sebagai hari kerja. */
    public array $holidays = [];
}
