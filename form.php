<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <!-- Подключаем внешний файл со стилями CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Регистрационная анкета</h1>

    <!-- Вывод сообщений (ошибки, успех) -->
    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $msg): ?>
            <?php echo $msg; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- 
        action="" - отправляем форму на тот же URL (index.php)
        method="POST" - используем метод POST для отправки данных
    -->
    <form action="" method="POST">
        
        <!-- ===== 1. ПОЛЕ "ФИО" ===== -->
        <div class="form-group">
            <label class="required">ФИО</label>
            <!-- 
                value - подставляем сохранённое значение из Cookies
                class="error" - если есть ошибка, поле подсвечивается красным
                htmlspecialchars() - экранирует спецсимволы для безопасности (защита от XSS)
            -->
            <input type="text" name="fio" 
                   value="<?php echo htmlspecialchars($values['fio'] ?? ''); ?>"
                   class="<?php echo ($errors['fio'] ?? false) ? 'error' : ''; ?>">
        </div>

        <!-- ===== 2. ПОЛЕ "Телефон" ===== -->
        <div class="form-group">
            <label class="required">Телефон</label>
            <!-- type="tel" - мобильные устройства покажут цифровую клавиатуру -->
            <input type="tel" name="phone" 
                   value="<?php echo htmlspecialchars($values['phone'] ?? ''); ?>"
                   class="<?php echo ($errors['phone'] ?? false) ? 'error' : ''; ?>">
        </div>

        <!-- ===== 3. ПОЛЕ "Email" ===== -->
        <div class="form-group">
            <label class="required">Email</label>
            <!-- type="email" - браузер проверяет формат email -->
            <input type="email" name="email" 
                   value="<?php echo htmlspecialchars($values['email'] ?? ''); ?>"
                   class="<?php echo ($errors['email'] ?? false) ? 'error' : ''; ?>">
        </div>

        <!-- ===== 4. ПОЛЕ "Дата рождения" ===== -->
        <div class="form-group">
            <label class="required">Дата рождения</label>
            <!-- type="date" - показывает календарь для выбора даты -->
            <input type="date" name="birthdate" 
                   value="<?php echo htmlspecialchars($values['birthdate'] ?? ''); ?>"
                   class="<?php echo ($errors['birthdate'] ?? false) ? 'error' : ''; ?>">
        </div>

        <!-- ===== 5. ПОЛЕ "Пол" (радиокнопки) ===== -->
        <div class="form-group">
            <label class="required">Пол</label>
            <div class="radio-group">
                <!-- 
                    checked - если сохранённое значение равно 'male', кнопка выбрана
                    name="gender" - одинаковое имя для всех радиокнопок (группа)
                -->
                <label>
                    <input type="radio" name="gender" value="male" 
                        <?php echo (($values['gender'] ?? '') == 'male') ? 'checked' : ''; ?>> 
                    Мужской
                </label>
                <label>
                    <input type="radio" name="gender" value="female" 
                        <?php echo (($values['gender'] ?? '') == 'female') ? 'checked' : ''; ?>> 
                    Женский
                </label>
            </div>
            <?php if ($errors['gender'] ?? false): ?>
                <small style="color:red;">Выберите пол</small>
            <?php endif; ?>
        </div>

        <!-- ===== 6. ПОЛЕ "Любимый язык" (множественный выбор) ===== -->
        <div class="form-group">
            <label class="required">Любимый язык программирования</label>
            <!-- 
                name="langs[]" - квадратные скобки означают, что будет передан массив
                multiple - разрешает выбор нескольких вариантов
                size="6" - показывает 6 строк одновременно
            -->
            <select name="langs[]" multiple size="6" 
                    class="<?php echo ($errors['langs'] ?? false) ? 'error' : ''; ?>">
                <?php 
                // Список языков (ключи - значения для отправки, display - для отображения)
                $lang_list = ['pascal', 'c', 'c++', 'javascript', 'php', 'python', 
                              'java', 'haskell', 'clojure', 'prolog', 'scala', 'go'];
                $lang_display = [
                    'pascal' => 'Pascal',
                    'c' => 'C',
                    'c++' => 'C++',
                    'javascript' => 'JavaScript',
                    'php' => 'PHP',
                    'python' => 'Python',
                    'java' => 'Java',
                    'haskell' => 'Haskell',
                    'clojure' => 'Clojure',
                    'prolog' => 'Prolog',
                    'scala' => 'Scala',
                    'go' => 'Go'
                ];
                // Ранее выбранные языки (из Cookies)
                $selected_langs = $values['langs'] ?? [];
                
                // Перебираем все языки и формируем option
                foreach ($lang_list as $lang): 
                    // in_array() - проверяет, был ли выбран этот язык ранее
                    $selected = in_array($lang, $selected_langs) ? 'selected' : '';
                ?>
                    <option value="<?php echo $lang; ?>" <?php echo $selected; ?>>
                        <?php echo $lang_display[$lang]; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small>Зажмите Ctrl (Cmd) для выбора нескольких</small>
        </div>

        <!-- ===== 7. ПОЛЕ "Биография" (многострочное) ===== -->
        <div class="form-group">
            <label>Биография</label>
            <!-- textarea - многострочное текстовое поле -->
            <textarea name="bio" rows="5" 
                class="<?php echo ($errors['bio'] ?? false) ? 'error' : ''; ?>"><?php 
                echo htmlspecialchars($values['bio'] ?? ''); 
            ?></textarea>
        </div>

        <!-- ===== 8. ЧЕКБОКС "Согласие с контрактом" ===== -->
        <div class="form-group checkbox-group">
            <label>
                <!-- 
                    type="checkbox" - флажок
                    name="contract" - имя поля
                    checked - если ранее был отмечен
                -->
                <input type="checkbox" name="contract" 
                    <?php echo (($values['contract'] ?? '') == 'on') ? 'checked' : ''; ?>>
                Я ознакомлен с контрактом
            </label>
            <?php if ($errors['contract'] ?? false): ?>
                <div><small style="color:red;">Необходимо подтвердить ознакомление</small></div>
            <?php endif; ?>
        </div>

        <!-- КНОПКА ОТПРАВКИ -->
        <button type="submit">Сохранить</button>
        
    </form>
</div>
</body>
</html>
