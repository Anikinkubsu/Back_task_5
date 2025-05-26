<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: text/html; charset=UTF-8');

$db_host = 'localhost';
$db_name = 'u68908';
$db_user = 'u68908';
$db_pass = '9704645';

// Проверка авторизации
if (empty($_SESSION['login'])) {
    header('Location: login.php');
    exit();
}

$errors = [];
$values = [];

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получаем данные пользователя
    $stmt = $pdo->prepare("SELECT u.*, a.* FROM users u JOIN applications a ON u.application_id = a.id WHERE u.login = ?");
    $stmt->execute([$_SESSION['login']]);
    $user_data = $stmt->fetch();

    if (!$user_data) {
        die("Пользователь не найден");
    }

    // Заполняем значения для формы
    $values = [
        'full_name' => $user_data['full_name'],
        'phone' => $user_data['phone'],
        'email' => $user_data['email'],
        'birth_date' => $user_data['birth_date'],
        'gender' => $user_data['gender'],
        'biography' => $user_data['biography'],
        'contract_agreed' => (bool)$user_data['contract_agreed'],
        'languages' => []
    ];

    // Получаем выбранные языки программирования
    $stmt = $pdo->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
    $stmt->execute([$user_data['id']]);
    $languages = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $values['languages'] = $languages;

    // Получаем список всех языков для select
    $stmt = $pdo->query("SELECT * FROM programming_languages");
    $all_languages = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}

// Обработка POST-запроса (обновление данных)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $values = $_POST;
    $values['languages'] = $_POST['languages'] ?? [];
    $values['contract_agreed'] = isset($_POST['contract_agreed']);

    // Валидация (такая же как в index.php)
    $validation_failed = false;

    if (empty($values['full_name']) || !preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]{2,150}$/u', $values['full_name'])) {
        $errors['full_name'] = true;
        $validation_failed = true;
    }

    // ... (остальные проверки валидации как в index.php) ...

    if (!$validation_failed) {
        try {
            // Обновляем основную информацию
            $stmt = $pdo->prepare("UPDATE applications SET 
                full_name = ?, phone = ?, email = ?, birth_date = ?, 
                gender = ?, biography = ?, contract_agreed = ?
                WHERE id = ?");
            
            $stmt->execute([
                $values['full_name'],
                $values['phone'],
                $values['email'],
                $values['birth_date'],
                $values['gender'],
                $values['biography'],
                $values['contract_agreed'] ? 1 : 0,
                $user_data['id']
            ]);

            // Удаляем старые языки
            $stmt = $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?");
            $stmt->execute([$user_data['id']]);

            // Добавляем новые языки
            $stmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
            foreach ($values['languages'] as $lang_id) {
                $stmt->execute([$user_data['id'], $lang_id]);
            }

            $_SESSION['form_data'] = $values;
            $_SESSION['message'] = "Данные успешно обновлены!";
            header('Location: index.php');
            exit();

        } catch (PDOException $e) {
            die("Ошибка базы данных: " . $e->getMessage());
        }
    } else {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $values;
        header('Location: edit.php');
        exit();
    }
}

// Передаем данные в форму
$_SESSION['form_data'] = $values;
$_SESSION['languages'] = $all_languages;
$_SESSION['is_edit_mode'] = true;

include('form.php');
?>
