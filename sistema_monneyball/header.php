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
        'icon'  => '<circle cx="9" cy="8" r="3.2"/><path d="M3.5 19c0-3.3 2.5-5.5 5.5-5.5s5.5 2.2 5.5 5.5"/><path d="M16 8.3a3 3 0 1 1 0-6" opacity="0"/><path d="M15.2 5.2a2.9 2.9 0 1 1 2.1 5"/><path d="M15 13.7c2.6.3 4.5 2.3 4.5 5.3"/>',
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
<style>
:root{
  --bg:#07090c;
  --bg-panel:#10141a;
  --bg-panel-2:#161c24;
  --border:#212832;
  --border-soft:#1a2029;
  --blue:#5AD1E6;
  --blue-dim:#2c4a52;
  --blue-soft:rgba(90,209,230,.10);
  --text:#e7edf2;
  --text-dim:#8a96a3;
  --text-faint:#55606c;
  --good:#5fd6a0;
  --warn:#e8b84b;
  --danger:#e2665f;
}
*{box-sizing:border-box;}
html,body{margin:0;padding:0;}
body{
  background:var(--bg);
  color:var(--text);
  font-family:'Inter',system-ui,sans-serif;
  min-height:100vh;
  display:flex;
  flex-direction:row-reverse;
}
::selection{ background:var(--blue-soft); color:var(--blue); }

h1,h2,h3{
  font-family:'Chakra Petch','Inter',sans-serif;
  font-weight:600;
  letter-spacing:.01em;
  margin:0 0 4px;
  color:var(--text);
}
h1{ font-size:26px; }
h2{ font-size:16px; text-transform:uppercase; letter-spacing:.08em; color:var(--text-dim); font-weight:600; margin-top:36px; }
p{ line-height:1.6; color:var(--text-dim); }

