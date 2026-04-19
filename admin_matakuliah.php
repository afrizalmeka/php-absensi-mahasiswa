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
        $kode = trim($_POST['kode'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $sks  = (int)($_POST['sks'] ?? 2);
        $dosenId = (int)($_POST['dosen_id'] ?? 0);
        if ($kode === '' || $nama === '') { $error = 'Kode dan nama wajib diisi.'; }
        else {
            try {
                $pdo->prepare("INSERT INTO matakuliah (kode, nama, sks, dosen_id) VALUES (?,?,?,?)")->execute([$kode, $nama, $sks, $dosenId ?: null]);
                $msg = 'Mata kuliah berhasil ditambahkan.';
            } catch (Exception $e) { $error = 'Kode mata kuliah sudah ada.'; }
        }
    } elseif ($act === 'edit') {
        $id   = (int)($_POST['id'] ?? 0);
        $kode = trim($_POST['kode'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $sks  = (int)($_POST['sks'] ?? 2);
        $dosenId = (int)($_POST['dosen_id'] ?? 0);
        if ($kode === '' || $nama === '') { $error = 'Kode dan nama wajib diisi.'; }
        else {
            $pdo->prepare("UPDATE matakuliah SET kode=?,nama=?,sks=?,dosen_id=? WHERE id=?")->execute([$kode, $nama, $sks, $dosenId ?: null, $id]);
            $msg = 'Mata kuliah diperbarui.';
        }
    } elseif ($act === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM absensi WHERE matakuliah_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM matakuliah WHERE id = ?")->execute([$id]);
        $msg = 'Mata kuliah berhasil dihapus.';
    }
}

$mkList = $pdo->query("SELECT mk.*, u.name AS dosen_name FROM matakuliah mk LEFT JOIN users u ON mk.dosen_id = u.id ORDER BY mk.kode")->fetchAll();
$dosenList = $pdo->query("SELECT * FROM users WHERE role IN ('dosen','admin') ORDER BY name")->fetchAll();
$editMk = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM matakuliah WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editMk = $stmt->fetch();
}

$pageTitle = 'Kelola Mata Kuliah — AbsenKu';
include __DIR__ . '/php/header.php';
?>
<div class="container">
    <div class="page-header"><h1>Kelola Mata Kuliah</h1></div>
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" style="display:grid;grid-template-columns:1fr 2fr 80px 2fr auto;gap:.6rem;align-items:end;">
                <input type="hidden" name="action" value="<?= $editMk ? 'edit' : 'add' ?>">
                <?php if ($editMk): ?><input type="hidden" name="id" value="<?= $editMk['id'] ?>"><?php endif; ?>
                <div class="form-group" style="margin:0;"><label>Kode MK</label><input type="text" name="kode" value="<?= htmlspecialchars($editMk['kode'] ?? '') ?>" required></div>
                <div class="form-group" style="margin:0;"><label>Nama</label><input type="text" name="nama" value="<?= htmlspecialchars($editMk['nama'] ?? '') ?>" required></div>
                <div class="form-group" style="margin:0;"><label>SKS</label><input type="number" name="sks" value="<?= $editMk['sks'] ?? 2 ?>" min="1" max="6"></div>
                <div class="form-group" style="margin:0;"><label>Dosen</label>
                    <select name="dosen_id">
                        <option value="">-- Pilih Dosen --</option>
                        <?php foreach ($dosenList as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= ($editMk['dosen_id'] ?? 0) == $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-<?= $editMk ? 'primary' : 'success' ?>"><?= $editMk ? 'Update' : 'Tambah' ?></button>
            </form>
        </div>
    </div>

    <div class="card"><div class="card-body" style="padding:0;">
        <table>
            <thead><tr><th>Kode</th><th>Nama</th><th>SKS</th><th>Dosen</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($mkList as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['kode']) ?></td>
                <td><?= htmlspecialchars($m['nama']) ?></td>
                <td><?= $m['sks'] ?></td>
                <td><?= htmlspecialchars($m['dosen_name'] ?? '-') ?></td>
                <td style="display:flex;gap:.4rem;">
                    <a href="admin_matakuliah.php?edit=<?= $m['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                    <form method="post" onsubmit="return confirm('Hapus mata kuliah ini?')">
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
