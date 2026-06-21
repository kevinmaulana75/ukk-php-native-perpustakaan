<?php
// pages/admin_peminjaman.php — Admin: Loan management
requireAdmin();
$pdo       = db();
$search    = trim($_GET['search'] ?? '');
$status    = trim($_GET['status'] ?? '');
$pageTitle = 'Data Peminjaman';
$activeNav = 'admin_peminjaman';

$sql    = 'SELECT p.*, b.judul_buku, a.nama AS nama_anggota FROM peminjaman p JOIN buku b ON p.id_buku=b.id_buku JOIN anggota a ON p.id_anggota=a.id_anggota WHERE 1=1';
$params = [];
if ($status) { $sql .= ' AND p.status=?'; $params[] = $status; }
if ($search) {
    $sql .= ' AND (a.nama LIKE ? OR b.judul_buku LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%";
}
$sql .= ' ORDER BY FIELD(p.status,"menunggu","pengajuan_kembali","dipinjam","dikembalikan"), p.tanggal_pinjam DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$peminjaman = $stmt->fetchAll();

// Count by status for summary cards
$counts = [];
$cs = $pdo->query('SELECT status, COUNT(*) as c FROM peminjaman GROUP BY status');
foreach ($cs->fetchAll() as $row) $counts[$row['status']] = $row['c'];

$success = hasFlash('success') ? getFlash('success') : '';
$error   = hasFlash('error')   ? getFlash('error')   : '';

include __DIR__ . '/../_layout_head.php';
?>
<div class="toolbar">
    <h1 class="page-title" style="margin-bottom:0">Data Peminjaman</h1>
    <a href="?page=admin_peminjaman_create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Tambah Manual</a>
</div>

<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<!-- Status Summary Cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px">
    <?php
    $statusCards = [
        'menunggu'          => ['label'=>'Menunggu',        'color'=>'#e17055','icon'=>'clock'],
        'dipinjam'          => ['label'=>'Dipinjam',        'color'=>'#fdcb6e','icon'=>'book-open-reader'],
        'pengajuan_kembali' => ['label'=>'Pengajuan Balik', 'color'=>'#6c5ce7','icon'=>'rotate-left'],
        'dikembalikan'      => ['label'=>'Dikembalikan',    'color'=>'#00b894','icon'=>'check-circle'],
    ];
    foreach ($statusCards as $sk => $sv): ?>
    <a href="?page=admin_peminjaman&status=<?= $sk ?>" style="text-decoration:none">
        <div style="background:rgba(255,255,255,0.65);border-radius:18px;padding:18px 20px;border:2px solid <?= ($status===$sk)?$sv['color']:'rgba(255,255,255,0.4)' ?>;display:flex;align-items:center;gap:14px;transition:all .2s;box-shadow:0 6px 20px rgba(0,0,0,.05)">
            <div style="width:44px;height:44px;border-radius:14px;background:<?= $sv['color'] ?>22;display:flex;align-items:center;justify-content:center;color:<?= $sv['color'] ?>;font-size:1.2rem;flex-shrink:0">
                <i class="fa-solid fa-<?= $sv['icon'] ?>"></i>
            </div>
            <div>
                <div style="font-size:1.4rem;font-weight:700;color:var(--brand-dark)"><?= $counts[$sk] ?? 0 ?></div>
                <div style="font-size:0.75rem;color:var(--brand-muted);font-weight:500"><?= $sv['label'] ?></div>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<form method="GET" action="?">
    <input type="hidden" name="page" value="admin_peminjaman">
    <div class="search-bar">
        <input type="text" class="glass-input" name="search" placeholder="Cari nama anggota / judul buku..." value="<?= e($search) ?>">
        <select name="status" class="glass-input" style="max-width:210px">
            <option value="">Semua Status</option>
            <option value="menunggu"          <?= $status === 'menunggu'          ? 'selected' : '' ?>>&#9203; Menunggu</option>
            <option value="dipinjam"          <?= $status === 'dipinjam'          ? 'selected' : '' ?>>&#128214; Dipinjam</option>
            <option value="pengajuan_kembali" <?= $status === 'pengajuan_kembali' ? 'selected' : '' ?>>&#128260; Pengajuan Kembali</option>
            <option value="dikembalikan"      <?= $status === 'dikembalikan'      ? 'selected' : '' ?>>&#9989; Dikembalikan</option>
        </select>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i></button>
        <?php if ($search || $status): ?><a href="?page=admin_peminjaman" class="btn btn-outline">Reset</a><?php endif; ?>
    </div>
