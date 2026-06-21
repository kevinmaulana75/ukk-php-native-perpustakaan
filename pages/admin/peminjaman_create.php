<?php
// pages/admin_peminjaman_create.php — Admin: Add loan record
requireAdmin();
$pdo       = db();
$bukus     = $pdo->query('SELECT id_buku, judul_buku FROM buku WHERE stok>0 ORDER BY judul_buku')->fetchAll();
$anggota   = $pdo->query('SELECT id_anggota, nama FROM anggota ORDER BY nama')->fetchAll();
$pageTitle = 'Tambah Peminjaman';
$activeNav = 'admin_peminjaman';
$error = hasFlash('error') ? getFlash('error') : '';
include __DIR__ . '/../_layout_head.php';
?>
<div class="toolbar">
    <h1 class="page-title" style="margin-bottom:0">Tambah Peminjaman</h1>
    <a href="?page=admin_peminjaman" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
</div>

<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<div class="form-card">
    <form action="?page=admin_peminjaman_store" method="POST">
        <div class="form-group">
            <label class="form-label">Anggota *</label>
            <select name="id_anggota" class="glass-input" required>
                <option value="">-- Pilih Anggota --</option>
                <?php foreach ($anggota as $a): ?>
                    <option value="<?= (int)$a['id_anggota'] ?>"><?= e($a['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Buku *</label>
            <select name="id_buku" class="glass-input" required>
                <option value="">-- Pilih Buku --</option>
                <?php foreach ($bukus as $b): ?>
                    <option value="<?= (int)$b['id_buku'] ?>"><?= e($b['judul_buku']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Tanggal Pinjam *</label>
            <input type="date" name="tanggal_pinjam" class="glass-input" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Tanggal Kembali *</label>
            <input type="date" name="tanggal_kembali" class="glass-input" required>
        </div>
        <div class="form-group">
            <label class="form-label">Status *</label>
            <select name="status" class="glass-input">
                <option value="dipinjam">Dipinjam</option>
                <option value="dikembalikan">Dikembalikan</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Denda Awal (opsional)</label>
            <input type="number" name="denda" class="glass-input" value="0" min="0">
        </div>
        <div class="form-group">
            <label class="form-label">Catatan (opsional)</label>
            <textarea name="catatan" class="glass-input" style="height:70px"></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
            <a href="?page=admin_peminjaman" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../_layout_foot.php'; ?>
