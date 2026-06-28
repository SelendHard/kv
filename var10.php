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

// Добавление услуги
if (isset($_POST['add_service_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $image = trim($_POST['image']);
    if ($name && $description) {
        $name = mysqli_real_escape_string($conn, $name);
        $description = mysqli_real_escape_string($conn, $description);
        $image = mysqli_real_escape_string($conn, $image);
        mysqli_query($conn, "INSERT INTO services (name, description, image) VALUES ('$name', '$description', '$image')");
        $msg_service = 'Услуга добавлена!';
    } else {
        $msg_service = 'Заполните все поля!';
    }
}

// Добавление отзыва
if (isset($_POST['add_review_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $name = trim($_POST['review_name']);
    $text = trim($_POST['review_text']);
    $author = trim($_POST['review_author']);
    $image = trim($_POST['review_image']);
    if ($name && $text) {
        $name = mysqli_real_escape_string($conn, $name);
        $text = mysqli_real_escape_string($conn, $text);
        $author = mysqli_real_escape_string($conn, $author);
        $image = mysqli_real_escape_string($conn, $image);
        mysqli_query($conn, "INSERT INTO reviews (name, text, author, image) VALUES ('$name', '$text', '$author', '$image')");
        $msg_review = 'Отзыв добавлен!';
    } else {
        $msg_review = 'Заполните все поля!';
    }
}

// Удаление услуги
if (isset($_GET['delete_service']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $id = intval($_GET['delete_service']);
    mysqli_query($conn, "DELETE FROM services WHERE id=$id");
    header('Location: ?page=admin');
    exit;
}

// Удаление отзыва
if (isset($_GET['delete_review']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $id = intval($_GET['delete_review']);
    mysqli_query($conn, "DELETE FROM reviews WHERE id=$id");
    header('Location: ?page=admin');
    exit;
}

// Получаем услуги
$services = [];
$result_serv = mysqli_query($conn, "SELECT * FROM services ORDER BY id");
while ($row = mysqli_fetch_assoc($result_serv)) {
    $services[] = $row;
}

// Получаем отзывы
$reviews = [];
$result_rev = mysqli_query($conn, "SELECT * FROM reviews ORDER BY id");
while ($row = mysqli_fetch_assoc($result_rev)) {
    $reviews[] = $row;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Обработка формы запроса
if (isset($_POST['send_request'])) {
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $country = mysqli_real_escape_string($conn, trim($_POST['country']));
    $terms = isset($_POST['terms']) ? 1 : 0;
    if ($fullname && $email) {
        mysqli_query($conn, "INSERT INTO requests (fullname, email, phone, country, terms) 
                            VALUES ('$fullname', '$email', '$phone', '$country', '$terms')");
        $request_msg = 'Заявка отправлена!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Travel</title>
    <style>
        /* ====== GRID.CSS ====== */
        *, *:before, *:after { box-sizing: border-box; }
        .area { margin-left: auto; margin-right: auto; }
        @media (min-width: 768px) { .area { width: 750px; } }
        @media (min-width: 992px) { .area { width: 970px; } }
        @media (min-width: 1200px) { .area { width: 1170px; } }
        .row:before, .row:after { content: " "; display: table; }
        .row:after { clear: both; }
        .col { padding: 15px; text-align: center; }
        .col-xs-1, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9, .col-xs-10, .col-xs-11, .col-xs-12, 
        .col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12, 
        .col-md-1, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-10, .col-md-11, .col-md-12, 
        .col-lg-1, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-lg-10, .col-lg-11, .col-lg-12 {
            position: relative;
            float: left;
        }
        .col-xs-1  { width: 8.33333333%; }
        .col-xs-2  { width: 16.66666667%; }
        .col-xs-3  { width: 25%; }
        .col-xs-4  { width: 33.33333333%; }
        .col-xs-5  { width: 41.66666667%; }
        .col-xs-6  { width: 50%; }
        .col-xs-7  { width: 58.33333333%; }
        .col-xs-8  { width: 66.66666667%; }
        .col-xs-9  { width: 75%; }
        .col-xs-10 { width: 83.33333333%; }
        .col-xs-11 { width: 91.66666667%; }
        .col-xs-12 { width: 100%; }
        @media (min-width: 768px) {
            .col-sm-1  { width: 8.33333333%; }
            .col-sm-2  { width: 16.66666667%; }
            .col-sm-3  { width: 25%; }
            .col-sm-4  { width: 33.33333333%; }
            .col-sm-5  { width: 41.66666667%; }
            .col-sm-6  { width: 50%; }
            .col-sm-7  { width: 58.33333333%; }
            .col-sm-8  { width: 66.66666667%; }
            .col-sm-9  { width: 75%; }
            .col-sm-10 { width: 83.33333333%; }
            .col-sm-11 { width: 91.66666667%; }
            .col-sm-12 { width: 100%; }
        }
        @media (min-width: 992px) {
            .col-md-1  { width: 8.33333333%; }
            .col-md-2  { width: 16.66666667%; }
            .col-md-3  { width: 25%; }
            .col-md-4  { width: 33.33333333%; }
            .col-md-5  { width: 41.66666667%; }
            .col-md-6  { width: 50%; }
            .col-md-7  { width: 58.33333333%; }
            .col-md-8  { width: 66.66666667%; }
            .col-md-9  { width: 75%; }
            .col-md-10 { width: 83.33333333%; }
            .col-md-11 { width: 91.66666667%; }
            .col-md-12 { width: 100%; }
        }
        @media (min-width: 1200px) {
            .col-lg-1  { width: 8.33333333%; }
            .col-lg-2  { width: 16.66666667%; }
            .col-lg-3  { width: 25%; }
            .col-lg-4  { width: 33.33333333%; }
            .col-lg-5  { width: 41.66666667%; }
            .col-lg-6  { width: 50%; }
            .col-lg-7  { width: 58.33333333%; }
            .col-lg-8  { width: 66.66666667%; }
            .col-lg-9  { width: 75%; }
            .col-lg-10 { width: 83.33333333%; }
            .col-lg-11 { width: 91.66666667%; }
            .col-lg-12 { width: 100%; }
        }

        /* ====== STYLE.CSS ====== */
        body { margin:0; padding:0; background: white; }
        @font-face {
            font-family: Montserrat; 
            src: url(fonts/Montserrat-Regular.ttf); 
        }
        @font-face {
            font-family: PT_Serif;
            src: url(fonts/PT_Serif-Web-Regular.ttf);
        }
        h1 { font-size: 40px; font-family: Montserrat,PT_Serif; text-align: center; }
        .align-left { text-align: left; }
        h3 { text-align: center; }
        .color_text { color: #d03958; }
        .big_text { font-size: 40px; }
        
        .first { width: 100%; background: url(img/road.jpg); background-size: cover; height: auto; }
        .slogan { margin-top: 300px; color: black; font-family: Montserrat,PT_Serif; font-size: 80px; width: 80%; font-weight: bold; text-align: left; }
        .name { font-size: 49px; color: white; font-family: Montserrat,PT_Serif; font-weight: bold; width: 60%; }
        .btn {
            background: #e3d5b8; color: black; padding: 10px; font-size: 20px; border: none;
            border-radius: 5px; display: block; text-align: center; text-decoration: none;
            width: 100%; position: center; margin-top: 5px; margin-bottom: 5px;
        }
        .black { background: black; color: white; }
        .btn:hover { background: #ff567a; color: white; }
        .legend { color: black; font-size: 12px; text-align: left; float: left; }
        .second { font-family: Montserrat,PT_Serif; padding: 40px; }
        .third { background: #d03958; height: auto; padding: 50px; padding-top: 20px; padding-bottom: 20px; }
        .third span { font-family: Montserrat,PT_Serif; font-size: 18px; }
        .fourth { background: #e3d5b8; height: auto; padding-top: 20px; }
        .contact { width: 260px; text-align: left; }
        .contact span { font-family:Montserrat,PT_Serif; font-size: 20px; }
        .form {
            border: none; color: gray; font-size: 20px; font-family: Montserrat,PT_Serif;
            display: block; width: 100%; padding: 10px; margin-bottom: 10px;
        }
        .footer { background: #161616; padding-bottom: 50px; padding-top: 50px; }
        .footer span { color: white; font-style: italic; font-family: Montserrat,PT_Serif; }
        nav, ul { margin: 20; padding: 0; opacity: 0.9; text-align: right; }
        nav li{ display: inline-block; margin: 10px; }
        nav a {
            text-decoration: none; padding: 5px 20px; color: black;
            display: block; font-family: Montserrat,PT_Serif;
        }
        nav a:hover { background: #d03958; border-radius: 5px; color: white; }
        .request { color: white; background: #d03958; border-radius: 8px; padding: 10px; float: right; margin-top: 300px; }
        .checkbox { float: left; text-align: left; margin: 10px; }
        .story { color: white; padding-right: 20px; float: left; }
        .price {
            margin: 20px; margin-bottom: 0; padding: 20px; padding-bottom: 0;
            background: white; color: black; text-align: center;
            border-radius: 5px; font-size: 20px;
        }
        .price > .btn { margin-bottom: 0; padding: 10px; width: 50%; margin-left: 25%; }
        .popular { position: absolute; left: 15px; top: 15px; }
        .comment {
            padding: 10px; margin-bottom: 30px; margin-top: 30px; display: block;
            border-radius: 5px; background: white; width: 400px; height: 125px;
        }
        .ccomment { background: #e3d5b8; }
        .fifth { margin-top: 50px; margin-bottom: 50px; }
        .gallary { border-radius: 50px; }
        .logo { float: left; margin-top: 20px; }
        .logo_bottom { float: right; }
        .avatar { border-radius: 50px; position: absolute; right: 100px; }
        .link { text-align: center; font-size: 40px; color: #d03958; }
        .legend { padding-right: 200px; }

        /* ====== АДМИН-СТИЛИ ====== */
        .admin-panel { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .admin-form { background: #f9f9f9; padding: 20px; border-radius: 10px; margin-bottom: 30px; border: 1px solid #ddd; }
        .admin-form input, .admin-form textarea, .admin-form select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .admin-form button { padding: 12px 30px; background: #28a745; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .admin-form button:hover { background: #218838; }
        .item-card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; background: #fff; }
        .item-card img { max-width: 100px; border-radius: 5px; }
        .delete-btn { padding: 5px 15px; background: #dc3545; color: #fff; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin-top: 10px; }
        .delete-btn:hover { background: #c82333; }
        .msg-success { color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
        .msg-error { color: red; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
        .login-form { max-width: 400px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 10px; box-shadow: 0 5px 30px rgba(0,0,0,0.1); }
        .login-form h1 { text-align: center; margin-bottom: 30px; }
        .login-form input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .login-form button { width: 100%; padding: 12px; background: #007bff; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .login-form button:hover { background: #0069d9; }
        .admin-link { color: red !important; font-weight: bold; }
        .login-link { color: #007bff !important; font-weight: bold; }
        nav li a.admin-link, nav li a.login-link { color: #000 !important; }
        nav li a.admin-link:hover, nav li a.login-link:hover { color: #fff !important; }
        .request-msg { text-align: center; padding: 15px; margin: 10px 0; border-radius: 5px; font-size: 18px; }
        .request-msg.success { background: #d4edda; color: #155724; }
        .request-msg.error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

    <!-- ====== HEADER ====== -->
    <div class="first">
        <div class="area">
            <div class="row">
                <img src="img/logo.png" alt="" class="logo">
                <nav>
                    <ul>
                        <li><a href="?page=home">Home</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#gallery">Gallery</a></li>
                        <li><a href="#contact">Contact</a></li>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                            <li><a href="?page=admin" class="admin-link">Админ</a></li>
                            <li><a href="?logout=1" class="admin-link">Выйти</a></li>
                        <?php else: ?>
                            <li><a href="?page=login" class="login-link">Войти</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <?php if ($page == 'home'): ?>
                <div class="col col-xs-8 col-md-8 col-lg-8">
                    <div class="slogan"><span>TRAVEL IS<div class="color_text">BEAUTIFUL</div></span></div>
                    <div class="legend">
                        <span>Lovem Ipsum is simply dummy text of the printing and typesetting industry. Lovem Ipsum has been the industry's standard dummy text ever since the 1500s</span>
                    </div>
                </div>

                <div class="col col-xs-12 col-md-4 col-lg-4 request">
                    <h3>REQUEST A QUOTE</h3>
                    <?php if (isset($request_msg)): ?>
                        <div class="request-msg success"><?php echo $request_msg; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input class="form" type="text" name="fullname" placeholder="Full name">
                        <input class="form" type="text" name="email" placeholder="Email-addres">
                        <input class="form" type="text" name="phone" placeholder="Phone Number">
                        <input class="form" type="text" name="country" placeholder="Country">
                        <input type="checkbox" class="checkbox" name="terms"><label>I accept the terms & conditions</label>
                        <input class="btn black" type="submit" name="send_request" value="SEND">
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($page == 'home'): ?>
    <!-- ====== SERVICES ====== -->
    <div class="second" id="services">
        <div class="area">
            <div class="row">
                <h1 class="align-left"><span class="color_text">OUR</span> SERVCES</h1>
                <?php if (empty($services)): ?>
                    <div class="col col-xs-12 col-md-3 col-lg-3">
                        <img src="img/heart.png" alt="">
                        <span>Making your trip beautiful and easier</span>
                    </div>
                    <div class="col col-xs-12 col-md-3 col-lg-3">
                        <img src="img/tools.png" alt="">
                        <span>Tools to help you success tomorrow</span>
                    </div>
                    <div class="col col-xs-12 col-md-3 col-lg-3">
                        <img src="img/lamp.png" alt="">
                        <span>Ideas that blows you out of blue</span>
                    </div>
                    <div class="col col-xs-12 col-md-3 col-lg-3">
                        <img src="img/compass.png" alt="">
                        <span>Navigate your path to beautiful world</span>
                    </div>
                <?php else: ?>
                    <?php foreach($services as $service): ?>
                        <div class="col col-xs-12 col-md-3 col-lg-3">
                            <img src="<?php echo !empty($service['image']) ? htmlspecialchars($service['image']) : 'img/heart.png'; ?>" alt="">
                            <span><?php echo htmlspecialchars($service['description']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ====== STORY ====== -->
    <div class="third" id="about">
        <div class="area">
            <div class="row">
                <div class="story col-xs-8 col-md-8 col-lg-12">We are in business for over 6 years providing amazing services to client and people love them to the core. View our story to know more</div>
                <a href="#" class="btn col-xs-4 col-md-4 col-lg-12">Our story</a>
            </div>
        </div>
    </div>

    <!-- ====== PRICING ====== -->
    <div class="fourth">
        <div class="area">
            <div class="row">
                <h1>PRICING</h1>
                <div class="text col-xs-4 col-md-4 col-lg-4">
                    <div class="price">
                        Starter <br> <br>
                        <div class="color_text big_text">FREE</div><br>
                        Free service<br>
                        Multiple accounts<br>
                        Management no<br>
                        --<br>
                        --<br>
                        <br>
                        <a href="#" class="btn">Try</a>
                    </div>
                </div>
                <div class="text col-xs-12 col-md-4 col-lg-4">
                    <img src="img/popular.png" alt="" class="popular">
                    <div class="price">
                        Business <br> <br>
                        <div class="color_text big_text">$97</div> <br>
                        Free service<br>
                        Multiple accounts<br>
                        Management no<br>
                        Unlimited data<br>
                        --<br>
                        <br>
                        <a href="#" class="btn">Sign up</a>
                    </div>
                </div>
                <div class="text col-xs-12 col-md-4 col-lg-4">
                    <div class="price">
                        Starter <br> <br>
                        <div class="color_text big_text">$297</div><br>
                        Free service<br>
                        Multiple accounts<br>
                        Management no<br>
                        Unlimited data<br>
                        Whatever you need<br>
                        <br>
                        <a href="#" class="btn">Sign up</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ====== TESTIMONIAL & GALLERY ====== -->
    <div class="fifth" id="gallery">
        <div class="area">
            <div class="row">
                <div class="text col-xs-12 col-md-6 col-lg-6">
                    <h1>TESTIMONIAL</h1>
                    <?php if (empty($reviews)): ?>
                        <div class="comment">
                            <img src="img/adult1c.jpg" alt="" class="avatar" width="100px" height="100px">
                            I have been involved with this company for ages and just want to say this is a great work by designcoon.<br><br>
                            <div class="color_text">DJ Bravo - Frequent Traveler</div>
                        </div>
                        <div class="comment ccomment">
                            <img src="img/adult2c.jpg" alt="" class="avatar" width="100px" height="100px">
                            I have been involved with this company for ages and just.<br><br>
                            <div class="color_text">DJ Bravo - Frequent Traveler</div>
                        </div>
                    <?php else: ?>
                        <?php foreach($reviews as $review): ?>
                            <div class="comment <?php echo ($review['id'] % 2 == 0) ? 'ccomment' : ''; ?>">
                                <img src="<?php echo !empty($review['image']) ? htmlspecialchars($review['image']) : 'img/adult1c.jpg'; ?>" alt="" class="avatar" width="100px" height="100px">
                                <?php echo htmlspecialchars($review['text']); ?><br><br>
                                <div class="color_text"><?php echo htmlspecialchars($review['author']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="text col-xs-12 col-md-6 col-lg-6">
                    <h1>GALLERY</h1>
                    <div class="text col-xs-4 col-md-4 col-lg-4"><img src="img/girl.jpg" class="gallary" width="100px" height="100px"></div>
                    <div class="text col-xs-4 col-md-4 col-lg-4"><img src="img/blue.jpg" class="gallary" width="100px" height="100px"></div>
                    <div class="text col-xs-4 col-md-4 col-lg-4"><img src="img/lemons.jpg" class="gallary" width="100px" height="100px"></div>
                    <div class="text col-xs-4 col-md-4 col-lg-4"><img src="img/cup.jpg" class="gallary" width="100px" height="100px"></div>
                    <div class="text col-xs-4 col-md-4 col-lg-4"><img src="img/womans-legs.jpg" class="gallary" width="100px" height="100px"></div>
                    <div class="text col-xs-4 col-md-4 col-lg-4"><img src="img/rocks.jpeg" class="gallary" width="100px" height="100px"></div>
                    <a href="#" class="link">View more pictures</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ====== LOGIN PAGE ====== -->
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

    <!-- ====== ADMIN PANEL ====== -->
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

                <!-- Добавление услуги -->
                <div class="admin-form">
                    <h2>Добавить услугу</h2>
                    <?php if (isset($msg_service)): ?>
                        <div class="msg-success"><?php echo $msg_service; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="text" name="name" placeholder="Название услуги" required>
                        <textarea name="description" placeholder="Описание" rows="3" required></textarea>
                        <input type="text" name="image" placeholder="Ссылка на картинку">
                        <button type="submit" name="add_service_btn">Добавить услугу</button>
                    </form>
                </div>

                <!-- Добавление отзыва -->
                <div class="admin-form">
                    <h2>Добавить отзыв</h2>
                    <?php if (isset($msg_review)): ?>
                        <div class="msg-success"><?php echo $msg_review; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="text" name="review_name" placeholder="Название" required>
                        <textarea name="review_text" placeholder="Текст отзыва" rows="3" required></textarea>
                        <input type="text" name="review_author" placeholder="Автор">
                        <input type="text" name="review_image" placeholder="Ссылка на фото">
                        <button type="submit" name="add_review_btn">Добавить отзыв</button>
                    </form>
                </div>

                <!-- Список услуг -->
                <h2>Все услуги (<?php echo count($services); ?>)</h2>
                <?php if (empty($services)): ?>
                    <p>Нет услуг</p>
                <?php else: ?>
                    <?php foreach($services as $service): ?>
                        <div class="item-card">
                            <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                            <p><?php echo htmlspecialchars($service['description']); ?></p>
                            <?php if (!empty($service['image'])): ?>
                                <img src="<?php echo htmlspecialchars($service['image']); ?>">
                            <?php endif; ?>
                            <br>
                            <a href="?page=admin&delete_service=<?php echo $service['id']; ?>" 
                               class="delete-btn" onclick="return confirm('Удалить услугу?')">Удалить</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Список отзывов -->
                <h2>Все отзывы (<?php echo count($reviews); ?>)</h2>
                <?php if (empty($reviews)): ?>
                    <p>Нет отзывов</p>
                <?php else: ?>
                    <?php foreach($reviews as $review): ?>
                        <div class="item-card">
                            <h4><?php echo htmlspecialchars($review['name']); ?></h4>
                            <p><?php echo htmlspecialchars($review['text']); ?></p>
                            <p><strong>Автор:</strong> <?php echo htmlspecialchars($review['author']); ?></p>
                            <?php if (!empty($review['image'])): ?>
                                <img src="<?php echo htmlspecialchars($review['image']); ?>">
                            <?php endif; ?>
                            <br>
                            <a href="?page=admin&delete_review=<?php echo $review['id']; ?>" 
                               class="delete-btn" onclick="return confirm('Удалить отзыв?')">Удалить</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <p style="text-align:center;margin-top:30px;"><a href="?page=home">На главную</a></p>
            </div>
        </div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- ====== FOOTER ====== -->
    <div class="footer" id="contact">
        <div class="area">
            <div class="row">
                <div class="col col-md-6 col-xs-6 col-lg-6">
                    <span>All Rights Reserved | 2016 | Designed with love by <span class="color_text">DesignCoon</span></span>
                </div>
                <div class="col col-md-6 col-xs-6 col-lg-6"><img src="img/logo.png" alt="" class="logo_bottom"></div>
            </div>
        </div>
    </div>

</body>
</html>
<?php
mysqli_close($conn);
?>


-- ============================================================
-- БАЗА ДАННЫХ ДЛЯ САЙТА "Travel"
-- ============================================================

-- 1. СОЗДАНИЕ БАЗЫ ДАННЫХ
CREATE DATABASE IF NOT EXISTS exam_db;
USE exam_db;

-- ============================================================
-- 2. УДАЛЕНИЕ СТАРЫХ ТАБЛИЦ (ЕСЛИ НУЖНО ПЕРЕСОЗДАТЬ)
-- ============================================================
DROP TABLE IF EXISTS requests;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS services;
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
-- 4. ТАБЛИЦА УСЛУГ
-- ============================================================
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255),
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
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. ТАБЛИЦА ЗАЯВОК (REQUEST A QUOTE)
-- ============================================================
CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    country VARCHAR(100),
    terms TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. ДОБАВЛЕНИЕ АДМИНИСТРАТОРА
-- ============================================================
INSERT INTO users (login, password, role) VALUES 
('admin', 'admin', 'admin')
ON DUPLICATE KEY UPDATE login=login;

-- ============================================================
-- 8. ДОБАВЛЕНИЕ ТЕСТОВЫХ УСЛУГ
-- ============================================================
INSERT INTO services (name, description, image) VALUES 
('Making your trip beautiful', 'Making your trip beautiful and easier', 'img/heart.png'),
('Tools to help you', 'Tools to help you success tomorrow', 'img/tools.png'),
('Ideas that blows you', 'Ideas that blows you out of blue', 'img/lamp.png'),
('Navigate your path', 'Navigate your path to beautiful world', 'img/compass.png')
ON DUPLICATE KEY UPDATE name=name;

-- ============================================================
-- 9. ДОБАВЛЕНИЕ ТЕСТОВЫХ ОТЗЫВОВ
-- ============================================================
INSERT INTO reviews (name, text, author, image) VALUES 
('Отличный сервис', 'I have been involved with this company for ages and just want to say this is a great work by designcoon.', 'DJ Bravo - Frequent Traveler', 'img/adult1c.jpg'),
('Лучшее путешествие', 'I have been involved with this company for ages and just.', 'DJ Bravo - Frequent Traveler', 'img/adult2c.jpg')
ON DUPLICATE KEY UPDATE name=name;

-- ============================================================
-- 10. ДОБАВЛЕНИЕ ТЕСТОВЫХ ЗАЯВОК
-- ============================================================
INSERT INTO requests (fullname, email, phone, country, terms) VALUES 
('Иван Петров', 'ivan@mail.ru', '+7(999)123-45-67', 'Россия', 1),
('Мария Смирнова', 'maria@mail.ru', '+7(999)234-56-78', 'Италия', 1),
('Алексей Иванов', 'alex@mail.ru', '+7(999)345-67-89', 'Франция', 0);

-- ============================================================
-- 11. ПРОВЕРКА ДАННЫХ
-- ============================================================
SELECT * FROM users;
SELECT * FROM services;
SELECT * FROM reviews;
SELECT * FROM requests;

-- ============================================================
-- 12. ПОКАЗАТЬ СТРУКТУРУ ТАБЛИЦ
-- ============================================================
DESCRIBE users;
DESCRIBE services;
DESCRIBE reviews;
DESCRIBE requests;

-- ============================================================
-- 13. ДОПОЛНИТЕЛЬНЫЕ ЗАПРОСЫ
-- ============================================================

-- Количество записей в таблицах
SELECT 'users' as table_name, COUNT(*) as count FROM users
UNION ALL
SELECT 'services', COUNT(*) FROM services
UNION ALL
SELECT 'reviews', COUNT(*) FROM reviews
UNION ALL
SELECT 'requests', COUNT(*) FROM requests;

-- Все услуги с сортировкой по имени
SELECT * FROM services ORDER BY name;

-- Все отзывы с сортировкой по автору
SELECT * FROM reviews ORDER BY author;

-- Заявки за последнюю неделю
SELECT * FROM requests WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY);

-- Заявки с согласием на условия
SELECT * FROM requests WHERE terms = 1;

-- Количество заявок по странам
SELECT country, COUNT(*) as count FROM requests GROUP BY country;

-- ============================================================
-- 14. ПОИСК
-- ============================================================
-- SELECT * FROM services WHERE name LIKE '%trip%';
-- SELECT * FROM reviews WHERE author LIKE '%Bravo%';

-- ============================================================
-- 15. ОЧИСТКА ТАБЛИЦ (ЕСЛИ НУЖНО)
-- ============================================================
-- TRUNCATE TABLE services;
-- TRUNCATE TABLE reviews;
-- TRUNCATE TABLE requests;
-- TRUNCATE TABLE users;

-- ============================================================
-- 16. ПОЛНОЕ УДАЛЕНИЕ БАЗЫ (ЕСЛИ НУЖНО ПЕРЕСОЗДАТЬ)
-- ============================================================
-- DROP DATABASE IF EXISTS exam_db;