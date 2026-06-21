<?php
// pages/buku_create.php — Admin: Add new book
requireAdmin();
$pageTitle = 'Tambah Buku';
$activeNav = 'admin_buku';
$error = hasFlash('error') ? getFlash('error') : '';
include __DIR__ . '/../_layout_head.php';
?>
<div class="toolbar">
    <div>
        <h1 class="page-title" style="margin-bottom:0">Tambah Buku</h1>
    </div>
    <a href="?page=admin_buku" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
</div>

<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<div class="form-card">
    <form action="?page=buku_store" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label class="form-label">Judul Buku *</label>
            <input type="text" name="judul_buku" class="glass-input" value="<?= old('judul_buku') ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Kategori</label>
            <?php
            $kategori_list = ['Novel', 'Komik', 'Romance', 'Horror', 'Fantasy', 'Sci-Fi', 'Misteri', 'Biografi', 'Sejarah', 'Ensiklopedia', 'Motivasi', 'Manajemen'];
            $old_kat = old('kategori');
            ?>
            <select name="kategori" class="glass-input">
                <option value="" disabled <?= $old_kat == '' ? 'selected' : '' ?>>Pilih Kategori</option>
                <?php foreach ($kategori_list as $kat): ?>
                    <option value="<?= $kat ?>" <?= $old_kat === $kat ? 'selected' : '' ?>><?= $kat ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Pengarang *</label>
            <input type="text" name="pengarang" class="glass-input" value="<?= old('pengarang') ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Penerbit *</label>
            <input type="text" name="penerbit" class="glass-input" value="<?= old('penerbit') ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Tahun Terbit *</label>
            <input type="number" name="tahun_terbit" class="glass-input" value="<?= old('tahun_terbit', (string)date('Y')) ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Stok *</label>
            <input type="number" name="stok" class="glass-input" value="<?= old('stok', '0') ?>" min="0" required>
        </div>
        <div class="form-group">
            <label class="form-label">Cover Buku (opsional)</label>
            <input type="file" name="gambar" class="glass-input" accept="image/*">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
            <a href="?page=admin_buku" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../_layout_foot.php'; ?>
