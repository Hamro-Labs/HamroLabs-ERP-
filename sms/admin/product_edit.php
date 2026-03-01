<?php
include('config/dbcon.php');
include('includes/header.php');


$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$id) {
    die("Error: Product ID is missing.");
}

// Fetch current product to handle the image logic
$select_query = "SELECT * FROM products WHERE product_id = $id LIMIT 1";
$run_query = mysqli_query($conn, $select_query);
while ($row = mysqli_fetch_array($run_query)) {
    $product = $row;
}
$allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'svg', 'webp'];

$product_images = [];

foreach ($allowed_ext as $ext) {
    $image_path = "../Includes/Images/" . $id . "." . $ext;
    if (file_exists($image_path)) {
        $product_images[] = $id . "." . $ext;
    }
}

// 1. Get and validate ID

if (isset($_POST['submit'])) {
    // 2. Collect and Sanitize
    $p_name = ($_POST['product_name']);
    $cat = $_POST['category'];
    $qty = intval($_POST['quantity']);
    $dept = $_POST['related_department'];

    // 3. Advanced Image Logic
    $filename = $_FILES["uploadfile"]["name"];

    if (!empty($filename)) {
        $tempname = $_FILES["uploadfile"]["tmp_name"];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        // Security check: Is it actually an image?
        if (in_array($ext, $allowed)) {
            $new_filename = bin2hex(random_bytes(8)) . "." . $ext; // Unique name
            $folder = "../Includes/Images/" . $image_path;

            if (move_uploaded_file($tempname, $folder)) {
                // Delete the OLD image file from the server to save space
                if (file_exists($product['product_image'])) {
                    unlink($product['product_image']);
                }
            }
            header("Location: view_all_products.php");
            exit();
        } else {
            die("Error: Invalid file type.");
        }
    } else {

        $folder = $product['product_image'];
    }


    $update_sql = "UPDATE products SET product_name=?, category=?, quantity=?, related_department=?, product_image=? WHERE product_id=?";
    $update_stmt = $conn->prepare($update_sql);


    $update_stmt->bind_param("ssissi", $p_name, $cat, $qty, $dept, $folder, $id);

    if ($update_stmt->execute()) {
        echo "<script>alert('Product updated successfully!'); window.location.href='products.php';</script>";
        header("Location: view_all_products.php");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: 500;
            color: #555;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            width: 100%;
        }

        .btn {
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
        }

        .submit-btn {
            background: #4CAF50;
            color: white;
        }

        .submit-btn:hover {
            background: #43a047;
        }

        .cancel-btn {
            background: #f44336;
            color: white;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .cancel-btn:hover {
            background: #d32f2f;
        }

        .image-preview {
            text-align: center;
            margin-top: 10px;
        }

        .image-preview img {
            max-width: 150px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
    </style>
    <div class="container">
        <h2>Edit <?php echo ($product['product_name']); ?>'s Details</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <label>Product Name:</label>
            <input type="text" name="product_name" value="<?php echo $product['product_name']; ?>" required>

            <label>Category:</label>
            <input type="text" name="category" value="<?php echo $product['category']; ?>">

            <label>Quantity:</label>
            <input type="number" name="quantity" value="<?php echo $product['quantity']; ?>">

            <label>Department:</label>
            <input type="text" name="related_department" value="<?php echo ($product['related_department']); ?>">

            <label>Select Image:</label>
            <input type="file" name="uploadfile">
            <div class="image-preview">
                <?php if (!empty($product['product_image'])): ?>
                    <img src="<?php echo $image_path; ?>" alt="<?php echo $product['product_name']; ?>" class="product-img">
                <?php endif; ?>
            </div>

            <button type="submit" name="submit" class="btn submit-btn">Update Product</button>
            <a href="view_all_products.php" class="btn cancel-btn">Cancel</a>
        </form>
    </div>
<?php include 'Includes/footer.php'; ?>