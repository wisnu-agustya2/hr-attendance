<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\LeavePolicy;
use App\Models\LeaveRequestModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class LeaveApprovalController extends BaseController
{
    public function index(): string
    {
        $status = $this->request->getGet('status');
        $status = array_key_exists((string) $status, LeaveRequestModel::STATUSES) ? $status : 'pending';

        $leaves = model(LeaveRequestModel::class)
            ->select('leave_requests.*, employees.name AS employee_name')
            ->join('employees', 'employees.id = leave_requests.employee_id')
            ->where('leave_requests.status', $status)
            ->orderBy('leave_requests.start_date', $status === 'pending' ? 'ASC' : 'DESC')
            ->findAll();

        return view('admin/leaves', ['leaves' => $leaves, 'status' => $status]);
    }

    public function approve(int $id): RedirectResponse
    {
        $leave = $this->findPending($id);

        // Cek ulang kuota: bisa saja ada cuti lain yang disetujui setelah pengajuan ini dibuat
        $error = LeavePolicy::make()->check((int) $leave['employee_id'], $leave['type'], $leave['start_date'], $leave['end_date'], (int) $leave['id']);
        if ($error !== null) {
            return redirect()->to(site_url('admin/leaves'))->with('error', 'Tidak bisa disetujui: ' . $error);
        }

        $this->review($leave, 'approved');

        return redirect()->to(site_url('admin/leaves'))->with('success', 'Pengajuan disetujui.');
    }

    public function reject(int $id): RedirectResponse
    {
        $leave = $this->findPending($id);
        $this->review($leave, 'rejected');

        return redirect()->to(site_url('admin/leaves'))->with('success', 'Pengajuan ditolak.');
    }

    private function review(array $leave, string $status): void
    {
        $note = trim((string) $this->request->getPost('review_note'));
        model(LeaveRequestModel::class)->update($leave['id'], [
            'status'      => $status,
            'reviewed_by' => $this->userId(),
            'reviewed_at' => $this->now()->toDateTimeString(),
            'review_note' => $note === '' ? null : mb_substr($note, 0, 255),
        ]);
    }

    private function findPending(int $id): array
    {
        $leave = model(LeaveRequestModel::class)->find($id);
        if ($leave === null || $leave['status'] !== 'pending') {
            throw PageNotFoundException::forPageNotFound();
        }

        return $leave;
    }
}
