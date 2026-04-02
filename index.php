<?php

// Отправляем браузеру правильную кодировку UTF-8
header('Content-Type: text/html; charset=UTF-8');

// ---- НАСТРОЙКИ ПОДКЛЮЧЕНИЯ К БАЗЕ ДАННЫХ ----
// Замени на свои данные, полученные от преподавателя
$db_host = 'localhost';      // Хост БД (обычно localhost)
$db_name = 'u82465';         // Имя базы данных (совпадает с логином)
$db_user = 'u82465';         // Пользователь БД (совпадает с логином)
$db_pass = '3772684';        // Пароль от БД
// -------------------------------------------------

/**
 * Функция для безопасного получения POST-данных
 * 
 * @param string $key     Имя поля в массиве $_POST
 * @param string $default Значение по умолчанию, если поле не найдено
 * @return string         Очищенное значение или значение по умолчанию
 */
function get_post_param($key, $default = '') {
    // Проверяем, существует ли поле в POST-запросе
    if (isset($_POST[$key])) {
        // trim() - удаляет пробелы в начале и конце строки
        return trim($_POST[$key]);
    }
    return $default;
}

// --- ОБРАБОТКА GET-ЗАПРОСА (показ формы) ---
// Когда пользователь впервые открывает страницу или обновляет её
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    // Массив для хранения сообщений пользователю
    $messages = array();

    // Проверяем, есть ли кука с признаком успешного сохранения
    // !empty($_COOKIE['save_success']) - true если кука существует и не пуста
    if (!empty($_COOKIE['save_success'])) {
        // Добавляем сообщение об успехе
        $messages[] = '<div class="success">Данные успешно сохранены!</div>';
        // Удаляем куку, устанавливая время в прошлом (time() - 3600 = час назад)
        setcookie('save_success', '', time() - 3600);
    }

    // Массивы для ошибок и значений полей
    $errors = array();   // Будет хранить флаги ошибок (true/false)
    $values = array();   // Будет хранить предыдущие значения полей

    // Список всех полей формы
    $fields = ['fio', 'phone', 'email', 'birthdate', 'gender', 'bio', 'contract'];
    
    // Заполняем массивы ошибок и значений из Cookies
    foreach ($fields as $field) {
        // Если кука с ошибкой существует - поле было заполнено неверно
        $errors[$field] = !empty($_COOKIE[$field . '_error']);
        
        // Если есть сохранённое значение поля - загружаем его
        $values[$field] = isset($_COOKIE[$field . '_value']) 
            ? $_COOKIE[$field . '_value'] 
            : '';
        
        // Удаляем куку ошибки после прочтения (одноразовое сообщение)
        if ($errors[$field]) {
            setcookie($field . '_error', '', time() - 3600);
        }
    }

    // Отдельная обработка для поля "Языки программирования" (множественный выбор)
    // Так как это массив, используем serialize() для сохранения в куку
    $errors['langs'] = !empty($_COOKIE['langs_error']);
    $values['langs'] = isset($_COOKIE['langs_value']) 
        ? unserialize($_COOKIE['langs_value'])   // unserialize - превращает строку обратно в массив
        : [];
    if ($errors['langs']) {
        setcookie('langs_error', '', time() - 3600);
    }

    // Формируем понятные сообщения об ошибках для пользователя
    $error_messages = [];
    if ($errors['fio']) $error_messages[] = 'Ошибка в поле ФИО (только буквы и пробелы, до 150 символов).';
    if ($errors['phone']) $error_messages[] = 'Ошибка в поле Телефон (допустимы цифры, +, -, пробелы).';
    if ($errors['email']) $error_messages[] = 'Ошибка в поле Email (неверный формат).';
    if ($errors['birthdate']) $error_messages[] = 'Ошибка в поле Дата рождения.';
    if ($errors['gender']) $error_messages[] = 'Ошибка в поле Пол (выберите значение).';
    if ($errors['langs']) $error_messages[] = 'Ошибка в поле Любимый язык (выберите из списка).';
    if ($errors['bio']) $error_messages[] = 'Ошибка в поле Биография (не более 1000 символов).';
    if ($errors['contract']) $error_messages[] = 'Необходимо согласие с контрактом.';

    // Если есть ошибки - добавляем блок с сообщениями
    if (!empty($error_messages)) {
        $messages[] = '<div class="error-block"><strong>Исправьте ошибки:</strong><br>' 
                    . implode('<br>', $error_messages) . '</div>';
    }

    // Подключаем файл с HTML-формой
    // В нём будут доступны переменные $messages, $errors, $values
    include('form.php');
    exit();  // Прерываем выполнение скрипта
}

