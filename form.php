<?php
header('Content-Type: text/html; charset=UTF-8');

$values = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['errors'] ?? [];
$generated_credentials = $_SESSION['generated_credentials'] ?? null;
$login = $_SESSION['login'] ?? null;

// Определяем действие формы
$is_edit_mode = isset($_GET['edit']) && !empty($login);
$form_action = $is_edit_mode ? 'edit.php' : 'index.php';

header('Content-Type: text/html; charset=UTF-8');

// Проверяем режим редактирования
$is_edit_mode = isset($_GET['edit']) && !empty($_SESSION['login']);

// Если режим редактирования, загружаем данные пользователя
if ($is_edit_mode && empty($_SESSION['form_data'])) {
    try {
        $db_host = 'localhost';
        $db_name = 'u68908';
        $db_user = 'u68908';
        $db_pass = '9704645';
        
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
        
        // Получаем данные пользователя
        $stmt = $pdo->prepare("SELECT a.* FROM applications a JOIN users u ON a.id = u.application_id WHERE u.login = ?");
        $stmt->execute([$_SESSION['login']]);
        $user_data = $stmt->fetch();
        
        if ($user_data) {
            // Получаем выбранные языки программирования
            $stmt = $pdo->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
            $stmt->execute([$user_data['id']]);
            $languages = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Заполняем данные для формы
            $_SESSION['form_data'] = [
                'full_name' => $user_data['full_name'],
                'phone' => $user_data['phone'],
                'email' => $user_data['email'],
                'birth_date' => $user_data['birth_date'],
                'gender' => $user_data['gender'],
                'biography' => $user_data['biography'],
                'contract_agreed' => $user_data['contract_agreed'],
                'languages' => $languages
            ];
        }
    } catch (PDOException $e) {
        die("Ошибка базы данных: " . $e->getMessage());
    }
}

$values = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['errors'] ?? [];
$generated_credentials = $_SESSION['generated_credentials'] ?? null;
$login = $_SESSION['login'] ?? null;

