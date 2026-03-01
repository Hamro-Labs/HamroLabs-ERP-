<?php
include "config/dbcon.php";
include "Includes/header.php";

if (isset($_POST['submit'])) {

    // Check if file is uploaded
    if (!isset($_FILES["uploadfile"]) || $_FILES["uploadfile"]["error"] != 0) {
        $_SESSION['message'] = "Please select an image file!";
        exit;
    }

    // Get form data
    $p_name = $_POST['product_name'];
    $cat = $_POST['category'];
    $qty = $_POST['quantity'];
    $dept = $_POST['related_department'];


    $filename = $_FILES["uploadfile"]["name"];
    $tempname = $_FILES["uploadfile"]["tmp_name"];
    $folder = "../Includes/Images/";

    $ext = pathinfo($filename, PATHINFO_EXTENSION);


    $temp_filename = "temp_" . time() . "." . $ext;
    $temp_path = $folder . $temp_filename;

    if (move_uploaded_file($tempname, $temp_path)) {

        $query = "INSERT INTO products (product_name, category, quantity, related_department, product_image) 
                  VALUES ('$p_name', '$cat', '$qty', '$dept', '')";

        if (mysqli_query($conn, $query)) {
            $pro_id = mysqli_insert_id($conn);


            $final_filename = $pro_id . "." . $ext;
            $final_path = $folder . $final_filename;

            rename($temp_path, $final_path);

            // Update product with final image name
            $update_query = "UPDATE products SET product_image='$final_filename' WHERE product_id=$pro_id";
            mysqli_query($conn, $update_query);

            $_SESSION['message'] = "The new Product is added with Product ID: $pro_id";
        } else {
            // If database fails, delete the uploaded temp file
            unlink($temp_path);
            $_SESSION['message'] = "Failed to add product!";
        }

    } else {
        $_SESSION['message'] = "Failed to upload image!";
    }
}
?>


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="css/addproduct.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f8;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 500px;
            max-width: 90%;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
        }

        form input[type="text"],
        form input[type="number"],
        form input[type="file"] {
            width: 100%;
            padding: 10px 15px;
            margin: 10px 0 20px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        form input[type="submit"],
        form a.btn {
            display: inline-block;
            width: 48%;
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }

        form input[type="submit"] {
            background-color: #3498db;
            color: #fff;
        }

        form a.btn {
            background-color: #e67e22;
            color: #fff;
            margin-left: 4%;
        }

        form input[type="submit"]:hover {
            background-color: #2980b9;
        }

        form a.btn:hover {
            background-color: #d35400;
        }

        .message {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: green;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Add Product</h2>

        <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='message'>" . $_SESSION['message'] . "</div>";
            unset($_SESSION['message']);
        }
        ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="text" name="product_name" placeholder="Product Name" required>
            <input type="text" name="category" placeholder="Category">
            <input type="number" name="quantity" placeholder="Quantity">
            <input type="text" name="related_department" placeholder="Department">
            <input type="file" name="uploadfile" required>

            <input type="submit" name="submit" value="Add Product">
            <br>
            <br>
            <div class="grid">

                <a href="view_all_products.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>

<?php include 'Includes/footer.php'; ?>