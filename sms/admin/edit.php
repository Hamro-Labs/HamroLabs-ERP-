<?php
session_start();
include "config/dbcon.php";

/* ------------------------------------
   UPDATE USER
------------------------------------ */
if (isset($_POST['update_btn'])) {
    $id = intval($_POST['user_id']);
    $name = $_POST['name'];
    $email = $_POST['email'];
    $faculty = $_POST['faculty'];
    $year_part = $_POST['year_part'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // If password is empty, don't update it
    if ($password == "") {
        // Simple query without prepared statement
        $update_sql = "UPDATE user SET name='$name', email='$email', faculty='$faculty', year_part='$year_part', address='$address', contact='$contact', role='$role' WHERE id=$id";
    } else {
        // Update with new password (plain text)
        $update_sql = "UPDATE user SET name='$name', email='$email', faculty='$faculty', year_part='$year_part', address='$address', contact='$contact', role='$role', password='$password' WHERE id=$id";
    }

    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['message'] = "User updated successfully";
        header("Location: user_management.php");
        exit();
    } else {
        $_SESSION['message'] = "Update failed: " . mysqli_error($conn);
    }
}

/* ------------------------------------
   FETCH USER DATA
------------------------------------ */
if (!isset($_GET['id'])) {
    header("Location: user_management.php");
    exit();
}

$user_id = intval($_GET['id']);
$query = "SELECT * FROM user WHERE id=$user_id";
$result = mysqli_query($conn, $query);

$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo "<h4>User not found</h4>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="Offline/CSS/bootstrap.css">
    <link rel="stylesheet" href="CSS/edit.css">
</head>

<body>

    <div class="container mt-4">
        <h3>Edit <?php echo htmlspecialchars($user['name']); ?>'s Details</h3>

        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="alert alert-warning">' . htmlspecialchars($_SESSION['message']) . '</div>';
            unset($_SESSION['message']);
        }
        ?>

        <form action="" method="POST">

            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">

            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control"
                    value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control"
                    value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Faculty</label>
                <input type="text" name="faculty" class="form-control"
                    value="<?php echo htmlspecialchars($user['faculty']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Year / Part</label>
                <input type="text" name="year_part" class="form-control"
                    value="<?php echo htmlspecialchars($user['year_part']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Address</label>
                <input type="text" name="address" class="form-control"
                    value="<?php echo htmlspecialchars($user['address']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Contact</label>
                <input type="text" name="contact" class="form-control"
                    value="<?php echo htmlspecialchars($user['contact']); ?>" required>
            </div>

            <div class="mb-3">
                <label>Role</label>
                <select name="role" id="" class="form-control">
                    <option value="Admin" <?php if ($user['role'] == 'Admin')
                        echo 'selected'; ?>>Admin</option>
                    <option value="user" <?php if ($user['role'] == 'user')
                        echo 'selected'; ?>>User</option>
                </select>
            </div>

            <div class="mb-3">
                <label>New Password (Leave blank to keep existing)</label>
                <input type="password" name="password" class="form-control" placeholder="Enter new password">
            </div>

            <button type="submit" name="update_btn" class="btn btn-primary">Update</button>
            <a href="user_management.php" class="btn btn-secondary">Cancel</a>

        </form>
    </div>

</body>

</html>