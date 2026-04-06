var params = new URLSearchParams(window.location.search);

if (params.get("sucesso") == "1") {
    document.getElementById("msg-sucesso").textContent = "Changelog criado com sucesso!";
    document.getElementById("msg-sucesso").style.display = "block";
}

if (params.get("sucesso") == "2") {
    document.getElementById("msg-sucesso").textContent = "Changelog atualizado com sucesso!";
    document.getElementById("msg-sucesso").style.display = "block";
}

if (params.get("sucesso") == "3") {
    document.getElementById("msg-sucesso").textContent = "Changelog deletado com sucesso!";
    document.getElementById("msg-sucesso").style.display = "block";
}

if (params.get("erro") == "1") {
    document.getElementById("msg-erro").textContent = "Preencha todos os campos obrigatórios.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "db") {
    document.getElementById("msg-erro").textContent = "Erro ao processar changelog. Tente novamente.";
    document.getElementById("msg-erro").style.display = "block";
}

// carregar changelogs
fetch("../../backend/admin/listar_changelog.php")
    .then(function (response) { return response.json(); })
    .then(function (changelogs) {
        var tbody = document.getElementById("lista-changelog");

        if (changelogs.length == 0) {
            tbody.innerHTML = '<tr><td colspan="5">Nenhum changelog criado.</td></tr>';
            return;
        }

        changelogs.forEach(function (c) {
            var dataFormatada = c.data.split("-").reverse().join("/");

            var acoes = '<a href="editar_changelog.html?id=' + c.id + '" class="btn-acao btn-editar">Editar</a> ' +
                '<a href="../../backend/admin/deletar_changelog.php?id=' + c.id + '" class="btn-acao btn-deletar" onclick="return confirm(\'Tem certeza?\')">Deletar</a>';

            var tr = document.createElement("tr");
            tr.innerHTML = '<td><strong>' + c.versao + '</strong></td>' +
                '<td>' + dataFormatada + '</td>' +
                '<td>' + c.autor + '</td>' +
                '<td><small>' + c.descricao.substring(0, 100) + (c.descricao.length > 100 ? '...' : '') + '</small></td>' +
                '<td>' + acoes + '</td>';

            tbody.appendChild(tr);
        });
    })
    .catch(function (error) {
        console.error("Erro ao carregar changelogs:", error);
        document.getElementById("lista-changelog").innerHTML = '<tr><td colspan="5">Erro ao carregar changelogs.</td></tr>';
    });