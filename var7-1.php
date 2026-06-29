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

// Добавление новости (только для админа)
if (isset($_POST['add_news_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $title = trim($_POST['title']);
    $text = trim($_POST['text']);
    $link = trim($_POST['link']);
    
    if ($title && $text) {
        $title = mysqli_real_escape_string($conn, $title);
        $text = mysqli_real_escape_string($conn, $text);
        $link = mysqli_real_escape_string($conn, $link);
        
        mysqli_query($conn, "INSERT INTO news (title, text, link) VALUES ('$title', '$text', '$link')");
        $msg_news = 'Новость добавлена!';
    } else {
        $msg_news = 'Заполните все поля!';
    }
}

// Добавление материала (только для админа)
if (isset($_POST['add_material_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $title = trim($_POST['material_title']);
    $description = trim($_POST['material_description']);
    $link = trim($_POST['material_link']);
    
    if ($title && $description) {
        $title = mysqli_real_escape_string($conn, $title);
        $description = mysqli_real_escape_string($conn, $description);
        $link = mysqli_real_escape_string($conn, $link);
        
        mysqli_query($conn, "INSERT INTO materials (title, description, link) VALUES ('$title', '$description', '$link')");
        $msg_material = 'Материал добавлен!';
    } else {
        $msg_material = 'Заполните все поля!';
    }
}

// Удаление новости
if (isset($_GET['delete_news']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $id = intval($_GET['delete_news']);
    mysqli_query($conn, "DELETE FROM news WHERE id=$id");
    header('Location: ?page=admin');
    exit;
}

// Удаление материала
if (isset($_GET['delete_material']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $id = intval($_GET['delete_material']);
    mysqli_query($conn, "DELETE FROM materials WHERE id=$id");
    header('Location: ?page=admin');
    exit;
}

// Получаем все новости
$result_news = mysqli_query($conn, "SELECT * FROM news ORDER BY id DESC");
$news_items = [];
while ($row = mysqli_fetch_assoc($result_news)) {
    $news_items[] = $row;
}

// Получаем все материалы
$result_materials = mysqli_query($conn, "SELECT * FROM materials ORDER BY id DESC");
$material_items = [];
while ($row = mysqli_fetch_assoc($result_materials)) {
    $material_items[] = $row;
}

// Определяем страницу
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football For Beginners</title>
    <style>
        /* ============================================================
           ВСЕ СТИЛИ ВСТРОЕНЫ ЗДЕСЬ
           ============================================================ */
        
        /* ----- ШРИФТЫ ----- */
        @font-face {
            font-family: "OpenSansSemiBold";
            src: url(fonts/OpenSans-SemiBold.ttf);
            font-style: normal;
            font-weight: normal;
        }
        @font-face {
            font-family: "OpenSansRegular";
            font-style: normal;
            font-weight: normal;
            src: url(fonts/OpenSans-Regular.ttf);
        }
        @font-face {
            font-family: "OpenSansBold";
            font-style: normal;
            font-weight: normal;
            src: url(fonts/OpenSans-Bold.ttf);
        }

        /* ----- RESET ----- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        a {
            text-decoration: none;
        }

        /* ----- HEADER ----- */
        header {
            width: 100%;
            height: 50px;
            background: #222222;
        }

        .header {
            margin-left: 360px;
            display: flex;
            justify-content: space-between;
            margin-right: 360px;
        }

        .img_logo {
            display: flex;
        }

        .links {
            margin-left: 50px;
            display: flex;
        }

        .try {
            margin-top: 9px;
        }

        .header_img {
            margin-top: 14px;
            margin-left: 15px;
            font-size: 16px;
            line-height: 22px;
            text-align: center;
            color: #FFFFFF;
            font-family: "OpenSansSemiBold";
        }

        .header_menu {
            display: flex;
            font-size: 16px;
            line-height: 22px;
            text-align: center;
        }

        .link:hover {
            background: linear-gradient(180deg, #F4893B 0%, #EF5F31 100%);
        }

        .link {
            width: 150px;
            height: 50px;
            color: white;
        }

        .link_header {
            margin-top: 14px;
            font-family: "OpenSansSemiBold";
            color: white;
        }

        .link:not(:last-child) {
            color: #FFFFFF;
        }

        .link:last-child {
            color: white;
        }

        .header_login {
            margin-top: 13px;
        }

        .link_log {
            color: white;
            font-size: 16px;
            line-height: 22px;
            text-align: center;
            font-family: "OpenSansSemiBold";
        }

        .link_log:hover {
            color: #F4893B;
        }

        .link_log:first-child {
            margin-right: 30px;
        }

        /* ----- АДМИН-ССЫЛКИ ----- */
        .admin-link {
            color: red !important;
            font-weight: bold;
        }
        .login-link {
            color: #007bff !important;
            font-weight: bold;
        }

        /* ----- CONTAINER ----- */
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ----- INFO HEADER ----- */
        .header_footbol {
            padding-top: 70px;
        }

        .info_header {
            background-image: url(img/slide2.png);
            width: 100%;
            height: 420px;
            background-size: cover;
            background-position: center;
        }

        .h1_header {
            color: white;
            font-size: 36px;
            line-height: 49px;
            font-family: "OpenSansBold";
        }

        .welcome {
            margin-top: 10px;
            font-size: 18px;
            line-height: 25px;
            color: white;
            font-family: "OpenSansRegular";
        }

        .button_header {
            margin-top: 87px;
        }

        .button {
            background: linear-gradient(180deg, #F4893B 0%, #EF5F31 100%);
            width: 280px;
            height: 50px;
            border-radius: 5px;
            border: 0;
            color: white;
            font-size: 18px;
            line-height: 25px;
            cursor: pointer;
            font-family: "OpenSansSemiBold";
        }

        /* ----- MATERIALS ----- */
        .rectangle {
            width: 380px;
            height: 190px;
            display: block;
        }

        .rectangle_first {
            background-image: url(img/Rectangle6.png);
            background-size: cover;
            background-position: center;
            height: 100%;
        }

        .rectangle_second {
            background-image: url(img/Rectangle7.png);
            background-size: cover;
            background-position: center;
            height: 100%;
        }

        .rectangle_third {
            background-image: url(img/Rectangle8.png);
            background-size: cover;
            background-position: center;
            height: 100%;
        }

        .content_first,
        .content_second {
            padding: 81px 40px 82px 40px;
            color: white;
            font-size: 20px;
            line-height: 27px;
            text-align: center;
            font-family: "OpenSansSemiBold";
            font-weight: bold;
        }

        .content_third {
            padding: 81px 40px 56px 40px;
            color: white;
            font-size: 20px;
            text-align: center;
            font-weight: bold;
            font-family: "OpenSansSemiBold";
        }

        .content {
            display: flex;
            margin-top: 30px;
            flex-wrap: wrap;
            gap: 30px;
        }

        .rectangle:not(:last-child) {
            margin-right: 30px;
        }

        .materials_header {
            margin-top: 50px;
        }

        .materials {
            font-size: 24px;
            line-height: 33px;
            color: #292929;
            font-weight: 600;
            font-family: "OpenSansSemiBold";
        }

        /* ----- ABOUT ----- */
        .ol {
            margin-left: 25px;
            margin-top: 10px;
            font-weight: normal;
            font-size: 18px;
            line-height: 28px;
            color: #292929;
            font-family: "OpenSansRegular";
        }

        .text {
            font-weight: 600;
            font-size: 24px;
            line-height: 33px;
            font-family: "OpenSansSemiBold";
            color: #292929;
        }

        .text2 {
            margin-top: 15px;
            font-weight: normal;
            font-size: 18px;
            line-height: 25px;
            color: #292929;
            font-family: "OpenSansRegular";
        }

        .about_us {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            flex-wrap: wrap;
            gap: 30px;
        }

        .about_us img {
            max-width: 100%;
        }

        /* ----- NEWS ----- */
        .news_header {
            font-weight: 600;
            font-size: 24px;
            line-height: 33px;
            color: #292929;
            margin-bottom: 30px;
            margin-top: 50px;
            font-family: "OpenSansSemiBold";
        }

        .news_feed {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .football_news {
            margin-bottom: 100px;
        }

        .news {
            width: 291px;
            height: 140px;
            border: 1px solid #C7D4E2;
            box-sizing: border-box;
            border-radius: 4px;
            padding: 15px 0 0 20px;
        }

        .news_text {
            margin: 0 0 22px 0;
            font-weight: 600;
            font-size: 16px;
            line-height: 22px;
            font-family: "OpenSansSemiBold";
        }

        .news_link {
            font-weight: 600;
            font-size: 16px;
            line-height: 22px;
            color: #2386E1;
            font-family: "OpenSansSemiBold";
        }

        .news_link:hover {
            color: #F4893B;
        }

        /* ----- FOOTER ----- */
        .footer {
            width: 100%;
            height: auto;
            background: #222222;
            padding: 30px 0;
        }

        .footer_logo {
            padding-top: 25px;
        }

        .footer_image {
            width: 64px;
            height: 44px;
            margin-left: 35px;
        }

        .footer_img {
            font-size: 16px;
            line-height: 22px;
            color: #FFFFFF;
            font-family: "OpenSansSemiBold";
        }

        .container_footer {
            max-width: 1300px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            padding: 0 20px;
        }

        .footer_links_headings {
            display: flex;
            flex-direction: column;
            color: white;
            margin: 10px 0;
        }

        .headings {
            font-weight: 600;
            font-size: 18px;
            line-height: 25px;
            margin-bottom: 15px;
            font-family: "OpenSansSemiBold";
        }

        .footer_link {
            color: white;
            font-weight: 600;
            font-size: 18px;
            line-height: 25px;
            font-family: "OpenSansRegular";
        }

        .footer_link:not(:last-child) {
            margin-bottom: 10px;
        }

        .fresh_news {
            font-weight: 600;
            font-size: 18px;
            line-height: 25px;
            margin-bottom: 15px;
            font-family: "OpenSansSemiBold";
        }

        .footer_links_fresh {
            display: flex;
            flex-direction: column;
            color: white;
            margin: 10px 0;
        }

        .footer_link:hover {
            color: #F4893B;
        }

        /* ----- АДМИН-ПАНЕЛЬ ----- */
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
        .admin-form textarea {
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
        .header_login .admin-link {
            color: red !important;
            font-weight: bold;
        }
        .header_login .login-link {
            color: #007bff !important;
            font-weight: bold;
        }

        /* ----- АДАПТИВ ----- */
        @media (max-width: 1200px) {
            .header {
                margin-left: 20px;
                margin-right: 20px;
            }
            .rectangle {
                width: 300px;
            }
            .news {
                width: 230px;
            }
        }
        @media (max-width: 992px) {
            .header_menu {
                flex-wrap: wrap;
            }
            .links {
                margin-left: 20px;
            }
            .link {
                width: 120px;
            }
            .rectangle {
                width: 100%;
                max-width: 380px;
            }
            .content {
                justify-content: center;
            }
            .news {
                width: 100%;
                max-width: 291px;
            }
            .news_feed {
                justify-content: center;
            }
            .container_footer {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .footer_links_headings,
            .footer_links_fresh {
                align-items: center;
            }
            .footer_image {
                margin-left: 0;
            }
        }
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                height: auto;
                padding: 10px 0;
            }
            .header_menu {
                flex-direction: column;
                align-items: center;
            }
            .links {
                margin-left: 0;
                flex-wrap: wrap;
                justify-content: center;
            }
            .header_login {
                text-align: center;
            }
            .info_header {
                height: 350px;
            }
            .h1_header {
                font-size: 28px;
            }
            .button {
                width: 200px;
            }
            .about_us {
                flex-direction: column;
                align-items: center;
            }
            .about_us img {
                max-width: 100%;
            }
        }
        @media (max-width: 480px) {
            .h1_header {
                font-size: 22px;
            }
            .welcome {
                font-size: 16px;
            }
            .button {
                width: 180px;
                font-size: 14px;
            }
            .rectangle {
                height: 150px;
            }
            .content_first,
            .content_second,
            .content_third {
                padding: 40px 20px;
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="header">
            <div class="header_menu">
                <p class="try"><img src="img/soccer1.png" alt="" class="logo">
                <p class="header_img">Football For Begginers</p>
                </p>
                <div class="links">
                    <a href="?page=home" class="link">
                        <div class="link_header">Главная</div>
                    </a>
                    <a href="#myid1" class="link">
                        <div class="link_header">О нас</div>
                    </a>
                    <a href="#myid2" class="link">
                        <div class="link_header">Материалы</div>
                    </a>
                </div>
            </div>
            <div class="header_login">
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                    <a href="?page=admin" class="link_log admin-link">Админ</a>
                    <a href="?logout=1" class="link_log admin-link">Выйти (<?php echo $_SESSION['user_login']; ?>)</a>
                <?php else: ?>
                    <a href="?page=login" class="link_log login-link">Войти</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if ($page == 'home'): ?>
    <div class="main_information">
        <div class="info_header">
            <div class="container">
                <div class="header_footbol">
                    <h1 class="h1_header">Футбол для начинающих</h1>
                    <p class="welcome">Добро пожаловать на футбольный сайт для<br> начинающих футболистов!</p>
                    <div class="button_header">
                        <button class="button">Смотреть главные новости</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="main_materials">
            <div class="content">
    <?php 
    // Получаем материалы из БД (если они есть)
    $display_materials = !empty($material_items) ? $material_items : [];
    
    // Если материалов нет, используем статические
    if (empty($display_materials)): ?>
        <!-- Статические материалы -->
        <a href="materials.html" class="rectangle">
            <div class="rectangle_first" style="background-image: url('img/Rectangle6.png');">
                <div class="content_first">
                    <p>Обучающие материалы</p>
                </div>
            </div>
        </a>
        <a href="sport_event.html" class="rectangle">
            <div class="rectangle_second" style="background-image: url('img/Rectangle7.png');">
                <div class="content_second">
                    <p>Спортивные мероприятия</p>
                </div>
            </div>
        </a>
        <a href="news.html" class="rectangle">
            <div class="rectangle_third" style="background-image: url('img/Rectangle8.png');">
                <div class="content_third">
                    <p>Последние новости в мире футбола</p>
                </div>
            </div>
        </a>
    <?php else: ?>
        <!-- Динамические материалы из БД -->
        <?php foreach($display_materials as $material): 
            // Определяем CSS-класс для фона
            $bg_class = 'rectangle_first';
            $bg_image = !empty($material['image']) ? htmlspecialchars($material['image']) : 'img/Rectangle6.png';
        ?>
            <a href="<?php echo !empty($material['link']) ? htmlspecialchars($material['link']) : '#'; ?>" class="rectangle">
                <div class="<?php echo $bg_class; ?>" style="background-image: url('<?php echo $bg_image; ?>'); background-size: cover; background-position: center;">
                    <div class="content_first">
                        <p><?php echo htmlspecialchars($material['title']); ?></p>
                        <p style="font-size:12px;color:#fff;opacity:0.8; margin-top:5px;">
                            <?php echo htmlspecialchars($material['description']); ?>
                        </p>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
                </div>
                <div class="about_us">
                    <div class="info_about_us">
                        <p class="materials"><span id="myid1">О нас</span></p>
                        <p class="text2">Football for begginers - это познавательный сайт для любителей футбола:</p>
                        <ol class="ol">
                            <li>Пройти познавательные методики по обучению игре в футбол</li>
                            <li>Узнать ближайщие футбольные мероприятия</li>
                            <li>Просмотреть последние новости в мире футбола</li>
                        </ol>
                    </div>
                    <div>
                        <img src="img/image1.png" alt="">
                    </div>
                </div>
                <div class="football_news">
                    <p class="news_header">Популярные новости в мире футбола</p>
                    <div class="news_feed">
                        <?php if (empty($news_items)): ?>
                            <!-- Статические новости -->
                            <div class="news">
                                <p class="news_text">Коронавирус отступает: немецкий футбол вернется 9 мая</p>
                                <a href="https://www.gazeta.ru/sport/2020/04/26/a_13063165.shtml" class="news_link">Читать далее</a>
                            </div>
                            <div class="news">
                                <p class="news_text">В «Эспаньоле» согласились на сокращение зарплат</p>
                                <a href="https://www.gazeta.ru/sport/news/2020/04/27/n_14349553.shtml" class="news_link">Читать далее</a>
                            </div>
                            <div class="news">
                                <p class="news_text">«Матч ТВ» покажет игры сборной СССР по футболу на ЧМ-1970</p>
                                <a href="https://news.sportbox.ru/Vidy_sporta/Futbol/world_cup/spbnews_NI1184191_Match_TV_pokazhet_igry_sbornoj_SSSR_po_futbolu_na_ChM_1970" class="news_link">Читать далее</a>
                            </div>
                            <div class="news">
                                <p class="news_text">В сети появилось изображение будущей формы «Атлетико»</p>
                                <a href="https://news.sportbox.ru/Vidy_sporta/Futbol/Evropejskie_chempionaty/Ispaniya/spbnews_NI1183963_V_seti_pojavilos_izobrazhenije_budushhej_formy_Atletiko" class="news_link">Читать далее</a>
                            </div>
                        <?php else: ?>
                            <?php foreach($news_items as $news): ?>
                                <div class="news">
                                    <p class="news_text"><?php echo htmlspecialchars($news['title']); ?></p>
                                    <p style="font-size:12px;color:#666;"><?php echo htmlspecialchars($news['text']); ?></p>
                                    <?php if (!empty($news['link'])): ?>
                                        <a href="<?php echo htmlspecialchars($news['link']); ?>" class="news_link">Читать далее</a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($page == 'login'): ?>
    <div class="main_information" style="padding:50px 0;">
        <div class="login-form">
            <h1>⚽ Авторизация</h1>
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
        <div class="main_information" style="padding:50px 0;text-align:center;">
            <h1>⛔ Доступ запрещен</h1>
            <p>У вас нет прав доступа! <a href="?page=login">Войти</a></p>
        </div>
    <?php else: ?>
        <div class="main_information">
            <div class="admin-panel">
                <h1 style="text-align:center;">⚡ Админ-панель</h1>
                <p style="text-align:center;">Добро пожаловать, <strong><?php echo $_SESSION['user_login']; ?></strong>! 👋</p>
                
                <!-- Добавление новости -->
                <div class="admin-form">
                    <h2>📰 Добавить новость</h2>
                    <?php if (isset($msg_news)): ?>
                        <div class="msg-success">✅ <?php echo $msg_news; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="text" name="title" placeholder="Заголовок новости" required>
                        <textarea name="text" placeholder="Текст новости" rows="3" required></textarea>
                        <input type="text" name="link" placeholder="Ссылка на полную новость">
                        <button type="submit" name="add_news_btn">➕ Добавить новость</button>
                    </form>
                </div>

                <!-- Добавление материала -->
                <div class="admin-form">
                    <h2>📚 Добавить материал</h2>
                    <?php if (isset($msg_material)): ?>
                        <div class="msg-success">✅ <?php echo $msg_material; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="text" name="material_title" placeholder="Название материала" required>
                        <textarea name="material_description" placeholder="Описание материала" rows="3" required></textarea>
                        <input type="text" name="material_link" placeholder="Ссылка на материал">
                        <button type="submit" name="add_material_btn">➕ Добавить материал</button>
                    </form>
                </div>

                <!-- Список новостей -->
                <h2>📋 Все новости (<?php echo count($news_items); ?>)</h2>
                <?php if (empty($news_items)): ?>
                    <p>Нет новостей</p>
                <?php else: ?>
                    <?php foreach($news_items as $news): ?>
                        <div class="item-card">
                            <h3><?php echo htmlspecialchars($news['title']); ?></h3>
                            <p><?php echo htmlspecialchars($news['text']); ?></p>
                            <?php if (!empty($news['link'])): ?>
                                <a href="<?php echo htmlspecialchars($news['link']); ?>" target="_blank">Ссылка</a>
                            <?php endif; ?>
                            <br>
                            <small style="color:#999;">Добавлено: <?php echo $news['created_at']; ?></small>
                            <br>
                            <a href="?page=admin&delete_news=<?php echo $news['id']; ?>" 
                               class="delete-btn" 
                               onclick="return confirm('Удалить новость?')">🗑 Удалить</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Список материалов -->
                <h2>📋 Все материалы (<?php echo count($material_items); ?>)</h2>
                <?php if (empty($material_items)): ?>
                    <p>Нет материалов</p>
                <?php else: ?>
                    <?php foreach($material_items as $material): ?>
                        <div class="item-card">
                            <h3><?php echo htmlspecialchars($material['title']); ?></h3>
                            <p><?php echo htmlspecialchars($material['description']); ?></p>
                            <?php if (!empty($material['link'])): ?>
                                <a href="<?php echo htmlspecialchars($material['link']); ?>" target="_blank">Ссылка</a>
                            <?php endif; ?>
                            <br>
                            <small style="color:#999;">Добавлено: <?php echo $material['created_at']; ?></small>
                            <br>
                            <a href="?page=admin&delete_material=<?php echo $material['id']; ?>" 
                               class="delete-btn" 
                               onclick="return confirm('Удалить материал?')">🗑 Удалить</a>
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

    <footer>
        <div class="footer">
            <div class="container_footer">
                <div class="footer_logo">
                    <img src="img/soccer1.png" alt="" class="footer_image">
                    <p class="footer_img">Football For Begginers</p>
                </div>
                <div class="footer_links_headings">
                    <p class="headings">Рубрики</p>
                    <a href="https://terrikon.com/football/england/championship/" class="footer_link">Чемпионат Англии, Премьер-Лига</a>
                    <a href="https://terrikon.com/football/germany/championship/" class="footer_link">Чемпионат Германии, Бундеслига</a>
                    <a href="https://www.sports.ru/rfpl/" class="footer_link">Чемпионат России, РПЛ</a>
                </div>
                <div class="footer_links_fresh">
                    <p class="fresh_news">Свежие новости</p>
                    <a href="https://www.gazeta.ru/sport/2020/04/26/a_13063165.shtml" class="footer_link">Коронавирус отступает: немецкий футбол вернется 9 мая</a>
                    <a href="https://news.sportbox.ru/Vidy_sporta/Futbol/world_cup/spbnews_NI1184191_Match_TV_pokazhet_igry_sbornoj_SSSR_po_futbolu_na_ChM_1970" class="footer_link">«Матч ТВ» покажет игры сборной СССР по футболу на ЧМ-1970</a>
                    <a href="https://www.gazeta.ru/sport/news/2020/04/27/n_14349553.shtml" class="footer_link">В «Эспаньоле» согласились на сокращение зарплат</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
<?php
// Закрываем подключение
mysqli_close($conn);
?>



-- ============================================================
-- БАЗА ДАННЫХ ДЛЯ САЙТА "Football For Beginners"
-- ============================================================

-- 1. СОЗДАНИЕ БАЗЫ ДАННЫХ
CREATE DATABASE IF NOT EXISTS exam_db;
USE exam_db;

-- 2. УДАЛЕНИЕ СТАРЫХ ТАБЛИЦ
DROP TABLE IF EXISTS materials;
DROP TABLE IF EXISTS news;
DROP TABLE IF EXISTS users;

-- 3. ТАБЛИЦА ПОЛЬЗОВАТЕЛЕЙ
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. ТАБЛИЦА НОВОСТЕЙ
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    text TEXT NOT NULL,
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. ТАБЛИЦА МАТЕРИАЛОВ (С КАРТИНКАМИ)
CREATE TABLE IF NOT EXISTS materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255),
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. ДОБАВЛЕНИЕ АДМИНИСТРАТОРА
INSERT INTO users (login, password, role) VALUES ('admin', 'admin', 'admin');

-- 7. ДОБАВЛЕНИЕ ТЕСТОВЫХ НОВОСТЕЙ
INSERT INTO news (title, text, link) VALUES 
('Коронавирус отступает: немецкий футбол вернется 9 мая', 'Немецкий футбол возвращается после паузы из-за коронавируса. Матчи будут проходить без зрителей.', 'https://www.gazeta.ru/sport/2020/04/26/a_13063165.shtml'),
('В «Эспаньоле» согласились на сокращение зарплат', 'Игроки испанского клуба согласились на сокращение зарплат на время пандемии.', 'https://www.gazeta.ru/sport/news/2020/04/27/n_14349553.shtml'),
('«Матч ТВ» покажет игры сборной СССР по футболу на ЧМ-1970', 'Телеканал покажет исторические матчи сборной СССР на чемпионате мира 1970 года.', 'https://news.sportbox.ru/Vidy_sporta/Futbol/world_cup/spbnews_NI1184191_Match_TV_pokazhet_igry_sbornoj_SSSR_po_futbolu_na_ChM_1970');

-- 8. ДОБАВЛЕНИЕ ТЕСТОВЫХ МАТЕРИАЛОВ С КАРТИНКАМИ
INSERT INTO materials (title, description, image, link) VALUES 
('Обучающие материалы', 'Видеоуроки и статьи по основам футбола для начинающих', 'img/Rectangle6.png', 'materials.html'),
('Спортивные мероприятия', 'Анонсы и расписание ближайших футбольных событий', 'img/Rectangle7.png', 'sport_event.html'),
('Последние новости', 'Свежие новости из мира футбола', 'img/Rectangle8.png', 'news.html');

-- 9. ПРОВЕРКА ДАННЫХ
SELECT * FROM users;
SELECT * FROM news;
SELECT * FROM materials;