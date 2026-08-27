<?php
// ============================================================
// Cabeçalho/shell compartilhado. Inclua este arquivo DEPOIS de
// session_start() + verificação de login em cada página protegida.
// Monta a sidebar (direita), o cabeçalho da marca e abre <main>.
// Cada página deve fechar com: </main></div></body></html>
// ============================================================
$perfil      = $_SESSION['tipo_usuario'];
$paginaAtual = basename($_SERVER['PHP_SELF']);

// Itens de navegação: rota, rótulo, ícone (svg), perfis que podem ver
$navItens = [
    [
        'href'  => 'painel.php',
        'label' => 'Início',
        'roles' => ['administrador', 'comissao', 'jogador'],
        'icon'  => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10v9a1 1 0 0 0 1 1H9.5a1 1 0 0 0 1-1v-4h3v4a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-9"/>',
    ],
    [
        'href'  => 'jogadores.php',
        'label' => 'Jogadores',
        'roles' => ['administrador', 'comissao', 'jogador'],
        'icon'  => '<rect x="3.5" y="4" width="17" height="4.2" rx="1"/><rect x="3.5" y="10.4" width="17" height="4.2" rx="1"/><rect x="3.5" y="16.8" width="17" height="4.2" rx="1"/>',
    ],
    [
        'href'  => 'comparar.php',
        'label' => 'Comparar',
        'roles' => ['administrador', 'comissao', 'jogador'],
        'icon'  => '<path d="M8 3v18"/><path d="M16 3v18"/><path d="M4 8h4"/><path d="M4 14h4"/><path d="M16 8h4"/><path d="M16 14h4"/>',
    ],
    [
        'href'  => 'cadastrar.php',
        'label' => 'Adicionar',
        'roles' => ['administrador', 'comissao'],
        'icon'  => '<circle cx="12" cy="12" r="8.5"/><path d="M12 8.2v7.6"/><path d="M8.2 12h7.6"/>',
    ],
    [
        'href'  => 'importar.php',
        'label' => 'Importar Excel',
        'roles' => ['administrador'],
        'icon'  => '<path d="M12 15.5V4"/><path d="M7.5 8.5 12 4l4.5 4.5"/><path d="M4.5 15.5v3a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-3"/>',
    ],
    [
        'href'  => 'usuarios.php',
        'label' => 'Usuários',
        'roles' => ['administrador'],
        'icon'  => '<circle cx="9" cy="8" r="3.2"/><path d="M3.5 19c0-3.3 2.5-5.5 5.5-5.5s5.5 2.2 5.5 5.5"/><path d="M15.2 5.2a2.9 2.9 0 1 1 2.1 5"/><path d="M15 13.7c2.6.3 4.5 2.3 4.5 5.3"/>',
    ],
];

$rotuloPerfil = [
    'administrador' => 'Administrador',
    'comissao'      => 'Comissão',
    'jogador'       => 'Jogador',
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Valorant Tracker</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="ambient"><div class="scanlines"></div></div>

<button class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menu">
    <svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
</button>

<aside class="sidebar">
    <a href="painel.php" class="brand">
        <span class="brand-mark"></span>
        <span class="brand-text">Valorant<br><span>TRACKER · MONEYBALL DS</span></span>
    </a>

    <nav class="nav">
        <div class="nav-pill" id="navPill"></div>
        <?php foreach ($navItens as $item): ?>
            <?php if (in_array($perfil, $item['roles'], true)): ?>
                <?php $ativo = ($paginaAtual === $item['href']) ? 'ativo' : ''; ?>
                <a href="<?php echo $item['href']; ?>" class="<?php echo $ativo; ?>">
                    <svg viewBox="0 0 24 24"><?php echo $item['icon']; ?></svg>
                    <?php echo $item['label']; ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-row">
            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['nome'], 0, 1)); ?></div>
            <div class="user-meta">
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['nome']); ?></div>
                <span class="badge badge-<?php echo $perfil; ?>"><?php echo htmlspecialchars($rotuloPerfil[$perfil] ?? $perfil); ?></span>
            </div>
        </div>
        <a href="logout.php" class="btn-logout">
            <svg viewBox="0 0 24 24"><path d="M9 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h3"/><path d="M15 16l4-4-4-4"/><path d="M19 12H9"/></svg>
            Sair
        </a>
    </div>
</aside>

<div class="content">
<main>