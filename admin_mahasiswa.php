<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database/init.php';
initDatabase(getDB());
require_once __DIR__ . '/php/auth.php';
requireAdmin();

$pdo = getDB();
$msg = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if ($act === 'add') {
        $nim    = trim($_POST['nim'] ?? '');
        $nama   = trim($_POST['nama'] ?? '');
        $prodi  = trim($_POST['prodi'] ?? '');
        $angkat = (int)($_POST['angkatan'] ?? 0);
        if ($nim === '' || $nama === '') { $error = 'NIM dan nama wajib diisi.'; }
        elseif (strlen($nim) < 3) { $error = 'NIM minimal 3 karakter.'; }
        else {
            try {
                $pdo->prepare("INSERT INTO mahasiswa (nim, nama, prodi, angkatan) VALUES (?,?,?,?)")->execute([$nim, $nama, $prodi, $angkat ?: null]);
                $msg = 'Mahasiswa berhasil ditambahkan.';
            } catch (Exception $e) { $error = 'NIM sudah terdaftar.'; }
        }
    } elseif ($act === 'edit') {
        $id    = (int)($_POST['id'] ?? 0);
        $nim   = trim($_POST['nim'] ?? '');
        $nama  = trim($_POST['nama'] ?? '');
        $prodi = trim($_POST['prodi'] ?? '');
        $angkat = (int)($_POST['angkatan'] ?? 0);
        if ($nim === '' || $nama === '') { $error = 'NIM dan nama wajib diisi.'; }
        else {
            $pdo->prepare("UPDATE mahasiswa SET nim=?, nama=?, prodi=?, angkatan=? WHERE id=?")->execute([$nim, $nama, $prodi, $angkat ?: null, $id]);
            $msg = 'Data mahasiswa diperbarui.';
        }
    } elseif ($act === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM absensi WHERE mahasiswa_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM mahasiswa WHERE id = ?")->execute([$id]);
        $msg = 'Mahasiswa berhasil dihapus.';
    }
}

$mahasiswaList = $pdo->query("SELECT * FROM mahasiswa ORDER BY nim")->fetchAll();
$editMhs = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editMhs = $stmt->fetch();
}

$pageTitle = 'Kelola Mahasiswa — AbsenKu';
include __DIR__ . '/php/header.php';
?>
<div class="container">
    <div class="page-header"><h1>Kelola Mahasiswa</h1></div>
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card">
        <div class="card-header"><?= $editMhs ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' ?></div>
        <div class="card-body">
            <form method="post" style="display:grid;grid-template-columns:1fr 2fr 1fr 1fr auto;gap:.6rem;align-items:end;">
                <input type="hidden" name="action" value="<?= $editMhs ? 'edit' : 'add' ?>">
                <?php if ($editMhs): ?><input type="hidden" name="id" value="<?= $editMhs['id'] ?>"><?php endif; ?>
                <div class="form-group" style="margin:0;"><label>NIM</label><input type="text" name="nim" value="<?= htmlspecialchars($editMhs['nim'] ?? '') ?>" required></div>
                <div class="form-group" style="margin:0;"><label>Nama</label><input type="text" name="nama" value="<?= htmlspecialchars($editMhs['nama'] ?? '') ?>" required></div>
                <div class="form-group" style="margin:0;"><label>Prodi</label><input type="text" name="prodi" value="<?= htmlspecialchars($editMhs['prodi'] ?? '') ?>"></div>
                <div class="form-group" style="margin:0;"><label>Angkatan</label><input type="number" name="angkatan" value="<?= $editMhs['angkatan'] ?? '' ?>" min="2000" max="2099"></div>
                <button type="submit" class="btn btn-<?= $editMhs ? 'primary' : 'success' ?>"><?= $editMhs ? 'Update' : 'Tambah' ?></button>
            </form>
        </div>
    </div>

    <div class="card"><div class="card-body" style="padding:0;">
        <table>
            <thead><tr><th>NIM</th><th>Nama</th><th>Prodi</th><th>Angkatan</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($mahasiswaList as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['nim']) ?></td>
                <td><?= htmlspecialchars($m['nama']) ?></td>
                <td><?= htmlspecialchars($m['prodi'] ?? '-') ?></td>
                <td><?= $m['angkatan'] ?? '-' ?></td>
                <td style="display:flex;gap:.4rem;">
                    <a href="admin_mahasiswa.php?edit=<?= $m['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                    <form method="post" onsubmit="return confirm('Hapus mahasiswa ini beserta data absensinya?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div></div>
</div>
</body>
</html>
