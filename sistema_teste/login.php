<?php

$host   = 'localhost';
$user   = 'root';
$pass   = ''; 
$dbname = 'moneyball_valorant';

$conexao = mysqli_connect($host, $user, $pass, $dbname);

if (!$conexao) {
    die("Erro na conexão com o banco: " . mysqli_connect_error());
}

mysqli_set_charset($conexao, 'utf8mb4');

$erro = '';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = mysqli_prepare(
        $conexao,
        "SELECT id_usuario, nome, senha_hash, tipo_usuario FROM usuarios WHERE email = ?"
    );
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

    if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
        // Login certo: guarda os dados na sessão
        $_SESSION['id_usuario']   = $usuario['id_usuario'];
        $_SESSION['nome']         = $usuario['nome'];
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];

        header('Location: teste.php');
        exit;
    } else {
        $erro = 'E-mail ou senha inválidos.';
    }
}

mysqli_close($conexao);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Moneyball DS</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 80px auto; padding: 0 20px; }
        h1 { color: #1a1a2e; text-align: center; }
        form { background: #f4f4f4; padding: 20px; border-radius: 8px; }
        input { display: block; width: 100%; margin-bottom: 12px; padding: 8px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #ff4655; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .erro { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h1>Moneyball DS</h1>

    <?php if ($erro): ?>
        <p class="erro"><?php echo htmlspecialchars($erro); ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>E-mail</label>
        <input type="email" name="email" required>

        <label>Senha</label>
        <input type="password" name="senha" required>

        <button type="submit">Entrar</button>
    </form>
</body>
</html>