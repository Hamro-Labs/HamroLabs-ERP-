<?php
include "admin/config/dbcon.php";
include "Includes/header.php";

if (isset($_GET['search_btn'])) {
    $query = $_GET['query'];

    $sql = "SELECT * FROM products WHERE product_name LIKE '%$query%' OR related_department LIKE '%$query%' OR category LIKE '%$query%'";
    $run_query = mysqli_query($conn, $sql);


    if (mysqli_num_rows($run_query) > 0) {
        $count = mysqli_num_rows($run_query);
        ?>

        <head>
            <link rel="stylesheet" href="search.css">
        </head>
        <div class="search-results">
            <div class="search-title">
                <h2>Search Results for "<?php echo $query; ?>"</h2>
                <p class="result-count"><?php echo $count; ?> products found</p>
            </div>

            <div class="product-grid">
                <?php while ($row = mysqli_fetch_assoc($run_query)) {
                    // Find product image
                    $imageFound = false;
                    $imagePath = "Includes/Images/placeholder.jpg";
                    $extensions = ['jpg', 'jpeg', 'png', 'gif'];

                    foreach ($extensions as $ext) {
                        $checkPath = "Includes/Images/" . $row['product_id'] . "." . $ext;
                        if (file_exists($checkPath)) {
                            $imagePath = $checkPath;
                            $imageFound = true;
                            break;
                        }
                    }
                    ?>

                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo $imagePath; ?>" alt="<?php echo $row['product_name']; ?>">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?php echo $row['product_name']; ?></h3>
                            <div class="product-details">
                                <p><strong>Department:</strong> <?php echo $row['related_department']; ?></p>
                                <p><strong>Category:</strong> <?php echo $row['category']; ?></p>
                            </div>
                            <a href="rent_request.php?id=<?php echo $row['product_id']; ?>" class="rent-btn">Request For Rent</a>
                        </div>
                    </div>

                <?php } ?>
            </div>
        </div>

        <?php
    } else {
        ?>

        <div class="no-results">
            <div class="no-results-icon">
                <i>🔍</i>
            </div>
            <h2>No Results Found</h2>
            <p class="search-query">Your search for "<?php echo $query; ?>" did not match any products.</p>

            <div class="suggestions">
                <h3>Suggestions:</h3>
                <ul>
                    <li>Check your spelling</li>
                    <li>Try different keywords</li>
                    <li>Search by department or category</li>
                </ul>
            </div>

            <div class="action-buttons">
                <a href="javascript:history.back()" class="back-btn">Go Back</a>
                <a href="index.php" class="home-btn">Home Page</a>
            </div>
        </div>

        <?php
    }
} else {
    header("Location: index.php");
    exit();
}

include "Includes/bottom.php";
?>