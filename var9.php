<?php
$host = 'localhost';
$dbname = 'kvas_db';
$username = 'root';
$password = '';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(50) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    alcohol VARCHAR(50),
    calories VARCHAR(50),
    volume VARCHAR(50),
    price VARCHAR(50),
    image VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    phone2 VARCHAR(50),
    email VARCHAR(255) NOT NULL,
    volume_order VARCHAR(50),
    message TEXT,
    subscription TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$check_admin = mysqli_query($conn, "SELECT * FROM users WHERE login='admin'");
if (mysqli_num_rows($check_admin) == 0) {
    mysqli_query($conn, "INSERT INTO users (login, password, role) VALUES ('admin', 'admin', 'admin')");
}

$check_products = mysqli_query($conn, "SELECT * FROM products");
if (mysqli_num_rows($check_products) == 0) {
    mysqli_query($conn, "INSERT INTO products (name, alcohol, calories, volume, price, image, description) VALUES 
        ('Вахтёр', '< 1%', '35', '0.5 л', '150 Р.', 'img/bottle-left.jpg', 'Терпкий и мощный, вкус этого кваса взбодрит после тяжёлого дня и придаст сил для вечерних приключений!'),
        ('Романтик', '< 0.5%', '27', '0.5 л', '90 Р.', 'img/bottle-right.jpg', 'Нежный и мягкий вкус этого кваса создан для романтических вечеров и приятных встреч с друзьями.'),
        ('Классика', '< 1.2%', '33', '330 мл', '120 Р.', 'img/bottle-classic.jpg', 'Традиционный русский квас по классическому рецепту.'),
        ('Шабаш', '> 1.2%', '45', '330 мл', '260 Р.', 'img/bottle-shabash.jpg', 'Крепкий квас для настоящих ценителей.')");
}

session_start();

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

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ?page=home');
    exit;
}

