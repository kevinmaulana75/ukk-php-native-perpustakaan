<?php
// pages/register.php
if (isLoggedIn()) redirect('?page=dashboard');

$errorsRaw = hasFlash('errors') ? getFlash('errors') : '';
$errors    = $errorsRaw ? explode('||', $errorsRaw) : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Bookavy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body class="auth-body">
    <div class="auth-card wide">
        <div class="auth-logo">
            <i class="fa-solid fa-book-open"></i> Bookavy
        </div>
        <div class="auth-subtitle"></div>

        <?php if ($errors): ?>
            <div class="alert alert-error" style="text-align:left">
                <strong>Oops! Tolong periksa form Anda:</strong>
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="?page=register" method="POST">
            <div class="form-group">
                <label class="form-label" for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" class="glass-input" value="<?= old('nama') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="kelas">Kelas</label>
                <input type="text" id="kelas" name="kelas" class="glass-input" value="<?= old('kelas') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="alamat">Alamat Lengkap</label>
                <textarea id="alamat" name="alamat" class="glass-input" style="height:80px" required><?= old('alamat') ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="glass-input" value="<?= old('username') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="glass-input" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Daftar</button>
        </form>

        <div class="mt-link">
            Sudah punya akun? <a href="?page=login">Masuk di sini</a>
        </div>
    </div>
</body>
</html>
