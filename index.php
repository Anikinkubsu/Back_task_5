<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: text/html; charset=UTF-8');

$db_host = 'localhost';
$db_name = 'u68908';
$db_user = 'u68908';
$db_pass = '9704645';

$errors = [];
$values = [
    'fullname' => '',
    'phone' => '',
    'email' => '',
    'birthdate' => '',
    'gender' => '',
    'bio' => '',
    'contract_' => false,
    'languages' => []
];


if (!empty($_SESSION['user_id'])) {
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT a.* FROM applications a 
                              JOIN users u ON a.id = u.application_id 
                              WHERE u.id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $app_data = $stmt->fetch();
        
        if ($app_data) {
            $stmt = $pdo->prepare("SELECT language_id FROM application_languages 
                                  WHERE application_id = ?");
            $stmt->execute([$app_data['id']]);
            $langs = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $values = [
                'fullname' => $app_data['fullname'],
                'phone' => $app_data['phone'],
                'email' => $app_data['email'],
                'birthdate' => $app_data['birthdate'],
                'gender' => $app_data['gender'],
                'bio' => $app_data['bio'],
                'contract_' => (bool)$app_data['contract_'],
                'languages' => $langs
            ];
            
            $_SESSION['form_data'] = $values;
        }
    } catch (PDOException $e) {
       
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    include('form.php');
    exit();
}


$values = $_POST;
$values['languages'] = $_POST['languages'] ?? [];
$values['contract_'] = isset($_POST['contract_']);


$validation_failed = false;

if (empty($values['fullname']) || !preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]{2,150}$/u', $values['fullname'])) {
    $errors['fullname'] = true;
    $validation_failed = true;
}

if (empty($values['phone']) || !preg_match('/^\+?\d{10,15}$/', $values['phone'])) {
    $errors['phone'] = true;
    $validation_failed = true;
}

if (empty($values['email']) || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = true;
    $validation_failed = true;
}

$today = new DateTime();
$birthdate = DateTime::createFromFormat('Y-m-d', $values['birthdate']);
if (empty($values['birthdate']) || !$birthdate || $birthdate > $today) {
    $errors['birthdate'] = true;
    $validation_failed = true;
}

if (empty($values['gender']) || !in_array($values['gender'], ['male', 'female'])) {
    $errors['gender'] = true;
    $validation_failed = true;
}

if (empty($values['languages'])) {
    $errors['languages'] = true;
    $validation_failed = true;
}

if (!$values['contract_']) {
    $errors['contract_'] = true;
    $validation_failed = true;
}

if ($validation_failed) {
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = $values;
    header('Location: index.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!empty($_SESSION['user_id']) && isset($_POST['update'])) {
       
        $stmt = $pdo->prepare("UPDATE applications SET 
                              fullname = ?, phone = ?, email = ?, 
                              birthdate = ?, gender = ?, bio = ?, 
                              contract_ = ? 
                              WHERE id = (SELECT application_id FROM users WHERE id = ?)");
        $stmt->execute([
            $values['fullname'],
            $values['phone'],
            $values['email'],
            $values['birthdate'],
            $values['gender'],
            $values['bio'],
            $values['contract_'] ? 1 : 0,
            $_SESSION['user_id']
        ]);
        
        $stmt = $pdo->prepare("DELETE FROM application_languages 
                              WHERE application_id = (SELECT application_id FROM users WHERE id = ?)");
        $stmt->execute([$_SESSION['user_id']]);
        
        $stmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) 
                              VALUES ((SELECT application_id FROM users WHERE id = ?), ?)");
        foreach ($values['languages'] as $lang_id) {
            $stmt->execute([$_SESSION['user_id'], $lang_id]);
        }
        
        $_SESSION['update_success'] = true;
    } else {
       
        $stmt = $pdo->prepare("INSERT INTO applications (fullname, phone, email, birthdate, gender, bio, contract_) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $values['fullname'],
            $values['phone'],
            $values['email'],
            $values['birthdate'],
            $values['gender'],
            $values['bio'],
            $values['contract_'] ? 1 : 0
        ]);
        
        $app_id = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
        foreach ($values['languages'] as $lang_id) {
            $stmt->execute([$app_id, $lang_id]);
        }
        
        if (empty($_SESSION['user_id'])) {
            $login = 'user_' . substr(md5(time()), 0, 8);
            $password = substr(md5(uniqid()), 0, 8);
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (login, password, application_id) VALUES (?, ?, ?)");
            $stmt->execute([$login, $password_hash, $app_id]);
            
            $_SESSION['login'] = $login;
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['generated_credentials'] = [
                'login' => $login,
                'password' => $password
            ];
        }
    }
    
    header("Location: index.php");
    exit();
    
} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}
?>
