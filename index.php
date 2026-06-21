<?php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$page = $_GET['page'] ?? 'dashboard';

//  POST ACTION HANDLERS

// --- LOGIN ---
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        flash('error', 'Username dan password wajib diisi.');
        redirect('?page=login');
    }

    $pdo = db();

    // Check admin
    $stmt = $pdo->prepare('SELECT * FROM admin WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['user_id']   = $admin['id_admin'];
        $_SESSION['role']      = 'admin';
        $_SESSION['user_name'] = $admin['nama_admin'];
        clearOld();
        redirect('?page=dashboard');
    }

    // Check anggota
    $stmt = $pdo->prepare('SELECT * FROM anggota WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $anggota = $stmt->fetch();
    if ($anggota && password_verify($password, $anggota['password'])) {
        $_SESSION['user_id']   = $anggota['id_anggota'];
        $_SESSION['role']      = 'anggota';
        $_SESSION['user_name'] = $anggota['nama'];
        clearOld();
        redirect('?page=dashboard');
    }

    storeOld(['username' => $username]);
    flash('error', 'Username atau password salah!');
    redirect('?page=login');
}

// --- REGISTER ---
if ($page === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nama'     => trim($_POST['nama'] ?? ''),
        'kelas'    => trim($_POST['kelas'] ?? ''),
        'alamat'   => trim($_POST['alamat'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'password' => $_POST['password'] ?? '',
    ];

    $errors = [];
    if (!$data['nama'])     $errors[] = 'Nama wajib diisi.';
    if (!$data['kelas'])    $errors[] = 'Kelas wajib diisi.';
    if (!$data['alamat'])   $errors[] = 'Alamat wajib diisi.';
    if (!$data['username']) $errors[] = 'Username wajib diisi.';
    if (strlen($data['password']) < 6) $errors[] = 'Password minimal 6 karakter.';

    if (!$errors) {
        $pdo = db();
        // Check uniqueness
        $s = $pdo->prepare('SELECT id_anggota FROM anggota WHERE username = ? LIMIT 1');
        $s->execute([$data['username']]);
        if ($s->fetch()) $errors[] = 'Username sudah digunakan.';

        $s = $pdo->prepare('SELECT id_admin FROM admin WHERE username = ? LIMIT 1');
        $s->execute([$data['username']]);
        if ($s->fetch()) $errors[] = 'Username sudah digunakan.';
    }

    if ($errors) {
        storeOld($data);
        flash('errors', implode('||', $errors));
        redirect('?page=register');
    }

    $pdo = db();
    $stmt = $pdo->prepare('INSERT INTO anggota (nama, kelas, alamat, username, password, tanggal_daftar) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$data['nama'], $data['kelas'], $data['alamat'], $data['username'], password_hash($data['password'], PASSWORD_BCRYPT), date('Y-m-d')]);

    clearOld();
    flash('success', 'Registrasi berhasil! Silakan login.');
    redirect('?page=login');
}

// --- LOGOUT ---
if ($page === 'logout') {
    session_destroy();
    header('Location: ?page=login');
    exit;
}

//  BUKU ACTIONS

// Pinjam buku (POST) — siswa mengajukan peminjaman, status = menunggu
if ($page === 'buku_pinjam' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    if (!isAnggota()) redirect('?page=login');

    $id           = (int)($_POST['id_buku'] ?? 0);
    $tgl_pinjam   = $_POST['tanggal_pinjam'] ?? '';
    $tgl_kembali  = $_POST['tanggal_kembali'] ?? '';
    $catatan      = trim($_POST['catatan'] ?? '');
    $userId       = $_SESSION['user_id'];
    $pdo          = db();

    // Validasi
    if (!$tgl_pinjam || !$tgl_kembali) { flash('error', 'Tanggal pinjam dan kembali wajib diisi.'); redirect('?page=buku'); }
    $diff = (strtotime($tgl_kembali) - strtotime($tgl_pinjam)) / 86400;
    if ($diff > 14 || $diff < 0) { flash('error', 'Maksimal peminjaman 14 hari.'); redirect('?page=buku'); }

    $buku = $pdo->prepare('SELECT * FROM buku WHERE id_buku = ? LIMIT 1');
    $buku->execute([$id]);
    $b = $buku->fetch();
    if (!$b || $b['stok'] <= 0) { flash('error', 'Stok buku habis.'); redirect('?page=buku'); }

    // Cek apakah sudah ada pengajuan/peminjaman aktif untuk buku ini
    $chk = $pdo->prepare('SELECT id_peminjaman FROM peminjaman WHERE id_anggota=? AND id_buku=? AND status IN ("menunggu","dipinjam") LIMIT 1');
    $chk->execute([$userId, $id]);
    if ($chk->fetch()) { flash('error', 'Anda sudah mengajukan atau meminjam buku ini.'); redirect('?page=buku'); }

    $cnt = $pdo->prepare('SELECT COUNT(*) FROM peminjaman WHERE id_anggota=? AND status IN ("menunggu","dipinjam")');
    $cnt->execute([$userId]);
    if ($cnt->fetchColumn() >= 3) { flash('error', 'Batas maksimal 3 buku dipinjam.'); redirect('?page=buku'); }

    // Status = menunggu, stok dikurangi langsung (reservasi)
    $pdo->prepare('INSERT INTO peminjaman (id_anggota,id_buku,tanggal_pinjam,tanggal_kembali,status,catatan) VALUES(?,?,?,?,?,?)')
        ->execute([$userId, $id, $tgl_pinjam, $tgl_kembali, 'menunggu', $catatan]);
    
    // Kurangi stok buku
    $pdo->prepare('UPDATE buku SET stok = stok - 1 WHERE id_buku = ?')->execute([$id]);

    flash('success', 'Pengajuan peminjaman berhasil dan buku telah dipesan! Menunggu persetujuan admin.');
    redirect('?page=peminjaman');
}

// Store (POST)
if ($page === 'buku_store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $pdo = db();
    $data = [
        'judul_buku'   => trim($_POST['judul_buku'] ?? ''),
        'kategori'     => trim($_POST['kategori'] ?? ''),
        'pengarang'    => trim($_POST['pengarang'] ?? ''),
        'penerbit'     => trim($_POST['penerbit'] ?? ''),
        'tahun_terbit' => (int)($_POST['tahun_terbit'] ?? 0),
        'stok'         => (int)($_POST['stok'] ?? 0),
        'gambar'       => null,
    ];
    if (!$data['judul_buku'] || !$data['pengarang'] || !$data['penerbit']) {
        flash('error', 'Judul, pengarang, dan penerbit wajib diisi.');
        redirect('?page=buku_create');
    }
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/books/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];
        if (!in_array($ext, $allowed)) { flash('error', 'Format gambar tidak valid.'); redirect('?page=buku_create'); }
        $filename = 'books/' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadDir . basename($filename));
        $data['gambar'] = $filename;
    }
    $pdo->prepare('INSERT INTO buku (judul_buku,kategori,pengarang,penerbit,tahun_terbit,stok,gambar) VALUES(?,?,?,?,?,?,?)')
        ->execute(array_values($data));
    flash('success', 'Buku berhasil ditambahkan!');
    redirect('?page=buku');
}

