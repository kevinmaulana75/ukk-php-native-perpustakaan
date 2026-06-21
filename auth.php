<?php
// Auth helper functions — always include config.php before this file

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isAnggota(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'anggota';
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('?page=login');
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        redirect('?page=login');
    }
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

function flash(string $key, string $message): void {
    $_SESSION['flash'][$key] = $message;
}

function hasFlash(string $key): bool {
    return isset($_SESSION['flash'][$key]);
}

function getFlash(string $key): string {
    $msg = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function e(mixed $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function old(string $key, string $default = ''): string {
    return e($_SESSION['old'][$key] ?? $default);
}

function storeOld(array $data): void {
    $_SESSION['old'] = $data;
}

function clearOld(): void {
    unset($_SESSION['old']);
}

/**
 * Menghitung denda keterlambatan berdasarkan tanggal hari ini.
 * Default tarif: Rp 1.000 / hari.
 */
function hitungDenda(string $tgl_kembali_seharusnya): int {
    $tgl_sekarang = date('Y-m-d');
    $denda_per_hari = 1000;

    if (strtotime($tgl_sekarang) > strtotime($tgl_kembali_seharusnya)) {
        $diff = (strtotime($tgl_sekarang) - strtotime($tgl_kembali_seharusnya)) / 86400;
        return (int)max(0, floor($diff) * $denda_per_hari);
    }
    return 0;
}
