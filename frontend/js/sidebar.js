(function() {
    var cargo = document.body.getAttribute("data-cargo");
    var pagina = document.body.getAttribute("data-pagina");

    if (!cargo) return;

    var menus = {
        baladeiro: [
            { nome: "Início", icone: "fa-house", link: "area_baladeiro.html", id: "inicio" },
            { separador: true },
            { nome: "Ver Eventos", icone: "fa-calendar", link: "ver_eventos.html", id: "eventos" },
            { nome: "Meus Ingressos", icone: "fa-ticket", link: "meus_ingressos.html", id: "ingressos" },
            { nome: "Minha Pulseira", icone: "fa-id-badge", link: "pulseira.html", id: "pulseira" },
            { nome: "Histórico", icone: "fa-receipt", link: "historico_consumo.html", id: "historico" },
            { separador: true },
            { nome: "Meu Perfil", icone: "fa-user", link: "perfil_baladeiro.html", id: "perfil" },
            { nome: "Editar Dados", icone: "fa-pen-to-square", link: "editar_baladeiro.html", id: "editar" },
            { separador: true },
            { nome: "Sair", icone: "fa-right-from-bracket", link: "../backend/logout.php", id: "sair" }
        ],
        gestor: [
            { nome: "Início", icone: "fa-house", link: "area_gestor.html", id: "inicio" },
            { separador: true },
            { titulo: "Eventos" },
            { nome: "Meus Eventos", icone: "fa-calendar", link: "listar_eventos.html", id: "eventos" },
            { nome: "Criar Evento", icone: "fa-circle-plus", link: "criar_evento.html", id: "criar-evento" },
            { separador: true },
            { titulo: "Equipe" },
            { nome: "Funcionários", icone: "fa-users", link: "listar_funcionarios.html", id: "funcionarios" },
            { nome: "Novo Funcionário", icone: "fa-user-plus", link: "cadastrar_funcionario.html", id: "cadastrar-funcionario" },
            { separador: true },
            { titulo: "Relatórios" },
            { nome: "Visão Geral", icone: "fa-chart-pie", link: "relatorios.html", id: "relatorios" },
            { nome: "Entradas", icone: "fa-door-open", link: "relatorio_entradas.html", id: "rel-entradas" },
            { nome: "Ingressos", icone: "fa-ticket", link: "relatorio_ingressos.html", id: "rel-ingressos" },
            { nome: "Bar", icone: "fa-martini-glass", link: "relatorio_bar.html", id: "rel-bar" },
            { nome: "Resumo", icone: "fa-file-lines", link: "relatorio_geral.html", id: "rel-geral" },
            { separador: true },
            { nome: "Sair", icone: "fa-right-from-bracket", link: "../backend/logout.php", id: "sair" }
        ],
        funcionario: [
            { nome: "Início", icone: "fa-house", link: "area_funcionario.html", id: "inicio" },
            { separador: true },
            { nome: "Validar Entrada", icone: "fa-qrcode", link: "validar_entrada.html", id: "validar" },
            { nome: "Controle do Bar", icone: "fa-beer-mug-empty", link: "bar.html", id: "bar" },
            { nome: "Controle de Fila", icone: "fa-people-line", link: "controle_fila.html", id: "fila" },
            { separador: true },
            { nome: "Sair", icone: "fa-right-from-bracket", link: "../backend/logout.php", id: "sair" }
        ],
        admin: [
            { nome: "Início", icone: "fa-house", link: "area_admin.html", id: "inicio" },
            { separador: true },
            { nome: "Listar Gestores", icone: "fa-list", link: "listar_gestores.html", id: "gestores" },
            { nome: "Novo Gestor", icone: "fa-user-plus", link: "cadastrar_gestor.html", id: "cadastrar-gestor" },
            { separador: true },
            { nome: "Sair", icone: "fa-right-from-bracket", link: "../backend/logout.php", id: "sair" }
        ]
    };

    var itens = menus[cargo];
    if (!itens) return;

    document.body.classList.add("tem-sidebar");

    var html = '<div class="logo-area">';
    html += '<img src="assets/logo.png" alt="SemFila">';
    html += '</div>';
    html += '<nav>';

    for (var i = 0; i < itens.length; i++) {
        var item = itens[i];
        if (item.separador) {
            html += '<div class="separador"></div>';
        } else if (item.titulo) {
            html += '<div class="titulo-secao">' + item.titulo + '</div>';
        } else {
            var classe = (item.id === pagina) ? ' class="ativo"' : '';
            html += '<a href="' + item.link + '"' + classe + '>';
            html += '<i class="fa-solid ' + item.icone + '"></i>';
            html += item.nome;
            html += '</a>';
        }
    }

    html += '</nav>';

    var sidebar = document.createElement("aside");
    sidebar.className = "sidebar";
    sidebar.innerHTML = html;

    var toggle = document.createElement("button");
    toggle.className = "sidebar-toggle";
    toggle.innerHTML = '<i class="fa-solid fa-bars"></i>';
    toggle.onclick = function() {
        sidebar.classList.toggle("aberta");
    };

    document.addEventListener("click", function(e) {
        if (sidebar.classList.contains("aberta") && !sidebar.contains(e.target) && e.target !== toggle && !toggle.contains(e.target)) {
            sidebar.classList.remove("aberta");
        }
    });

    document.body.insertBefore(sidebar, document.body.firstChild);
    document.body.insertBefore(toggle, document.body.firstChild);
})();
