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
// Só comissão e administrador podem cadastrar jogadores/estatísticas (RF03 e RF08)
if ($_SESSION['tipo_usuario'] !== 'comissao' && $_SESSION['tipo_usuario'] !== 'administrador') {
    header('Location: painel.php');
    exit;
}

$mensagemJogador = '';
$mensagemStat    = '';
$abaAtiva        = 'jogador';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'jogador') {
    $abaAtiva = 'jogador';
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
        $mensagemJogador = "Jogador cadastrado com sucesso!";
    } else {
        $mensagemJogador = "Erro ao cadastrar: Riot ID já existe.";
    }
    mysqli_stmt_close($stmt);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'estatistica') {
    $abaAtiva = 'estatistica';
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
        $mensagemStat = "Estatística cadastrada com sucesso!";
    } else {
        $mensagemStat = "Erro ao cadastrar: " . mysqli_error($conexao);
    }
    mysqli_stmt_close($stmt);
}

// Lista de jogadores pro <select> de estatísticas
$jogadoresSelect = mysqli_query($conexao, "SELECT id_jogador, nome, nickname FROM jogadores ORDER BY nome");
$temJogadores    = mysqli_num_rows($jogadoresSelect) > 0;

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

<div class="eyebrow-title"><span class="dash"></span><span style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-faint);">Cadastro</span></div>
<h1>Adicionar jogador e estatísticas</h1>
<p style="margin-bottom:24px;">Cadastre novos jogadores no elenco e registre o desempenho de cada partida.</p>

<!-- Sub-abas locais -->
<div style="display:flex; gap:8px; margin-bottom:22px;">
    <button type="button" class="btn-outline" id="tab-btn-jogador" onclick="mostrarAba('jogador')">Novo jogador</button>
    <button type="button" class="btn-outline" id="tab-btn-estatistica" onclick="mostrarAba('estatistica')">Nova estatística</button>
</div>

