<?php
// pages/anggota_edit.php — Admin: Edit member
requireAdmin();
$id   = (int)($_GET['id'] ?? 0);
$pdo  = db();
$stmt = $pdo->prepare('SELECT * FROM anggota WHERE id_anggota=? LIMIT 1');
$stmt->execute([$id]);
$anggota = $stmt->fetch();
if (!$anggota) { flash('error', 'Anggota tidak ditemukan.'); redirect('?page=anggota'); }

$pageTitle = 'Edit Anggota';
$activeNav = 'anggota';
$error = hasFlash('error') ? getFlash('error') : '';
include __DIR__ . '/../_layout_head.php';
?>
<div class="toolbar">
    <h1 class="page-title" style="margin-bottom:0">Edit Anggota</h1>
    <a href="?page=anggota" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
</div>

<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<div class="form-card">
    <form action="?page=anggota_update" method="POST">
        <input type="hidden" name="id_anggota" value="<?= (int)$anggota['id_anggota'] ?>">
        <div class="form-group">
            <label class="form-label">Nama Lengkap *</label>
            <input type="text" name="nama" class="glass-input" value="<?= e($anggota['nama']) ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Kelas *</label>
            <input type="text" name="kelas" class="glass-input" value="<?= e($anggota['kelas']) ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Alamat *</label>
            <textarea name="alamat" class="glass-input" style="height:80px" required><?= e($anggota['alamat']) ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Username *</label>
            <input type="text" name="username" class="glass-input" value="<?= e($anggota['username']) ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Password Baru <small style="color:var(--brand-muted)">(kosongkan jika tidak diubah)</small></label>
            <input type="password" name="password" class="glass-input">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
            <a href="?page=anggota" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../_layout_foot.php'; ?>
