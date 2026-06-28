<?php
// Подключение к БД
$host = 'localhost';
$dbname = 'exam_db';
$username = 'root';
$password = '';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

// ===== СОЗДАЕМ ТАБЛИЦЫ =====
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(50) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description VARCHAR(255),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    price VARCHAR(50) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255),
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Добавляем админа
$check_admin = mysqli_query($conn, "SELECT * FROM users WHERE login='admin'");
if (mysqli_num_rows($check_admin) == 0) {
    mysqli_query($conn, "INSERT INTO users (login, password, role) VALUES ('admin', 'admin', 'admin')");
}

// Добавляем тестовые данные
$check_cat = mysqli_query($conn, "SELECT * FROM categories");
if (mysqli_num_rows($check_cat) == 0) {
    mysqli_query($conn, "INSERT INTO categories (name, description, image) VALUES 
        ('Игры', 'Для детей', 'images/c1.jpg'),
        ('Интерьер', 'Фигурки', 'images/c2.jpg'),
        ('Одежда', 'Для мужчин', 'images/c3.jpg'),
        ('Интерьер', 'Полиграфия', 'images/c4.jpg'),
        ('Одежда', 'Для женщин', 'images/c5.png'),
        ('Интерьер', 'Посуда', 'images/c6.jpg')");
}

$check_prod = mysqli_query($conn, "SELECT * FROM products");
if (mysqli_num_rows($check_prod) == 0) {
    mysqli_query($conn, "INSERT INTO products (category_id, name, price, description, image, link) VALUES 
        (1, 'Слоник', '249.00', 'Милый слоник из коллекции', 'images/e1.png', 'e1.html'),
        (1, 'Сова', '249.00', 'Мудрая сова', 'images/e2.png', 'e2.html'),
        (1, 'Медвежонок', '300.00', 'Плюшевый медвежонок', 'images/e3.png', 'e3.html'),
        (2, 'Эльфивая Башня', '249.00', 'Сказочная башня', 'images/e4.png', 'e4.html'),
        (2, 'Подставка под снифтер', '340.00', 'Стильная подставка', 'images/e5.png', 'e5.html'),
        (1, 'Ёжик', '249.00', 'Коллекционный ёжик', 'images/e6.png', 'e6.html')");
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

// Добавление категории
if (isset($_POST['add_category_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $name = trim($_POST['category_name']);
    $description = trim($_POST['category_description']);
    $image = trim($_POST['category_image']);
    
    if ($name) {
        $name = mysqli_real_escape_string($conn, $name);
        $description = mysqli_real_escape_string($conn, $description);
        $image = mysqli_real_escape_string($conn, $image);
        mysqli_query($conn, "INSERT INTO categories (name, description, image) VALUES ('$name', '$description', '$image')");
        $msg_cat = 'Категория добавлена!';
    } else {
        $msg_cat = 'Заполните название категории!';
    }
}

// Добавление товара
if (isset($_POST['add_product_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $category_id = intval($_POST['category_id']);
    $name = trim($_POST['product_name']);
    $price = trim($_POST['product_price']);
    $description = trim($_POST['product_description']);
    $image = trim($_POST['product_image']);
    $link = trim($_POST['product_link']);
    
    if ($category_id && $name && $price) {
        $name = mysqli_real_escape_string($conn, $name);
        $price = mysqli_real_escape_string($conn, $price);
        $description = mysqli_real_escape_string($conn, $description);
        $image = mysqli_real_escape_string($conn, $image);
        $link = mysqli_real_escape_string($conn, $link);
        mysqli_query($conn, "INSERT INTO products (category_id, name, price, description, image, link) 
                            VALUES ('$category_id', '$name', '$price', '$description', '$image', '$link')");
        $msg_product = 'Товар добавлен!';
    } else {
        $msg_product = 'Заполните все поля!';
    }
}

// Удаление
if (isset($_GET['delete_category']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    mysqli_query($conn, "DELETE FROM categories WHERE id=" . intval($_GET['delete_category']));
    header('Location: ?page=admin');
    exit;
}
if (isset($_GET['delete_product']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    mysqli_query($conn, "DELETE FROM products WHERE id=" . intval($_GET['delete_product']));
    header('Location: ?page=admin');
    exit;
}

// Получаем данные
$categories = [];
$result_cat = mysqli_query($conn, "SELECT * FROM categories ORDER BY id");
if ($result_cat) {
    while ($row = mysqli_fetch_assoc($result_cat)) $categories[] = $row;
}

$products = [];
$result_prod = mysqli_query($conn, "SELECT * FROM products ORDER BY id");
if ($result_prod) {
    while ($row = mysqli_fetch_assoc($result_prod)) $products[] = $row;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Обработка формы
if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['user_name']));
    $surname = mysqli_real_escape_string($conn, trim($_POST['user_surname']));
    $email = mysqli_real_escape_string($conn, trim($_POST['Email']));
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));
    if ($name && $email && $message) {
        mysqli_query($conn, "INSERT INTO messages (name, surname, email, message) VALUES ('$name', '$surname', '$email', '$message')");
        $form_msg = 'Сообщение отправлено!';
    } else {
        $form_msg = 'Заполните все поля!';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gentil</title>
    
    <script src="js/jquery.min.js"></script>
    <script src="js/responsiveslides.min.js"></script>
    <script src="js/jquery.flexisel.js"></script>
    <script src="js/jquery.easydropdown.js"></script>
    <script src="js/scripts.js" type="text/javascript"></script>
    <script type="text/javascript" src="js/move-top.js"></script>
    <script type="text/javascript" src="js/easing.js"></script>
    
    <link href='http://fonts.googleapis.com/css?family=Roboto:500,900,100,300,700,400' rel='stylesheet' type='text/css'>
    
    <style>
        /* ============================================================
           ВСЕ СТИЛИ ВСТРОЕНЫ ЗДЕСЬ
           ============================================================ */
        
        /* ----- RESET ----- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background: #fff;
        }
        a {
            text-decoration: none;
            transition: 0.5s all;
            -webkit-transition: 0.5s all;
            -moz-transition: 0.5s all;
            -o-transition: 0.5s all;
            -ms-transition: 0.5s all;
        }
        a:hover {
            color: #FFB987;
        }
        .clearfix {
            clear: both;
        }
        .container {
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
            width: 1170px;
        }
        @media (max-width: 1200px) {
            .container { width: 970px; }
        }
        @media (max-width: 992px) {
            .container { width: 750px; }
        }
        @media (max-width: 768px) {
            .container { width: 100%; padding: 0 15px; }
        }
        
        /* ----- ШАПКА (из first_page.html) ----- */
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
        
        /* ----- БАННЕР (из style.css) ----- */
        .banner-bg1 {
            background: url(images/bg.jpg) no-repeat;
            background-size: cover;
            min-height: 835px;
            position: relative;
        }
        .caption {
            width: 100%;
            position: absolute;
            top: 42%;
            left: 0;
            text-align: center;
        }
        .caption h1 {
            font-size: 3.5em;
            font-weight: 500;
            color: #ec4539;
            margin-bottom: 8px;
            -webkit-text-stroke-width: 3px;
            -webkit-text-stroke-color: rgb(155, 0, 0);
        }
        .caption p {
            color: #ec4539;
            font-size: 2em;
            font-weight: 500;
            margin-bottom: 1em;
            background: rgba(0, 0, 0, 0.5);
        }
        .caption p span {
            color: #b40000;
        }
        a.morebtn {
            background: #ec4539;
            padding: 1em 2.3em;
            font-size: 0.875em;
            text-transform: uppercase;
            color: #fff;
            display: inline-block;
            border-radius: 7px;
        }
        a.morebtn:hover {
            background: #b40000;
        }
        .dwn {
            position: absolute;
            bottom: 4%;
            left: 47%;
        }
        
        /* ----- КАТЕГОРИИ (из style.css) ----- */
        .categories {
            background: url(images/cate.webp) no-repeat;
            background-size: cover;
            min-height: 600px;
            text-align: center;
            padding: 5em 0;
        }
        .categories h3 {
            color: #fff;
            font-size: 2em;
            font-weight: 600;
        }
        .categorie-grids {
            margin-top: 4em;
        }
        .cate-grid {
            padding: 10em 0 5em 0;
            min-height: 290px;
            background-size: cover !important;
            background-position: center !important;
            margin: 0 12px;
            width: 31%;
            float: left;
            text-align: center;
        }
        .cate-grid h4 {
            color: #fff;
            font-size: 2.1em;
            font-weight: 1000;
            letter-spacing: 1px;
            margin-bottom: 6px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
        }
        .cate-grid p {
            color: #fff;
            font-size: 1.6em;
            font-weight: 500;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
        }
        .grid1 { background: url(images/c1.jpg) no-repeat; background-size: cover; }
        .grid2 { background: url(images/c2.jpg) no-repeat; background-size: cover; }
        .grid3 { background: url(images/c3.jpg) no-repeat; background-size: cover; }
        .grid4 { background: url(images/c4.jpg) no-repeat; background-size: cover; }
        .grid5 { background: url(images/c5.png) no-repeat; background-size: cover; }
        .grid6 { background: url(images/c6.jpg) no-repeat; background-size: cover; }
        
        a.store {
            color: #ec4539;
            font-size: 0.9em;
            border: 3px solid #ec4539;
            border-radius: 7px;
            font-weight: 500;
            display: inline-block;
            padding: 0.7em 1.4em;
            margin-top: 0.5em;
            background: rgba(255,255,255,0.9);
        }
        a.store:hover {
            border: 3px solid #b40000;
            color: #b40000;
            background: #fff;
        }
        
        /* ----- ТОВАРЫ (из style.css) ----- */
        .bikes {
            padding: 6em 0 2em 0;
            background: url(images/perech.jpg) no-repeat;
            background-size: cover;
        }
        .bikes h3 {
            color: #fff;
            font-size: 2em;
            font-weight: 600;
            text-align: center;
        }
        .bikes-grids {
            margin-top: 8em;
        }
        .bike-info {
            padding: 0 20px;
            margin-top: 4em;
        }
        .model {
            float: left;
            width: 50%;
            text-align: left;
        }
        .model h4 {
            color: #ec4539;
            font-size: 1.1em;
            font-weight: 600;
        }
        .model h4 span {
            color: #ec4539;
            display: block;
            margin-top: 5px;
            font-size: 0.95em;
            font-weight: 300;
        }
        .model-info {
            float: left;
            width: 50%;
        }
        .model-info select {
            padding: 10px 2.5em 10px 0.5em;
            border-radius: 5px;
            margin-right: 5px;
            outline: none;
            cursor: pointer;
        }
        .model-info a {
            color: #fff;
            font-size: 0.9em;
            font-weight: 500;
            padding: 1em 2.5em;
            border-radius: 5px;
            background: #ec4539;
        }
        .model-info a:hover {
            background: #b40000;
            color: #fff;
        }
        .viw {
            position: absolute;
            background: #ec4539;
            padding: 1.3em 2.5em;
            font-size: 1em;
            font-weight: 500;
            top: 33%;
            left: 34%;
            display: none;
        }
        .viw a {
            color: #fff;
        }
        .nbs-flexisel-item:hover div.viw {
            display: block;
        }
        .nbs-flexisel-item {
            position: relative;
            float: left;
            list-style: none;
            padding: 0 15px;
        }
        .nbs-flexisel-ul {
            position: relative;
            width: 9999px;
            margin: 0;
            padding: 0;
            list-style-type: none;
            text-align: center;
        }
        .nbs-flexisel-inner {
            overflow: hidden;
        }
        .nbs-flexisel-container {
            position: relative;
            max-width: 100%;
        }
        .nbs-flexisel-item > img {
            cursor: auto;
            position: relative;
            width: 90%;
            margin: 0 5%;
        }
        
        /* ----- КОНТАКТЫ (из style.css) ----- */
        .contact {
            background: url(images/contact.jpg) no-repeat 0px 0px;
            min-height: 560px;
            background-size: cover;
            text-align: center;
            padding: 4em 0;
        }
        .contact h3 {
            color: #ec4539;
            font-size: 2em;
            font-weight: 600;
            margin-bottom: 0.7em;
        }
        .contact p {
            color: #b40000;
            font-size: 1.15em;
            font-weight: 500;
        }
        .contact form {
            width: 43%;
            margin: 3em auto 0 auto;
        }
        .contact form input[type="text"],
        .contact textarea {
            width: 43%;
            margin-right: 4%;
            padding: 10px;
            border: 1px solid #b7b7b7;
            font-size: 1em;
            margin-bottom: 2em;
            font-style: italic;
            color: #000;
            background: #fff;
            outline: none;
            font-weight: 400;
            border-radius: 7px;
        }
        .contact form input[type="text"].user,
        .contact textarea {
            width: 90%;
            resize: none;
        }
        .contact textarea {
            height: 170px;
        }
        .contact form input[type="submit"] {
            color: #fff;
            background: #ec4539;
            font-size: 1em;
            font-weight: 400;
            border: none;
            width: 90%;
            margin: 0 auto;
            padding: 10px 30px;
            display: block;
            border-radius: 7px;
        }
        .contact form input[type="submit"]:hover {
            background: #b40000;
            color: #fff;
        }
        
        /* ----- ФУТЕР (из style.css) ----- */
        .footer {
            background: #207bc5;
            padding: 1.5em;
        }
        .footer .logo2 {
            float: left;
        }
        .footer .ftr-menu {
            float: right;
        }
        .footer .ftr-menu ul li {
            display: inline-block;
            margin-top: 10px;
        }
        .footer .ftr-menu ul li a {
            display: block;
            text-decoration: none;
            font-size: 1.15em;
            font-weight: 500;
            color: #fff;
            padding: 0 1em;
        }
        .footer .ftr-menu ul li a:hover {
            color: #030303;
        }
        
        /* ----- ДРОПДАУН МЕНЮ (из nav.css) ----- */
        .dropdown1 {
            position: relative;
        }
        .dropdown2 {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: #ec4539;
            min-width: 200px;
            padding: 0;
            list-style: none;
            z-index: 999;
        }
        .dropdown1:hover .dropdown2 {
            display: block;
        }
        .dropdown2 li a {
            display: block;
            padding: 10px 15px;
            color: #fff !important;
            text-decoration: none;
        }
        .dropdown2 li a:hover {
            background: #b40000;
        }
        
        /* ----- АДМИНКА ----- */
        .admin-panel {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
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
            max-width: 100px;
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
        .login-form {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
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
        
        .wrap {
            width: 100%;
            max-width: 1170px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* ----- АДАПТИВ (из style.css) ----- */
        @media (max-width: 768px) {
            .cate-grid {
                width: 44%;
                margin: 0 7px;
                min-height: 200px;
                padding: 6em 0 0 0;
            }
            .contact form {
                width: 70%;
            }
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
        }
        @media (max-width: 480px) {
            .cate-grid {
                width: 26%;
                min-height: 125px;
                padding: 3.5em 0 0 0;
            }
            .cate-grid h4 {
                font-size: 0.7em;
            }
            .cate-grid p {
                display: none;
            }
            .contact form {
                width: 100%;
            }
            .caption h1 {
                font-size: 1.8em;
            }
            .caption p {
                font-size: 1.1em;
            }
        }
        @media (max-width: 320px) {
            .caption h1 {
                font-size: 1.3em;
            }
            .caption p {
                font-size: 0.8em;
            }
        }
    </style>
    
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $(".scroll").click(function(event){        
                event.preventDefault();
                $('html,body').animate({scrollTop:$(this.hash).offset().top},900);
            });
        });
    </script>
</head>
<body>

<!-- ===== БАННЕР ===== -->
<div class="banner-bg banner-bg1">    
    <div class="container">
        <div class="header">
            <div class="logo">
                <a href="?page=home"><img src="images/logo.png" alt=""/></a>
            </div>                             
            <div class="header_links">                                         
                <a href="product1.html">Одежда</a>
                <a href="product2.html">Игры</a>
                <a href="product3.html">Аксессуары</a>
                <a href="product4.html">Интерьер</a>
                <a href="404.html"><img src="images/cart.png" alt=""/></a>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                    <a href="?page=admin" class="admin-link">Админ</a>
                    <a href="?logout=1" class="admin-link">Выйти</a>
                <?php else: ?>
                    <a href="?page=login" class="login-link">Войти</a>
                <?php endif; ?>
            </div>
        </div>
    </div>     
    <div class="caption">
        <div class="slider">
            <div class="callbacks_container">
                <h1>Gentil</h1>
                <p>Сделай <span>лучше</span> свою <span>или</span> чужую <span>жизнь </span></p>
                <a class="morebtn" href="404.html">В магазин</a>
            </div>
        </div>
    </div>
    <div class="dwn">
        <a class="scroll" href="#cate"><img src="images/scroll.png" alt=""/></a>
    </div>                 
</div>

<?php if ($page == 'home'): ?>

<!-- ===== КАТЕГОРИИ ===== -->
<div id="cate" class="categories">
    <div class="container">
        <h3>КАТЕГОРИИ</h3>
        <div class="categorie-grids">
            <?php if (empty($categories)): ?>
                <a href="product2.html"><div class="cate-grid grid1">
                    <h4>Игры</h4>
                    <p>Для детей</p>
                    <a class="store" href="product2.html">В Магазин</a>
                </div></a>
                <a href="product4.html"><div class="cate-grid grid2">
                    <h4>Интерьер</h4>
                    <p>Фигурки</p>
                    <a class="store" href="product4.html">В Магазин</a>
                </div></a>
                <a href="product1.html"><div class="cate-grid grid3">
                    <h4>Одежда</h4>
                    <p>Для мужчин</p>
                    <a class="store" href="product1.html">В Магазин</a>
                </div></a>
                <a href="product4.html"><div class="cate-grid grid4">
                    <h4>Интерьер</h4>
                    <p>Полиграфия</p>
                    <a class="store" href="product4.html">В Магазин</a>
                </div></a>
                <a href="product1.html"><div class="cate-grid grid5">
                    <h4>Одежда</h4>
                    <p>Для женщин</p>
                    <a class="store" href="product1.html">В Магазин</a>
                </div></a>
                <a href="product4.html"><div class="cate-grid grid6">
                    <h4>Интерьер</h4>
                    <p>Посуда</p>
                    <a class="store" href="product4.html">В Магазин</a>
                </div></a>
            <?php else: ?>
                <?php foreach($categories as $cat): ?>
                    <a href="?page=category&id=<?php echo $cat['id']; ?>">
                        <div class="cate-grid" style="background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('<?php echo !empty($cat['image']) ? htmlspecialchars($cat['image']) : 'images/c1.jpg'; ?>') no-repeat center center; background-size: cover;">
                            <h4><?php echo htmlspecialchars($cat['name']); ?></h4>
                            <p><?php echo htmlspecialchars($cat['description']); ?></p>
                            <a class="store" href="?page=category&id=<?php echo $cat['id']; ?>">В Магазин</a>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
            <div class="clearfix"></div>
        </div>
    </div>
</div>

<!-- ===== ТОВАРЫ ===== -->
<div class="bikes">    
    <h3>Популярные товары</h3>
    <div class="bikes-grids">             
        <ul id="flexiselDemo1">
            <?php if (empty($products)): ?>
                <li>
                    <img src="images/e1.png" alt=""/>
                    <div class="bike-info">
                        <div class="model"><h4>Слоник<span>$249.00</span></h4></div>
                        <div class="model-info">
                            <select><option>OPTION</option></select>
                            <a href="e1.html">Купить</a>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="viw"><a href="e1.html">Посмотреть</a></div>
                </li>
                <li>
                    <img src="images/e2.png" alt=""/>
                    <div class="bike-info">
                        <div class="model"><h4>Сова<span>$249.00</span></h4></div>
                        <div class="model-info">
                            <select><option>OPTION</option></select>
                            <a href="e2.html">Купить</a>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="viw"><a href="e2.html">Посмотреть</a></div>
                </li>
                <li>
                    <img src="images/e3.png" alt=""/>
                    <div class="bike-info">
                        <div class="model"><h4>Медвежонок<span>$300.00</span></h4></div>
                        <div class="model-info">
                            <select><option>OPTION</option></select>
                            <a href="e3.html">Купить</a>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="viw"><a href="e3.html">Посмотреть</a></div>
                </li>
                <li>
                    <img src="images/e4.png" alt=""/>
                    <div class="bike-info">
                        <div class="model"><h4>Эльфивая Башня<span>$249.00</span></h4></div>
                        <div class="model-info">
                            <select><option>OPTION</option></select>
                            <a href="e4.html">Купить</a>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="viw"><a href="e4.html">Посмотреть</a></div>
                </li>
                <li>
                    <img src="images/e5.png" alt=""/>
                    <div class="bike-info">
                        <div class="model"><h4>Подставка<span>$340.00</span></h4></div>
                        <div class="model-info">
                            <select><option>OPTION</option></select>
                            <a href="e5.html">Купить</a>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="viw"><a href="e5.html">Посмотреть</a></div>
                </li>
                <li>
                    <img src="images/e6.png" alt=""/>
                    <div class="bike-info">
                        <div class="model"><h4>Ёжик<span>$249.00</span></h4></div>
                        <div class="model-info">
                            <select><option>OPTION</option></select>
                            <a href="e6.html">Купить</a>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="viw"><a href="e6.html">Посмотреть</a></div>
                </li>
            <?php else: ?>
                <?php foreach($products as $product): ?>
                    <li>
                        <img src="<?php echo !empty($product['image']) ? htmlspecialchars($product['image']) : 'images/e1.png'; ?>" alt=""/>
                        <div class="bike-info">
                            <div class="model">
                                <h4><?php echo htmlspecialchars($product['name']); ?><span>$<?php echo htmlspecialchars($product['price']); ?></span></h4>
                            </div>
                            <div class="model-info">
                                <select><option>OPTION</option></select>
                                <a href="<?php echo !empty($product['link']) ? htmlspecialchars($product['link']) : '#'; ?>">Купить</a>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="viw">
                            <a href="<?php echo !empty($product['link']) ? htmlspecialchars($product['link']) : '#'; ?>">Посмотреть</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        
        <script type="text/javascript">
            $(window).load(function() {            
                $("#flexiselDemo1").flexisel({
                    visibleItems: 3,
                    animationSpeed: 2000,
                    autoPlay: true,
                    autoPlaySpeed: 4000,            
                    pauseOnHover: true,
                    enableResponsiveBreakpoints: true,
                    responsiveBreakpoints: { 
                        portrait: { changePoint: 480, visibleItems: 1 }, 
                        landscape: { changePoint: 640, visibleItems: 2 },
                        tablet: { changePoint: 768, visibleItems: 3 }
                    }
                });
            });
        </script>            
    </div>
</div>

<!-- ===== КОНТАКТЫ ===== -->
<div class="contact">
    <div class="container">
        <h3>Свяжитесь с нами</h3>
        <p>Хотите предложить новый товар, идею или ищете работу - пишите!</p>
        <?php if (isset($form_msg)): ?>
            <div class="msg-success"><?php echo $form_msg; ?></div>
        <?php endif; ?>
        <form action="" method="post">
            <input type="text" name="user_name" placeholder="Имя" required="">
            <input type="text" name="user_surname" placeholder="Фамилия" required="">             
            <input class="user" name="Email" type="text" placeholder="E-mail" required=""><br>
            <textarea name="message" placeholder="Сообщение" required=""></textarea>
            <input type="submit" name="submit" value="Отправить">
        </form>
    </div>
</div>

<?php endif; ?>

<?php if ($page == 'login'): ?>
<div style="padding:50px 0; background:#f5f5f5;">
    <div class="login-form">
        <h1>🔑 Авторизация</h1>
        <?php if (isset($error)): ?>
            <div class="msg-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="login" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit" name="login_btn">Войти</button>
        </form>
        <p style="text-align:center; margin-top:20px;">
            <strong>Логин: admin, Пароль: admin</strong>
        </p>
        <p style="text-align:center;">
            <a href="?page=home">На главную</a>
        </p>
    </div>
</div>
<?php endif; ?>

<?php if ($page == 'admin'): ?>
<?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin'): ?>
    <div style="padding:50px 0; text-align:center; background:#f5f5f5;">
        <h1>⛔ Доступ запрещен</h1>
        <p>У вас нет прав доступа! <a href="?page=login">Войти</a></p>
    </div>
<?php else: ?>
    <div style="background: #f0f0f0; padding: 2em 0;">
        <div class="container">
            <div class="admin-panel">
                <h1 style="text-align:center; color:#333;">⚡ Админ-панель</h1>
                <p style="text-align:center; font-size:1.2em;">Добро пожаловать, <strong><?php echo $_SESSION['user_login']; ?></strong>! 👋</p>
                <p style="text-align:center; margin-bottom:30px;">
                    <a href="?page=home" style="color:#007bff;">← На главную</a>
                </p>
                
                <!-- Добавление категории -->
                <div class="admin-form">
                    <h2>➕ Добавить категорию</h2>
                    <?php if (isset($msg_cat)): ?>
                        <div class="msg-success">✅ <?php echo $msg_cat; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="text" name="category_name" placeholder="Название категории" required>
                        <input type="text" name="category_description" placeholder="Описание">
                        <input type="text" name="category_image" placeholder="Ссылка на картинку (images/c1.jpg)">
                        <button type="submit" name="add_category_btn">➕ Добавить категорию</button>
                    </form>
                </div>

                <!-- Добавление товара -->
                <div class="admin-form">
                    <h2>➕ Добавить товар</h2>
                    <?php if (isset($msg_product)): ?>
                        <div class="msg-success">✅ <?php echo $msg_product; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <select name="category_id" required>
                            <option value="">Выберите категорию</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="product_name" placeholder="Название товара" required>
                        <input type="text" name="product_price" placeholder="Цена (249.00)" required>
                        <textarea name="product_description" placeholder="Описание" rows="3"></textarea>
                        <input type="text" name="product_image" placeholder="Ссылка на картинку (images/e1.png)">
                        <input type="text" name="product_link" placeholder="Ссылка (e1.html)">
                        <button type="submit" name="add_product_btn">➕ Добавить товар</button>
                    </form>
                </div>

                <!-- Список категорий -->
                <h2 style="color:#333;">📋 Все категории (<?php echo count($categories); ?>)</h2>
                <?php foreach($categories as $cat): ?>
                    <div class="item-card">
                        <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                        <p><?php echo htmlspecialchars($cat['description']); ?></p>
                        <?php if (!empty($cat['image'])): ?>
                            <img src="<?php echo htmlspecialchars($cat['image']); ?>" alt="">
                        <?php endif; ?>
                        <br>
                        <a href="?page=admin&delete_category=<?php echo $cat['id']; ?>" 
                           class="delete-btn" onclick="return confirm('Удалить категорию?')">🗑 Удалить</a>
                    </div>
                <?php endforeach; ?>

                <!-- Список товаров -->
                <h2 style="color:#333;">📋 Все товары (<?php echo count($products); ?>)</h2>
                <?php foreach($products as $product): ?>
                    <div class="item-card">
                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                        <p><strong>Цена:</strong> $<?php echo htmlspecialchars($product['price']); ?></p>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="">
                        <?php endif; ?>
                        <br>
                        <small style="color:#999;">Добавлено: <?php echo $product['created_at']; ?></small>
                        <br>
                        <a href="?page=admin&delete_product=<?php echo $product['id']; ?>" 
                           class="delete-btn" onclick="return confirm('Удалить товар?')">🗑 Удалить</a>
                    </div>
                <?php endforeach; ?>
                
                <p style="text-align:center; margin-top:30px;">
                    <a href="?page=home">← На главную</a>
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php endif; ?>

<!-- ===== ФУТЕР ===== -->
<div class="footer">
    <div class="wrap">
        <div class="logo2">
            <a href="?page=home"><img src="images/logo.png" alt=""/></a>
        </div>
        <div class="ftr-menu">
            <ul>
                <li><a href="product1.html">Одежда</a></li>
                <li><a href="product2.html">Игры</a></li>
                <li><a href="product3.html">Аксессуары</a></li>
                <li><a href="product4.html">Интерьер</a></li>
            </ul>
        </div>
        <div class="clearfix"></div>
    </div>
</div>

</body>
</html>
<?php mysqli_close($conn); ?>



-- ============================================================
-- БАЗА ДАННЫХ ДЛЯ САЙТА "Gentil" (Интернет-магазин)
-- ============================================================

-- 1. СОЗДАНИЕ БАЗЫ ДАННЫХ
CREATE DATABASE IF NOT EXISTS exam_db;
USE exam_db;

-- ============================================================
-- 2. УДАЛЕНИЕ СТАРЫХ ТАБЛИЦ (ЕСЛИ НУЖНО ПЕРЕСОЗДАТЬ)
-- ============================================================
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS messages;
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
-- 4. ТАБЛИЦА КАТЕГОРИЙ
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description VARCHAR(255),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. ТАБЛИЦА ТОВАРОВ
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    price VARCHAR(50) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. ТАБЛИЦА СООБЩЕНИЙ (ФОРМА ОБРАТНОЙ СВЯЗИ)
-- ============================================================
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255),
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. ДОБАВЛЕНИЕ АДМИНИСТРАТОРА
-- ============================================================
INSERT INTO users (login, password, role) VALUES 
('admin', 'admin', 'admin')
ON DUPLICATE KEY UPDATE login=login;

-- ============================================================
-- 8. ДОБАВЛЕНИЕ ТЕСТОВЫХ КАТЕГОРИЙ
-- ============================================================
INSERT INTO categories (name, description, image) VALUES 
('Игры', 'Для детей', 'images/c1.jpg'),
('Интерьер', 'Фигурки', 'images/c2.jpg'),
('Одежда', 'Для мужчин', 'images/c3.jpg'),
('Интерьер', 'Полиграфия', 'images/c4.jpg'),
('Одежда', 'Для женщин', 'images/c5.png'),
('Интерьер', 'Посуда', 'images/c6.jpg')
ON DUPLICATE KEY UPDATE name=name;

-- ============================================================
-- 9. ДОБАВЛЕНИЕ ТЕСТОВЫХ ТОВАРОВ
-- ============================================================
INSERT INTO products (category_id, name, price, description, image, link) VALUES 
(1, 'Слоник', '249.00', 'Милый слоник из коллекции', 'images/e1.png', 'e1.html'),
(1, 'Сова', '249.00', 'Мудрая сова', 'images/e2.png', 'e2.html'),
(1, 'Медвежонок', '300.00', 'Плюшевый медвежонок', 'images/e3.png', 'e3.html'),
(2, 'Эльфивая Башня', '249.00', 'Сказочная башня', 'images/e4.png', 'e4.html'),
(2, 'Подставка под снифтер', '340.00', 'Стильная подставка', 'images/e5.png', 'e5.html'),
(1, 'Ёжик', '249.00', 'Коллекционный ёжик', 'images/e6.png', 'e6.html')
ON DUPLICATE KEY UPDATE name=name;

-- ============================================================
-- 10. ДОБАВЛЕНИЕ ТЕСТОВЫХ СООБЩЕНИЙ
-- ============================================================
INSERT INTO messages (name, surname, email, message) VALUES 
('Иван', 'Петров', 'ivan@mail.ru', 'Хочу заказать товар, свяжитесь со мной'),
('Мария', 'Смирнова', 'maria@mail.ru', 'Интересует доставка в другой город'),
('Алексей', 'Иванов', 'alex@mail.ru', 'Есть ли скидки для оптовых покупателей?');

-- ============================================================
-- 11. ПРОВЕРКА ДАННЫХ
-- ============================================================
SELECT * FROM users;
SELECT * FROM categories;
SELECT * FROM products;
SELECT * FROM messages;

-- ============================================================
-- 12. ПОКАЗАТЬ СТРУКТУРУ ТАБЛИЦ
-- ============================================================
DESCRIBE users;
DESCRIBE categories;
DESCRIBE products;
DESCRIBE messages;

-- ============================================================
-- 13. ДОПОЛНИТЕЛЬНЫЕ ЗАПРОСЫ
-- ============================================================

-- Количество записей в таблицах
SELECT 'users' as table_name, COUNT(*) as count FROM users
UNION ALL
SELECT 'categories', COUNT(*) FROM categories
UNION ALL
SELECT 'products', COUNT(*) FROM products
UNION ALL
SELECT 'messages', COUNT(*) FROM messages;

-- Все товары с названиями категорий
SELECT p.id, p.name, p.price, p.description, c.name as category_name 
FROM products p 
LEFT JOIN categories c ON p.category_id = c.id 
ORDER BY p.id;

-- Товары по категории "Игры"
SELECT * FROM products WHERE category_id = (SELECT id FROM categories WHERE name = 'Игры');

-- Последние 5 сообщений из формы обратной связи
SELECT * FROM messages ORDER BY id DESC LIMIT 5;

-- Количество товаров по категориям
SELECT c.name, COUNT(p.id) as product_count 
FROM categories c 
LEFT JOIN products p ON c.id = p.category_id 
GROUP BY c.id;

-- ============================================================
-- 14. ПОИСК ТОВАРОВ
-- ============================================================
-- SELECT * FROM products WHERE name LIKE '%сова%';
-- SELECT * FROM products WHERE price < 300;

-- ============================================================
-- 15. ОЧИСТКА ТАБЛИЦ (ЕСЛИ НУЖНО)
-- ============================================================
-- TRUNCATE TABLE products;
-- TRUNCATE TABLE categories;
-- TRUNCATE TABLE messages;
-- TRUNCATE TABLE users;

-- ============================================================
-- 16. ПОЛНОЕ УДАЛЕНИЕ БАЗЫ (ЕСЛИ НУЖНО ПЕРЕСОЗДАТЬ)
-- ============================================================
-- DROP DATABASE IF EXISTS exam_db;