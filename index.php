<?php
session_start();
include 'db_connect.php';

$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);

$edit = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM students WHERE id=:id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

$stmt = $conn->query("SELECT * FROM students WHERE is_deleted=0 ORDER BY created_at DESC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$archived_stmt = $conn->query("SELECT * FROM students WHERE is_deleted=1 ORDER BY created_at DESC");
$archived = $archived_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Record Module</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Student Record Management System</h1>

<?php if ($message): ?>
    <div class="msg <?= $message['type'] ?>"><?= $message['text'] ?></div>
<?php endif; ?>

<div class="card">
    <h2><?= $edit ? 'Edit Student Record' : 'Add New Student' ?></h2>
    <form action="process.php" method="POST">
        <?php if ($edit): ?>
            <input type="hidden" name="record_id" value="<?= $edit['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="student_id">Student ID</label>
            <input type="text" id="student_id" name="student_id" 
                   value="<?= $edit ? htmlspecialchars($edit['student_id']) : '' ?>" required>
        </div>

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" 
                   value="<?= $edit ? htmlspecialchars($edit['name']) : '' ?>" required>
        </div>

        <div class="form-group">
            <label for="program">Program / Course</label>
            <input type="text" id="program" name="program" 
                   value="<?= $edit ? htmlspecialchars($edit['program']) : '' ?>" required>
        </div>

        <button type="submit" name="<?= $edit ? 'update' : 'create' ?>" class="btn-primary">
            <?= $edit ? 'Update Record' : 'Save Record' ?>
        </button>
        
        <?php if ($edit): ?>
            <a href="index.php" style="margin-left:1rem; color:#666;">Cancel Edit</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <h2>Active Student Records</h2>
    <?php if (empty($students)): ?>
        <p>No records yet.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Program</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($students as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['student_id']) ?></td>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td><?= htmlspecialchars($s['program']) ?></td>
                <td><?= $s['created_at'] ?></td>
                <td>
                    <a href="index.php?action=edit&id=<?= $s['id'] ?>" class="btn-edit">Edit</a>
                    <a href="process.php?action=delete&id=<?= $s['id'] ?>" class="btn-delete"
                       onclick="return confirm('Archive this record? It can be restored later.')">Archive</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<div class="card archived">
    <h2>Archived Records</h2>
    <?php if (empty($archived)): ?>
        <p>No archived records.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Program</th>
                <th>Archived On</th>
                <th>Action</th>
            </tr>
            <?php foreach ($archived as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['student_id']) ?></td>
                <td><?= htmlspecialchars($a['name']) ?></td>
                <td><?= htmlspecialchars($a['program']) ?></td>
                <td><?= $a['updated_at'] ?></td>
                <td>
                    <a href="process.php?action=restore&id=<?= $a['id'] ?>" class="btn-restore"
                       onclick="return confirm('Restore this record?')">Restore</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Data Flow Explanation</h2>
    <ol>
        <li><strong>User Input → Frontend Form:</strong> User fills Student ID, Name, Program → submits via POST</li>
        <li><strong>Controller (process.php):</strong> Receives request, cleans/validates input → prevents invalid/duplicate entries</li>
        <li><strong>Database INSERT:</strong> Valid data saved to <code>students</code> table; <code>is_deleted=0</code> = active</li>
        <li><strong>Read → Display:</strong> <code>index.php</code> queries active records → renders in table</li>
        <li><strong>Update:</strong> Load record → edit → POST → UPDATE row → refresh list</li>
        <li><strong>Delete (Archive):</strong> Does NOT DELETE row; sets <code>is_deleted=1</code> → moved to Archived section → can Restore</li>
        <li><strong>Feedback:</strong> Session messages show success/error after every action</li>
    </ol>
</div>

</body>
</html>