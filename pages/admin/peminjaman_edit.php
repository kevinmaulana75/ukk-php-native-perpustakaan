<?php
// pages/admin_peminjaman_edit.php — Admin: Edit loan record
requireAdmin();
$id   = (int)($_GET['id'] ?? 0);
$pdo  = db();
$stmt = $pdo->prepare('SELECT * FROM peminjaman WHERE id_peminjaman=? LIMIT 1');
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { flash('error', 'Data peminjaman tidak ditemukan.'); redirect('?page=admin_peminjaman'); }

$bukus   = $pdo->query('SELECT id_buku, judul_buku FROM buku ORDER BY judul_buku')->fetchAll();
$anggota = $pdo->query('SELECT id_anggota, nama FROM anggota ORDER BY nama')->fetchAll();

$pageTitle = 'Edit Peminjaman';
$activeNav = 'admin_peminjaman';
$error = hasFlash('error') ? getFlash('error') : '';
include __DIR__ . '/../_layout_head.php';
?>
<div class="toolbar">
    <h1 class="page-title" style="margin-bottom:0">Edit Peminjaman</h1>
    <a href="?page=admin_peminjaman" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
</div>

<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<div class="form-card">
    <form action="?page=admin_peminjaman_update" method="POST">
        <input type="hidden" name="id_peminjaman" value="<?= (int)$p['id_peminjaman'] ?>">
        <div class="form-group">
            <label class="form-label">Anggota *</label>
            <select name="id_anggota" class="glass-input" required>
                <?php foreach ($anggota as $a): ?>
                    <option value="<?= (int)$a['id_anggota'] ?>" <?= $p['id_anggota'] == $a['id_anggota'] ? 'selected' : '' ?>>
                        <?= e($a['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Buku *</label>
            <select name="id_buku" class="glass-input" required>
                <?php foreach ($bukus as $b): ?>
                    <option value="<?= (int)$b['id_buku'] ?>" <?= $p['id_buku'] == $b['id_buku'] ? 'selected' : '' ?>>
                        <?= e($b['judul_buku']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Tanggal Pinjam *</label>
            <input type="date" name="tanggal_pinjam" class="glass-input" value="<?= e($p['tanggal_pinjam']) ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Tanggal Kembali *</label>
            <input type="date" name="tanggal_kembali" class="glass-input" value="<?= e($p['tanggal_kembali']) ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Status *</label>
            <select name="status" class="glass-input">
                <option value="menunggu"          <?= $p['status'] === 'menunggu'          ? 'selected' : '' ?>>&#9203; Menunggu</option>
                <option value="dipinjam"          <?= $p['status'] === 'dipinjam'          ? 'selected' : '' ?>>&#128214; Dipinjam</option>
                <option value="pengajuan_kembali" <?= $p['status'] === 'pengajuan_kembali' ? 'selected' : '' ?>>&#128260; Pengajuan Kembali</option>
                <option value="dikembalikan"      <?= $p['status'] === 'dikembalikan'      ? 'selected' : '' ?>>&#9989; Dikembalikan</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Denda (Rp)</label>
            <input type="number" name="denda" class="glass-input" value="<?= (int)($p['denda'] ?? 0) ?>" min="0">
        </div>
        <div class="form-group">
            <label class="form-label">Catatan (opsional)</label>
            <textarea name="catatan" class="glass-input" style="height:70px"><?= e($p['catatan'] ?? '') ?></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
            <a href="?page=admin_peminjaman" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../_layout_foot.php'; ?>
