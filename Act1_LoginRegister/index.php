<?php
require 'config.php';

// ---------------------------------------------------------------
// Login handling
// ---------------------------------------------------------------

/**
 * Check credentials and the chosen role.
 * Returns an error message, or '' when the login is valid.
 */
function attempt_login(PDO $pdo, string $email, string $password, string $loginAs): string
{
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return 'Invalid email or password.';
    }

    if ($user['status'] !== 'approved') {
        return $user['status'] === 'pending'
            ? 'Your account is waiting for admin approval.'
            : 'Your account was not approved.';
    }

    // The role chosen on the form must match the role stored in the database.
    if ($user['role'] !== $loginAs) {
        return $user['role'] === 'admin'
            ? 'This is an administrator account. Choose "Admin" to sign in.'
            : 'This account is not an administrator. Choose "User" to sign in.';
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'   => $user['id'],
        'name' => $user['full_name'],
        'role' => $user['role'],
    ];
    return '';
}

$error = '';
$selectedRole = 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $loginAs  = $_POST['login_as'] ?? 'user';
    $selectedRole = in_array($loginAs, ['admin', 'user'], true) ? $loginAs : 'user';

    $error = attempt_login($pdo, $email, $password, $selectedRole);
    if ($error === '') {
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Library Portal | Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <h1>Library Portal</h1>
        <p class="muted">Sign in to continue</p>

        <?php show_flash(); ?>
        <?php if ($error): ?>
            <p class="error"><?= h($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>

            <label>Sign in as</label>
            <div class="role-switch">
                <label class="role-option">
                    <input type="radio" name="login_as" value="user" <?= $selectedRole === 'user' ? 'checked' : '' ?>>
                    <span>User</span>
                </label>
                <label class="role-option">
                    <input type="radio" name="login_as" value="admin" <?= $selectedRole === 'admin' ? 'checked' : '' ?>>
                    <span>Admin</span>
                </label>
            </div>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button>Sign in</button>
        </form>

        <p class="muted">New here? <a href="register.php">Create an account</a></p>
    </div>
</body>
</html>
