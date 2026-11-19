<?php $user = session('user'); $isAdmin = ($user['role'] ?? '') === 'admin'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Absensi') ?> | HR Attendance</title>
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
</head>
<body>
<header class="topbar">
    <a href="<?= site_url('/') ?>" class="brand">🕘 HR Attendance</a>
    <nav>
        <a href="<?= site_url('/') ?>">Beranda</a>
        <a href="<?= site_url('attendance/history') ?>">Riwayat</a>
        <a href="<?= site_url('leaves') ?>">Cuti</a>
        <?php if ($isAdmin): ?>
            <span class="sep"></span>
            <a href="<?= site_url('admin') ?>">Panel HR</a>
            <a href="<?= site_url('admin/employees') ?>">Karyawan</a>
            <a href="<?= site_url('admin/leaves') ?>">Persetujuan</a>
            <a href="<?= site_url('admin/recap') ?>">Rekap</a>
        <?php endif ?>
    </nav>
    <form method="post" action="<?= site_url('logout') ?>" class="user">
        <?= csrf_field() ?>
        <span><?= esc($user['name'] ?? '') ?></span>
        <button class="link">Keluar</button>
    </form>
</header>

<main class="container">
    <?= view('partials/flash') ?>
    <?= $this->renderSection('content') ?>
</main>
</body>
</html>
