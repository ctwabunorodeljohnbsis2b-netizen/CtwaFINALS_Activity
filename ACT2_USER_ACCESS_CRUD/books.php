<?php
require 'config.php';
require 'layout.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id       = (int)($_POST['id'] ?? 0);
        $title    = trim($_POST['title'] ?? '');
        $author   = trim($_POST['author'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $qty      = max(0, (int)($_POST['quantity'] ?? 0));

        if ($title !== '' && $author !== '' && $category !== '') {
            if ($id) {
                $s = $pdo->prepare("UPDATE books SET title=?, author=?, category=?, quantity=? WHERE id=?");
                $s->execute([$title, $author, $category, $qty, $id]);
            } else {
                $s = $pdo->prepare("INSERT INTO books(title, author, category, quantity) VALUES(?,?,?,?)");
                $s->execute([$title, $author, $category, $qty]);
            }
            flash('Book saved.');
        } else {
            flash('Please complete all book fields.');
        }
    } elseif ($action === 'delete') {
        try {
            $s = $pdo->prepare("DELETE FROM books WHERE id=?");
            $s->execute([(int)$_POST['id']]);
            flash('Book deleted.');
        } catch (PDOException $e) {
            flash('This book has borrowing records and cannot be deleted.');
        }
    }

    header('Location: books.php');
    exit;
}

$edit = null;
if (isset($_GET['edit']) && is_admin()) {
    $s = $pdo->prepare("SELECT * FROM books WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch();
}
$books = $pdo->query("SELECT * FROM books ORDER BY id DESC")->fetchAll();

page_start('Books', 'books.php');
?>
<h1>Books</h1>
<?php show_flash(); ?>

<?php if (is_admin()): ?>
<div class="panel">
    <h2><?= $edit ? 'Edit book' : 'Add a book' ?></h2>
    <form class="form-grid" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= h($edit['id'] ?? '') ?>">

        <label>Title
            <input name="title" required value="<?= h($edit['title'] ?? '') ?>">
        </label>
        <label>Author
            <input name="author" required value="<?= h($edit['author'] ?? '') ?>">
        </label>
        <label>Category
            <input name="category" required value="<?= h($edit['category'] ?? '') ?>">
        </label>
        <label>Quantity
            <input type="number" min="0" name="quantity" required value="<?= h($edit['quantity'] ?? 1) ?>">
        </label>

        <button>Save book</button>
        <?php if ($edit): ?>
            <a class="button secondary" href="books.php">Cancel</a>
        <?php endif; ?>
    </form>
</div>
<?php endif; ?>

<div class="panel">
    <h2>Book list</h2>
    <div class="table-wrap">
        <table>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>Quantity</th>
                <?php if (is_admin()): ?><th>Actions</th><?php endif; ?>
            </tr>
            <?php foreach ($books as $b): ?>
            <tr>
                <td><?= h($b['title']) ?></td>
                <td><?= h($b['author']) ?></td>
                <td><?= h($b['category']) ?></td>
                <td><?= $b['quantity'] ?></td>
                <?php if (is_admin()): ?>
                <td class="actions">
                    <a href="books.php?edit=<?= $b['id'] ?>">Edit</a>
                    <form method="post" onsubmit="return confirm('Delete this book?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
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
