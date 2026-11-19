<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<h1>Halo, <?= esc($user['name']) ?> 👋</h1>
<p class="muted"><?= tanggal($now->toDateString()) ?> · Jam masuk paling lambat <?= esc($deadline) ?></p>

<section class="card clock">
    <div>
        <span class="muted">Status hari ini</span>
        <?php if ($today === null): ?>
            <strong><?= $isWorkday ? 'Belum check-in' : 'Hari libur' ?></strong>
        <?php else: ?>
            <strong>Masuk <?= jam($today['check_in']) ?> · Pulang <?= jam($today['check_out']) ?></strong>
            <?= status_badge($today['status']) ?>
        <?php endif ?>
    </div>
    <div class="actions">
        <?php if ($today === null): ?>
            <form method="post" action="<?= site_url('attendance/check-in') ?>">
                <?= csrf_field() ?>
                <input name="note" placeholder="Catatan (opsional), mis. WFH" maxlength="255">
                <button class="btn">Check-in</button>
            </form>
        <?php elseif ($today['check_out'] === null): ?>
            <form method="post" action="<?= site_url('attendance/check-out') ?>">
                <?= csrf_field() ?>
                <button class="btn btn-outline">Check-out</button>
            </form>
        <?php else: ?>
            <span class="muted">Absensi hari ini lengkap ✔</span>
        <?php endif ?>
    </div>
</section>

<h2>Ringkasan <?= esc($monthLabel) ?></h2>
<div class="stats">
    <div class="card stat"><span>Hadir</span><strong><?= $recap['present'] ?></strong></div>
    <div class="card stat"><span>Terlambat</span><strong><?= $recap['late'] ?></strong></div>
    <div class="card stat"><span>Cuti/Sakit/Izin</span><strong><?= $recap['annual'] + $recap['sick'] + $recap['permit'] ?></strong></div>
    <div class="card stat"><span>Alpa</span><strong><?= $recap['absent'] ?></strong></div>
    <div class="card stat"><span>Jam kerja</span><strong><?= durasi((int) $recap['worked_minutes']) ?></strong></div>
    <div class="card stat"><span>Sisa cuti tahunan</span><strong><?= $leaveLeft ?>/<?= $leaveQuota ?></strong></div>
</div>

<h2>Absensi Terakhir</h2>
<div class="card">
    <?= view('attendance/table', ['rows' => $recent]) ?>
    <p><a href="<?= site_url('attendance/history') ?>">Lihat semua riwayat →</a></p>
</div>
<?= $this->endSection() ?>
