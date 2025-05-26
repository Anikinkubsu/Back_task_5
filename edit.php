<?php
session_start();

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
    
    // Получаем данные пользователя
    $stmt = $pdo->prepare("SELECT a.* FROM applications a JOIN users u ON a.id = u.application_id WHERE u.login = ?");
    $stmt->execute([$_SESSION['login']]);
    $user_data = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Обработка формы редактирования
        $values = [
            'full_name' => $_POST['full_name'],
            'phone' => $_POST['phone'],
            'email' => $_POST['email'],
            'birth_date' => $_POST['birth_date'],
            'gender' => $_POST['gender'],
            'biography' => $_POST['biography'],
            'contract_agreed' => isset($_POST['contract_agreed']) ? 1 : 0
        ];

        // Обновляем данные
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
            $values['contract_agreed'],
            $user_data['id']
        ]);

        // Обновляем языки программирования
        $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?")
            ->execute([$user_data['id']]);

        if (!empty($_POST['languages'])) {
            $stmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
            foreach ($_POST['languages'] as $lang_id) {
                $stmt->execute([$user_data['id'], $lang_id]);
            }
        }

        $_SESSION['message'] = "Данные успешно обновлены!";
        header('Location: index.php');
        exit();
    }

    // Получаем выбранные языки
    $stmt = $pdo->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
    $stmt->execute([$user_data['id']]);
    $selected_langs = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Получаем все языки
    $languages = $pdo->query("SELECT * FROM programming_languages")->fetchAll();

    // Подготавливаем данные для формы
    $_SESSION['form_data'] = [
        'full_name' => $user_data['full_name'],
        'phone' => $user_data['phone'],
        'email' => $user_data['email'],
        'birth_date' => $user_data['birth_date'],
        'gender' => $user_data['gender'],
        'biography' => $user_data['biography'],
        'contract_agreed' => $user_data['contract_agreed'],
        'languages' => $selected_langs
    ];
    $_SESSION['languages'] = $languages;

} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}

// Подключаем форму
include('form.php');
?>
