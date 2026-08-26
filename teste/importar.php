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
// Aba exclusiva de administrador (RF04)
if ($_SESSION['tipo_usuario'] !== 'administrador') {
    header('Location: painel.php');
    exit;
}

$mensagem = '';
$linhasImportadas = 0;
$linhasComErro = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo_csv'])) {

    $arquivo = $_FILES['arquivo_csv']['tmp_name'];
    $handle  = fopen($arquivo, 'r');

    if ($handle === false) {
        $mensagem = "Não foi possível abrir o arquivo.";
    } else {

        // Pula a primeira linha (cabeçalho da planilha)
        fgetcsv($handle, 0, ';');

        // Lê linha por linha até o fim do arquivo
        while (($linha = fgetcsv($handle, 0, ';')) !== false) {

            // Ordem esperada das colunas no CSV:
            // riot_id;nickname;nome;regiao;equipe;elo;agente_favorito;data_partida;mapa;agente_utilizado;kills;deaths;assists;acs;headshot_pct;first_bloods;clutches;resultado
            if (count($linha) < 18) {
                $linhasComErro++;
                continue; // linha incompleta, pula pra próxima
            }

            $riot_id         = trim($linha[0]);
            $nickname        = trim($linha[1]);
            $nome            = trim($linha[2]);
            $regiao          = trim($linha[3]);
            $equipe          = trim($linha[4]);
            $elo             = trim($linha[5]);
            $agente_favorito = trim($linha[6]);
            $data_partida    = trim($linha[7]);
            $mapa            = trim($linha[8]);
            $agente_utilizado = trim($linha[9]);
            $kills           = (int)   $linha[10];
            $deaths          = (int)   $linha[11];
            $assists         = (int)   $linha[12];
            $acs             = (float) $linha[13];
            $headshot_pct    = (float) $linha[14];
            $first_bloods    = (int)   $linha[15];
            $clutches        = (int)   $linha[16];
            $resultado       = trim($linha[17]);

            // 1) Verifica se o jogador já existe (pelo riot_id)
            $stmt = mysqli_prepare($conexao, "SELECT id_jogador FROM jogadores WHERE riot_id = ?");
            mysqli_stmt_bind_param($stmt, "s", $riot_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $id_jogador);
            $existe = mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            // 2) Se não existe, cadastra o jogador primeiro
            if (!$existe) {
                $stmt = mysqli_prepare(
                    $conexao,
                    "INSERT INTO jogadores (nome, riot_id, nickname, regiao, equipe, elo, agente_favorito)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param(
                    $stmt, "sssssss",
                    $nome, $riot_id, $nickname, $regiao, $equipe, $elo, $agente_favorito
                );
                mysqli_stmt_execute($stmt);
                $id_jogador = mysqli_insert_id($conexao);
                mysqli_stmt_close($stmt);
            }

            // 3) Insere a estatística da partida vinculada ao jogador
            $stmt = mysqli_prepare(
                $conexao,
                "INSERT INTO estatisticas
                    (id_jogador, data_partida, mapa, agente_utilizado, kills, deaths, assists, acs, headshot_pct, first_bloods, clutches, resultado)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param(
                $stmt, "isssiiidsiis",
                $id_jogador, $data_partida, $mapa, $agente_utilizado,
                $kills, $deaths, $assists, $acs, $headshot_pct, $first_bloods, $clutches, $resultado
            );

            if (mysqli_stmt_execute($stmt)) {
                $linhasImportadas++;
            } else {
                $linhasComErro++;
            }
            mysqli_stmt_close($stmt);
        }

        fclose($handle);
        $mensagem = "Importação concluída: $linhasImportadas linha(s) importada(s), $linhasComErro com erro.";
    }
}

require 'header.php';
?>

<div class="eyebrow-title"><span class="dash"></span><span style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-faint);">Administração</span></div>
<h1>Importar jogadores e estatísticas</h1>
<p style="margin-bottom:24px;">Importe uma planilha em lote para cadastrar jogadores e partidas de uma vez.</p>

<?php if ($mensagem): ?>
    <p class="msg <?php echo $linhasComErro > 0 ? 'msg-warn' : ''; ?>"><?php echo htmlspecialchars($mensagem); ?></p>
<?php endif; ?>

<div class="card">
    <p style="margin-top:0;"><b style="color:var(--text);">Como preparar o arquivo no Excel:</b></p>
    <ol style="color:var(--text-dim); padding-left:18px; line-height:1.7;">
        <li>Monte a planilha com estas colunas, nesta ordem, na primeira linha (cabeçalho):</li>
    </ol>
    <p style="font-size:12px; background:var(--bg-panel-2); color:var(--text-dim); padding:12px 14px; border-radius:3px; overflow-x:auto; border:1px solid var(--border);">
        riot_id | nickname | nome | regiao | equipe | elo | agente_favorito | data_partida (AAAA-MM-DD) | mapa | agente_utilizado | kills | deaths | assists | acs | headshot_pct | first_bloods | clutches | resultado (Vitória/Derrota/Empate)
    </p>
    <ol start="2" style="color:var(--text-dim); padding-left:18px; line-height:1.7;">
        <li>No Excel: Arquivo → Salvar como → tipo <b style="color:var(--text);">CSV (separado por ponto e vírgula)</b></li>
        <li>Envie o arquivo .csv gerado abaixo</li>
    </ol>
</div>

<form class="card" method="POST" enctype="multipart/form-data">
    <label>Arquivo CSV</label>
    <input type="file" name="arquivo_csv" accept=".csv" required>
    <button type="submit">Importar</button>
</form>

</main>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
<?php mysqli_close($conexao); ?>
