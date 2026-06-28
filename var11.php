<?php
session_start();

// Подключение к БД
$host = 'localhost';
$dbname = 'exam_db';
$username = 'root';
$password = '';

$conn = mysqli_connect($host, $username, $password, $dbname);
if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

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

// Добавление категории (праздника)
if (isset($_POST['add_holiday_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $name = trim($_POST['name']);
    if ($name) {
        $name = mysqli_real_escape_string($conn, $name);
        mysqli_query($conn, "INSERT INTO holidays (name) VALUES ('$name')");
        $msg_holiday = 'Праздник добавлен!';
    } else {
        $msg_holiday = 'Заполните название!';
    }
}

// Добавление отзыва
if (isset($_POST['add_review_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $name = trim($_POST['review_name']);
    $text = trim($_POST['review_text']);
    $author = trim($_POST['review_author']);
    if ($name && $text) {
        $name = mysqli_real_escape_string($conn, $name);
        $text = mysqli_real_escape_string($conn, $text);
        $author = mysqli_real_escape_string($conn, $author);
        mysqli_query($conn, "INSERT INTO reviews (name, text, author) VALUES ('$name', '$text', '$author')");
        $msg_review = 'Отзыв добавлен!';
    } else {
        $msg_review = 'Заполните все поля!';
    }
}

// Добавление блога
if (isset($_POST['add_blog_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $title = trim($_POST['blog_title']);
    $description = trim($_POST['blog_description']);
    if ($title && $description) {
        $title = mysqli_real_escape_string($conn, $title);
        $description = mysqli_real_escape_string($conn, $description);
        mysqli_query($conn, "INSERT INTO blogs (title, description) VALUES ('$title', '$description')");
        $msg_blog = 'Статья добавлена!';
    } else {
        $msg_blog = 'Заполните все поля!';
    }
}

// Удаление
if (isset($_GET['delete_holiday']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $id = intval($_GET['delete_holiday']);
    mysqli_query($conn, "DELETE FROM holidays WHERE id=$id");
    header('Location: ?page=admin');
    exit;
}
if (isset($_GET['delete_review']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $id = intval($_GET['delete_review']);
    mysqli_query($conn, "DELETE FROM reviews WHERE id=$id");
    header('Location: ?page=admin');
    exit;
}
if (isset($_GET['delete_blog']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $id = intval($_GET['delete_blog']);
    mysqli_query($conn, "DELETE FROM blogs WHERE id=$id");
    header('Location: ?page=admin');
    exit;
}

// Получаем данные
$holidays = [];
$result = mysqli_query($conn, "SELECT * FROM holidays ORDER BY id");
while ($row = mysqli_fetch_assoc($result)) {
    $holidays[] = $row;
}

$reviews = [];
$result = mysqli_query($conn, "SELECT * FROM reviews ORDER BY id");
while ($row = mysqli_fetch_assoc($result)) {
    $reviews[] = $row;
}

$blogs = [];
$result = mysqli_query($conn, "SELECT * FROM blogs ORDER BY id");
while ($row = mysqli_fetch_assoc($result)) {
    $blogs[] = $row;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Маффин</title>
    <style>
        /* ====== ВСЕ СТИЛИ ====== */
        * { margin: 0; padding: 0; color: #2F344D; }
        @font-face { font-family: "bolt"; src: url(fonts/Montserrat-Bold.ttf); }
        @font-face { font-family: "ExtraBold"; src: url(fonts/Montserrat-ExtraBold.ttf); }
        @font-face { font-family: "Medium"; src: url(fonts/Montserrat-Medium.ttf); }
        @font-face { font-family: "light"; src: url(fonts/Montserrat-Light.ttf); }

        header { display: grid; grid-template-columns: 1fr 1fr; }
        .logo { font-family: "bolt"; font-size: 20px; text-decoration: none; }
        .logo_F { color: #d12b10; }
        .header_1_2 { padding-top: 0.2%; padding-left: 22.2%; font-family: "bolt"; font-size: 14px; display: flex; gap: 5.8%; align-items: center; flex-wrap: wrap; }
        .header_1_2 a { text-decoration: none; color: #2F344D; font-family: "bolt"; font-size: 14px; transition: 0.3s; }
        .header_1_2 a:hover { color: #d12b10; }
        .probel { color: white; }
        .img_head { position: absolute; right: 0; z-index: -1; height: 78%; }
        .button_header_1 { padding-left: 59.8%; padding-top: 2%; }
        .button_header { color: white; font-family: "bolt"; font-size: 14px; background-color: #d12b10; border: 0; border-radius: 8px; height: 44px; width: 130px; cursor: pointer; }
        
        /* Стили для кнопок админки */
        .admin-btn { 
            text-decoration: none; 
            color: #d12b10 !important; 
            font-weight: bold; 
            font-family: "bolt"; 
            font-size: 14px; 
            padding: 5px 10px;
            border-radius: 5px;
            transition: 0.3s;
        }
        .admin-btn:hover { 
            background: #d12b10; 
            color: #fff !important; 
        }
        .login-btn {
            text-decoration: none; 
            color: #007bff !important; 
            font-weight: bold; 
            font-family: "bolt"; 
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 5px;
            transition: 0.3s;
        }
        .login-btn:hover { 
            background: #007bff; 
            color: #fff !important; 
        }
        
        /* Остальные стили из вашего CSS */
        .ci_1z { padding-top: 36.5%; padding-left: 22.2%; }
        .ci_1 { display: grid; grid-template-columns: 1fr 1fr; }
        .ci_1_1 { font-family: "ExtraBold"; font-size: 48px; }
        .ci_1_2 { font-family: "ExtraBold"; font-size: 48px; padding-top: 1%; }
        .ci_1_3 { font-family: "Medium"; font-size: 18px; padding-top: 6.5%; }
        .ci_1_4 { font-family: "Medium"; font-size: 18px; padding-top: 1%; }
        .ci_1_5 { padding-top: 6.5%; }
        .button_ci_1 { color: white; font-family: "bolt"; font-size: 14px; background-color: #d12b10; border: 0; border-radius: 8px; height: 44px; width: 135px; cursor: pointer; }
        .ci_1_0z { padding-left: 2.3%; padding-top: 8.1%; }
        .ig { width: 77%; object-fit: cover; }
        .ci_2_name { font-family: "ExtraBold"; font-size: 48px; padding-left: 30.3%; padding-top: 6.9%; }
        .grid { display: grid; grid-template-columns: 27.5% 27.5% 27.5%; grid-template-rows: 77.4% 77.4%; grid-gap: 5.5% 2.5%; padding-top: 2.4%; padding-left: 11.2%; }
        .card { display: grid; }
        .card img { width: 91%; }
        .card_1 { height: 27%; width: 22.3%; border-radius: 32px; align-content: center; place-self: end; box-shadow: 1px 1px 5px rgba(0,0,0,0.5); font-family: "Medium"; font-size: 18px; position: absolute; z-index: -1; }
        .button_card { color: white; font-family: "bolt"; font-size: 14px; background-color: #d12b10; border: 0; border-radius: 8px; height: 44px; width: 130px; cursor: pointer; }
        .pas { display: flex; justify-content: center; }
        .text { text-align: center; }
        .ci_2_0 { padding-top: 6.4%; display: grid; row-gap: 55%; }
        .ci_3 { padding-top: 31%; }
        .grid_2 { display: grid; grid-template-columns: 23.7% 23.7% 23.7%; gap: 8.2%; padding-left: 11.1%; padding-top: 2%; }
        .ci_3_zag { font-family: "ExtraBold"; font-size: 40px; text-align: center; }
        .ci_3_card { box-shadow: 1px 1px 5px rgba(0,0,0,0.5); border-radius: 128px 32px 32px 32px; }
        .ci_3_img { width: 100%; display: flex; justify-content: center; padding-top: 11.7%; }
        .Text { text-align: center; font-family: "Medium"; font-size: 18px; padding-top: 11.6%; }
        .text_1 { width: 100%; display: flex; justify-content: center; text-align: center; line-height: 26px; font-family: "Light"; font-size: 16px; padding-top: 11.9%; padding-bottom: 17%; }
        .qw_ot { padding-bottom: 8.3%; }
        .ci_3_ot { padding-top: 7.3%; }
        .ci_3_t { font-family: "ExtraBold"; font-size: 28px; }
        .ci_3_t1 { font-family: "light"; font-size: 16px; }
        .ci_3_2 { display: grid; grid-template-columns: 1fr 1fr 1fr; border-radius: 32px 128px 32px 32px; background: linear-gradient(to left, rgba(59,52,44,0.2), rgba(209,43,16,0.2)); }
        .ci_3_2_ot { padding-top: 5%; padding-left: 11.1%; padding-right: 11.1%; }
        .card_3_0 { padding-top: 9.5%; padding-bottom: 15.5%; }
        .ci_3_paz { text-align: center; display: grid; gap: 42.5%; }
        .z0 { padding-right: 6%; }
        .z1 { padding-left: 6.5%; }
        .ci_4 { padding-top: 9%; }
        .ci_4_1 { background-color: #2f344d40; display: flex; padding-bottom: 4.3%; }
        .ci_4_img { margin-top: -11.8%; padding-left: 29.5%; height: 109.5%; }
        .ci_4_2 { padding-top: 6.2%; font-family: "ExtraBold"; font-size: 28px; }
        .lit { font-family: "light"; font-size: 14px; color: #969696; padding-top: 3.9%; padding-bottom: 3.9%; padding-left: 8%; }
        .lit1 { background-color: white; width: 110.6%; border: 1px solid black; display: flex; }
        .ci_4_3 { padding-left: 20%; padding-top: 1.4%; }
        .grid_4 { padding-top: 3.8%; display: grid; gap: 16.9%; }
        .button_cta { color: white; font-family: "bolt"; font-size: 14px; background-color: #d12b10; border: 0; border-radius: 8px; height: 44px; width: 130px; cursor: pointer; }
        .lit1 img { height: 44%; padding-top: 2.9%; }
        .z1 { font-family: "light"; font-size: 14px; color: #969696; padding-left: 5%; }
        .ci_5_1 { font-family: "ExtraBold"; font-size: 40px; text-align: center; padding-top: 2.6%; }
        .grid_5 { display: grid; grid-template-columns: 20% 20% 20% 20%; padding-left: 11.1%; gap: 2.5%; padding-top: 0.9%; }
        .crug { background-color: white; font-family: "ExtraBold"; font-size: 40px; height: 100px; width: 100px; border-radius: 50%; display: flex; justify-content: center; align-items: center; z-index: 1; position: absolute; }
        .text_card_1 { font-family: "Medium"; font-size: 18px; text-align: center; }
        .text_card_2 { font-size: 16px; font-family: "light"; text-align: center; line-height: 151%; }
        .card_12 { display: grid; justify-items: center; }
        .we_2 { display: grid; justify-content: center; }
        .color_text { color: #d12b10; }
        .color_f { background: linear-gradient(to left, rgba(59,52,44,0.2), rgba(209,43,16,0.2)); border-radius: 32px; padding-top: 7%; display: grid; gap: 24%; padding-bottom: 46%; z-index: -1; margin-top: 19.5%; padding-top: 26.5%; }
        .q1 { padding-bottom: 55%; }
        .ci_6_1 { font-family: "ExtraBold"; font-size: 40px; text-align: center; padding-top: 7.15%; }
        .grid_6 { display: grid; grid-template-columns: 27.5% 27.5% 27.5%; grid-template-rows: 105%; padding-left: 11.1%; padding-top: 5%; gap: 2.5%; }
        .text_grid_1 { font-family: "Light"; font-size: 16px; text-align: center; margin-left: 10%; margin-right: 10%; margin-top: 8.9%; line-height: 160%; }
        .text_grid_2 { font-family: "Medium"; font-size: 18px; text-align: center; padding-top: 9.7%; }
        .card_6 { border-radius: 128px 32px 32px 32px; box-shadow: 1px 1px 5px rgba(0,0,0,0.5); }
        .ci_6_img { display: flex; justify-content: center; padding-top: 1%; }
        .qwe { padding-top: 17%; }
        .flex_7 { display: flex; }
        .ci_7_1 { font-family: "ExtraBold"; font-size: 28px; display: grid; gap: 10%; }
        .ci_7_2 { font-family: "Medium"; font-size: 18px; margin-top: 9.5%; }
        .ci_7_3 { font-family: "light"; font-size: 14px; display: grid; gap: 17.3%; margin-top: 7.7%; margin-left: 0.7%; }
        .ci_7_button { color: white; font-family: "bolt"; font-size: 14px; background-color: #d12b10; border: 0; border-radius: 8px; height: 44px; width: 130px; cursor: pointer; }
        .ci_7_0 { margin-top: 8%; margin-left: 10.9%; }
        .ci_7_4 { border: 1px solid black; padding-left: 7.5%; padding-top: 3.1%; padding-bottom: 3.1%; width: 89.5%; display: flex; }
        .ci_7_5 { margin-top: 7.9%; margin-left: 8%; }
        .ci_8_1 { font-family: "ExtraBold"; font-size: 40px; text-align: center; margin-top: 6.1%; }
        .grid_8 { display: grid; grid-template-columns: 352px 352px 352px; grid-template-rows: 472px; grid-gap: 32px 33px; justify-content: center; }
        .card_8 { height: 472px; width: 352px; padding-left: 160px; padding-top: 36px; display: grid; }
        .card_18 { width: 320px; height: 220px; border-radius: 32px; display: grid; align-content: center; place-self: end; row-gap: 32px; box-shadow: 1px 1px 5px rgba(0,0,0,0.5); font-family: "Medium"; position: absolute; z-index: -1; }
        .text8 { width: 320px; display: flex; justify-content: center; padding-top: 5.9%; }
        .ci_2_0 { display: grid; padding-top: 50px; row-gap: 5px; font-size: 18px; line-height: 28px; }
        .ci_8_img { height: 30px; }
        .z12 { font-family: "ExtraBold"; font-size: 28px; }
        .z2 { font-family: "light"; font-size: 16px; }
        .w1 { margin-top: 4%; margin-left: 28.2%; }
        .w2 { margin-left: 28.1%; margin-top: 6.2%; line-height: 165%; }
        .flex_9 { display: flex; background-color: #96969685; margin-top: 12.2%; }
        .ci_9_img { margin-top: -3.7%; margin-left: 12%; padding-bottom: 6%; }
        .w11 { margin-top: 6%; margin-left: 28.2%; }
        .w22 { margin-left: 28.3%; margin-top: 6.2%; line-height: 165%; }
        .w21 { margin-left: 28.3%; margin-top: 6.2%; line-height: 165%; }
        .ci_9_1 { margin-top: 3%; margin-left: 28.2%; display: flex; gap: 4.5%; }
        .ci_10_1 { background-color: #d7280e99; border-radius: 32px 128px 32px 32px; margin-top: 6.9%; margin-left: 11.1%; margin-right: 11.1%; display: grid; padding-left: 6.7%; padding-top: 4.7%; gap: 35%; padding-bottom: 10%; }
        .ci_10_t { font-size: 28px; font-family: "ExtraBold"; }
        .q { color: white; }
        .q4 { color: white; }
        .ci_10_t1 { font-size: 14px; font-family: "Light"; }
        .ci_10_4 { padding-top: 1.6%; }
        .e_1 { font-family: "bolt"; font-size: 20px; }
        .e_2 { font-family: "light"; font-size: 14px; }
        .grid_11 { display: grid; grid-template-columns: 19% 19% 19% 19%; gap: 12px; background-color: #96969680; margin-top: 6.9%; padding-left: 11.1%; padding-top: 2.4%; }
        .ci_10_img { height: 30px; }
        .zo { display: grid; gap: 35.4%; padding-bottom: 50%; }

        /* ====== АДМИН-СТИЛИ ====== */
        .admin-panel { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .admin-form { background: #f9f9f9; padding: 20px; border-radius: 10px; margin-bottom: 30px; border: 1px solid #ddd; }
        .admin-form input, .admin-form textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .admin-form button { padding: 12px 30px; background: #28a745; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .admin-form button:hover { background: #218838; }
        .item-card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; background: #fff; }
        .delete-btn { padding: 5px 15px; background: #dc3545; color: #fff; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin-top: 10px; }
        .delete-btn:hover { background: #c82333; }
        .msg-success { color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
        .msg-error { color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
        .login-form { max-width: 400px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 10px; box-shadow: 0 5px 30px rgba(0,0,0,0.1); }
        .login-form h1 { text-align: center; margin-bottom: 30px; }
        .login-form input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .login-form button { width: 100%; padding: 12px; background: #007bff; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .login-form button:hover { background: #0069d9; }
    </style>
</head>
<body>

<header>
    <div class="header_1_2">
        <a href="#" class="logo">мафф<span class="logo_F">ф</span>ин</a>
        <a href="#about">О нас</a>
        <a href="#prices">Цены</a>
        <a href="#delivery">Доставка</a>
        <a href="#blog">Блог</a>
        <a href="#contacts">Контакты</a>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
            <a href="?page=admin" class="admin-btn">Админ</a>
            <a href="?logout=1" class="admin-btn">Выйти</a>
        <?php else: ?>
            <a href="?page=login" class="login-btn">Войти</a>
        <?php endif; ?>
    </div>
    <div class="button_header_1">
        <button class="button_header">Позвонить</button>
    </div>
    <img class="img_head" src="img/Ellipse 29.png">
</header>

<!-- ОСТАЛЬНОЙ КОД САЙТА (секции home, login, admin) -->
<!-- ... -->

<?php if ($page == 'home'): ?>

<!-- ====== ВСЕ СЕКЦИИ КАК РАНЬШЕ ====== -->
<div class="ci_1">
    <div class="ci_1z">
        <p class="ci_1_1">Свежие маффины</p>
        <p class="ci_1_2">на любой вкус</p>
        <p class="ci_1_3">Мы готовим маленькие сладости, которые приносят</p>
        <p class="ci_1_4">большие улыбки в любой день и без повода</p>
        <div class="ci_1_5">
            <button class="button_ci_1">Подробнее</button>
        </div>
    </div>
    <div class="ci_1_0">
        <div class="ci_1_0z">
            <img class="ig" src="img/Group 21.png">
        </div>
    </div>
</div>

<!-- СЕКЦИЯ 2 - СЛАДОСТИ НА ПРАЗДНИК -->
<div class="ci_2">
    <p class="ci_2_name">Сладости на празник</p>
    <div class="grid">
        <?php if (empty($holidays)): ?>
            <div class="card"><img src="img/Group 1.png"><div class="card_1"><div class="ci_2_0"><div class="text"><p>День рождение</p></div><div class="pas"><button class="button_card">Заказать</button></div></div></div></div>
            <div class="card"><img src="img/Group 1.png"><div class="card_1"><div class="ci_2_0"><div class="text"><p>Свадьба</p></div><div class="pas"><button class="button_card">Заказать</button></div></div></div></div>
            <div class="card"><img src="img/Group 1.png"><div class="card_1"><div class="ci_2_0"><div class="text"><p>8 марта</p></div><div class="pas"><button class="button_card">Заказать</button></div></div></div></div>
            <div class="card"><img src="img/Group 1.png"><div class="card_1"><div class="ci_2_0"><div class="text"><p>День всех влюблённых</p></div><div class="pas"><button class="button_card">Заказать</button></div></div></div></div>
            <div class="card"><img src="img/Group 1.png"><div class="card_1"><div class="ci_2_0"><div class="text"><p>Новый год</p></div><div class="pas"><button class="button_card">Заказать</button></div></div></div></div>
            <div class="card"><img src="img/Group 1.png"><div class="card_1"><div class="ci_2_0"><div class="text"><p>Корпоратив</p></div><div class="pas"><button class="button_card">Заказать</button></div></div></div></div>
        <?php else: ?>
            <?php foreach($holidays as $holiday): ?>
                <div class="card"><img src="img/Group 1.png"><div class="card_1"><div class="ci_2_0"><div class="text"><p><?php echo htmlspecialchars($holiday['name']); ?></p></div><div class="pas"><button class="button_card">Заказать</button></div></div></div></div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- СЕКЦИЯ 3 - ПОЧЕМУ МЫ -->
<div class="ci_3">
    <div class="ci_3_zag"><p>Почему мы?</p></div>
    <div class="grid_2">
        <div><div class="ci_3_card"><div class="ci_3_img"><img src="img/Group 22.png"></div><div class="Text"><p>Мы работаем с любовью!</p></div><div class="text_1"><p>Большой опыт и квалификация специалистов гарантирует безупречное качество заказов</p></div></div></div>
        <div class="ci_3_ot"><div class="ci_3_card"><div class="ci_3_img"><img src="img/Group 25.png"></div><div class="Text"><p>Бережная доставка заказа</p></div><div class="text_1"><p>Мы упакуем все ваши маффины в коробки и доставим их целыми и невредимыми</p></div></div></div>
        <div><div class="ci_3_card"><div class="ci_3_img"><img src="img/Group 26.png"></div><div class="Text"><p>Натуральные продукты</p></div><div class="text_1 qw_ot"><p>Отсутствие в маффинах консервантов и красителей гарантирует безопасность для здоровья</p></div></div></div>
    </div>
    <div class="ci_3_2_ot">
        <div class="ci_3_2">
            <div class="card_3_0"><div class="ci_3_paz z0"><p class="ci_3_t">3</p><p class="ci_3_t1">года опыта</p></div></div>
            <div class="card_3_0"><div class="ci_3_paz"><p class="ci_3_t">8000</p><p class="ci_3_t1">проданых маффинов</p></div></div>
            <div class="card_3_0"><div class="ci_3_paz z1"><p class="ci_3_t">1000</p><p class="ci_3_t1">довольных клиентов</p></div></div>
        </div>
    </div>
</div>

<!-- СЕКЦИЯ 4 - ЗАКАЖИ ПО ФОТО -->
<div class="ci_4">
    <div class="ci_4_1">
        <div><img class="ci_4_img" src="img/Mask group3.png"></div>
        <div class="ci_4_3">
            <div class="ci_4_2"><p>Закажи мафин по фото</p></div>
            <div class="grid_4">
                <div class="lit1"><p class="lit">Имя</p></div>
                <div class="lit1"><p class="lit">Номер</p></div>
                <div class="lit1"><img class="lit" src="img/fi-rs-upload.png"><p class="lit z1">Загрузить фотографию</p></div>
                <div class="button_cta"><button class="button_header">Позвонить</button></div>
            </div>
        </div>
    </div>
</div>

<!-- СЕКЦИЯ 5 - ПРОЦЕСС РЕАЛИЗАЦИИ -->
<div class="ci_5">
    <div class="ci_5_1"><p>Процесс реализации заказа</p></div>
    <div class="grid_5">
        <div><div class="card_12"><div class="crug"><p class="color_text">1</p></div><div class="color_f"><p class="text_card_1">Утверждаем заказ</p><p class="text_card_2">Получаем заказ и перезваниваем вам для уточнения деталей</p></div></div></div>
        <div><div class="card_12"><div class="crug"><p class="color_text">2</p></div><div class="color_f q1"><p class="text_card_1">Готовим маффины</p><p class="text_card_2">Готовим вкусные маффины по выбранному вами рецепту</p></div></div></div>
        <div><div class="card_12"><div class="crug"><p class="color_text">3</p></div><div class="color_f q1"><p class="text_card_1">Отправляем</p><p class="text_card_2">Отправляем ваш заказ курьером</p></div></div></div>
        <div><div class="card_12"><div class="crug"><p class="color_text">4</p></div><div class="color_f q1"><p class="text_card_1">Получение заказа</p><p class="text_card_2">После звонка курьера вы получаете свой заказ</p></div></div></div>
    </div>
</div>

<!-- СЕКЦИЯ 6 - ОТЗЫВЫ -->
<div class="ci_6">
    <div class="ci_6_1"><p>Что говорят о нас наши клиенты</p></div>
    <div class="grid_6">
        <?php if (empty($reviews)): ?>
            <div class="card_6"><div class="ci_6_img"><img src="img/pngwing.com 1.png"></div><div class="text_grid_1"><p>Мягкие, немного влажноватые, как будто с пропиткой. Дети на завтрак смолотили в секунду. Очень вкусно! Буду покупать еще</p></div><div class="text_grid_2"><p>Ольга</p></div></div>
            <div class="card_6"><div class="ci_6_img"><img src="img/pngwing.com 1.png"></div><div class="text_grid_1"><p>Приятные на вкус, покупали на день рождение дочери. Гости были в восторге.</p></div><div class="text_grid_2 qwe"><p>Ирина</p></div></div>
            <div class="card_6"><div class="ci_6_img"><img src="img/pngwing.com 1.png"></div><div class="text_grid_1"><p>Спасибо все идеально. Отлично упаковано, доставка вовремя, блюдо очень красиво оформлено и все было невероятно вкусно.</p></div><div class="text_grid_2"><p>София</p></div></div>
        <?php else: ?>
            <?php foreach($reviews as $review): ?>
                <div class="card_6">
                    <div class="ci_6_img"><img src="img/pngwing.com 1.png"></div>
                    <div class="text_grid_1"><p><?php echo htmlspecialchars($review['text']); ?></p></div>
                    <div class="text_grid_2"><p><?php echo htmlspecialchars($review['author']); ?></p></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- СЕКЦИЯ 7 - ЗАЯВКА -->
<div class="ci_7">
    <div class="flex_7">
        <div class="ci_7_0">
            <div class="ci_7_1"><p>Оставьте заявку!</p><p>Перезвони в течении 5 минут</p></div>
            <div class="ci_7_2"><p>Для заказа заполните форму</p></div>
            <div class="ci_7_3">
                <div class="ci_7_4"><p>Имя</p></div>
                <div class="ci_7_4"><p>Номер</p></div>
                <div class="ci_7_4"><p>Выбрать вкус маффина</p></div>
                <div><button class="ci_7_button">Позвонить</button></div>
            </div>
        </div>
        <div class="ci_7_5"><img src="img/Group 21.png"></div>
    </div>
</div>

<!-- СЕКЦИЯ 8 - БЛОГ -->
<div class="ci_8">
    <div class="ci_8_1"><p>Наш блог</p></div>
    <div class="grid_8">
        <?php if (empty($blogs)): ?>
            <div class="card_8"><img src="img/Group 1.png"><div class="card_18"><div class="ci_2_0"><div class="text"><p>7 рецептов самых вкусных шоколадных маффинов</p></div><div class="pas"><button class="button_card">Заказать</button></div></div></div></div>
            <div class="card_8"><img src="img/Group 1.png"><div class="card_18"><div class="ci_2_0"><div class="text"><p>Лучшие начинки для маффинов: как приготовить?</p></div><div class="pas"><button class="button_card">Заказать</button></div></div></div></div>
            <div class="card_8"><img src="img/Group 1.png"><div class="card_18"><div class="ci_2_0"><div class="text"><p>Как сделать воздушный крем для маффинов?</p></div><div class="pas"><button class="button_card">Заказать</button></div></div></div></div>
        <?php else: ?>
            <?php foreach($blogs as $blog): ?>
                <div class="card_8"><img src="img/Group 1.png"><div class="card_18"><div class="ci_2_0"><div class="text"><p><?php echo htmlspecialchars($blog['title']); ?></p></div><div class="pas"><button class="button_card">Заказать</button></div></div></div></div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- СЕКЦИЯ 9 - КОНТАКТЫ -->
<div class="ci_9">
    <div class="flex_9">
        <div>
            <div><div class="z12 w1"><p>Где мы находимся?</p></div><div class="z2 w2"><p>Кондитерская "Маффин" находится по адресу: г.Вкусно, ул. Сладкая,9</p></div></div>
            <div><div class="z12 w11"><p>Время работы:</p></div><div class="z2 w22"><p>Пн - Пт: 08:00 - 18:00<br>сб:08:00 - 15:00<br>Вс: выходной</p></div></div>
            <div><div class="z12 w11"><p>Наши контакты:</p></div><div class="z2 w21"><p>+123456789: +134567890</p></div></div>
            <div class="ci_9_1">
                <img class="ci_8_img" src="img/free-icon-vk-5968835.png">
                <img class="ci_8_img" src="img/free-icon-telegram-2111646.png">
                <img class="ci_8_img" src="img/free-icon-facebook-733547.png">
            </div>
        </div>
        <div class="ci_9_img"><img src="img/image.png"></div>
    </div>
</div>

<!-- СЕКЦИЯ 10 -->
<div class="ci_10">
    <div class="ci_10_1">
        <div class="ci_10_t"><p class="q">Сделай Заказ!</p></div>
        <div class="ci_10_t1"><p class="q4">Порадуй себя чем-то вкусненьким. Позвони и закажи маффин прямо сейчас!</p></div>
        <div class="ci_10_4"><button class="button_ci_1">Позвонить</button></div>
    </div>
</div>

<?php endif; ?>

<!-- ====== СТРАНИЦА ВХОДА ====== -->
<?php if ($page == 'login'): ?>
<div style="padding:50px 0;">
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
        <p style="text-align:center;margin-top:20px;"><strong>Логин: admin, Пароль: admin</strong></p>
        <p style="text-align:center;"><a href="?page=home">На главную</a></p>
    </div>
</div>
<?php endif; ?>

<!-- ====== АДМИН-ПАНЕЛЬ ====== -->
<?php if ($page == 'admin'): ?>
<?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin'): ?>
    <div style="padding:50px 0;text-align:center;">
        <h1>Доступ запрещен</h1>
        <p>У вас нет прав доступа! <a href="?page=login">Войти</a></p>
    </div>
<?php else: ?>
    <div style="padding:30px 0;">
        <div class="admin-panel">
            <h1 style="text-align:center;">Админ-панель</h1>
            <p style="text-align:center;">Добро пожаловать, <strong><?php echo $_SESSION['user_login']; ?></strong>!</p>

            <div class="admin-form">
                <h2>Добавить праздник</h2>
                <?php if (isset($msg_holiday)): ?>
                    <div class="msg-success"><?php echo $msg_holiday; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="text" name="name" placeholder="Название праздника" required>
                    <button type="submit" name="add_holiday_btn">Добавить</button>
                </form>
            </div>

            <div class="admin-form">
                <h2>Добавить отзыв</h2>
                <?php if (isset($msg_review)): ?>
                    <div class="msg-success"><?php echo $msg_review; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="text" name="review_name" placeholder="Название" required>
                    <textarea name="review_text" placeholder="Текст отзыва" rows="3" required></textarea>
                    <input type="text" name="review_author" placeholder="Автор">
                    <button type="submit" name="add_review_btn">Добавить</button>
                </form>
            </div>

            <div class="admin-form">
                <h2>Добавить статью блога</h2>
                <?php if (isset($msg_blog)): ?>
                    <div class="msg-success"><?php echo $msg_blog; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="text" name="blog_title" placeholder="Заголовок" required>
                    <textarea name="blog_description" placeholder="Описание" rows="3" required></textarea>
                    <button type="submit" name="add_blog_btn">Добавить</button>
                </form>
            </div>

            <h2>Все праздники (<?php echo count($holidays); ?>)</h2>
            <?php if (empty($holidays)): ?>
                <p>Нет праздников</p>
            <?php else: ?>
                <?php foreach($holidays as $item): ?>
                    <div class="item-card">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <a href="?page=admin&delete_holiday=<?php echo $item['id']; ?>" class="delete-btn" onclick="return confirm('Удалить?')">Удалить</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <h2>Все отзывы (<?php echo count($reviews); ?>)</h2>
            <?php if (empty($reviews)): ?>
                <p>Нет отзывов</p>
            <?php else: ?>
                <?php foreach($reviews as $item): ?>
                    <div class="item-card">
                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                        <p><?php echo htmlspecialchars($item['text']); ?></p>
                        <p><strong>Автор:</strong> <?php echo htmlspecialchars($item['author']); ?></p>
                        <a href="?page=admin&delete_review=<?php echo $item['id']; ?>" class="delete-btn" onclick="return confirm('Удалить?')">Удалить</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <h2>Все статьи блога (<?php echo count($blogs); ?>)</h2>
            <?php if (empty($blogs)): ?>
                <p>Нет статей</p>
            <?php else: ?>
                <?php foreach($blogs as $item): ?>
                    <div class="item-card">
                        <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <a href="?page=admin&delete_blog=<?php echo $item['id']; ?>" class="delete-btn" onclick="return confirm('Удалить?')">Удалить</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <p style="text-align:center;margin-top:30px;"><a href="?page=home">На главную</a></p>
        </div>
    </div>
<?php endif; ?>
<?php endif; ?>

<!-- ====== FOOTER ====== -->
<footer>
    <div class="grid_11">
        <div class="zo">
            <p class="e_1">мафффин</p>
            <p class="e_2">Мы готовим маленькие сладости, которые приносят большие улыбки в любой день и без повода</p>
        </div>
        <div class="zo">
            <p class="e_1">информация</p>
            <p class="e_2">Почему мы?</p>
            <p class="e_2">Как сделать заказ?</p>
            <p class="e_2">Оплата и доставка</p>
        </div>
        <div class="zo">
            <p class="e_1">служба поддержки</p>
            <p class="e_2">Обратная связь</p>
            <p class="e_2">Возврат маффинов</p>
            <p class="e_2">Отзывы</p>
        </div>
        <div class="zo">
            <p class="e_1">мы в соц сетях</p>
            <div>
                <img class="ci_10_img" src="img/free-icon-vk-5968835.png">
                <img class="ci_10_img" src="img/free-icon-telegram-2111646.png">
                <img class="ci_10_img" src="img/free-icon-facebook-733547.png">
            </div>
        </div>
    </div>
</footer>

</body>
</html>
<?php
mysqli_close($conn);
?>


-- ============================================================
-- БАЗА ДАННЫХ ДЛЯ САЙТА "МАФФИН"
-- ============================================================

-- 1. СОЗДАНИЕ БАЗЫ ДАННЫХ
CREATE DATABASE IF NOT EXISTS exam_db;
USE exam_db;

-- ============================================================
-- 2. УДАЛЕНИЕ СТАРЫХ ТАБЛИЦ (ЕСЛИ НУЖНО ПЕРЕСОЗДАТЬ)
-- ============================================================
DROP TABLE IF EXISTS blogs;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS holidays;
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
-- 4. ТАБЛИЦА ПРАЗДНИКОВ
-- ============================================================
CREATE TABLE IF NOT EXISTS holidays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. ТАБЛИЦА ОТЗЫВОВ
-- ============================================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    text TEXT NOT NULL,
    author VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. ТАБЛИЦА БЛОГА
-- ============================================================
CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. ДОБАВЛЕНИЕ АДМИНИСТРАТОРА
-- ============================================================
INSERT INTO users (login, password, role) VALUES 
('admin', 'admin', 'admin')
ON DUPLICATE KEY UPDATE login=login;

-- ============================================================
-- 8. ДОБАВЛЕНИЕ ТЕСТОВЫХ ПРАЗДНИКОВ
-- ============================================================
INSERT INTO holidays (name) VALUES 
('День рождение'),
('Свадьба'),
('8 марта'),
('День всех влюблённых'),
('Новый год'),
('Корпоратив')
ON DUPLICATE KEY UPDATE name=name;

-- ============================================================
-- 9. ДОБАВЛЕНИЕ ТЕСТОВЫХ ОТЗЫВОВ
-- ============================================================
INSERT INTO reviews (name, text, author) VALUES 
('Вкусные маффины', 'Мягкие, немного влажноватые, как будто с пропиткой. Дети на завтрак смолотили в секунду. Очень вкусно! Буду покупать еще', 'Ольга'),
('Лучший подарок', 'Приятные на вкус, покупали на день рождение дочери. Гости были в восторге.', 'Ирина'),
('Безупречный сервис', 'Спасибо все идеально. Отлично упаковано, доставка вовремя, блюдо очень красиво оформлено и все было невероятно вкусно.', 'София');

-- ============================================================
-- 10. ДОБАВЛЕНИЕ ТЕСТОВЫХ СТАТЕЙ БЛОГА
-- ============================================================
INSERT INTO blogs (title, description) VALUES 
('7 рецептов самых вкусных шоколадных маффинов', 'Шоколадные маффины – это классика, которая никогда не надоедает. В этой статье мы собрали для вас 7 лучших рецептов, которые покорят ваше сердце и желудок. От классических с шоколадной крошкой до изысканных с жидкой начинкой – каждый найдет свой идеальный рецепт.'),
('Лучшие начинки для маффинов: как приготовить?', 'Начинка – это душа маффина! В этой статье мы расскажем о самых популярных и вкусных начинках: от классической шоколадной пасты до нежного творожного крема. Вы узнаете секреты идеальной начинки и научитесь готовить ее дома.'),
('Как сделать воздушный крем для маффинов?', 'Воздушный крем – это то, что делает маффины по-настоящему особенными. В этой статье мы поделимся с вами профессиональными секретами приготовления идеального крема. Вы узнаете, как добиться нужной консистенции и какие ингредиенты использовать для лучшего результата.');

-- ============================================================
-- 11. ПРОВЕРКА ДАННЫХ
-- ============================================================
SELECT * FROM users;
SELECT * FROM holidays;
SELECT * FROM reviews;
SELECT * FROM blogs;

-- ============================================================
-- 12. ПОКАЗАТЬ СТРУКТУРУ ТАБЛИЦ
-- ============================================================
DESCRIBE users;
DESCRIBE holidays;
DESCRIBE reviews;
DESCRIBE blogs;

-- ============================================================
-- 13. ДОПОЛНИТЕЛЬНЫЕ ЗАПРОСЫ
-- ============================================================

-- Количество записей в таблицах
SELECT 'users' as table_name, COUNT(*) as count FROM users
UNION ALL
SELECT 'holidays', COUNT(*) FROM holidays
UNION ALL
SELECT 'reviews', COUNT(*) FROM reviews
UNION ALL
SELECT 'blogs', COUNT(*) FROM blogs;

-- Последние 5 добавленных отзывов
SELECT * FROM reviews ORDER BY id DESC LIMIT 5;

-- Все праздники в алфавитном порядке
SELECT * FROM holidays ORDER BY name;

-- Статьи блога с датами создания
SELECT id, title, created_at FROM blogs ORDER BY created_at DESC;

-- ============================================================
-- 14. ОЧИСТКА ТАБЛИЦ (ЕСЛИ НУЖНО)
-- ============================================================
-- TRUNCATE TABLE holidays;
-- TRUNCATE TABLE reviews;
-- TRUNCATE TABLE blogs;
-- TRUNCATE TABLE users;

-- ============================================================
-- 15. ПОЛНОЕ УДАЛЕНИЕ БАЗЫ (ЕСЛИ НУЖНО ПЕРЕСОЗДАТЬ)
-- ============================================================
-- DROP DATABASE IF EXISTS exam_db;