if (isset($_POST['add_product_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $alcohol = mysqli_real_escape_string($conn, trim($_POST['alcohol']));
    $calories = mysqli_real_escape_string($conn, trim($_POST['calories']));
    $volume = mysqli_real_escape_string($conn, trim($_POST['volume']));
    $price = mysqli_real_escape_string($conn, trim($_POST['price']));
    $image = mysqli_real_escape_string($conn, trim($_POST['image']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    
    if ($name && $price) {
        mysqli_query($conn, "INSERT INTO products (name, alcohol, calories, volume, price, image, description) 
                            VALUES ('$name', '$alcohol', '$calories', '$volume', '$price', '$image', '$description')");
        $msg_product = 'Товар добавлен!';
    } else {
        $msg_product = 'Заполните название и цену!';
    }
}

if (isset($_GET['delete_product']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    mysqli_query($conn, "DELETE FROM products WHERE id=" . intval($_GET['delete_product']));
    header('Location: ?page=admin');
    exit;
}

$products = [];
$result_prod = mysqli_query($conn, "SELECT * FROM products ORDER BY id");
if ($result_prod) {
    while ($row = mysqli_fetch_assoc($result_prod)) $products[] = $row;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

if (isset($_POST['order_submit'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $phone2 = mysqli_real_escape_string($conn, trim($_POST['phone-two']));
    $email = mysqli_real_escape_string($conn, trim($_POST['mail']));
    $volume_order = mysqli_real_escape_string($conn, trim($_POST['size']));
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));
    $subscription = isset($_POST['subscription']) ? 1 : 0;
    
    if ($name && $phone && $email) {
        mysqli_query($conn, "INSERT INTO orders (name, phone, phone2, email, volume_order, message, subscription) 
                            VALUES ('$name', '$phone', '$phone2', '$email', '$volume_order', '$message', '$subscription')");
        $order_msg = 'Ваш заказ отправлен! Мы свяжемся с вами в ближайшее время.';
    } else {
        $order_msg = 'Заполните все обязательные поля!';
    }
}
?><!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Кваст</title>
<style>
/* ===== ВСЕ СТИЛИ ИЗ STYLE.CSS ===== */
body{font:12px/18px Arial,Tahoma,Verdana,sans-serif;background:#fff;width:100%;min-width:1040px;margin:0;padding:0}
h2,h3{text-transform:uppercase}
h2{font-size:36px}
h3{font-size:22px}
.center{width:900px;margin:0 auto}
.page-header{height:650px;background:url(img/background.jpg) no-repeat 50% 50%}
.page-header a{color:#fff;text-decoration:none}
.header-top{padding-top:50px}
.header-logo{position:absolute}
.header-menu{width:600px;border-top:1px solid grey;border-bottom:1px solid grey;margin-left:200px;padding-left:0}
.header-menu li{display:inline-block;text-transform:uppercase;padding:20px 50px 20px 0}
.header-menu li:last-child{padding-right:0}
.header-menu a{display:block;font-size:15px;font-weight:bold}
.header-promo{margin-top:120px;color:#fff;font-size:72px;font-weight:bold;text-align:center;line-height:72px}
.header-promo p{width:830px;margin:0 auto;position:absolute}
.header-promo .hidden{visibility:hidden}
.slider-menu{margin-top:410px;color:#fff;width:170px}
.slider-menu li{display:inline-block;width:7px;height:7px;padding:2px;margin-right:40px;background-color:#fff;opacity:0.6}
li.slideractive{opacity:1}
.new-sale{margin-top:100px}
.new-sale h2{font-size:36px;margin-bottom:110px}
.new-sale h3{font-size:36px}
.new-sale table{border-bottom:1px solid grey}
.new-item{display:inline-block;width:340px;height:600px;border-left:1px solid grey;margin:0;padding:30px}
.new-item:last-child{border-right:1px solid grey}
.new-item img{position:relative;top:-130px}
.new-item table{text-transform:uppercase;font-weight:bold}
.new-item table,.new-item .sost{width:150px;font-size:14px;position:relative;left:220px;z-index:5}
.new-item h3,.ptop,.btn1,.btn2,b{position:relative;top:-150px}
.new-item .ptop{font-size:16px;line-height:16px}
.btn1{display:inline-block;text-align:center;vertical-align:middle;text-decoration:none;text-transform:uppercase;width:142px;height:42px;line-height:46px;color:#834700;font-size:14px;font-weight:bold;border:2px solid #834700}
.btn1:hover{background:#834700;color:#fff}
.btn2{margin-left:5px;margin-right:30px;display:inline-block;text-align:center;text-transform:uppercase;text-decoration:none;vertical-align:middle;width:106px;height:46px;line-height:46px;color:#fff;font-size:14px;font-weight:bold;background:#834700}
.btn2:hover{background:#A65A00}
.advantages{margin-top:-40px;height:400px;background:#FFCB78}
.advantages li{margin-top:110px;display:inline-block;vertical-align:bottom;text-align:left;padding:0 40px;width:210px;height:230px;font-size:16px;line-height:26px;border-right:1px solid #E7B66A}
.advantages li:last-child{border-right:none}
.advantages li p{color:#434446;margin-top:30px}
.history{margin-top:90px}
.history table{width:100%;font-size:16px;line-height:26px;margin-top:80px}
.history table td:first-child{width:490px}
.history table td:last-child{padding-left:110px}
.history table a{color:#894A03}
.press-block{height:480px;background:#EDE5D8}
.press h2{font-size:40px;padding-top:100px;margin-bottom:90px}
.press .presspost{width:690px;font-size:16px;line-height:32px;color:#414342}
.press a,.cite{display:inline-block;margin-top:20px}
.press .cite{font-weight:bold;text-transform:uppercase;margin-left:270px;font-size:20px}
.press a{margin-bottom:40px;text-align:center;text-transform:uppercase;text-decoration:none;width:166px;height:46px;line-height:46px;color:#C0B8AD;font-size:16px;font-weight:bold;border:2px solid #A69F95}
.press a:hover{background:#A69F95;color:#fff}
.stock{height:630px}
.stock h2{font-size:40px;padding-top:80px;margin-bottom:90px}
.stock table{text-transform:uppercase;border-collapse:collapse}
.stock table tr{line-height:46px;font-size:16px;border-bottom:1px solid #E5E5E5}
.stock th{font-size:12px;font-weight:400;color:#B7B7B7;text-align:left;padding-bottom:20px}
.stock th:first-child,.stock td:first-child{width:400px;height:65px}
.stock td:first-child{font-weight:bold}
.stock th:nth-child(2),.stock td:nth-child(2){width:60px;height:65px;text-align:center}
.stock th:nth-child(3),.stock td:nth-child(3){width:60px;height:65px;text-align:center}
.stock th:nth-child(4),.stock td:nth-child(4){width:100px;height:65px;text-align:center}
.stock th:nth-child(5),.stock td:nth-child(5){width:200px;height:65px;text-align:right}
.stock td:nth-child(n+2){color:#454545}
.stock td sup{font-weight:bold;color:#fff;background:#000;font-size:8px;padding:2px 5px;margin-left:20px}
.order-form{height:1090px;background:#FFCB78}
.order h2{font-size:40px;padding-top:100px;margin-bottom:100px}
.order p{width:650px;font-size:16px;line-height:32px;color:#414342;margin-bottom:70px}
.order .feedback-form-group{margin-bottom:10px}
.order label{display:inline-block;vertical-align:middle;width:200px;text-transform:uppercase;font-weight:bold;font-size:15px;line-height:50px;height:50px}
.order input{display:inline-block;width:580px;height:50px;background:#E6B66C;border:none}
.order input[placeholder],.order select[placeholder],.order textarea[placeholder]{text-transform:uppercase;font-weight:bold;font-size:15px;line-height:50px;padding-left:20px}
.order select option{text-transform:uppercase;font-weight:bold;font-size:25px;line-height:50px;padding-left:20px}
.order input::-webkit-input-placeholder,.order select::-webkit-input-placeholder,.order textarea::-webkit-input-placeholder{color:#B49157}
.order select{width:600px;height:50px;background:#E6B66C;border:none;color:#B49157}
.order .labtext{vertical-align:top}
.order textarea{width:580px;height:150px;background:#E6B66C;border:none}
#phone,#phone-two{width:180px}
.size{margin-top:60px}
#labelphone-two{margin-left:50px;margin-right:35px;width:110px}
.checkbox-area input{display:none}
.checkbox-area label{margin-top:40px;display:inline-block;width:700px;height:20px;line-height:40px;font-size:16px}
.checkbox-area label span{vertical-align:top;margin-left:20px}
.check{content:"";position:relative;display:inline-block;width:40px;height:40px;line-height:20px;margin-left:200px;background:#E6B66C;cursor:pointer}
.check:after{content:"";position:absolute;top:20px;left:15px;display:inline-block;width:20px;height:3px;background:#000;transform:rotate(-45deg);display:none}
.check:before{content:"";position:absolute;top:23px;left:10px;display:inline-block;width:10px;height:3px;background:#000;transform:rotate(45deg);display:none}
.checkbox-area input:checked ~ .check:before,.checkbox-area input:checked ~ .check:after{display:block}
.order input[type="submit"]{display:inline-block;text-align:center;text-transform:uppercase;vertical-align:middle;width:205px;height:55px;line-height:50px;color:#fff;font-size:14px;font-weight:bold;background:#834700;margin-left:200px;margin-top:40px;border:none;cursor:pointer}
.order input[type="submit"]:hover{background:#A65A00}
.page-footer{color:#fff;background:#000;height:300px}
.footer-top{width:940px;padding-top:50px}
.footer-logo{position:absolute}
.page-footer a{color:#fff;text-decoration:none}
.page-footer a:hover{color:#FFCB78}
.footer-menu{width:650px;margin-left:200px;padding-left:0}
.footer-menu li{display:inline-block;text-transform:uppercase;padding:20px 50px 20px 0}
.footer-menu li:last-child{padding-right:0}
.footer-menu a{display:block;font-size:15px;font-weight:bold}
.footer-bottom{width:1030px;margin:50px auto}
.footer-bottom div{display:inline-block;vertical-align:middle}
.social-btn img{display:inline-block;width:75px;height:68px;margin-right:-6px}
.social-btn:hover{opacity:0.7}

/* ===== СТИЛИ ДЛЯ АДМИНКИ ===== */
.admin-panel{max-width:1200px;margin:0 auto;padding:20px;background:#fff;border-radius:10px}
.admin-form{background:#f9f9f9;padding:20px;border-radius:10px;margin-bottom:30px;border:1px solid #ddd}
.admin-form input,.admin-form textarea,.admin-form select{width:100%;padding:10px;margin:10px 0;border:1px solid #ddd;border-radius:5px;box-sizing:border-box}
.admin-form button{padding:12px 30px;background:#28a745;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:16px}
.admin-form button:hover{background:#218838}
.item-card{border:1px solid #ddd;padding:15px;margin:10px 0;border-radius:5px;background:#fff}
.item-card img{max-width:100px;border-radius:5px}
.delete-btn{padding:5px 15px;background:#dc3545;color:#fff;border:none;border-radius:5px;cursor:pointer;text-decoration:none;display:inline-block;margin-top:10px}
.delete-btn:hover{background:#c82333}
.msg-success{color:green;padding:10px;background:#d4edda;border-radius:5px;margin:10px 0}
.msg-error{color:red;padding:10px;background:#f8d7da;border-radius:5px;margin:10px 0}
.login-form{max-width:400px;margin:50px auto;padding:30px;background:#fff;border-radius:10px;box-shadow:0 5px 30px rgba(0,0,0,0.1)}
.login-form h1{text-align:center;margin-bottom:30px}
.login-form input{width:100%;padding:12px;margin:10px 0;border:1px solid #ddd;border-radius:5px;box-sizing:border-box}
.login-form button{width:100%;padding:12px;background:#007bff;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:16px}
.login-form button:hover{background:#0069d9}
.admin-link{color:red!important;font-weight:bold}
.login-link{color:#007bff!important;font-weight:bold}
.header-menu .admin-link,.header-menu .login-link{display:inline-block;padding:20px 20px 20px 0}

/* ===== АДАПТИВ ===== */
@media(max-width:1040px){body{min-width:auto}.center{width:100%;padding:0 20px}.header-menu{width:auto;margin-left:180px}.header-menu li{padding:20px 30px 20px 0}.header-promo p{width:100%;font-size:48px;line-height:56px}.new-item{width:45%;margin:0}.new-item table,.new-item .sost{left:150px}.order p{width:100%}.order input,.order textarea{width:100%}.order select{width:100%}.order label{width:150px}.check{margin-left:150px}.order input[type="submit"]{margin-left:150px}.footer-top{width:100%}.footer-menu{width:auto;margin-left:180px}.footer-menu li{padding:20px 30px 20px 0}.footer-bottom{width:100%}.advantages li{width:30%;padding:0 20px}}
@media(max-width:768px){.header-top{padding-top:20px}.header-logo{position:relative;text-align:center}.header-logo img{width:80px}.header-menu{margin-left:0;border:none;width:100%;text-align:center}.header-menu li{padding:10px 15px}.header-promo{margin-top:60px}.header-promo p{font-size:32px;line-height:40px;position:relative}.page-header{height:500px}.new-item{width:100%;border:none;border-bottom:1px solid #ccc}.new-item:last-child{border:none}.advantages{height:auto;padding:30px 0}.advantages li{width:100%;border:none;padding:20px 0;text-align:center;margin-top:20px}.history table td:first-child{width:100%;display:block}.history table td:last-child{padding-left:0;display:block}.history table img{max-width:150px}.press-block{height:auto;padding-bottom:40px}.press .presspost{width:100%}.press .cite{margin-left:0;display:block}.stock{height:auto;padding-bottom:40px}.stock table{font-size:12px}.stock th:first-child,.stock td:first-child{width:auto}.order-form{height:auto;padding-bottom:40px}.order label{width:100%}.check{margin-left:0}.order input[type="submit"]{margin-left:0;width:100%}.checkbox-area label{width:100%}#labelphone-two{margin-left:0}.footer-top{text-align:center}.footer-logo{position:relative;display:block;margin:0 auto}.footer-menu{margin-left:0;text-align:center}.footer-menu li{padding:10px 15px}.page-footer{height:auto;padding-bottom:20px}.footer-bottom{text-align:center}.header-menu .admin-link,.header-menu .login-link{padding:10px 15px}}
</style>
</head>
<body>

<div id="pheader" class="page-header">
    <div class="header-top center">
        <div class="header-logo">
            <img class="logo" src="img/logo.png" alt="КВАСТ">
        </div>
        <ul class="header-menu">
            <li><a href="#history">История</a></li>
            <li><a href="#manufacture">Производство</a></li>
            <li><a href="#assortment">Ассортимент</a></li>
            <li><a href="#buy">Где купить</a></li>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                <li><a href="?page=admin" class="admin-link">Админ</a></li>
                <li><a href="?logout=1" class="admin-link">Выйти</a></li>
            <?php else: ?>
                <li><a href="?page=login" class="login-link">Войти</a></li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="header-promo center">
        <p>Крафтовый квас<br> всему голова!</p>
    </div>
</div>

<?php if ($page == 'home'): ?>

<div class="new-sale center">
    <h2>Новинки</h2>
    <?php 
    $new_products = array_slice($products, 0, 2);
    if (empty($new_products)): ?>
        <div class="new-item">
            <table>
                <tr><td>Алк.</td><td>&lt; 1%</td></tr>
                <tr><td>Ккал:</td><td>35</td></tr>
            </table>
            <p class="sost">Ржаной хлеб, Вода, Солод, Соль</p>
            <img src="img/bottle-left.jpg" alt="">
            <h3>Вахтёр</h3>
            <p class="ptop">Терпкий и мощный, вкус этого кваса взбодрит после тяжёлого дня и придаст сил для вечерних приключений!</p>
            <a class="btn1" href="#">Подробнее</a>
            <a class="btn2" href="#">Купить</a>
            <b>150 р.</b>
        </div>
        <div class="new-item">
            <table>
                <tr><td>Алк.</td><td>&lt; 0.5%</td></tr>
                <tr><td>Ккал:</td><td>27</td></tr>
            </table>
            <p class="sost">Ржаной хлеб, Вода, Солод, Соль</p>
            <img src="img/bottle-right.jpg" alt="">
            <h3>Романтик</h3>
            <p class="ptop">Нежный и мягкий вкус этого кваса создан для романтических вечеров и приятных встреч с друзьями.</p>
            <a class="btn1" href="#">Подробнее</a>
            <a class="btn2" href="#">Купить</a>
            <b>90 р.</b>
        </div>
    <?php else: ?>
        <?php foreach($new_products as $product): ?>
            <div class="new-item">
                <table>
                    <tr><td>Алк.</td><td><?php echo htmlspecialchars($product['alcohol']); ?></td></tr>
                    <tr><td>Ккал:</td><td><?php echo htmlspecialchars($product['calories']); ?></td></tr>
                </table>
                <p class="sost">Ржаной хлеб, Вода, Солод, Соль</p>
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="ptop"><?php echo htmlspecialchars($product['description']); ?></p>
                <a class="btn1" href="#">Подробнее</a>
                <a class="btn2" href="#">Купить</a>
                <b><?php echo htmlspecialchars($product['price']); ?></b>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<a id="manufacture"></a>
<div class="advantages">
    <ul class="center">
        <li>
            <img src="img/pic1.png" alt="">
            <h3>Варим сами</h3>
            <p>Вот этими самыми руками. Экспериментируем и творим, что хотим.</p>
        </li>
        <li>
            <img src="img/pic2.png" alt="">
            <h3>Своё, родное</h3>
            <p>Не заработка ради, а импортозамещения для. Поднимаем производство.</p>
        </li>
        <li>
            <img src="img/pic3.png" alt="">
            <h3>Не экономим</h3>
            <p>Человек это то, что он ест и пьет. У нас только качественные ингредиенты.</p>
        </li>
    </ul>
</div>

<a id="history"></a>
<div class="history center">
    <h2>Немного истории</h2>
    <table>
        <tr>
            <td><h3>Происхождение кваса</h3></td>
            <td><i>Источник: <a href="#">ru.wikipedia.org</a></i></td>
        </tr>
        <tr>
            <td><p>Квас на Руси появился в X-XI веке. Само слово &laquo;квас&raquo; тоже имеет древнерусское происхождение. Помимо России квас готовят в Белоруссии, Сербии, Македонии, Словакии и многих других странах, но называется он везде одинаково &mdash; квас.</p></td>
            <td><img src="img/wiki-1.png" alt=""></td>
        </tr>
        <tr>
            <td><h3>Классификация кваса</h3></td>
            <td><i>Источник: <a href="#">ru.wikipedia.org</a></i></td>
        </tr>
        <tr>
            <td><p>По российскому ГОСТу для промышленного изготовления &mdash; это напиток с объёмной долей этилового спирта не более 1,2%, изготовленный в результате незавершённого спиртового и молочнокислого брожения сусла.</p></td>
            <td><img src="img/wiki-2.png" alt=""></td>
        </tr>
    </table>
</div>

<div class="press-block">
    <div class="press center">
        <h2>Пресса о нас</h2>
        <p class="presspost">Сегодня мы провели дегустацию чего-то действительно новенького &mdash; крафтового кваса. Да, да, вы не ослышались! Спасибо Арсену и Руслану за то, что предоставили целую бочку своего напитка!</p>
        <a class="btn" href="">Читать далее</a>
        <p class="cite">Газета столичный Cтольник</p>
    </div>
</div>

<a id="assortment"></a>
<div class="stock center">
    <h2>Ассортимент</h2>
    <table>
        <tr>
            <th>Название</th>
            <th>Алк.</th>
            <th>Ккал</th>
            <th>Объём</th>
            <th>Стоимость</th>
        </tr>
        <?php if (empty($products)): ?>
            <tr><td>Классика</td><td>&lt; 1.2%</td><td>33</td><td>330 мл</td><td>120 Р.</td></tr>
            <tr><td>Шабаш</td><td>&gt; 1.2%</td><td>45</td><td>330 мл</td><td>260 Р.</td></tr>
            <tr><td>Вахтёр <sup>Новинка</sup></td><td>&lt; 1%</td><td>35</td><td>0.5 л</td><td>150 Р.</td></tr>
            <tr><td>Романтик <sup>Новинка</sup></td><td>&lt; 0.5%</td><td>27</td><td>0.5 л</td><td>90 Р.</td></tr>
        <?php else: ?>
            <?php foreach($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo htmlspecialchars($product['alcohol']); ?></td>
                    <td><?php echo htmlspecialchars($product['calories']); ?></td>
                    <td><?php echo htmlspecialchars($product['volume']); ?></td>
                    <td><?php echo htmlspecialchars($product['price']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>

<a id="buy"></a>
<div class="order-form">
    <div class="order center">
        <h2>Заказать</h2>
        <p>Не ждите пока нас отрекламируют, как Вятский квас, и мы поднимем цены! Закажите настоящий крафтовый квас сейчас. При заказе от 10 баррелей &mdash; скидка 10%.</p>
        <?php if (isset($order_msg)): ?>
            <div class="msg-success"><?php echo $order_msg; ?></div>
        <?php endif; ?>
        <form class="feedback-form" method="post">
            <div class="feedback-form-group">
                <label for="fullname">Представьтесь:</label>
                <input type="text" name="fullname" id="fullname" placeholder="Как мы можем к вам обращаться?" required>
            </div>
            <div class="feedback-form-group">
                <label for="phone">Номер телефона:</label>
                <input type="text" name="phone" id="phone" placeholder="+7 (XXX) XXX-XX-XX" required>
                <label id="labelphone-two" for="phone-two">Доп. номер:</label>
                <input type="text" name="phone-two" id="phone-two" placeholder="+7 (XXX) XXX-XX-XX">
            </div>
            <div class="feedback-form-group">
                <label for="mail">E-mail:</label>
                <input type="email" name="mail" id="mail" placeholder="Ваша электронная почта" required>
            </div>
            <div class="feedback-form-group size">
                <label for="size">Объем заказа:</label>
                <select name="size" id="size">
                    <option value="1 баррель">1 баррель</option>
                    <option value="5 баррелей">5 баррелей</option>
                    <option value="10 баррелей">10 баррелей</option>
                    <option value="20 баррелей">20 баррелей</option>
                    <option value="50 баррелей">50 баррелей</option>
                </select>
            </div>
            <div class="feedback-form-group">
                <label class="labtext" for="message">Доп. комментарий:</label>
                <textarea rows="5" name="message" id="message" placeholder="Сообщите нам всё, что считаете нужным"></textarea>
            </div>
            <div class="checkbox-area">
                <label>
                    <input type="checkbox" name="subscription" checked>
                    <div class="check"></div>
                    <span>Я согласен получать квасную рассылку</span>
                </label>
            </div>
            <input type="submit" class="btn" name="order_submit" value="Отправить заказ">
        </form>
    </div>
</div>

<?php endif; ?>

<?php if ($page == 'login'): ?>
<div style="padding:50px 0;background:#f5f5f5;">
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
        <p style="text-align:center;margin-top:20px;">
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
    <div style="padding:50px 0;text-align:center;background:#f5f5f5;">
        <h1>⛔ Доступ запрещен</h1>
        <p>У вас нет прав доступа! <a href="?page=login">Войти</a></p>
    </div>
<?php else: ?>
    <div style="background:#f0f0f0;padding:2em 0;">
        <div class="center">
            <div class="admin-panel">
                <h1 style="text-align:center;color:#333;">⚡ Админ-панель</h1>
                <p style="text-align:center;font-size:1.2em;">Добро пожаловать, <strong><?php echo $_SESSION['user_login']; ?></strong>! 👋</p>
                <p style="text-align:center;margin-bottom:30px;">
                    <a href="?page=home" style="color:#007bff;">← На главную</a>
                </p>
                
                <div class="admin-form">
                    <h2>➕ Добавить товар</h2>
                    <?php if (isset($msg_product)): ?>
                        <div class="msg-success">✅ <?php echo $msg_product; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="text" name="name" placeholder="Название" required>
                        <input type="text" name="alcohol" placeholder="Алкоголь (например: < 1%)">
                        <input type="text" name="calories" placeholder="Ккал">
                        <input type="text" name="volume" placeholder="Объем (например: 0.5 л)">
                        <input type="text" name="price" placeholder="Цена (например: 150 Р.)" required>
                        <input type="text" name="image" placeholder="Путь к картинке (img/bottle-left.jpg)">
                        <textarea name="description" placeholder="Описание" rows="3"></textarea>
                        <button type="submit" name="add_product_btn">➕ Добавить товар</button>
                    </form>
                </div>

                <h2 style="color:#333;">📋 Все товары (<?php echo count($products); ?>)</h2>
                <?php foreach($products as $product): ?>
                    <div class="item-card">
                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                        <p><strong>Цена:</strong> <?php echo htmlspecialchars($product['price']); ?></p>
                        <p><strong>Алк.:</strong> <?php echo htmlspecialchars($product['alcohol']); ?></p>
                        <p><strong>Ккал:</strong> <?php echo htmlspecialchars($product['calories']); ?></p>
                        <p><strong>Объем:</strong> <?php echo htmlspecialchars($product['volume']); ?></p>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="">
                        <?php endif; ?>
                        <br>
                        <a href="?page=admin&delete_product=<?php echo $product['id']; ?>" 
                           class="delete-btn" onclick="return confirm('Удалить товар?')">🗑 Удалить</a>
                    </div>
                <?php endforeach; ?>
                
                <p style="text-align:center;margin-top:30px;">
                    <a href="?page=home">← На главную</a>
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php endif; ?>

<div class="page-footer">
    <div class="footer-top center">
        <img class="footer-logo" src="img/logo.png" alt="КВАСТ">
        <ul class="footer-menu">
            <li><a href="#history">История</a></li>
            <li><a href="#manufacture">Производство</a></li>
            <li><a href="#assortment">Ассортимент</a></li>
            <li><a href="#buy">Где купить</a></li>
        </ul>
    </div>
    <div class="footer-bottom center">
        <div class="footer-social">
            <a class="social-btn" href="#"><img src="img/logoinst.png" alt="Инстаграм"></a>
            <a class="social-btn" href="#"><img src="img/logoface.png" alt="Фейсбук"></a>
            <a class="social-btn" href="#"><img src="img/logovk.png" alt="Вконтакте"></a>
        </div>
    </div>
</div>

</body>
</html>
<?php mysqli_close($conn); ?>



-- ============================================================
-- БАЗА ДАННЫХ ДЛЯ САЙТА "Кваст"
-- ============================================================

-- 1. СОЗДАНИЕ БАЗЫ ДАННЫХ
CREATE DATABASE IF NOT EXISTS kvas_db;
USE kvas_db;

-- ============================================================
-- 2. УДАЛЕНИЕ СТАРЫХ ТАБЛИЦ (ЕСЛИ НУЖНО ПЕРЕСОЗДАТЬ)
-- ============================================================
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
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
-- 4. ТАБЛИЦА ТОВАРОВ (КВАС)
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    alcohol VARCHAR(50),
    calories VARCHAR(50),
    volume VARCHAR(50),
    price VARCHAR(50),
    image VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. ТАБЛИЦА ЗАКАЗОВ
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    phone2 VARCHAR(50),
    email VARCHAR(255) NOT NULL,
    volume_order VARCHAR(50),
    message TEXT,
    subscription TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. ДОБАВЛЕНИЕ АДМИНИСТРАТОРА
-- ============================================================
INSERT INTO users (login, password, role) VALUES 
('admin', 'admin', 'admin')
ON DUPLICATE KEY UPDATE login=login;

-- ============================================================
-- 7. ДОБАВЛЕНИЕ ТЕСТОВЫХ ТОВАРОВ
-- ============================================================
INSERT INTO products (name, alcohol, calories, volume, price, image, description) VALUES 
('Вахтёр', '< 1%', '35', '0.5 л', '150 Р.', 'img/bottle-left.jpg', 'Терпкий и мощный, вкус этого кваса взбодрит после тяжёлого дня и придаст сил для вечерних приключений!'),
('Романтик', '< 0.5%', '27', '0.5 л', '90 Р.', 'img/bottle-right.jpg', 'Нежный и мягкий вкус этого кваса создан для романтических вечеров и приятных встреч с друзьями.'),
('Классика', '< 1.2%', '33', '330 мл', '120 Р.', 'img/bottle-classic.jpg', 'Традиционный русский квас по классическому рецепту.'),
('Шабаш', '> 1.2%', '45', '330 мл', '260 Р.', 'img/bottle-shabash.jpg', 'Крепкий квас для настоящих ценителей.')
ON DUPLICATE KEY UPDATE name=name;

-- ============================================================
-- 8. ДОБАВЛЕНИЕ ТЕСТОВЫХ ЗАКАЗОВ
-- ============================================================
INSERT INTO orders (name, phone, phone2, email, volume_order, message, subscription) VALUES 
('Иван Петров', '+7(999)123-45-67', '+7(999)123-45-68', 'ivan@mail.ru', '10 баррелей', 'Доставка в пятницу', 1),
('Мария Смирнова', '+7(999)234-56-78', '', 'maria@mail.ru', '5 баррелей', 'Хочу попробовать все виды', 1),
('Алексей Иванов', '+7(999)345-67-89', '+7(999)345-67-80', 'alex@mail.ru', '1 баррель', 'Срочная доставка', 0);

-- ============================================================
-- 9. ПРОВЕРКА ДАННЫХ
-- ============================================================
SELECT * FROM users;
SELECT * FROM products;
SELECT * FROM orders;

-- ============================================================
-- 10. ПОКАЗАТЬ СТРУКТУРУ ТАБЛИЦ
-- ============================================================
DESCRIBE users;
DESCRIBE products;
DESCRIBE orders;

-- ============================================================
-- 11. ДОПОЛНИТЕЛЬНЫЕ ЗАПРОСЫ
-- ============================================================

-- Количество записей в таблицах
SELECT 'users' as table_name, COUNT(*) as count FROM users
UNION ALL
SELECT 'products', COUNT(*) FROM products
UNION ALL
SELECT 'orders', COUNT(*) FROM orders;

-- Все товары с сортировкой по цене
SELECT * FROM products ORDER BY CAST(price AS DECIMAL(10,2));

-- Самый дорогой квас
SELECT * FROM products ORDER BY CAST(price AS DECIMAL(10,2)) DESC LIMIT 1;

-- Самый дешевый квас
SELECT * FROM products ORDER BY CAST(price AS DECIMAL(10,2)) ASC LIMIT 1;

-- Заказы за последнюю неделю
SELECT * FROM orders WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY);

-- Заказы с подпиской на рассылку
SELECT * FROM orders WHERE subscription = 1;

-- Количество заказов по объему
SELECT volume_order, COUNT(*) as count FROM orders GROUP BY volume_order;

-- ============================================================
-- 12. ПОИСК ТОВАРОВ
-- ============================================================
-- SELECT * FROM products WHERE name LIKE '%романтик%';
-- SELECT * FROM products WHERE calories < 40;

-- ============================================================
-- 13. ОЧИСТКА ТАБЛИЦ (ЕСЛИ НУЖНО)
-- ============================================================
-- TRUNCATE TABLE products;
-- TRUNCATE TABLE orders;
-- TRUNCATE TABLE users;

-- ============================================================
-- 14. ПОЛНОЕ УДАЛЕНИЕ БАЗЫ (ЕСЛИ НУЖНО ПЕРЕСОЗДАТЬ)
-- ============================================================
-- DROP DATABASE IF EXISTS kvas_db;