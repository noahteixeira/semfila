var params = new URLSearchParams(window.location.search);

if (params.get("desativado") == "1") {
    document.getElementById("msg-sucesso").style.display = "block";
}

fetch("../backend/listar_funcionarios.php")
    .then(function (response) { return response.json(); })
    .then(function (funcionarios) {

        var tbody = document.getElementById("lista-funcionarios");

        if (funcionarios.length == 0) {
            document.getElementById("tabela-container").style.display = "none";
            document.getElementById("msg-vazio").style.display = "block";
            return;
        }

        for (var i = 0; i < funcionarios.length; i++) {
            var f = funcionarios[i];

            var tr = document.createElement("tr");

            // status
            var statusClass = f.ativo == 1 ? "badge-ativo" : "badge-inativo";
            var statusText = f.ativo == 1 ? "Ativo" : "Inativo";

            // data de criacao
            var dataCriacao = f.criado_em.split(" ")[0];
            var dataParts = dataCriacao.split("-");
            var dataFormatada = dataParts[2] + "/" + dataParts[1] + "/" + dataParts[0];

            // acoes
            var acoes = '<a href="editar_funcionario.html?id=' + f.id + '" class="btn-acao btn-editar">Editar</a>';
            if (f.ativo == 1) {
                acoes += ' <a href="#" class="btn-acao btn-desativar" onclick="return confirmarDesativar(' + f.id + ')">Desativar</a>';
            }

            tr.innerHTML = '<td>' + f.nome + '</td>' +
                '<td>' + f.email + '</td>' +
                '<td><span class="badge ' + statusClass + '">' + statusText + '</span></td>' +
                '<td>' + dataFormatada + '</td>' +
                '<td>' + acoes + '</td>';

            tbody.appendChild(tr);
        }
    })
    .catch(function (error) {
        console.error("Erro ao carregar funcionários:", error);
    });

function confirmarDesativar(id) {
    if (confirm("Desativar este funcionário?")) {
        var form = document.createElement("form");
        form.method = "POST";
        form.action = "../backend/desativar_funcionario.php";

        var inputId = document.createElement("input");
        inputId.type = "hidden";
        inputId.name = "id";
        inputId.value = id;
        form.appendChild(inputId);

        document.body.appendChild(form);
        form.submit();
    }
    return false;
}
