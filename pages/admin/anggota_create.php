<?php
// pages/anggota_create.php — Admin: Add member
requireAdmin();
$pageTitle = 'Tambah Anggota';
$activeNav = 'anggota';
$error = hasFlash('error') ? getFlash('error') : '';
include __DIR__ . '/../_layout_head.php';
?>
<div class="toolbar">
    <h1 class="page-title" style="margin-bottom:0">Tambah Anggota</h1>
    <a href="?page=anggota" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
</div>

<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<div class="form-card">
    <form action="?page=anggota_store" method="POST">
        <div class="form-group">
            <label class="form-label">Nama Lengkap *</label>
            <input type="text" name="nama" class="glass-input" value="<?= old('nama') ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Kelas *</label>
            <input type="text" name="kelas" class="glass-input" value="<?= old('kelas') ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Alamat *</label>
            <textarea name="alamat" class="glass-input" style="height:80px" required><?= old('alamat') ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Username *</label>
            <input type="text" name="username" class="glass-input" value="<?= old('username') ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Password * <small style="color:var(--brand-muted)">(min. 6 karakter)</small></label>
            <input type="password" name="password" class="glass-input" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
            <a href="?page=anggota" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../_layout_foot.php'; ?>
