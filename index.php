<?php
// Environment toggle: set to false in production
$isDev = true;

// Hardened Logger Configuration
$logFile = '/var/log/php_crud_errors.log';

if ($isDev) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', $logFile);

    if (!file_exists($logFile)) {
        touch($logFile);
        chmod($logFile, 0640); // Owner read/write, group read
        chown($logFile, 'www-data'); // Or 'apache' for CentOS
    }
}

// Database credentials
$host = 'localhost';
$db = 'studentdb';
$user = 'devops';
$pass = 'password';

// Initialize variables
$edit_mode = false;
$edit_id = 0;
$edit_name = '';
$edit_email = '';

try {
    $dsn = "mysql:host=$host;dbname=$db";
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle Delete
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $stmt = $conn->prepare("DELETE FROM students WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: index.php");
        exit;
    }

    // Handle Edit
    if (isset($_GET['edit'])) {
        $edit_mode = true;
        $edit_id = intval($_GET['edit']);
        $stmt = $conn->prepare("SELECT * FROM students WHERE id = :id");
        $stmt->execute(['id' => $edit_id]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $edit_name = $row['name'];
            $edit_email = $row['email'];
        }
    }

    // Handle Insert/Update
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];

        if (!empty($_POST['id'])) {
            $id = intval($_POST['id']);
            $stmt = $conn->prepare("UPDATE students SET name = :name, email = :email WHERE id = :id");
            $stmt->execute(['name' => $name, 'email' => $email, 'id' => $id]);
        } else {
            $stmt = $conn->prepare("INSERT INTO students (name, email) VALUES (:name, :email)");
            $stmt->execute(['name' => $name, 'email' => $email]);
        }

        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    if ($isDev) {
        die('Connection failed: ' . $e->getMessage());
    } else {
        error_log('DB connection failed: ' . $e->getMessage());
        die('We are experiencing issues. Please try again later.');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP CRUD App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px 12px;
            text-align: left;
        }
        caption {
            font-weight: bold;
            margin-bottom: 8px;
        }
        input, button {
            margin: 5px 0;
            padding: 6px;
        }
        h1, h2, h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <h1>TEAM 22 🤝</h1>
    <h1>PHP CRUD App - Almost a DevOps Engineer!</h1>
    <h2>This application was built by WALE, RUKKY & TIMI</h2>

    <h3><?= $edit_mode ? 'Edit Student' : 'Add New Student'; ?></h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $edit_mode ? $edit_id : ''; ?>">
        <input type="text" name="name" placeholder="Name" required value="<?= htmlspecialchars($edit_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($edit_email ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit"><?= $edit_mode ? 'Update' : 'Create'; ?></button>
        <?php if ($edit_mode): ?>
            <a href="index.php">Cancel</a>
        <?php endif; ?>
    </form>

    <h3>Student List</h3>
    <table>
        <caption>List of all registered students</caption>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>
        <?php
        $stmt = $conn->query("SELECT * FROM students");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = $row['id'];
            $name = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
            echo "<tr>
                    <td>$id</td>
                    <td>$name</td>
                    <td>$email</td>
                    <td>
                        <a href='?edit=$id'>Edit</a> |
                        <a href='?delete=$id' onclick=\"return confirm('Are you sure?')\">Delete</a>
                    </td>
                </tr>";
        }
        ?>
    </table>
</body>
</html>
