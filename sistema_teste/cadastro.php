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

session_start();

$erro = '';


if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $tipo  = $_POST['tipo_usuario']; 

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT); // RNF01

    $stmt = mysqli_prepare(
        $conexao,
        "INSERT INTO usuarios (nome, email, senha_hash, tipo_usuario) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "ssss", $nome, $email, $senhaHash, $tipo);

    if (mysqli_stmt_execute($stmt)) {
        $mensagem = "Usuário cadastrado com sucesso!";
    } else {
        $mensagem = "Erro: " . mysqli_error($conexao) . " (o e-mail já pode estar em uso)";
    }
    mysqli_stmt_close($stmt);
}


$resultUsuarios = mysqli_query($conexao, "SELECT id_usuario, nome, email, tipo_usuario FROM usuarios ORDER BY id_usuario DESC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Usuário - Moneyball DS</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; padding: 0 20px; }
        h1 { color: #1a1a2e; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; font-size: 14px; }
        th { background: #1a1a2e; color: white; }
        form { background: #f4f4f4; padding: 20px; border-radius: 8px; }
        input, select { display: block; width: 100%; margin-bottom: 12px; padding: 8px; box-sizing: border-box; }
        button { padding: 8px 16px; background: #ff4655; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .msg { background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .topo { display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>
    <div class="topo">
        <h1>Cadastrar Usuário</h1>
        <p>Logado como: <b><?php echo htmlspecialchars($_SESSION['nome']); ?></b> (<?php echo $_SESSION['tipo_usuario']; ?>) | <a href="logout.php">Sair</a></p>
    </div>

    <?php if ($mensagem): ?>
        <p class="msg"><?php echo htmlspecialchars($mensagem); ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Nome</label>
        <input type="text" name="nome" required>

        <label>E-mail</label>
        <input type="email" name="email" required>

        <label>Senha</label>
        <input type="password" name="senha" required>

        <label>Tipo de usuário</label>
        <select name="tipo_usuario">
            <option value="comum">Comum</option>
            <option value="admin">Admin</option>
        </select>

        <button type="submit">Cadastrar</button>
    </form>

    <h2>Usuários cadastrados</h2>
    <table>
        <tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Tipo</th></tr>
        <?php while ($u = mysqli_fetch_assoc($resultUsuarios)): ?>
            <tr>
                <td><?php echo $u['id_usuario']; ?></td>
                <td><?php echo htmlspecialchars($u['nome']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><?php echo htmlspecialchars($u['tipo_usuario']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
<?php mysqli_close($conexao); ?>