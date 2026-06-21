<?php
// pages/dashboard.php
requireLogin();
$pdo      = db();
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';

if (isAdmin()) {
    $total_buku       = $pdo->query('SELECT COUNT(*) FROM buku')->fetchColumn();
    $total_anggota    = $pdo->query('SELECT COUNT(*) FROM anggota')->fetchColumn();
    $total_peminjaman = $pdo->query('SELECT COUNT(*) FROM peminjaman')->fetchColumn();
    $buku_dipinjam    = $pdo->query('SELECT COUNT(*) FROM peminjaman WHERE status="dipinjam"')->fetchColumn();
}

include __DIR__ . '/_layout_head.php';
?>

<?php if (isAdmin()): ?>
    <h1 class="page-title">Dashboard Admin</h1>
    <p class="page-subtitle">Selamat datang, <?= e($_SESSION['user_name']) ?>!</p>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(116,185,255,.2);color:#0984e3">
                <i class="fa-solid fa-swatchbook"></i>
            </div>
            <div>
                <div class="stat-number"><?= $total_buku ?></div>
                <div class="stat-label">Total Buku</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(85,239,196,.2);color:#00b894">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="stat-number"><?= $total_anggota ?></div>
                <div class="stat-label">Total Anggota</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(162,155,254,.2);color:#6c5ce7">
                <i class="fa-solid fa-handshake"></i>
            </div>
            <div>
                <div class="stat-number"><?= $total_peminjaman ?></div>
                <div class="stat-label">Total Peminjaman</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(253,203,110,.3);color:#e17055">
                <i class="fa-solid fa-book-open-reader"></i>
            </div>
            <div>
                <div class="stat-number"><?= $buku_dipinjam ?></div>
                <div class="stat-label">Buku Sedang Dipinjam</div>
            </div>
        </div>
    </div>

<?php elseif (isAnggota()): ?>
    <?php
    $userId = $_SESSION['user_id'];
    
    // Stats for Anggota
    $statMenunggu = $pdo->prepare('SELECT COUNT(*) FROM peminjaman WHERE id_anggota=? AND status="menunggu"');
    $statMenunggu->execute([$userId]);
    $stMenunggu = $statMenunggu->fetchColumn();

    $statDipinjam = $pdo->prepare('SELECT COUNT(*) FROM peminjaman WHERE id_anggota=? AND status="dipinjam"');
    $statDipinjam->execute([$userId]);
    $stDipinjam = $statDipinjam->fetchColumn();

    $statSelesai = $pdo->prepare('SELECT COUNT(*) FROM peminjaman WHERE id_anggota=? AND status="dikembalikan"');
    $statSelesai->execute([$userId]);
    $stSelesai = $statSelesai->fetchColumn();

    // Total Denda Berjalan
    $activeLoans = $pdo->prepare('SELECT tanggal_kembali FROM peminjaman WHERE id_anggota=? AND status IN ("dipinjam","pengajuan_kembali")');
    $activeLoans->execute([$userId]);
    $totalDenda = 0;
    foreach ($activeLoans->fetchAll() as $al) {
        $totalDenda += hitungDenda($al['tanggal_kembali']);
    }

    // Latest Books
    $latestBooks = $pdo->query('SELECT * FROM buku ORDER BY id_buku DESC LIMIT 4')->fetchAll();
    ?>

    <h1 class="page-title">Halo, <?= e($_SESSION['user_name']) ?>! 👋</h1>
    <p class="page-subtitle">Selamat datang di perpustakaan Bookavy.</p>
    <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:32px">
        <a href="?page=buku" class="btn btn-primary"><i class="fa-solid fa-book"></i> Lihat Semua Buku</a>
        <a href="?page=peminjaman" class="btn btn-outline"><i class="fa-solid fa-book-open-reader"></i> Peminjaman Saya</a>
    </div>

    <div class="stat-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="stat-card" style="padding: 24px;">
            <div class="stat-icon" style="background:rgba(225,112,85,.2);color:#e17055;width:55px;height:55px;font-size:1.4rem;">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <div class="stat-number" style="font-size: 2rem;"><?= $stMenunggu ?></div>
                <div class="stat-label">Menunggu Persetujuan</div>
            </div>
        </div>
        <div class="stat-card" style="padding: 24px;">
            <div class="stat-icon" style="background:rgba(253,203,110,.3);color:#e17055;width:55px;height:55px;font-size:1.4rem;">
                <i class="fa-solid fa-book-open-reader"></i>
            </div>
            <div>
                <div class="stat-number" style="font-size: 2rem;"><?= $stDipinjam ?></div>
                <div class="stat-label">Sedang Dipinjam</div>
            </div>
        </div>
        <div class="stat-card" style="padding: 24px;">
            <div class="stat-icon" style="background:rgba(85,239,196,.2);color:#00b894;width:55px;height:55px;font-size:1.4rem;">
                <i class="fa-solid fa-check-double"></i>
            </div>
            <div>
                <div class="stat-number" style="font-size: 2rem;"><?= $stSelesai ?></div>
                <div class="stat-label">Buku Dikembalikan</div>
            </div>
        </div>
        <div class="stat-card" style="padding: 24px; border: 1px solid <?= $totalDenda > 0 ? 'rgba(225, 112, 85, .3)' : 'transparent' ?>;">
            <div class="stat-icon" style="background:<?= $totalDenda > 0 ? 'rgba(225, 112, 85, .2)' : 'rgba(85, 239, 196, .2)' ?>;color:<?= $totalDenda > 0 ? '#e17055' : '#00b894' ?>;width:55px;height:55px;font-size:1.4rem;">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div>
                <div class="stat-number" style="font-size: 2rem; color: <?= $totalDenda > 0 ? '#e17055' : 'inherit' ?>;">Rp <?= number_format($totalDenda, 0, ',', '.') ?></div>
                <div class="stat-label">Denda Berjalan</div>
            </div>
        </div>
    </div>

    <h2 style="font-size:1.1rem;font-weight:600;margin-bottom:16px;margin-top:20px;color:var(--brand-muted)">
        <i class="fa-solid fa-sparkles"></i> Buku Terbaru di Perpustakaan
    </h2>
    <?php if (empty($latestBooks)): ?>
        <p style="color:var(--brand-muted)">Belum ada buku saat ini.</p>
    <?php else: ?>
        <div class="book-grid" style="grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));">
            <?php foreach ($latestBooks as $b): ?>
            <a href="?page=buku&search=<?= urlencode($b['judul_buku']) ?>" style="text-decoration:none; color:inherit;">
                <div class="book-card">
                    <div class="book-cover" style="height:160px; font-size:2rem;">
                        <?php if ($b['gambar']): ?>
                            <img src="../uploads/<?= e($b['gambar']) ?>" alt="<?= e($b['judul_buku']) ?>" onerror="this.parentElement.innerHTML='<i class=\'fa-solid fa-book\'></i>'">
                        <?php else: ?>
                            <i class="fa-solid fa-book"></i>
                        <?php endif; ?>
                    </div>
                    <div class="book-info" style="padding:12px;">
                        <div class="book-title" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= e($b['judul_buku']) ?></div>
                        <div class="book-author" style="margin-bottom:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= e($b['pengarang']) ?></div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php else: ?>
    <h1 class="page-title">Selamat Datang di Bookavy</h1>
    <p class="page-subtitle">Silakan <a href="?page=login">login</a> atau <a href="?page=register">daftar</a> untuk mulai meminjam buku.</p>
<?php endif; ?>

<?php include __DIR__ . '/_layout_foot.php'; ?>