// --- ОБРАБОТКА POST-ЗАПРОСА (сохранение данных) ---
// Когда пользователь отправляет форму
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Флаг наличия ошибок валидации
    $has_error = false;

    // ---- ВАЛИДАЦИЯ ПОЛЯ "ФИО" ----
    // get_post_param() - получает и очищает значение из $_POST
    $fio = get_post_param('fio');
    
    // Проверка:
    // 1. Не пустое
    // 2. Соответствует регулярному выражению:
    //    - [a-zA-Zа-яА-ЯёЁ] - буквы (латиница и кириллица)
    //    - \s - пробелы
    //    - \- - дефис
    //    - {1,150} - длина от 1 до 150 символов
    if (empty($fio) || !preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]{1,150}$/u', $fio)) {
        // Сохраняем куку с ошибкой на 24 часа
        setcookie('fio_error', '1', time() + 86400);
        $has_error = true;
    }
    // Сохраняем значение поля в куку на 365 дней (даже если с ошибкой)
    setcookie('fio_value', $fio, time() + 365 * 86400);

    // ---- ВАЛИДАЦИЯ ПОЛЯ "Телефон" ----
    $phone = get_post_param('phone');
    // Регулярка: цифры, пробелы, дефисы, плюсы, скобки, длина 5-20 символов
    if (empty($phone) || !preg_match('/^[\d\s\-\+\(\)]{5,20}$/', $phone)) {
        setcookie('phone_error', '1', time() + 86400);
        $has_error = true;
    }
    setcookie('phone_value', $phone, time() + 365 * 86400);

    // ---- ВАЛИДАЦИЯ ПОЛЯ "Email" ----
    $email = get_post_param('email');
    // filter_var() - встроенная PHP функция для проверки email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', time() + 86400);
        $has_error = true;
    }
    setcookie('email_value', $email, time() + 365 * 86400);

    // ---- ВАЛИДАЦИЯ ПОЛЯ "Дата рождения" ----
    $birthdate = get_post_param('birthdate');
    // date_create() - пытается создать объект даты из строки
    $date_check = date_create($birthdate);
    if (empty($birthdate) || !$date_check) {
        setcookie('birthdate_error', '1', time() + 86400);
        $has_error = true;
    }
    setcookie('birthdate_value', $birthdate, time() + 365 * 86400);

    // ---- ВАЛИДАЦИЯ ПОЛЯ "Пол" ----
    $gender = get_post_param('gender');
    // Проверяем, что значение равно 'male' или 'female'
    if (!in_array($gender, ['male', 'female'])) {
        setcookie('gender_error', '1', time() + 86400);
        $has_error = true;
    }
    setcookie('gender_value', $gender, time() + 365 * 86400);

    // ---- ВАЛИДАЦИЯ ПОЛЯ "Языки программирования" ----
    // Получаем массив выбранных языков (если не выбран ни один - пустой массив)
    $langs = isset($_POST['langs']) ? $_POST['langs'] : [];
    
    // Список разрешённых языков (в нижнем регистре, как в БД)
    $allowed_langs = ['pascal', 'c', 'c++', 'javascript', 'php', 'python', 
                      'java', 'haskell', 'clojure', 'prolog', 'scala', 'go'];
    
    // array_intersect() - оставляет только те значения, которые есть в $allowed_langs
    // Это защита от поддельных значений
    $valid_langs = array_intersect($langs, $allowed_langs);
    
    // Если не выбран ни один разрешённый язык - ошибка
    if (empty($valid_langs)) {
        setcookie('langs_error', '1', time() + 86400);
        $has_error = true;
    }
    // serialize() - превращает массив в строку для сохранения в куку
    setcookie('langs_value', serialize($valid_langs), time() + 365 * 86400);

    // ---- ВАЛИДАЦИЯ ПОЛЯ "Биография" ----
    $bio = get_post_param('bio');
    // strlen() - считает количество байт (для латиницы = символам, для кириллицы - больше)
    // Для простоты используем strlen (mb_strlen не было на сервере)
    if (strlen($bio) > 1000) {
        setcookie('bio_error', '1', time() + 86400);
        $has_error = true;
    }
    setcookie('bio_value', $bio, time() + 365 * 86400);

    // ---- ВАЛИДАЦИЯ ЧЕКБОКСА "Контракт" ----
    $contract = get_post_param('contract');
    // Чекбокс передаёт значение 'on' если отмечен
    if ($contract !== 'on') {
        setcookie('contract_error', '1', time() + 86400);
        $has_error = true;
    }
    setcookie('contract_value', $contract, time() + 365 * 86400);

    // Если есть ошибки валидации - возвращаем пользователя обратно на форму
    if ($has_error) {
        header('Location: index.php');
        exit();
    }

    // ---- СОХРАНЕНИЕ В БАЗУ ДАННЫХ (выполняется только если ошибок нет) ----
    try {
        // Подключение к базе данных через PDO
        // PDO - PHP Data Objects (объекты данных PHP)
        // Позволяет безопасно работать с разными СУБД
        $pdo = new PDO(
            "mysql:host=$db_host;dbname=$db_name;charset=utf8",  // DSN - источник данных
            $db_user,   // Пользователь
            $db_pass,   // Пароль
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Включаем режим исключений
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC  // Результат в виде ассоциативного массива
            ]
        );

        // Начинаем транзакцию
        // Транзакция - группа запросов, которые выполняются вместе
        // Если один запрос не выполнится - откатываются все
        $pdo->beginTransaction();

        // ---- ВСТАВКА ДАННЫХ В ТАБЛИЦУ "users" ----
        // Подготовленный запрос (Prepared Statement)
        // Знак ? - это плейсхолдер (место для подстановки значения)
        $stmt = $pdo->prepare("INSERT INTO users 
            (full_name, phone, email, birth_date, gender, bio, agreed_to_terms) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        // execute() - выполняет запрос, подставляя значения вместо ?
        // Порядок значений соответствует порядку ? в запросе
        // 1 = согласен с контрактом
        $stmt->execute([$fio, $phone, $email, $birthdate, $gender, $bio, 1]);
        
        // lastInsertId() - получает автоматически сгенерированный ID последней вставки
        $user_id = $pdo->lastInsertId();

        // ---- ВСТАВКА ЯЗЫКОВ ПРОГРАММИРОВАНИЯ ----
        // Создаём строку с плейсхолдерами для каждого языка
        // Например: если выбрано 3 языка, получим "?,?,?"
        $placeholders = implode(',', array_fill(0, count($valid_langs), '?'));
        
        // Получаем ID выбранных языков из таблицы languages
        $stmt_lang_ids = $pdo->prepare("SELECT id, name FROM languages WHERE name IN ($placeholders)");
        $stmt_lang_ids->execute($valid_langs);
        $lang_ids = $stmt_lang_ids->fetchAll();
        
        // Вставляем связи в таблицу user_languages
        // Каждая запись связывает user_id с language_id
        $stmt_link = $pdo->prepare("INSERT INTO user_languages (user_id, language_id) VALUES (?, ?)");
        foreach ($lang_ids as $lang) {
            $stmt_link->execute([$user_id, $lang['id']]);
        }

        // Фиксируем транзакцию (все запросы успешно выполнены)
        $pdo->commit();

        // ---- ОЧИСТКА КУК С ДАННЫМИ ФОРМЫ ----
        // После успешного сохранения удаляем куки со значениями полей
        $fields = ['fio', 'phone', 'email', 'birthdate', 'gender', 'bio', 'contract'];
        foreach ($fields as $field) {
            setcookie($field . '_value', '', time() - 3600);  // Время в прошлом = удалить
        }
        setcookie('langs_value', '', time() - 3600);
        
        // Устанавливаем куку с признаком успешного сохранения (на 30 секунд)
        setcookie('save_success', '1', time() + 30);

        // Перенаправляем на GET-запрос (чтобы не было повторной отправки формы)
        header('Location: index.php');
        exit();

    } catch (PDOException $e) {
        // Если произошла ошибка - откатываем транзакцию
        $pdo->rollBack();
        
        // Записываем ошибку в лог сервера
        error_log("DB Error: " . $e->getMessage());
        
        // Показываем сообщение об ошибке пользователю
        $messages[] = '<div class="error-block">Ошибка базы данных: ' . $e->getMessage() . '</div>';
        include('form.php');
        exit();
    }
}
?>