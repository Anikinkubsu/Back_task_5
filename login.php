<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = 'localhost';
$db_name = 'u68908';
$db_user = 'u68908';
$db_pass = '9704645';


if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}


if (!empty($_SESSION['login'])) {
    header('Location: index.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['login'] = $user['login'];
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php');
            exit();
        } else {
            $_SESSION['error_message'] = 'Неверный логин или пароль';
            header('Location: login.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Ошибка системы';
        header('Location: login.php');
        exit();
    }
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему</title>
    <style>

        button:hover {
          background-color: #8EE4AF;
          transform: translateY(-2px);
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        button:active {
          transform: translateY(0);
        }

        .error {
          color: #ff6b6b;
          background-color: #fff;
          padding: 10px;
          border-radius: 5px;
          margin-bottom: 15px;
          text-align: center;
          font-weight: bold;
        }

        footer {
          background-color: #379683;
          color: #EDF5E1;
          text-align: center;
          padding: 20px 0;
          width: 100%;
          margin-top: auto;
          box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
        }

        footer p {
          margin: 0;
          font-size: 14px;
        }
      </style>
</head>
<body>
    <div class="login-form">
        <h2>Вход в систему</h2>
        
        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="error"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Логин:</label>
                <input type="text" name="login" required>
            </div>
            
            <div class="form-group">
                <label>Пароль:</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit">Войти</button>
        </form>
    </div>
</body>
</html>
