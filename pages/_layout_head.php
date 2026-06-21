<?php
// pages/_layout_head.php — Shared HTML head + sidebar (included by other pages)
// Usage: include __DIR__ . '/_layout_head.php';
// Variables used: $pageTitle (string), $activeNav (string key)

$userName = $_SESSION['user_name'] ?? 'Tamu';
$role     = $_SESSION['role'] ?? '';

function navLink(string $page, string $label, string $icon, string $active): string {
    $cls = $active === $page ? 'nav-link active' : 'nav-link';
    return '<a href="?page=' . $page . '" class="' . $cls . '">'
         . '<i class="fa-solid fa-' . $icon . '"></i> ' . $label . '</a>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Bookavy') ?> - Bookavy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body style="overflow:hidden">
<div class="app-shell">
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="?page=dashboard" class="sidebar-logo no-underline">
            <i class="fa-solid fa-book-open"></i> Bookavy
        </a>
        <div class="sidebar-avatar">
            <img src="https://placehold.co/100x100/e0e0e0/333333?text=U" alt="Avatar">
            <div class="sidebar-username"><?= e($userName) ?></div>
        </div>
        <nav class="sidebar-nav">
            <?php if ($role === 'admin'): ?>
                <?= navLink('dashboard',           'Dashboard Admin',  'house',            $activeNav ?? '') ?>
                <?= navLink('anggota',             'Data Anggota',     'users',            $activeNav ?? '') ?>
                <?= navLink('admin_buku',          'Data Buku',        'swatchbook',       $activeNav ?? '') ?>
                <?= navLink('admin_peminjaman',    'Data Peminjaman',  'book-open-reader', $activeNav ?? '') ?>
            <?php elseif ($role === 'anggota'): ?>
                <?= navLink('dashboard',  'Dashboard',        'house',            $activeNav ?? '') ?>
                <?= navLink('buku',       'Daftar Buku',      'book',             $activeNav ?? '') ?>
                <?= navLink('peminjaman', 'Peminjaman Saya',  'book-open-reader', $activeNav ?? '') ?>
            <?php else: ?>
                <?= navLink('dashboard', 'Dashboard', 'house', $activeNav ?? '') ?>
                <?= navLink('buku',      'Daftar Buku', 'book', $activeNav ?? '') ?>
            <?php endif; ?>

            <?php if (isLoggedIn()): ?>
                <a href="?page=logout" class="nav-link danger mt-nav">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            <?php else: ?>
                <a href="?page=login" class="nav-link mt-nav">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </a>
            <?php endif; ?>
        </nav>
    </div>
    <!-- Main Content Start -->
    <div class="main-content">
