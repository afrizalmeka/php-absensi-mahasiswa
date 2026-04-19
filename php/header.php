<?php require_once __DIR__ . '/../php/auth.php'; ?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'AbsenKu') ?></title>
<link rel="stylesheet" href="<?= $cssPath ?? 'css/style.css' ?>">
</head><body>
<nav class="navbar">
    <a href="index.php" class="brand">📋 AbsenKu</a>
    <nav>
        <a href="index.php">Dashboard</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="input_absen.php">Input Absensi</a>
            <a href="rekap.php">Rekap</a>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="admin_mahasiswa.php">Kelola Mahasiswa</a>
                <a href="admin_matakuliah.php">Mata Kuliah</a>
            <?php endif; ?>
            <a href="logout.php">Keluar (<?= htmlspecialchars($_SESSION['user_name']) ?>)</a>
        <?php else: ?>
            <a href="login.php">Masuk</a>
        <?php endif; ?>
    </nav>
</nav>
