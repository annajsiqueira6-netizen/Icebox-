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





echo "<h2>Executando migração...</h2>";


$sql = "
    CREATE TABLE IF NOT EXISTS usuarios (
        id_usuario      INT AUTO_INCREMENT PRIMARY KEY,
        nome            VARCHAR(100)    NOT NULL,
        email           VARCHAR(150)    NOT NULL UNIQUE,
        senha_hash      VARCHAR(255)    NOT NULL,
        tipo_usuario    ENUM('admin', 'comum') NOT NULL DEFAULT 'comum',
        data_criacao    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
";
if (mysqli_query($conexao, $sql)) {
    echo "<p>✔ Tabela <b>usuarios</b> OK</p>";
} else {
    echo "<p>✘ Erro ao criar usuarios: " . mysqli_error($conexao) . "</p>";
}


$sql = "
    CREATE TABLE IF NOT EXISTS jogadores (
        id_jogador      INT AUTO_INCREMENT PRIMARY KEY,
        nome            VARCHAR(100)    NOT NULL,
        riot_id         VARCHAR(50)     NOT NULL UNIQUE,
        nickname        VARCHAR(50)     NOT NULL,
        regiao          VARCHAR(50)     NULL,
        equipe          VARCHAR(100)    NULL,
        elo             VARCHAR(30)     NULL,
        agente_favorito VARCHAR(50)     NULL,
        data_cadastro   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
";
if (mysqli_query($conexao, $sql)) {
    echo "<p>✔ Tabela <b>jogadores</b> OK</p>";
} else {
    echo "<p>✘ Erro ao criar jogadores: " . mysqli_error($conexao) . "</p>";
}


$sql = "
    CREATE TABLE IF NOT EXISTS estatisticas (
        id_estatistica   INT AUTO_INCREMENT PRIMARY KEY,
        id_jogador       INT             NOT NULL,
        data_partida     DATE            NOT NULL,
        mapa             VARCHAR(50)     NULL,
        agente_utilizado VARCHAR(50)     NULL,
        kills            INT             NOT NULL DEFAULT 0,
        deaths           INT             NOT NULL DEFAULT 0,
        assists          INT             NOT NULL DEFAULT 0,
        acs              DECIMAL(6,2)    NOT NULL DEFAULT 0,
        headshot_pct     DECIMAL(5,2)    NOT NULL DEFAULT 0,
        first_bloods     INT             NOT NULL DEFAULT 0,
        clutches         INT             NOT NULL DEFAULT 0,
        resultado        ENUM('Vitória', 'Derrota', 'Empate') NOT NULL,
        FOREIGN KEY (id_jogador) REFERENCES jogadores(id_jogador)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE=InnoDB
";
if (mysqli_query($conexao, $sql)) {
    echo "<p>✔ Tabela <b>estatisticas</b> OK</p>";
} else {
    echo "<p>✘ Erro ao criar estatisticas: " . mysqli_error($conexao) . "</p>";
}


$emailAdmin = 'admin@moneyball.com';
$senhaAdmin = 'admin123';

$stmt = mysqli_prepare($conexao, "SELECT COUNT(*) FROM usuarios WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $emailAdmin);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $total);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if ($total == 0) {
    $senhaHash = password_hash($senhaAdmin, PASSWORD_DEFAULT); 
    $nome = 'Administrador';
    $tipo = 'admin';

    $stmt = mysqli_prepare(
        $conexao,
        "INSERT INTO usuarios (nome, email, senha_hash, tipo_usuario) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "ssss", $nome, $emailAdmin, $senhaHash, $tipo);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo "<p>✔ Usuário <b>admin</b> criado! Login: <b>$emailAdmin</b> / Senha: <b>$senhaAdmin</b></p>";
} else {
    echo "<p>ℹ Usuário admin já existia, nada foi alterado.</p>";
}

echo "<h3>Migração concluída com sucesso!</h3>";
echo "<p><a href='teste.php'>Ir para a página de teste →</a></p>";

mysqli_close($conexao);

?>