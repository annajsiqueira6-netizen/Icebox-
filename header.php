<?php
// ============================================================
// Cabeçalho/menu compartilhado. Inclua este arquivo DEPOIS de
// session_start() + verificação de login em cada página protegida.
// ============================================================
$perfil = $_SESSION['tipo_usuario'];
$paginaAtual = basename($_SERVER['PHP_SELF']);

function aba($href, $label, $paginaAtual) {
    $ativo = ($paginaAtual === $href) ? 'aria-current="page" class="ativo"' : '';
    echo "<a href=\"$href\" $ativo>$label</a>";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Moneyball DS</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; background: #f7f7f9; }
        nav { background: #1a1a2e; padding: 0 20px; display: flex; align-items: center; justify-content: space-between; }
        nav .abas a { display: inline-block; color: #ccc; text-decoration: none; padding: 16px 14px; font-size: 14px; }
        nav .abas a.ativo, nav .abas a:hover { color: #fff; background: #ff4655; }
        nav .usuario { color: #ccc; font-size: 13px; }
        nav .usuario a { color: #ff8a94; text-decoration: none; margin-left: 10px; }
        main { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        h1 { color: #1a1a2e; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; background: white; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 14px; }
        th { background: #1a1a2e; color: white; }
        form.card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #e0e0e0; }
        input, select { display: block; width: 100%; margin-bottom: 12px; padding: 8px; box-sizing: border-box; }
        button { padding: 8px 16px; background: #ff4655; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .msg { background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: bold; }
        .badge-administrador { background: #f8d7da; color: #721c24; }
        .badge-comissao { background: #fff3cd; color: #856404; }
        .badge-jogador { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
<nav>
    <div class="abas">
        <?php aba('painel.php', 'Painel', $paginaAtual); ?>
        <?php aba('jogadores.php', 'Jogadores', $paginaAtual); ?>
        <?php if ($perfil === 'comissao' || $perfil === 'administrador'): ?>
            <?php aba('estatisticas.php', 'Cadastrar Estatística', $paginaAtual); ?>
        <?php endif; ?>
        <?php if ($perfil === 'administrador'): ?>
            <?php aba('usuarios.php', 'Usuários', $paginaAtual); ?>
        <?php endif; ?>
    </div>
    <div class="usuario">
        <?php echo htmlspecialchars($_SESSION['nome']); ?>
        <span class="badge badge-<?php echo $perfil; ?>"><?php echo $perfil; ?></span>
        <a href="logout.php">Sair</a>
    </div>
</nav>
<main>
