<?php
// pages/buku.php  — Daftar buku (view for anggota)
requireLogin();
if (!isAnggota()) redirect('?page=dashboard');

$pdo       = db();
$search    = trim($_GET['search'] ?? '');
$kategori  = trim($_GET['kategori'] ?? '');
$userId    = $_SESSION['user_id'];
$pageTitle = 'Daftar Buku';
$activeNav = 'buku';

$sql  = 'SELECT * FROM buku WHERE 1=1';
$params = [];
if ($search) { $sql .= ' AND judul_buku LIKE ?'; $params[] = "%$search%"; }
if ($kategori) { $sql .= ' AND kategori = ?'; $params[] = $kategori; }
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bukus = $stmt->fetchAll();

// Books currently borrowed or pending by this user
$bp = $pdo->prepare('SELECT id_buku FROM peminjaman WHERE id_anggota=? AND status IN ("menunggu","dipinjam","pengajuan_kembali")');
$bp->execute([$userId]);
$sedang_dipinjam = array_column($bp->fetchAll(), 'id_buku');

// Get unique categories
$cats = $pdo->query('SELECT DISTINCT kategori FROM buku WHERE kategori IS NOT NULL AND kategori != "" ORDER BY kategori')->fetchAll(PDO::FETCH_COLUMN);

$success = hasFlash('success') ? getFlash('success') : '';
$error   = hasFlash('error')   ? getFlash('error')   : '';

include __DIR__ . '/../_layout_head.php';
?>
<h1 class="page-title">Daftar Buku</h1>

<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<form method="GET" action="?">
    <input type="hidden" name="page" value="buku">
    <div class="search-bar">
        <input type="text" class="glass-input" name="search" placeholder="Cari judul buku..." value="<?= e($search) ?>">
        <select name="kategori" class="glass-input" style="max-width:200px">
            <option value="">Semua Kategori</option>
            <?php foreach ($cats as $cat): ?>
                <option value="<?= e($cat) ?>" <?= $kategori === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Cari</button>
        <?php if ($search || $kategori): ?>
            <a href="?page=buku" class="btn btn-outline">Reset</a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($bukus)): ?>
    <div class="empty-state"><i class="fa-solid fa-book-open"></i><br>Tidak ada buku ditemukan.</div>
<?php else: ?>
<div class="book-grid">
    <?php foreach ($bukus as $b): ?>
    <?php $dipinjam = in_array($b['id_buku'], $sedang_dipinjam); ?>
    <div class="book-card">
        <div class="book-cover">
            <?php if ($b['gambar']): ?>
                <img src="../uploads/<?= e($b['gambar']) ?>" alt="<?= e($b['judul_buku']) ?>" onerror="this.parentElement.innerHTML='<i class=\'fa-solid fa-book\'></i>'">
            <?php else: ?>
                <i class="fa-solid fa-book"></i>
            <?php endif; ?>
        </div>
        <div class="book-info">
            <div class="book-title"><?= e($b['judul_buku']) ?></div>
            <div class="book-author"><?= e($b['pengarang']) ?></div>
            <div class="book-meta">
                <span><?= e($b['kategori'] ?? '-') ?></span>
                <span class="stok-badge <?= $b['stok'] <= 0 ? 'stok-zero' : '' ?>">Stok: <?= (int)$b['stok'] ?></span>
            </div>
            <?php if ($dipinjam): ?>
                <button class="btn btn-warning" style="width:100%;justify-content:center;opacity:.6" disabled>
                    <?php
                    $bp2 = $pdo->prepare('SELECT status FROM peminjaman WHERE id_anggota=? AND id_buku=? AND status IN ("menunggu","dipinjam","pengajuan_kembali") LIMIT 1');
                    $bp2->execute([$userId, $b['id_buku']]);
                    $bstatus = $bp2->fetchColumn();
                    if ($bstatus === 'menunggu') echo '&#9203; Menunggu Persetujuan';
                    elseif ($bstatus === 'pengajuan_kembali') echo '&#128260; Proses Pengembalian';
                    else echo 'Sedang Dipinjam';
                    ?>
                </button>
            <?php elseif ($b['stok'] > 0): ?>
                <button class="btn btn-primary" style="width:100%;justify-content:center"
                    onclick="openPinjamModal(<?= (int)$b['id_buku'] ?>, '<?= addslashes(e($b['judul_buku'])) ?>')">
                    <i class="fa-solid fa-hand-holding"></i> Pinjam
                </button>
            <?php else: ?>
                <button class="btn btn-danger" style="width:100%;justify-content:center;opacity:.6" disabled>Stok Habis</button>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Borrow Modal -->
<div id="pinjam-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:100;align-items:center;justify-content:center">
    <div class="form-card" style="max-width:420px;width:90%;background:#ffffff;">
        <h2 style="margin-bottom:20px;font-size:1.2rem">Pinjam Buku</h2>
        <p id="modal-book-title" style="font-weight:600;margin-bottom:20px;color:var(--brand-muted)"></p>
        <form action="?page=buku_pinjam" method="POST">
            <input type="hidden" name="id_buku" id="modal-id-buku">
            <div class="form-group">
                <label class="form-label">Tanggal Pinjam</label>
                <input type="date" name="tanggal_pinjam" id="modal-tgl-pinjam" class="glass-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Kembali <small style="color:var(--brand-muted)">(maks. 14 hari)</small></label>
                <input type="date" name="tanggal_kembali" id="modal-tgl-kembali" class="glass-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Catatan (opsional)</label>
                <textarea name="catatan" class="glass-input" style="height:70px"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Konfirmasi Pinjam</button>
                <button type="button" class="btn btn-outline" onclick="closePinjamModal()">Batal</button>
            </div>
        </form>
    </div>
</div>
<script>
function openPinjamModal(id, judul) {
    document.getElementById('modal-id-buku').value = id;
    document.getElementById('modal-book-title').textContent = judul;
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('modal-tgl-pinjam').value = today;
    const max14 = new Date(); max14.setDate(max14.getDate()+14);
    document.getElementById('modal-tgl-kembali').value = max14.toISOString().split('T')[0];
    document.getElementById('pinjam-modal').style.display = 'flex';
}
function closePinjamModal() {
    document.getElementById('pinjam-modal').style.display = 'none';
}
</script>

<?php include __DIR__ . '/../_layout_foot.php'; ?>
