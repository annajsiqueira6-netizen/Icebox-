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

$mensagem = '';
$podeCadastrar = ($_SESSION['tipo_usuario'] === 'administrador' || $_SESSION['tipo_usuario'] === 'comissao');

if ($podeCadastrar && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome            = $_POST['nome'];
    $riot_id         = $_POST['riot_id'];
    $nickname        = $_POST['nickname'];
    $regiao          = $_POST['regiao'];
    $equipe          = $_POST['equipe'];
    $elo             = $_POST['elo'];
    $agente_favorito = $_POST['agente_favorito'];

    $stmt = mysqli_prepare(
        $conexao,
        "INSERT INTO jogadores (nome, riot_id, nickname, regiao, equipe, elo, agente_favorito)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param(
        $stmt, "sssssss",
        $nome, $riot_id, $nickname, $regiao, $equipe, $elo, $agente_favorito
    );

    if (mysqli_stmt_execute($stmt)) {
        $mensagem = "Jogador cadastrado com sucesso!";
    } else {
        $mensagem = "Erro ao cadastrar: Riot ID já existe.";
    }
    mysqli_stmt_close($stmt);
}

// Tabela de jogadores com estatísticas agregadas (RF08: K/D, ACS, partidas jogadas, agente mais usado)
$jogadores = mysqli_query($conexao, "
    SELECT
        j.id_jogador, j.nome, j.nickname, j.regiao, j.elo, j.agente_favorito,
        COUNT(e.id_estatistica)                                   AS partidas,
        COALESCE(ROUND(AVG(e.kills), 1), 0)                       AS media_kills,
        COALESCE(ROUND(AVG(e.deaths), 1), 0)                      AS media_deaths,
        COALESCE(ROUND(AVG(e.acs), 1), 0)                         AS media_acs,
        COALESCE(ROUND(SUM(e.resultado = 'Vitória') / NULLIF(COUNT(e.id_estatistica),0) * 100, 1), 0) AS win_rate
    FROM jogadores j
    LEFT JOIN estatisticas e ON e.id_jogador = j.id_jogador
    GROUP BY j.id_jogador
    ORDER BY win_rate DESC, media_acs DESC
");

require 'header.php';
?>

<h1>Jogadores</h1>

<?php if ($mensagem): ?>
    <p class="msg"><?php echo htmlspecialchars($mensagem); ?></p>
<?php endif; ?>

<?php if ($podeCadastrar): ?>
<form class="card" method="POST">
    <label>Nome</label>
    <input type="text" name="nome" required>

    <label>Riot ID (ex: Fulano#BR1)</label>
    <input type="text" name="riot_id" required>

    <label>Nickname</label>
    <input type="text" name="nickname" required>

    <label>Região</label>
    <input type="text" name="regiao" placeholder="BR, NA, EU...">

    <label>Equipe</label>
    <input type="text" name="equipe" placeholder="opcional">

    <label>Elo</label>
    <input type="text" name="elo" placeholder="Immortal, Radiant...">

    <label>Agente favorito</label>
    <input type="text" name="agente_favorito" placeholder="Jett, Sova...">

    <button type="submit">Cadastrar jogador</button>
</form>
<?php endif; ?>

<h2>Elenco e desempenho (RF08 — ranking automático)</h2>
<table>
    <tr>
        <th>Jogador</th><th>Elo</th><th>Região</th><th>Agente favorito</th>
        <th>Partidas</th><th>Média K</th><th>Média D</th><th>Média ACS</th><th>Win Rate</th>
    </tr>
    <?php while ($j = mysqli_fetch_assoc($jogadores)): ?>
        <tr>
            <td><?php echo htmlspecialchars($j['nome'] . ' (' . $j['nickname'] . ')'); ?></td>
            <td><?php echo htmlspecialchars($j['elo']); ?></td>
            <td><?php echo htmlspecialchars($j['regiao']); ?></td>
            <td><?php echo htmlspecialchars($j['agente_favorito']); ?></td>
            <td><?php echo $j['partidas']; ?></td>
            <td><?php echo $j['media_kills']; ?></td>
            <td><?php echo $j['media_deaths']; ?></td>
            <td><?php echo $j['media_acs']; ?></td>
            <td><?php echo $j['win_rate']; ?>%</td>
        </tr>
    <?php endwhile; ?>
</table>

</main>
</body>
</html>
<?php mysqli_close($conexao); ?>
