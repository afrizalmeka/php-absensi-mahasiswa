<?php
function initDatabase(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'dosen',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS matakuliah (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        kode TEXT UNIQUE NOT NULL,
        nama TEXT NOT NULL,
        sks INTEGER NOT NULL DEFAULT 2,
        dosen_id INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (dosen_id) REFERENCES users(id)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS mahasiswa (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nim TEXT UNIQUE NOT NULL,
        nama TEXT NOT NULL,
        prodi TEXT,
        angkatan INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS absensi (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        matakuliah_id INTEGER NOT NULL,
        mahasiswa_id INTEGER NOT NULL,
        tanggal DATE NOT NULL,
        pertemuan INTEGER NOT NULL,
        status TEXT NOT NULL DEFAULT 'hadir',
        keterangan TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (matakuliah_id) REFERENCES matakuliah(id),
        FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id)
    )");

    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $pass = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Admin Sistem', 'admin@absenku.com', '$pass', 'admin')");
        $dosen = password_hash('dosen123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Dr. Ahmad Fauzi', 'ahmad@kampus.ac.id', '$dosen', 'dosen')");
        $pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Ir. Siti Rahayu', 'siti@kampus.ac.id', '$dosen', 'dosen')");

        $pdo->exec("INSERT INTO matakuliah (kode, nama, sks, dosen_id) VALUES ('IF101', 'Pemrograman Web', 3, 2)");
        $pdo->exec("INSERT INTO matakuliah (kode, nama, sks, dosen_id) VALUES ('IF102', 'Basis Data', 3, 3)");

        $mahasiswa = [
            ['21001', 'Andi Wijaya',    'Informatika', 2021],
            ['21002', 'Budi Santoso',   'Informatika', 2021],
            ['21003', 'Citra Dewi',     'Informatika', 2021],
            ['21004', 'Doni Prasetyo',  'Informatika', 2021],
            ['21005', 'Eka Putri',      'Informatika', 2021],
        ];
        $stmt = $pdo->prepare("INSERT INTO mahasiswa (nim, nama, prodi, angkatan) VALUES (?,?,?,?)");
        foreach ($mahasiswa as $m) $stmt->execute($m);

        // Seed beberapa record absensi
        $today = date('Y-m-d');
        $seed = [
            [1, 1, $today, 1, 'hadir'],
            [1, 2, $today, 1, 'hadir'],
            [1, 3, $today, 1, 'izin'],
            [1, 4, $today, 1, 'hadir'],
            [1, 5, $today, 1, 'alpha'],
        ];
        $stmt = $pdo->prepare("INSERT INTO absensi (matakuliah_id, mahasiswa_id, tanggal, pertemuan, status) VALUES (?,?,?,?,?)");
        foreach ($seed as $s) $stmt->execute($s);
    }
}