// Update (POST)
if ($page === 'buku_update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $id  = (int)($_POST['id_buku'] ?? 0);
    $pdo = db();
    $b   = $pdo->prepare('SELECT * FROM buku WHERE id_buku=? LIMIT 1');
    $b->execute([$id]);
    $buku = $b->fetch();
    if (!$buku) { flash('error', 'Buku tidak ditemukan.'); redirect('?page=admin_buku'); }

    $data = [
        'judul_buku'   => trim($_POST['judul_buku'] ?? ''),
        'kategori'     => trim($_POST['kategori'] ?? ''),
        'pengarang'    => trim($_POST['pengarang'] ?? ''),
        'penerbit'     => trim($_POST['penerbit'] ?? ''),
        'tahun_terbit' => (int)($_POST['tahun_terbit'] ?? 0),
        'stok'         => (int)($_POST['stok'] ?? 0),
        'gambar'       => $buku['gambar'],
    ];
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/books/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];
        if (in_array($ext, $allowed)) {
            // Delete old
            if ($buku['gambar'] && file_exists($uploadDir . basename($buku['gambar']))) {
                unlink($uploadDir . basename($buku['gambar']));
            }
            $filename = 'books/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadDir . basename($filename));
            $data['gambar'] = $filename;
        }
    }
    $pdo->prepare('UPDATE buku SET judul_buku=?,kategori=?,pengarang=?,penerbit=?,tahun_terbit=?,stok=?,gambar=? WHERE id_buku=?')
        ->execute([...(array_values($data)), $id]);
    flash('success', 'Buku berhasil diperbarui!');
    redirect('?page=admin_buku');
}

