<?php

$host   = 'localhost';
$user   = 'root';
$pass   = ''; 
$dbname = 'moneyball_valorant';

$conexao = mysqli_connect($host, $user, $pass, $dbname);

if (!$conexao) {
    die("Erro na conexão com o banco: " . mysqli_connect_error());
}

mysqli_set_charset($conexao, 'utf8mb4');



$mensagem = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $stmt,
        "sssssss",
        $nome, $riot_id, $nickname, $regiao, $equipe, $elo, $agente_favorito
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $mensagem = "Jogador cadastrado com sucesso! Se ele aparecer na lista abaixo, o banco está funcionando.";
}


$resultUsuarios  = mysqli_query($conexao, "SELECT id_usuario, nome, email, tipo_usuario FROM usuarios");
$resultJogadores = mysqli_query($conexao, "SELECT id_jogador, nome, riot_id, nickname, regiao, elo FROM jogadores ORDER BY id_jogador DESC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Teste de Conexão - Moneyball DS</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 40px auto; padding: 0 20px; }
        h1 { color: #1a1a2e; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; font-size: 14px; }
        th { background: #1a1a2e; color: white; }
        form { background: #f4f4f4; padding: 20px; border-radius: 8px; }
        input, select { display: block; width: 100%; margin-bottom: 10px; padding: 6px; box-sizing: border-box; }
        button { padding: 8px 16px; background: #ff4655; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .msg { background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Teste de Conexão - Moneyball DS (Valorant)</h1>

    <?php if ($mensagem): ?>
        <p class="msg"><?php echo htmlspecialchars($mensagem); ?></p>
    <?php endif; ?>

    <h2>Usuários cadastrados</h2>
    <table>
        <tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Tipo</th></tr>
        <?php while ($u = mysqli_fetch_assoc($resultUsuarios)): ?>
            <tr>
                <td><?php echo $u['id_usuario']; ?></td>
                <td><?php echo htmlspecialchars($u['nome']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><?php echo htmlspecialchars($u['tipo_usuario']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Cadastrar jogador de teste</h2>
    <form method="POST">
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

    <h2>Jogadores cadastrados</h2>
    <table>
        <tr><th>ID</th><th>Nome</th><th>Riot ID</th><th>Nickname</th><th>Região</th><th>Elo</th></tr>
        <?php while ($j = mysqli_fetch_assoc($resultJogadores)): ?>
            <tr>
                <td><?php echo $j['id_jogador']; ?></td>
                <td><?php echo htmlspecialchars($j['nome']); ?></td>
                <td><?php echo htmlspecialchars($j['riot_id']); ?></td>
                <td><?php echo htmlspecialchars($j['nickname']); ?></td>
                <td><?php echo htmlspecialchars($j['regiao']); ?></td>
                <td><?php echo htmlspecialchars($j['elo']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
<?php mysqli_close($conexao); ?>