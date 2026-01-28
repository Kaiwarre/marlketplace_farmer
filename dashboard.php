<?php
session_start();
require_once 'includes/db.php';

// Check if user is seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: index.php");
    exit;
}

// Fetch seller's products
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - Farmers Market</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">🌿 Farmers Market</a>
                <div class="nav-links">
                    <a href="index.php">Главная</a>
                    <a href="logout.php">Выйти</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>Мои товары</h1>
            <a href="add_product.php" class="btn">Добавить товар</a>
        </div>

        <?php if (empty($products)): ?>
            <p>Вы еще не добавили ни одного товара.</p>
        <?php else: ?>
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Фото</th>
                        <th>Название</th>
                        <th>Категория</th>
                        <th>Цена</th>
                        <th>Дата</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <?php
                                    $image_url = $product['image_url'] ?: 'img/placeholder.svg';
                                ?>
                                <img src="<?= htmlspecialchars($image_url) ?>" alt="Product" style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td><?= htmlspecialchars($product['title']) ?></td>
                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                            <td><?= number_format($product['price'], 0, '.', ' ') ?> сом</td>
                            <td><?= date('M j, Y', strtotime($product['created_at'])) ?></td>
                            <td>
                                <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn" style="padding: 6px 10px; font-size: 0.9em;">Редактировать</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
