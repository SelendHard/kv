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

// Добавление категории
if (isset($_POST['add_category_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $name = trim($_POST['category_name']);
    $description = trim($_POST['category_description']);
    $image = trim($_POST['category_image']);
    
    if ($name && $description) {
        $name = mysqli_real_escape_string($conn, $name);
        $description = mysqli_real_escape_string($conn, $description);
        $image = mysqli_real_escape_string($conn, $image);
        
        mysqli_query($conn, "INSERT INTO categories (name, description, image) VALUES ('$name', '$description', '$image')");
        $msg_cat = 'Категория добавлена!';
    } else {
        $msg_cat = 'Заполните все поля!';
    }
}

// Добавление мотоцикла
if (isset($_POST['add_moto_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $category_id = intval($_POST['category_id']);
    $name = trim($_POST['moto_name']);
    $price = trim($_POST['moto_price']);
    $characteristic = trim($_POST['moto_characteristic']);
    $image = trim($_POST['moto_image']);
    $link = trim($_POST['moto_link']);
    
    if ($category_id && $name && $price) {
        $name = mysqli_real_escape_string($conn, $name);
        $price = mysqli_real_escape_string($conn, $price);
        $characteristic = mysqli_real_escape_string($conn, $characteristic);
        $image = mysqli_real_escape_string($conn, $image);
        $link = mysqli_real_escape_string($conn, $link);
        
        mysqli_query($conn, "INSERT INTO motos (category_id, name, price, characteristic, image, link) 
                            VALUES ('$category_id', '$name', '$price', '$characteristic', '$image', '$link')");
        $msg_moto = 'Мотоцикл добавлен!';
    } else {
        $msg_moto = 'Заполните все поля!';
    }
}

// Удаление категории
if (isset($_GET['delete_category']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $id = intval($_GET['delete_category']);
    mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
    header('Location: ?page=admin');
    exit;
}

// Удаление мотоцикла
if (isset($_GET['delete_moto']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $id = intval($_GET['delete_moto']);
    mysqli_query($conn, "DELETE FROM motos WHERE id=$id");
    header('Location: ?page=admin');
    exit;
}

// Получаем все категории и мотоциклы
$categories = [];
$result_cat = mysqli_query($conn, "SELECT * FROM categories ORDER BY id");
while ($row = mysqli_fetch_assoc($result_cat)) {
    $categories[] = $row;
}

$motos = [];
$result_moto = mysqli_query($conn, "SELECT * FROM motos ORDER BY id");
while ($row = mysqli_fetch_assoc($result_moto)) {
    $motos[] = $row;
}

// Определяем страницу
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="main_page.css">
    <title>Moto Moto</title>
</head>

<body>
    <header>
        <div class="header_menu">
            <img src="img/moto_moto_logo-02.svg" alt="" class="logo">
            <div class="header_links">
                <a href="for_client.html" class="link1">Покупателю</a>
                <a href="about_us.html" class="link2">О нас</a>
                <a href="form.php" class="link3">Обратная связь</a>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                    <a href="?page=admin" class="link4" style="color:red;font-weight:bold;">Админ</a>
                    <a href="?logout=1" class="link4" style="color:red;font-weight:bold;">Выйти</a>
                <?php else: ?>
                    <a href="?page=login" class="link4">Войти</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if ($page == 'home'): ?>
    <div class="main_information">
        <p class="main_header">Мотоциклы</p>
        <div class="main_links">
            <?php if (empty($categories)): ?>
                <a href="classic_moto.html" class="main_link">Классические мотоциклы</a>
                <a href="sport_moto.html" class="main_link">Спортивные мотоциклы</a>
                <a href="choppers_moto.html" class="main_link">Чопперы</a>
                <a href="turrers_moto.html" class="main_link">Туреры</a>
                <a href="skuters_moto.html" class="main_link1">Скутеры</a>
            <?php else: ?>
                <?php foreach($categories as $cat): ?>
                    <a href="?page=category&id=<?php echo $cat['id']; ?>" class="main_link">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="main_info">
            <?php if (empty($categories)): ?>
                <!-- Статические категории -->
                <div class="about_moto">
                    <div class="test">
                        <div>
                            <p class="moto">Классические мотоциклы</p>
                            <p class="info_moto">Классический мотоцикл это проверенный <br>временем дизайн и компоновка,
                                прямая
                                посадка <br>и неприхотливость в обслуживании, надежность <br>и удобство в эксплуатации.
                                Внешне
                                он
                                может <br>быть
                                немного похож как на чоппер, но не с <br>такой вальяжной посадкой водителя и тоннами
                                <br>хрома,
                                так
                                и на
                                спортбайк, только с более <br>спокойными характеристиками двигателя и <br>длинными
                                тяговитыми
                                передачами.</p>
                        </div>
                        <img src="img/try.png" alt="" class="moto_img">
                    </div>
                    <div class="examples">
                        <div class="moto_example1">
                            <a href="jawa_info.php" class="check"> 
                                <p class="header_moto">JAWA 350 Premier</p>
                                <img src="img/s1200.png" alt="" class="JAWA">
                                <p class="price">от 290 000 ₽</p>
                                <p class="characteristic">397 куб.см, 23 л.с, 30.6 Н*м, 160 кг,<br>
                                    кикстартер и раздельная система смазки</p>
                            </a>
                        </div>
                        <div class="moto_example2">
                            <a href="guzzi_info.php" class="check">
                                <p class="header_moto">Moto Guzzi V7 III ANNIVERSARIO</p>
                                <img src="img/guzzi.jpg" alt="" class="Guzzi">
                                <p class="price">от 859 000 ₽</p>
                                <p class="characteristic">744 куб.см, 52 л.с, 60 Н*м<br>
                                    4900 об/мин,189 кг</p>
                            </a>
                        </div>
                    </div>
                    <div class="button">
                        <a href="classic_moto.html"><input type="button" name="submit" id="" value="Остальные модели" class="sent"></a>
                    </div>
                </div>

                <div class="about_moto">
                    <div class="test">
                        <div>
                            <p class="moto">Спортивные мотоциклы</p>
                            <p class="info_moto">Мотоциклы, которые дают ощущение свободы <br>своим обладателям, находясь на
                                витых дорогах <br>города или гладком асфальте специальных <br>треков. Благодаря совершенным
                                тормозным <br>системам, они способны менее чем за 3 секунды <br>молниеносно разогнаться с
                                места до
                                100 км/ч, <br>увеличить скорость в разы и с легкостью <br>остановиться.</p>
                        </div>
                        <img src="img/sport 1.png" alt="" class="moto_img">
                    </div>
                    <div class="examples">
                        <div class="sport_moto">
                            <a href="r1_moto.php" class="check">
                                <p class="header_moto">YZF-R1</p>
                                <img src="img/moto1.jpg" alt="" class="sportmoto">
                                <p class="price">от 290 000 ₽</p>
                                <p class="characteristic">397 куб.см, 23 л.с, 30.6 Н*м, 160 кг,<br>
                                    кикстартер и раздельная система смазки</p>
                            </a>
                        </div>
                        <div class="moto_example2">
                            <a href="r3_moto.php" class="check">
                                <p class="header_moto">YZF-R3</p>
                                <img src="img/mtoo31.jpg" alt="" class="sportmoto2">
                                <p class="price">от 429 000 ₽</p>
                                <p class="characteristic">321 куб. см, 29 Н*м 10 750 об/мин, 201 кг,<br>
                                    3,8 л/100 км, транзисторная система<br> зажигания TCI</p>
                            </a>
                        </div>
                    </div>
                    <div class="button">
                        <a href="sport_moto.html"><input type="button" name="submit" id="" value="Остальные модели" class="sent"></a>
                    </div>
                </div>

                <div class="about_moto">
                    <div class="test">
                        <div>
                            <p class="moto">Чопперы</p>
                            <p class="info_moto">Мощные тяжёлые мотоциклы для неспешных<br> поездок по городу. Пластиковых
                                обтекателей ни<br> имеют. Обеспечивают удобную прямую и<br> низкую посадку «ногами вперёд»
                                за счёт<br>
                                вынесенных вперёд подножек. Отличаются<br> низкооборотистыми двигателями. Ценятся<br>
                                владельцами за
                                возможность легко и вальяжно<br> кататься, наблюдая окрестности и показывая<br> себя.</p>
                        </div>
                        <img src="img/harley1.png" alt="" class="moto_img">
                    </div>
                    <div class="examples">
                        <div class="moto_example1">
                            <a href="forty_moto.php" class="check">
                                <p class="header_moto">FORTY-EIGHT</p>
                                <img src="img/chopper.PNG" alt="" class="JAWA">
                                <p class="price">от 1 499 000 ₽</p>
                                <p class="characteristic">1 202 куб. см, 96 Н*м 11 500 об/мин, 252кг,<br> Электронная
                                    система
                                    последовательного<br> впрыска топлива</p>
                            </a>
                        </div>
                        <div class="moto_example2">
                            <a href="superflow_moto.php" class="check">
                                <p class="header_moto">SUPERFLOW 1200T</p>
                                <img src="img/superflow.PNG" alt="" class="Guzzi">
                                <p class="price">от 429 000 ₽</p>
                                <p class="characteristic">321 куб. см, 96 Н*м 10 750 об/мин, 263 кг,<br>
                                    17 л, электронная система<br> последовательного впрыска топлива</p>
                            </a>
                        </div>
                    </div>
                    <div class="button">
                        <a href="choppers_moto.html"><input type="button" name="submit" id="" value="Остальные модели" class="sent"></a>
                    </div>
                </div>

                <div class="about_moto">
                    <div class="test">
                        <div>
                            <p class="moto">Туреры</p>
                            <p class="info_moto">Мотоциклы, предназначенные для путешествий<br> на большие расстояния.
                                Отличаются<br> комфортной посадкой, топливными баками<br> большой ёмкости,
                                низкооборотистыми<br>
                                двигателями, крупными размерами.<br> Управляемость приносится в жертву комфорту.<br>
                                Туристические
                                мотоциклы имеют большое<br> количество дополнительного оборудования:<br> кондиционеры,
                                магнитолы,
                                подушки<br> безопасности.</p>
                        </div>
                        <img src="img/turers1.png" alt="" class="moto_img">
                    </div>
                    <div class="examples">
                        <div class="moto_example1">
                            <a href="limited_moto.php" class="check">
                                <p class="header_moto">ULTRA LIMITED</p>
                                <img src="img/turer.PNG" alt="" class="JAWA">
                                <p class="price">от 2 563 000 ₽</p>
                                <p class="characteristic">1 202 куб. см, 164 Н*м, 399кг, электронная<br> система
                                    последовательного впрыска<br> топлива</p>
                            </a>
                        </div>
                        <div class="moto_example2">
                            <a href="glide_moto.php" class="check">
                                <p class="header_moto">ROAD GLIDE SPECIAL</p>
                                <img src="img/turer2.PNG" alt="" class="Guzzi">
                                <p class="price">от 2 434 000 ₽</p>
                                <p class="characteristic">1 868 куб. см, 163 Н*м, 371 кг, 22.7 л,<br> электронная система
                                    последовательного<br> впрыска топлива</p>
                            </a>
                        </div>
                    </div>
                    <div class="button">
                        <a href="turrers_moto.html"><input type="button" name="submit" id="" value="Остальные модели" class="sent"></a>
                    </div>
                </div>

                <div class="about_moto">
                    <div class="test">
                        <div>
                            <p class="moto">Скутеры</p>
                            <p class="info_moto">Настоящее спасение для того, кто нуждается в<br> транспорте, но не любит
                                стоять
                                в пробках. Это<br> техническое средство имеет нечто общее с<br> мотоциклом, но отличается
                                меньшей<br>
                                мощностью, размерами и весом.</p>
                        </div>
                        <img src="img/123.png" alt="" class="moto_img">
                    </div>
                    <div class="examples">
                        <div class="moto_example1">
                            <a href="tmax_moto.php" class="check">
                                <p class="header_moto">TMAX DX</p>
                                <img src="img/skuter.PNG" alt="" class="JAWA">
                                <p class="price">от 936 000 ₽</p>
                                <p class="characteristic">562 куб. см, 55 Н*м 5 250 об/мин, 220 кг,<br> транзисторная
                                    система
                                    зажигания TCI</p>
                            </a>
                        </div>
                        <div class="moto_example2">
                            <a href="nmax_moto.php" class="check">
                                <p class="header_moto">NMAX 150</p>
                                <img src="img/skuter2.PNG" alt="" class="Guzzi">
                                <p class="price">от 268 000 ₽</p>
                                <p class="characteristic">155 куб. см, 14 Н*м 6 000 об/мин, 127 кг,<br> 6.6 л, транзисторная
                                    система зажигания TCI</p>
                            </a>
                        </div>
                    </div>
                    <div class="button">
                        <a href="skuters_moto.html"><input type="button" name="submit" id="" value="Остальные модели" class="sent"></a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Динамические категории из БД -->
                <?php foreach($categories as $cat): ?>
                    <div class="about_moto">
                        <div class="test">
                            <div>
                                <p class="moto"><?php echo htmlspecialchars($cat['name']); ?></p>
                                <p class="info_moto"><?php echo nl2br(htmlspecialchars($cat['description'])); ?></p>
                            </div>
                            <?php if (!empty($cat['image'])): ?>
                                <img src="<?php echo htmlspecialchars($cat['image']); ?>" alt="" class="moto_img">
                            <?php else: ?>
                                <img src="img/try.png" alt="" class="moto_img">
                            <?php endif; ?>
                        </div>
                        <div class="examples">
                            <?php 
                            $cat_motos = array_filter($motos, function($m) use ($cat) {
                                return $m['category_id'] == $cat['id'];
                            });
                            $moto_counter = 0;
                            foreach($cat_motos as $moto): 
                                $moto_counter++;
                                if ($moto_counter > 2) break;
                            ?>
                                <div class="moto_example<?php echo $moto_counter; ?>">
                                    <a href="<?php echo !empty($moto['link']) ? htmlspecialchars($moto['link']) : '#'; ?>" class="check">
                                        <p class="header_moto"><?php echo htmlspecialchars($moto['name']); ?></p>
                                        <?php if (!empty($moto['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($moto['image']); ?>" alt="" class="<?php echo ($moto_counter == 1) ? 'JAWA' : 'Guzzi'; ?>">
                                        <?php else: ?>
                                            <img src="img/s1200.png" alt="" class="<?php echo ($moto_counter == 1) ? 'JAWA' : 'Guzzi'; ?>">
                                        <?php endif; ?>
                                        <p class="price">от <?php echo htmlspecialchars($moto['price']); ?> ₽</p>
                                        <p class="characteristic"><?php echo nl2br(htmlspecialchars($moto['characteristic'])); ?></p>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="button">
                            <a href="?page=category&id=<?php echo $cat['id']; ?>">
                                <input type="button" value="Остальные модели" class="sent">
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($page == 'login'): ?>
    <!-- Страница входа -->
    <div class="main_information" style="padding:50px 0;">
        <div style="max-width:400px;margin:0 auto;padding:30px;background:#fff;border-radius:10px;box-shadow:0 5px 30px rgba(0,0,0,0.1);">
            <h1 style="text-align:center;margin-bottom:30px;">Авторизация</h1>
            <?php if (isset($error)): ?>
                <p style="color:red;text-align:center;padding:10px;background:#fde8e8;border-radius:5px;"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="login" placeholder="Логин" required style="width:100%;padding:12px;margin:10px 0;border:1px solid #ddd;border-radius:5px;">
                <input type="password" name="password" placeholder="Пароль" required style="width:100%;padding:12px;margin:10px 0;border:1px solid #ddd;border-radius:5px;">
                <button type="submit" name="login_btn" style="width:100%;padding:12px;background:#007bff;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:16px;">Войти</button>
            </form>
            <p style="text-align:center;margin-top:20px;"><strong>Логин: admin, Пароль: admin</strong></p>
            <p style="text-align:center;"><a href="?page=home">На главную</a></p>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($page == 'admin'): ?>
    <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin'): ?>
        <div class="main_information" style="text-align:center;padding:50px 0;">
            <h1>Доступ запрещен</h1>
            <p>У вас нет прав доступа! <a href="?page=login">Войти</a></p>
        </div>
    <?php else: ?>
        <!-- Админ-панель -->
        <div class="main_information">
            <div style="max-width:1200px;margin:0 auto;padding:20px;">
                <h1 style="text-align:center;">Админ-панель</h1>
                <p style="text-align:center;">Добро пожаловать, <strong><?php echo $_SESSION['user_login']; ?></strong>!</p>
                
                <!-- Добавление категории -->
                <div style="background:#f9f9f9;padding:20px;border-radius:10px;margin-bottom:30px;border:1px solid #ddd;">
                    <h2>Добавить категорию</h2>
                    <?php if (isset($msg_cat)): ?>
                        <p style="color:green;"><?php echo $msg_cat; ?></p>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="text" name="category_name" placeholder="Название категории" required style="width:100%;padding:10px;margin:10px 0;border:1px solid #ddd;border-radius:5px;">
                        <textarea name="category_description" placeholder="Описание категории" rows="4" required style="width:100%;padding:10px;margin:10px 0;border:1px solid #ddd;border-radius:5px;"></textarea>
                        <input type="text" name="category_image" placeholder="Ссылка на картинку" style="width:100%;padding:10px;margin:10px 0;border:1px solid #ddd;border-radius:5px;">
                        <button type="submit" name="add_category_btn" style="padding:10px 30px;background:#28a745;color:#fff;border:none;border-radius:5px;cursor:pointer;">Добавить категорию</button>
                    </form>
                </div>

                <!-- Добавление мотоцикла -->
                <div style="background:#f9f9f9;padding:20px;border-radius:10px;margin-bottom:30px;border:1px solid #ddd;">
                    <h2>Добавить мотоцикл</h2>
                    <?php if (isset($msg_moto)): ?>
                        <p style="color:green;"><?php echo $msg_moto; ?></p>
                    <?php endif; ?>
                    <form method="POST">
                        <select name="category_id" required style="width:100%;padding:10px;margin:10px 0;border:1px solid #ddd;border-radius:5px;">
                            <option value="">Выберите категорию</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="moto_name" placeholder="Название мотоцикла" required style="width:100%;padding:10px;margin:10px 0;border:1px solid #ddd;border-radius:5px;">
                        <input type="text" name="moto_price" placeholder="Цена (например: 290 000)" required style="width:100%;padding:10px;margin:10px 0;border:1px solid #ddd;border-radius:5px;">
                        <textarea name="moto_characteristic" placeholder="Характеристики" rows="4" style="width:100%;padding:10px;margin:10px 0;border:1px solid #ddd;border-radius:5px;"></textarea>
                        <input type="text" name="moto_image" placeholder="Ссылка на картинку" style="width:100%;padding:10px;margin:10px 0;border:1px solid #ddd;border-radius:5px;">
                        <input type="text" name="moto_link" placeholder="Ссылка на страницу" style="width:100%;padding:10px;margin:10px 0;border:1px solid #ddd;border-radius:5px;">
                        <button type="submit" name="add_moto_btn" style="padding:10px 30px;background:#28a745;color:#fff;border:none;border-radius:5px;cursor:pointer;">Добавить мотоцикл</button>
                    </form>
                </div>

                <!-- Список категорий -->
                <h2>Все категории (<?php echo count($categories); ?>)</h2>
                <?php if (empty($categories)): ?>
                    <p>Нет категорий</p>
                <?php else: ?>
                    <?php foreach($categories as $cat): ?>
                        <div style="border:1px solid #ddd;padding:15px;margin:10px 0;border-radius:5px;background:#fff;">
                            <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                            <p><?php echo htmlspecialchars($cat['description']); ?></p>
                            <?php if (!empty($cat['image'])): ?>
                                <img src="<?php echo htmlspecialchars($cat['image']); ?>" style="max-width:100px;">
                            <?php endif; ?>
                            <br>
                            <a href="?page=admin&delete_category=<?php echo $cat['id']; ?>" 
                               style="padding:5px 15px;background:#dc3545;color:#fff;border:none;border-radius:5px;cursor:pointer;text-decoration:none;display:inline-block;margin-top:10px;"
                               onclick="return confirm('Удалить категорию?')">Удалить</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Список мотоциклов -->
                <h2>Все мотоциклы (<?php echo count($motos); ?>)</h2>
                <?php if (empty($motos)): ?>
                    <p>Нет мотоциклов</p>
                <?php else: ?>
                    <?php foreach($motos as $moto): ?>
                        <div style="border:1px solid #ddd;padding:15px;margin:10px 0;border-radius:5px;background:#fff;">
                            <h4><?php echo htmlspecialchars($moto['name']); ?></h4>
                            <p><strong>Цена:</strong> <?php echo htmlspecialchars($moto['price']); ?> ₽</p>
                            <p><?php echo nl2br(htmlspecialchars($moto['characteristic'])); ?></p>
                            <?php if (!empty($moto['image'])): ?>
                                <img src="<?php echo htmlspecialchars($moto['image']); ?>" style="max-width:100px;">
                            <?php endif; ?>
                            <br>
                            <a href="?page=admin&delete_moto=<?php echo $moto['id']; ?>" 
                               style="padding:5px 15px;background:#dc3545;color:#fff;border:none;border-radius:5px;cursor:pointer;text-decoration:none;display:inline-block;margin-top:10px;"
                               onclick="return confirm('Удалить мотоцикл?')">Удалить</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <p style="text-align:center;margin-top:30px;"><a href="?page=home">На главную</a></p>
            </div>
        </div>
    <?php endif; ?>
    <?php endif; ?>

    <footer>
        <div class="footer_links">
            <div class="first_part">
                <p class="header">Мотоциклы</p><br>
                <a href="classic_moto.html" class="footer_link">Классические мотоциклы</a>
                <a href="sport_moto.html" class="footer_link">Спортивные мотоциклы</a><br>
                <div class="flink">
                <a href="choppers_moto.html" class="flink">Чопперы</a>
                <a href="turrers_moto.html" class="flink">Туреры</a>
                <a href="skuters_moto.html" class="flink">Скутеры</a>
            </div>
            </div>
            <div class="second_part">
                <div class="social">
                    <div class="hi">
                <a href="#" class="vektor"><img src="img/iconmonstr-vk-5.svg" alt=""></a>
                <a href="#" class="vektor"><img src="img/iconmonstr-facebook-5.svg" alt=""></a>
                <a href="#"><img src="img/iconmonstr-youtube-5.svg" alt=""></a>
                </div>
                </div>
                <div class="footer_button">
                    <a href="form.php"><input type="button" name="submit" id="" value="Обратная связь" class="button_footer"></a>
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

-- Таблица категорий
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица мотоциклов
CREATE TABLE IF NOT EXISTS motos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    price VARCHAR(100) NOT NULL,
    characteristic TEXT,
    image VARCHAR(255),
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Добавляем админа
INSERT INTO users (login, password, role) VALUES ('admin', 'admin', 'admin');

-- Добавляем категории
INSERT INTO categories (name, description, image) VALUES 
('Классические мотоциклы', 'Классический мотоцикл это проверенный временем дизайн и компоновка, прямая посадка и неприхотливость в обслуживании, надежность и удобство в эксплуатации.', 'img/try.png'),
('Спортивные мотоциклы', 'Мотоциклы, которые дают ощущение свободы своим обладателям, находясь на витых дорогах города или гладком асфальте специальных треков.', 'img/sport 1.png'),
('Чопперы', 'Мощные тяжёлые мотоциклы для неспешных поездок по городу. Пластиковых обтекателей не имеют. Обеспечивают удобную прямую и низкую посадку.', 'img/harley1.png'),
('Туреры', 'Мотоциклы, предназначенные для путешествий на большие расстояния. Отличаются комфортной посадкой, топливными баками большой ёмкости.', 'img/turers1.png'),
('Скутеры', 'Настоящее спасение для того, кто нуждается в транспорте, но не любит стоять в пробках. Техническое средство имеет нечто общее с мотоциклом.', 'img/123.png');

-- Добавляем мотоциклы
INSERT INTO motos (category_id, name, price, characteristic, image, link) VALUES 
(1, 'JAWA 350 Premier', '290 000', '397 куб.см, 23 л.с, 30.6 Н*м, 160 кг, кикстартер и раздельная система смазки', 'img/s1200.png', 'jawa_info.php'),
(1, 'Moto Guzzi V7 III ANNIVERSARIO', '859 000', '744 куб.см, 52 л.с, 60 Н*м, 4900 об/мин, 189 кг', 'img/guzzi.jpg', 'guzzi_info.php'),
(2, 'YZF-R1', '290 000', '397 куб.см, 23 л.с, 30.6 Н*м, 160 кг, кикстартер и раздельная система смазки', 'img/moto1.jpg', 'r1_moto.php'),
(2, 'YZF-R3', '429 000', '321 куб. см, 29 Н*м 10 750 об/мин, 201 кг, 3,8 л/100 км, TCI', 'img/mtoo31.jpg', 'r3_moto.php'),
(3, 'FORTY-EIGHT', '1 499 000', '1 202 куб. см, 96 Н*м 11 500 об/мин, 252 кг, электронная система впрыска', 'img/chopper.PNG', 'forty_moto.php'),
(3, 'SUPERFLOW 1200T', '429 000', '321 куб. см, 96 Н*м 10 750 об/мин, 263 кг, 17 л, электронная система впрыска', 'img/superflow.PNG', 'superflow_moto.php'),
(4, 'ULTRA LIMITED', '2 563 000', '1 202 куб. см, 164 Н*м, 399 кг, электронная система впрыска', 'img/turer.PNG', 'limited_moto.php'),
(4, 'ROAD GLIDE SPECIAL', '2 434 000', '1 868 куб. см, 163 Н*м, 371 кг, 22.7 л, электронная система впрыска', 'img/turer2.PNG', 'glide_moto.php'),
(5, 'TMAX DX', '936 000', '562 куб. см, 55 Н*м 5 250 об/мин, 220 кг, TCI', 'img/skuter.PNG', 'tmax_moto.php'),
(5, 'NMAX 150', '268 000', '155 куб. см, 14 Н*м 6 000 об/мин, 127 кг, 6.6 л, TCI', 'img/skuter2.PNG', 'nmax_moto.php');

-- Проверка данных
SELECT * FROM users;
SELECT * FROM categories;
SELECT * FROM motos;