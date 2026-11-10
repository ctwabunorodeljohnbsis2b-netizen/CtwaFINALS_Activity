<?php
require 'config.php';
require 'layout.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id     = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'status') {
        $status = $_POST['status'] ?? 'pending';
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $s = $pdo->prepare("UPDATE users SET status=? WHERE id=? AND role='user'");
            $s->execute([$status, $id]);
            flash('Account status updated.');
        }
    } elseif ($action === 'delete') {
        $s = $pdo->prepare("DELETE FROM users WHERE id=? AND role='user'");
        $s->execute([$id]);
        flash('User account deleted.');
    }

    header('Location: users.php');
    exit;
}

$users = $pdo->query(
    "SELECT id, full_name, email, status, created_at
     FROM users WHERE role='user' ORDER BY id DESC"
)->fetchAll();

page_start('User Accounts', 'users.php');
?>
<h1>User Accounts</h1>
<?php show_flash(); ?>

<div class="panel">
    <div class="table-wrap">
        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Registered</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= h($u['full_name']) ?></td>
                <td><?= h($u['email']) ?></td>
                <td><?= h($u['status']) ?></td>
                <td><?= h($u['created_at']) ?></td>
                <td>
                    <form class="inline-form" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="status">
                        <select name="status">
                            <?php foreach (['pending', 'approved', 'rejected'] as $st): ?>
                                <option value="<?= $st ?>" <?= $u['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="small">Update</button>
                    </form>
                    <form method="post" onsubmit="return confirm('Delete this account?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button class="link danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<?php page_end(); ?>
