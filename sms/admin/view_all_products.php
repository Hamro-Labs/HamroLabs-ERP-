<?php
include "config/dbcon.php";
include "Includes/header.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .table-container {
            margin-top: 40px;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        table th,
        table td {
            vertical-align: middle !important;
        }

        table tbody tr:hover {
            background-color: #e9f5ff;
        }

        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        .btn {
            border-radius: 50px;
        }

        .action-btns a {
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <div class="container table-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Product List</h3>
            <a href="add_product.php" class="btn btn-info text-white">
                <i class="bi bi-plus-circle"></i> Add Product
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Department</th>
                        <th>Image</th>
                        <th>Added Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 

                    $select_query = "SELECT * FROM products";
                    $run_query = mysqli_query($conn, $select_query);
                    while ($row = mysqli_fetch_array($run_query)) {
                        $product_id = $row['product_id'];
                        $product_name = $row['product_name'];
                        $product_category = $row['category'];
                        $product_quantity = $row['quantity'];
                        $product_department = $row['related_department'];
                        $product_image = $row['product_image'];
                        $product_created = $row['created_at'];
                        ?>

                        <?php
                        
                        $productImages = [];
                        $available_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                        $imageBase = $product_id;
                        foreach ($available_extensions as $ext) {
                            $path = "../Includes/Images/" . $imageBase . "." . $ext;
                            if (file_exists($path)) {
                                $productImages[] = $imageBase . "." . $ext;
                            }
                            if (count($productImages) >= 10)
                                break;
                        } ?>

                        <tr>
                            <td><?= $product_id ?></td>
                            <td><?= $product_name ?></td>
                            <td><?= $product_category ?></td>
                            <td><?= $product_quantity ?></td>
                            <td><?= $product_department ?></td>
                            <td>
                                <?php if (!empty($product_image)): ?>
                                   <img src="../Includes/Images/<?php echo $product_image; ?>" alt="<?php echo $product_name; ?>" class="product-img">
                                <?php else: ?>
                                    <span class="text-muted">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date("d M Y, h:i A", strtotime($product_created)) ?></td>
                            <td class="action-btns">



                                <a href="product_edit.php?id=<?= $product_id ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <a href="product_delete.php?id=<?= $product_id ?>" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>