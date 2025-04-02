<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'devops';
$pass = 'password';
$db = 'studentdb';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM students WHERE id=$id");
    header("Location: index.php");
    exit;
}

// Handle Edit
$edit_mode = false;
$edit_id = 0;
$edit_name = '';
$edit_email = '';
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM students WHERE id=$edit_id");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $edit_name = $row['name'];
        $edit_email = $row['email'];
    }
}

// Handle Insert or Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    if (isset($_POST['id']) && $_POST['id']) {
        // Update
        $id = intval($_POST['id']);
        $conn->query("UPDATE students SET name='$name', email='$email' WHERE id=$id");
    } else {
        // Create
        $conn->query("INSERT INTO students (name, email) VALUES ('$name', '$email')");
    }
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP CRUD App</title>
</head>
<body>
    <h1>PHP CRUD App - Almost a DevOps Engineer!</h1>
    <h2>This application was built by WALE, RUKKY & TIMI</h2>

    <h3><?php echo $edit_mode ? 'Edit Student' : 'Add New Student'; ?></h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $edit_mode ? $edit_id : ''; ?>">
        <input type="text" name="name" placeholder="Name" required value="<?php echo $edit_name; ?>">
        <input type="email" name="email" placeholder="Email" required value="<?php echo $edit_email; ?>">
        <button type="submit"><?php echo $edit_mode ? 'Update' : 'Create'; ?></button>
        <?php if ($edit_mode): ?>
            <a href="index.php">Cancel</a>
        <?php endif; ?>
    </form>

    <h3>Student List</h3>
    <table border="1" cellpadding="6">
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>
        <?php
        $result = $conn->query("SELECT * FROM students");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['email']}</td>
                    <td>
                        <a href='?edit={$row['id']}'>Edit</a> |
                        <a href='?delete={$row['id']}' onclick=\"return confirm('Are you sure?')\">Delete</a>
                    </td>
                </tr>";
        }
        ?>
    </table>
</body>
</html>

<br>
CeeyIT!
