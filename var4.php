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

// Вход (с проверкой через БД)
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

// Добавление отзыва (только для админа)
if (isset($_POST['add_feedback_btn']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $name = trim($_POST['name']);
    $title = trim($_POST['title']);
    $text = trim($_POST['text']);
    $date = trim($_POST['date']);
    
    if ($name && $title && $text) {
        $name = mysqli_real_escape_string($conn, $name);
        $title = mysqli_real_escape_string($conn, $title);
        $text = mysqli_real_escape_string($conn, $text);
        $date = mysqli_real_escape_string($conn, $date);
        
        mysqli_query($conn, "INSERT INTO feedback (name, title, text, date) VALUES ('$name', '$title', '$text', '$date')");
        $msg_feedback = 'Отзыв добавлен!';
    } else {
        $msg_feedback = 'Заполните все поля!';
    }
}

// Удаление неисправности
if (isset($_GET['delete_repair']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $id = intval($_GET['delete_repair']);
    mysqli_query($conn, "DELETE FROM repairs WHERE id=$id");
    header('Location: ?page=admin');
    exit;
}

// Удаление отзыва
if (isset($_GET['delete_feedback']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    $id = intval($_GET['delete_feedback']);
    mysqli_query($conn, "DELETE FROM feedback WHERE id=$id");
    header('Location: ?page=admin');
    exit;
}

// Получаем все неисправности
$result = mysqli_query($conn, "SELECT * FROM repairs ORDER BY id DESC");
$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}

// Получаем все отзывы
$result_feedback = mysqli_query($conn, "SELECT * FROM feedback ORDER BY id DESC");
$feedback_items = [];
while ($row = mysqli_fetch_assoc($result_feedback)) {
    $feedback_items[] = $row;
}

// Определяем страницу
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	<title>Droid</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
	<header class="header div_blue">
		<div class="container">
			<div class="header__inner">
				<div class="header__logo-block">
					<img class="header__logo" alt="logo" src="media/Images/logo.svg">
				</div>
				<nav class="header__menu">
					<a href="?page=home" class="menu__item">О нас</a>
					<a href="?page=home" class="menu__item">Цены</a>
					<a href="?page=home" class="menu__item">Контакты</a>
					<a href="?page=home" class="menu__item">Вакансии</a>
					<?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
						<a href="?page=admin" class="menu__item" style="color:red;">Админ</a>
						<a href="?logout=1" class="menu__item" style="color:red;">Выйти (<?php echo $_SESSION['user_login']; ?>)</a>
					<?php else: ?>
						<a href="?page=login" class="menu__item">Войти</a>
					<?php endif; ?>
				</nav>
				<div class="header__contacts">
					<a href="tel:8(3822)94-06-06" class="contacts__item">Телефон</a>
					<a href="mailto:service@2droida.ru" class="contacts__item">E-mail</a>
					<a href="//send?phone=7903950645" class="contacts__item">Whatsapp</a>
					<a href="//vk.com/droid" class="contacts__item">VK</a>
					<a href="//instagram.com/droid" class="contacts__item">Instagram</a>
				</div>
				<div class="header__schedule-block">
					<span class="header__schedule">Ежедневно<br>10:00 — 20:00</span>
				</div>
			</div>
		</div>
	</header>

	<?php if ($page == 'home'): ?>
	<!-- Call блок -->
	<div class="call">
		<div class="container">
			<div class="call__inner inner">
				<div class="inner__title-block">
					<h1 class="inner__title">Заказать звонок</h1>
				</div>
				<div class="inner__content">
					<form class="inner__form">
						<input type="text" class="form__input form__item" placeholder="Введите имя">
						<input type="text" class="form__input form__item" placeholder="Введите телефон">
						<input type="submit" class="form__submit form__item" value="Позвонить мне!">
					</form>
					<div class="inner__companies">
						<div class="inner__title-block">
							<h2 class="inner__title">Мы работаем с такими фирмами как</h2>
						</div>
						<div class="companies__content">
							<div class="company__item-block">
								<img class="company__item" alt="company" src="media/Content/firms/honor.jpg">
							</div>
							<div class="company__item-block">
								<img class="company__item" alt="company" src="media/Content/firms/huawei.jpg">
							</div>
							<div class="company__item-block">
								<img class="company__item" alt="company" src="media/Content/firms/meizu.jpg">
							</div>
							<div class="company__item-block">
								<img class="company__item" alt="company" src="media/Content/firms/mi.png">
							</div>
							<div class="company__item-block">
								<img class="company__item" alt="company" src="media/Content/firms/oneplus.jpg">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Преимущества -->
	<div class="advantages div_blue">
		<div class="container">
			<div class="advantages__inner inner">
				<div class="inner__title-block">
					<h1 class="inner__title">Преимущества нашего сервиса</h1>
				</div>
				<div class="inner__content">
					<div class="advantages__item-block">
						<div class="advantages__title-block">
							<h2 class="advantages__title">Минимальные сроки ремонта</h2>
						</div>
						<div class="advantages__text-block">
							<p class="advantages__text">Благодаря большому опыту инженеров и наличию запасных частей на складе мы выполним ремонт быстро, качественно и недорого. До 80% заказов специалисты выполняют в день обращения.</p>
						</div>
					</div>
					<div class="advantages__item-block">
						<div class="advantages__title-block">
							<h2 class="advantages__title">Бесплатная диагностика</h2>
						</div>
						<div class="advantages__text-block">
							<p class="advantages__text">Диагностика в течении 5 минут. Инженеры на месте определят неполадки, стоимость ремонта и сколько времени это займет.</p>
						</div>
					</div>
					<div class="advantages__item-block">
						<div class="advantages__title-block">
							<h2 class="advantages__title">Ответственность за результат</h2>
						</div>
						<div class="advantages__text-block">
							<p class="advantages__text">Используем только качественные запасные части и строго соблюдаем технологию проведения ремонта. Предоставляем гарантию на выполненные работы и замененные комплектующие.</p>
						</div>
					</div>
					<div class="advantages__item-block">
						<div class="advantages__title-block">
							<h2 class="advantages__title">Информирование</h2>
						</div>
						<div class="advantages__text-block">
							<p class="advantages__text">Мы предоставляем нашим клиентам бесплатное sms-информирование о завершении ремонта. Специалисты нашего call-центра всегда готовы ответить на все Ваши вопросы.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Примеры неисправностей (из БД) -->
	<div class="examples">
		<div class="container">
			<div class="inner">
				<div class="inner__title-block">
					<h1 class="inner__title">Примеры неисправностей</h1>
				</div>
				<div class="inner__content">
					<?php if (empty($items)): ?>
						<p style="padding:20px;text-align:center;">Нет записей</p>
					<?php else: ?>
						<?php foreach($items as $item): ?>
							<div class="examples__item">
								<div class="examples__image-block">
									<?php if (!empty($item['image'])): ?>
										<img src="<?php echo htmlspecialchars($item['image']); ?>" class="examples__image" alt="phone">
									<?php else: ?>
										<img src="media/Content/brokes/1.png" class="examples__image" alt="phone">
									<?php endif; ?>
								</div>
								<div class="examples__description">
									<div class="examples__title-block">
										<h2 class="examples__title"><?php echo htmlspecialchars($item['title']); ?></h2>
										<span class="examples__price">от <?php echo htmlspecialchars($item['price']); ?></span>
									</div>
									<div class="exaples__text-block">
										<p class="examples__text"><?php echo htmlspecialchars($item['description']); ?></p>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>	
			</div>
		</div>
	</div>

	<!-- Цены -->
	<div class="prices div_blue">
		<div class="prices__container container">
			<div class="inner">
				<div class="inner__title-block">
					<h1 class="inner__title">Цены на наши услуги</h1>
				</div>
				<div class="inner__content">
					<div class="slider">
						<span class="slider__switch">&larr;</span>
						<div class="slider__content">
							<div class="slider__title-block">
								<h2 class="slider__title">Пересборка</h2>
							</div>
							<table border=1 frame="void" cellspacing="5" class="slider__table">
								<thead class="table__head">
									<tr class="table__row table__head-row">
										<th class="table__cell table__head-cell">Базовая цена</th>
										<th class="table__cell table__head-cell">Средняя сложность</th>
										<th class="table__cell table__head-cell">Сложный ремонт</th>
									</tr>
								</thead>
								<tbody class="table__body">
									<tr class="table__row">
										<td class="table__cell">200</td>
										<td class="table__cell">350</td>
										<td class="table__cell">500</td>
									</tr>
								</tbody>
							</table>
						</div>
						<span class="slider__switch">&rarr;</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Контакты -->
	<div class="contacts">
		<div class="container">
			<div class="inner">
				<div class="inner__title-block">
					<h1 class="inner__title">Связь с нами</h1>
				</div>
				<div class="inner__content">
					<div class="contacts__item-block">
						<a href="tel:8(3822)94-06-06" class="contacts__link">Телефон</a>
						<a href="mailto:service@2droida.ru" class="contacts__link">E-mail</a>
						<a href="//send?phone=7903950645" class="contacts__link">Whatsapp</a>
						<a href="//vk.com/droid" class="contacts__link">VKontakte</a>
						<a href="//instagram.com/droid" class="contacts__link">Instagram</a>
					</div>
				</div>	
			</div>
		</div>
	</div>

	<!-- Отзывы (из БД) -->
	<div class="feedback div_blue">
		<div class="container">
			<div class="inner">
				<div class="inner__title-block">
					<h1 class="inner__title">Отзывы о нас</h1>
				</div>
				<div class="inner__content">
					<div class="slider">
						<span class="slider__switch">&larr;</span>
						<div class="feedback-slider__content">
							<?php if (empty($feedback_items)): ?>
								<div class="feedback-slider__title-block">
									<span class="slider__title">Георгий Леонидович</span>
									<span class="feedback__date">01.10.2019</span>
								</div>
								<div class="feedback__item">
									<h3 class="feedback__name">Замена АКБ Sony Xperia Z2</h3>
									<p class="feedback__text">24 сентября 2019 г обратился для замены АКБ в своем телефоне. НН Ленина 127. Мастер Сазанов А.Е. все адекватно объяснил, показал и рассказал как будет проходить ремонт телефона. Назвал стоимость за батарею и замену АКБ. Назвал сроки. Все сделано качественно и в срок. Приятно с Вами работать. Спасибо.</p>
								</div>
							<?php else: ?>
								<?php foreach($feedback_items as $feedback): ?>
									<div class="feedback-slider__title-block">
										<span class="slider__title"><?php echo htmlspecialchars($feedback['name']); ?></span>
										<span class="feedback__date"><?php echo htmlspecialchars($feedback['date']); ?></span>
									</div>
									<div class="feedback__item">
										<h3 class="feedback__name"><?php echo htmlspecialchars($feedback['title']); ?></h3>
										<p class="feedback__text"><?php echo htmlspecialchars($feedback['text']); ?></p>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
						<span class="slider__switch">&rarr;</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Примеры работ -->
	<div class="fixes">
		<div class=" fixes__container container">
			<div class="inner">
				<div class="inner__title-block">
					<h1 class="inner__title">Примеры наших работ</h1>
				</div>
				<div class="inner__content">
					<div class="slider">
						<span class="slider__switch">&larr;</span>
						<div class="slider__content">
							<div class="fixes__item">
								<img src="media/Content/fixes/8.jpg" class="fixes__image" alt="fix picture">
							</div>
						</div>
						<span class="slider__switch">&rarr;</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php elseif ($page == 'login'): ?>
	<!-- Страница входа -->
	<div class="call">
		<div class="container">
			<div class="call__inner inner">
				<div class="inner__title-block">
					<h1 class="inner__title">Авторизация</h1>
				</div>
				<div class="inner__content">
					<?php if (isset($error)) echo "<p style='color:red;padding:10px;'>$error</p>"; ?>
					<form method="POST" style="padding:20px;">
						<input type="text" name="login" placeholder="Логин" required style="width:300px;padding:10px;margin:10px 0;border:1px solid #ccc;">
						<input type="password" name="password" placeholder="Пароль" required style="width:300px;padding:10px;margin:10px 0;border:1px solid #ccc;">
						<input type="submit" name="login_btn" value="Войти" style="padding:10px 30px;background:#007bff;color:#fff;border:none;cursor:pointer;">
					</form>
					<p style="padding:0 20px 20px;"><strong>Логин: admin, Пароль: admin</strong></p>
					<p style="padding:0 20px 20px;"><a href="?page=home">На главную</a></p>
				</div>
			</div>
		</div>
	</div>

	<?php elseif ($page == 'admin'): ?>
	<?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin'): ?>
		<div class="call">
			<div class="container">
				<div class="call__inner inner">
					<div class="inner__title-block">
						<h1 class="inner__title">Доступ запрещен</h1>
					</div>
					<div class="inner__content">
						<p style="padding:20px;">У вас нет прав доступа! <a href="?page=login">Войти</a></p>
					</div>
				</div>
			</div>
		</div>
	<?php else: ?>
		<!-- Админ-панель -->
		<div class="call">
			<div class="container">
				<div class="call__inner inner">
					<div class="inner__title-block">
						<h1 class="inner__title">Админ-панель</h1>
						<p style="text-align:center;">Добро пожаловать, <strong><?php echo $_SESSION['user_login']; ?></strong>!</p>
					</div>
					<div class="inner__content">
						<!-- Добавление неисправности -->
						<h2 style="padding:10px 0;">Добавить неисправность</h2>
						<?php if (isset($msg)) echo "<p style='color:green;padding:10px;'>$msg</p>"; ?>
						<form method="POST" style="padding:20px;">
							<input type="text" name="title" placeholder="Название" required style="width:100%;padding:10px;margin:10px 0;border:1px solid #ccc;">
							<textarea name="description" placeholder="Описание" required style="width:100%;padding:10px;margin:10px 0;border:1px solid #ccc;height:100px;"></textarea>
							<input type="text" name="price" placeholder="Цена (например: 1000)" required style="width:100%;padding:10px;margin:10px 0;border:1px solid #ccc;">
							<input type="text" name="image" placeholder="Ссылка на картинку" style="width:100%;padding:10px;margin:10px 0;border:1px solid #ccc;">
							<input type="submit" name="add_btn" value="Добавить неисправность" style="padding:10px 30px;background:#007bff;color:#fff;border:none;cursor:pointer;">
						</form>
						
						<hr style="margin:30px 0;">
						
						<!-- Добавление отзыва -->
						<h2 style="padding:10px 0;">Добавить отзыв</h2>
						<?php if (isset($msg_feedback)) echo "<p style='color:green;padding:10px;'>$msg_feedback</p>"; ?>
						<form method="POST" style="padding:20px;">
							<input type="text" name="name" placeholder="Имя" required style="width:100%;padding:10px;margin:10px 0;border:1px solid #ccc;">
							<input type="text" name="title" placeholder="Заголовок отзыва" required style="width:100%;padding:10px;margin:10px 0;border:1px solid #ccc;">
							<textarea name="text" placeholder="Текст отзыва" required style="width:100%;padding:10px;margin:10px 0;border:1px solid #ccc;height:100px;"></textarea>
							<input type="text" name="date" placeholder="Дата (например: 01.10.2019)" style="width:100%;padding:10px;margin:10px 0;border:1px solid #ccc;">
							<input type="submit" name="add_feedback_btn" value="Добавить отзыв" style="padding:10px 30px;background:#28a745;color:#fff;border:none;cursor:pointer;">
						</form>
						
						<hr style="margin:30px 0;">
						
						<!-- Все неисправности -->
						<h2 style="padding:10px 0;">Все неисправности (<?php echo count($items); ?>)</h2>
						<?php if (empty($items)): ?>
							<p style="padding:20px;">Нет записей</p>
						<?php else: ?>
							<?php foreach($items as $item): ?>
								<div style="border:1px solid #ddd;padding:15px;margin:10px 0;">
									<h3><?php echo htmlspecialchars($item['title']); ?></h3>
									<p><?php echo htmlspecialchars($item['description']); ?></p>
									<p><strong>Цена: от <?php echo htmlspecialchars($item['price']); ?></strong></p>
									<small style="color:#999;">Добавлено: <?php echo $item['created_at']; ?></small>
									<br>
									<a href="?page=admin&delete_repair=<?php echo $item['id']; ?>" 
									   style="padding:5px 15px;background:#dc3545;color:#fff;border:none;border-radius:5px;cursor:pointer;text-decoration:none;display:inline-block;margin-top:10px;"
									   onclick="return confirm('Удалить неисправность?')">Удалить</a>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
						
						<hr style="margin:30px 0;">
						
						<!-- Все отзывы -->
						<h2 style="padding:10px 0;">Все отзывы (<?php echo count($feedback_items); ?>)</h2>
						<?php if (empty($feedback_items)): ?>
							<p style="padding:20px;">Нет отзывов</p>
						<?php else: ?>
							<?php foreach($feedback_items as $feedback): ?>
								<div style="border:1px solid #ddd;padding:15px;margin:10px 0;">
									<h3><?php echo htmlspecialchars($feedback['name']); ?></h3>
									<h4><?php echo htmlspecialchars($feedback['title']); ?></h4>
									<p><?php echo htmlspecialchars($feedback['text']); ?></p>
									<small style="color:#999;">Дата: <?php echo htmlspecialchars($feedback['date']); ?></small>
									<br>
									<a href="?page=admin&delete_feedback=<?php echo $feedback['id']; ?>" 
									   style="padding:5px 15px;background:#dc3545;color:#fff;border:none;border-radius:5px;cursor:pointer;text-decoration:none;display:inline-block;margin-top:10px;"
									   onclick="return confirm('Удалить отзыв?')">Удалить</a>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php endif; ?>

	<!-- Футер -->
	<footer class="footer div_blue">
		<div class="container">
			<div class="inner">
				<div class="inner__title-block">
					<h1 class="inner__title">Краткая информация</h1>
				</div>
				<div class="inner__content footer__content">
					<div class="footer__item">
						<p class="footer__text">Сервисный центр DROID выполняет срочный, качественный и недорогой ремонт телефонов. Самые распространённые виды ремонта -это замена дисплея, антикоррозийная чистка при попадании влаги, замена аккумулятора и прошивка телефонов.</p>
					</div>
					<div class="footer__item">
						<p class="footer__text">Мы используем только качественные запасные части и строго соблюдаем технологию проведения ремонта. Мы предоставляем гарантию на выполненные работы и замененные комплектующие.</p>
					</div>
					<div class="footer__item">
						<ul class="footer__list">
							<li class="list__item">Наименование: ООО «С групп»</li>
							<li class="list__item">Юридический адрес: 634057, г. Новосибирск, ул. 79 Гвардейской дивизии, 7-179</li>
							<li class="list__item">ИНН: 7017417869</li>
							<li class="list__item">КПП: 762701001</li>
						</ul>
					</div>
					<div class="footer__item">
						<nav class="footer__menu">
							<a href="#" class="menu__item footer__menu-item">О нас</a>
							<a href="#" class="menu__item footer__menu-item">Цены</a>
							<a href="#" class="menu__item footer__menu-item">Контакты</a>
							<a href="#" class="menu__item footer__menu-item">Вакансии</a>
						</nav>
					</div>
				</div>
			</div>
		</div>
	</footer>
	<button class="order-call">Заказать звонок!</button>
</body>
</html>
<?php
// Закрываем подключение
mysqli_close($conn);
?>



-- var4.php - Droid (ремонт телефонов)
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

-- Таблица отзывов
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    text TEXT NOT NULL,
    date VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Добавляем админа
INSERT INTO users (login, password, role) VALUES ('admin', 'admin', 'admin');

-- Тестовые неисправности
INSERT INTO repairs (title, description, price, image) VALUES 
('Замена дисплейного модуля', 'Разбит дисплей, не работает сенсор, не показывает экран', '1000', 'media/Content/brokes/1.png'),
('Замена аккумулятора', 'Не держит заряд, выключается на холоде или вздулся аккумулятор', '2000', 'media/Content/brokes/2.png'),
('Обновление ПО', 'Ошибки приложений Android, не работает Google Play', '300', 'media/Content/brokes/3.png'),
('Чистка от коррозии', 'Залит телефон, внутри влага, утопили', '1000', 'media/Content/brokes/4.png');

-- Тестовые отзывы
INSERT INTO feedback (name, title, text, date) VALUES 
('Георгий Леонидович', 'Замена АКБ Sony Xperia Z2', '24 сентября 2019 г обратился для замены АКБ в своем телефоне. НН Ленина 127. Мастер Сазанов А.Е. все адекватно объяснил.', '01.10.2019');