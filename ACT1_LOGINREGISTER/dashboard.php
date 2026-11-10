<?php
require 'config.php';
require 'layout.php';
require_login();

$bookCount     = (int)$pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$borrowedCount = (int)$pdo->query("SELECT COUNT(*) FROM borrow_records WHERE status='Borrowed'")->fetchColumn();
$userCount     = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

page_start('Dashboard', 'dashboard.php');
?>
<h1>Dashboard</h1>
<p class="muted">Library activity overview.</p>
<?php show_flash(); ?>

<div class="stats">
    <div class="stat"><span>Total books</span><strong><?= $bookCount ?></strong></div>
    <div class="stat"><span>Currently borrowed</span><strong><?= $borrowedCount ?></strong></div>
    <div class="stat"><span>Registered accounts</span><strong><?= $userCount ?></strong></div>
</div>

<div class="panel">
    <h2>Welcome to the Library Portal</h2>
    <p>Manage books, members, and borrowing records from the sidebar.</p>
</div>
<?php page_end(); ?>
