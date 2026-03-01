<?php
include "config/dbcon.php";
include "Includes/header.php";
?>

<head>
    <style>
        .add_new_user_form {
            justify-content: center;
            max-width: 420px;
            width: 90%;
            margin: 50px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<nav class="navbar navbar-light justify-content-center fs-3 mb-5" style="background-color: #15a5e373;">
    Manage Users
</nav>

<div class="container">

    <?php if (!empty($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Add New User Form -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">Add New User</div>
        <div class="card-body">
            <div class="add_new_user_form">

                <form method="post">
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="fullname" name="name"
                            placeholder="Enter your full name" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="faculty" class="form-label">Faculty</label>
                        <input type="text" name="faculty" id="faculty" class="form-control" placeholder="Faculty"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="year/part" class="form-label">Year / Part </label>
                        <input type="text" name="year_part" id="year/part" class="form-control" placeholder="Year/Part"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address"
                            placeholder="Enter your address" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact</label>
                        <input type="text" class="form-control" id="contact" name="contact"
                            placeholder="Enter your Contact " required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Create a password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="" disabled selected>Select role</option>
                            <option value="Admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <button type="submit" name="add_user" class="btn btn-success">Save</button>
                    <button type="button" onclick="window.location.href='user_management.php';"
                        class="btn btn-danger">Cancel</button>

                </form>
            </div>
        </div>
    </div>

    <!-- Handle Form Submission -->
    <?php

    if (isset($_POST['add_user'])) {

        $name = $_POST['name'];
        $address = $_POST['address'];
        $email = $_POST['email'];
        $faculty = $_POST['faculty'];
        $year_part = $_POST['year_part'];
        $contact = $_POST['contact'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        // Check if email already exists
        $checkmail = "SELECT * FROM user WHERE email = '$email'";
        $checkmail_run = mysqli_query($conn, $checkmail);

        if (mysqli_num_rows($checkmail_run) > 0) {
            echo '<div class="alert alert-danger">Email already exists.</div>';
        } else {
            // Insert user with plain text password
            $sql = "INSERT INTO user (name, address, email, faculty, year_part, contact, password, role) 
                    VALUES ('$name', '$address', '$email', '$faculty', '$year_part', '$contact', '$password', '$role')";

            if (mysqli_query($conn, $sql)) {
                $new_user_id = mysqli_insert_id($conn);
                echo '<script>window.location="user_management.php?msg=User added successfully with ID: ' . $new_user_id . '";</script>';
            } else {
                echo '<div class="alert alert-danger">Error: ' . mysqli_error($conn) . '</div>';
            }
        }

    } elseif (isset($_POST['cancel'])) {
        header("Location: user_management.php");
    }
    ?>

</div>

<?php include('Includes/footer.php'); ?>