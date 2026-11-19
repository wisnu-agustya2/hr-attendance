<table>
    <thead><tr><th>Tanggal</th><th>Masuk</th><th>Pulang</th><th>Durasi</th><th>Status</th><th>Catatan</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= tanggal($r['work_date']) ?></td>
            <td><?= jam($r['check_in']) ?></td>
            <td><?= jam($r['check_out']) ?></td>
            <td><?= $r['check_out'] ? durasi((int) $r['worked_minutes']) : '-' ?></td>
            <td><?= status_badge($r['status']) ?></td>
            <td><?= esc($r['note'] ?? '') ?></td>
        </tr>
    <?php endforeach ?>
    <?php if ($rows === []): ?>
        <tr><td colspan="6" class="muted">Belum ada data absensi.</td></tr>
    <?php endif ?>
    </tbody>
</table>
