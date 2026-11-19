<?php use App\Models\LeaveRequestModel; ?>
<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <h1>Cuti & Izin</h1>
    <a href="<?= site_url('leaves/new') ?>" class="btn">+ Ajukan</a>
</div>
<p>Sisa cuti tahunan <?= $year ?>: <strong><?= $remaining ?> dari <?= $quota ?> hari</strong></p>

<div class="card">
    <table>
        <thead><tr><th>Jenis</th><th>Tanggal</th><th>Hari kerja</th><th>Alasan</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($leaves as $l): ?>
            <tr>
                <td><?= esc(LeaveRequestModel::TYPES[$l['type']] ?? $l['type']) ?></td>
                <td><?= tanggal($l['start_date'], false) ?> – <?= tanggal($l['end_date'], false) ?></td>
                <td><?= (int) $l['days'] ?></td>
                <td><?= esc($l['reason']) ?><?php if ($l['review_note']): ?><br><small class="muted">HR: <?= esc($l['review_note']) ?></small><?php endif ?></td>
                <td><?= status_badge($l['status']) ?></td>
                <td>
                    <?php if ($l['status'] === 'pending'): ?>
                        <form method="post" action="<?= site_url('leaves/' . $l['id'] . '/cancel') ?>" onsubmit="return confirm('Batalkan pengajuan ini?')">
                            <?= csrf_field() ?><button class="link danger">Batalkan</button>
                        </form>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
        <?php if ($leaves === []): ?>
            <tr><td colspan="6" class="muted">Belum ada pengajuan.</td></tr>
        <?php endif ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
