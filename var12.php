<?php
$host = 'localhost';
$dbname = 'crypto_course_db';
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

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    course VARCHAR(50) DEFAULT 'Курс по криптовалюте x1',
    price VARCHAR(50) DEFAULT '25000',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS faq (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Добавляем админа
$check_admin = mysqli_query($conn, "SELECT * FROM users WHERE login='admin'");
if (mysqli_num_rows($check_admin) == 0) {
    mysqli_query($conn, "INSERT INTO users (login, password, role) VALUES ('admin', 'admin', 'admin')");
}

// Добавляем тестовые FAQ
$check_faq = mysqli_query($conn, "SELECT * FROM faq");
if (mysqli_num_rows($check_faq) == 0) {
    mysqli_query($conn, "INSERT INTO faq (question, answer) VALUES 
        ('Тон-полутоновый контрапункт контрастных фактур: предпосылки и развитие?', 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Earum nulla rerum voluptatum unde id veritatis culpa suscipit iste fugit, tempora quidem, praesentium, ipsam dolorem doloribus consequuntur voluptatem animi possimus reprehenderit.'),
        ('Абстрактный график функции в XXI веке?', 'Рондо, так или иначе, представляет собой open-air. Хорус, в первом приближении, трансформирует диссонансный флюгель-горн. Пауза многопланово начинает дорийский пласт.'),
        ('Прецизионный курс: гипотеза и теории?', 'Рондо, так или иначе, представляет собой open-air. Хорус, в первом приближении, трансформирует диссонансный флюгель-горн. Пауза многопланово начинает дорийский пласт.'),
        ('Твердый альтиметр: привлечение аудитории или ПИГ?', 'Рондо, так или иначе, представляет собой open-air. Хорус, в первом приближении, трансформирует диссонансный флюгель-горн. Пауза многопланово начинает дорийский пласт.')");
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

// ===== ОБРАБОТКА ЗАКАЗА ИЗ МОДАЛЬНОГО ОКНА =====
if (isset($_POST['order_submit'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['order_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['order_email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['order_phone']));
    $course = isset($_POST['order_course']) ? mysqli_real_escape_string($conn, trim($_POST['order_course'])) : 'Курс по криптовалюте x1';
    $price = isset($_POST['order_price']) ? mysqli_real_escape_string($conn, trim($_POST['order_price'])) : '25000';
    
    if ($name && $email && $phone) {
        $sql = "INSERT INTO orders (name, email, phone, course, price) 
                VALUES ('$name', '$email', '$phone', '$course', '$price')";
        
        if (mysqli_query($conn, $sql)) {
            $order_msg = '✅ Заказ успешно оформлен! Мы свяжемся с вами.';
        } else {
            $order_msg = '❌ Ошибка оформления заказа: ' . mysqli_error($conn);
        }
    } else {
        $order_msg = '❌ Заполните все поля!';
    }
}

$faq_list = [];
$result_faq = mysqli_query($conn, "SELECT * FROM faq");
if ($result_faq) {
    while ($row = mysqli_fetch_assoc($result_faq)) $faq_list[] = $row;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Курс по криптовалюте</title>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    
    <style>
        /* ============================================================
           ВСЕ СТИЛИ ИЗ true_style.css ВСТРОЕНЫ ЗДЕСЬ
           ============================================================ */
        
        /* ----- ШРИФТЫ ----- */
        @font-face {
            font-family: 'Open Sans';
            src: url('fonts/OpenSans-VariableFont_wdth,wght.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Inter';
            src: url('fonts/Inter-VariableFont_slnt,wght.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Dela Gothic One';
            src: url('fonts/DelaGothicOne-Regular.ttf') format('truetype');
        }

        /* ----- RESET ----- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0D0D0D; font-family: 'Open Sans', sans-serif; }
        
        p { color: #ECEEF3; font-family: 'Open Sans', sans-serif; font-size: 22px; }
        a { color: #ECEEF3; font-family: 'Open Sans', sans-serif; font-size: 22px; text-decoration: none; }
        a:hover { text-decoration: underline; }
        h6 { font-weight: normal; }
        button:hover { cursor: pointer; }
        
        .anim, .anim2 { animation-play-state: paused; }
        .anim.play, .anim2.play { animation-play-state: running; }

        .wrapper {
            position: relative;
            width: 100%;
            min-height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .container {
            max-width: 1920px;
            margin: 0 auto;
            padding: 0 100px;
            box-sizing: border-box;
        }

        @media (max-width: 1280px) { .container { padding: 0 70px; } }
        @media (max-width: 768px) { .container { padding: 0 40px; } }
        @media (max-width: 500px) { .container { padding: 0 10px; } }

        .line {
            background: linear-gradient(90deg, #5852F2 0%, #6F6BF3 100%);
            height: 13px;
        }
        .title {
            color: #ECEEF3;
            font-family: 'Dela Gothic One', sans-serif;
            font-size: 60px;
            font-weight: 400;
        }
        .course-buy {
            display: flex;
            align-items: center;
        }
        .course-buy-left {
            display: inline-block;
        }
        .course-buy-text {
            font-family: 'Open Sans', sans-serif;
            font-size: 28px;
            font-weight: 600;
            background: linear-gradient(92deg, #5952F2 1.37%, #6F6BF3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 9px;
        }
        .course-buy-line {
            background: linear-gradient(90deg, #5852F2 0%, #6F6BF3 100%);
            height: 7px;
            float: right;
            transition: .5s;
            width: 100%;
        }
        .course-buy-arrow {
            margin-left: 25px;
            transition: .5s;
        }
        .course-buy-left:hover { cursor: pointer; }
        .course-buy-left:hover .course-buy-line { width: 80%; }
        .course-buy-left:hover ~ .course-buy-arrow { margin-left: 35px; }

        @media (max-width: 1280px) { .title { font-size: 45px; } }
        @media (max-width: 768px) { .title { font-size: 35px; margin-top: 20px; } }
        @media (max-width: 500px) { .title { font-size: 25px; } }
        @media (max-width: 650px) {
            .line { width: 160px !important; height: 7px; }
            .block-main .line { margin: 20px 0 !important; }
        }

        /* ----- HEADER ----- */
        .header {
            margin-top: 23px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            width: 100%;
            z-index: 3;
        }
        .header-buttons { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
        .header-button {
            color: #ECEEF3;
            font-family: 'Open Sans', sans-serif;
            font-size: 22px;
            opacity: 0.5;
            margin-bottom: 0;
            box-sizing: border-box;
            background: none;
            border: none;
            padding: 11px 10px;
            transition: 0.2s;
        }
        .header-button:hover { opacity: 1; cursor: pointer; }
        .header-button.c {
            border: 2px solid #ECEDF2;
            padding: 11px 10px;
            transition: 0.2s;
        }
        .header-button.c:hover { opacity: 1; }
        .gl { position: relative; }
        .header-nav {
            position: absolute;
            transform: translate(-50%);
            left: 50%;
            top: 0;
            border-radius: 2px;
            background: #ECEEF3;
            box-shadow: 0px 4px 4px 0px rgba(0, 0, 0, 0.25) inset;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0px 16px;
            box-sizing: border-box;
            max-height: 0px;
            transition: max-height 0.3s, padding 0.3s;
            overflow: hidden;
        }
        .header-nav.active {
            padding: 6px 16px;
            max-height: 540px;
            transition: max-height 0.3s, padding 0.3s;
        }
        .header-nav a {
            color: #0D0D0D;
            font-family: 'Open Sans', sans-serif;
            font-size: 22px;
            text-align: center;
        }
        .h-dots {
            display: flex;
            justify-content: space-between;
            width: 40%;
            margin: 9px 0;
        }
        .h-dots .dot { background: black; opacity: 0.1; }
        .dots {
            grid-gap: 6px;
            display: flex;
            justify-content: center;
            transition: 0.2s;
        }
        .dot {
            width: 6px;
            height: 6px;
            background: #ECEDF2;
            border-radius: 50%;
            opacity: 0.5;
        }
        .gl:hover .dots { grid-gap: 15px; }
        .logo { width: 80px; height: 80px; }

        @media (max-width: 650px) {
            .header-button { font-size: 18px; }
            .header-nav a { font-size: 18px; }
            .header-button.c { margin-left: 25px; }
        }
        @media (max-width: 350px) {
            .header-button { font-size: 14px; }
            .header-nav a { font-size: 14px; }
            .header-button.c { margin-left: 5px; }
        }

        .admin-link { color: red !important; font-weight: bold; }
        .login-link { color: #007bff !important; font-weight: bold; }
        .header .admin-link { opacity: 1; }
        .header .login-link { opacity: 1; }

        /* ----- MAIN BLOCK ----- */
        .block-main {
            position: relative;
            height: 900px;
        }
        .main-text {
            color: #ECEDF2;
            font-family: 'Dela Gothic One', sans-serif;
            font-size: 115px;
            line-height: 117.2%;
            margin-top: 60px;
        }
        .main-left {
            animation-duration: 0.8s;
            animation-name: textAppear;
            animation-fill-mode: both;
        }
        @keyframes textAppear {
            0% { margin-left: -200px; opacity: 0; }
            100% { margin-left: 0px; opacity: 1; }
        }
        .main-bg {
            transform: translate(-50%);
            position: absolute;
            bottom: 0;
            left: 40%;
            animation-delay: 0.4s;
            animation-duration: 0.6s;
            animation-name: imgAppear;
            animation-fill-mode: both;
        }
        @keyframes imgAppear {
            0% { margin-left: -100px; opacity: 0; }
            100% { margin-left: 0px; opacity: 1; }
        }
        .main-side {
            position: absolute;
            width: 590px;
            height: 1050px;
            top: 0px;
            display: flex;
            align-items: flex-end;
        }
        @media (min-width: 1281px) {
            .main-side {
                animation-delay: 0.8s;
                animation-duration: 0.8s;
                animation-name: sideAppear;
                animation-fill-mode: both;
            }
        }
        @keyframes sideAppear {
            0% { right: -600px; }
            100% { right: 0px; }
        }
        .side-bg, .side-bg-h {
            position: absolute;
            top: 0;
            right: 0px;
            backdrop-filter: blur(10px);
            width: 100%;
            height: 100%;
            z-index: 0;
        }
        .side-bg-h { display: none; }
        .side-arrow {
            position: absolute;
            top: 380px;
            left: -10px;
        }
        @media (min-width: 1281px) {
            .side-arrow {
                animation-delay: 1.2s;
                animation-duration: 0.6s;
                animation-name: arrowRotate;
                animation-fill-mode: both;
            }
        }
        @keyframes arrowRotate {
            0% { rotate: 90deg; }
            100% { rotate: 0deg; }
        }
        .side-content {
            position: relative;
            z-index: 1;
            height: 50%;
            width: 100%;
            padding: 0 68px 180px 68px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .side-text-b { font-size: 40px; font-weight: 600; }
        .side-text { font-size: 20px; font-weight: 200; opacity: 0.7; }
        .side-price { font-size: 38px; font-weight: 400; opacity: 0.7; }

        @media (max-width: 1880px) { .main-text { font-size: 90px; } }
        @media (max-width: 1620px) {
            .main-text { font-size: 60px; }
            .main-bg { left: 30%; }
        }
        @media (max-width: 1280px) {
            .block-main { height: 700px; }
            .main-text { font-size: 53px; }
            .main-bg { width: 825px; left: 50%; }
            .logo.c { width: 60px; height: 60px; }
            .main-side {
                position: relative;
                top: -150px;
                width: auto;
                height: 395px;
                align-items: flex-start;
            }
            .side-arrow { top: 0; left: 17.5%; }
            .side-bg { display: none; }
            .side-bg-h { display: block; }
            .side-content { padding: 80px 70px 40px 70px; height: 100%; }
        }
        @media (max-width: 768px) {
            .block-main { height: 500px; }
            .main-bg { width: 550px; left: 60%; }
            .logo.c { width: 40px; height: 40px; }
            .main-side { top: -50px; }
        }
        @media (max-width: 650px) {
            .block-main { height: 375px; }
            .main-bg { width: 400px; left: 60%; }
            .main-text { font-size: 35px; }
            .main-img img { width: 15px; }
            .main-side { height: 320px; }
            .side-content { padding: 45px 40px; }
            .side-text-b { font-size: 30px; }
            .side-text { font-size: 17px; }
            .side-price { font-size: 28px; }
        }
        @media (max-width: 500px) {
            .side-content { padding: 45px 10px; }
            .main-bg { left: 50%; }
            .main-text { font-size: 30px; }
        }
        @media (max-width: 350px) {
            .block-main { height: 325px; }
            .main-bg { width: 350px; left: 50%; }
            .main-text { font-size: 25px; }
            .side-text-b { font-size: 25px; }
            .side-text { font-size: 14px; }
            .side-price { font-size: 23px; }
        }

        /* ----- TRANSITION ----- */
        .transition { position: relative; }
        .transition.hidden { display: none; }
        .transition-bg {
            position: absolute;
            top: -20px;
            left: -20px;
            width: 120%;
            height: 500px;
            transform: rotate(4deg);
            background-color: white;
        }
        .transition-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            transform: rotate(4deg);
        }
        .transition-row {
            display: flex;
            width: 100%;
        }
        .transition-row.u { justify-content: flex-end; }
        .transition-row.d { justify-content: flex-start; }
        .transition-text {
            color: #0D0D0D;
            font-family: 'Dela Gothic One', sans-serif;
            font-size: 60px;
            font-weight: 400;
            flex-shrink: 0;
            margin: 0 30px;
            width: 30%;
        }
        .transition-text.down { width: 60%; }
        .transition-arrow {
            background-image: url("img/Arrow 2.svg");
            background-repeat: no-repeat;
            background-position: right center;
            filter: drop-shadow(0px 4px 4px rgba(0, 0, 0, 0.25));
            width: 38%;
        }
        .transition-line {
            display: flex;
            align-items: center;
            width: 55%;
            overflow: hidden;
        }
        .transition-line-icons {
            margin: 0 15px;
            transform: rotate(-4deg);
        }
        .transition-line-l {
            width: 80%;
            height: 9px;
            background: linear-gradient(90deg, #5852F2 0%, #6F6BF3 100%);
            filter: drop-shadow(0px 4px 4px rgba(0, 0, 0, 0.25));
        }
        .transition-line-l.short { width: 10%; }
        @media (max-width: 1620px) { .transition-text { font-size: 50px; } }
        @media (max-width: 1280px) {
            .transition { z-index: -1; }
            .transition-text { font-size: 40px; }
        }
        @media (max-width: 768px) { .transition-arrow { width: 23%; } }
        @media (max-width: 650px) { .transition-text { font-size: 30px; } }
        @media (max-width: 500px) { .transition-text { font-size: 20px; } }

        /* ----- PLUSES ----- */
        .block-pluses { position: relative; margin-top: 20px; background: white; }
        .pluses-list {
            padding-top: 100px;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }
        .pluses-item {
            position: relative;
            width: 500px;
            height: 386px;
            background: #5A54F2;
            flex-shrink: 0;
            transform: rotate(3deg);
            margin: 0 40px;
            margin-bottom: 130px;
            padding-left: 45px;
            padding-top: 32px;
            box-sizing: border-box;
        }
        .pluses-cnt {
            animation-duration: 0.5s;
            animation-name: plusItemAppear;
            animation-fill-mode: forwards;
            opacity: 0;
        }
        .pluses-cnt.s { animation-delay: 0.3s; }
        .pluses-cnt.t { animation-delay: 0.6s; }
        @keyframes plusItemAppear {
            0% { opacity: 0; transform: translate(-220px, 220px) rotate(-20deg); }
            100% { opacity: 1; transform: translate(0, 0px); }
        }
        .pluses-item-list { width: 400px; }
        .pluses-item-row {
            display: flex;
            align-items: flex-start;
        }
        .pluses-p {
            color: #ECEEF3;
            font-family: 'Open Sans', sans-serif;
            font-size: 20px;
            margin-bottom: 0;
            line-height: 144.523%;
            margin-left: 10px;
        }
        .pluses-item-tag {
            position: absolute;
            left: -40px;
            bottom: -60px;
            width: 365px;
            height: 115px;
            border-radius: 4px;
            background: #5A54F2;
            text-align: center;
            padding: 20px 0;
            box-sizing: border-box;
            z-index: 2;
        }
        .pluses-item-tag p {
            color: rgba(255, 255, 255, 0.90);
            font-size: 52px;
            font-weight: 600;
        }
        .pluses-img-sub {
            position: absolute;
            transform: rotate(-3deg);
            left: -8px;
            bottom: -45px;
            width: 101%;
        }
        .pluses-img-icon {
            position: absolute;
            z-index: 1;
            bottom: 0px;
            left: 10px;
            transform: rotate(-3deg);
        }
        .pluses-img-un {
            position: absolute;
            right: -6px;
            bottom: -13px;
            transform: rotate(-3deg);
        }
        @media (max-width: 650px) {
            .pluses-cnt { scale: 0.7; margin-top: -150px; }
        }
        @media (max-width: 500px) {
            .pluses-cnt { scale: 0.5; margin-top: -250px; }
            .pluses-list { padding-top: 150px; }
        }

        /* ----- ABOUT ----- */
        .about-imgt { position: relative; }
        .about-text {
            position: absolute;
            top: 0px;
            left: 50%;
        }
        .about-text p {
            color: #FFF;
            font-family: 'Open Sans', sans-serif;
            font-size: 25px;
            font-weight: 600;
        }
        .about-text p span { font-size: 20px; opacity: 0.8; }
        .about-bubble {
            position: absolute;
            right: 118px;
            top: 325px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            background-image: url(img/about-b.png);
            width: 595px;
            height: 462px;
            z-index: 2;
            box-sizing: border-box;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .bubble-text {
            width: 380px;
            color: rgba(236, 238, 243, 0.80);
            font-family: 'Inter', sans-serif;
            font-weight: 400;
            font-size: 22px;
            margin-top: 85px;
        }
        .bubble-text span { color: #5953F2; }
        .about-img { width: 100%; }

        @media (max-width: 1280px) {
            .about-bubble {
                position: relative;
                margin-top: -100px;
                right: 0;
                top: 0;
                width: 100%;
                height: 600px;
            }
            .bubble-text { font-size: 24px; width: 450px; }
            .about-text p { font-size: 20px; }
            .about-text p span { font-size: 18px; }
        }
        @media (max-width: 768px) {
            .about-text p { font-size: 16px; }
            .about-text p span { font-size: 14px; }
            .bubble-text { font-size: 20px; width: 400px; }
        }
        @media (max-width: 650px) {
            .about-text p { font-size: 12px; }
            .about-text p span { font-size: 10px; }
            .about-bubble { height: 475px; }
            .bubble-text { font-size: 16px; width: 350px; }
        }
        @media (max-width: 500px) {
            .about-text p { font-size: 8px; }
            .about-text p span { font-size: 6px; }
            .about-bubble { height: 425px; }
            .bubble-text { font-size: 12px; width: 250px; }
        }

        /* ----- COURSE ----- */
        .block-course {
            padding-top: 50px;
            position: relative;
        }
        .course-bg {
            position: absolute;
            z-index: -1;
            top: 171px;
            right: -100px;
            width: 821px;
            height: 448px;
            transform: rotate(19deg);
            border-radius: 2px;
            background: #5953F2;
        }
        .course-bg-img {
            position: absolute;
            z-index: -1;
            top: 67px;
            right: 0;
        }
        .course-list {
            margin-top: 65px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        .course-item {
            height: 231px;
            width: 100%;
            background-color: #262626;
            position: relative;
            flex-shrink: 0;
            margin-bottom: 21px;
        }
        .course-item.b { height: 483px; }
        .course-tag {
            position: absolute;
            z-index: 2;
            bottom: 0;
            left: 0;
            padding: 10px 20px 17px 20px;
            fill: rgba(236, 237, 242, 0.10);
            backdrop-filter: blur(21.299999237060547px);
            border: 1px solid rgba(255, 255, 255, 0.10);
            box-sizing: border-box;
        }
        .course-tag p {
            font-size: 25px;
            font-weight: 400;
            text-align: left;
        }
        .course-icon {
            position: absolute;
            bottom: 0;
            left: 55%;
            transform: translate(-50%);
            z-index: 1;
        }
        .course-column {
            display: flex;
            flex-direction: column;
            width: 32.5%;
        }
        .more {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100px;
            margin: 0 auto;
            margin-bottom: 45px;
            box-sizing: border-box;
            transition: .5s;
        }
        .more-p {
            margin-top: 7px;
            color: rgba(236, 238, 243, 0.80);
            font-family: 'Open Sans', sans-serif;
            font-size: 14px;
            font-weight: 300;
            transition: .5s;
        }
        .more:hover { padding-top: 7px; }
        .more:hover .more-p { margin-top: 0px; }

        @media (max-width: 1280px) {
            .course-bg, .course-bg-img { display: none; }
            .course-list { margin-top: 50px; }
            .course-column { width: 48%; }
            .course-column.b { width: 100%; }
            .course-item.b { height: 162px; }
            .course-tag p { font-size: 22px; }
            .course-icon { right: -25px; }
            .course-icon.b { width: 240px; }
        }
        @media (max-width: 768px) {
            .block-course { padding-top: 0; }
            .course-icon { width: 225px; }
            .course-icon.sm { width: 150px; }
            .course-tag p { font-size: 18px; }
        }
        @media (max-width: 500px) {
            .course-icon { width: 175px; }
            .course-icon.sm { width: 125px; }
            .course-icon.b { width: 200px; }
            .course-tag p { font-size: 12px; }
            .course-item { height: 130px; }
            .course-item.b { height: 130px; }
            .course-tag { padding: 8px 15px 8px 15px; }
        }
        @media (max-width: 350px) {
            .course-icon { width: 130px; }
            .course-icon.sm { width: 100px; }
            .course-icon.b { width: 180px; }
            .course-tag p { font-size: 12px; }
            .course-item { height: 130px; }
            .course-item.b { height: 130px; }
        }

        /* ----- PROGRAM ----- */
        .program-desc {
            margin-top: 35px;
            margin-bottom: 50px;
            font-size: 20px;
            max-width: 712px;
            opacity: 0.8;
        }
        .program-module {
            border-top: 4px solid #ECEEF3;
            border-bottom: 4px solid #ECEEF3;
            overflow: hidden;
            margin-bottom: -4px;
            max-height: 124px;
            transition: max-height 0.3s;
        }
        .program-module.active {
            max-height: 360px;
            transition: max-height 0.3s;
        }
        .program-module-img {
            position: absolute;
            background-image: url("img/dog.svg");
            background-repeat: no-repeat;
            background-position: right center;
            background-size: contain;
            width: 100%;
            height: 100%;
            top: 0;
            right: 200px;
            opacity: 0;
            transition: .3s;
        }
        .program-module.active .program-module-img { opacity: 1; }
        .program-module-num {
            color: #5A54F2;
            font-size: 15px;
            margin-top: 14px;
            transition: .3s;
        }
        .program-module-name {
            margin-top: -12px;
            color: #fbfbfb;
            font-size: 100px;
            opacity: 0.2;
            white-space: nowrap;
            transition: .3s;
        }
        .program-module.active .program-module-name {
            font-size: 49px;
            opacity: 0.7;
        }
        .program-module-steps {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            max-width: 700px;
            margin-top: 37px;
        }
        .p-mod-p {
            font-size: 20px;
            opacity: 0.8;
            margin-bottom: 9px;
        }
        .program-side {
            position: absolute;
            top: 150px;
            right: 0;
            background-image: url("img/program-side.svg");
            background-position: left top;
            backdrop-filter: blur(29px);
            filter: drop-shadow(-8px 4px 4px rgba(0, 0, 0, 0.08));
            width: 233px;
            height: 1419px;
            margin-right: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .program-arrow {
            width: 40px;
            height: 40px;
            opacity: 0.6;
            position: absolute;
            top: 1550px;
            right: 96px;
            margin-right: 100px;
        }
        .program-img {
            width: 20px;
            height: 20px;
            margin: 25px 0;
            opacity: 0.2;
        }
        .barcode {
            opacity: 0.2;
            margin-top: 22px;
            width: 160px;
            height: 50px;
        }
        .program-info {
            margin-top: 83px;
            margin-bottom: 100px;
            display: flex;
            justify-content: space-between;
            width: 90%;
        }
        .program-info-block { margin-right: 10px; }
        .program-info-down {
            display: flex;
            align-items: end;
            margin-top: -10px;
        }
        .program-info-text1 {
            color: #ECEEF3;
            font-family: 'Open Sans', sans-serif;
            font-size: 30px;
            font-weight: 300;
            opacity: 0.5;
        }
        .program-info-text2 {
            color: #ECEEF3;
            font-family: 'Inter', sans-serif;
            font-size: 100px;
            font-weight: 700;
        }
        .program-info-text3 {
            color: #ECEEF3;
            font-family: 'Open Sans', sans-serif;
            font-size: 81px;
            font-weight: 400;
            opacity: 0.79;
            margin-left: 21px;
        }

        @media (max-width: 1280px) {
            .program-desc { font-size: 18px; }
            .program-side, .program-arrow { margin-top: 60px; margin-right: 70px; }
            .program-info { margin-top: 50px; margin-bottom: 100px; width: 50%; }
            .program-info-down { margin-top: 0px; }
            .program-info-text1 { font-size: 14px; }
            .program-info-text2 { font-size: 50px; }
            .program-info-text3 { font-size: 27px; margin-bottom: 4px; margin-left: 15px; }
            .program-module-img { display: none; }
            .program-module-steps { flex-direction: column; }
            .program-module-name { font-size: 60px; margin-top: 0px; }
            .program-module.active .program-module-name {
                word-wrap: break-word;
                white-space: wrap;
                margin-top: 15px;
                width: 60%;
                opacity: 1;
            }
            .program-module.active { max-height: 565px; }
        }
        @media (max-width: 768px) {
            .program-desc { font-size: 16px; }
            .program-side, .program-arrow { margin-right: 40px; }
        }
        @media (max-width: 650px) {
            .program-side, .program-arrow { display: none; }
            .program-module.active .program-module-name { width: 100%; font-size: 40px; }
            .p-mod-p { font-size: 16px; }
        }
        @media (max-width: 500px) {
            .program-side, .program-arrow { display: none; }
            .program-module.active .program-module-name { width: 100%; font-size: 40px; }
            .p-mod-p { font-size: 16px; }
            .program-info-text1 { font-size: 12px; }
            .program-info-text2 { font-size: 40px; }
            .program-info-text3 { font-size: 20px; margin-left: 5px; margin-bottom: 4px; }
        }
        @media (max-width: 350px) { .program-info { flex-direction: column; } }
        @media (min-width: 1280px) {
            .program-module.active .program-module-num { font-size: 109px; color: white; }
        }

        /* ----- REVIEWS ----- */
        .block-reviews {
            box-sizing: border-box;
            padding-top: 72px;
        }
        .review-bottom-text1 {
            color: #ECEEF3;
            font-family: 'Open Sans', sans-serif;
            font-size: 40px;
            font-weight: 700;
        }
        .review-bottom-text2 {
            color: #ECEEF3;
            font-family: 'Inter', sans-serif;
            font-size: 30px;
            font-weight: 400;
            opacity: 0.5;
        }
        .review-scroll {
            margin: 0 auto;
            margin-top: 140px;
            transform: rotate(4deg);
            max-width: 1940px;
            width: 150%;
            position: relative;
            left: -25px;
            overflow: hidden;
        }
        @keyframes rowl {
            0% { transform: translateZ(0); }
            100% { transform: translate3d(-106%, 0, 0); }
        }
        @keyframes rowr {
            0% { transform: translate3d(-106%, 0, 0); }
            100% { transform: translateZ(0); }
        }
        .review-row {
            display: flex;
            flex-wrap: nowrap;
            margin-bottom: 34px;
            will-change: transform;
        }
        .review-row.r { animation: rowr 80s linear infinite; }
        .review-row.l { animation: rowl 80s linear infinite; }
        .review-scroll:hover .review-row { animation-play-state: paused; }
        .review-item {
            position: relative;
            width: 264px;
            height: 148px;
            border-radius: 2px;
            background: #ECEEF3;
            box-shadow: 0px 4px 4px 0px rgba(0, 0, 0, 0.25);
            padding: 15px 25px;
            box-sizing: border-box;
            margin-right: 30px;
            flex-shrink: 0;
        }
        .review-item:hover { cursor: pointer; }
        .review-img {
            width: 54px;
            margin-right: 20px;
            border-radius: 50%;
            box-shadow: 0px 4px 4px 0px rgba(0, 0, 0, 0.25);
        }
        .review-name {
            color: #0D0D0D;
            font-family: 'Open Sans', sans-serif;
            font-size: 20px;
            font-weight: 400;
        }
        .review-text {
            margin-top: 10px;
            color: #0D0D0D;
            font-family: 'Open Sans', sans-serif;
            font-size: 14px;
            font-weight: 300;
        }
        .review-top { display: flex; }
        .review-more {
            position: absolute;
            right: 10px;
            font-family: 'Open Sans', sans-serif;
            font-size: 13px;
            font-weight: 600;
            background: linear-gradient(92deg, #5952F2 1.37%, #6F6BF3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ----- POPUP ----- */
        .popup {
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 99;
            visibility: hidden;
            padding: 30px;
            box-sizing: border-box;
        }
        .popup.active { visibility: visible; }
        .black {
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            background-color: black;
            opacity: 0;
            transition: 0.8s;
        }
        .popup.active .black { opacity: 0.8; }
        .popup-content {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100%;
        }
        .popup-wp {
            transform: rotate(-11deg);
            display: flex;
            justify-content: center;
        }
        .popup-body {
            position: relative;
            width: 90%;
            max-width: 700px;
            min-height: 324px;
            background-color: #ECEEF3;
            padding: 40px 50px;
            box-sizing: border-box;
            transition: all 0.3s ease 0.2s;
            transform: scale(0);
        }
        .popup.active .popup-body {
            transition: all 0.3s ease 0.2s;
            transform: scale(1);
        }
        .quote {
            position: absolute;
            right: -58px;
            top: -42px;
            width: 115px;
            height: 85px;
            border-radius: 4px;
            border: 1px solid rgba(236, 238, 243, 0.20);
            background: rgba(217, 217, 217, 0.10);
            backdrop-filter: blur(12.5px);
        }
        .quote p {
            text-align: center;
            color: #5953F2;
            font-family: 'Inter', sans-serif;
            font-size: 110px;
            font-weight: 400;
        }

        @media (max-width: 768px) {
            .review-scroll { margin-top: 60px; }
            .review-bottom-text1 { font-size: 30px; }
            .review-bottom-text2 { font-size: 20px; }
        }

        /* ----- FAQ ----- */
        .block-faq {
            box-sizing: border-box;
            padding-top: 94px;
        }
        .faq-f {
            margin-top: 55px;
            position: relative;
            height: 80px;
        }
        .faq-f-text {
            color: #ECEEF3;
            font-family: 'Open Sans', sans-serif;
            font-size: 40px;
            font-weight: 400;
            position: absolute;
            left: 29px;
            top: 13px;
        }
        .faq-f-q {
            position: absolute;
            color: rgba(236, 238, 243, 0.71);
            font-family: 'Dela Gothic One', sans-serif;
            font-size: 40px;
            font-weight: 400;
            transform: rotate(17deg);
            left: 230px;
            top: -40px;
        }
        .faq-f-img {
            position: absolute;
        }
        .faq-questions { margin-top: 72px; }
        .faq-line {
            display: flex;
            align-items: center;
        }
        .faq-line-l {
            height: 2px;
            background-color: rgba(251, 251, 251, 0.40);
            width: 100%;
        }
        .faq-line-img {
            margin: 0 35px;
            opacity: 0.3;
        }
        .faq-questions-q { margin: 27px 0; }
        .faq-questions-q-title {
            display: flex;
            align-items: center;
        }
        .faq-questions-q-img {
            height: 20px;
            width: 20px;
            margin-right: 20px;
            transition: 0.5s;
            transform: rotate(0deg);
            opacity: 0.6;
        }
        .faq-questions-q:hover .faq-questions-q-img { opacity: 1; }
        .faq-questions-q-text {
            color: #ECEEF3;
            font-family: 'Open Sans', sans-serif;
            font-size: 25px;
            font-weight: 400;
        }
        .faq-questions-q-desc {
            margin-left: 40px;
            color: rgba(251, 251, 251, 0.80);
            font-family: 'Open Sans', sans-serif;
            font-size: 14px;
            font-weight: 400;
            overflow: hidden;
            max-height: 0px;
            transition: max-height 0.5s, margin-top 0.5s;
        }
        .faq-questions-q.active .faq-questions-q-desc {
            margin-top: 8px;
            max-height: 200px;
            transition: max-height 0.5s, margin-top 0.5s;
        }
        .faq-questions-q.active .faq-questions-q-img {
            transform: rotate(-90deg);
        }

        @media (max-width: 768px) {
            .faq-questions-q-text { font-size: 16px; }
            .faq-f-img { width: 120px; }
            .faq-f-text { font-size: 22px; left: 15px; top: 4px; }
            .faq-f-q { font-size: 20px; left: 115px; top: -20px; }
            .faq-questions { margin-top: 20px; }
        }

        /* ----- COMMUNITY ----- */
        .block-community {
            position: relative;
            height: 1080px;
            padding-top: 180px;
            box-sizing: border-box;
        }
        .community-bg {
            position: absolute;
            overflow: hidden;
            opacity: 0.2;
            top: 0;
            height: 1080px;
            width: 1000px;
            right: 0;
        }
        .community-bg-white {
            position: absolute;
            right: -500px;
            top: -200px;
            height: 1800px;
            width: 1000px;
            transform: rotate(12deg);
            background-color: white;
            box-shadow: inset 10px 4px 21px rgba(0, 0, 0, 0.33);
        }
        .community-bg-img {
            position: absolute;
            right: 0;
            top: 212px;
        }
        .community-bg-text {
            position: absolute;
            right: 50px;
            top: 350px;
            color: #ECEEF3;
            font-family: 'Open Sans', sans-serif;
            font-size: 20px;
            font-weight: 600;
            transform: rotate(7deg);
        }
        .community-bg-arrow1 {
            position: absolute;
            right: 12px;
            bottom: 407px;
        }
        .community-bg-arrow2 {
            position: absolute;
            right: 257px;
            bottom: 435px;
        }
        .community-info {
            max-width: 665px;
            border-radius: 2px;
            background: #6661F3;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 37px 75px;
            box-sizing: border-box;
            margin-bottom: 80px;
            position: relative;
        }
        .communuty-bottom {
            display: flex;
            align-items: flex-end;
        }
        .community-text { margin-right: 115px; }
        .community-text-top {
            color: rgba(251, 251, 251, 0.30);
            font-family: 'Open Sans', sans-serif;
            font-size: 30px;
            font-weight: 700;
        }
        .community-text-bottom {
            margin-top: -27px;
            color: #ECEEF3;
            font-family: 'Open Sans', sans-serif;
            font-size: 162px;
            font-weight: 700;
        }
        .community-info-text-t {
            color: #ECEEF3;
            font-family: 'Open Sans', sans-serif;
            font-size: 30px;
            font-weight: 700;
        }
        .community-info-text {
            color: #ECEEF3;
            font-family: 'Inter', sans-serif;
            font-size: 22px;
            font-weight: 400;
            line-height: 179.523%;
        }
        .community-info-p {
            display: flex;
            align-items: start;
        }
        .community-info-img {
            width: 23px;
            margin-top: 10px;
            margin-right: 18.45px;
        }
        .community-info-list { margin-top: 20px; }
        .community-discord {
            position: absolute;
            top: 15px;
            left: -80px;
        }
        .com-left {
            animation-delay: 0.4s;
            animation-duration: 0.6s;
            animation-name: comLeftAppear;
            animation-fill-mode: both;
        }
        @keyframes comLeftAppear {
            0% { margin-left: -1200px; }
            100% { margin-left: 0px; }
        }
        .community-title {
            position: absolute;
            top: 65px;
            left: -102px;
            max-width: 1160px;
            width: 105%;
            height: 144px;
            transform: rotate(7deg);
            border-radius: 2px;
            border: 1px solid rgba(236, 238, 243, 0.20);
            background: rgba(236, 238, 243, 0.10);
            backdrop-filter: blur(43px);
            display: flex;
            justify-content: end;
            align-items: center;
            z-index: 1;
            box-sizing: border-box;
            animation-duration: 0.6s;
            animation-name: comTitleAppear;
            animation-fill-mode: both;
        }
        @keyframes comTitleAppear {
            0% { margin-left: -1200px; margin-top: -150px; }
            100% { margin-left: 0px; margin-top: 0px; }
        }
        .community-title p {
            color: #ECEEF3;
            font-family: 'Dela Gothic One', sans-serif;
            font-size: 60px;
            font-weight: 400;
            margin-bottom: 15px;
        }
        .community-title-img {
            width: 70px;
            height: 70px;
            opacity: 0.2;
            margin: 0 40px;
        }
        .community-img {
            position: absolute;
            top: 0;
            z-index: 2;
            height: 800px;
            transform: translate(-50%);
            left: 60%;
            animation-delay: 0.8s;
            animation-duration: 0.6s;
            animation-name: comPhoneAppear;
            animation-fill-mode: both;
        }
        @keyframes comPhoneAppear {
            0% { margin-left: -2000px; }
            100% { margin-left: 0px; }
        }
        .community-signals {
            position: absolute;
            bottom: 230px;
            width: 371.162px;
            height: 144px;
            transform: rotate(-10deg);
            border-radius: 2px;
            border: 1px solid rgba(236, 238, 243, 0.20);
            background: rgba(236, 238, 243, 0.10);
            backdrop-filter: blur(43.400001525878906px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 3;
            animation-delay: 0.8s;
            animation-duration: 0.6s;
            animation-name: comSignalsAppear;
            animation-fill-mode: both;
        }
        @keyframes comSignalsAppear {
            0% { right: -100%; }
            100% { right: 20%; }
        }
        .community-signals-img {
            transform: rotate(10deg);
            width: 55px;
            height: 55px;
            margin-right: 15px;
            opacity: 0.3;
        }
        .community-signals-text {
            color: rgba(236, 238, 243, 0.90);
            font-family: 'Open Sans', sans-serif;
            font-size: 47px;
            font-weight: 600;
        }
        .community-shadow {
            position: absolute;
            right: 825px;
            top: 220px;
            width: 200px;
            height: 700px;
            transform: rotate(-31.485deg);
            border-radius: 769.347px;
            background: #0D0D0D;
            filter: blur(57.5px);
        }
        .area-right {
            position: absolute;
            top: 0;
            right: 0;
            height: 100%;
            width: 10%;
            z-index: 6;
        }
        .black-bg {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: #0D0D0D;
            opacity: 0;
            z-index: 4;
            transition: 1s;
        }
        .community-u {
            position: absolute;
            overflow: hidden;
            right: -1920px;
            top: 0;
            width: 100%;
            height: 100%;
            z-index: 5;
            transition: 1s;
        }
        .u-bottom {
            position: absolute;
            right: 0;
            bottom: 0;
        }
        .u-white {
            position: absolute;
            transform: rotate(12deg);
            background-color: white;
            right: -200px;
            top: -200px;
            height: 2000px;
            width: 1700px;
        }
        .u-circle {
            position: absolute;
            top: -150px;
            right: 300px;
            width: 800px;
            height: 800px;
            border-radius: 50%;
            background: #6661F3;
        }
        .u-img {
            position: absolute;
            right: -800px;
            top: -250px;
            z-index: 5;
            width: 2564px;
            height: 1442px;
        }
        .u-community-title {
            position: absolute;
            bottom: 40px;
            right: -102px;
            width: 1158px;
            height: 144px;
            transform: rotate(7deg);
            border-radius: 2px;
            border: 1px solid rgba(236, 238, 243, 0.20);
            background: rgba(236, 238, 243, 0.10);
            backdrop-filter: blur(43.400001525878906px);
            display: flex;
            justify-content: start;
            align-items: center;
            z-index: 5;
        }
        .u-text1 {
            position: absolute;
            top: 50px;
            right: 400px;
            z-index: 5;
            transform: rotate(6.957deg);
        }
        .u-text2 {
            position: absolute;
            top: 350px;
            right: 800px;
            font-size: 30px;
            font-weight: 700;
            transform: rotate(7deg);
            z-index: 5;
            border: 1px solid #ECEEF3;
            padding: 0 25px 5px 25px;
        }
        .u-text3 {
            position: absolute;
            top: 425px;
            right: 890px;
            transform: rotate(7deg);
            display: flex;
            flex-direction: column;
            align-items: center;
            font-weight: 100;
            z-index: 5;
        }
        .u-sprite {
            position: absolute;
            top: 390px;
            right: 815px;
            width: 290px;
            height: 290px;
            transform: rotate(7deg);
            z-index: 5;
        }
        .u-arrow-l {
            position: absolute;
            top: 545px;
            right: 1100px;
            width: 32px;
            transform: rotate(16deg);
            z-index: 5;
        }
        .u-arrow-r {
            position: absolute;
            top: 580px;
            right: 840px;
            width: 32px;
            transform: rotate(12deg);
            z-index: 5;
        }
        .block-community-mobile {
            display: none;
            position: relative;
            margin-top: -350px;
        }
        .community-bg-mobile { position: absolute; bottom: 0; }
        .community-bg-mobile.bottom, .community-bg-mobile.white { width: 100%; left: 0; }
        .community-bg-mobile.m { width: 140%; right: -32%; }
        .community-bg-mobile.spr {
            left: 220px;
            bottom: 147px;
            width: 140px;
            height: 140px;
            transform: rotate(7.015deg);
        }
        .community-bg-mobile.ar { left: 195px; bottom: 187px; }
        .community-bg-mobile.ar1 { left: 325px; bottom: 207px; }
        .community-bg-mobile.text1 {
            transform: rotate(7deg);
            color: #ECEEF3;
            font-family: 'Open Sans', sans-serif;
            font-weight: 600;
        }
        .community-bg-mobile.text2 {
            transform: rotate(7deg);
            color: #F5F5FE;
            font-family: 'Open Sans', sans-serif;
            font-weight: 700;
            border: 1px solid #ECEEF3;
            padding: 0 8px 3px 8px;
        }
        .community-bg-mobile.text3 {
            transform: rotate(7deg);
            color: #ECEEF3;
            font-family: 'Open Sans', sans-serif;
            font-weight: 400;
            text-align: center;
        }
        .community-bg-mobile.text3 span { font-weight: 300; }
        .community-bg-mobile.text4 {
            width: 421px;
            transform: rotate(7deg);
            color: #F5F5FE;
            font-family: 'Open Sans', sans-serif;
            font-weight: 600;
        }

        @media (max-width: 1440px) {
            .block-community { height: auto; overflow: visible; }
            .community-bg, .black-bg, .community-u, .community-signals { display: none; }
            .block-community-mobile { display: block; }
            .communuty-bottom { flex-direction: column; align-items: flex-start; }
            .community-img { height: 516px; top: 200px; }
            .community-info { max-width: 496px; }
            .community-info-text { font-size: 14px; line-height: 179.523%; }
            .community-info-img { width: 14px; }
            .community-info-text-t { font-size: 23px; }
            .community-text-top { font-size: 22px; }
            .community-text-bottom { margin-top: -20px; font-size: 100px; }
            .community-title { height: 90px; max-width: 800px; }
            .community-title p { font-size: 40px; }
            .community-title-img { margin: 0 20px; width: 35px; height: 35px; }
            .community-bg-mobile.text1 { font-size: 60px; left: 30%; bottom: 50%; }
            .community-bg-mobile.text2 { left: 28%; bottom: 43%; font-size: 30px; }
            .community-bg-mobile.text3 { left: 28%; bottom: 36%; font-size: 30px; }
            .community-bg-mobile.text3 span { font-size: 28px; }
            .community-bg-mobile.text4 { width: 700px; left: 10%; bottom: 6%; }
            .community-bg-mobile.spr { width: 250px; height: 250px; left: 29%; bottom: 22%; }
            .community-bg-mobile.ar { width: 40px; left: 25%; bottom: 28%; }
            .community-bg-mobile.ar1 { width: 40px; left: 45%; bottom: 30%; }
        }
        @media (max-width: 1100px) {
            .block-community-mobile { margin-top: -200px; }
            .community-bg-mobile.text1 { font-size: 30px; left: 30%; bottom: 55%; }
            .community-bg-mobile.text2 { left: 28%; bottom: 48%; font-size: 20px; }
            .community-bg-mobile.text3 { left: 30%; bottom: 42%; font-size: 16px; }
            .community-bg-mobile.text3 span { font-size: 14px; }
            .community-bg-mobile.text4 { font-size: 16px; width: 490px; left: 10%; bottom: 3%; }
            .community-bg-mobile.spr { width: 200px; height: 200px; left: 29%; bottom: 22%; }
            .community-bg-mobile.ar { width: 40px; left: 25%; bottom: 28%; }
            .community-bg-mobile.ar1 { width: 40px; left: 45%; bottom: 30%; }
        }
        @media (max-width: 700px) {
            .block-community-mobile { margin-top: -100px; }
            .community-bg-mobile.text1 { font-size: 16px; left: 30%; bottom: 55%; }
            .community-bg-mobile.text2 { left: 28%; bottom: 48%; font-size: 10px; }
            .community-bg-mobile.text3 { left: 30%; bottom: 42%; font-size: 8px; }
            .community-bg-mobile.text3 span { font-size: 6px; }
            .community-bg-mobile.text4 { font-size: 10px; width: 400px; left: 10%; bottom: 2%; }
            .community-bg-mobile.spr { width: 100px; height: 100px; left: 29%; bottom: 22%; }
            .community-bg-mobile.ar { width: 20px; left: 22%; bottom: 28%; }
            .community-bg-mobile.ar1 { width: 20px; left: 48%; bottom: 30%; }
        }
        @media (max-width: 425px) {
            .block-community-mobile { margin-top: 0px; }
            .community-bg-mobile.text1 { font-size: 12px; left: 30%; bottom: 55%; }
            .community-bg-mobile.text2 { left: 28%; bottom: 48%; font-size: 8px; }
            .community-bg-mobile.text3 { left: 28%; bottom: 42%; font-size: 6px; }
            .community-bg-mobile.text3 span { font-size: 4px; }
            .community-bg-mobile.text4 { font-size: 7px; width: 250px; left: 3%; bottom: 1%; transform: rotate(0deg); }
            .community-bg-mobile.spr { width: 80px; height: 80px; left: 29%; bottom: 22%; }
            .community-bg-mobile.ar { width: 20px; left: 22%; bottom: 28%; }
            .community-bg-mobile.ar1 { width: 20px; left: 48%; bottom: 30%; }
        }
        @media (max-width: 768px) {
            .community-discord { width: 120px; left: -45px; }
            .community-title { height: 60px; max-width: 600px; }
            .community-title p { font-size: 30px; margin-bottom: 8px; }
            .community-title-img { margin: 0 20px; width: 35px; height: 35px; }
            .community-text-top { font-size: 15px; }
            .community-text-bottom { margin-top: -20px; font-size: 70px; }
            .community-img { height: 400px; top: 250px; left: 70%; }
        }
        @media (max-width: 600px) {
            .community-discord { width: 80px; left: -45px; }
            .community-title { height: 45px; max-width: 475px; width: 125%; }
            .community-title p { font-size: 20px; margin-bottom: 8px; }
            .community-title-img { margin: 0 10px; width: 20px; height: 20px; }
            .community-info { max-width: 496px; padding: 20px 35px; }
            .community-info-text-t { font-size: 18px; }
            .community-info-text { font-size: 12px; }
            .community-info-img { margin-top: 6px; width: 12px; }
        }
        @media (max-width: 500px) { .community-discord { top: 20px; left: -30px; } }
        @media (max-width: 350px) {
            .community-title { justify-content: center; padding-left: 110px; height: auto; }
            .community-img { display: none; }
        }

        /* ----- FOOTER ----- */
        .footer {
            height: 159px;
            position: relative;
        }
        .footer-white {
            position: absolute;
            width: 40%;
            height: 159px;
            background-color: #ECEEF3;
        }
        .footer-bg {
            position: absolute;
            left: calc((100% - 1920px) / 2);
            z-index: -1;
        }
        .footer-content {
            padding-top: 36px;
            box-sizing: border-box;
            display: flex;
            justify-content: space-between;
            position: relative;
        }
        .footer-list { margin-left: 25px; }
        .footer-list-title {
            color: rgba(13, 13, 13, 0.80);
            font-family: 'Open Sans', sans-serif;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .footer-list-row {
            display: flex;
            align-items: center;
        }
        .footer-list-a {
            color: rgba(13, 13, 13, 0.60);
            font-family: 'Open Sans', sans-serif;
            font-size: 14px;
            font-weight: 400;
            line-height: 155.023%;
            margin-left: 13px;
        }
        .footer-ip {
            color: rgba(13, 13, 13, 0.80);
            font-family: 'Open Sans', sans-serif;
            font-size: 14px;
            font-weight: 300;
            line-height: 155%;
            white-space: nowrap;
        }
        .footer-ip.m { display: none; }
        .footer-copyright {
            color: rgba(13, 13, 13, 0.20);
            font-family: 'Open Sans', sans-serif;
            font-size: 14px;
            font-weight: 300;
        }
        .footer-copyright.m {
            display: none;
            position: absolute;
            bottom: 0;
            transform: translate(-50%);
            left: 50%;
        }
        .footer-bg-fly {
            position: absolute;
            left: calc(50% - 11px);
            top: -5px;
            opacity: 0.3;
        }
        .footer-left {
            display: flex;
            justify-content: space-between;
            width: 50%;
            max-width: 600px;
        }

        @media (max-width: 1280px) { .footer-text.c { margin-left: 25px; } }
        @media (max-width: 768px) {
            .footer { margin-top: 17px; }
            .footer-right { display: none; }
            .footer-copyright.m, .footer-ip.m { display: block; }
            .footer-left { width: 100%; }
            .footer-list-a, .footer-ip, .footer-copyright { font-size: 12px; }
            .footer-list-title { font-size: 18px; }
            .footer-white { width: 30%; }
            .logo.f { width: 60px; height: 60px; }
        }
        @media (max-width: 400px) {
            .footer-list-a, .footer-ip, .footer-copyright { font-size: 10px; }
            .footer-list-title { font-size: 16px; }
            .logo.f { display: none; }
            .footer-list { margin: 0; }
        }

        /* ----- BUY / ORDER ----- */
        .buy {
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 99;
            padding: 30px;
            box-sizing: border-box;
            visibility: hidden;
        }
        .buy.active { visibility: visible; }
        .buy.active .black { opacity: 0.8; }
        .buy-content {
            background-color: white;
            height: 68%;
            width: 100%;
            position: fixed;
            bottom: -68%;
            left: 0;
            padding: 28px 100px;
            box-sizing: border-box;
            overflow: hidden;
            transition: 0.5s;
        }
        .buy.active .buy-content { bottom: 0%; }
        .buy-content .title { color: black; }
        .buy-left { max-width: 520px; }
        .buy-line {
            height: 1px;
            width: 520px;
            background: #D9D9D9;
            margin-bottom: 28px;
        }
        .buy-text {
            color: rgba(13, 13, 13, 0.60);
            font-size: 20px;
            margin: 0 5px;
        }
        .buy-text2 {
            color: black;
            font-size: 22px;
            margin: 0 5px;
        }
        .buy-button:hover .buy-button-line { margin-left: 35px; }
        .buy-button-text {
            font-family: 'Open Sans', sans-serif;
            font-size: 28px;
            font-weight: 600;
            background: linear-gradient(92deg, #5952F2 1.37%, #6F6BF3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 9px;
        }
        .buy-button-line {
            transition: 0.3s;
            height: 7px;
            background: linear-gradient(90deg, #5852F2 0%, #6F6BF3 100%);
        }
        .buy-q {
            color: rgba(13, 13, 13, 0.50);
            font-size: 12px;
            font-weight: 400;
            text-decoration: underline;
        }
        .ch { margin-left: 55px; }
        .checkbox {
            position: absolute;
            z-index: -1;
            opacity: 0;
        }
        .checkbox+label {
            display: inline-flex;
            position: relative;
            align-items: center;
            user-select: none;
            opacity: 0.42;
            transition: 0.3s;
        }
        .checkbox+label::before {
            content: '';
            display: inline-block;
            width: 13px;
            height: 13px;
            flex-shrink: 0;
            flex-grow: 0;
            border: 1px solid #C3C3C3;
        }
        .checkbox+label:hover { opacity: 1; }
        .flag {
            position: absolute;
            bottom: 2px;
            left: -2px;
            visibility: hidden;
        }
        .checkbox:checked+label { opacity: 1; }
        .checkbox:checked+label .flag { visibility: visible; }

        .checkbox2 {
            position: absolute;
            z-index: -1;
            opacity: 0;
        }
        .checkbox2+label {
            display: inline-flex;
            position: relative;
            align-items: center;
            user-select: none;
            opacity: 0.42;
            transition: 0.3s;
        }
        .checkbox2+label::before {
            content: '';
            display: inline-block;
            width: 13px;
            height: 13px;
            flex-shrink: 0;
            flex-grow: 0;
            border: 1px solid white;
        }
        .checkbox2+label:hover { opacity: 0.82; }
        .checkbox2:checked+label { opacity: 0.82; }
        .checkbox2:checked+label .flag { visibility: visible; }

        .buy-right {
            position: absolute;
            z-index: 1;
            right: 0;
            top: 0;
            width: 700px;
            height: 100%;
            background-color: #a9a7f2;
            display: flex;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
            filter: drop-shadow(0px 6px 6px rgba(0, 0, 0, 0.28));
        }
        .buy-right::before {
            content: "";
            position: absolute;
            width: 129px;
            height: 409px;
            transform: translate(-100%, -50%);
            left: 1px;
            top: 50%;
            background-image: url("img/buy-bg.png");
        }
        .buy-right-title {
            color: #FFF;
            font-family: 'Dela Gothic One', sans-serif;
            font-size: 25px;
        }
        .buy-inputs {
            display: flex;
            flex-direction: column;
            width: 435px;
        }
        .buy-inputs input {
            margin-top: 30px;
            padding: 16px 15px;
            background: transparent;
            border: 1px white solid;
            opacity: 0.42;
            color: white;
            font-family: 'Inter', sans-serif;
            font-size: 22px;
            font-weight: 300;
            transition: 0.3s;
        }
        .buy-inputs input::placeholder {
            color: white;
            font-family: 'Inter', sans-serif;
            font-size: 22px;
            font-weight: 300;
        }
        .buy-inputs input:focus { opacity: 0.82; outline: none; }
        .buy-inputs input:hover { opacity: 0.82; }
        .buy-ch-label p {
            color: white;
            opacity: 0.42;
            font-family: 'Inter', sans-serif;
            font-size: 22px;
            font-weight: 300;
            line-height: 148.523%;
        }
        .buy-ch-label p span { text-decoration: underline; }
        .buy-arrow {
            position: absolute;
            left: -90px;
            transform: translate(0, -50%);
            top: 50%;
        }
        .buy-right-btn {
            position: absolute;
            left: 0;
            top: 25%;
            width: 62px;
            height: 175px;
            transform: translate(-100%, -50%);
        }
        .buy-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            max-width: 600px;
        }
        .buy-button { width: fit-content; }
        .buy-course-chbx {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .buy-sum {
            display: flex;
            margin-top: 39px;
            margin-bottom: 28px;
            justify-content: space-between;
        }

        @media (max-width: 1550px) {
            .buy-right {
                background-image: url("img/buy-bg2.png");
                width: 574px;
                right: -540px;
                transition: 0.8s;
            }
            .buy-right::before {
                top: 25%;
                width: 62px;
                height: 222px;
                background-image: url("img/buy-bg2.png");
            }
            .buy-right.active { right: 0px; }
            .buy-arrow {
                width: 40px;
                height: 40px;
                left: -45px;
                top: 25%;
                rotate: 0deg;
                transition: 0.8s;
            }
            .buy-right.active .buy-arrow {
                margin-top: -40px;
                rotate: 180deg;
            }
        }
        @media (max-width: 768px) {
            .buy-content { padding: 16px 40px; }
            .buy .title { margin-top: 0; }
            .buy-line { display: none; }
            .ch { margin-left: 22px; }
        }
        @media (max-width: 650px) {
            .buy-right {
                width: calc(95% - 62px);
                right: calc(-95% + 62px + 10px);
            }
            .buy-right-content { width: 70%; }
            .buy-inputs { width: 100%; }
            .buy-buttons { flex-direction: column; margin-bottom: 10px; }
            .buy-course-chbx { flex-direction: column; max-width: 225px; margin-right: 35px; }
            .buy-course-chbx img { width: 100%; }
            .buy-sum { margin-top: 10px; margin-bottom: 10px; }
            .buy-content { padding: 16px 10px; padding-right: 40px; }
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
        .admin-form input, .admin-form textarea, .admin-form select {
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
        .admin-form button:hover { background: #218838; }
        .item-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            background: #fff;
        }
        .item-card img { max-width: 100px; border-radius: 5px; }
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
        .delete-btn:hover { background: #c82333; }
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
        .login-form h1 { text-align: center; margin-bottom: 30px; }
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
        .login-form button:hover { background: #0069d9; }
    </style>
</head>
<body>

<div class="wrapper">

    <!-- ===== HEADER ===== -->
    <div class="container header">
        <img src="img/coin.png" alt="logo" class="logo c">
        <div class="header-buttons">
            <div class="gl">
                <div class="dots">
                    <div class="dot"></div>
                    <div class="dot"></div>
                    <div class="dot"></div>
                </div>
                <button class="header-button" id="nav-btn">главная</button>
                <div class="header-nav" id="nav">
                    <a href="#main">главная</a>
                    <div class="h-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                    <a href="#who">кому подходит</a>
                    <div class="h-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                    <a href="#about">об авторе</a>
                    <div class="h-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                    <a href="#course">что входит в курс</a>
                    <div class="h-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                    <a href="#program">программа курса</a>
                    <div class="h-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                    <a href="#community">закрытое сообщество</a>
                    <div class="h-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                    <a href="#reviews">отзывы</a>
                    <div class="h-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                    <a href="#faq">FAQ</a>
                </div>
            </div>
            
            <!-- ===== АДМИНКА И ВХОД В ОТДЕЛЬНЫХ КНОПКАХ ===== -->
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                <a href="?page=admin" class="header-button c" style="color:red !important; font-weight:bold; border-color:red;">Админ</a>
                <a href="?logout=1" class="header-button c" style="color:red !important; font-weight:bold; border-color:red;">Выйти</a>
            <?php else: ?>
                <a href="?page=login" class="header-button c" style="color:#007bff !important; font-weight:bold; border-color:#007bff;">Войти</a>
            <?php endif; ?>
            
            <button class="header-button c">личный кабинет</button>
        </div>
    </div>

    <?php if ($page == 'home'): ?>

    <!-- ===== MAIN ===== -->
    <div class="block-main" id="main">
        <div class="container">
            <div class="main-left">
                <p class="main-text">Курс<br>по криптовалюте<br>с нуля</p>
                <div class="line" style="width: 330px; margin: 40px 0;"></div>
                <div style="display: flex;" class="main-img">
                    <img src="img/fly.svg" alt="fly" style="margin-right: 27px;">
                    <img src="img/message.svg" alt="msg" style="margin-right: 27px;">
                    <img src="img/money.svg" alt="card">
                </div>
            </div>
            <img src="img/zaka orig 2.png" alt="bg" class="main-bg">
        </div>
    </div>
    <div class="main-side">
        <img src="img/main-side.svg" alt="bg" class="side-bg">
        <img src="img/main-side-h.png" alt="bg" class="side-bg-h">
        <img src="img/side_down.svg" alt="down" class="side-arrow">
        <div class="side-content">
            <div>
                <p class="side-text-b">Почему мы?</p>
                <p class="side-text">Не устал проводить десятки часов в телеграм каналах, где вы будете искать информацию по крупицам? У нас только самая важная и всегда актуальная инфомация!</p>
            </div>
            <div>
                <p class="side-price">25 000 рублей</p>
                <div class="course-buy" style="margin-top: 0px;">
                    <div class="course-buy-left" id="buyOpen">
                        <p class="course-buy-text">КУПИТЬ КУРС</p>
                        <div class="course-buy-line"></div>
                    </div>
                    <img src="img/Arrow 1.svg" alt="arrow" class="course-buy-arrow">
                </div>
            </div>
        </div>
    </div>

    <!-- ===== TRANSITION ===== -->
    <div class="transition" id="who">
        <div class="transition-bg"></div>
        <div class="transition-content">
            <div class="transition-row u">
                <div class="transition-text top"><h4 style="float: right;">С нуля</h4></div>
                <div class="transition-line">
                    <div class="transition-line-l"></div>
                    <img src="img/icons.svg" alt="icons" class="transition-line-icons">
                    <div class="transition-line-l short"></div>
                </div>
            </div>
            <div class="transition-row">
                <div class="transition-arrow"></div>
                <div class="transition-text down"><h4>до специалиста</h4></div>
            </div>
        </div>
    </div>

    <!-- ===== PLUSES ===== -->
    <div class="block-pluses trigger" style="background: white;">
        <div class="pluses-list">
            <div class="pluses-cnt anim">
                <div class="pluses-item">
                    <div class="pluses-item-list">
                        <div class="pluses-item-row"><img src="img/Group 23.svg" alt="img"><p class="pluses-p">С самых азов</p></div>
                        <div class="pluses-item-row"><img src="img/Group 23.svg" alt="img"><p class="pluses-p">Простым языком</p></div>
                        <div class="pluses-item-row"><img src="img/Group 23.svg" alt="img"><p class="pluses-p">С расшифровкой терминов</p></div>
                        <div class="pluses-item-row"><img src="img/Group 23.svg" alt="img"><p class="pluses-p">Старт с нулевым капиталом</p></div>
                    </div>
                    <img src="img/Subtract.svg" alt="Subtract" class="pluses-img-sub">
                    <img src="img/icon.svg" alt="icon" class="pluses-img-icon">
                    <div class="pluses-item-tag"><p>новичкам</p></div>
                    <img src="img/Union_t.png" alt="asdas" class="pluses-img-un">
                </div>
            </div>
            <div class="pluses-cnt s anim">
                <div class="pluses-item">
                    <div class="pluses-item-list">
                        <div class="pluses-item-row"><img src="img/Group 23.svg" alt="img"><p class="pluses-p">Стратегии заработка в долгий срок</p></div>
                        <div class="pluses-item-row"><img src="img/Group 23.svg" alt="img"><p class="pluses-p">Трейдинг и скальпинг</p></div>
                        <div class="pluses-item-row"><img src="img/Group 23.svg" alt="img"><p class="pluses-p">Токенсейлы, ноды</p></div>
                        <div class="pluses-item-row"><img src="img/Group 23.svg" alt="img"><p class="pluses-p">Поиск и анализ перспективных монет</p></div>
                    </div>
                    <img src="img/Subtract.svg" alt="Subtract" class="pluses-img-sub">
                    <img src="img/icon1.svg" alt="icon" class="pluses-img-icon">
                    <div class="pluses-item-tag"><p>инвесторам</p></div>
                    <img src="img/Union_t.png" alt="asdas" class="pluses-img-un">
                </div>
            </div>
            <div class="pluses-cnt t anim">
                <div class="pluses-item">
                    <div class="pluses-item-list">
                        <div class="pluses-item-row"><img src="img/Group 23.svg" alt="img"><p class="pluses-p">Интенсив по блокчейн и криптовалюте</p></div>
                        <div class="pluses-item-row"><img src="img/Group 23.svg" alt="img"><p class="pluses-p">Перспективы для вашего бизнеса</p></div>
                        <div class="pluses-item-row"><img src="img/Group 23.svg" alt="img"><p class="pluses-p">Расширение кругозора</p></div>
                    </div>
                    <img src="img/Subtract.svg" alt="Subtract" class="pluses-img-sub">
                    <img src="img/icon2.svg" alt="icon" class="pluses-img-icon">
                    <div class="pluses-item-tag"><p>экспертам</p></div>
                    <img src="img/Union_t.png" alt="asdas" class="pluses-img-un">
                </div>
            </div>
        </div>
    </div>

    <!-- ===== ABOUT ===== -->
    <div class="block-about" id="about" style="position: relative;">
        <div class="container" style="position: relative;">
            <h4 class="title">Об авторе проекта</h4>
            <div class="line" style="width: 331px; margin-top: 9px; margin-bottom: 30px;"></div>
            <div class="about-imgt">
                <img src="img/bg.png" alt="bg" class="about-img">
                <div class="about-text">
                    <p><span>1,4 млн подписчиков</span></p>
                    <p>Блогер миллионник</p>
                    <br>
                    <p>Автор Бестселлера</p>
                    <p>"Переходи в online"</p>
                    <br>
                    <p>Криптоинвестор, Специалист по IT,</p>
                    <p>Хороший человек, Зоозащитник</p>
                </div>
            </div>
            <div class="about-bubble" id="bubble">
                <p class="bubble-text">В <span>криптовалюте</span> очень легко заработать деньги,<br>но гораздо легче их там потерять.<br><br>Без знаний, на авось... в крипту?<br>Это финансовый суицид.</p>
            </div>
        </div>
    </div>

    <!-- ===== COURSE ===== -->
    <div class="block-course" id="course">
        <img src="img/dog.svg" alt="dog" class="course-bg-img">
        <div class="course-bg"></div>
        <div class="container">
            <h4 class="title">Что входит в курс</h4>
            <div class="course-list">
                <div class="course-column">
                    <div class="course-item">
                        <img src="img/icon3.svg" alt="icon" class="course-icon">
                        <div class="course-tag"><p>Видеоуроки</p></div>
                    </div>
                    <div class="course-item">
                        <img src="img/icon4.svg" alt="icon" class="course-icon">
                        <div class="course-tag"><p>Прямые эфиры</p></div>
                    </div>
                </div>
                <div class="course-column">
                    <div class="course-item">
                        <img src="img/icon7.svg" alt="icon" class="course-icon sm">
                        <div class="course-tag"><p>Теория, <br>практика и трейдинг</p></div>
                    </div>
                    <div class="course-item">
                        <img src="img/icon5.svg" alt="icon" class="course-icon">
                        <div class="course-tag"><p>Конспекты, <br>тесты и чек-листы</p></div>
                    </div>
                </div>
                <div class="course-column b">
                    <div class="course-item b">
                        <img src="img/icon6.svg" alt="icon" class="course-icon b">
                        <div class="course-tag"><p>Закрытый чат <br>криптоинвесторов</p></div>
                    </div>
                </div>
            </div>
            <div class="more">
                <img src="img/Arrow 6.svg" alt="arrow">
                <p class="more-p">узнать больше</p>
            </div>
        </div>
    </div>

    <!-- ===== PROGRAM ===== -->
    <div class="block-program" id="program" style="position: relative;">
        <div class="container">
            <div class="title">Программа курса</div>
            <p class="program-desc">Видеокурс разбит на 9 модулей, в каждом по десятку видеоуроков. После прохождения каждого модуля проводится прямой эфир, где закрываются накопившиеся вопросы. Плюс, разумеется, поддержка в закрытом сообществе.</p>
        </div>
        <div class="program-module-list">
            <?php
            $modules = [
                ['num' => 'Модуль 1', 'name' => 'Введение в криптовалюту', 'steps' => ['Теория деградации денег', 'Технология Blockchain', 'Структура криптовалюты', 'Теория майнинга', 'Приобретение и хранение крипты', 'Риски в криптовалюте']],
                ['num' => 'Модуль 2', 'name' => 'Криптовалютные биржи и Metamask', 'steps' => ['Теория деградации денег', 'Технология Blockchain', 'Структура криптовалюты', 'Теория майнинга', 'Приобретение и хранение крипты', 'Риски в криптовалюте']],
                ['num' => 'Модуль 3', 'name' => 'Понимание рынка. Долгосрочные инвестиции', 'steps' => ['Теория деградации денег', 'Технология Blockchain', 'Структура криптовалюты', 'Теория майнинга', 'Приобретение и хранение крипты', 'Риски в криптовалюте']],
                ['num' => 'Модуль 4', 'name' => 'Методы поиска и анализа монет', 'steps' => ['Теория деградации денег', 'Технология Blockchain', 'Структура криптовалюты', 'Теория майнинга', 'Приобретение и хранение крипты', 'Риски в криптовалюте']],
                ['num' => 'Модуль 5', 'name' => 'Методы заработка. Автоматизация', 'steps' => ['Теория деградации денег', 'Технология Blockchain', 'Структура криптовалюты', 'Теория майнинга', 'Приобретение и хранение крипты', 'Риски в криптовалюте']],
                ['num' => 'Модуль 6', 'name' => 'Токенсейлы. Ноды. Заработок без вложений', 'steps' => ['Теория деградации денег', 'Технология Blockchain', 'Структура криптовалюты', 'Теория майнинга', 'Приобретение и хранение крипты', 'Риски в криптовалюте']],
                ['num' => 'Модуль 7', 'name' => 'Стейкинг и децентрализованные биржи', 'steps' => ['Теория деградации денег', 'Технология Blockchain', 'Структура криптовалюты', 'Теория майнинга', 'Приобретение и хранение крипты', 'Риски в криптовалюте']],
                ['num' => 'Модуль 8', 'name' => 'Фьючерсная торговля', 'steps' => ['Теория деградации денег', 'Технология Blockchain', 'Структура криптовалюты', 'Теория майнинга', 'Приобретение и хранение крипты', 'Риски в криптовалюте']],
                ['num' => 'Модуль 9', 'name' => 'NFT, Криптобезопасность, Создание токенов', 'steps' => ['Теория деградации денег', 'Технология Blockchain', 'Структура криптовалюты', 'Теория майнинга', 'Приобретение и хранение крипты', 'Риски в криптовалюте']]
            ];
            foreach ($modules as $mod): ?>
                <div class="program-module">
                    <div class="container" style="position: relative;">
                        <div class="program-module-img"></div>
                        <p class="program-module-num"><?php echo $mod['num']; ?></p>
                        <p class="program-module-name"><?php echo $mod['name']; ?></p>
                        <div class="program-module-steps">
                            <div class="program-module-column">
                                <p class="p-mod-p">1. <?php echo $mod['steps'][0]; ?></p>
                                <p class="p-mod-p">2. <?php echo $mod['steps'][1]; ?></p>
                                <p class="p-mod-p">3. <?php echo $mod['steps'][2]; ?></p>
                            </div>
                            <div class="program-module-column">
                                <p class="p-mod-p">4. <?php echo $mod['steps'][3]; ?></p>
                                <p class="p-mod-p">5. <?php echo $mod['steps'][4]; ?></p>
                                <p class="p-mod-p">6. <?php echo $mod['steps'][5]; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="container">
            <div class="course-buy" style="margin-top: 120px;">
                <div class="course-buy-left" id="buyOpen2">
                    <p class="course-buy-text">КУПИТЬ КУРС</p>
                    <div class="course-buy-line"></div>
                </div>
                <img src="img/Arrow 1.svg" alt="arrow" class="course-buy-arrow">
            </div>
            <div class="program-info">
                <div class="program-info-block">
                    <p class="program-info-text1">Видеоуроков</p>
                    <div class="program-info-down">
                        <p class="program-info-text2">>70</p>
                        <p class="program-info-text3">Роликов</p>
                    </div>
                </div>
                <div class="program-info-block">
                    <p class="program-info-text1">Продолжительность</p>
                    <div class="program-info-down">
                        <p class="program-info-text2">>11</p>
                        <p class="program-info-text3">Часов</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="program-side">
            <img src="img/barcode.png" alt="barcode" class="barcode">
            <p style="opacity: 0.2; margin-bottom: 35px;">cryptocurency</p>
            <div style="width: 2px; height: 50px; background-color: white; opacity: 0.2;"></div>
            <img src="img/fly.svg" alt="fly" class="program-img">
            <div style="width: 2px; height: 450px; background-color: white; opacity: 0.2;"></div>
            <img src="img/message.svg" alt="msg" class="program-img">
            <div style="width: 2px; height: 450px; background-color: white; opacity: 0.2;"></div>
            <img src="img/money.svg" alt="card" class="program-img">
            <div style="width: 2px; height: 50px; background-color: white; opacity: 0.2;"></div>
        </div>
        <img src="img/side_down.svg" alt="arrow" class="program-arrow">
    </div>

    <!-- ===== COMMUNITY ===== -->
    <div class="block-community trigger2" id="community">
        <div class="community-bg">
            <div class="community-bg-white"></div>
            <img src="img/cloud.svg" alt="cloud" class="community-bg-img">
            <img src="img/Arrow 7.svg" alt="arrow" class="community-bg-arrow1">
            <img src="img/Arrow 8.svg" alt="arrow" class="community-bg-arrow2">
            <p class="community-bg-text">Собственная<br>метавселенная</p>
        </div>
        <div class="community-title anim2">
            <p>Закрытое сообщество</p>
            <img src="img/message.svg" alt="message" class="community-title-img">
        </div>
        <div class="container" style="position: relative;">
            <div class="com-left anim2">
                <div class="community-info">
                    <img src="img/discord.png" alt="discord" class="community-discord">
                    <div class="community-info-content">
                        <p class="community-info-text-t">Наш закрытый канал<br>в Discord.</p>
                        <div class="community-info-list">
                            <div class="community-info-p"><img src="img/Group 23.svg" alt="img" class="community-info-img"><p class="community-info-text">Общение, поддержка, сигналы</p></div>
                            <div class="community-info-p"><img src="img/Group 23.svg" alt="img" class="community-info-img"><p class="community-info-text">Бот публикующий только сигналы автора</p></div>
                            <div class="community-info-p"><img src="img/Group 23.svg" alt="img" class="community-info-img"><p class="community-info-text">Ежедневная сводка сообщества ChubaNEWS</p></div>
                            <div class="community-info-p"><img src="img/Group 23.svg" alt="img" class="community-info-img"><p class="community-info-text">Регулярные обзоры рынка</p></div>
                            <div class="community-info-p"><img src="img/Group 23.svg" alt="img" class="community-info-img"><p class="community-info-text">Инфопоток - вычленяем важное из инфошума</p></div>
                            <div class="community-info-p"><img src="img/Group 23.svg" alt="img" class="community-info-img"><p class="community-info-text">Голосовые чаты, стримы, вебинары</p></div>
                            <div class="community-info-p"><img src="img/Group 23.svg" alt="img" class="community-info-img"><p class="community-info-text">Удобное распределение информации по комнатам</p></div>
                        </div>
                    </div>
                </div>
                <div class="communuty-bottom">
                    <div class="community-text"><p class="community-text-top">Нас уже более</p><p class="community-text-bottom">2700</p></div>
                    <div class="community-text"><p class="community-text-top">Куча контента<br>каждый день</p><p class="community-text-bottom">New</p></div>
                </div>
            </div>
            <img src="img/phones.png" alt="phones" class="community-img anim2">
            <div class="community-signals anim2">
                <img src="img/up.svg" alt="up" class="community-signals-img">
                <p class="community-signals-text">Сигналы</p>
            </div>
        </div>
        <div class="black-bg" id="b_bg"></div>
        <div class="community-u" id="u">
            <div class="u-white"></div>
            <img src="img/com-bottom.svg" alt="bottom" class="u-bottom">
            <img src="img/community_bg.png" alt="bg" class="u-img">
            <div class="u-text1"><p style="font-size: 50px;">Собственная</p><p style="font-size: 95px;">Метавселенная</p></div>
            <div class="u-community-title">
                <img src="img/message.svg" alt="message" class="community-title-img">
                <p class="title" style="margin-bottom: 15px;">Закрытое сообщество</p>
            </div>
            <p class="u-text2">посмотреть карту</p>
            <div class="u-text3"><p style="font-size: 20px;">создай свой аватар</p><p style="font-size: 14px;">1/999</p></div>
            <img src="img/hero_sprite.png" alt="bg" class="u-sprite">
            <img src="img/Arrow_c.svg" alt="bg" class="u-arrow-l">
            <img src="img/Arrow_c1.svg" alt="bg" class="u-arrow-r">
        </div>
        <div class="area-right" id="a_r"></div>
    </div>
    <div class="block-community-mobile">
        <img src="img/com_bg_m.svg" alt="bg" class="community-bg-mobile white" style="z-index: 0; position: relative;">
        <img src="img/community_bg.png" alt="bg" class="community-bg-mobile m" style="z-index: 1;">
        <img src="img/hero_sprite.png" alt="bg" class="community-bg-mobile spr" style="z-index: 2;">
        <img src="img/Arrow_c.svg" alt="bg" class="community-bg-mobile ar" style="z-index: 2;">
        <img src="img/Arrow_c1.svg" alt="bg" class="community-bg-mobile ar1" style="z-index: 2;">
        <p class="community-bg-mobile text1" style="z-index: 3;">Собственная<br>Метавселенная</p>
        <p class="community-bg-mobile text2" style="z-index: 3;">посмотреть карту</p>
        <p class="community-bg-mobile text3" style="z-index: 3;">создай свой аватар<br><span>1/999</span></p>
        <p class="community-bg-mobile text4" style="z-index: 3;">Цифровое пространство доступное только для участников проекта. Регулярно собираемся там с другими криптоинвесторами и изучайем все возможности с автором курса на прямых трансляциях</p>
        <img src="img/com_bg_m1.svg" alt="bg" class="community-bg-mobile bottom" style="z-index: 2;">
    </div>

    <!-- ===== REVIEWS ===== -->
    <div class="block-reviews" id="reviews">
        <div class="container"><p class="title">Отзывы</p></div>
        <div class="review-scroll">
            <div class="review-row r">
                <?php for ($i = 0; $i < 14; $i++): ?>
                <div class="review-item">
                    <div class="review-top">
                        <img src="img/user.png" alt="user_img" class="review-img">
                        <p class="review-name">Viacheslav <br>Morozov</p>
                    </div>
                    <p class="review-text">Сказать что я просто доволен - это ничего не сказать!</p>
                    <p class="review-more">подробнее</p>
                </div>
                <?php endfor; ?>
            </div>
            <div class="review-row l">
                <?php for ($i = 0; $i < 14; $i++): ?>
                <div class="review-item">
                    <div class="review-top">
                        <img src="img/user.png" alt="user_img" class="review-img">
                        <p class="review-name">Viacheslav <br>Morozov</p>
                    </div>
                    <p class="review-text">Сказать что я просто доволен - это ничего не сказать!</p>
                    <p class="review-more">подробнее</p>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        <div class="container">
            <p class="review-bottom-text1">Уже 500+</p>
            <p class="review-bottom-text2">отзывов</p>
        </div>
    </div>

    <!-- ===== FAQ ===== -->
    <div class="block-faq" id="faq">
        <div class="container">
            <p class="title">Ещё остались вопросы?</p>
            <div class="faq-f">
                <img src="img/Subtract_faq.svg" alt="faq-rect" class="faq-f-img">
                <p class="faq-f-text">FAQ</p>
                <p class="faq-f-q">?</p>
            </div>
            <div class="faq-questions">
                <?php $first = true; foreach ($faq_list as $faq): ?>
                <div class="faq-line">
                    <div class="faq-line-l"></div>
                    <img src="img/fly.svg" alt="airplane" class="faq-line-img">
                    <div class="faq-line-l"></div>
                </div>
                <div class="faq-questions-q <?php echo $first ? 'active' : ''; ?>">
                    <div class="faq-questions-q-title">
                        <img src="img/down_small.svg" alt="down" class="faq-questions-q-img">
                        <p class="faq-questions-q-text"><?php echo htmlspecialchars($faq['question']); ?></p>
                    </div>
                    <p class="faq-questions-q-desc"><?php echo htmlspecialchars($faq['answer']); ?></p>
                </div>
                <?php $first = false; endforeach; ?>
                <div class="faq-line">
                    <div class="faq-line-l"></div>
                    <img src="img/fly.svg" alt="airplane" class="faq-line-img">
                    <div class="faq-line-l"></div>
                </div>
            </div>
            <div class="course-buy" style="margin-top: 26px; margin-bottom: 37px;">
                <div class="course-buy-left" id="buyOpen3">
                    <p class="course-buy-text">КУПИТЬ КУРС</p>
                    <div class="course-buy-line"></div>
                </div>
                <img src="img/Arrow 1.svg" alt="arrow" class="course-buy-arrow">
            </div>
        </div>
    </div>

    <?php endif; ?>

    <!-- ===== LOGIN PAGE ===== -->
    <?php if ($page == 'login'): ?>
    <div style="padding: 100px 0; background: #0D0D0D; min-height: 80vh; display: flex; align-items: center; justify-content: center;">
        <div class="login-form">
            <h1 style="color: #0D0D0D;">🔑 Авторизация</h1>
            <?php if (isset($error)): ?>
                <div class="msg-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="login" placeholder="Логин" required>
                <input type="password" name="password" placeholder="Пароль" required>
                <button type="submit" name="login_btn">Войти</button>
            </form>
            <p style="text-align:center; margin-top:20px; color:#333;">
                <strong>Логин: admin, Пароль: admin</strong>
            </p>
            <p style="text-align:center; margin-top:10px;">
                <a href="?page=home" style="color:#007bff; font-size:16px;">На главную</a>
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- ===== ADMIN PANEL ===== -->
    <?php if ($page == 'admin'): ?>
    <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin'): ?>
        <div style="padding:100px 0; text-align:center; background:#0D0D0D; min-height:80vh; display:flex; align-items:center; justify-content:center; flex-direction:column;">
            <h1 style="color:#ECEEF3;">⛔ Доступ запрещен</h1>
            <p style="color:#ECEEF3; font-size:20px; margin-top:20px;">У вас нет прав доступа! <a href="?page=login" style="color:#007bff;">Войти</a></p>
        </div>
    <?php else: ?>
        <div style="background:#f0f0f0; padding: 2em 0; min-height:100vh;">
            <div class="container">
                <div class="admin-panel">
                    <h1 style="text-align:center; color:#333;">⚡ Админ-панель</h1>
                    <p style="text-align:center; font-size:1.2em; color:#333;">Добро пожаловать, <strong><?php echo $_SESSION['user_login']; ?></strong>! 👋</p>
                    <p style="text-align:center; margin-bottom:30px;">
                        <a href="?page=home" style="color:#007bff;">← На главную</a>
                    </p>
                    
                    <h2 style="color:#333;">📋 Заказы</h2>
                    <?php
                    $orders = [];
                    $result_orders = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC");
                    if ($result_orders) {
                        while ($row = mysqli_fetch_assoc($result_orders)) $orders[] = $row;
                    }
                    ?>
                    <?php if (empty($orders)): ?>
                        <p style="color:#666;">Нет заказов</p>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="item-card">
                                <h4><?php echo htmlspecialchars($order['name']); ?></h4>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                                <p><strong>Телефон:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                                <p><strong>Курс:</strong> <?php echo htmlspecialchars($order['course']); ?></p>
                                <p><strong>Цена:</strong> <?php echo htmlspecialchars($order['price']); ?> руб.</p>
                                <small style="color:#999;">Дата: <?php echo $order['created_at']; ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- ===== FOOTER ===== -->
    <div class="footer">
        <div class="footer-white" style="left: 0;"></div>
        <div class="footer-white" style="right: 0;"></div>
        <img src="img/footer-bg.svg" alt="footer-bg" class="footer-bg">
        <img src="img/fly.svg" alt="fly" class="footer-bg-fly">
        <div class="container">
            <div class="footer-content">
                <div class="footer-left">
                    <img src="img/coin.png" alt="logo" class="logo f" style="margin-top: 10px;">
                    <div class="footer-list">
                        <p class="footer-list-title">Соглашение</p>
                        <div class="footer-list-row"><img src="img/footer_icon1.svg" alt="icon"><a href="#" class="footer-list-a">Правила</a></div>
                        <div class="footer-list-row"><img src="img/footer_icon1.svg" alt="icon"><a href="#" class="footer-list-a">Публичная оферта</a></div>
                        <div class="footer-list-row"><img src="img/footer_icon1.svg" alt="icon"><a href="#" class="footer-list-a">Политика конфидециальности</a></div>
                    </div>
                    <div class="footer-list">
                        <p class="footer-list-title">Контакты</p>
                        <div class="footer-list-row"><img src="img/inst.png" alt="icon" style="width:12px;height:12px;opacity:0.5;"><a href="#" class="footer-list-a">@gleb_kornilov</a></div>
                        <p class="footer-ip m">ИП Корнилов Г. Л.<br>ИНН 772850346280</p>
                    </div>
                </div>
                <div class="footer-right">
                    <p class="footer-ip">ИП Корнилов Г. Л.<br>ИНН 772850346280</p>
                    <p class="footer-copyright">© 2022 Глеб Корнилов</p>
                </div>
            </div>
            <p class="footer-copyright m">© 2022 Глеб Корнилов</p>
        </div>
    </div>

    <!-- ===== POPUP REVIEW ===== -->
    <div class="popup">
        <div class="black"></div>
        <div class="popup-content">
            <div class="popup-wp">
                <div class="popup-body">
                    <div class="quote"><p>"</p></div>
                    <div class="review-top">
                        <img src="img/user.png" alt="user_img" class="review-img">
                        <p class="review-name">Viacheslav <br>Morozov</p>
                    </div>
                    <p class="review-text">Сказать что я просто доволен - это ничего не сказать! Попав в эту шикарную крипто шайку во главе с нашим Чубабатей, моя жизнь кардинально изменилась и наконец то приняла четкий вектор развития. Я открыл для себя много очень важных инструментов которые уже приносят хорошие деньги. А когда я первый раз оплатил чашку кофе с моей крипто карты я понял, вот она свобода и вот оно будущее:). Благодарю тебя Глеб, ты очень хороший веселый человек и поэтому у нас тут так круто и увлекательно! А еще мы теперь зарабатываем любимым делом.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== BUY/ORDER ===== -->
    <div class="buy" id="buyModal">
        <div class="black"></div>
        <div class="buy-content">
            <div class="buy-left">
                <p class="title" style="margin-bottom: 19px;">Ваш заказ</p>
                <div class="buy-line"></div>
                
                <?php if (isset($order_msg)): ?>
                    <div class="msg-success"><?php echo $order_msg; ?></div>
                <?php endif; ?>
                
                <form method="POST" id="orderForm">
                    <div class="buy-course-chbx">
                        <img src="img/fromzero.png" alt="img">
                        <div class="ch">
                            <input type="checkbox" id="checkbox" class="checkbox" checked>
                            <label for="checkbox"><img src="img/flag.svg" alt="flag" class="flag"></label>
                        </div>
                    </div>
                    <p class="buy-text">Курс по криптовалюте x1</p>
                    <div style="display: flex; margin-top: 8px; justify-content: space-between;">
                        <p class="buy-text">Доступ к закрытому чату на 2 мес.</p>
                        <p class="buy-text" id="displayPrice">25 000р</p>
                    </div>
                    <div class="buy-sum">
                        <p class="buy-text2">Сумма</p>
                        <p class="buy-text2" id="displayTotal">25 000р</p>
                    </div>
                    
                    <!-- ===== СКРЫТЫЕ ПОЛЯ ДЛЯ ОТПРАВКИ ===== -->
                    <input type="hidden" name="order_course" value="Курс по криптовалюте x1">
                    <input type="hidden" name="order_price" value="25000">
                    <input type="hidden" name="order_name" id="orderName" required>
                    <input type="hidden" name="order_email" id="orderEmail" required>
                    <input type="hidden" name="order_phone" id="orderPhone" required>
                    
                    <div class="buy-buttons">
                        <button type="submit" name="order_submit" class="buy-button" style="background:none;border:none;margin-right:15px;cursor:pointer;">
                            <p class="buy-button-text">Оплатить</p>
                            <div class="buy-button-line"></div>
                        </button>
                        <div class="buy-button">
                            <p class="buy-button-text">Купить в рассрочку</p>
                            <div class="buy-button-line"></div>
                        </div>
                    </div>
                    <div class="buy-line"></div>
                    <p class="buy-q">Как оплатить из Украины, Европы и иных стран</p>
                    <p class="buy-q">Оплатить криптовалютой</p>
                </form>
            </div>
            
            <div class="buy-right">
                <div class="buy-right-content">
                    <p class="buy-right-title">Детали покупки</p>
                    <div class="buy-inputs">
                        <input type="text" id="buyName" placeholder="Имя" oninput="document.getElementById('orderName').value=this.value">
                        <input type="email" id="buyEmail" placeholder="E-mail" oninput="document.getElementById('orderEmail').value=this.value">
                        <input type="tel" id="buyPhone" placeholder="Телефон" oninput="document.getElementById('orderPhone').value=this.value">
                    </div>
                    <div style="display: flex; align-items: flex-start; margin-top: 20px;">
                        <input type="checkbox" id="checkbox2" class="checkbox2" checked>
                        <label for="checkbox2"><img src="img/flag.svg" alt="flag" class="flag"></label>
                        <div class="buy-ch-label" style="margin-left: 10px; margin-top: -8px;">
                            <p><span>Правила</span> понятны.</p>
                            <p><span>Публичную оферту</span> подтверждаю</p>
                        </div>
                    </div>
                    <button type="button" onclick="submitOrder()" style="margin-top:20px;padding:12px 40px;background:#5952F2;color:white;border:none;border-radius:5px;font-size:18px;cursor:pointer;">
                        Оформить заказ
                    </button>
                </div>
                <img src="img/buy-arrow.svg" alt="arrow" class="buy-arrow">
                <div class="buy-right-btn" id="buybtn"></div>
            </div>
        </div>
    </div>

</div>

<script>
    // ===== NAV =====
    document.getElementById("nav-btn").addEventListener("click", function() {
        document.getElementById("nav").classList.toggle("active");
    });
    document.querySelectorAll("#nav a").forEach(function(el) {
        el.addEventListener("click", function() {
            document.getElementById("nav").classList.remove("active");
        });
    });
    document.addEventListener("mouseup", function(e) {
        var div = document.getElementById("nav");
        if (!div.contains(e.target) && e.target !== document.getElementById("nav-btn")) {
            div.classList.remove("active");
        }
    });

    // ===== PROGRAM MODULES =====
    document.querySelectorAll(".program-module").forEach(function(el) {
        el.addEventListener("click", function() {
            document.querySelectorAll(".program-module.active").forEach(function(active) {
                active.classList.remove("active");
            });
            this.classList.add("active");
        });
    });

    // ===== FAQ =====
    document.querySelectorAll(".faq-questions-q").forEach(function(el) {
        el.addEventListener("click", function() {
            this.classList.toggle("active");
        });
    });

    // ===== COMMUNITY HOVER =====
    document.getElementById("a_r").addEventListener("mouseenter", function() {
        document.getElementById("u").style.right = "0";
        document.getElementById("b_bg").style.opacity = "0.7";
    });
    document.getElementById("a_r").addEventListener("mouseleave", function() {
        document.getElementById("u").style.right = "-1920px";
        document.getElementById("b_bg").style.opacity = "0";
    });

    // ===== REVIEW POPUP =====
    document.querySelectorAll(".review-item").forEach(function(el) {
        el.addEventListener("click", function() {
            document.querySelector(".popup").classList.add("active");
        });
    });
    document.addEventListener("mouseup", function(e) {
        var div = document.querySelector(".popup-body");
        if (div && !div.contains(e.target)) {
            document.querySelector(".popup").classList.remove("active");
        }
    });

    // ===== BUY MODAL =====
    function openBuy() {
        document.getElementById("buyModal").classList.add("active");
        document.querySelector(".buy-right").classList.add("active");
    }
    document.querySelectorAll("#buyOpen, #buyOpen2, #buyOpen3").forEach(function(el) {
        el.addEventListener("click", openBuy);
    });
    document.getElementById("buybtn").addEventListener("click", function() {
        document.querySelector(".buy-right").classList.toggle("active");
    });
    document.addEventListener("mouseup", function(e) {
        var div = document.querySelector(".buy-content");
        if (div && !div.contains(e.target)) {
            document.getElementById("buyModal").classList.remove("active");
            document.querySelector(".buy-right").classList.remove("active");
        }
    });

    // ===== ОТПРАВКА ФОРМЫ =====
    function submitOrder() {
        var name = document.getElementById('buyName').value;
        var email = document.getElementById('buyEmail').value;
        var phone = document.getElementById('buyPhone').value;
        
        if (name && email && phone) {
            document.getElementById('orderName').value = name;
            document.getElementById('orderEmail').value = email;
            document.getElementById('orderPhone').value = phone;
            document.getElementById('orderForm').submit();
        } else {
            alert('Пожалуйста, заполните все поля!');
        }
    }

    // ===== BUBBLE TRACKING =====
    let bubble = document.getElementById("bubble");
    if (bubble) {
        let bubbleX = bubble.offsetLeft + bubble.offsetWidth / 2;
        let bubbleY = bubble.offsetTop + bubble.offsetHeight / 2;
        document.addEventListener("mousemove", function(e) {
            let hX = (e.pageX - bubbleX) / 400;
            let hY = (e.pageY - bubbleY) / 400;
            bubble.style.transform = "translate(" + hX + "%," + hY + "%)";
        });
    }

    // ===== ANIMATION =====
    let bottomScreen = 0;
    const windowInnerHeight = window.innerHeight;
    let triggerOffTop = document.querySelector(".trigger") ? document.querySelector(".trigger").offsetTop + document.querySelector(".trigger").offsetHeight / 3 : 0;
    let triggerOffBottom = document.querySelector(".trigger") ? document.querySelector(".trigger").offsetTop + document.querySelector(".trigger").offsetHeight : 0;
    let trigger2OffTop = document.querySelector(".trigger2") ? document.querySelector(".trigger2").offsetTop + document.querySelector(".trigger2").offsetHeight / 3 : 0;
    let trigger2OffBottom = document.querySelector(".trigger2") ? document.querySelector(".trigger2").offsetTop + document.querySelector(".trigger2").offsetHeight : 0;

    function checkAnim() {
        bottomScreen = window.scrollY + windowInnerHeight;
        if (triggerOffTop < bottomScreen && triggerOffBottom > window.scrollY) {
            document.querySelectorAll(".anim").forEach(function(el) { el.classList.add("play"); });
        }
        if (trigger2OffTop < bottomScreen && trigger2OffBottom > window.scrollY) {
            document.querySelectorAll(".anim2").forEach(function(el) { el.classList.add("play"); });
        }
    }
    window.addEventListener("load", checkAnim);
    window.addEventListener("scroll", checkAnim);
</script>

</body>
</html>
<?php mysqli_close($conn); ?>


-- ============================================================
-- БАЗА ДАННЫХ ДЛЯ САЙТА "КУРС ПО КРИПТОВАЛЮТЕ"
-- ============================================================

-- 1. СОЗДАНИЕ БАЗЫ ДАННЫХ (ЕСЛИ НЕ СУЩЕСТВУЕТ)
CREATE DATABASE IF NOT EXISTS crypto_course_db;
USE crypto_course_db;

-- ============================================================
-- 2. УДАЛЕНИЕ СТАРЫХ ТАБЛИЦ (ЕСЛИ НУЖНО ПЕРЕСОЗДАТЬ)
-- ============================================================
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS faq;

-- ============================================================
-- 3. ТАБЛИЦА ПОЛЬЗОВАТЕЛЕЙ
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(50) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. ТАБЛИЦА ЗАКАЗОВ
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    course VARCHAR(50) DEFAULT 'Курс по криптовалюте x1',
    price VARCHAR(50) DEFAULT '25000',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. ТАБЛИЦА ОТЗЫВОВ
-- ============================================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. ТАБЛИЦА FAQ
-- ============================================================
CREATE TABLE IF NOT EXISTS faq (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. ДОБАВЛЕНИЕ АДМИНИСТРАТОРА
-- ============================================================
INSERT INTO users (login, password, role) VALUES 
('admin', 'admin', 'admin')
ON DUPLICATE KEY UPDATE login=login;

-- ============================================================
-- 8. ДОБАВЛЕНИЕ ТЕСТОВЫХ FAQ
-- ============================================================
INSERT INTO faq (question, answer) VALUES 
('Тон-полутоновый контрапункт контрастных фактур: предпосылки и развитие?', 
 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Earum nulla rerum voluptatum unde id veritatis culpa suscipit iste fugit, tempora quidem, praesentium, ipsam dolorem doloribus consequuntur voluptatem animi possimus reprehenderit.'),

('Абстрактный график функции в XXI веке?', 
 'Рондо, так или иначе, представляет собой open-air. Хорус, в первом приближении, трансформирует диссонансный флюгель-горн. Пауза многопланово начинает дорийский пласт.'),

('Прецизионный курс: гипотеза и теории?', 
 'Рондо, так или иначе, представляет собой open-air. Хорус, в первом приближении, трансформирует диссонансный флюгель-горн. Пауза многопланово начинает дорийский пласт.'),

('Твердый альтиметр: привлечение аудитории или ПИГ?', 
 'Рондо, так или иначе, представляет собой open-air. Хорус, в первом приближении, трансформирует диссонансный флюгель-горн. Пауза многопланово начинает дорийский пласт.')
ON DUPLICATE KEY UPDATE question=question;

-- ============================================================
-- 9. ПРОВЕРКА ДАННЫХ
-- ============================================================
SELECT * FROM users;
SELECT * FROM faq;
SELECT * FROM orders;
SELECT * FROM reviews;

-- ============================================================
-- 10. ПОКАЗАТЬ СТРУКТУРУ ТАБЛИЦ
-- ============================================================
DESCRIBE users;
DESCRIBE orders;
DESCRIBE faq;
DESCRIBE reviews;