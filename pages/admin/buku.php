<?php
// pages/admin_buku.php — Admin view of books
requireAdmin();

$pdo       = db();
$search    = trim($_GET['search'] ?? '');
$kategori  = trim($_GET['kategori'] ?? '');
$pageTitle = 'Data Buku';
$activeNav = 'admin_buku';

$sql    = 'SELECT * FROM buku WHERE 1=1';
$params = [];
if ($search)   { $sql .= ' AND judul_buku LIKE ?'; $params[] = "%$search%"; }
if ($kategori) { $sql .= ' AND kategori = ?'; $params[] = $kategori; }
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bukus = $stmt->fetchAll();

$cats = $pdo->query('SELECT DISTINCT kategori FROM buku WHERE kategori IS NOT NULL AND kategori != "" ORDER BY kategori')->fetchAll(PDO::FETCH_COLUMN);

$success = hasFlash('success') ? getFlash('success') : '';
$error   = hasFlash('error')   ? getFlash('error')   : '';

include __DIR__ . '/../_layout_head.php';
?>
<div class="toolbar">
    <div>
        <h1 class="page-title" style="margin-bottom:0">Data Buku</h1>
    </div>
    <a href="?page=buku_create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Tambah Buku</a>
</div>

<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<form method="GET" action="?">
    <input type="hidden" name="page" value="admin_buku">
    <div class="search-bar">
        <input type="text" class="glass-input" name="search" placeholder="Cari judul buku..." value="<?= e($search) ?>">
        <select name="kategori" class="glass-input" style="max-width:200px">
            <option value="">Semua Kategori</option>
            <?php foreach ($cats as $cat): ?>
                <option value="<?= e($cat) ?>" <?= $kategori === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i></button>
        <?php if ($search || $kategori): ?><a href="?page=admin_buku" class="btn btn-outline">Reset</a><?php endif; ?>
    </div>
</form>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th><th>Judul</th><th>Kategori</th><th>Pengarang</th><th>Penerbit</th><th>Tahun</th><th>Stok</th><th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($bukus)): ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--brand-muted)">Tidak ada buku.</td></tr>
        <?php else: ?>
        <?php foreach ($bukus as $i => $b): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><strong><?= e($b['judul_buku']) ?></strong></td>
                <td><?= e($b['kategori'] ?? '-') ?></td>
                <td><?= e($b['pengarang']) ?></td>
                <td><?= e($b['penerbit']) ?></td>
                <td><?= e($b['tahun_terbit']) ?></td>
                <td><span class="stok-badge <?= $b['stok'] <= 0 ? 'stok-zero' : '' ?>"><?= (int)$b['stok'] ?></span></td>
                <td>
                    <div style="display:flex;gap:6px">
                        <a href="?page=buku_edit&id=<?= (int)$b['id_buku'] ?>" class="btn btn-warning" style="padding:6px 12px">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form action="?page=buku_delete" method="POST" onsubmit="return confirm('Hapus buku ini?')">
                            <input type="hidden" name="id_buku" value="<?= (int)$b['id_buku'] ?>">
                            <button type="submit" class="btn btn-danger" style="padding:6px 12px">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../_layout_foot.php'; ?>
