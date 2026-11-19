<?php $e = $employee ?? []; $editing = $employee !== null; ?>
<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<h1><?= $editing ? 'Ubah Karyawan' : 'Tambah Karyawan' ?></h1>
<form method="post" action="<?= site_url($editing ? 'admin/employees/' . $e['id'] : 'admin/employees') ?>" class="card form">
    <?= csrf_field() ?>
    <label>Nama <input name="name" value="<?= esc(old('name') ?? $e['name'] ?? '', 'attr') ?>" required maxlength="100"></label>
    <label>Email <input type="email" name="email" value="<?= esc(old('email') ?? $e['email'] ?? '', 'attr') ?>" required></label>
    <label>Jabatan <input name="position" value="<?= esc(old('position') ?? $e['position'] ?? '', 'attr') ?>" maxlength="100"></label>
    <?php $role = old('role') ?? $e['role'] ?? 'employee'; ?>
    <label>Peran
        <select name="role">
            <option value="employee" <?= $role === 'employee' ? 'selected' : '' ?>>Karyawan</option>
            <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>HR / Admin</option>
        </select>
    </label>
    <label>Password <input type="password" name="password" minlength="8" <?= $editing ? '' : 'required' ?> autocomplete="new-password">
        <?php if ($editing): ?><small class="muted">Kosongkan jika tidak ingin mengganti password.</small><?php endif ?>
    </label>
    <?php if ($editing): ?>
        <label class="check"><input type="checkbox" name="is_active" value="1" <?= (int) $e['is_active'] ? 'checked' : '' ?>> Akun aktif</label>
    <?php endif ?>
    <div class="actions">
        <button class="btn">Simpan</button>
        <a href="<?= site_url('admin/employees') ?>" class="btn btn-outline">Batal</a>
    </div>
</form>
<?= $this->endSection() ?>
