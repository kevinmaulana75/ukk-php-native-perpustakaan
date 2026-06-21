<?php
// pages/anggota.php — Admin: Member list
requireAdmin();
$pdo       = db();
$anggota   = $pdo->query('SELECT * FROM anggota ORDER BY nama')->fetchAll();
$pageTitle = 'Data Anggota';
$activeNav = 'anggota';
$success = hasFlash('success') ? getFlash('success') : '';
$error   = hasFlash('error')   ? getFlash('error')   : '';
include __DIR__ . '/../_layout_head.php';
?>
<div class="toolbar">
    <h1 class="page-title" style="margin-bottom:0">Data Anggota</h1>
    <a href="?page=anggota_create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Tambah Anggota</a>
</div>

<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr><th>#</th><th>Nama</th><th>Kelas</th><th>Alamat</th><th>Username</th><th>Tgl Daftar</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php if (empty($anggota)): ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--brand-muted)">Belum ada anggota.</td></tr>
        <?php else: ?>
        <?php foreach ($anggota as $i => $a): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><strong><?= e($a['nama']) ?></strong></td>
                <td><?= e($a['kelas']) ?></td>
                <td><?= e($a['alamat']) ?></td>
                <td><?= e($a['username']) ?></td>
                <td><?= e($a['tanggal_daftar'] ?? '-') ?></td>
                <td>
                    <div style="display:flex;gap:6px">
                        <a href="?page=anggota_edit&id=<?= (int)$a['id_anggota'] ?>" class="btn btn-warning" style="padding:6px 12px">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form action="?page=anggota_delete" method="POST" onsubmit="return confirm('Hapus anggota ini?')">
                            <input type="hidden" name="id_anggota" value="<?= (int)$a['id_anggota'] ?>">
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
