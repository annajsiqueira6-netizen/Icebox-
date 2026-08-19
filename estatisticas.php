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
// Só comissão e administrador podem cadastrar estatísticas
if ($_SESSION['tipo_usuario'] !== 'comissao' && $_SESSION['tipo_usuario'] !== 'administrador') {
    header('Location: painel.php');
    exit;
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_jogador       = $_POST['id_jogador'];
    $data_partida     = $_POST['data_partida'];
    $mapa             = $_POST['mapa'];
    $agente_utilizado = $_POST['agente_utilizado'];
    $kills            = $_POST['kills'];
    $deaths           = $_POST['deaths'];
    $assists          = $_POST['assists'];
    $acs              = $_POST['acs'];
    $headshot_pct     = $_POST['headshot_pct'];
    $first_bloods     = $_POST['first_bloods'];
    $clutches         = $_POST['clutches'];
    $resultado        = $_POST['resultado'];

    $stmt = mysqli_prepare(
        $conexao,
        "INSERT INTO estatisticas
            (id_jogador, data_partida, mapa, agente_utilizado, kills, deaths, assists, acs, headshot_pct, first_bloods, clutches, resultado)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param(
        $stmt,
        "isssiiidsiis",
        $id_jogador, $data_partida, $mapa, $agente_utilizado,
        $kills, $deaths, $assists, $acs, $headshot_pct, $first_bloods, $clutches, $resultado
    );

    if (mysqli_stmt_execute($stmt)) {
        $mensagem = "Estatística cadastrada com sucesso!";
    } else {
        $mensagem = "Erro ao cadastrar: " . mysqli_error($conexao);
    }
    mysqli_stmt_close($stmt);
}

// Lista de jogadores pro <select>
$jogadoresSelect = mysqli_query($conexao, "SELECT id_jogador, nome, nickname FROM jogadores ORDER BY nome");

// Últimas estatísticas cadastradas
$ultimasStats = mysqli_query($conexao, "
    SELECT e.data_partida, j.nome, j.nickname, e.mapa, e.agente_utilizado, e.kills, e.deaths, e.assists, e.acs, e.resultado
    FROM estatisticas e
    JOIN jogadores j ON j.id_jogador = e.id_jogador
    ORDER BY e.id_estatistica DESC
    LIMIT 10
");

require 'header.php';
?>

<h1>Cadastrar Estatística de Partida</h1>

<?php if ($mensagem): ?>
    <p class="msg"><?php echo htmlspecialchars($mensagem); ?></p>
<?php endif; ?>

<?php if (mysqli_num_rows($jogadoresSelect) === 0): ?>
    <p class="msg" style="background:#fff3cd;color:#856404;">
        Nenhum jogador cadastrado ainda. Cadastre um jogador na aba <b>Jogadores</b> antes de lançar estatísticas.
    </p>
<?php else: ?>
<form class="card" method="POST">
    <label>Jogador</label>
    <select name="id_jogador" required>
        <?php mysqli_data_seek($jogadoresSelect, 0); while ($j = mysqli_fetch_assoc($jogadoresSelect)): ?>
            <option value="<?php echo $j['id_jogador']; ?>">
                <?php echo htmlspecialchars($j['nome'] . ' (' . $j['nickname'] . ')'); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Data da partida</label>
    <input type="date" name="data_partida" required>

    <label>Mapa</label>
    <input type="text" name="mapa" placeholder="Ascent, Bind, Haven...">

    <label>Agente utilizado</label>
    <input type="text" name="agente_utilizado" placeholder="Jett, Sova...">

    <label>Kills</label>
    <input type="number" name="kills" value="0" min="0" required>

    <label>Deaths</label>
    <input type="number" name="deaths" value="0" min="0" required>

    <label>Assists</label>
    <input type="number" name="assists" value="0" min="0" required>

    <label>ACS (Average Combat Score)</label>
    <input type="number" step="0.01" name="acs" value="0" required>

    <label>Headshot %</label>
    <input type="number" step="0.01" name="headshot_pct" value="0" required>

    <label>First bloods</label>
    <input type="number" name="first_bloods" value="0" min="0" required>

    <label>Clutches</label>
    <input type="number" name="clutches" value="0" min="0" required>

    <label>Resultado</label>
    <select name="resultado" required>
        <option value="Vitória">Vitória</option>
        <option value="Derrota">Derrota</option>
        <option value="Empate">Empate</option>
    </select>

    <button type="submit">Cadastrar estatística</button>
</form>
<?php endif; ?>

<h2>Últimas estatísticas cadastradas</h2>
<table>
    <tr>
        <th>Data</th><th>Jogador</th><th>Mapa</th><th>Agente</th>
        <th>K</th><th>D</th><th>A</th><th>ACS</th><th>Resultado</th>
    </tr>
    <?php while ($s = mysqli_fetch_assoc($ultimasStats)): ?>
        <tr>
            <td><?php echo $s['data_partida']; ?></td>
            <td><?php echo htmlspecialchars($s['nome']); ?></td>
            <td><?php echo htmlspecialchars($s['mapa']); ?></td>
            <td><?php echo htmlspecialchars($s['agente_utilizado']); ?></td>
            <td><?php echo $s['kills']; ?></td>
            <td><?php echo $s['deaths']; ?></td>
            <td><?php echo $s['assists']; ?></td>
            <td><?php echo $s['acs']; ?></td>
            <td><?php echo $s['resultado']; ?></td>
        </tr>
    <?php endwhile; ?>
</table>

</main>
</body>
</html>
<?php mysqli_close($conexao); ?>