// Получаем список всех языков программирования
try {
    $db_host = 'localhost';
    $db_name = 'u68908';
    $db_user = 'u68908';
    $db_pass = '9704645';
    
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $stmt = $pdo->query("SELECT * FROM programming_languages");
    $all_languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $all_languages = [];
}
try {
    $db_host = 'localhost';
    $db_name = 'u68908';
    $db_user = 'u68908';
    $db_pass = '9704645';
    
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $stmt = $pdo->query("SELECT * FROM programming_languages");
    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $languages = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit_mode ? 'Редактирование профиля' : 'Анкета' ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        .message {
            background: #dff0d8;
            color: #3c763d;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #d6e9c6;
        }
        .user-panel {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .user-actions a {
            margin-right: 15px;
            color: #007bff;
            text-decoration: none;
        }
        .user-actions a:hover {
            text-decoration: underline;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .input-label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .input-group {
            margin-top: 5px;
        }
        .input-option {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        .input-option input[type="radio"],
        .input-option input[type="checkbox"] {
            width: auto;
            margin: 0 10px 0 0;
            transform: scale(1.2);
        }
        .option-label {
            font-weight: normal;
            cursor: pointer;
            user-select: none;
            margin-bottom: 0;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        select[multiple] {
            height: 120px;
        }
        .error {
            border-color: #dc3545;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.8em;
            margin-top: 5px;
        }
        button {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #218838;
        }
        .credentials {
            background: #f0f8ff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #d1e7ff;
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message"><?= htmlspecialchars($_SESSION['message']) ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (!empty($login)): ?>
        <div class="user-panel">
            <p>Вы вошли как: <?= htmlspecialchars($login) ?></p>
            <div class="user-actions">
                <?php if (!$is_edit_mode): ?>
                    <a href="?edit=1">Редактировать профиль</a> | 
                <?php endif; ?>
                <a href="login.php?action=logout">Выйти</a>
            </div>
        </div>
    <?php else: ?>
        <div class="login-link">
            <a href="login.php">Войти</a>
        </div>
    <?php endif; ?>

    <?php if (!empty($generated_credentials)): ?>
        <div class="credentials">
            <h3>Ваши данные для входа:</h3>
            <p><strong>Логин:</strong> <?= htmlspecialchars($generated_credentials['login']) ?></p>
            <p><strong>Пароль:</strong> <?= htmlspecialchars($generated_credentials['password']) ?></p>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars($form_action) ?>">
        <div class="form-group">
            <label for="full_name" class="input-label">ФИО*</label>
            <input type="text" id="full_name" name="full_name" 
                   value="<?= htmlspecialchars($values['full_name'] ?? '') ?>"
                   class="<?= !empty($errors['full_name']) ? 'error' : '' ?>" required>
            <?php if (!empty($errors['full_name'])): ?>
                <div class="error-message">Введите корректное ФИО</div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="phone" class="input-label">Телефон*</label>
            <input type="tel" id="phone" name="phone" 
                   value="<?= htmlspecialchars($values['phone'] ?? '') ?>"
                   class="<?= !empty($errors['phone']) ? 'error' : '' ?>" required>
            <?php if (!empty($errors['phone'])): ?>
                <div class="error-message">Введите корректный телефон</div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email" class="input-label">Email*</label>
            <input type="email" id="email" name="email" 
                   value="<?= htmlspecialchars($values['email'] ?? '') ?>"
                   class="<?= !empty($errors['email']) ? 'error' : '' ?>" required>
            <?php if (!empty($errors['email'])): ?>
                <div class="error-message">Введите корректный email</div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="birth_date" class="input-label">Дата рождения*</label>
            <input type="date" id="birth_date" name="birth_date" 
                   value="<?= htmlspecialchars($values['birth_date'] ?? '') ?>"
                   class="<?= !empty($errors['birth_date']) ? 'error' : '' ?>" required>
            <?php if (!empty($errors['birth_date'])): ?>
                <div class="error-message">Введите корректную дату</div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="input-label">Пол*</label>
            <div class="input-group">
                <div class="input-option">
                    <input type="radio" id="gender_male" name="gender" value="male"
                        <?= ($values['gender'] ?? '') === 'male' ? 'checked' : '' ?> required>
                    <label for="gender_male" class="option-label">Мужской</label>
                </div>
                <div class="input-option">
                    <input type="radio" id="gender_female" name="gender" value="female"
                        <?= ($values['gender'] ?? '') === 'female' ? 'checked' : '' ?>>
                    <label for="gender_female" class="option-label">Женский</label>
                </div>
            </div>
            <?php if (!empty($errors['gender'])): ?>
                <div class="error-message">Выберите пол</div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="languages" class="input-label">Языки программирования*</label>
            <select id="languages" name="languages[]" multiple 
                    class="<?= !empty($errors['languages']) ? 'error' : '' ?>" required>
                <?php foreach ($languages as $lang): ?>
                    <option value="<?= $lang['id'] ?>"
                        <?= in_array($lang['id'], $values['languages'] ?? []) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lang['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['languages'])): ?>
                <div class="error-message">Выберите хотя бы один язык</div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="biography" class="input-label">Биография</label>
            <textarea id="biography" name="biography" rows="4"><?= htmlspecialchars($values['biography'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <div class="input-group">
                <div class="input-option">
                    <input type="checkbox" id="contract_agreed" name="contract_agreed"
                        <?= ($values['contract_agreed'] ?? false) ? 'checked' : '' ?> required>
                    <label for="contract_agreed" class="option-label">С контрактом ознакомлен*</label>
                </div>
            </div>
            <?php if (!empty($errors['contract_agreed'])): ?>
                <div class="error-message">Необходимо согласие</div>
            <?php endif; ?>
        </div>

        <button type="submit"><?= $is_edit_mode ? 'Обновить данные' : 'Отправить' ?></button>
    </form>
</body>
</html>
<?php
unset($_SESSION['errors'], $_SESSION['generated_credentials']);
?>
