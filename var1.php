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

// Добавление отзыва
if (isset($_POST['add_review_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $name = trim($_POST['name']);
    $review = trim($_POST['review']);
    
    if ($name && $review) {
        $name = mysqli_real_escape_string($conn, $name);
        $review = mysqli_real_escape_string($conn, $review);
        mysqli_query($conn, "INSERT INTO reviews (name, review) VALUES ('$name', '$review')");
        $msg_review = 'Отзыв добавлен!';
    } else {
        $msg_review = 'Заполните все поля!';
    }
}

// Добавление номера
if (isset($_POST['add_room_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $image = trim($_POST['image']);
    
    if ($title && $description) {
        $title = mysqli_real_escape_string($conn, $title);
        $description = mysqli_real_escape_string($conn, $description);
        $image = mysqli_real_escape_string($conn, $image);
        mysqli_query($conn, "INSERT INTO rooms (title, description, image) VALUES ('$title', '$description', '$image')");
        $msg_room = 'Номер добавлен!';
    } else {
        $msg_room = 'Заполните все поля!';
    }
}

// Удаление отзыва
if (isset($_GET['delete_review']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $id = intval($_GET['delete_review']);
    mysqli_query($conn, "DELETE FROM reviews WHERE id=$id");
    header('Location: ?page=admin');
    exit;
}

// Удаление номера
if (isset($_GET['delete_room']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $id = intval($_GET['delete_room']);
    mysqli_query($conn, "DELETE FROM rooms WHERE id=$id");
    header('Location: ?page=admin');
    exit;
}

// Получаем все отзывы
$result_reviews = mysqli_query($conn, "SELECT * FROM reviews ORDER BY id DESC");
$reviews = [];
while ($row = mysqli_fetch_assoc($result_reviews)) {
    $reviews[] = $row;
}

// Получаем все номера
$result_rooms = mysqli_query($conn, "SELECT * FROM rooms ORDER BY id DESC");
$rooms = [];
while ($row = mysqli_fetch_assoc($result_rooms)) {
    $rooms[] = $row;
}

// Определяем страницу
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <div class="wrapper">
        
        <button class="select">Забронировать</button>

        <header class="header">
            <div class="container">
                <div class="header__body">
                    <div class="header__logo">
                        <object data="svg/logo.svg"></object>
                    </div>

                    <nav class="header__navbar">
                        <ul class="header__list">
                            <li><a href="?page=home" class="header__link">Главная</a></li>
                            <li><a href="#hostel__num" class="header__link">Наши номера</a></li>
                            <li><a href="#hostel__photo" class="header__link">Фото</a></li>
                            <li><a href="#otzyv__photo" class="header__link">Отзывы</a></li>
                            <li><a href="#hostel__contact" class="header__link">Контакты</a></li>
                            <li><a href="" class="header__link">Правила</a></li>
                            <li><a href="" class="header__link">Наши гости</a></li>
                            <li><a href="" class="header__link">Забронировать</a></li>
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                                <li><a href="?page=admin" class="header__link" style="color:red;">Админ</a></li>
                                <li><a href="?logout=1" class="header__link" style="color:red;">Выйти (<?php echo $_SESSION['user_login']; ?>)</a></li>
                            <?php else: ?>
                                <li><a href="?page=login" class="header__link">Войти</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>

                    <div class="header__search">
                        <input type="search" class="search" placeholder="Поиск">
                    </div>
                </div>
            </div>
           
        </header>

        <main class="content">
            <?php if ($page == 'home'): ?>
            <section class="main__menu" id="main__menu">
                <div class="container">
                    <div class="menu__body">
                        <h1 class="menu__title">ЭКО-ХОСТЕЛ</h1>
                        <p class="menu__slogan">Продуманный комфорт. Отдых в спокойствии. Уют. Чистота. Экологичность</p>
                        <p class="menu__tel">+7 (909) 543 16 23</p>
                    </div>
                </div>
            </section>

            <section class="advent" id="hostel__num">
                <div class="container">
                    <div class="advent__body">
                        <div class="advent__content advent__content1">
                            <object data="svg/adv/hour.svg"></object>
                            <h4>Мы работаем 24/7</h4>
                            <p>Мы рады Вас видеть в нашем уютном и комфортабельном хостеле в
                                любое время дня и любой день недели</p>
                        </div>
                        <div class="advent__content advent__content2">
                            <object data="svg/adv/center.svg"></object>
                            <h4>В самом центре</h4>
                            <p>За 5-10 минут пешком можно дойти до ГУМа, ЦУМа, набережной и других популярных мест
                            </p>
                        </div>
                        <div class="advent__content advent__content3">
                            <object data="svg/adv/plus.svg"></object>
                            <h4>Все, что нужно</h4>
                            <p>К Вашим услугам: постельное белье с полотенцем, пользование общей кухней, стиральной машиной, утюгом, феном, компьютером, Wi-Fi</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="hostel__num">
                <div class="container">
                    <h2 class="num__title">Наши номера</h2>
                    
                    <?php if (empty($rooms)): ?>
                        <!-- Статические номера по умолчанию -->
                        <div class="hostel__num1">
                            <div class="num__body">
                               <div class="num__content1">
                                   <h4>Комната для компании</h4>
                                   <p>Комната для четырех человек. Две двухуровневые кровати. Эту комнату мы называем еще «семейной». Она может сдаваться и по местам (при отсутствии мест в других комнатах). В комнате есть рабочий стол, зеркало, диванчик и телевизор.</p>
                               </div>
                            </div>
                        </div>

                        <div class="hostel__num2">
                            <div class="num__body">
                               <div class="num__content2">
                                   <h4>Комната повышенной комфортности</h4>
                                   <p>Комната для одного или двух человек. Двуспальная кровать.
                                    В комнате есть холодильник, микроволновка, чайник, телевизор.
                                    Комната укомплектована раковиной.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php 
                        $counter = 1;
                        foreach($rooms as $room): 
                            $class = ($counter % 2 == 1) ? 'hostel__num1' : 'hostel__num2';
                            $content_class = ($counter % 2 == 1) ? 'num__content1' : 'num__content2';
                        ?>
                            <div class="<?php echo $class; ?>">
                                <div class="num__body">
                                   <div class="<?php echo $content_class; ?>">
                                       <h4><?php echo htmlspecialchars($room['title']); ?></h4>
                                       <p><?php echo htmlspecialchars($room['description']); ?></p>
                                       <?php if (!empty($room['image'])): ?>
                                           <img src="<?php echo htmlspecialchars($room['image']); ?>" alt="Номер" style="max-width:100%;margin-top:10px;">
                                       <?php endif; ?>
                                   </div>
                                </div>
                            </div>
                        <?php 
                        $counter++;
                        endforeach; 
                        ?>
                    <?php endif; ?>
                </div>
            </section>

            <section class="hostel__photo" id="hostel__photo">
                <div class="container">
                    <div class="photo__body">
                        <h2 class="photo__title">Наш отель</h2>
                        <div class="photo__slider">
                            <div class="photo__slides">
                                <input type="radio" name="r" id="r1" checked>
                                <input type="radio" name="r" id="r2">
                                <input type="radio" name="r" id="r3">
                                <input type="radio" name="r" id="r4">
                                <input type="radio" name="r" id="r5">

                                <div class="slide slide1 s1">
                                    <img src="img/hostel__photo/1.jpg" alt="Error">
                                </div>
                                <div class="slide slide2">
                                    <img src="img/hostel__photo/2.jpg" alt="Error">
                                </div>
                                <div class="slide slide3">
                                    <img src="img/hostel__photo/3.jpg" alt="Error">
                                </div>
                                <div class="slide slide4">
                                    <img src="img/hostel__photo/4.jpg" alt="Error">
                                </div>
                                <div class="slide slide5">
                                    <img src="img/hostel__photo/5.jpg" alt="Error">
                                </div>
                            </div>

                            <div class="information">
                                <label for="r1" class="rad"></label>
                                <label for="r2" class="rad"></label>
                                <label for="r3" class="rad"></label>
                                <label for="r4" class="rad"></label>
                                <label for="r5" class="rad"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="hostel__contact" id="hostel__contact">
                <div class="container">
                    <h2 class="contact__title">Наши контакты</h2>
                    <div class="contact__body">
                        <div class="contact__photo">
                            <img src="img/contact/1.jpg" alt="Error">
                        </div>
                        <div class="contact__information">
                            <p class="contact__tel"><strong>Телефон:</strong>+7 (909) 543 16 23</p>
                            <p class="contact__email"><strong>Email:</strong>hostl@mail.ru</p>
                            <p class="vk"><strong>ВКонтакте</strong></p>
                        </div>
                    </div>
                </div>
            </div>

            <section class="otzyv__photo" id="otzyv__photo">
                <div class="container">
                    <div class="otz__body">
                        <h2 class="photo__title">Отзывы</h2>
                        <div class="otz__slider">
                            <div class="otz__slides">
                                <?php if (empty($reviews)): ?>
                                    <!-- Статические отзывы по умолчанию -->
                                    <input type="radio" name="rr" id="or1" checked>
                                    <input type="radio" name="rr" id="or2">
                                    <input type="radio" name="rr" id="or3">
                                    <input type="radio" name="rr" id="or4">
                                    <input type="radio" name="rr" id="or5">

                                    <div class="otz__slide slide1 a1">
                                        <div class="logo__otz"></div>
                                        <h4>Артём Кужлев</h4>
                                        <p>Прекрасно провёл время. Аккуратные, чистые номера и прекрасные хозяева!</p>
                                    </div>
                                    <div class="otz__slide slide2">
                                        <div class="logo__otz"></div>
                                        <h4>Лана Т.</h4>
                                        <p>Здравствуйте,Фортуна!Останавливались на ночлег по пути домой с 2 мопсами. Приветливый администратор,отличные условия проживания.Если снова будем проездом в Симферополе,то остановимся именно в этом хостеле. Желаем процветания!!! Алексей,Светлана+ питомцы (Москва)</p>
                                    </div>
                                    <div class="otz__slide slide3">
                                        <div class="logo__otz"></div>
                                        <h4>Станислав Г.</h4>
                                        <p>Хороший чистый хостел. Все есть, все на уровне!</p>
                                    </div>
                                    <div class="otz__slide slide4">
                                        <div class="logo__otz"></div>
                                        <h4>Марина</h4>
                                        <p>Плюсы: Всё прошло отлично. Очень рекомендую эту гостиницу. Расположение-тихое, место в 10 мин от вокзала. На территории есть частная парковка. Номера большие с комфортабельной кроватью, ортопедическим матрацом. Завтрак вполне приличный. Хорошее соотношение цены и качества</p>
                                    </div>
                                    <div class="otz__slide slide5">
                                        <div class="logo__otz"></div>
                                        <h4>Юлия</h4>
                                        <p>Плюсы: Хороший хостел! Чисто , спокойно . Останавливалась на одну ночь . Встретили с поздним заездом без проблем. Спасибо за все</p>
                                    </div>
                                <?php else: ?>
                                    <?php 
                                    $counter = 1;
                                    foreach($reviews as $review): 
                                    ?>
                                        <input type="radio" name="rr" id="or<?php echo $counter; ?>" <?php echo ($counter == 1) ? 'checked' : ''; ?>>
                                        <div class="otz__slide slide<?php echo $counter; ?> <?php echo ($counter == 1) ? 'a1' : ''; ?>">
                                            <div class="logo__otz"></div>
                                            <h4><?php echo htmlspecialchars($review['name']); ?></h4>
                                            <p><?php echo htmlspecialchars($review['review']); ?></p>
                                            <small style="color:#999;"><?php echo $review['created_at']; ?></small>
                                        </div>
                                    <?php 
                                    $counter++;
                                    endforeach; 
                                    ?>
                                <?php endif; ?>
                            </div>

                            <div class="information">
                                <?php if (empty($reviews)): ?>
                                    <label for="or1" class="otz__rad"></label>
                                    <label for="or2" class="otz__rad"></label>
                                    <label for="or3" class="otz__rad"></label>
                                    <label for="or4" class="otz__rad"></label>
                                    <label for="or5" class="otz__rad"></label>
                                <?php else: ?>
                                    <?php for($i = 1; $i <= count($reviews); $i++): ?>
                                        <label for="or<?php echo $i; ?>" class="otz__rad"></label>
                                    <?php endfor; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <?php elseif ($page == 'login'): ?>
            <!-- Страница входа -->
            <section class="main__menu">
                <div class="container">
                    <div class="menu__body" style="padding:50px 0;">
                        <h1 class="menu__title">Авторизация</h1>
                        <?php if (isset($error)) echo "<p style='color:red;padding:10px;'>$error</p>"; ?>
                        <form method="POST" style="max-width:400px;margin:0 auto;text-align:center;">
                            <input type="text" name="login" placeholder="Логин" required style="width:100%;padding:12px;margin:10px 0;border:1px solid #ddd;border-radius:5px;">
                            <input type="password" name="password" placeholder="Пароль" required style="width:100%;padding:12px;margin:10px 0;border:1px solid #ddd;border-radius:5px;">
                            <input type="submit" name="login_btn" value="Войти" style="width:100%;padding:12px;background:#2c3e50;color:#fff;border:none;border-radius:5px;cursor:pointer;">
                        </form>
                        <p style="text-align:center;margin-top:20px;"><strong>Логин: admin, Пароль: admin</strong></p>
                        <p style="text-align:center;"><a href="?page=home">На главную</a></p>
                    </div>
                </div>
            </section>

            <?php elseif ($page == 'admin'): ?>
            <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin'): ?>
                <section class="main__menu">
                    <div class="container">
                        <div class="menu__body" style="padding:50px 0;">
                            <h1 class="menu__title">Доступ запрещен</h1>
                            <p style="text-align:center;">У вас нет прав доступа! <a href="?page=login">Войти</a></p>
                        </div>
                    </div>
                </section>
            <?php else: ?>
                <!-- Админ-панель -->
                <section class="main__menu">
                    <div class="container">
                        <div class="menu__body" style="padding:50px 0;">
                            <h1 class="menu__title">Админ-панель</h1>
                            <p style="text-align:center;">Добро пожаловать, <strong><?php echo $_SESSION['user_login']; ?></strong>!</p>
                            
                            <!-- Добавление отзыва -->
                            <div style="background:#f9f9f9;padding:30px;margin:20px 0;border-radius:10px;">
                                <h2 style="text-align:center;">Добавить отзыв</h2>
                                <?php if (isset($msg_review)) echo "<p style='color:green;text-align:center;'>$msg_review</p>"; ?>
                                <form method="POST" style="max-width:500px;margin:0 auto;">
                                    <input type="text" name="name" placeholder="Имя" required style="width:100%;padding:12px;margin:10px 0;border:1px solid #ddd;border-radius:5px;">
                                    <textarea name="review" placeholder="Отзыв" required style="width:100%;padding:12px;margin:10px 0;border:1px solid #ddd;border-radius:5px;height:100px;"></textarea>
                                    <input type="submit" name="add_review_btn" value="Добавить отзыв" style="width:100%;padding:12px;background:#27ae60;color:#fff;border:none;border-radius:5px;cursor:pointer;">
                                </form>
                            </div>

                            <!-- Добавление номера -->
                            <div style="background:#f9f9f9;padding:30px;margin:20px 0;border-radius:10px;">
                                <h2 style="text-align:center;">Добавить номер</h2>
                                <?php if (isset($msg_room)) echo "<p style='color:green;text-align:center;'>$msg_room</p>"; ?>
                                <form method="POST" style="max-width:500px;margin:0 auto;">
                                    <input type="text" name="title" placeholder="Название номера" required style="width:100%;padding:12px;margin:10px 0;border:1px solid #ddd;border-radius:5px;">
                                    <textarea name="description" placeholder="Описание" required style="width:100%;padding:12px;margin:10px 0;border:1px solid #ddd;border-radius:5px;height:100px;"></textarea>
                                    <input type="text" name="image" placeholder="Ссылка на картинку" style="width:100%;padding:12px;margin:10px 0;border:1px solid #ddd;border-radius:5px;">
                                    <input type="submit" name="add_room_btn" value="Добавить номер" style="width:100%;padding:12px;background:#2980b9;color:#fff;border:none;border-radius:5px;cursor:pointer;">
                                </form>
                            </div>

                            <!-- Все отзывы -->
                            <div style="background:#f9f9f9;padding:30px;margin:20px 0;border-radius:10px;">
                                <h2 style="text-align:center;">Все отзывы (<?php echo count($reviews); ?>)</h2>
                                <?php if (empty($reviews)): ?>
                                    <p style="text-align:center;">Нет отзывов</p>
                                <?php else: ?>
                                    <?php foreach($reviews as $review): ?>
                                        <div style="border:1px solid #ddd;padding:15px;margin:10px 0;border-radius:5px;background:#fff;">
                                            <h4><?php echo htmlspecialchars($review['name']); ?></h4>
                                            <p><?php echo htmlspecialchars($review['review']); ?></p>
                                            <small style="color:#999;"><?php echo $review['created_at']; ?></small>
                                            <br>
                                            <a href="?page=admin&delete_review=<?php echo $review['id']; ?>" 
                                               style="padding:5px 15px;background:#dc3545;color:#fff;border:none;border-radius:5px;cursor:pointer;text-decoration:none;display:inline-block;margin-top:10px;"
                                               onclick="return confirm('Удалить отзыв?')">Удалить</a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Все номера -->
                            <div style="background:#f9f9f9;padding:30px;margin:20px 0;border-radius:10px;">
                                <h2 style="text-align:center;">Все номера (<?php echo count($rooms); ?>)</h2>
                                <?php if (empty($rooms)): ?>
                                    <p style="text-align:center;">Нет номеров</p>
                                <?php else: ?>
                                    <?php foreach($rooms as $room): ?>
                                        <div style="border:1px solid #ddd;padding:15px;margin:10px 0;border-radius:5px;background:#fff;">
                                            <h4><?php echo htmlspecialchars($room['title']); ?></h4>
                                            <p><?php echo htmlspecialchars($room['description']); ?></p>
                                            <?php if (!empty($room['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($room['image']); ?>" style="max-width:200px;">
                                            <?php endif; ?>
                                            <small style="color:#999;"><?php echo $room['created_at']; ?></small>
                                            <br>
                                            <a href="?page=admin&delete_room=<?php echo $room['id']; ?>" 
                                               style="padding:5px 15px;background:#dc3545;color:#fff;border:none;border-radius:5px;cursor:pointer;text-decoration:none;display:inline-block;margin-top:10px;"
                                               onclick="return confirm('Удалить номер?')">Удалить</a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <p style="text-align:center;"><a href="?page=home">На главную</a></p>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
            <?php endif; ?>
        </main>

        <footer class="footer">
            <div class="container">
                <div class="footer__body">
                    <h2>ЭКО ОТЕЛЬ</h2>
                    <button class="select">Забронировать</button>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
<?php
// Закрываем подключение
mysqli_close($conn);
?>



-- var1.php - Hostel (хостел)
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

-- Таблица отзывов
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    review TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица номеров
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Добавляем админа
INSERT INTO users (login, password, role) VALUES ('admin', 'admin', 'admin');

-- Тестовые отзывы
INSERT INTO reviews (name, review) VALUES 
('Артём Кужлев', 'Прекрасно провёл время. Аккуратные, чистые номера и прекрасные хозяева!'),
('Лана Т.', 'Отличные условия проживания. Если снова будем проездом, остановимся именно здесь.'),
('Станислав Г.', 'Хороший чистый хостел. Все есть, все на уровне!');

-- Тестовые номера
INSERT INTO rooms (title, description) VALUES 
('Комната для компании', 'Комната для четырех человек. Две двухуровневые кровати. В комнате есть рабочий стол, зеркало, диванчик и телевизор.'),
('Комната повышенной комфортности', 'Комната для одного или двух человек. Двуспальная кровать. В комнате есть холодильник, микроволновка, чайник, телевизор.');