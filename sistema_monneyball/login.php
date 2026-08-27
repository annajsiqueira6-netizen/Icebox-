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
    <style>
        :root{
            --bg:#07090c; --bg-panel:#10141a; --bg-panel-2:#161c24;
            --border:#212832; --blue:#5AD1E6; --blue-soft:rgba(90,209,230,.10);
            --text:#e7edf2; --text-dim:#8a96a3; --text-faint:#55606c; --danger:#e2665f;
        }
        *{ box-sizing:border-box; }
        html,body{ height:100%; margin:0; }
        body{
            background:
                radial-gradient(circle at 18% 20%, rgba(90,209,230,.06), transparent 45%),
                radial-gradient(circle at 82% 78%, rgba(90,209,230,.05), transparent 45%),
                var(--bg);
            color:var(--text);
            font-family:'Inter',system-ui,sans-serif;
            display:flex; align-items:center; justify-content:center;
        }
        .wrap{ width:100%; max-width:380px; padding:24px; }
        .brand{ display:flex; align-items:center; gap:10px; justify-content:center; margin-bottom:34px; }
        .brand-mark{ width:13px; height:13px; background:var(--blue); clip-path:polygon(50% 0%,100% 50%,50% 100%,0% 50%); box-shadow:0 0 12px rgba(90,209,230,.65); }
        .brand-text{ font-family:'Chakra Petch',sans-serif; font-weight:700; font-size:19px; letter-spacing:.06em; text-transform:uppercase; }
        .brand-text span{ color:var(--text-faint); font-weight:500; }

        .card{
            background:var(--bg-panel); border:1px solid var(--border); border-radius:5px;
            padding:30px 28px; position:relative;
        }
        .card::before, .card::after{ content:""; position:absolute; width:12px; height:12px; }
        .card::before{ top:-1px; left:-1px; border-top:2px solid var(--blue); border-left:2px solid var(--blue); }
        .card::after{ bottom:-1px; right:-1px; border-bottom:2px solid var(--blue); border-right:2px solid var(--blue); }

        h1{ font-family:'Chakra Petch',sans-serif; font-size:15px; text-transform:uppercase; letter-spacing:.1em; color:var(--text-dim); font-weight:600; margin:0 0 22px; text-align:center; }

        label{ display:block; font-size:11px; font-weight:600; color:var(--text-dim); text-transform:uppercase; letter-spacing:.05em; margin:0 0 6px; }
        input{
            display:block; width:100%; margin-bottom:16px; padding:10px 12px;
            background:var(--bg-panel-2); border:1px solid var(--border); border-radius:3px;
            color:var(--text); font-family:'Inter',sans-serif; font-size:14px;
        }
        input:focus{ outline:none; border-color:var(--blue); }

        button{
            width:100%; padding:12px; margin-top:6px;
            background:var(--blue); color:#06181c; border:none; border-radius:3px;
            font-family:'Chakra Petch',sans-serif; font-weight:700; font-size:13.5px;
            letter-spacing:.05em; text-transform:uppercase; cursor:pointer;
        }
        button:hover{ filter:brightness(1.1); }

        .erro{
            background:rgba(226,102,95,.10); border:1px solid rgba(226,102,95,.35);
            color:var(--danger); padding:10px 13px; border-radius:3px; margin-bottom:18px; font-size:13px;
        }
        .hint{ text-align:center; margin-top:18px; font-size:11.5px; color:var(--text-faint); letter-spacing:.03em; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <span class="brand-mark"></span>
        <span class="brand-text">Valorant <span>Tracker</span></span>
    </div>

    <div class="card">
        <h1>Acessar plataforma</h1>

        <?php if ($erro): ?>
            <p class="erro"><?php echo htmlspecialchars($erro); ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>E-mail</label>
            <input type="email" name="email" placeholder="seu@email.com" required autofocus>

            <label>Senha</label>
            <input type="password" name="senha" placeholder="••••••••" required>

            <button type="submit">Entrar</button>
        </form>
    </div>

    <p class="hint">Sistema de gerenciamento e estatísticas de jogadores</p>
</div>
</body>
</html>
