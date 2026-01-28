<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
    header("Location: dashboard.php");
    exit;
}

// Fetch product and ensure ownership
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$product_id, $_SESSION['user_id']]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: dashboard.php");
    exit;
}

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $category_id = $_POST['category_id'] ?? '';

    $image_url = $product['image_url'] ?: 'img/placeholder.svg';

    if (isset($_POST['remove_image'])) {
        $image_url = 'img/placeholder.svg';
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = __DIR__ . '/uploads/';
        $upload_url_base = 'uploads/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }
        if (!is_writable($upload_dir)) {
            $error = "Папка uploads недоступна для записи. Проверьте права доступа.";
        }

        if (!$error && !is_uploaded_file($_FILES['image']['tmp_name'])) {
            $error = "Файл не распознан как загруженный.";
        } elseif (!$error) {
            $image_info = @getimagesize($_FILES['image']['tmp_name']);
            $allowed_mimes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
            ];

            if ($image_info === false || empty($image_info['mime']) || !isset($allowed_mimes[$image_info['mime']])) {
                $error = "Разрешены только файлы JPG, JPEG, PNG, GIF и WEBP.";
            } else {
                $ext = $allowed_mimes[$image_info['mime']];
                $file_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $target_file = $upload_dir . $file_name;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_url = $upload_url_base . $file_name;
                } else {
                    $error = "Ошибка загрузки изображения.";
                }
            }
        }
    }

    if (!$error && $title && $price && $category_id) {
        $stmt = $pdo->prepare("UPDATE products SET category_id = ?, title = ?, description = ?, price = ?, image_url = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$category_id, $title, $description, $price, $image_url, $product_id, $_SESSION['user_id']])) {
            $success = "Товар обновлён!";
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
            $stmt->execute([$product_id, $_SESSION['user_id']]);
            $product = $stmt->fetch();
        } else {
            $error = "Ошибка обновления товара.";
        }
    } elseif (!$error) {
        $error = "Заполните все обязательные поля.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Farmers Market</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">🌿 Farmers Market</a>
                <div class="nav-links">
                    <a href="dashboard.php">Назад в кабинет</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="form-container">
            <h2>Редактировать товар</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?> <a href="dashboard.php">Перейти в кабинет</a></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Название *</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($product['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Категория *</label>
                    <select name="category_id" required>
                        <option value="">Выберите категорию</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Цена (сом) *</label>
                    <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Текущее фото</label>
                    <?php $current_image = $product['image_url'] ?: 'img/placeholder.svg'; ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?= htmlspecialchars($current_image) ?>" alt="Product" style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px;">
                    </div>
                    <label><input type="checkbox" name="remove_image" value="1"> Удалить фото (поставить заглушку)</label>
                </div>

                <div class="form-group">
                    <label>Новое фото</label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <button type="submit" class="btn" style="width: 100%">Сохранить</button>
            </form>
        </div>
    </div>
</body>
</html>
