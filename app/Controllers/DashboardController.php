<?php

namespace App\Controllers;

use App\Libraries\AttendanceRules;
use App\Libraries\MonthlyRecap;
use App\Libraries\WorkCalendar;
use App\Models\AttendanceModel;
use App\Models\LeaveRequestModel;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $now   = $this->now();
        $today = $now->toDateString();
        $year  = (int) $now->getYear();
        $month = (int) $now->getMonth();
        $user  = $this->user();

        $attendances = model(AttendanceModel::class)->forMonth($year, $month, $this->userId());
        $leaves      = model(LeaveRequestModel::class)->where('employee_id', $this->userId())->where('status', 'approved')->findAll();
        $recap       = (new MonthlyRecap(WorkCalendar::fromConfig()))->build(
            $year,
            $month,
            [['id' => $this->userId(), 'name' => $user['name']]],
            $attendances,
            $leaves,
            $today,
        )[0];

        $quota = config('Attendance')->annualLeaveQuota;
        $used  = model(LeaveRequestModel::class)->annualDaysUsed($this->userId(), $year);

        return view('dashboard', [
            'user'        => $user,
            'now'         => $now,
            'today'       => model(AttendanceModel::class)->forDay($this->userId(), $today),
            'isWorkday'   => WorkCalendar::fromConfig()->isWorkday($today),
            'deadline'    => AttendanceRules::fromConfig()->deadline($now)->format('H:i'),
            'recap'       => $recap,
            'recent'      => array_slice($attendances, 0, 5),
            'leaveQuota'  => $quota,
            'leaveLeft'   => max(0, $quota - $used),
            'monthLabel'  => bulan($year, $month),
        ]);
    }
}
