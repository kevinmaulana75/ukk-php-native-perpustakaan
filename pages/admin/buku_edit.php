<?php
// pages/buku_edit.php — Admin: Edit book
requireAdmin();
$id        = (int)($_GET['id'] ?? 0);
$pdo       = db();
$stmt      = $pdo->prepare('SELECT * FROM buku WHERE id_buku=? LIMIT 1');
$stmt->execute([$id]);
$buku = $stmt->fetch();
if (!$buku) { flash('error', 'Buku tidak ditemukan.'); redirect('?page=admin_buku'); }

$pageTitle = 'Edit Buku';
$activeNav = 'admin_buku';
$error = hasFlash('error') ? getFlash('error') : '';
include __DIR__ . '/../_layout_head.php';
?>
<div class="toolbar">
    <div>
        <h1 class="page-title" style="margin-bottom:0">Edit Buku</h1>
    </div>
    <a href="?page=admin_buku" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
</div>

<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<div class="form-card">
    <form action="?page=buku_update" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_buku" value="<?= (int)$buku['id_buku'] ?>">
        <div class="form-group">
            <label class="form-label">Judul Buku *</label>
            <input type="text" name="judul_buku" class="glass-input" value="<?= e($buku['judul_buku']) ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Kategori</label>
            <?php
            $kategori_list = ['Novel', 'Komik', 'Romance', 'Horror', 'Fantasy', 'Sci-Fi', 'Misteri', 'Biografi', 'Sejarah', 'Ensiklopedia', 'Motivasi', 'Manajemen'];
            $cur_kat = $buku['kategori'] ?? '';
            ?>
            <select name="kategori" class="glass-input">
                <option value="" disabled <?= $cur_kat === '' ? 'selected' : '' ?>>Pilih Kategori</option>
                <?php foreach ($kategori_list as $kat): ?>
                    <option value="<?= $kat ?>" <?= $cur_kat === $kat ? 'selected' : '' ?>><?= $kat ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Pengarang *</label>
            <input type="text" name="pengarang" class="glass-input" value="<?= e($buku['pengarang']) ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Penerbit *</label>
            <input type="text" name="penerbit" class="glass-input" value="<?= e($buku['penerbit']) ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Tahun Terbit *</label>
            <input type="number" name="tahun_terbit" class="glass-input" value="<?= e($buku['tahun_terbit']) ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Stok *</label>
            <input type="number" name="stok" class="glass-input" value="<?= e($buku['stok']) ?>" min="0" required>
        </div>
        <div class="form-group">
            <?php if ($buku['gambar']): ?>
                <label class="form-label">Cover Saat Ini</label>
                <img src="../uploads/<?= e($buku['gambar']) ?>" alt="Cover" style="height:100px;border-radius:8px;margin-bottom:10px;display:block">
            <?php endif; ?>
            <label class="form-label">Ganti Cover (opsional)</label>
            <input type="file" name="gambar" class="glass-input" accept="image/*">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
            <a href="?page=admin_buku" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../_layout_foot.php'; ?>
