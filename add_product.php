<?php
session_start();
require_once 'includes/db.php';

// Check if user is seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    
    // Image Upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = 'uploads/';
        $file_name = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Basic validation
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = $target_file;
            } else {
                $error = "Ошибка загрузки изображения.";
            }
        } else {
            $error = "Разрешены только файлы JPG, JPEG, PNG и GIF.";
        }
    }

    if (!$error && $title && $price && $category_id) {
        $stmt = $pdo->prepare("INSERT INTO products (user_id, category_id, title, description, price, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $category_id, $title, $description, $price, $image_url])) {
            $success = "Товар успешно добавлен!";
        } else {
            $error = "Ошибка добавления товара.";
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
    <title>Add Product - Farmers Market</title>
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
            <h2>Добавить товар</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?> <a href="dashboard.php">Перейти в кабинет</a></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Название *</label>
                    <input type="text" name="title" required>
                </div>
                
                <div class="form-group">
                    <label>Категория *</label>
                    <select name="category_id" required>
                        <option value="">Выберите категорию</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Цена (сом) *</label>
                    <input type="number" step="0.01" name="price" required>
                </div>

                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label>Фото товара</label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <button type="submit" class="btn" style="width: 100%">Добавить товар</button>
            </form>
        </div>
    </div>
</body>
</html>
