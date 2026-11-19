<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-head">
    <h1>Karyawan</h1>
    <a href="<?= site_url('admin/employees/new') ?>" class="btn">+ Tambah Karyawan</a>
</div>
<div class="card">
    <table>
        <thead><tr><th>Nama</th><th>Email</th><th>Jabatan</th><th>Peran</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($employees as $e): ?>
            <tr class="<?= $e['is_active'] ? '' : 'inactive' ?>">
                <td><?= esc($e['name']) ?></td>
                <td><?= esc($e['email']) ?></td>
                <td><?= esc($e['position'] ?? '-') ?></td>
                <td><?= $e['role'] === 'admin' ? 'HR / Admin' : 'Karyawan' ?></td>
                <td><?= $e['is_active'] ? '<span class="badge ok">Aktif</span>' : '<span class="badge">Nonaktif</span>' ?></td>
                <td><a href="<?= site_url('admin/employees/' . $e['id'] . '/edit') ?>">Ubah</a></td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
