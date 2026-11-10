<?php

function page_start(string $title, string $active = ''): void
{
    $user = $_SESSION['user'];

    $links = [
        'dashboard.php' => 'Dashboard',
        'books.php'     => 'Books',
        'borrowing.php' => 'Borrowing Records',
    ];
    if (is_admin()) {
        $links['members.php'] = 'Members';
        $links['users.php']   = 'User Accounts';
    }
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= h($title) ?> | Library Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="dashboard.php">Library Portal</a>
        <div>
            <?= h($user['name']) ?>
            <span class="badge <?= h($user['role']) ?>"><?= h(ucfirst($user['role'])) ?></span>
            · <a href="logout.php">Logout</a>
        </div>
    </header>
    <div class="layout">
        <aside class="sidebar">
            <?php foreach ($links as $file => $label): ?>
                <a href="<?= $file ?>"<?= $file === $active ? ' class="active"' : '' ?>><?= h($label) ?></a>
            <?php endforeach; ?>
        </aside>
        <main class="content">
<?php
}

function page_end(): void
{
    ?>
        </main>
    </div>
</body>
</html>
<?php
}
