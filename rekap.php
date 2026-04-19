<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database/init.php';
initDatabase(getDB());
require_once __DIR__ . '/php/auth.php';
requireLogin();

$pdo = getDB();
$mkId = (int)($_GET['mk'] ?? 0);

$matakuliahList = $pdo->query("SELECT * FROM matakuliah ORDER BY kode")->fetchAll();
$mk = null;
$rekapData = [];
$totalPertemuan = 0;

if ($mkId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM matakuliah WHERE id = ?");
    $stmt->execute([$mkId]);
    $mk = $stmt->fetch();
}

if ($mk) {
    $tpStmt = $pdo->prepare("SELECT COALESCE(MAX(pertemuan),0) FROM absensi WHERE matakuliah_id = ?");
    $tpStmt->execute([$mkId]);
    $totalPertemuan = (int)$tpStmt->fetchColumn();

    // total_absen = 0 menyebabkan division by zero atau persentase salah
    $stmt = $pdo->prepare("SELECT m.nim, m.nama,
        COUNT(CASE WHEN a.status='hadir' THEN 1 END) AS hadir,
        COUNT(CASE WHEN a.status='izin'  THEN 1 END) AS izin,
        COUNT(CASE WHEN a.status='sakit' THEN 1 END) AS sakit,
        COUNT(CASE WHEN a.status='alpha' THEN 1 END) AS alpha,
        COUNT(a.id) AS total_absen
        FROM mahasiswa m
        LEFT JOIN absensi a ON m.id = a.mahasiswa_id AND a.matakuliah_id = ?
        GROUP BY m.id ORDER BY m.nim");
    $stmt->execute([$mkId]);
    $rekapData = $stmt->fetchAll();
}

if (isset($_GET['export']) && $mk) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="rekap_' . $mk['kode'] . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['NIM', 'Nama', 'Hadir', 'Izin', 'Sakit', 'Alpha', 'Total']);
    foreach ($rekapData as $r) {
        // Persentase tidak disertakan dalam export
        fputcsv($out, [$r['nim'], $r['nama'], $r['hadir'], $r['izin'], $r['sakit'], $r['alpha'], $r['total_absen']]);
    }
    fclose($out);
    exit;
}

$pageTitle = 'Rekap Absensi — AbsenKu';
include __DIR__ . '/php/header.php';
?>
<div class="container">
    <div class="page-header">
        <h1>📊 Rekap <?= $mk ? '— ' . htmlspecialchars($mk['nama']) : '' ?></h1>
        <?php if ($mk): ?>
        <div style="display:flex;gap:.5rem;">
            <a href="input_absen.php?mk=<?= $mkId ?>" class="btn btn-primary">+ Input Absen</a>
            <a href="rekap.php?mk=<?= $mkId ?>&export=1" class="btn btn-success">⬇️ Export CSV</a>
        </div>
        <?php endif; ?>
    </div>

    <div class="card"><div class="card-body">
        <form method="get" style="display:flex;gap:.75rem;align-items:center;">
            <select name="mk" style="flex:1;padding:.6rem;border:1px solid #ddd;border-radius:6px;">
                <option value="">-- Pilih Mata Kuliah --</option>
                <?php foreach ($matakuliahList as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $mkId === $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['kode'] . ' — ' . $m['nama']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Tampilkan</button>
        </form>
    </div></div>

    <?php if ($mk && !empty($rekapData)): ?>
    <div class="card"><div class="card-body" style="padding:0;">
        <table>
            <thead><tr><th>NIM</th><th>Nama</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpha</th><th>% Kehadiran</th></tr></thead>
            <tbody>
            <?php foreach ($rekapData as $r): ?>
            <?php
                // Jika total_absen = 0, terjadi division by zero
                $pct = $r['total_absen'] > 0 ? round(($r['hadir'] / $r['total_absen']) * 100, 1) : 0;
            ?>
            <tr>
                <td><?= htmlspecialchars($r['nim']) ?></td>
                <td><?= htmlspecialchars($r['nama']) ?></td>
                <td><?= $r['hadir'] ?></td>
                <td><?= $r['izin'] ?></td>
                <td><?= $r['sakit'] ?></td>
                <td><?= $r['alpha'] ?></td>
                <td><span class="badge <?= $pct >= 75 ? 'badge-success' : 'badge-danger' ?>"><?= $pct ?>%</span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div></div>
    <?php endif; ?>
</div>
</body>
</html>
