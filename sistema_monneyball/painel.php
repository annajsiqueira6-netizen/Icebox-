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

// ---------- Cartões de resumo ----------
$totalJogadores = (int) mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM jogadores"))[0];
$totalPartidas  = (int) mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM estatisticas"))[0];
$totalUsuarios  = (int) mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM usuarios"))[0];

$agenteTopRow = mysqli_fetch_assoc(mysqli_query($conexao, "
    SELECT agente_utilizado, COUNT(*) AS total
    FROM estatisticas
    WHERE agente_utilizado IS NOT NULL AND agente_utilizado <> ''
    GROUP BY agente_utilizado
    ORDER BY total DESC
    LIMIT 1
"));
$agenteTop = $agenteTopRow ? $agenteTopRow['agente_utilizado'] : '—';

// ---------- Jogadores em destaque (top 5 por win rate / ACS) ----------
$destaques = mysqli_query($conexao, "
    SELECT
        j.nome, j.nickname, j.elo, j.regiao, j.agente_favorito,
        COUNT(e.id_estatistica) AS partidas,
        COALESCE(ROUND(AVG(e.acs), 1), 0) AS media_acs,
        COALESCE(ROUND(SUM(e.resultado = 'Vitória') / NULLIF(COUNT(e.id_estatistica),0) * 100, 1), 0) AS win_rate
    FROM jogadores j
    LEFT JOIN estatisticas e ON e.id_jogador = j.id_jogador
    GROUP BY j.id_jogador
    HAVING partidas > 0
    ORDER BY win_rate DESC, media_acs DESC
    LIMIT 5
");
$temDestaques = mysqli_num_rows($destaques) > 0;

// ---------- Últimos jogadores cadastrados ----------
$recentes = mysqli_query($conexao, "
    SELECT nome, nickname, riot_id, elo, regiao, data_cadastro
    FROM jogadores
    ORDER BY data_cadastro DESC
    LIMIT 5
");

require 'header.php';
?>

<div class="eyebrow-title"><span class="dash"></span><span style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-faint);">Painel geral</span></div>
<h1>Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['nome']); ?></h1>
<p style="margin-bottom:28px;">
    <?php if ($_SESSION['tipo_usuario'] === 'administrador'): ?>
        Você está logado como <b style="color:var(--text);">administrador</b>. Gerencie contas na aba <b>Usuários</b> e acompanhe o elenco na aba <b>Jogadores</b>.
    <?php elseif ($_SESSION['tipo_usuario'] === 'comissao'): ?>
        Você está logado como <b style="color:var(--text);">comissão técnica</b>. Use a aba <b>Adicionar</b> para cadastrar jogadores e registrar o desempenho de cada partida.
    <?php else: ?>
        Você está logado como <b style="color:var(--text);">jogador</b>. Consulte o elenco e compare estatísticas nas abas ao lado.
    <?php endif; ?>
</p>

<div class="stat-grid">
    <div class="stat-card">
        <div class="eyebrow">Jogadores cadastrados</div>
        <div class="value"><?php echo $totalJogadores; ?></div>
        <div class="sub">Elenco ativo no sistema</div>
    </div>
    <div class="stat-card">
        <div class="eyebrow">Partidas registradas</div>
        <div class="value"><?php echo $totalPartidas; ?></div>
        <div class="sub">Estatísticas lançadas</div>
    </div>
    <div class="stat-card">
        <div class="eyebrow">Agente mais usado</div>
        <div class="value" style="font-size:22px;"><?php echo htmlspecialchars($agenteTop); ?></div>
        <div class="sub">Com base nas partidas registradas</div>
    </div>
    <div class="stat-card">
        <div class="eyebrow">Usuários do sistema</div>
        <div class="value"><?php echo $totalUsuarios; ?></div>
        <div class="sub">Contas cadastradas</div>
    </div>
</div>

<h2>Destaques &mdash; melhor desempenho</h2>
<div class="card">
    <?php if (!$temDestaques): ?>
        <p style="margin:0;">Ainda não há estatísticas suficientes para gerar destaques. Cadastre partidas na aba <b>Adicionar</b>.</p>
    <?php else: ?>
    <table>
        <tr>
            <th>Jogador</th><th>Elo</th><th>Agente favorito</th>
            <th class="num">Partidas</th><th class="num">ACS médio</th><th class="num">Win Rate</th>
        </tr>
        <?php while ($d = mysqli_fetch_assoc($destaques)): ?>
        <tr>
            <td><?php echo htmlspecialchars($d['nome'] . ' (' . $d['nickname'] . ')'); ?></td>
            <td><?php echo htmlspecialchars($d['elo'] ?: '—'); ?></td>
            <td><?php echo htmlspecialchars($d['agente_favorito'] ?: '—'); ?></td>
            <td class="num"><?php echo $d['partidas']; ?></td>
            <td class="num"><?php echo $d['media_acs']; ?></td>
            <td class="num"><?php echo $d['win_rate']; ?>%</td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php endif; ?>
</div>

<h2>Últimos jogadores cadastrados</h2>
<div class="card">
    <?php if (mysqli_num_rows($recentes) === 0): ?>
        <p style="margin:0;">Nenhum jogador cadastrado ainda.</p>
    <?php else: ?>
    <table>
        <tr><th>Nome</th><th>Riot ID</th><th>Elo</th><th>Região</th><th>Cadastrado em</th></tr>
        <?php while ($r = mysqli_fetch_assoc($recentes)): ?>
        <tr>
            <td><?php echo htmlspecialchars($r['nome'] . ' (' . $r['nickname'] . ')'); ?></td>
            <td><?php echo htmlspecialchars($r['riot_id']); ?></td>
            <td><?php echo htmlspecialchars($r['elo'] ?: '—'); ?></td>
            <td><?php echo htmlspecialchars($r['regiao'] ?: '—'); ?></td>
            <td><?php echo date('d/m/Y', strtotime($r['data_cadastro'])); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php endif; ?>
</div>

</main>
</div>
</body>
</html>
<?php mysqli_close($conexao); ?>
