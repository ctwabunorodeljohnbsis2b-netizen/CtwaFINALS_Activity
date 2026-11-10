<?php
require 'config.php';
require 'layout.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id    = (int)($_POST['id'] ?? 0);
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($name && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($id) {
                $s = $pdo->prepare("UPDATE users SET full_name=?, email=? WHERE id=? AND role='user'");
                $s->execute([$name, $email, $id]);
            } else {
                $s = $pdo->prepare("INSERT INTO users(full_name, email, password, role, status) VALUES(?,?,?,'user','approved')");
                $s->execute([$name, $email, password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT)]);
            }
            flash('Member saved.');
        } else {
            flash('Enter a valid name and email.');
        }
    } elseif ($action === 'delete') {
        $s = $pdo->prepare("DELETE FROM users WHERE id=? AND role='user'");
        $s->execute([(int)$_POST['id']]);
        flash('Member deleted.');
    }

    header('Location: members.php');
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT id, full_name, email FROM users WHERE id=? AND role='user'");
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch();
}
$members = $pdo->query(
    "SELECT id, full_name, email, status, created_at
     FROM users WHERE role='user' ORDER BY id DESC"
)->fetchAll();

page_start('Members', 'members.php');
?>
<h1>Members</h1>
<?php show_flash(); ?>

<div class="panel">
    <h2><?= $edit ? 'Edit member' : 'Add member' ?></h2>
    <form class="form-grid" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= h($edit['id'] ?? '') ?>">

        <label>Full name
            <input name="name" required value="<?= h($edit['full_name'] ?? '') ?>">
        </label>
        <label>Email
            <input type="email" name="email" required value="<?= h($edit['email'] ?? '') ?>">
        </label>

        <button>Save member</button>
    </form>
</div>

<div class="panel">
    <h2>Member list</h2>
    <div class="table-wrap">
        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Account status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($members as $m): ?>
            <tr>
                <td><?= h($m['full_name']) ?></td>
                <td><?= h($m['email']) ?></td>
                <td><?= h($m['status']) ?></td>
                <td class="actions">
                    <a href="?edit=<?= $m['id'] ?>">Edit</a>
                    <form method="post" onsubmit="return confirm('Delete member?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                        <button class="link danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<?php page_end(); ?>
