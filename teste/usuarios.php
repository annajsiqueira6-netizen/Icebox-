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
// Aba exclusiva de administrador (RF02)
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

<div class="eyebrow-title"><span class="dash"></span><span style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-faint);">Administração</span></div>
<h1>Cadastrar usuário</h1>
<p style="margin-bottom:24px;">Crie novas contas de acesso ao sistema e defina o perfil de cada uma.</p>

<?php if ($mensagem): ?>
    <p class="msg <?php echo strpos($mensagem, 'Erro') === 0 ? 'msg-warn' : ''; ?>"><?php echo htmlspecialchars($mensagem); ?></p>
<?php endif; ?>

<form class="card" method="POST">
    <div class="filter-row">
        <div><label>Nome</label><input type="text" name="nome" required></div>
        <div><label>E-mail</label><input type="email" name="email" required></div>
        <div><label>Senha</label><input type="password" name="senha" required></div>
        <div>
            <label>Tipo de perfil</label>
            <select name="tipo_usuario" required>
                <option value="jogador">Jogador — funções normais (consulta)</option>
                <option value="comissao">Comissão — cadastra jogadores e estatísticas</option>
                <option value="administrador">Administrador — acesso total</option>
            </select>
        </div>
    </div>
    <button type="submit">Cadastrar</button>
</form>

<h2>Usuários cadastrados</h2>
<div class="card">
<table>
    <tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Perfil</th></tr>
    <?php while ($u = mysqli_fetch_assoc($resultUsuarios)): ?>
        <tr>
            <td class="num"><?php echo $u['id_usuario']; ?></td>
            <td><?php echo htmlspecialchars($u['nome']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><span class="badge badge-<?php echo $u['tipo_usuario']; ?>"><?php echo $u['tipo_usuario']; ?></span></td>
        </tr>
    <?php endwhile; ?>
</table>
</div>

</main>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
<?php mysqli_close($conexao); ?>
