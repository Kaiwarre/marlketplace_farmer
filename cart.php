<?php
session_start();
require_once 'includes/db.php';

// Handle Remove from Cart
if (isset($_POST['remove_id'])) {
    $remove_id = $_POST['remove_id'];
    unset($_SESSION['cart'][$remove_id]);
}

// Handle Checkout (Mock)
if (isset($_POST['checkout'])) {
    // In a real app, we would save the order to DB here
    $_SESSION['cart'] = [];
    $success = "Заказ успешно оформлен! Спасибо за поддержку местных фермеров.";
}

$cart_items = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    if (!empty($ids)) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $products = $stmt->fetchAll();

        foreach ($products as $product) {
            $qty = $_SESSION['cart'][$product['id']];
            $product['qty'] = $qty;
            $product['subtotal'] = $product['price'] * $qty;
            $cart_items[] = $product;
            $total_price += $product['subtotal'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Farmers Market</title>
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
                        <a href="logout.php">Выйти</a>
                    <?php else: ?>
                        <a href="login.php">Войти</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1>Корзина</h1>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?> <a href="index.php">Продолжить покупки</a></div>
        <?php elseif (empty($cart_items)): ?>
            <p>Ваша корзина пуста. <a href="index.php">Вернуться в магазин</a>.</p>
        <?php else: ?>
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Товар</th>
                        <th>Цена</th>
                        <th>Кол-во</th>
                        <th>Сумма</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['title']) ?></td>
                            <td><?= number_format($item['price'], 0, '.', ' ') ?> сом</td>
                            <td><?= $item['qty'] ?></td>
                            <td><?= number_format($item['subtotal'], 0, '.', ' ') ?> сом</td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="remove_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn" style="background-color: #e53935; padding: 5px 10px;">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right; font-weight: bold;">Итого:</td>
                        <td style="font-weight: bold; font-size: 1.2em;"><?= number_format($total_price, 0, '.', ' ') ?> сом</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <div style="margin-top: 20px; text-align: right;">
                <form method="POST">
                    <button type="submit" name="checkout" class="btn" style="font-size: 1.2em;">Оформить заказ</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
