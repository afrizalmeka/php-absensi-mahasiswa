<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database/init.php';
initDatabase(getDB());
require_once __DIR__ . '/php/auth.php';
requireLogin();

$pdo = getDB();
$mkId = (int)($_GET['mk'] ?? 0);
$error = '';

$stmt = $pdo->prepare("SELECT mk.*, u.name AS dosen_name FROM matakuliah mk JOIN users u ON mk.dosen_id = u.id WHERE mk.id = ?");
$stmt->execute([$mkId]);
$mk = $stmt->fetch();
if (!$mk) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal   = trim($_POST['tanggal'] ?? '');
    $pertemuan = (int)($_POST['pertemuan'] ?? 0);
    $statusList = $_POST['status'] ?? [];

    // dan tidak ada cek duplikasi pertemuan — pertemuan yang sama bisa diinput ulang
    if ($tanggal === '') {
        $error = 'Tanggal wajib diisi.';
    } else {
        $insert = $pdo->prepare("INSERT INTO absensi (matakuliah_id, mahasiswa_id, tanggal, pertemuan, status, keterangan) VALUES (?,?,?,?,?,?)");
        $allowed = ['hadir', 'izin', 'sakit', 'alpha'];
        foreach ($statusList as $mhsId => $status) {
            $mhsId = (int)$mhsId;
            if (!in_array($status, $allowed)) $status = 'alpha';
            $ket = trim($_POST['keterangan'][$mhsId] ?? '');
            $insert->execute([$mkId, $mhsId, $tanggal, $pertemuan, $status, $ket ?: null]);
        }
        $_SESSION['flash'] = ['type'=>'success', 'msg'=>"Absensi pertemuan ke-$pertemuan disimpan."];
        header('Location: index.php');
        exit;
    }
}

$mahasiswaList = $pdo->query("SELECT * FROM mahasiswa ORDER BY nim")->fetchAll();
$lastPertemuan = $pdo->prepare("SELECT COALESCE(MAX(pertemuan), 0) FROM absensi WHERE matakuliah_id = ?");
$lastPertemuan->execute([$mkId]);
$nextPertemuan = (int)$lastPertemuan->fetchColumn() + 1;

$pageTitle = 'Input Absensi — AbsenKu';
include __DIR__ . '/php/header.php';
?>
<div class="container">
    <div class="page-header">
        <h1>Input Absensi: <?= htmlspecialchars($mk['nama']) ?></h1>
        <a href="index.php" class="btn btn-secondary">← Kembali</a>
    </div>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="post">
        <div class="card">
            <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group" style="margin:0;"><label>Tanggal</label>
                    <input type="date" name="tanggal" value="<?= htmlspecialchars($_POST['tanggal'] ?? date('Y-m-d')) ?>" required></div>
                <div class="form-group" style="margin:0;"><label>Pertemuan ke-</label>
                    <input type="number" name="pertemuan" value="<?= $_POST['pertemuan'] ?? $nextPertemuan ?>" min="1" max="16" required></div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Daftar Mahasiswa</div>
            <div class="card-body" style="padding:0;">
                <table>
                    <thead><tr><th>NIM</th><th>Nama</th><th>Status</th><th>Keterangan</th></tr></thead>
                    <tbody>
                    <?php foreach ($mahasiswaList as $mhs): ?>
                    <tr>
                        <td><?= htmlspecialchars($mhs['nim']) ?></td>
                        <td><?= htmlspecialchars($mhs['nama']) ?></td>
                        <td>
                            <select name="status[<?= $mhs['id'] ?>]" style="padding:.3rem;border:1px solid #ddd;border-radius:4px;">
                                <?php foreach (['hadir'=>'Hadir','izin'=>'Izin','sakit'=>'Sakit','alpha'=>'Alpha'] as $v => $l): ?>
                                <option value="<?= $v ?>"><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="text" name="keterangan[<?= $mhs['id'] ?>]" style="padding:.3rem;border:1px solid #ddd;border-radius:4px;width:100%;"></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <button type="submit" class="btn btn-success">Simpan Absensi</button>
    </form>
</div>
</body>
</html>