// Delete (POST)
if ($page === 'buku_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $id  = (int)($_POST['id_buku'] ?? 0);
    $pdo = db();
    $chk = $pdo->prepare('SELECT id_peminjaman FROM peminjaman WHERE id_buku=? AND status="dipinjam" LIMIT 1');
    $chk->execute([$id]);
    if ($chk->fetch()) { flash('error', 'Buku sedang dipinjam, tidak bisa dihapus.'); redirect('?page=admin_buku'); }
    $b = $pdo->prepare('SELECT gambar FROM buku WHERE id_buku=? LIMIT 1'); $b->execute([$id]);
    $buku = $b->fetch();
    if ($buku && $buku['gambar']) { @unlink(__DIR__ . '/uploads/' . $buku['gambar']); }
    $pdo->prepare('DELETE FROM buku WHERE id_buku=?')->execute([$id]);
    flash('success', 'Buku berhasil dihapus!');
    redirect('?page=admin_buku');
}

//  ANGGOTA ACTIONS

if ($page === 'anggota_store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $pdo  = db();
    $data = [
        'nama'     => trim($_POST['nama'] ?? ''),
        'kelas'    => trim($_POST['kelas'] ?? ''),
        'alamat'   => trim($_POST['alamat'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'password' => $_POST['password'] ?? '',
    ];
    $errors = [];
    foreach (['nama','kelas','alamat','username'] as $f) if (!$data[$f]) $errors[] = ucfirst($f) . ' wajib diisi.';
    if (strlen($data['password']) < 6) $errors[] = 'Password minimal 6 karakter.';
    $s = $pdo->prepare('SELECT id_anggota FROM anggota WHERE username=? LIMIT 1'); $s->execute([$data['username']]);
    if ($s->fetch()) $errors[] = 'Username sudah digunakan.';
    if ($errors) { flash('error', implode(' ', $errors)); redirect('?page=anggota_create'); }
    $pdo->prepare('INSERT INTO anggota (nama,kelas,alamat,username,password,tanggal_daftar) VALUES(?,?,?,?,?,?)')
        ->execute([$data['nama'], $data['kelas'], $data['alamat'], $data['username'], password_hash($data['password'], PASSWORD_BCRYPT), date('Y-m-d')]);
    flash('success', 'Anggota berhasil ditambahkan.');
    redirect('?page=anggota');
}

if ($page === 'anggota_update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $id  = (int)($_POST['id_anggota'] ?? 0);
    $pdo = db();
    $s   = $pdo->prepare('SELECT * FROM anggota WHERE id_anggota=? LIMIT 1'); $s->execute([$id]);
    $ang = $s->fetch();
    if (!$ang) { flash('error', 'Anggota tidak ditemukan.'); redirect('?page=anggota'); }

    $updateData = [
        'nama'     => trim($_POST['nama'] ?? ''),
        'kelas'    => trim($_POST['kelas'] ?? ''),
        'alamat'   => trim($_POST['alamat'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
    ];
    $password = $_POST['password'] ?? '';
    if ($password) {
        if (strlen($password) < 6) { flash('error', 'Password minimal 6 karakter.'); redirect('?page=anggota_edit&id=' . $id); }
        $updateData['password'] = password_hash($password, PASSWORD_BCRYPT);
        $pdo->prepare('UPDATE anggota SET nama=?,kelas=?,alamat=?,username=?,password=? WHERE id_anggota=?')
            ->execute(array_merge(array_values($updateData), [$id]));
    } else {
        $pdo->prepare('UPDATE anggota SET nama=?,kelas=?,alamat=?,username=? WHERE id_anggota=?')
            ->execute([...(array_values($updateData)), $id]);
    }
    flash('success', 'Data anggota berhasil diperbarui.');
    redirect('?page=anggota');
}

if ($page === 'anggota_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $id  = (int)($_POST['id_anggota'] ?? 0);
    $pdo = db();
    $chk = $pdo->prepare('SELECT id_peminjaman FROM peminjaman WHERE id_anggota=? AND status="dipinjam" LIMIT 1'); $chk->execute([$id]);
    if ($chk->fetch()) { flash('error', 'Anggota masih memiliki buku yang dipinjam.'); redirect('?page=anggota'); }
    $pdo->prepare('DELETE FROM peminjaman WHERE id_anggota=?')->execute([$id]);
    $pdo->prepare('DELETE FROM anggota WHERE id_anggota=?')->execute([$id]);
    flash('success', 'Anggota berhasil dihapus.');
    redirect('?page=anggota');
}

//  PEMINJAMAN (USER) ACTIONS

// Siswa ajukan pengembalian — status = pengajuan_kembali
if ($page === 'kembalikan' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    if (!isAnggota()) redirect('?page=login');
    $id     = (int)($_POST['id_peminjaman'] ?? 0);
    $userId = $_SESSION['user_id'];
    $pdo    = db();
    $s      = $pdo->prepare('SELECT * FROM peminjaman WHERE id_peminjaman=? AND id_anggota=? AND status="dipinjam" LIMIT 1');
    $s->execute([$id, $userId]);
    $p = $s->fetch();
    if (!$p) { flash('error', 'Data peminjaman tidak ditemukan.'); redirect('?page=peminjaman'); }
    // Status = pengajuan_kembali, stok BELUM bertambah (menunggu verifikasi admin)
    $pdo->prepare('UPDATE peminjaman SET status=? WHERE id_peminjaman=?')
        ->execute(['pengajuan_kembali', $id]);
    flash('success', 'Pengajuan pengembalian berhasil! Menunggu verifikasi admin.');
    redirect('?page=peminjaman');
}

// Siswa batalkan pengajuan peminjaman (menunggu → hapus) + kembalikan stok
if ($page === 'anggota_batal_pinjam' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    if (!isAnggota()) redirect('?page=login');
    $id     = (int)($_POST['id_peminjaman'] ?? 0);
    $userId = $_SESSION['user_id'];
    $pdo    = db();

    // Pastikan milik user sendiri dan status masih menunggu
    $s = $pdo->prepare('SELECT * FROM peminjaman WHERE id_peminjaman=? AND id_anggota=? AND status="menunggu" LIMIT 1');
    $s->execute([$id, $userId]);
    $p = $s->fetch();

    if (!$p) {
        flash('error', 'Hanya pengajuan berstatus menunggu yang dapat dibatalkan.');
        redirect('?page=peminjaman');
    }

    // Hapus peminjaman dan kembalikan stok
    $pdo->prepare('DELETE FROM peminjaman WHERE id_peminjaman=?')->execute([$id]);
    $pdo->prepare('UPDATE buku SET stok = stok + 1 WHERE id_buku = ?')->execute([$p['id_buku']]);

    flash('success', 'Peminjaman berhasil dibatalkan dan stok buku telah kembali.');
    redirect('?page=peminjaman');
}

// Admin: Batalkan pengajuan peminjaman (menunggu → batal/hapus)
if ($page === 'admin_tolak_pinjam' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $id  = (int)($_POST['id_peminjaman'] ?? 0);
    $pdo = db();
    $s   = $pdo->prepare('SELECT * FROM peminjaman WHERE id_peminjaman=? AND status="menunggu" LIMIT 1');
    $s->execute([$id]);
    $p = $s->fetch();
    if (!$p) { flash('error', 'Data tidak ditemukan atau status bukan menunggu.'); redirect('?page=admin_peminjaman'); }
    
    // Hapus dan kembalikan stok (karena saat menunggu sudah dikurangi)
    $pdo->prepare('DELETE FROM peminjaman WHERE id_peminjaman=?')->execute([$id]);
    $pdo->prepare('UPDATE buku SET stok = stok + 1 WHERE id_buku = ?')->execute([$p['id_buku']]);

    flash('success', 'Pengajuan peminjaman ditolak, data dihapus, dan stok dikembalikan.');
    redirect('?page=admin_peminjaman');
}

// Admin: Setujui peminjaman (menunggu → dipinjam) + kurangi stok
if ($page === 'admin_approve_pinjam' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $id  = (int)($_POST['id_peminjaman'] ?? 0);
    $pdo = db();
    $s   = $pdo->prepare('SELECT * FROM peminjaman WHERE id_peminjaman=? AND status="menunggu" LIMIT 1');
    $s->execute([$id]);
    $p = $s->fetch();
    if (!$p) { flash('error', 'Data tidak ditemukan atau status bukan menunggu.'); redirect('?page=admin_peminjaman'); }
    
    // Stok sudah dikurangi saat pengajuan, jadi tinggal ubah status
    $pdo->prepare('UPDATE peminjaman SET status=? WHERE id_peminjaman=?')->execute(['dipinjam', $id]);
    
    flash('success', 'Peminjaman disetujui!');
    redirect('?page=admin_peminjaman');
}

// Admin: Verifikasi pengembalian (pengajuan_kembali → dikembalikan) + tambah stok
if ($page === 'admin_verif_kembali' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $id  = (int)($_POST['id_peminjaman'] ?? 0);
    $pdo = db();
    $s   = $pdo->prepare('SELECT * FROM peminjaman WHERE id_peminjaman=? AND status="pengajuan_kembali" LIMIT 1');
    $s->execute([$id]);
    $p = $s->fetch();
    if (!$p) { flash('error', 'Data tidak ditemukan atau status bukan pengajuan_kembali.'); redirect('?page=admin_peminjaman'); }

    $tgl_kembali_seharusnya = $p['tanggal_kembali'];
    $tgl_kembali_aktual = date('Y-m-d');
    $denda = hitungDenda($tgl_kembali_seharusnya);

    $pdo->prepare('UPDATE peminjaman SET status=?, tanggal_kembali=?, denda=? WHERE id_peminjaman=?')
        ->execute(['dikembalikan', $tgl_kembali_aktual, $denda, $id]);
    $pdo->prepare('UPDATE buku SET stok=stok+1 WHERE id_buku=?')->execute([$p['id_buku']]);

    $msg = 'Pengembalian diverifikasi! Stok buku telah ditambah.';
    if ($denda > 0) $msg .= ' Denda keterlambatan: Rp ' . number_format($denda, 0, ',', '.');
    flash('success', $msg);
    redirect('?page=admin_peminjaman');
}

//  ADMIN PEMINJAMAN ACTIONS

if ($page === 'admin_peminjaman_store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $pdo  = db();
    $d    = [
        'id_anggota'      => (int)($_POST['id_anggota'] ?? 0),
        'id_buku'         => (int)($_POST['id_buku'] ?? 0),
        'tanggal_pinjam'  => $_POST['tanggal_pinjam'] ?? '',
        'tanggal_kembali' => $_POST['tanggal_kembali'] ?? '',
        'status'          => $_POST['status'] ?? 'menunggu',
        'catatan'         => trim($_POST['catatan'] ?? ''),
        'denda'           => (int)($_POST['denda'] ?? 0),
    ];
    $chk = $pdo->prepare('SELECT id_peminjaman FROM peminjaman WHERE id_anggota=? AND id_buku=? AND status IN ("menunggu","dipinjam") LIMIT 1');
    $chk->execute([$d['id_anggota'], $d['id_buku']]);
    if ($chk->fetch()) { flash('error', 'Anggota sudah mengajukan atau meminjam buku ini.'); redirect('?page=admin_peminjaman_create'); }
    $pdo->prepare('INSERT INTO peminjaman (id_anggota,id_buku,tanggal_pinjam,tanggal_kembali,status,catatan,denda) VALUES(?,?,?,?,?,?,?)')
        ->execute(array_values($d));
    if (in_array($d['status'], ['menunggu', 'dipinjam'])) {
        $pdo->prepare('UPDATE buku SET stok=stok-1 WHERE id_buku=?')->execute([$d['id_buku']]);
    }
    flash('success', 'Data peminjaman berhasil ditambahkan!');
    redirect('?page=admin_peminjaman');
}

if ($page === 'admin_peminjaman_update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $id  = (int)($_POST['id_peminjaman'] ?? 0);
    $pdo = db();
    $old = $pdo->prepare('SELECT * FROM peminjaman WHERE id_peminjaman=? LIMIT 1'); $old->execute([$id]);
    $p   = $old->fetch();
    if (!$p) { flash('error', 'Data tidak ditemukan.'); redirect('?page=admin_peminjaman'); }
    $newStatus = $_POST['status'] ?? $p['status'];
    // Handle stock changes based on status transition
    if ($p['status'] !== $newStatus) {
        // Jika status lama 'dikembalikan' dan status baru bukan 'dikembalikan' (e.g. balik ke dipinjam/menunggu) -> kurangi stok
        if ($p['status'] === 'dikembalikan' && in_array($newStatus, ['menunggu', 'dipinjam', 'pengajuan_kembali'])) {
            $pdo->prepare('UPDATE buku SET stok=stok-1 WHERE id_buku=?')->execute([$p['id_buku']]);
        }
        // Jika status baru 'dikembalikan' dan status lama bukan 'dikembalikan' -> tambah stok
        elseif ($newStatus === 'dikembalikan' && $p['status'] !== 'dikembalikan') {
            $pdo->prepare('UPDATE buku SET stok=stok+1 WHERE id_buku=?')->execute([$p['id_buku']]);
        }
    }
    $pdo->prepare('UPDATE peminjaman SET id_anggota=?,id_buku=?,tanggal_pinjam=?,tanggal_kembali=?,status=?,catatan=?,denda=? WHERE id_peminjaman=?')
        ->execute([(int)$_POST['id_anggota'], (int)$_POST['id_buku'], $_POST['tanggal_pinjam'], $_POST['tanggal_kembali'], $newStatus, trim($_POST['catatan'] ?? ''), (int)$_POST['denda'], $id]);
    flash('success', 'Data peminjaman berhasil diperbarui!');
    redirect('?page=admin_peminjaman');
}

if ($page === 'admin_peminjaman_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdmin();
    $id  = (int)($_POST['id_peminjaman'] ?? 0);
    $pdo = db();
    $s   = $pdo->prepare('SELECT * FROM peminjaman WHERE id_peminjaman=? LIMIT 1'); $s->execute([$id]);
    $p   = $s->fetch();
    if ($p && in_array($p['status'], ['menunggu', 'dipinjam', 'pengajuan_kembali'])) {
        $pdo->prepare('UPDATE buku SET stok=stok+1 WHERE id_buku=?')->execute([$p['id_buku']]);
    }
    $pdo->prepare('DELETE FROM peminjaman WHERE id_peminjaman=?')->execute([$id]);
    flash('success', 'Data peminjaman berhasil dihapus!');
    redirect('?page=admin_peminjaman');
}

//  PAGE ROUTING

$pagePaths = [
    'login' => 'login.php',
    'register' => 'register.php',
    'dashboard' => 'dashboard.php',
    'buku' => 'anggota/buku.php',
    'peminjaman' => 'anggota/peminjaman.php',
    'admin_buku' => 'admin/buku.php',
    'buku_create' => 'admin/buku_create.php',
    'buku_edit' => 'admin/buku_edit.php',
    'anggota' => 'admin/anggota.php',
    'anggota_create' => 'admin/anggota_create.php',
    'anggota_edit' => 'admin/anggota_edit.php',
    'admin_peminjaman' => 'admin/peminjaman.php',
    'admin_peminjaman_create' => 'admin/peminjaman_create.php',
    'admin_peminjaman_edit' => 'admin/peminjaman_edit.php',
];

if (!array_key_exists($page, $pagePaths)) {
    $page = 'dashboard';
}

$file = __DIR__ . '/pages/' . $pagePaths[$page];
if (file_exists($file)) {
    require $file;
} else {
    echo '<p style="font-family:sans-serif;padding:20px">Halaman <strong>' . e($page) . '</strong> tidak ditemukan.</p>';
}