</form>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr><th>#</th><th>Anggota</th><th>Buku</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Denda</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php if (empty($peminjaman)): ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--brand-muted)">Tidak ada data.</td></tr>
        <?php else: ?>
        <?php foreach ($peminjaman as $i => $p): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= e($p['nama_anggota']) ?></td>
                <td><?= e($p['judul_buku']) ?></td>
                <td><?= e($p['tanggal_pinjam']) ?></td>
                <td><?= e($p['tanggal_kembali'] ?? '-') ?></td>
                <td>
                    <?php if (in_array($p['status'], ['dipinjam', 'pengajuan_kembali'])): ?>
                        <?php $runningDenda = hitungDenda($p['tanggal_kembali']); ?>
                        <strong style="color:var(--brand-danger)">Rp <?= number_format($runningDenda, 0, ',', '.') ?></strong>
                        <br><small style="color:var(--brand-muted);font-size:0.75rem;font-weight:500">(Berjalan)</small>
                    <?php else: ?>
                        <strong style="color:var(--brand-danger)">Rp <?= number_format($p['denda'] ?? 0, 0, ',', '.') ?></strong>
                    <?php endif; ?>
                </td>
                <td><span class="badge badge-<?= e($p['status']) ?>"><?= e($p['status']) ?></span></td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap">
                    <?php if ($p['status'] === 'menunggu'): ?>
                        <form action="?page=admin_approve_pinjam" method="POST" onsubmit="return confirm('Setujui peminjaman ini? Stok buku akan dikurangi.')">
                            <input type="hidden" name="id_peminjaman" value="<?= (int)$p['id_peminjaman'] ?>">
                            <button type="submit" class="btn btn-success" style="padding:6px 12px">
                                <i class="fa-solid fa-check"></i> Setujui
                            </button>
                        </form>
                        <form action="?page=admin_tolak_pinjam" method="POST" onsubmit="return confirm('Tolak dan hapus pengajuan ini?')">
                            <input type="hidden" name="id_peminjaman" value="<?= (int)$p['id_peminjaman'] ?>">
                            <button type="submit" class="btn btn-danger" style="padding:6px 12px">
                                <i class="fa-solid fa-xmark"></i> Tolak
                            </button>
                        </form>
                    <?php elseif ($p['status'] === 'pengajuan_kembali'): ?>
                        <form action="?page=admin_verif_kembali" method="POST" onsubmit="return confirm('Verifikasi pengembalian ini? Stok buku akan bertambah.')">
                            <input type="hidden" name="id_peminjaman" value="<?= (int)$p['id_peminjaman'] ?>">
                            <button type="submit" class="btn btn-success" style="padding:6px 12px">
                                <i class="fa-solid fa-rotate-left"></i> Verifikasi
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="?page=admin_peminjaman_edit&id=<?= (int)$p['id_peminjaman'] ?>" class="btn btn-warning" style="padding:6px 12px">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form action="?page=admin_peminjaman_delete" method="POST" onsubmit="return confirm('Hapus data peminjaman ini?')">
                            <input type="hidden" name="id_peminjaman" value="<?= (int)$p['id_peminjaman'] ?>">
                            <button type="submit" class="btn btn-danger" style="padding:6px 12px">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../_layout_foot.php'; ?>
