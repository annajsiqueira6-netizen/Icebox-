// ==========================================================
// VALORANT TRACKER — interações de interface
// ==========================================================
document.addEventListener('DOMContentLoaded', function () {

    // Marca a página como carregada -> dispara as transições de entrada do CSS
    requestAnimationFrame(function () {
        document.body.classList.add('loaded');
    });

    // ---------- Pill deslizante atrás do link ativo da navegação ----------
    var nav = document.querySelector('.nav');
    var pill = document.getElementById('navPill');
    var ativo = nav ? nav.querySelector('a.ativo') : null;

    function posicionarPill() {
        if (!pill || !ativo || !nav) return;
        var navRect = nav.getBoundingClientRect();
        var itemRect = ativo.getBoundingClientRect();
        pill.style.top = (itemRect.top - navRect.top) + 'px';
        pill.style.height = itemRect.height + 'px';
        pill.style.opacity = '1';
    }
    posicionarPill();
    window.addEventListener('resize', posicionarPill);

    // ---------- Contadores animados (stat cards) ----------
    var contadores = document.querySelectorAll('[data-count]');
    contadores.forEach(function (el) {
        var alvo = parseFloat(el.getAttribute('data-count'));
        if (isNaN(alvo)) return;
        var duracao = 900;
        var inicio = null;
        var casasDecimais = el.getAttribute('data-count').indexOf('.') > -1 ? 1 : 0;

        function passo(ts) {
            if (!inicio) inicio = ts;
            var progresso = Math.min((ts - inicio) / duracao, 1);
            var facil = 1 - Math.pow(1 - progresso, 3); // ease-out cubic
            var valor = alvo * facil;
            el.textContent = casasDecimais ? valor.toFixed(1) : Math.round(valor);
            if (progresso < 1) requestAnimationFrame(passo);
            else el.textContent = casasDecimais ? alvo.toFixed(1) : alvo;
        }
        requestAnimationFrame(passo);
    });

    // ---------- Efeito ripple nos botões ----------
    document.querySelectorAll('button, .btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var rect = btn.getBoundingClientRect();
            var ripple = document.createElement('span');
            var tamanho = Math.max(rect.width, rect.height);
            ripple.className = 'ripple';
            ripple.style.width = ripple.style.height = tamanho + 'px';
            ripple.style.left = (e.clientX - rect.left - tamanho / 2) + 'px';
            ripple.style.top = (e.clientY - rect.top - tamanho / 2) + 'px';
            btn.appendChild(ripple);
            setTimeout(function () { ripple.remove(); }, 600);
        });
    });

    // ---------- Relógio ao vivo (hero do painel) ----------
    var relogio = document.getElementById('heroClock');
    if (relogio) {
        function atualizarRelogio() {
            var agora = new Date();
            var dias = ['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'];
            var hh = String(agora.getHours()).padStart(2, '0');
            var mm = String(agora.getMinutes()).padStart(2, '0');
            var ss = String(agora.getSeconds()).padStart(2, '0');
            relogio.innerHTML = dias[agora.getDay()] + ' &middot; <b>' + hh + ':' + mm + ':' + ss + '</b>';
        }
        atualizarRelogio();
        setInterval(atualizarRelogio, 1000);
    }

    // ---------- Toggle da sidebar no mobile ----------
    var toggle = document.getElementById('sidebarToggle');
    var sidebar = document.querySelector('.sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('aberta');
        });
        document.addEventListener('click', function (e) {
            if (sidebar.classList.contains('aberta') && !sidebar.contains(e.target) && e.target !== toggle && !toggle.contains(e.target)) {
                sidebar.classList.remove('aberta');
            }
        });
    }
});
