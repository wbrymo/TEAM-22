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
    <table>
        <caption>List of all registered students</caption>
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
