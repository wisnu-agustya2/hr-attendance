<?php

namespace App\Controllers;

use App\Libraries\LeavePolicy;
use App\Models\LeaveRequestModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class LeaveController extends BaseController
{
    public function index(): string
    {
        $year = (int) $this->now()->getYear();

        return view('leaves/index', [
            'leaves'    => model(LeaveRequestModel::class)->where('employee_id', $this->userId())->orderBy('start_date', 'DESC')->findAll(),
            'quota'     => config('Attendance')->annualLeaveQuota,
            'remaining' => LeavePolicy::make()->remainingAnnual($this->userId(), $year),
            'year'      => $year,
        ]);
    }

    public function new(): string
    {
        return view('leaves/form', ['types' => LeaveRequestModel::TYPES]);
    }

    public function create(): RedirectResponse
    {
        $rules = [
            'type'       => 'required|in_list[' . implode(',', array_keys(LeaveRequestModel::TYPES)) . ']',
            'start_date' => 'required|valid_date[Y-m-d]',
            'end_date'   => 'required|valid_date[Y-m-d]',
            'reason'     => 'required|min_length[5]|max_length[500]',
        ];
        $labels = ['type' => 'Jenis', 'start_date' => 'Tanggal mulai', 'end_date' => 'Tanggal selesai', 'reason' => 'Alasan'];
        if (! $this->validateData($this->request->getPost(), $this->withLabels($rules, $labels))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $data   = $this->validator->getValidated();
        $policy = LeavePolicy::make();

        $error = $policy->check($this->userId(), $data['type'], $data['start_date'], $data['end_date']);
        if ($error !== null) {
            return redirect()->back()->withInput()->with('errors', ['end_date' => $error]);
        }

        model(LeaveRequestModel::class)->insert([
            'employee_id' => $this->userId(),
            'type'        => $data['type'],
            'start_date'  => $data['start_date'],
            'end_date'    => $data['end_date'],
            'days'        => $policy->workdays($data['start_date'], $data['end_date']),
            'reason'      => trim($data['reason']),
            'status'      => 'pending',
        ]);

        return redirect()->to(site_url('leaves'))->with('success', 'Pengajuan terkirim dan menunggu persetujuan HR.');
    }

    public function cancel(int $id): RedirectResponse
    {
        $model = model(LeaveRequestModel::class);
        $leave = $model->where(['id' => $id, 'employee_id' => $this->userId()])->first();
        if ($leave === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        if ($leave['status'] !== 'pending') {
            return redirect()->to(site_url('leaves'))->with('error', 'Hanya pengajuan yang masih menunggu yang bisa dibatalkan.');
        }
        $model->delete($id);

        return redirect()->to(site_url('leaves'))->with('success', 'Pengajuan dibatalkan.');
    }
}
