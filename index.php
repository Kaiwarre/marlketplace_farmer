<?php
session_start();
error_log("INDEX: Started");
require_once 'includes/db.php';
error_log("INDEX: DB included");

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    // Simple cart: just count or list of IDs
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]++;
    } else {
        $_SESSION['cart'][$product_id] = 1;
    }
    $message = "Product added to cart!";
}

// Fetch Products
$stmt = $pdo->query("SELECT p.*, u.name as seller_name, c.name as category_name 
                     FROM products p 
                     JOIN users u ON p.user_id = u.id 
                     JOIN categories c ON p.category_id = c.id 
                     ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Local Farmers Marketplace</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">🌿 Farmers Market</a>
                <div class="nav-links">
                    <a href="index.php">Главная</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'seller'): ?>
                            <a href="dashboard.php">Кабинет продавца</a>
                        <?php endif; ?>
                        <a href="cart.php">Корзина (<?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0 ?>)</a>
                        <a href="logout.php">Выйти (<?= htmlspecialchars($_SESSION['user_name']) ?>)</a>
                    <?php else: ?>
                        <a href="login.php">Войти</a>
                        <a href="register.php">Регистрация</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <h1>Свежие местные продукты</h1>
        
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image" style="background-image: url('<?= htmlspecialchars($product['image_url'] ?: 'img/placeholder.svg') ?>'); background-size: cover; background-position: center;"></div>
                    <div class="product-info">
                        <div class="product-title"><?= htmlspecialchars($product['title']) ?></div>
                        <div class="product-category" style="font-size: 0.8em; color: #888;"><?= htmlspecialchars($product['category_name']) ?> • <?= htmlspecialchars($product['seller_name']) ?></div>
                        <div class="product-desc"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</div>
                        <div class="product-price"><?= number_format($product['price'], 0, '.', ' ') ?> сом</div>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <button type="submit" name="add_to_cart" class="btn" style="width: 100%">В корзину</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
