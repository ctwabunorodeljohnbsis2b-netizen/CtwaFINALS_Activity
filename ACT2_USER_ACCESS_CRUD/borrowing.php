<?php
require 'config.php';
require 'layout.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $member = trim($_POST['member_name'] ?? '');
        $book   = (int)($_POST['book_id'] ?? 0);
        $borrow = $_POST['borrow_date'] ?? '';
        $due    = $_POST['due_date'] ?? '';

        if ($member && $book && $borrow && $due && $due >= $borrow) {
            $s = $pdo->prepare("INSERT INTO borrow_records(member_name, book_id, borrow_date, due_date) VALUES(?,?,?,?)");
            $s->execute([$member, $book, $borrow, $due]);
            flash('Borrowing record added.');
        } else {
            flash('Enter valid borrowing details.');
        }
    } elseif ($action === 'return') {
        $s = $pdo->prepare("UPDATE borrow_records SET status='Returned' WHERE id=?");
        $s->execute([(int)$_POST['id']]);
        flash('Book marked as returned.');
    } elseif ($action === 'delete') {
        $s = $pdo->prepare("DELETE FROM borrow_records WHERE id=?");
        $s->execute([(int)$_POST['id']]);
        flash('Record deleted.');
    }

    header('Location: borrowing.php');
    exit;
}

$books   = $pdo->query("SELECT id, title FROM books ORDER BY title")->fetchAll();
$records = $pdo->query(
    "SELECT br.*, b.title
     FROM borrow_records br
     JOIN books b ON b.id = br.book_id
     ORDER BY br.id DESC"
)->fetchAll();

page_start('Borrowing', 'borrowing.php');
?>
<h1>Borrowing Records</h1>
<?php show_flash(); ?>

<?php if (is_admin()): ?>
<div class="panel">
    <h2>Add borrowing record</h2>
    <form class="form-grid" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">

        <label>Member name
            <input name="member_name" required>
        </label>
        <label>Book
            <select name="book_id" required>
                <option value="">Select book</option>
                <?php foreach ($books as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= h($b['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Borrow date
            <input type="date" name="borrow_date" required>
        </label>
        <label>Due date
            <input type="date" name="due_date" required>
        </label>

        <button>Add record</button>
    </form>
</div>
<?php endif; ?>

<div class="panel">
    <h2>Records</h2>
    <div class="table-wrap">
        <table>
            <tr>
                <th>Member</th>
                <th>Book</th>
                <th>Borrow date</th>
                <th>Due date</th>
                <th>Status</th>
                <?php if (is_admin()): ?><th>Actions</th><?php endif; ?>
            </tr>
            <?php foreach ($records as $r): ?>
            <tr>
                <td><?= h($r['member_name']) ?></td>
                <td><?= h($r['title']) ?></td>
                <td><?= h($r['borrow_date']) ?></td>
                <td><?= h($r['due_date']) ?></td>
                <td><?= h($r['status']) ?></td>
                <?php if (is_admin()): ?>
                <td class="actions">
                    <?php if ($r['status'] === 'Borrowed'): ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="return">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button class="small">Mark returned</button>
                    </form>
                    <?php endif; ?>
                    <form method="post" onsubmit="return confirm('Delete this record?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button class="link danger">Delete</button>
                    </form>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<?php page_end(); ?>
