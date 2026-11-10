<?php
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name  = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
        $error = 'Enter a name, valid email, and password with at least 8 characters.';
    } else {
        // The very first account becomes the approved administrator.
        $count  = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $role   = $count === 0 ? 'admin' : 'user';
        $status = $count === 0 ? 'approved' : 'pending';

        try {
            $stmt = $pdo->prepare("INSERT INTO users(full_name, email, password, role, status) VALUES(?,?,?,?,?)");
            $stmt->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), $role, $status]);
            flash($role === 'admin'
                ? 'First account created as administrator. Please sign in.'
                : 'Registration submitted. Wait for admin approval.');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'That email address is already registered.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Register | Library Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <h1>Create account</h1>
        <p class="muted">The first registered account becomes admin.</p>

        <?php if ($error): ?>
            <p class="error"><?= h($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>

            <label>Full name</label>
            <input name="full_name" required maxlength="120">

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" minlength="8" required>

            <button>Create account</button>
        </form>

        <p class="muted"><a href="index.php">Back to login</a></p>
    </div>
</body>
</html>
