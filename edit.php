<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Проверка авторизации
if (empty($_SESSION['login'])) {
    header('Location: login.php');
    exit();
}

// Подключение к БД
$db_host = 'localhost';
$db_name = 'u68908';
$db_user = 'u68908';
$db_pass = '9704645';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получаем ID приложения пользователя
    $stmt = $pdo->prepare("SELECT application_id FROM users WHERE login = ?");
    $stmt->execute([$_SESSION['login']]);
    $app_id = $stmt->fetchColumn();

    if (!$app_id) {
        die("Профиль пользователя не найден");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Подготовка данных
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $birth_date = $_POST['birth_date'];
        $gender = $_POST['gender'];
        $biography = trim($_POST['biography']);
        $contract_agreed = isset($_POST['contract_agreed']) ? 1 : 0;
        $languages = $_POST['languages'] ?? [];

        // Валидация данных
        $errors = [];
        
        if (empty($full_name)) {
            $errors['full_name'] = true;
        }
        


        if (empty($errors)) {
            // Обновляем основную информацию
            $stmt = $pdo->prepare("UPDATE applications SET 
                full_name = ?, phone = ?, email = ?, birth_date = ?,
                gender = ?, biography = ?, contract_agreed = ?
                WHERE id = ?");
            
            $stmt->execute([
                $full_name,
                $phone,
                $email,
                $birth_date,
                $gender,
                $biography,
                $contract_agreed,
                $app_id
            ]);

            // Обновляем языки программирования
            $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?")
                ->execute([$app_id]);

            if (!empty($languages)) {
                $stmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
                foreach ($languages as $lang_id) {
                    $stmt->execute([$app_id, $lang_id]);
                }
            }

            // Обновляем данные в сессии
            $_SESSION['form_data'] = [
                'full_name' => $full_name,
                'phone' => $phone,
                'email' => $email,
                'birth_date' => $birth_date,
                'gender' => $gender,
                'biography' => $biography,
                'contract_agreed' => $contract_agreed,
                'languages' => $languages
            ];

            $_SESSION['message'] = "Данные успешно обновлены!";
            header('Location: index.php');
            exit();
        } else {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: edit.php');
            exit();
        }
    }

    // Если GET-запрос, перенаправляем на форму с параметром edit
    header('Location: index.php?edit=1');
    exit();

} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}
