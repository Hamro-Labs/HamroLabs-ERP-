<?php
$pageTitle = "Home";
include "Includes/header.php";
include "admin/config/dbcon.php";

// Get current theme
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

// Fetch products
$select_query = "SELECT * FROM products LIMIT 20";
$run_query = mysqli_query($conn, $select_query);
?>

<style>
    /* 60-30-10 Color Rule - Homepage with Light/Dark Theme */
    :root {
        /* 60% - White/Light Neutral - Light Theme */
        --bg-60-white: #ffffff;
        --bg-60-light: #f8f9fa;
        --bg-60-lighter: #fafbfc;

        /* 30% - Blue-Gray Tones - Light Theme */
        --bg-30-panel: #e9ecef;
        --bg-30-card: #dee2e6;
        --bg-30-slate: #6c757d;

        /* 10% - Deep Accent - Light Theme */
        --accent-10-primary: #0d6efd;
        --accent-10-dark: #0b5ed7;
        --accent-10-hover: #0a58ca;

        /* Text Colors - Light Theme */
        --text-primary: #212529;
        --text-secondary: #495057;
        --text-muted: #6c757d;
        --shadow: rgba(13, 110, 253, 0.15);
    }

    [data-theme="dark"] {
        /* 60% - Dark Neutral - Dark Theme */
        --bg-60-white: #1a1a2e;
        --bg-60-light: #16213e;
        --bg-60-lighter: #0f3460;

        /* 30% - Dark Blue-Gray Tones - Dark Theme */
        --bg-30-panel: #0f3460;
        --bg-30-card: #1a1a2e;
        --bg-30-slate: #533483;

        /* 10% - Deep Accent - Dark Theme */
        --accent-10-primary: #e94560;
        --accent-10-dark: #d63a55;
        --accent-10-hover: #c52f45;

        /* Text Colors - Dark Theme */
        --text-primary: #f8f9fa;
        --text-secondary: #dee2e6;
        --text-muted: #adb5bd;
        --shadow: rgba(233, 69, 96, 0.25);
    }

    .main-content {
        width: 90%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 0;
    }

    /* Hero Section - 10% Deep Accent */
    .hero {
        position: relative;
        height: 500px;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 50px;
        box-shadow: 0 20px 60px var(--shadow);
    }

    .hero-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--accent-10-primary) 0%, var(--accent-10-dark) 100%);
        z-index: 1;
        transition: background 0.3s ease;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.3);
        z-index: 2;
    }

    .hero-content {
        position: relative;
        z-index: 3;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        color: white;
        padding: 40px;
    }

    .hero-content h1 {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 20px;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        transition: color 0.3s ease;
    }

    .hero-content p {
        font-size: 1.2rem;
        opacity: 0.95;
        max-width: 600px;
        margin-bottom: 35px;
        transition: color 0.3s ease;
    }

    /* Search Bar */
    .search-bar {
        display: flex;
        width: 100%;
        max-width: 600px;
        background: var(--bg-60-white);
        border-radius: 50px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        transition: background 0.3s ease;
    }

    .search-bar input {
        flex: 1;
        padding: 18px 30px;
        border: none;
        font-size: 1rem;
        outline: none;
        color: var(--text-primary);
        background: var(--bg-60-white);
        transition: background 0.3s ease, color 0.3s ease;
    }

    .search-bar button {
        padding: 18px 35px;
        background: var(--accent-10-primary);
        border: none;
        color: white;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .search-bar button:hover {
        background: var(--accent-10-hover);
    }

    /* Section Title */
    .section-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 35px;
        padding-left: 20px;
        border-left: 5px solid var(--accent-10-primary);
        transition: color 0.3s ease;
    }

    /* Product Grid */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
    }

    .product-card {
        background: var(--bg-60-white);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px var(--shadow);
        transition: all 0.4s ease;
        border: 1px solid var(--bg-30-panel);
    }

    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 50px var(--shadow);
    }

    .product-card .pic {
        position: relative;
        height: 200px;
        overflow: hidden;
        background: var(--bg-60-light);
        transition: background 0.3s ease;
    }

    .product-card .pic img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .product-card:hover .pic img {
        transform: scale(1.1);
    }

    .product-card .img-count {
        position: absolute;
        top: 12px;
        right: 12px;
        background: var(--accent-10-primary);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: background 0.3s ease;
    }

    .product-dets {
        padding: 20px;
        transition: background 0.3s ease;
    }

    .product-dets h1 {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
        transition: color 0.3s ease;
    }

    .product-dets .department {
        color: var(--accent-10-primary);
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 5px;
        transition: color 0.3s ease;
    }

    .product-dets .category {
        color: var(--text-muted);
        font-size: 0.85rem;
        transition: color 0.3s ease;
    }

    .rent-btn {
        display: block;
        width: calc(100% - 40px);
        margin: 0 20px 20px;
        padding: 14px;
        background: var(--accent-10-primary);
        color: white;
        text-align: center;
        text-decoration: none;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .rent-btn:hover {
        background: var(--accent-10-hover);
        transform: translateY(-3px);
        box-shadow: 0 10px 25px var(--shadow);
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
        transition: color 0.3s ease;
    }

    .empty-state i {
        font-size: 4rem;
        opacity: 0.3;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .hero {
            height: 400px;
            border-radius: 16px;
        }

        .hero-content h1 {
            font-size: 2rem;
        }

        .hero-content p {
            font-size: 1rem;
        }

        .search-bar {
            flex-direction: column;
            border-radius: 16px;
        }

        .search-bar input {
            border-radius: 16px 16px 0 0;
        }

        .search-bar button {
            border-radius: 0 0 16px 16px;
        }

        .product-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
    }

    @media (max-width: 480px) {
        .hero-content h1 {
            font-size: 1.6rem;
        }

        .main-content {
            padding: 25px 0;
        }

        .product-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="main-content">

    <!-- Hero Section -->
    <div class="hero">
        <div class="hero-background"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Welcome to Our College Store</h1>
            <p>Find notebooks, stationery, lab equipment, bags, and everything you need for college life.</p>

            <form action="search.php" method="GET" class="search-bar">
                <input type="text" name="query" placeholder="Search products, stationery, equipment…" required>
                <button type="submit" name="search_btn"><i class="bi bi-search"></i> Search</button>
            </form>
        </div>
    </div>

    <!-- Products Section -->
    <h3 class="section-title">Available Products</h3>

    <div class="product-grid">

        <?php
        if (mysqli_num_rows($run_query) > 0) {
            $cardIndex = 0;
            while ($row = mysqli_fetch_assoc($run_query)) {
                // Detect images
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'svg', 'webp'];
                $imageBase = $row['product_id'];
                $productImages = [];

                foreach ($allowed_ext as $ext) {
                    $path = "Includes/Images/" . $imageBase . "." . $ext;
                    if (file_exists($path)) {
                        $productImages[] = $imageBase . "." . $ext;
                    }
                    if (count($productImages) >= 10)
                        break;
                }

                $mainImage = !empty($productImages) ? $productImages[0] : 'no-image.png';
                ?>

                <div class="product-card animate-on-scroll" style="--card-index: <?php echo $cardIndex; ?>">
                    <div class="pic">
                        <img src="Includes/Images/<?php echo $mainImage; ?>"
                            alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                        <?php if (count($productImages) > 1): ?>
                            <span class="img-count">+<?php echo count($productImages); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-dets">
                        <h1><?php echo htmlspecialchars($row['product_name']); ?></h1>
                        <p class="department"><?php echo htmlspecialchars($row['related_department'] ?? 'General'); ?></p>
                        <p class="category"><?php echo htmlspecialchars($row['category'] ?? 'Uncategorized'); ?></p>
                    </div>
                    <a href="rent_request.php?id=<?php echo $row['product_id']; ?>" class="rent-btn">
                        <i class="bi bi-box-seam me-2"></i>Request For Rent
                    </a>
                </div>

                <?php
                $cardIndex++;
            }
        } else {
            echo '<div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h3>No Products Available</h3>
                <p>Check back later for new products.</p>
            </div>';
        }
        ?>

    </div>
</div>

<script>
    // Enhanced GSAP Animations
    document.addEventListener('DOMContentLoaded', () => {
        gsap.registerPlugin(ScrollTrigger);

        // Hero section animation
        gsap.from(".hero-background", {
            scale: 1.1,
            duration: 2,
            ease: "power2.out"
        });

        gsap.from(".hero-content", {
            y: 50,
            opacity: 0,
            duration: 1,
            delay: 0.3,
            ease: "power3.out"
        });

        // Section title animation
        gsap.from(".section-title", {
            x: -50,
            opacity: 0,
            duration: 0.8,
            scrollTrigger: {
                trigger: ".section-title",
                start: "top 85%"
            }
        });

        // Product cards staggered animation
        const productCards = document.querySelectorAll('.product-card');
        if (productCards.length > 0) {
            gsap.fromTo(productCards,
                {
                    y: 60,
                    opacity: 0,
                    scale: 0.9
                },
                {
                    y: 0,
                    opacity: 1,
                    scale: 1,
                    duration: 0.7,
                    stagger: 0.1,
                    ease: "back.out(1.7)",
                    scrollTrigger: {
                        trigger: ".product-grid",
                        start: "top 80%"
                    }
                }
            );
        }

        // Empty state animation
        const emptyState = document.querySelector('.empty-state');
        if (emptyState) {
            gsap.fromTo(emptyState,
                { y: 30, opacity: 0 },
                {
                    y: 0,
                    opacity: 1,
                    duration: 0.8,
                    scrollTrigger: {
                        trigger: ".product-grid",
                        start: "top 80%"
                    }
                }
            );
        }
    });
</script>

<?php include "Includes/bottom.php"; ?>