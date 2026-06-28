<?php
// Подключение к БД
$host = 'localhost';
$dbname = 'exam_db';
$username = 'root';
$password = '';

// Подключаемся к БД
$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

session_start();

// Вход через таблицу users
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

// Добавление товара (только для админа)
if (isset($_POST['add_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $price = trim($_POST['price']);
    $image = trim($_POST['image']);
    
    if ($title && $desc && $price) {
        $title = mysqli_real_escape_string($conn, $title);
        $desc = mysqli_real_escape_string($conn, $desc);
        $price = mysqli_real_escape_string($conn, $price);
        $image = mysqli_real_escape_string($conn, $image);
        
        mysqli_query($conn, "INSERT INTO items (title, description, price, image) VALUES ('$title', '$desc', '$price', '$image')");
        $msg = 'Запись добавлена!';
    } else {
        $msg = 'Заполните все поля!';
    }
}

// Получаем все записи
$result = mysqli_query($conn, "SELECT * FROM items ORDER BY id DESC");
$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}

// Определяем страницу
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Браслеты с гравировкой</title>
    <link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
    <script src="js/jquery-1.11.0.min.js"></script>
    <link href="css/style.css" rel="stylesheet" type="text/css" media="all" />	
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
    <script src="js/simpleCart.min.js"> </script>
    <link href="css/memenu.css" rel="stylesheet" type="text/css" media="all" />
    <script type="text/javascript" src="js/memenu.js"></script>
    <script>$(document).ready(function(){$(".memenu").memenu();});</script>	
    <script src="js/jquery.easydropdown.js"></script>			
</head>
<body> 
<!--top-header-->
<div class="top-header">
    <div class="container">
        <div class="top-header-main">
            <div class="col-md-6 top-header-left">
                <div class="drop">
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="col-md-6 top-header-left">
                <div class="cart box_1">
                    <a href="checkout.html">
                        <div class="total">
                            <span class="simpleCart_total"></span>
                        </div>
                        <img src="images/cart-1.png" alt="" />
                    </a>
                    <p><a href="javascript:;" class="simpleCart_empty">Очистить корзину</a></p>
                    <div class="clearfix"> </div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
<!--top-header-->

<!--start-logo-->
<div class="logo">
    <a href="index.php"><h1>Браслеты с гравировкой</h1></a>
</div>
<!--start-logo-->

<!--bottom-header-->
<div class="header-bottom">
    <div class="container">
        <div class="header">
            <div class="col-md-9 header-left">
                <div class="top-nav">
                    <ul class="memenu skyblue">
                        <li class="active"><a href="index.php">Главная</a></li>
                        <li class="grid"><a href="products.html">Браслеты</a></li>
                        <li class="grid"><a href="contact.php">Индивидуальный дизайн</a></li>	
                        <li class="grid"><a href="#">Доставка</a></li>					
                        <li class="grid"><a href="#">Контакты</a></li>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                            <li class="grid"><a href="?page=admin" style="color:red;">Админ-панель</a></li>
                            <li class="grid"><a href="?logout=1" style="color:red;">Выйти (<?php echo $_SESSION['user_login']; ?>)</a></li>
                        <?php else: ?>
                            <li class="grid"><a href="?page=login">Войти</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="clearfix"> </div>
            </div>
            <div class="col-md-3 header-right"> 
                <div class="search-bar">
                    <input type="text" value="Поиск" onfocus="this.value = '';" onblur="if (this.value == '') {this.value = 'Поиск';}">
                    <input type="submit" value="">
                </div>
            </div>
            <div class="clearfix"> </div>
        </div>
    </div>
</div>
<!--bottom-header-->

<!--start-breadcrumbs-->
<div class="breadcrumbs">
    <div class="container">
        <div class="breadcrumbs-main">
            <ol class="breadcrumb">
                <li><a href="index.php">Главная</a></li>
                <?php if ($page == 'admin'): ?>
                    <li class="active">Админ-панель</li>
                <?php elseif ($page == 'login'): ?>
                    <li class="active">Вход</li>
                <?php else: ?>
                    <li class="active">Товары</li>
                <?php endif; ?>
            </ol>
        </div>
    </div>
</div>
<!--end-breadcrumbs-->

<!--start-content-->
<div class="ckeckout">
    <div class="container">
        <?php if ($page == 'home'): ?>
            <div class="ckeck-top heading">
                <h2>НАШИ БРАСЛЕТЫ</h2>
            </div>
            <div class="ckeckout-top">
                <div class="cart-items">
                    <h3>Все товары (<?php echo count($items); ?>)</h3>
                    
                    <?php if (empty($items)): ?>
                        <p style="padding:20px;">Нет товаров</p>
                    <?php else: ?>
                        <?php 
                        $counter = 0;
                        foreach($items as $item): 
                            $counter++;
                            $close_class = ($counter == 1) ? 'close1' : (($counter == 2) ? 'close2' : 'close3');
                        ?>
                            <ul class="cart-header<?php echo ($counter > 3) ? '' : $counter; ?>">
                                <?php if ($counter <= 3): ?>
                                    <div class="<?php echo $close_class; ?>"> </div>
                                <?php endif; ?>
                                <li class="ring-in">
                                    <a href="single.html">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" class="img-responsive2" alt="">
                                        <?php else: ?>
                                            <img src="images/c-<?php echo $counter; ?>.jpg" class="img-responsive2" alt="">
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li>
                                    <span class="name"><?php echo htmlspecialchars($item['title']); ?></span>
                                </li>
                                <li>
                                    <span class="cost">₽ <?php echo htmlspecialchars($item['price']); ?></span>
                                </li>
                                <li>
                                    <span>Бесплатно</span>
                                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                                </li>
                                <div class="clearfix"> </div>
                            </ul>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($page == 'login'): ?>
            <div class="ckeck-top heading">
                <h2>АВТОРИЗАЦИЯ</h2>
            </div>
            <div class="ckeckout-top">
                <div class="cart-items">
                    <h3>Вход в админ-панель</h3>
                    <?php if (isset($error)) echo "<p style='color:red;padding:10px;'>$error</p>"; ?>
                    <form method="POST" style="padding:20px;">
                        <p><input type="text" name="login" placeholder="Логин" required style="width:300px;padding:8px;margin:10px 0;"></p>
                        <p><input type="password" name="password" placeholder="Пароль" required style="width:300px;padding:8px;margin:10px 0;"></p>
                        <p><button type="submit" name="login_btn" style="padding:8px 30px;background:#000;color:#fff;border:none;">Войти</button></p>
                    </form>
                    <p style="padding:0 20px 20px;"><strong>Логин: admin, Пароль: admin</strong></p>
                    <p style="padding:0 20px 20px;"><a href="?page=home">На главную</a></p>
                </div>
            </div>

        <?php elseif ($page == 'admin'): ?>
            <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin'): ?>
                <div class="ckeck-top heading">
                    <h2>ДОСТУП ЗАПРЕЩЕН</h2>
                </div>
                <div class="ckeckout-top">
                    <div class="cart-items">
                        <p style="padding:20px;">У вас нет прав доступа! <a href="?page=login">Войти</a></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="ckeck-top heading">
                    <h2>АДМИН-ПАНЕЛЬ</h2>
                </div>
                <div class="ckeckout-top">
                    <div class="cart-items">
                        <p style="padding:10px;">Добро пожаловать, <strong><?php echo $_SESSION['user_login']; ?></strong>!</p>
                        
                        <h3>Добавить товар</h3>
                        <?php if (isset($msg)) echo "<p style='color:green;padding:10px;'>$msg</p>"; ?>
                        <form method="POST" style="padding:20px;">
                            <p><input type="text" name="title" placeholder="Название" required style="width:300px;padding:8px;margin:10px 0;"></p>
                            <p><textarea name="description" placeholder="Описание" required style="width:300px;padding:8px;margin:10px 0;height:100px;"></textarea></p>
                            <p><input type="text" name="price" placeholder="Цена (например: 850.00)" required style="width:300px;padding:8px;margin:10px 0;"></p>
                            <p><input type="text" name="image" placeholder="Ссылка на картинку (необязательно)" style="width:300px;padding:8px;margin:10px 0;"></p>
                            <p><button type="submit" name="add_btn" style="padding:8px 30px;background:#000;color:#fff;border:none;">Добавить</button></p>
                        </form>
                        <hr>
                        <h3>Все товары (<?php echo count($items); ?>)</h3>
                        <?php if (empty($items)): ?>
                            <p style="padding:20px;">Нет товаров</p>
                        <?php else: ?>
                            <?php 
                            $counter = 0;
                            foreach($items as $item): 
                                $counter++;
                            ?>
                                <ul class="cart-header">
                                    <li class="ring-in">
                                        <a href="single.html">
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($item['image']); ?>" class="img-responsive2" alt="" style="width:100px;">
                                            <?php else: ?>
                                                <img src="images/c-<?php echo ($counter % 3) + 1; ?>.jpg" class="img-responsive2" alt="" style="width:100px;">
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <span class="name"><?php echo htmlspecialchars($item['title']); ?></span>
                                    </li>
                                    <li>
                                        <span class="cost">₽ <?php echo htmlspecialchars($item['price']); ?></span>
                                    </li>
                                    <li>
                                        <span>Бесплатно</span>
                                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                                        <small style="color:#999;">Добавлено: <?php echo $item['created_at']; ?></small>
                                    </li>
                                    <div class="clearfix"> </div>
                                </ul>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<!--end-content-->

<!--information-starts-->
<div class="information">
    <div class="container">
        <div class="infor-top">
            <div class="col-md-3 infor-left">
                <h3>Подписаться</h3>
                <ul>
                    <li><a href="https://vk.com/ypypypyppy"><span class="fb"></span><h6>VK</h6></a></li>
                    <li><a href="https://instagram.com/r.lis_26?igshid=1klvkpmrb9xi9"><span class="twit"></span><h6>Instagram</h6></a></li>
                </ul>
            </div>
            <div class="col-md-3 infor-left">
                <h3>Информация</h3>
                <ul>
                    <li><a href="products.html"><p>Браслеты</p></a></li>
                    <li><a href="contact.php"><p>Индивидуальный дизайн</p></a></li>
                    <li><a href="#"><p>Доставка</p></a></li>
                    <li><a href="#"><p>Контакты</p></a></li>
                </ul>
            </div>
            <div class="col-md-3 infor-left">
            </div>
            <div class="col-md-3 infor-left">
                <h3>О нас</h3>
                <h4>ИП,
                    <span>Его пока нет</span>
                    Ярославль</h4>
                <h5>8 800 555-35-35</h5>	
                <p><a href="mailto:rus_anis2000@mail.ru">rus_anis2000@mail.ru</a></p>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
<!--information-end-->

<?php
// Закрываем подключение
mysqli_close($conn);
?>

</body>
</html>





-- var2.php - Браслеты с гравировкой
CREATE DATABASE IF NOT EXISTS exam_db;
USE exam_db;

-- Таблица пользователей
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(50) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица товаров (браслеты)
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price VARCHAR(50) NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Добавляем админа
INSERT INTO users (login, password, role) VALUES ('admin', 'admin', 'admin');

-- Тестовые товары
INSERT INTO items (title, description, price, image) VALUES 
('Браслет "Мышонок"', 'Доставка в течении 5-12 дней.', '850.00', 'images/c-1.jpg'),
('Браслет "Кошка"', 'Доставка в течении 5-12 дней.', '850.00', 'images/c-2.jpg'),
('Браслет "Сова"', 'Доставка в течении 5-12 дней.', '850.00', 'images/c-3.jpg');