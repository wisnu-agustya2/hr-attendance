<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="card center">
    <h1>403 · Akses ditolak</h1>
    <p>Halaman ini hanya untuk HR / admin.</p>
    <p><a href="<?= site_url('/') ?>" class="btn">Kembali ke beranda</a></p>
</div>
<?= $this->endSection() ?>
