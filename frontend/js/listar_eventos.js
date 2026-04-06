var params = new URLSearchParams(window.location.search);

if (params.get("sucesso") == "1") {
    document.getElementById("msg-sucesso").textContent = "Evento criado com sucesso!";
    document.getElementById("msg-sucesso").style.display = "block";
}

if (params.get("sucesso") == "2") {
    document.getElementById("msg-sucesso").textContent = "Evento atualizado com sucesso!";
    document.getElementById("msg-sucesso").style.display = "block";
}

if (params.get("sucesso") == "3") {
    document.getElementById("msg-sucesso").textContent = "Evento encerrado/cancelado com sucesso!";
    document.getElementById("msg-sucesso").style.display = "block";
}

if (params.get("erro") == "balada") {
    document.getElementById("msg-erro").textContent = "Você precisa ter uma balada cadastrada para criar eventos.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "acesso") {
    document.getElementById("msg-erro").textContent = "Acesso negado ou evento não encontrado.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "db") {
    document.getElementById("msg-erro").textContent = "Erro no banco de dados. Tente novamente.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "acao") {
    document.getElementById("msg-erro").textContent = "Ação inválida.";
    document.getElementById("msg-erro").style.display = "block";
}

// carregar eventos
fetch("../../backend/gestor/listar_eventos.php")
    .then(function (response) { return response.json(); })
    .then(function (eventos) {
        var tbody = document.getElementById("lista-eventos");

        if (eventos.length == 0) {
            tbody.innerHTML = '<tr><td colspan="7">Nenhum evento encontrado.</td></tr>';
            return;
        }

        eventos.forEach(function (e) {
            var dataFormatada = e.data_evento.split("-").reverse().join("/");
            var badgeClass = "badge-" + e.status;

            var acoes = '<a href="editar_evento.html?id=' + e.id + '" class="btn-acao btn-editar">Editar</a>';
            if (e.status == "ativo") {
                acoes += ' <a href="../../backend/gestor/encerrar_evento.php" class="btn-acao btn-encerrar" onclick="return confirmarEncerrar(' + e.id + ', \'encerrar\')">Encerrar</a>';
                acoes += ' <a href="../../backend/gestor/encerrar_evento.php" class="btn-acao btn-encerrar" onclick="return confirmarEncerrar(' + e.id + ', \'cancelar\')">Cancelar</a>';
            }

            var tr = document.createElement("tr");
            tr.innerHTML = '<td>' + e.nome + '</td>' +
                '<td>' + dataFormatada + '</td>' +
                '<td>' + e.horario_abertura + '</td>' +
                '<td>' + e.idade_minima + '</td>' +
                '<td>' + e.capacidade_maxima + '</td>' +
                '<td><span class="badge ' + badgeClass + '">' + e.status + '</span></td>' +
                '<td>' + acoes + '</td>';

            tbody.appendChild(tr);
        });
    })
    .catch(function (error) {
        console.error("Erro ao carregar eventos:", error);
        document.getElementById("lista-eventos").innerHTML = '<tr><td colspan="7">Erro ao carregar eventos.</td></tr>';
    });

function confirmarEncerrar(id, acao) {
    var mensagem = acao == "encerrar" ? "Encerrar este evento?" : "Cancelar este evento?";
    if (confirm(mensagem)) {
        // criar form para POST
        var form = document.createElement("form");
        form.method = "POST";
        form.action = "../../backend/gestor/encerrar_evento.php";

        var inputId = document.createElement("input");
        inputId.type = "hidden";
        inputId.name = "evento_id";
        inputId.value = id;
        form.appendChild(inputId);

        var inputAcao = document.createElement("input");
        inputAcao.type = "hidden";
        inputAcao.name = "acao";
        inputAcao.value = acao;
        form.appendChild(inputAcao);

        document.body.appendChild(form);
        form.submit();
    }
    return false;
}