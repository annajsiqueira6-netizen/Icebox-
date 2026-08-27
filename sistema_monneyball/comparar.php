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

// Lista de jogadores pros <select>
$listaJogadores = mysqli_query($conexao, "SELECT id_jogador, nome, nickname FROM jogadores ORDER BY nome");
$temJogadores    = mysqli_num_rows($listaJogadores) > 0;

$id1 = isset($_GET['id1']) ? (int) $_GET['id1'] : 0;
$id2 = isset($_GET['id2']) ? (int) $_GET['id2'] : 0;
$comparando = ($id1 > 0 && $id2 > 0);
$erro = '';

function buscarDadosJogador($conexao, $id) {
    $stmt = mysqli_prepare($conexao, "
        SELECT
            j.nome, j.nickname, j.riot_id, j.regiao, j.equipe, j.elo, j.agente_favorito,
            COUNT(e.id_estatistica) AS partidas,
            COALESCE(ROUND(AVG(e.kills), 1), 0) AS media_kills,
            COALESCE(ROUND(AVG(e.deaths), 1), 0) AS media_deaths,
            COALESCE(ROUND(AVG(e.assists), 1), 0) AS media_assists,
            COALESCE(ROUND(AVG(e.acs), 1), 0) AS media_acs,
            COALESCE(ROUND(AVG(e.headshot_pct), 1), 0) AS media_hs,
            COALESCE(SUM(e.first_bloods), 0) AS total_first_bloods,
            COALESCE(SUM(e.clutches), 0) AS total_clutches,
            COALESCE(ROUND(SUM(e.resultado = 'Vitória') / NULLIF(COUNT(e.id_estatistica),0) * 100, 1), 0) AS win_rate
        FROM jogadores j
        LEFT JOIN estatisticas e ON e.id_jogador = j.id_jogador
        WHERE j.id_jogador = ?
        GROUP BY j.id_jogador
    ");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($res);
}

$jogador1 = $jogador2 = null;
if ($comparando) {
    if ($id1 === $id2) {
        $erro = 'Selecione dois jogadores diferentes para comparar.';
        $comparando = false;
    } else {
        $jogador1 = buscarDadosJogador($conexao, $id1);
        $jogador2 = buscarDadosJogador($conexao, $id2);
        if (!$jogador1 || !$jogador2) {
            $erro = 'Não foi possível encontrar um dos jogadores selecionados.';
            $comparando = false;
        }
    }
}

// Linhas da tabela comparativa: [rótulo, chave, formato]
$linhas = [
    ['Elo', 'elo', 'texto'],
    ['Região', 'regiao', 'texto'],
    ['Agente favorito', 'agente_favorito', 'texto'],
    ['Partidas jogadas', 'partidas', 'numero'],
    ['Win rate', 'win_rate', 'pct'],
    ['Média de kills', 'media_kills', 'numero'],
    ['Média de deaths', 'media_deaths', 'numero'],
    ['Média de assists', 'media_assists', 'numero'],
    ['ACS médio', 'media_acs', 'numero'],
    ['Headshot % médio', 'media_hs', 'pct'],
    ['Total de first bloods', 'total_first_bloods', 'numero'],
    ['Total de clutches', 'total_clutches', 'numero'],
];

function melhor($a, $b, $chave) {
    // Para "melhor", maior é melhor em todas as métricas numéricas usadas aqui.
    $numericas = ['partidas','win_rate','media_kills','media_assists','media_acs','media_hs','total_first_bloods','total_clutches'];
    if (!in_array($chave, $numericas, true)) return 0;
    if ($chave === 'media_deaths') return 0; // menor seria melhor, mas deixamos neutro
    if ((float)$a[$chave] > (float)$b[$chave]) return 1;
    if ((float)$a[$chave] < (float)$b[$chave]) return 2;
    return 0;
}

require 'header.php';
?>

<div class="eyebrow-title"><span class="dash"></span><span style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-faint);">Análise comparativa</span></div>
<h1>Comparar jogadores</h1>
<p style="margin-bottom:24px;">Selecione dois jogadores para comparar as estatísticas lado a lado.</p>

<?php if (!$temJogadores): ?>
    <p class="msg msg-warn">Nenhum jogador cadastrado ainda.</p>
<?php else: ?>

<div class="card">
    <form method="GET">
        <div class="filter-row">
            <div>
                <label>Jogador 1</label>
                <select name="id1" required>
                    <option value="">Selecione...</option>
                    <?php mysqli_data_seek($listaJogadores, 0); while ($j = mysqli_fetch_assoc($listaJogadores)): ?>
                        <option value="<?php echo $j['id_jogador']; ?>" <?php echo ($id1 == $j['id_jogador']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($j['nome'] . ' (' . $j['nickname'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label>Jogador 2</label>
                <select name="id2" required>
                    <option value="">Selecione...</option>
                    <?php mysqli_data_seek($listaJogadores, 0); while ($j = mysqli_fetch_assoc($listaJogadores)): ?>
                        <option value="<?php echo $j['id_jogador']; ?>" <?php echo ($id2 == $j['id_jogador']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($j['nome'] . ' (' . $j['nickname'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <button type="submit">Comparar</button>
    </form>
</div>

<?php if ($erro): ?>
    <p class="msg msg-warn"><?php echo htmlspecialchars($erro); ?></p>
<?php endif; ?>

<?php if ($comparando): ?>
<div class="card">
<table>
    <tr>
        <th style="width:26%;">Critério</th>
        <th style="width:37%;"><?php echo htmlspecialchars($jogador1['nome'] . ' (' . $jogador1['nickname'] . ')'); ?></th>
        <th style="width:37%;"><?php echo htmlspecialchars($jogador2['nome'] . ' (' . $jogador2['nickname'] . ')'); ?></th>
    </tr>
    <?php foreach ($linhas as [$rotulo, $chave, $formato]): ?>
        <?php
            $v1 = $jogador1[$chave]; $v2 = $jogador2[$chave];
            $venc = melhor($jogador1, $jogador2, $chave);
            $fmt = function ($v) use ($formato) {
                if ($v === null || $v === '') return '—';
                if ($formato === 'pct') return $v . '%';
                return $v;
            };
        ?>
        <tr>
            <td style="color:var(--text-dim);"><?php echo $rotulo; ?></td>
            <td style="<?php echo $venc === 1 ? 'color:var(--blue);font-weight:700;' : ''; ?>"><?php echo htmlspecialchars($fmt($v1)); ?></td>
            <td style="<?php echo $venc === 2 ? 'color:var(--blue);font-weight:700;' : ''; ?>"><?php echo htmlspecialchars($fmt($v2)); ?></td>
        </tr>
    <?php endforeach; ?>
</table>
</div>
<p style="font-size:12px; color:var(--text-faint);">Valores em azul indicam o melhor desempenho entre os dois jogadores para o critério.</p>
<?php endif; ?>

<?php endif; ?>

</main>
</div>
</body>
</html>
<?php mysqli_close($conexao); ?>
