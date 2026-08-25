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

// ---------- Pesquisa / filtros (RF06 e RF07) ----------
// O nome é o campo obrigatório de pesquisa; elo, região e agente favorito são opcionais.
$pesquisou   = isset($_GET['buscar']);
$erroBusca   = '';
$nomeBusca   = trim($_GET['nome'] ?? '');
$eloBusca    = trim($_GET['elo'] ?? '');
$regiaoBusca = trim($_GET['regiao'] ?? '');
$agenteBusca = trim($_GET['agente'] ?? '');

$where  = [];
$tipos  = '';
$params = [];

if ($pesquisou) {
    if ($nomeBusca === '') {
        $erroBusca = 'Informe o nome (ou parte dele) para pesquisar.';
    } else {
        $where[]  = "(j.nome LIKE ? OR j.nickname LIKE ? OR j.riot_id LIKE ?)";
        $tipos   .= 'sss';
        $like     = "%$nomeBusca%";
        $params[] = $like; $params[] = $like; $params[] = $like;

        if ($eloBusca !== '') {
            $where[]  = "j.elo LIKE ?";
            $tipos   .= 's';
            $params[] = "%$eloBusca%";
        }
        if ($regiaoBusca !== '') {
            $where[]  = "j.regiao LIKE ?";
            $tipos   .= 's';
            $params[] = "%$regiaoBusca%";
        }
        if ($agenteBusca !== '') {
            $where[]  = "j.agente_favorito LIKE ?";
            $tipos   .= 's';
            $params[] = "%$agenteBusca%";
        }
    }
}

$sql = "
    SELECT
        j.id_jogador, j.nome, j.nickname, j.riot_id, j.regiao, j.equipe, j.elo, j.agente_favorito,
        COUNT(e.id_estatistica)                                   AS partidas,
        COALESCE(ROUND(AVG(e.kills), 1), 0)                       AS media_kills,
        COALESCE(ROUND(AVG(e.deaths), 1), 0)                      AS media_deaths,
        COALESCE(ROUND(AVG(e.assists), 1), 0)                     AS media_assists,
        COALESCE(ROUND(AVG(e.acs), 1), 0)                         AS media_acs,
        COALESCE(ROUND(SUM(e.resultado = 'Vitória') / NULLIF(COUNT(e.id_estatistica),0) * 100, 1), 0) AS win_rate
    FROM jogadores j
    LEFT JOIN estatisticas e ON e.id_jogador = j.id_jogador
";

if (!empty($where) && $erroBusca === '') {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " GROUP BY j.id_jogador ORDER BY win_rate DESC, media_acs DESC";

if (!empty($where) && $erroBusca === '') {
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, $tipos, ...$params);
    mysqli_stmt_execute($stmt);
    $jogadores = mysqli_stmt_get_result($stmt);
} else {
    $jogadores = mysqli_query($conexao, $sql);
}

require 'header.php';
?>

<div class="eyebrow-title"><span class="dash"></span><span style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-faint);">Elenco</span></div>
<h1>Jogadores</h1>
<p style="margin-bottom:24px;">Consulte, pesquise e filtre o elenco cadastrado.</p>

<div class="card">
    <form method="GET">
        <div class="filter-row">
            <div>
                <label>Nome, nickname ou Riot ID *</label>
                <input type="text" name="nome" placeholder="ex: Fulano, Fulaninho, Fulano#BR1" value="<?php echo htmlspecialchars($nomeBusca); ?>" required>
            </div>
            <div>
                <label>Elo</label>
                <input type="text" name="elo" placeholder="Immortal, Radiant..." value="<?php echo htmlspecialchars($eloBusca); ?>">
            </div>
            <div>
                <label>Região</label>
                <input type="text" name="regiao" placeholder="BR, NA, EU..." value="<?php echo htmlspecialchars($regiaoBusca); ?>">
            </div>
            <div>
                <label>Agente favorito</label>
                <input type="text" name="agente" placeholder="Jett, Sova..." value="<?php echo htmlspecialchars($agenteBusca); ?>">
            </div>
        </div>
        <div style="display:flex; gap:10px; margin-top:2px;">
            <button type="submit" name="buscar" value="1">Pesquisar</button>
            <?php if ($pesquisou): ?>
                <a href="jogadores.php" class="btn btn-outline">Limpar e ver todos</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if ($erroBusca): ?>
    <p class="msg msg-warn"><?php echo htmlspecialchars($erroBusca); ?></p>
<?php elseif ($pesquisou): ?>
    <p class="msg"><?php echo mysqli_num_rows($jogadores); ?> jogador(es) encontrado(s) para a pesquisa.</p>
<?php endif; ?>

<div class="card">
<table>
    <tr>
        <th>Jogador</th><th>Elo</th><th>Região</th><th>Agente favorito</th>
        <th class="num">Partidas</th><th class="num">Média K</th><th class="num">Média D</th>
        <th class="num">Média A</th><th class="num">Média ACS</th><th class="num">Win Rate</th>
    </tr>
    <?php if (mysqli_num_rows($jogadores) === 0): ?>
        <tr><td colspan="10" style="color:var(--text-faint);">Nenhum jogador encontrado.</td></tr>
    <?php endif; ?>
    <?php while ($j = mysqli_fetch_assoc($jogadores)): ?>
        <tr>
            <td><?php echo htmlspecialchars($j['nome'] . ' (' . $j['nickname'] . ')'); ?></td>
            <td><?php echo htmlspecialchars($j['elo'] ?: '—'); ?></td>
            <td><?php echo htmlspecialchars($j['regiao'] ?: '—'); ?></td>
            <td><?php echo htmlspecialchars($j['agente_favorito'] ?: '—'); ?></td>
            <td class="num"><?php echo $j['partidas']; ?></td>
            <td class="num"><?php echo $j['media_kills']; ?></td>
            <td class="num"><?php echo $j['media_deaths']; ?></td>
            <td class="num"><?php echo $j['media_assists']; ?></td>
            <td class="num"><?php echo $j['media_acs']; ?></td>
            <td class="num"><?php echo $j['win_rate']; ?>%</td>
        </tr>
    <?php endwhile; ?>
</table>
</div>

</main>
</div>
</body>
</html>
<?php mysqli_close($conexao); ?>
