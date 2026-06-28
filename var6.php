<?php
// Подключение к БД
$host = 'localhost';
$dbname = 'test1';
$username = 'root';
$password = '';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

session_start();

// Вход
if (isset($_POST['login_btn'])) {
    $login = mysqli_real_escape_string($conn, $_POST['login']);
    $pass = mysqli_real_escape_string($conn, $_POST['password']);
    
    $result = mysqli_query($conn, "SELECT * FROM users WHERE login='$login' AND password='$pass'");
    $user = mysqli_fetch_assoc($result);
    
    if ($user && $user['role'] == 'admin') {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_login'] = $user['login'];
        $_SESSION['user_role'] = $user['role'];
        header('Location: ?page=admin');
        exit;
    } else {
        $error = 'Неверный логин или пароль!';
    }
}

// Выход
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ?page=home');
    exit;
}

// Добавление модели
if (isset($_POST['add_model_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $image = trim($_POST['image']);
    $category = trim($_POST['category']);
    
    if ($name && $image) {
        $name = mysqli_real_escape_string($conn, $name);
        $description = mysqli_real_escape_string($conn, $description);
        $image = mysqli_real_escape_string($conn, $image);
        $category = mysqli_real_escape_string($conn, $category);
        
        mysqli_query($conn, "INSERT INTO models (name, description, image, category) VALUES ('$name', '$description', '$image', '$category')");
        $msg = 'Модель добавлена!';
    } else {
        $msg = 'Заполните все поля!';
    }
}

// Удаление модели
if (isset($_GET['delete_model']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $id = intval($_GET['delete_model']);
    mysqli_query($conn, "DELETE FROM models WHERE id=$id");
    header('Location: ?page=admin');
    exit;
}

// Получаем все модели
$result = mysqli_query($conn, "SELECT * FROM models ORDER BY id DESC");
$models = [];
while ($row = mysqli_fetch_assoc($result)) {
    $models[] = $row;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple 3D Print</title>
    <style>
        /* ===== ОБЩИЕ СТИЛИ ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #fff;
        }

        a {
            text-decoration: none;
            color: #000;
            transition: color 0.3s;
        }

        a:hover {
            color: #FFB987;
        }

        /* ===== ШАПКА ===== */
        .header {
            height: 110px;
            background-color: #12ADDE;
            width: 100%;
            display: flex;
            align-items: center;
            padding: 0 50px;
        }

        .logo {
            margin-right: 50px;
        }

        .logo_svg {
            width: 66px;
            height: 51px;
        }

        .header_links {
            display: flex;
            gap: 90px;
            align-items: center;
        }

        .header_links a {
            font-size: 24px;
            color: #000;
        }

        .header_links a:hover {
            color: #FFB987;
        }

        .admin-link {
            color: red !important;
            font-weight: bold;
        }

        .login-link {
            color: #007bff !important;
            font-weight: bold;
        }

        /* ===== ОСНОВНОЙ КОНТЕНТ ===== */
        .main_content {
            padding: 44px 50px 0 50px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .main_info {
            text-align: center;
            margin-bottom: 30px;
        }

        .main_title {
            font-size: 36px;
            font-weight: bold;
        }

        .information {
            display: flex;
            align-items: flex-start;
            gap: 48px;
            margin-top: 20px;
        }

        .logo_main {
            height: 185px;
            width: 460px;
            object-fit: cover;
        }

        .about {
            font-size: 18px;
            line-height: 1.6;
        }

        /* ===== ПРИМЕРЫ МОДЕЛЕЙ ===== */
        .examples {
            padding: 47px 50px 56px 50px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .d-examples {
            font-size: 36px;
            text-align: center;
            margin-bottom: 30px;
        }

        .pictures_first {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
        }

        .pictures_second {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
        }

        .pictures_first img,
        .pictures_second img {
            max-width: 250px;
            max-height: 200px;
            border-radius: 10px;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .pictures_first img:hover,
        .pictures_second img:hover {
            transform: scale(1.05);
        }

        .model-item {
            text-align: center;
        }

        .model-item img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            object-fit: cover;
        }

        .model-item .model-name {
            font-weight: bold;
            margin-top: 8px;
            font-size: 16px;
        }

        .model-item .model-desc {
            font-size: 12px;
            color: #666;
        }

        /* ===== ФУТЕР ===== */
        .footer {
            height: 110px;
            background-color: #12ADDE;
            width: 100%;
            display: flex;
            align-items: center;
            padding: 0 50px;
        }

        .footer_links {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 40px;
        }

        .footer_links a {
            font-size: 38px;
            color: #000;
        }

        .footer_links a:hover {
            color: #FFB987;
        }

        .social img {
            width: 30px;
            height: 30px;
            margin-left: 10px;
        }

        /* ===== АДМИН-ПАНЕЛЬ ===== */
        .admin-panel {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .admin-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid #ddd;
        }

        .admin-form input,
        .admin-form textarea,
        .admin-form select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .admin-form button {
            padding: 12px 30px;
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .admin-form button:hover {
            background: #218838;
        }

        .item-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            background: #fff;
        }

        .item-card img {
            max-width: 150px;
            border-radius: 5px;
        }

        .delete-btn {
            padding: 5px 15px;
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        .msg-success {
            color: green;
            padding: 10px;
            background: #d4edda;
            border-radius: 5px;
            margin: 10px 0;
        }

        .msg-error {
            color: red;
            padding: 10px;
            background: #f8d7da;
            border-radius: 5px;
            margin: 10px 0;
        }

        /* ===== ВХОД ===== */
        .login-form {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.1);
        }

        .login-form h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-form input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .login-form button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .login-form button:hover {
            background: #0069d9;
        }

        .login-form .center {
            text-align: center;
            margin-top: 15px;
        }

        /* ===== АДАПТИВНОСТЬ ===== */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                height: auto;
                padding: 20px;
            }
            .header_links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 20px;
            }
            .information {
                flex-direction: column;
                align-items: center;
            }
            .logo_main {
                width: 100%;
                height: auto;
            }
            .footer {
                height: auto;
                padding: 20px;
                flex-wrap: wrap;
            }
            .footer_links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 20px;
            }
            .pictures_first,
            .pictures_second {
                gap: 15px;
            }
            .pictures_first img,
            .pictures_second img {
                max-width: 150px;
                max-height: 120px;
            }
            .main_title {
                font-size: 24px;
            }
            .d-examples {
                font-size: 24px;
            }
            .about {
                font-size: 16px;
            }
            .footer_links a {
                font-size: 24px;
            }
            .main_content {
                padding: 20px;
            }
            .examples {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <!-- ===== ШАПКА ===== -->
    <header>
        <div class="header">
            <div class="logo">
                <img src="img/svg-editor-image.svg" alt="Логотип" class="logo_svg">
            </div>
            <div class="header_links">
                <a href="second_page.html">Наши работы</a>
                <a href="form.php">Заказать 3D-Продукт</a>
                <a href="?page=home">О нас</a>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                    <a href="?page=admin" class="admin-link">Админ</a>
                    <a href="?logout=1" class="admin-link">Выйти</a>
                <?php else: ?>
                    <a href="?page=login" class="login-link">Войти</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- ===== ГЛАВНАЯ ===== -->
    <?php if ($page == 'home'): ?>
    <div class="main_content">
        <div class="main_info">
            <p class="main_title">Simple 3D Print [3D печать и моделирование]</p>
        </div>
        <div class="information">
            <div class="main_logo">
                <img src="img/print.png" alt="3D Печать" class="logo_main">
            </div>
            <div class="about">
                <p>
                    Сайт посвящен разработке и печати 3D моделей<br>
                    Pet-G, PLA, ABS пластиками в кратчайшие сроки,<br>
                    например: переходники, игрушки сувениры,<br>
                    шестеренки, крепежи, макеты, запчасти и зип,<br>
                    корпуса, элементы декора и любые другие<br>
                    вещи, которые ВЫ захотите.
                </p>
                <br>
                <p>
                    Печать осуществляется по вашим моделям<br>
                    (STL, OBJ), либо по детали, эскизу, чертежам.<br>
                    Так же можем сделать дизайн по вашим<br>
                    картинкам или рисункам.
                </p>
            </div>
        </div>
    </div>

    <!-- ===== ПРИМЕРЫ МОДЕЛЕЙ ===== -->
    <div class="examples">
        <p class="d-examples">Примеры 3D-моделей</p>

        <?php if (empty($models)): ?>
            <!-- Статические изображения -->
            <div class="pictures_first">
                <img src="img/putin.png" alt="Путник">
                <img src="img/something.png" alt="Сова">
                <img src="img/house.png" alt="Домик">
            </div>
            <div class="pictures_second">
                <img src="img/circle.png" alt="Шестеренка">
                <img src="img/bird.png" alt="Птица">
                <img src="img/hearts.png" alt="Сердце">
            </div>
        <?php else: ?>
            <!-- Динамические модели из БД -->
            <div class="pictures_first">
                <?php foreach ($models as $model): ?>
                    <div class="model-item">
                        <img src="<?php echo htmlspecialchars($model['image']); ?>" 
                             alt="<?php echo htmlspecialchars($model['name']); ?>">
                        <p class="model-name"><?php echo htmlspecialchars($model['name']); ?></p>
                        <?php if (!empty($model['description'])): ?>
                            <p class="model-desc"><?php echo htmlspecialchars($model['description']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ===== ВХОД ===== -->
    <?php if ($page == 'login'): ?>
    <div class="main_content">
        <div class="login-form">
            <h1>Авторизация</h1>
            <?php if (isset($error)): ?>
                <div class="msg-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="login" placeholder="Логин" required>
                <input type="password" name="password" placeholder="Пароль" required>
                <button type="submit" name="login_btn">Войти</button>
            </form>
            <div class="center">
                <strong>Логин: admin, Пароль: admin</strong><br>
                <a href="?page=home">На главную</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ===== АДМИН-ПАНЕЛЬ ===== -->
    <?php if ($page == 'admin'): ?>
        <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin'): ?>
            <div class="main_content" style="text-align:center;padding:50px 0;">
                <h1>Доступ запрещен</h1>
                <p>У вас нет прав доступа! <a href="?page=login">Войти</a></p>
            </div>
        <?php else: ?>
            <div class="main_content">
                <div class="admin-panel">
                    <h1 style="text-align:center;">Админ-панель</h1>

                    <!-- Добавление модели -->
                    <div class="admin-form">
                        <h2>Добавить 3D-модель</h2>
                        <?php if (isset($msg)): ?>
                            <div class="msg-success"><?php echo $msg; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <input type="text" name="name" placeholder="Название модели" required>
                            <input type="text" name="category" placeholder="Категория">
                            <textarea name="description" placeholder="Описание модели" rows="3"></textarea>
                            <input type="text" name="image" placeholder="Ссылка на картинку (img/название.png)" required>
                            <button type="submit" name="add_model_btn">Добавить модель</button>
                        </form>
                    </div>

                    <!-- Список моделей -->
                    <h2>Все модели (<?php echo count($models); ?>)</h2>
                    <?php if (empty($models)): ?>
                        <p>Нет моделей</p>
                    <?php else: ?>
                        <?php foreach ($models as $model): ?>
                            <div class="item-card">
                                <h3><?php echo htmlspecialchars($model['name']); ?></h3>
                                <?php if (!empty($model['category'])): ?>
                                    <p><strong>Категория:</strong> <?php echo htmlspecialchars($model['category']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($model['description'])): ?>
                                    <p><?php echo htmlspecialchars($model['description']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($model['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($model['image']); ?>" alt="">
                                <?php endif; ?>
                                <a href="?page=admin&delete_model=<?php echo $model['id']; ?>"
                                   class="delete-btn"
                                   onclick="return confirm('Удалить модель?')">Удалить</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <p style="text-align:center;margin-top:30px;">
                        <a href="?page=home">← На главную</a>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ===== ФУТЕР ===== -->
    <footer class="footer">
        <div class="footer_links">
            <a href="second_page.html">Модели</a>
            <a href="form.php">Заказать</a>
            <a href="?page=home">О нас</a>
            <a href="#" class="social"><img src="img/Vector.png" alt="VK"></a>
            <a href="#" class="social"><img src="img/Vector2.png" alt="Facebook"></a>
            <a href="#" class="social"><img src="img/Vector3.png" alt="YouTube"></a>
        </div>
    </footer>

</body>
</html>
<?php
mysqli_close($conn);
?>


-- ============================================================
-- БАЗА ДАННЫХ ДЛЯ САЙТА "Simple 3D Print"
-- ============================================================

-- 1. СОЗДАНИЕ БАЗЫ ДАННЫХ
CREATE DATABASE IF NOT EXISTS test1;
USE test1;

-- ============================================================
-- 2. УДАЛЕНИЕ СТАРЫХ ТАБЛИЦ (ЕСЛИ НУЖНО ПЕРЕСОЗДАТЬ)
-- ============================================================
DROP TABLE IF EXISTS models;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS users;

-- ============================================================
-- 3. ТАБЛИЦА ПОЛЬЗОВАТЕЛЕЙ
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. ТАБЛИЦА 3D-МОДЕЛЕЙ
-- ============================================================
CREATE TABLE IF NOT EXISTS models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. ТАБЛИЦА ЗАКАЗОВ
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. ДОБАВЛЕНИЕ АДМИНИСТРАТОРА
-- ============================================================
INSERT INTO users (login, password, role) VALUES 
('admin', 'admin', 'admin')
ON DUPLICATE KEY UPDATE login=login;

-- ============================================================
-- 7. ДОБАВЛЕНИЕ ТЕСТОВЫХ МОДЕЛЕЙ
-- ============================================================
INSERT INTO models (name, description, image, category) VALUES 
('Путник', '3D-модель фигурки для коллекции', 'img/putin.png', 'Фигуры'),
('Домик', '3D-модель домика для архитектурных макетов', 'img/house.png', 'Архитектура'),
('Сердце', 'Декоративная 3D-модель сердца в стиле арт', 'img/hearts.png', 'Декор'),
('Птица', '3D-модель птицы для декора', 'img/bird.png', 'Фигуры'),
('Шестеренка', '3D-модель шестеренки для механизмов', 'img/circle.png', 'Детали'),
('Сова', 'Модель совы для интерьера', 'img/something.png', 'Фигуры')
ON DUPLICATE KEY UPDATE name=name;

-- ============================================================
-- 8. ДОБАВЛЕНИЕ ТЕСТОВЫХ ЗАКАЗОВ
-- ============================================================
INSERT INTO orders (name, phone, email, message) VALUES 
('Иван Петров', '+7(999)123-45-67', 'ivan@mail.ru', 'Хочу заказать 3D-модель совы'),
('Мария Смирнова', '+7(999)234-56-78', 'maria@mail.ru', 'Нужна модель домика для макета'),
('Алексей Иванов', '+7(999)345-67-89', 'alex@mail.ru', 'Интересует печать шестеренок');

-- ============================================================
-- 9. ПРОВЕРКА ДАННЫХ
-- ============================================================
SELECT * FROM users;
SELECT * FROM models;
SELECT * FROM orders;

-- ============================================================
-- 10. ПОКАЗАТЬ СТРУКТУРУ ТАБЛИЦ
-- ============================================================
DESCRIBE users;
DESCRIBE models;
DESCRIBE orders;

-- ============================================================
-- 11. ДОПОЛНИТЕЛЬНЫЕ ЗАПРОСЫ
-- ============================================================

-- Количество записей в таблицах
SELECT 'users' as table_name, COUNT(*) as count FROM users
UNION ALL
SELECT 'models', COUNT(*) FROM models
UNION ALL
SELECT 'orders', COUNT(*) FROM orders;

-- Все модели с сортировкой по категориям
SELECT * FROM models ORDER BY category, name;

-- Последние 5 добавленных моделей
SELECT * FROM models ORDER BY id DESC LIMIT 5;

-- Все заказы за сегодня
SELECT * FROM orders WHERE DATE(created_at) = CURDATE();

-- Количество моделей по категориям
SELECT category, COUNT(*) as count FROM models GROUP BY category;

-- ============================================================
-- 12. ПОИСК МОДЕЛЕЙ ПО КАТЕГОРИИ
-- ============================================================
-- SELECT * FROM models WHERE category = 'Фигуры';

-- ============================================================
-- 13. ОЧИСТКА ТАБЛИЦ (ЕСЛИ НУЖНО)
-- ============================================================
-- TRUNCATE TABLE models;
-- TRUNCATE TABLE orders;
-- TRUNCATE TABLE users;

-- ============================================================
-- 14. ПОЛНОЕ УДАЛЕНИЕ БАЗЫ (ЕСЛИ НУЖНО ПЕРЕСОЗДАТЬ)
-- ============================================================
-- DROP DATABASE IF EXISTS test1;