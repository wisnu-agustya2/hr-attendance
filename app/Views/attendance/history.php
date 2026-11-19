<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <h1>Riwayat Absensi · <?= esc($monthLabel) ?></h1>
    <form method="get" class="inline-form">
        <input type="month" name="month" value="<?= esc($month, 'attr') ?>">
        <button class="btn btn-outline">Tampilkan</button>
    </form>
</div>
<div class="card"><?= view('attendance/table', ['rows' => $rows]) ?></div>
<?= $this->endSection() ?>
