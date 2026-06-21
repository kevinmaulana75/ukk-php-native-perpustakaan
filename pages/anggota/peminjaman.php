<?php
// pages/peminjaman.php — Anggota: view & return loans
requireLogin();
if (!isAnggota()) redirect('?page=dashboard');

$pdo    = db();
$userId = $_SESSION['user_id'];
$pageTitle = 'Peminjaman Saya';
$activeNav = 'peminjaman';

$stmt = $pdo->prepare('
    SELECT p.*, b.judul_buku, b.pengarang, b.gambar
    FROM peminjaman p
    JOIN buku b ON p.id_buku = b.id_buku
    WHERE p.id_anggota = ?
    ORDER BY FIELD(p.status,"menunggu","dipinjam","pengajuan_kembali","dikembalikan"), p.tanggal_pinjam DESC
');
$stmt->execute([$userId]);
$loans = $stmt->fetchAll();

$active_loans = array_filter($loans, fn($l) => in_array($l['status'], ['menunggu','dipinjam','pengajuan_kembali']));
$past_loans   = array_filter($loans, fn($l) => $l['status'] === 'dikembalikan');

$success = hasFlash('success') ? getFlash('success') : '';
$error   = hasFlash('error')   ? getFlash('error')   : '';

include __DIR__ . '/../_layout_head.php';
?>
<h1 class="page-title">Peminjaman Saya</h1>

<?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

<h2 style="font-size:1.1rem;font-weight:600;margin-bottom:12px;color:var(--brand-muted)">
    <i class="fa-solid fa-book-open-reader"></i> Aktif & Menunggu
</h2>
<?php if (empty($active_loans)): ?>
    <div class="empty-state" style="padding:32px">Tidak ada buku yang sedang dipinjam atau dalam proses.</div>
<?php else: ?>
<div class="table-card" style="margin-bottom:32px">
    <table class="data-table">
        <thead>
            <tr><th>Judul Buku</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th><th>Denda Berjalan</th><th>Catatan</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php foreach ($active_loans as $l): ?>
            <tr>
                <td><strong><?= e($l['judul_buku']) ?></strong><br><small style="color:var(--brand-muted)"><?= e($l['pengarang']) ?></small></td>
                <td><?= e($l['tanggal_pinjam']) ?></td>
                <td><?= e($l['tanggal_kembali'] ?? '-') ?></td>
                <td><span class="badge badge-<?= e($l['status']) ?>"><?= e($l['status']) ?></span></td>
                <td>
                    <?php 
                    $runningDenda = ($l['status'] === 'dipinjam' || $l['status'] === 'pengajuan_kembali') ? hitungDenda($l['tanggal_kembali']) : 0;
                    if ($runningDenda > 0): ?>
                        <strong style="color:var(--brand-danger)">Rp <?= number_format($runningDenda, 0, ',', '.') ?></strong>
                    <?php else: ?>
                        <span style="color:var(--brand-muted)">-</span>
                    <?php endif; ?>
                </td>
                <td><?= e($l['catatan'] ?? '-') ?></td>
                <td>
                    <?php if ($l['status'] === 'dipinjam'): ?>
                        <form action="?page=kembalikan" method="POST" onsubmit="return confirm('Ajukan pengembalian buku ini?')">
                            <input type="hidden" name="id_peminjaman" value="<?= (int)$l['id_peminjaman'] ?>">
                            <button type="submit" class="btn btn-success" style="padding:6px 14px">
                                <i class="fa-solid fa-rotate-left"></i> Kembalikan
                            </button>
                        </form>
                    <?php elseif ($l['status'] === 'menunggu'): ?>
                        <div style="display:flex;flex-direction:column;gap:4px">
                            <span style="font-size:0.8rem;color:#e17055;font-weight:500"><i class="fa-solid fa-clock"></i> Menunggu persetujuan</span>
                            <form action="?page=anggota_batal_pinjam" method="POST" onsubmit="return confirm('Batalkan pengajuan peminjaman ini? Stok buku akan kembali.')">
                                <input type="hidden" name="id_peminjaman" value="<?= (int)$l['id_peminjaman'] ?>">
                                <button type="submit" class="btn btn-danger" style="padding:4px 10px;font-size:0.8rem">
                                    <i class="fa-solid fa-xmark"></i> Batalkan
                                </button>
                            </form>
                        </div>
                    <?php elseif ($l['status'] === 'pengajuan_kembali'): ?>
                        <span style="font-size:0.8rem;color:#6c5ce7;font-weight:500"><i class="fa-solid fa-rotate-left"></i> Menunggu verifikasi admin</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<h2 style="font-size:1.1rem;font-weight:600;margin-bottom:12px;color:var(--brand-muted)">
    <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Pengembalian
</h2>
<?php if (empty($past_loans)): ?>
    <div class="empty-state" style="padding:32px">Belum ada riwayat pengembalian.</div>
<?php else: ?>
<div class="table-card">
    <table class="data-table">
        <thead>
            <tr><th>Judul Buku</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th><th>Denda Terbayar</th><th>Catatan</th></tr>
        </thead>
        <tbody>
        <?php foreach ($past_loans as $l): ?>
            <tr>
                <td><strong><?= e($l['judul_buku']) ?></strong></td>
                <td><?= e($l['tanggal_pinjam']) ?></td>
                <td><?= e($l['tanggal_kembali'] ?? '-') ?></td>
                <td><span class="badge badge-dikembalikan">dikembalikan</span></td>
                <td><strong style="color:var(--brand-danger)">Rp <?= number_format($l['denda'] ?? 0, 0, ',', '.') ?></strong></td>
                <td><?= e($l['catatan'] ?? '-') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../_layout_foot.php'; ?>
