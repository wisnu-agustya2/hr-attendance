<?php use App\Models\LeaveRequestModel; ?>
<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <h1>Kehadiran Hari Ini</h1>
    <span class="muted"><?= tanggal($today) ?></span>
</div>

<div class="stats">
    <div class="card stat"><span>Tepat waktu</span><strong><?= $summary['present'] ?></strong></div>
    <div class="card stat"><span>Terlambat</span><strong><?= $summary['late'] ?></strong></div>
    <div class="card stat"><span>Cuti/Izin</span><strong><?= $summary['leave'] ?></strong></div>
    <div class="card stat"><span>Belum hadir</span><strong><?= $summary['missing'] ?></strong></div>
    <a class="card stat link-card" href="<?= site_url('admin/leaves') ?>"><span>Cuti menunggu</span><strong><?= $pendingLeaves ?></strong></a>
</div>

<div class="card">
    <table>
        <thead><tr><th>Karyawan</th><th>Jabatan</th><th>Masuk</th><th>Pulang</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $a = $r['attendance']; ?>
            <tr>
                <td><?= esc($r['employee']['name']) ?></td>
                <td><?= esc($r['employee']['position'] ?? '-') ?></td>
                <td><?= $a ? jam($a['check_in']) : '-' ?></td>
                <td><?= $a ? jam($a['check_out']) : '-' ?></td>
                <td>
                    <?php if ($a): ?><?= status_badge($a['status']) ?>
                    <?php elseif ($r['leave']): ?><span class="badge"><?= esc(LeaveRequestModel::TYPES[$r['leave']]) ?></span>
                    <?php else: ?><span class="badge danger">Belum hadir</span><?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
