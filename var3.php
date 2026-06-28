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
    
    // Проверяем в БД
    $result = mysqli_query($conn, "SELECT * FROM users WHERE login='$login' AND password='$pass'");
    $user = mysqli_fetch_assoc($result);
    
    if ($user) {
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

// Добавление неисправности (только для админа)
if (isset($_POST['add_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $image = trim($_POST['image']);
    
    if ($title && $description && $price) {
        $title = mysqli_real_escape_string($conn, $title);
        $description = mysqli_real_escape_string($conn, $description);
        $price = mysqli_real_escape_string($conn, $price);
        $image = mysqli_real_escape_string($conn, $image);
        
        mysqli_query($conn, "INSERT INTO repairs (title, description, price, image) VALUES ('$title', '$description', '$price', '$image')");
        $msg = 'Запись добавлена!';
    } else {
        $msg = 'Заполните все поля!';
    }
}

// Удаление записи (только для админа)
if (isset($_GET['delete']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM repairs WHERE id=$id");
    header('Location: ?page=admin');
    exit;
}

// Получаем все записи
$result = mysqli_query($conn, "SELECT * FROM repairs ORDER BY id DESC");
$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}

// Определяем страницу
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Дроид</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-panel {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .admin-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .admin-form input, .admin-form textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .admin-form button {
            width: 100%;
            padding: 12px;
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
        }
        .delete-btn:hover {
            background: #c82333;
        }
        .msg-success {
            color: green;
            text-align: center;
            padding: 10px;
            background: #d4edda;
            border-radius: 5px;
            margin: 10px 0;
        }
        .msg-error {
            color: red;
            text-align: center;
            padding: 10px;
            background: #f8d7da;
            border-radius: 5px;
            margin: 10px 0;
        }
        .login-form {
            max-width: 400px;
            margin: 0 auto;
            text-align: center;
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
        .section-function {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        function {
            display: block;
        }
        .ex1, .ex2, .ex3, .ex4 {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #007bff;
        }
        .ex1 img, .ex2 img, .ex3 img, .ex4 img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .ex2 { border-left-color: #28a745; }
        .ex3 { border-left-color: #ffc107; }
        .ex4 { border-left-color: #dc3545; }
    </style>
</head>
<body>
<header>
    <div class="color-header">
        <div class="text-logo">
            <img class="logo" src="./img/logo.svg" alt="logo">
            <h1 class="text-header">Дроид</h1>
        </div>
        <ul class="contact">
            <li><a href="tel: 8(3822)94-06-06">Тел.: 8(3822)94-06-06</a></li>
            <li><a href="mailto: service@2droida.ru">Почта.: service@2droida.ru</a></li>
            <li>Режим работы: Ежедневно, 10:00 — 20:00</li>
        </ul>
    </div>
    <ul>
        <li class="Whatsapp"><a href="whatsapp://send?phone=7903950645">Whatsapp</a></li>
        <li class="VK"><a href="https://vk.com/droid">Vkontakte</a></li>
        <li class="Instagram"><a href="https://instagram.com/droid">Instagram</a></li>
    </ul>
    <ul>
        <li><a href="?page=home">О нас</a></li>
        <li><a href="?page=home">Цены</a></li>
        <li><a href="?page=home">Контакты</a></li>
        <li><a href="?page=home">Вакансии</a></li>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
            <li><a href="?page=admin" style="color:red;">Админ</a></li>
            <li><a href="?logout=1" style="color:red;">Выйти (<?php echo $_SESSION['user_login']; ?>)</a></li>
        <?php else: ?>
            <li><a href="?page=login">Войти</a></li>
        <?php endif; ?>
    </ul>
</header>
<main>
    <?php if ($page == 'home'): ?>
    <h2 class="section-model">Бренды</h2>
    <section class="model">
        <img src="./img/honor.jpg" alt="honor">
        <img src="./img/huawei.jpg" alt="huawei">
        <img src="./img/meizu.jpg" alt="meizu">
        <img src="./img/mi.png" alt="mi">
        <img src="./img/oneplus.jpg" alt="oneplus">
    </section>
    
    <h2 class="section-zvonok">Заказать Звонок</h2>
    <section class="zvonok">
        <form class="form-button" method="POST">
            <input type="text" name="name" placeholder="Имя" required>
            <input type="tel" name="phone" placeholder="Телефон" required>
            <button class="button" type="submit">Позвонить</button>
        </form>
    </section>
    
    <h2 class="text-adv">Преимущества сервиса</h2>
    <section class="section-rem">
        <ul class="section-adv">
            <li>
                <strong>Минимальные сроки ремонта</strong><br>
                Благодаря большому опыту инженеров и наличию запасных частей на складе мы выполним ремонт быстро,
                качественно и недорого. До 80% заказов специалисты выполняют в день обращения.
            </li>
            <li>
                <strong>Бесплатная диагностика</strong><br>
                Диагностика в течении 5 минут. Инженеры на месте определят неполадки, стоимость ремонта и сколько
                времени это займет.
            </li>
            <li>
                <strong>Ответственность за результат</strong><br>
                Используем только качественные запасные части и строго соблюдаем технологию проведения ремонта.
                Предоставляем гарантию на выполненные работы и замененные комплектующие.
            </li>
            <li>
                <strong>Информирование</strong><br>
                Мы предоставляем нашим клиентам бесплатное sms-информирование о завершении ремонта. Специалисты нашего
                call-центра всегда готовы ответить на все Ваши вопросы.
            </li>
        </ul>
    </section>
    
    <section>
        <h2 class="section-text">Неисправности</h2>
        <div class="section-function">
            <?php if (empty($items)): ?>
                <!-- Статические неисправности по умолчанию -->
                <function>
                    <div class="ex1">
                        <img src="./img/1.png" alt="repair">
                        <strong>Замена дисплейного модуля</strong><br>
                        Разбит дисплей, не работает сенсор, не показывает экран<br>
                        <strong>от 1000</strong>
                    </div>
                </function>
                <function>
                    <div class="ex2">
                        <img src="./img/2.png" alt="repair">
                        <strong>Замена аккумулятора</strong><br>
                        Не держит заряд, выключается на холоде или вздулся аккумулятор.<br>
                        А так же, если попала влага или был сильный удар.<br>
                        <strong>от 2000</strong>
                    </div>
                </function>
                <function>
                    <div class="ex3">
                        <img src="./img/3.png" alt="repair">
                        <strong>Обновление программного обеспечения</strong><br>
                        Ошибки приложений Android, не работает Google Play, не открывается галерея, Не видит сеть, нет
                        русского языка.<br>
                        <strong>от 300</strong>
                    </div>
                </function>
                <function>
                    <div class="ex4">
                        <img src="./img/4.png" alt="repair">
                        <strong>Чистка от коррозии после попадания жидкости</strong><br>
                        Залит телефон, внутри влага, утопили.<br>
                        <strong>от 1000</strong>
                    </div>
                </function>
            <?php else: ?>
                <?php foreach($items as $item): ?>
                    <function>
                        <div class="ex<?php echo rand(1,4); ?>">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="repair">
                            <?php else: ?>
                                <img src="./img/1.png" alt="repair">
                            <?php endif; ?>
                            <strong><?php echo htmlspecialchars($item['title']); ?></strong><br>
                            <?php echo htmlspecialchars($item['description']); ?><br>
                            <strong>от <?php echo htmlspecialchars($item['price']); ?></strong>
                        </div>
                    </function>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php elseif ($page == 'login'): ?>
    <!-- Страница входа -->
    <section class="zvonok" style="padding:50px 0;">
        <h2 class="section-zvonok">Авторизация</h2>
        <?php if (isset($error)): ?>
            <div class="msg-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" class="login-form">
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
    </section>

    <?php elseif ($page == 'admin'): ?>
    <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin'): ?>
        <section class="zvonok" style="padding:50px 0;">
            <h2 class="section-zvonok">Доступ запрещен</h2>
            <p style="text-align:center;">У вас нет прав доступа! <a href="?page=login">Войти</a></p>
        </section>
    <?php else: ?>
        <!-- Админ-панель -->
        <section class="zvonok" style="padding:50px 0;">
            <h2 class="section-zvonok">Админ-панель</h2>
            <p style="text-align:center;">Добро пожаловать, <strong><?php echo $_SESSION['user_login']; ?></strong>!</p>
            
            <div class="admin-panel">
                <!-- Форма добавления -->
                <div class="admin-form">
                    <h3 style="text-align:center;margin-bottom:20px;">Добавить неисправность</h3>
                    <?php if (isset($msg)): ?>
                        <div class="msg-success"><?php echo $msg; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="text" name="title" placeholder="Название" required>
                        <textarea name="description" placeholder="Описание" required rows="4"></textarea>
                        <input type="text" name="price" placeholder="Цена (например: 1000)" required>
                        <input type="text" name="image" placeholder="Ссылка на картинку (необязательно)">
                        <button type="submit" name="add_btn">Добавить запись</button>
                    </form>
                </div>

                <!-- Список записей -->
                <h3 style="text-align:center;">Все неисправности (<?php echo count($items); ?>)</h3>
                <?php if (empty($items)): ?>
                    <p style="text-align:center;">Нет записей</p>
                <?php else: ?>
                    <?php foreach($items as $item): ?>
                        <div class="item-card">
                            <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                            <p><strong>Цена: от <?php echo htmlspecialchars($item['price']); ?></strong></p>
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="repair">
                            <?php endif; ?>
                            <br>
                            <small style="color:#999;">Добавлено: <?php echo $item['created_at']; ?></small>
                            <br>
                            <a href="?page=admin&delete=<?php echo $item['id']; ?>" 
                               class="delete-btn" 
                               onclick="return confirm('Удалить запись?')">Удалить</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <p style="text-align:center;margin-top:20px;">
                <a href="?page=home">На главную</a>
            </p>
        </section>
    <?php endif; ?>
    <?php endif; ?>
</main>
<footer>
    <h2 class="company">О компании</h2>
    <p>Сервисный центр DROID выполняет срочный, качественный и недорогой ремонт телефонов. Самые распространённые виды
        ремонта -это замена дисплея, антикоррозийная чистка при попадании влаги, замена аккумулятора и прошивка
        телефонов.

        Мы используем только качественные запасные части и строго соблюдаем технологию проведения ремонта. Мы
        предоставляем гарантию на выполненные работы и замененные комплектующие.
    </p>
    <ul class="footer-cont">
        <li>Наименование: ООО «С групп»</li>
        <li>Юридический адрес: 634057, г. Новосибирск, ул. 79 Гвардейской дивизии, 7-179</li>
        <li>ИНН: 7017417869</li>
        <li>КПП: 762701001</li>
    </ul>
</footer>
</body>
</html>
<?php
// Закрываем подключение
mysqli_close($conn);
?>


-- var3.php - Дроид (упрощенная версия)
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

-- Таблица неисправностей
CREATE TABLE IF NOT EXISTS repairs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price VARCHAR(50) NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Добавляем админа
INSERT INTO users (login, password, role) VALUES ('admin', 'admin', 'admin');

-- Тестовые неисправности
INSERT INTO repairs (title, description, price, image) VALUES 
('Замена дисплейного модуля', 'Разбит дисплей, не работает сенсор, не показывает экран', '1000', './img/1.png'),
('Замена аккумулятора', 'Не держит заряд, выключается на холоде или вздулся аккумулятор.', '2000', './img/2.png'),
('Обновление ПО', 'Ошибки приложений Android, не работает Google Play, не открывается галерея.', '300', './img/3.png'),
('Чистка от коррозии', 'Залит телефон, внутри влага, утопили.', '1000', './img/4.png');