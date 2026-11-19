<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | HR Attendance</title>
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
</head>
<body class="login-page">
<form method="post" action="<?= site_url('login') ?>" class="card login-box">
    <?= csrf_field() ?>
    <h1>🕘 HR Attendance</h1>
    <p class="muted">Masuk untuk mencatat kehadiran.</p>
    <?= view('partials/flash') ?>
    <label>Email <input type="email" name="email" value="<?= esc(old('email') ?? '') ?>" required autofocus></label>
    <label>Password <input type="password" name="password" required></label>
    <button class="btn">Masuk</button>
</form>
</body>
</html>
