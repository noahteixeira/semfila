var params = new URLSearchParams(window.location.search);

if (params.get("desativado") == "1") {
    document.getElementById("msg-sucesso").style.display = "block";
}

fetch("../backend/listar_gestores.php")
    .then(function (response) { return response.json(); })
    .then(function (gestores) {

        var tbody = document.getElementById("lista-gestores");

        if (gestores.length == 0) {
            document.getElementById("tabela-container").style.display = "none";
            document.getElementById("msg-vazio").style.display = "block";
            return;
        }

        for (var i = 0; i < gestores.length; i++) {
            var g = gestores[i];

            var tr = document.createElement("tr");

            // formatar CNPJ para exibicao
            var cnpj = g.cnpj;
            if (cnpj.length == 14) {
                cnpj = cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5");
            }

            // formatar data
            var vencimento = g.data_vencimento.split("-");
            var dataFormatada = vencimento[2] + "/" + vencimento[1] + "/" + vencimento[0];

            // badge de status
            var badgeClass = g.status == "ativo" ? "badge-ativo" : "badge-inativo";

            // acoes
            var acoes = '<a href="editar_gestor.html?id=' + g.id + '" class="btn-acao btn-editar">Editar</a>';
            if (g.status == "ativo") {
                acoes += ' <a href="../backend/desativar_gestor.php?id=' + g.id + '" class="btn-acao btn-desativar" onclick="return confirm(\'Desativar este gestor?\')">Desativar</a>';
            }

            tr.innerHTML = '<td>' + g.nome + '</td>' +
                '<td>' + g.email + '</td>' +
                '<td>' + cnpj + '</td>' +
                '<td>' + g.razao_social + '</td>' +
                '<td>' + dataFormatada + '</td>' +
                '<td><span class="badge ' + badgeClass + '">' + g.status + '</span></td>' +
                '<td>' + acoes + '</td>';

            tbody.appendChild(tr);
        }
    })
    .catch(function (error) {
        console.error("Erro ao carregar gestores:", error);
    });
