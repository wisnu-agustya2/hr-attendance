<?php

namespace App\Database\Seeds;

use App\Libraries\AttendanceRules;
use App\Libraries\WorkCalendar;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;
use App\Models\LeaveRequestModel;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * Data demo: 1 akun HR, 4 karyawan, absensi 15 hari kerja terakhir, dan beberapa pengajuan cuti.
 * php spark db:seed DatabaseSeeder
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $employees = model(EmployeeModel::class);
        if ($employees->countAllResults() > 0) {
            CLI::write('Database sudah berisi data, seeder dilewati.', 'yellow');

            return;
        }

        $password = env('ADMIN_PASSWORD') ?: bin2hex(random_bytes(6));
        $hash     = password_hash($password, PASSWORD_DEFAULT);

        $people = [
            ['Dewi Lestari', 'hr@example.com', 'HR Manager', 'admin'],
            ['Andi Pratama', 'andi@example.com', 'Backend Developer', 'employee'],
            ['Siti Rahma', 'siti@example.com', 'UI/UX Designer', 'employee'],
            ['Budi Santoso', 'budi@example.com', 'QA Engineer', 'employee'],
            ['Rina Wijaya', 'rina@example.com', 'Project Manager', 'employee'],
        ];
        $ids = [];
        foreach ($people as [$name, $email, $position, $role]) {
            $ids[] = $employees->insert([
                'name' => $name, 'email' => $email, 'position' => $position,
                'role' => $role, 'password_hash' => $hash, 'is_active' => 1,
            ]);
        }

        // Absensi 15 hari kerja terakhir (tidak termasuk hari ini) dengan variasi jam masuk
        $calendar = WorkCalendar::fromConfig();
        $rules    = AttendanceRules::fromConfig();
        $today    = Time::today();
        $days     = $calendar->workdaysBetween($today->subDays(30)->toDateString(), $today->subDays(1)->toDateString());
        $days     = array_slice($days, -15);
        $model    = model(AttendanceModel::class);
        mt_srand(42); // data demo selalu sama

        foreach (array_slice($ids, 1) as $n => $employeeId) {
            foreach ($days as $i => $day) {
                if (($i + $n) % 9 === 4) {
                    continue; // sesekali tidak hadir
                }
                $checkIn  = Time::parse("{$day} 07:40:00")->addMinutes(mt_rand(0, 50));
                $checkOut = Time::parse("{$day} 17:00:00")->addMinutes(mt_rand(0, 60));
                $model->insert([
                    'employee_id' => $employeeId,
                    'work_date'   => $day,
                    'check_in'    => $checkIn->toDateTimeString(),
                    'check_out'   => $checkOut->toDateTimeString(),
                    'status'      => $rules->status($checkIn),
                ]);
            }
        }

        $leaves = model(LeaveRequestModel::class);
        $next   = $calendar->workdaysBetween($today->addDays(7)->toDateString(), $today->addDays(20)->toDateString());
        $leaves->insert([
            'employee_id' => $ids[1], 'type' => 'annual', 'start_date' => $next[0], 'end_date' => $next[1],
            'days' => 2, 'reason' => 'Acara keluarga di luar kota', 'status' => 'pending',
        ]);
        $leaves->insert([
            'employee_id' => $ids[2], 'type' => 'sick', 'start_date' => $days[3], 'end_date' => $days[3],
            'days' => 1, 'reason' => 'Demam, surat dokter terlampir', 'status' => 'approved',
            'reviewed_by' => $ids[0], 'reviewed_at' => Time::now()->toDateTimeString(),
        ]);

        CLI::write('Data demo dibuat. Semua akun memakai password: ' . $password, 'green');
        CLI::write('Login HR: hr@example.com · Login karyawan: andi@example.com', 'green');
    }
}
