<?php

namespace App\Controllers;

use App\Libraries\AttendanceRules;
use App\Models\AttendanceModel;
use CodeIgniter\HTTP\RedirectResponse;

class AttendanceController extends BaseController
{
    public function checkIn(): RedirectResponse
    {
        $now     = $this->now();
        $model   = model(AttendanceModel::class);
        $today   = $now->toDateString();

        if ($model->forDay($this->userId(), $today) !== null) {
            return redirect()->to(site_url('/'))->with('error', 'Anda sudah check-in hari ini.');
        }

        $rules  = AttendanceRules::fromConfig();
        $status = $rules->status($now);
        $note   = trim((string) $this->request->getPost('note'));

        try {
            $model->insert([
                'employee_id' => $this->userId(),
                'work_date'   => $today,
                'check_in'    => $now->toDateTimeString(),
                'status'      => $status,
                'note'        => $note === '' ? null : mb_substr($note, 0, 255),
            ]);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException) {
            // Unique key (employee_id, work_date) mencegah double check-in saat tombol diklik dua kali bersamaan
            return redirect()->to(site_url('/'))->with('error', 'Anda sudah check-in hari ini.');
        }

        $message = $status === AttendanceRules::LATE
            ? sprintf('Check-in tercatat %s. Anda terlambat %d menit.', $now->format('H:i'), $rules->lateMinutes($now))
            : sprintf('Check-in tercatat %s. Selamat bekerja!', $now->format('H:i'));

        return redirect()->to(site_url('/'))->with('success', $message);
    }

    public function checkOut(): RedirectResponse
    {
        $now   = $this->now();
        $model = model(AttendanceModel::class);
        $row   = $model->forDay($this->userId(), $now->toDateString());

        if ($row === null) {
            return redirect()->to(site_url('/'))->with('error', 'Anda belum check-in hari ini.');
        }
        if ($row['check_out'] !== null) {
            return redirect()->to(site_url('/'))->with('error', 'Anda sudah check-out hari ini.');
        }

        $model->update($row['id'], ['check_out' => $now->toDateTimeString()]);
        $minutes = AttendanceRules::workedMinutes(new \DateTimeImmutable($row['check_in']), $now);

        return redirect()->to(site_url('/'))->with('success', 'Check-out tercatat ' . $now->format('H:i') . '. Total kerja hari ini ' . durasi($minutes) . '.');
    }

    public function history(): string
    {
        [$year, $month] = $this->selectedMonth();

        return view('attendance/history', [
            'rows'       => model(AttendanceModel::class)->forMonth($year, $month, $this->userId()),
            'month'      => sprintf('%04d-%02d', $year, $month),
            'monthLabel' => bulan($year, $month),
        ]);
    }
}