<!-- ===== Aba: novo jogador ===== -->
<div id="tab-jogador">
    <?php if ($mensagemJogador): ?>
        <p class="msg <?php echo strpos($mensagemJogador, 'Erro') === 0 ? 'msg-warn' : ''; ?>"><?php echo htmlspecialchars($mensagemJogador); ?></p>
    <?php endif; ?>

    <form class="card" method="POST">
        <input type="hidden" name="acao" value="jogador">
        <div class="filter-row">
            <div><label>Nome</label><input type="text" name="nome" required></div>
            <div><label>Riot ID (ex: Fulano#BR1)</label><input type="text" name="riot_id" required></div>
            <div><label>Nickname</label><input type="text" name="nickname" required></div>
            <div><label>Região</label><input type="text" name="regiao" placeholder="BR, NA, EU..."></div>
            <div><label>Equipe</label><input type="text" name="equipe" placeholder="opcional"></div>
            <div><label>Elo</label><input type="text" name="elo" placeholder="Immortal, Radiant..."></div>
            <div class="full"><label>Agente favorito</label><input type="text" name="agente_favorito" placeholder="Jett, Sova..."></div>
        </div>
        <button type="submit">Cadastrar jogador</button>
    </form>
</div>

<!-- ===== Aba: nova estatística ===== -->
<div id="tab-estatistica" style="display:none;">
    <?php if ($mensagemStat): ?>
        <p class="msg <?php echo strpos($mensagemStat, 'Erro') === 0 ? 'msg-warn' : ''; ?>"><?php echo htmlspecialchars($mensagemStat); ?></p>
    <?php endif; ?>

    <?php if (!$temJogadores): ?>
        <p class="msg msg-warn">Nenhum jogador cadastrado ainda. Cadastre um jogador na aba <b>Novo jogador</b> antes de lançar estatísticas.</p>
    <?php else: ?>
    <form class="card" method="POST">
        <input type="hidden" name="acao" value="estatistica">
        <div class="filter-row">
            <div class="full">
                <label>Jogador</label>
                <select name="id_jogador" required>
                    <?php mysqli_data_seek($jogadoresSelect, 0); while ($j = mysqli_fetch_assoc($jogadoresSelect)): ?>
                        <option value="<?php echo $j['id_jogador']; ?>">
                            <?php echo htmlspecialchars($j['nome'] . ' (' . $j['nickname'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div><label>Data da partida</label><input type="date" name="data_partida" required></div>
            <div><label>Mapa</label><input type="text" name="mapa" placeholder="Ascent, Bind, Haven..."></div>
            <div><label>Agente utilizado</label><input type="text" name="agente_utilizado" placeholder="Jett, Sova..."></div>
            <div><label>Kills</label><input type="number" name="kills" value="0" min="0" required></div>
            <div><label>Deaths</label><input type="number" name="deaths" value="0" min="0" required></div>
            <div><label>Assists</label><input type="number" name="assists" value="0" min="0" required></div>
            <div><label>ACS (Average Combat Score)</label><input type="number" step="0.01" name="acs" value="0" required></div>
            <div><label>Headshot %</label><input type="number" step="0.01" name="headshot_pct" value="0" required></div>
            <div><label>First bloods</label><input type="number" name="first_bloods" value="0" min="0" required></div>
            <div><label>Clutches</label><input type="number" name="clutches" value="0" min="0" required></div>
            <div>
                <label>Resultado</label>
                <select name="resultado" required>
                    <option value="Vitória">Vitória</option>
                    <option value="Derrota">Derrota</option>
                    <option value="Empate">Empate</option>
                </select>
            </div>
        </div>
        <button type="submit">Cadastrar estatística</button>
    </form>
    <?php endif; ?>

    <h2>Últimas estatísticas cadastradas</h2>
    <div class="card">
    <table>
        <tr>
            <th>Data</th><th>Jogador</th><th>Mapa</th><th>Agente</th>
            <th class="num">K</th><th class="num">D</th><th class="num">A</th><th class="num">ACS</th><th>Resultado</th>
        </tr>
        <?php if (mysqli_num_rows($ultimasStats) === 0): ?>
            <tr><td colspan="9" style="color:var(--text-faint);">Nenhuma estatística cadastrada ainda.</td></tr>
        <?php endif; ?>
        <?php while ($s = mysqli_fetch_assoc($ultimasStats)): ?>
            <tr>
                <td><?php echo date('d/m/Y', strtotime($s['data_partida'])); ?></td>
                <td><?php echo htmlspecialchars($s['nome']); ?></td>
                <td><?php echo htmlspecialchars($s['mapa']); ?></td>
                <td><?php echo htmlspecialchars($s['agente_utilizado']); ?></td>
                <td class="num"><?php echo $s['kills']; ?></td>
                <td class="num"><?php echo $s['deaths']; ?></td>
                <td class="num"><?php echo $s['assists']; ?></td>
                <td class="num"><?php echo $s['acs']; ?></td>
                <td><?php echo htmlspecialchars($s['resultado']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
    </div>
</div>

<style>
    #tab-btn-jogador.aba-ativa, #tab-btn-estatistica.aba-ativa{
        background:var(--blue); color:#06181c; border-color:var(--blue);
        font-family:'Chakra Petch',sans-serif; font-weight:700; text-transform:uppercase; letter-spacing:.04em; font-size:13px;
    }
</style>
<script>
function mostrarAba(nome) {
    document.getElementById('tab-jogador').style.display = (nome === 'jogador') ? 'block' : 'none';
    document.getElementById('tab-estatistica').style.display = (nome === 'estatistica') ? 'block' : 'none';
    document.getElementById('tab-btn-jogador').classList.toggle('aba-ativa', nome === 'jogador');
    document.getElementById('tab-btn-estatistica').classList.toggle('aba-ativa', nome === 'estatistica');
}
mostrarAba('<?php echo $abaAtiva; ?>');
</script>

</main>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
<?php mysqli_close($conexao); ?>
