<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<h1>Ajukan Cuti / Izin</h1>
<form method="post" action="<?= site_url('leaves') ?>" class="card form">
    <?= csrf_field() ?>
    <label>Jenis
        <select name="type" required>
            <?php foreach ($types as $value => $label): ?>
                <option value="<?= esc($value, 'attr') ?>" <?= old('type') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach ?>
        </select>
    </label>
    <div class="row">
        <label>Tanggal mulai <input type="date" name="start_date" value="<?= esc(old('start_date') ?? '', 'attr') ?>" required></label>
        <label>Tanggal selesai <input type="date" name="end_date" value="<?= esc(old('end_date') ?? '', 'attr') ?>" required></label>
    </div>
    <label>Alasan <textarea name="reason" rows="4" maxlength="500" required><?= esc(old('reason') ?? '') ?></textarea></label>
    <p class="muted">Hanya hari kerja yang dihitung. Cuti tahunan memotong kuota setelah disetujui HR.</p>
    <div class="actions">
        <button class="btn">Kirim Pengajuan</button>
        <a href="<?= site_url('leaves') ?>" class="btn btn-outline">Batal</a>
    </div>
</form>
<?= $this->endSection() ?>
