<?php

header('Content-Type: text/html; charset=UTF-8');

$values = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['errors'] ?? [];
$generated_credentials = $_SESSION['generated_credentials'] ?? null;
$login = $_SESSION['login'] ?? null;


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

.user-actions {
    margin-top: 5px;
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

.login-link a {
    color: #0066cc;
    text-decoration: none;
}

.login-link a:hover {
    text-decoration: underline;
}
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        select[multiple] {
            height: 120px;
        }
        .error {
            border-color: red;
        }
        .error-message {
            color: red;
            font-size: 0.8em;
        }
        .credentials {
            background: #f0f8ff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
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
       
    </style>
</head>
<body>
    <?php if (!empty($login)): ?>
    <div class="user-panel">
        <p>Вы вошли как: <?= htmlspecialchars($login) ?></p>
        <div class="user-actions">
            <a href="?edit=1">Редактировать профиль</a> | 
            <a href="login.php?action=logout">Выйти</a>
        </div>
    </div>
<?php else: ?>
    <div class="login-link">
        <a href="login.php">Войти</a>
    </div>
<?php endif; ?>
    <?php if (!empty($login)): ?>
        <p>Вы вошли как: <?= htmlspecialchars($login) ?> (<a href="login.php?action=logout">Выйти</a>)</p>
    <?php else: ?>
        <p><a href="login.php">Войти</a></p>
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
            <label for="full_name">ФИО*</label>
            <input type="text" id="full_name" name="full_name" 
                   value="<?= htmlspecialchars($values['full_name'] ?? '') ?>"
                   class="<?= !empty($errors['full_name']) ? 'error' : '' ?>" required>
            <?php if (!empty($errors['full_name'])): ?>
                <div class="error-message">Введите корректное ФИО</div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="phone">Телефон*</label>
            <input type="tel" id="phone" name="phone" 
                   value="<?= htmlspecialchars($values['phone'] ?? '') ?>"
                   class="<?= !empty($errors['phone']) ? 'error' : '' ?>" required>
            <?php if (!empty($errors['phone'])): ?>
                <div class="error-message">Введите корректный телефон</div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email*</label>
            <input type="email" id="email" name="email" 
                   value="<?= htmlspecialchars($values['email'] ?? '') ?>"
                   class="<?= !empty($errors['email']) ? 'error' : '' ?>" required>
            <?php if (!empty($errors['email'])): ?>
                <div class="error-message">Введите корректный email</div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="birth_date">Дата рождения*</label>
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
            <label for="languages">Языки программирования*</label>
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
            <label for="biography">Биография</label>
            <textarea id="biography" name="biography"><?= htmlspecialchars($values['biography'] ?? '') ?></textarea>
        </div>
<div class="form-group">
    <div class="input-group">
        <div class="input-option">
            <input type="checkbox" id="contract_agreed" name="contract_agreed"
                <?= ($values['contract_agreed'] ?? false) ? 'checked' : '' ?> required>
            <label for="contract_agreed" class="option-label">С контрактом ознакомлпен*</label>
        </div>
    </div>
    <?php if (!empty($errors['contract_agreed'])): ?>
        <div class="error-message">Необходимо согласие</div>
    <?php endif; ?>
</div>

        <button type="submit">Отправить</button>
    </form>
</body>
</html>
<?php

unset($_SESSION['errors'], $_SESSION['generated_credentials']);
?>
