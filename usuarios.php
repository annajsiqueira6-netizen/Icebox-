<?php
session_start();
$host   = 'localhost';
$user   = 'root';
$pass   = '';   // sem senha
$dbname = 'moneyball_valorant';

$conexao = mysqli_connect($host, $user, $pass, $dbname);

if (!$conexao) {
    die("Erro na conexão com o banco: " . mysqli_connect_error());
}

mysqli_set_charset($conexao, 'utf8mb4');


if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}
// Só administrador pode acessar esta página (RF02)
if ($_SESSION['tipo_usuario'] !== 'administrador') {
    header('Location: painel.php');
    exit;
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $tipo  = $_POST['tipo_usuario']; // administrador | comissao | jogador

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT); // RNF01

    $stmt = mysqli_prepare(
        $conexao,
        "INSERT INTO usuarios (nome, email, senha_hash, tipo_usuario) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "ssss", $nome, $email, $senhaHash, $tipo);

    if (mysqli_stmt_execute($stmt)) {
        $mensagem = "Usuário cadastrado com sucesso!";
    } else {
        $mensagem = "Erro: e-mail já está em uso.";
    }
    mysqli_stmt_close($stmt);
}

$resultUsuarios = mysqli_query($conexao, "SELECT id_usuario, nome, email, tipo_usuario FROM usuarios ORDER BY id_usuario DESC");

require 'header.php';
?>

<h1>Cadastrar Usuário</h1>

<?php if ($mensagem): ?>
    <p class="msg"><?php echo htmlspecialchars($mensagem); ?></p>
<?php endif; ?>

<form class="card" method="POST">
    <label>Nome</label>
    <input type="text" name="nome" required>

    <label>E-mail</label>
    <input type="email" name="email" required>

    <label>Senha</label>
    <input type="password" name="senha" required>

    <label>Tipo de perfil</label>
    <select name="tipo_usuario" required>
        <option value="jogador">Jogador — funções normais (consulta)</option>
        <option value="comissao">Comissão — cadastra estatísticas</option>
        <option value="administrador">Administrador — acesso total</option>
    </select>

    <button type="submit">Cadastrar</button>
</form>

<h2>Usuários cadastrados</h2>
<table>
    <tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Perfil</th></tr>
    <?php while ($u = mysqli_fetch_assoc($resultUsuarios)): ?>
        <tr>
            <td><?php echo $u['id_usuario']; ?></td>
            <td><?php echo htmlspecialchars($u['nome']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><span class="badge badge-<?php echo $u['tipo_usuario']; ?>"><?php echo $u['tipo_usuario']; ?></span></td>
        </tr>
    <?php endwhile; ?>
</table>

</main>
</body>
</html>
<?php mysqli_close($conexao); ?>
