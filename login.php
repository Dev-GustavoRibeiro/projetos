<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $employee_query = "SELECT id, password FROM funcionarios WHERE username = ?";
    $stmt = $conn->prepare($employee_query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['employee_id'] = $user['id'];
            header("Location: dashboard.php"); 
            exit;
        } else {
            $error = "Senha incorreta.";
        }
    } else {
        $error = "Usuário não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Eita Mainha</title>
    <link rel="icon" href="../icons/icon-logo.png" type="image/png">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Entrar</h2>
            <form method="POST" action="login.php">
                <div class="input-group">
                    <label for="username">Usuário:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="password">Senha:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Entrar</button>
            </form>

            <?php if (isset($error)): ?>
                <p class="error" style="color: red;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
