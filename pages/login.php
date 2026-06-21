<?php
// pages/login.php
if (isLoggedIn()) redirect('?page=dashboard');

$error   = hasFlash('error')   ? getFlash('error')   : '';
$success = hasFlash('success') ? getFlash('success') : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bookavy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body class="auth-body">
    <div class="auth-card">
        <div class="auth-logo">
            <i class="fa-solid fa-book-open"></i> Bookavy
        </div>
        <div class="auth-subtitle">Selamat Datang!</div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <form action="?page=login" method="POST">
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="glass-input"
                        value="<?= old('username') ?>" placeholder="Masukkan username" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="glass-input"
                        placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Masuk</button>
        </form>

        <div class="mt-link">
            Belum punya akun? <a href="?page=register">Daftar di sini</a>
        </div>
    </div>
</body>
</html>
