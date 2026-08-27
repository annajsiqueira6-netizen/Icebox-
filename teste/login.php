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


$erro = '';

// Se já está logado, manda direto pro painel
if (isset($_SESSION['id_usuario'])) {
    header('Location: painel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = mysqli_prepare(
        $conexao,
        "SELECT id_usuario, nome, senha_hash, tipo_usuario FROM usuarios WHERE email = ?"
    );
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

    if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
        $_SESSION['id_usuario']   = $usuario['id_usuario'];
        $_SESSION['nome']         = $usuario['nome'];
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario']; // administrador | comissao | jogador

        header('Location: painel.php');
        exit;
    } else {
        $erro = 'E-mail ou senha inválidos.';
    }
}

mysqli_close($conexao);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar · Valorant Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body{ flex-direction:column; align-items:center; justify-content:center; }
        .login-wrap{ width:100%; max-width:380px; padding:24px; position:relative; z-index:1; }
        .login-brand{ display:flex; align-items:center; gap:10px; justify-content:center; margin-bottom:36px; }
        .login-brand .brand-text{ font-size:20px; }
        .login-card{ padding:32px 28px; }
        .login-card h1{
            font-size:14px; text-transform:uppercase; letter-spacing:.12em; color:var(--text-dim);
            font-weight:600; margin:0 0 24px; text-align:center;
        }
        .login-card .erro{
            background:rgba(226,102,95,.10); border:1px solid rgba(226,102,95,.35); color:var(--danger);
            padding:10px 13px; border-radius:4px; margin-bottom:18px; font-size:13px; animation:slideIn .35s var(--ease);
        }
        .login-hint{ text-align:center; margin-top:20px; font-size:11.5px; color:var(--text-faint); letter-spacing:.03em; }
        .login-card button{ width:100%; padding:12px; margin-top:6px; justify-content:center; }
        .field-icon{ position:relative; }
        .field-icon svg{
            position:absolute; left:12px; top:37px; width:15px; height:15px;
            stroke:var(--text-faint); fill:none; stroke-width:1.8; pointer-events:none; transition:stroke .15s ease;
        }
        .field-icon input{ padding-left:36px; }
        .field-icon input:focus + svg, .field-icon:focus-within svg{ stroke:var(--blue); }
    </style>
</head>
<body>

<div class="ambient"><div class="scanlines"></div></div>

<div class="login-wrap">
    <div class="login-brand">
        <span class="brand-mark"></span>
        <span class="brand-text">Valorant <span style="color:var(--text-faint); font-weight:500;">Tracker</span></span>
    </div>

    <div class="card login-card">
        <h1>Acessar plataforma</h1>

        <?php if ($erro): ?>
            <p class="erro"><?php echo htmlspecialchars($erro); ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="field-icon">
                <label>E-mail</label>
                <input type="email" name="email" placeholder="seu@email.com" required autofocus>
                <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
            </div>

            <div class="field-icon">
                <label>Senha</label>
                <input type="password" name="senha" placeholder="••••••••" required>
                <svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            </div>

            <button type="submit">Entrar</button>
        </form>
    </div>

    <p class="login-hint">Sistema de gerenciamento e estatísticas de jogadores</p>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
