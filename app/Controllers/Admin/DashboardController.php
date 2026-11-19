<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;
use App\Models\LeaveRequestModel;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $today     = $this->now()->toDateString();
        $employees = model(EmployeeModel::class)->activeEmployees();

        $attendance = [];
        foreach (model(AttendanceModel::class)->where('work_date', $today)->findAll() as $row) {
            $attendance[$row['employee_id']] = $row;
        }
        $onLeave = [];
        foreach (model(LeaveRequestModel::class)->approvedInRange($today, $today) as $leave) {
            $onLeave[$leave['employee_id']] = $leave['type'];
        }

        $rows    = [];
        $summary = ['present' => 0, 'late' => 0, 'leave' => 0, 'missing' => 0];
        foreach ($employees as $employee) {
            $a     = $attendance[$employee['id']] ?? null;
            $leave = $onLeave[$employee['id']] ?? null;
            if ($a) {
                $summary[$a['status'] === 'late' ? 'late' : 'present']++;
            } elseif ($leave) {
                $summary['leave']++;
            } else {
                $summary['missing']++;
            }
            $rows[] = ['employee' => $employee, 'attendance' => $a, 'leave' => $leave];
        }

        return view('admin/dashboard', [
            'today'         => $today,
            'rows'          => $rows,
            'summary'       => $summary,
            'pendingLeaves' => model(LeaveRequestModel::class)->where('status', 'pending')->countAllResults(),
        ]);
    }
}
