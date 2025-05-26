<?php
// Проверяем, не запущена ли уже сессия
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Устанавливаем заголовки
header('Content-Type: text/html; charset=UTF-8');

// Инициализация переменных из сессии
$values = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['errors'] ?? [];
$generated_credentials = $_SESSION['generated_credentials'] ?? null;
$login = $_SESSION['login'] ?? null;

// Подключение к базе данных
$languages = [];
try {
    $db_host = 'localhost';
    $db_name = 'u68908';
    $db_user = 'u68908';
    $db_pass = '9704645';
    
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT * FROM programming_languages");
    if ($stmt !== false) {
        $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $languages = [];
}

// Определение режима работы
$is_edit_mode = isset($_GET['edit']) && !empty($login);
$form_action = $is_edit_mode ? 'edit.php' : 'index.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета</title>
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
        }
        .user-panel {
            margin-bottom: 20px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .user-actions a {
            margin-right: 10px;
            color: #0066cc;
            text-decoration: none;
        }
        .user-actions a:hover {
            text-decoration: underline;
        }
        .login-link {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .input-label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
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
            border-color: red !important;
        }
        .error-message {
            color: red;
            font-size: 0.8em;
            margin-top: 5px;
        }
        .credentials {
            background: #f0f8ff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .input-option {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        .input-option input[type="radio"],
        .input-option input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <?php if (!empty($login)): ?>
        <div class="user-panel">
            <p>Вы вошли как: <?= htmlspecialchars($login) ?></p>
            <div class="user-actions">
                <a href="?edit=1">Редактировать профиль</a>
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

    <?php if (!empty($_SESSION['message'])): ?>
        <div class="message">
            <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars($form_action) ?>">
        <div class="form-group">
            <label for="full_name" class="input-label">ФИО*</label>
            <input type="text" id="full_name" name="full_name" 
                   value="<?= htmlspecialchars($values['full_name'] ?? '') ?>"
                   class="<?= !empty($errors['full_name']) ? 'error' : '' ?>" required>
            <?php if (!empty($errors['full_name'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['full_name']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="phone" class="input-label">Телефон*</label>
            <input type="tel" id="phone" name="phone" 
                   value="<?= htmlspecialchars($values['phone'] ?? '') ?>"
                   class="<?= !empty($errors['phone']) ? 'error' : '' ?>" required>
            <?php if (!empty($errors['phone'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['phone']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email" class="input-label">Email*</label>
            <input type="email" id="email" name="email" 
                   value="<?= htmlspecialchars($values['email'] ?? '') ?>"
                   class="<?= !empty($errors['email']) ? 'error' : '' ?>" required>
            <?php if (!empty($errors['email'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="birth_date" class="input-label">Дата рождения*</label>
            <input type="date" id="birth_date" name="birth_date" 
                   value="<?= htmlspecialchars($values['birth_date'] ?? '') ?>"
                   class="<?= !empty($errors['birth_date']) ? 'error' : '' ?>" required>
            <?php if (!empty($errors['birth_date'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['birth_date']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="input-label">Пол*</label>
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
            <?php if (!empty($errors['gender'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['gender']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="languages" class="input-label">Языки программирования*</label>
            <select id="languages" name="languages[]" multiple 
                    class="<?= !empty($errors['languages']) ? 'error' : '' ?>" required>
                <?php foreach ($languages as $lang): ?>
                    <option value="<?= htmlspecialchars($lang['id']) ?>"
                        <?= in_array($lang['id'], $values['languages'] ?? []) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lang['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['languages'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['languages']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="biography" class="input-label">Биография</label>
            <textarea id="biography" name="biography" rows="4"><?= htmlspecialchars($values['biography'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <div class="input-option">
                <input type="checkbox" id="contract_agreed" name="contract_agreed"
                    <?= ($values['contract_agreed'] ?? false) ? 'checked' : '' ?> required>
                <label for="contract_agreed" class="option-label">С контрактом ознакомлен*</label>
            </div>
            <?php if (!empty($errors['contract_agreed'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['contract_agreed']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit"><?= $is_edit_mode ? 'Обновить' : 'Отправить' ?></button>
    </form>
</body>
</html>
<?php
// Очистка временных данных сессии
unset($_SESSION['errors'], $_SESSION['generated_credentials'], $_SESSION['message']);
?>
