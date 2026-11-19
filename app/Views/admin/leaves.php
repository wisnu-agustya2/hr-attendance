<?php use App\Models\LeaveRequestModel; ?>
<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <h1>Persetujuan Cuti</h1>
    <nav class="tabs">
        <?php foreach (LeaveRequestModel::STATUSES as $value => $label): ?>
            <a href="<?= site_url('admin/leaves?status=' . $value) ?>" class="<?= $status === $value ? 'active' : '' ?>"><?= esc($label) ?></a>
        <?php endforeach ?>
    </nav>
</div>
<div class="card">
    <table>
        <thead><tr><th>Karyawan</th><th>Jenis</th><th>Tanggal</th><th>Hari</th><th>Alasan</th><th><?= $status === 'pending' ? 'Tindakan' : 'Catatan HR' ?></th></tr></thead>
        <tbody>
        <?php foreach ($leaves as $l): ?>
            <tr>
                <td><?= esc($l['employee_name']) ?></td>
                <td><?= esc(LeaveRequestModel::TYPES[$l['type']] ?? $l['type']) ?></td>
                <td><?= tanggal($l['start_date'], false) ?> – <?= tanggal($l['end_date'], false) ?></td>
                <td><?= (int) $l['days'] ?></td>
                <td><?= esc($l['reason']) ?></td>
                <td>
                    <?php if ($status === 'pending'): ?>
                        <form method="post" class="review">
                            <?= csrf_field() ?>
                            <input name="review_note" placeholder="Catatan (opsional)" maxlength="255">
                            <button class="btn btn-small" formaction="<?= site_url('admin/leaves/' . $l['id'] . '/approve') ?>">Setujui</button>
                            <button class="btn btn-small btn-danger" formaction="<?= site_url('admin/leaves/' . $l['id'] . '/reject') ?>">Tolak</button>
                        </form>
                    <?php else: ?>
                        <?= esc($l['review_note'] ?? '-') ?>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
        <?php if ($leaves === []): ?>
            <tr><td colspan="6" class="muted">Tidak ada pengajuan.</td></tr>
        <?php endif ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
