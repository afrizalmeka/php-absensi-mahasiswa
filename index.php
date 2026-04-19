<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database/init.php';
initDatabase(getDB());
require_once __DIR__ . '/php/auth.php';
requireLogin();

$pdo = getDB();
$today = date('Y-m-d');

$totalMhs = $pdo->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn();
$totalMk  = $pdo->query("SELECT COUNT(*) FROM matakuliah")->fetchColumn();
// BUG 2: Query hadir hari ini tidak difilter per tanggal — menghitung semua absensi 'hadir' dari semua waktu
$hadirCount = $pdo->query("SELECT COUNT(*) FROM absensi WHERE status = 'hadir'")->fetchColumn();

$matakuliahList = $pdo->query("SELECT mk.*, u.name AS dosen_name FROM matakuliah mk JOIN users u ON mk.dosen_id = u.id ORDER BY mk.kode")->fetchAll();

$pageTitle = 'Dashboard — AbsenKu';
include __DIR__ . '/php/header.php';
?>
<div class="container">
    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-<?= $_SESSION['flash']['type'] ?>"><?= htmlspecialchars($_SESSION['flash']['msg']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="page-header"><h1>📋 Dashboard Absensi</h1></div>

    <div class="stats-row">
        <div class="stat-card"><div class="stat-label">Total Mahasiswa</div><div class="stat-value"><?= $totalMhs ?></div></div>
        <div class="stat-card"><div class="stat-label">Mata Kuliah</div><div class="stat-value"><?= $totalMk ?></div></div>
        <div class="stat-card"><div class="stat-label">Hadir Hari Ini</div><div class="stat-value"><?= $hadirCount ?></div></div>
        <div class="stat-card"><div class="stat-label">Tanggal</div><div class="stat-value" style="font-size:1rem;"><?= date('d/m/Y') ?></div></div>
    </div>

    <div class="card"><div class="card-body" style="padding:0;">
        <table>
            <thead><tr><th>Kode</th><th>Nama</th><th>SKS</th><th>Dosen</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($matakuliahList as $mk): ?>
            <tr>
                <td><?= htmlspecialchars($mk['kode']) ?></td>
                <td><?= htmlspecialchars($mk['nama']) ?></td>
                <td><?= $mk['sks'] ?></td>
                <td><?= htmlspecialchars($mk['dosen_name']) ?></td>
                <td style="display:flex;gap:.4rem;">
                    <a href="input_absen.php?mk=<?= $mk['id'] ?>" class="btn btn-primary btn-sm">Input Absen</a>
                    <a href="rekap.php?mk=<?= $mk['id'] ?>" class="btn btn-info btn-sm">Rekap</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div></div>
</div>
</body>
</html>
