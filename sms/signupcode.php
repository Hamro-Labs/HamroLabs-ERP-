<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('admin/config/dbcon.php');

// Redirect if already logged in
if (isset($_SESSION['auth']) && $_SESSION['auth'] === true) {
    header("Location: index.php");
    exit;
}

// Process signup form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log("Signup form submitted via POST");
    
    // Sanitize inputs using mysqli_real_escape_string
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $contact = mysqli_real_escape_string($conn, trim($_POST['contact']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $faculty = mysqli_real_escape_string($conn, trim($_POST['faculty']));
    $year_part = mysqli_real_escape_string($conn, trim($_POST['year_part']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    error_log("Form data: name=$name, email=$email, contact=$contact, faculty=$faculty, year_part=$year_part");

    $errors = [];
    
    // Validation
    
    // Name validation
    if (empty($name)) {
        $errors[] = "Full name is required";
    } elseif (strlen($name) < 3) {
        $errors[] = "Name must be at least 3 characters long";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $errors[] = "Name can only contain letters and spaces";
    }
    
    // Email validation
    if (empty($email)) {
        $errors[] = "Email address is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists
        $email_check_query = "SELECT * FROM user WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $email_check_query);
        
        if (mysqli_num_rows($result) > 0) {
            $errors[] = "This email is already registered. Please use a different email or login.";
        }
    }
    
    // Contact validation
    if (empty($contact)) {
        $errors[] = "Contact number is required";
    } elseif (!preg_match("/^9[78]\d{8}$/", $contact)) {
        $errors[] = "Please enter a valid Nepali contact number (98XXXXXXXX or 97XXXXXXXX)";
    }
    
    // Address validation
    if (empty($address)) {
        $errors[] = "Address is required";
    } elseif (strlen($address) < 5) {
        $errors[] = "Please enter a complete address";
    }
    
    // Faculty validation
    if (empty($faculty)) {
        $errors[] = "Please select your faculty";
    }
    
    // Year/Part validation
    if (empty($year_part)) {
        $errors[] = "Please select your year/part";
    }
    
    // Password validation
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    // Confirm password validation
    if (empty($confirm_password)) {
        $errors[] = "Please confirm your password";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // If there are validation errors, redirect back with error messages
    if (!empty($errors)) {
        error_log("Validation errors: " . implode(", ", $errors));
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = "error";
        header("Location: SignUp.php");
        exit;
    }
    error_log("Validation passed, proceeding to insert");
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Set default role as 'user'
    $role = 'user';
    
    // Insert user into database
    $insert_query = "INSERT INTO user (name, address, email, faculty, year_part, contact, password, role) 
                     VALUES ('$name', '$address', '$email', '$faculty', '$year_part', '$contact', '$hashed_password', '$role')";
    
    if (mysqli_query($conn, $insert_query)) {
        // Registration successful
        $user_id = mysqli_insert_id($conn);
        error_log("User registered successfully with ID: $user_id");

        // Set session variables for auto-login
        $_SESSION['auth'] = true;
        $_SESSION['auth_user'] = [
            'user_id' => $user_id,
            'user_name' => $name,
            'user_email' => $email,
            'role' => $role
        ];

        // Set success message
        $_SESSION['message'] = "🎉 Welcome to Birgunj Institute of Technology, " . htmlspecialchars($name) . "! Your account has been created successfully. You are now logged in.";

        // Redirect to message page then to index
        header("Location: msg.php");
        exit;

    } else {
        // Registration failed
        $db_error = mysqli_error($conn);
        error_log("Registration failed: $db_error");
        $_SESSION['message'] = "Registration failed. Please try again. Error: " . $db_error;
        $_SESSION['message_type'] = "error";
        header("Location: SignUp.php");
        exit;
    }
    
} else {
    // If accessed directly without POST request
    header("Location: SignUp.php");
    exit;
}

mysqli_close($conn);
?>
