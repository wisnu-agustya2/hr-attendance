<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <h1>Rekap Absensi · <?= esc($monthLabel) ?></h1>
    <form method="get" class="inline-form">
        <input type="month" name="month" value="<?= esc($month, 'attr') ?>">
        <button class="btn btn-outline">Tampilkan</button>
        <a href="<?= site_url('admin/recap/export?month=' . $month) ?>" class="btn">Export CSV</a>
    </form>
</div>
<div class="card scroll">
    <table>
        <thead>
            <tr><th>Nama</th><th>Hari kerja</th><th>Hadir</th><th>Terlambat</th><th>Cuti</th><th>Sakit</th><th>Izin</th><th>Alpa</th><th>Jam kerja</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= esc($r['name']) ?></td>
                <td><?= $r['workdays'] ?></td>
                <td><?= $r['present'] ?></td>
                <td class="<?= $r['late'] > 0 ? 'warn' : '' ?>"><?= $r['late'] ?></td>
                <td><?= $r['annual'] ?></td>
                <td><?= $r['sick'] ?></td>
                <td><?= $r['permit'] ?></td>
                <td class="<?= $r['absent'] > 0 ? 'danger' : '' ?>"><?= $r['absent'] ?></td>
                <td><?= durasi((int) $r['worked_minutes']) ?></td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>
<p class="muted">Alpa = hari kerja yang sudah lewat tanpa absensi dan tanpa cuti/izin yang disetujui.</p>
<?= $this->endSection() ?>