/* ---------- Sidebar (direita) ---------- */
.sidebar{
  width:236px;
  min-width:236px;
  background:linear-gradient(180deg,#0c0f13,#0a0c10);
  border-left:1px solid var(--border);
  display:flex;
  flex-direction:column;
  position:sticky;
  top:0;
  height:100vh;
}
.brand{
  display:flex;
  align-items:center;
  gap:10px;
  padding:22px 20px;
  border-bottom:1px solid var(--border-soft);
  text-decoration:none;
  color:var(--text);
}
.brand-mark{
  width:11px; height:11px;
  background:var(--blue);
  clip-path:polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
  flex-shrink:0;
  box-shadow:0 0 10px rgba(90,209,230,.65);
}
.brand-text{
  font-family:'Chakra Petch',sans-serif;
  font-weight:700;
  font-size:15px;
  letter-spacing:.06em;
  text-transform:uppercase;
  line-height:1.15;
}
.brand-text span{ display:block; font-size:9px; font-weight:500; color:var(--text-faint); letter-spacing:.18em; margin-top:2px; }

.nav{ flex:1; padding:14px 12px; display:flex; flex-direction:column; gap:2px; overflow-y:auto; }
.nav a{
  display:flex; align-items:center; gap:11px;
  padding:10px 12px;
  border-radius:3px;
  color:var(--text-dim);
  text-decoration:none;
  font-size:13px;
  font-weight:500;
  letter-spacing:.02em;
  border-left:2px solid transparent;
  transition:background .12s ease, color .12s ease, border-color .12s ease;
}
.nav a svg{ width:16px; height:16px; flex-shrink:0; stroke:currentColor; fill:none; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
.nav a:hover{ background:var(--bg-panel-2); color:var(--text); }
.nav a.ativo{ background:var(--blue-soft); color:var(--blue); border-left-color:var(--blue); }

.nav-divider{ height:1px; background:var(--border-soft); margin:10px 8px; }

.sidebar-footer{
  padding:16px 18px 20px;
  border-top:1px solid var(--border-soft);
}
.user-row{ display:flex; align-items:center; gap:10px; margin-bottom:12px; }
.user-avatar{
  width:34px; height:34px; border-radius:3px;
  background:var(--bg-panel-2);
  border:1px solid var(--border);
  display:flex; align-items:center; justify-content:center;
  font-family:'Chakra Petch',sans-serif;
  font-weight:700; font-size:13px; color:var(--blue);
  flex-shrink:0;
}
.user-meta{ min-width:0; }
.user-name{ font-size:13px; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.badge{
  display:inline-block; margin-top:3px;
  padding:2px 7px; border-radius:2px;
  font-size:9.5px; font-weight:700; letter-spacing:.06em; text-transform:uppercase;
}
.badge-administrador{ background:rgba(226,102,95,.14); color:var(--danger); }
.badge-comissao{ background:rgba(232,184,75,.14); color:var(--warn); }
.badge-jogador{ background:rgba(90,209,230,.14); color:var(--blue); }

.btn-logout{
  display:flex; align-items:center; justify-content:center; gap:8px;
  width:100%; padding:9px 10px;
  background:transparent; border:1px solid var(--border);
  border-radius:3px; color:var(--text-dim);
  font-family:'Inter',sans-serif; font-size:12.5px; font-weight:600;
  text-decoration:none; letter-spacing:.03em;
  transition:border-color .12s ease, color .12s ease, background .12s ease;
}
.btn-logout svg{ width:14px; height:14px; stroke:currentColor; fill:none; stroke-width:2; }
.btn-logout:hover{ border-color:var(--danger); color:var(--danger); background:rgba(226,102,95,.06); }

/* ---------- Conteúdo ---------- */
.content{ flex:1; min-width:0; }
main{
  max-width:1080px;
  margin:0 auto;
  padding:40px 32px 60px;
}

/* ---------- Componentes reutilizáveis ---------- */
.card{
  background:var(--bg-panel);
  border:1px solid var(--border);
  border-radius:4px;
  padding:22px;
  position:relative;
  margin-bottom:26px;
}
.card::before, .card::after{
  content:""; position:absolute; width:10px; height:10px;
  border-color:var(--blue-dim); opacity:.7;
}
.card::before{ top:-1px; left:-1px; border-top:2px solid var(--blue); border-left:2px solid var(--blue); }
.card::after{ bottom:-1px; right:-1px; border-bottom:2px solid var(--blue); border-right:2px solid var(--blue); }

label{ display:block; font-size:11.5px; font-weight:600; color:var(--text-dim); text-transform:uppercase; letter-spacing:.05em; margin:0 0 6px; }
input, select{
  display:block; width:100%; margin-bottom:16px;
  padding:9px 11px;
  background:var(--bg-panel-2);
  border:1px solid var(--border);
  border-radius:3px;
  color:var(--text);
  font-family:'Inter',sans-serif; font-size:13.5px;
}
input:focus, select:focus{ outline:none; border-color:var(--blue); }
input::placeholder{ color:var(--text-faint); }

button, .btn{
  padding:10px 18px;
  background:var(--blue);
  color:#06181c;
  border:none; border-radius:3px;
  font-family:'Chakra Petch',sans-serif;
  font-weight:700; font-size:13px; letter-spacing:.04em; text-transform:uppercase;
  cursor:pointer;
  text-decoration:none;
  display:inline-flex; align-items:center; gap:6px;
  transition:filter .12s ease, transform .05s ease;
}
button:hover, .btn:hover{ filter:brightness(1.1); }
button:active, .btn:active{ transform:translateY(1px); }
.btn-outline{
  background:transparent; color:var(--text-dim); border:1px solid var(--border);
}
.btn-outline:hover{ color:var(--blue); border-color:var(--blue); filter:none; }

table{ border-collapse:collapse; width:100%; margin-bottom:10px; }
th, td{ border-bottom:1px solid var(--border-soft); padding:11px 12px; text-align:left; font-size:13px; }
th{
  color:var(--text-faint); font-size:10.5px; font-weight:700;
  text-transform:uppercase; letter-spacing:.06em;
  border-bottom:1px solid var(--border);
}
tbody tr{ transition:background .1s ease; }
tbody tr:hover{ background:var(--bg-panel-2); }
td.num, th.num{ text-align:right; font-variant-numeric:tabular-nums; }

.msg{ background:rgba(95,214,160,.10); border:1px solid rgba(95,214,160,.35); color:var(--good); padding:11px 14px; border-radius:3px; margin-bottom:20px; font-size:13.5px; }
.msg-warn{ background:rgba(232,184,75,.10); border-color:rgba(232,184,75,.35); color:var(--warn); }

.stat-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:16px; margin-bottom:8px; }
.stat-card{
  background:var(--bg-panel); border:1px solid var(--border); border-radius:4px;
  padding:18px 20px; position:relative;
}
.stat-card .eyebrow{ font-size:10.5px; text-transform:uppercase; letter-spacing:.08em; color:var(--text-faint); font-weight:700; margin-bottom:8px; }
.stat-card .value{ font-family:'Chakra Petch',sans-serif; font-size:30px; font-weight:700; color:var(--blue); line-height:1; }
.stat-card .sub{ font-size:12px; color:var(--text-dim); margin-top:6px; }

.filter-row{ display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:0 16px; align-items:end; }
.filter-row .full{ grid-column:1/-1; }

.eyebrow-title{ display:flex; align-items:center; gap:10px; margin-bottom:4px; }
.eyebrow-title .dash{ width:18px; height:2px; background:var(--blue); }
</style>
</head>
<body>

<aside class="sidebar">
    <a href="painel.php" class="brand">
        <span class="brand-mark"></span>
        <span class="brand-text">Valorant<br><span>TRACKER · MONEYBALL DS</span></span>
    </a>

    <nav class="nav">
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