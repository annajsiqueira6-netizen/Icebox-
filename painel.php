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

require 'header.php';
?>

<h1>Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['nome']); ?></h1>

<?php if ($_SESSION['tipo_usuario'] === 'administrador'): ?>
    <p>Você está logado como <b>administrador</b>. Use a aba <b>Usuários</b> para cadastrar novas contas
    (jogador, comissão ou administrador) e a aba <b>Jogadores</b> para ver o elenco cadastrado.</p>

<?php elseif ($_SESSION['tipo_usuario'] === 'comissao'): ?>
    <p>Você está logado como <b>comissão técnica</b>. Use a aba <b>Cadastrar Estatística</b> para
    registrar o desempenho dos jogadores em cada partida. A aba <b>Jogadores</b> mostra a tabela
    atualizada com todo o elenco.</p>

<?php else: ?>
    <p>Você está logado como <b>jogador</b>. Use a aba <b>Jogadores</b> para consultar o elenco e as
    estatísticas registradas pela comissão técnica.</p>
<?php endif; ?>

</main>
</body>
</html>
