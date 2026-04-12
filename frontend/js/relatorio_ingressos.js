var eventoSelect = document.getElementById("evento-select");
var carregarBtn = document.getElementById("carregar-btn");
var relatorioDiv = document.getElementById("relatorio");

function carregarEventos() {
    fetch("../backend/listar_eventos.php")
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.erro) {
                relatorioDiv.innerHTML = '<p class="msg-erro">' + data.erro + '</p>';
                return;
            }

            eventoSelect.innerHTML = '<option value="">Selecione um evento</option>';
            data.forEach(function(evento) {
                var option = document.createElement("option");
                option.value = evento.id;
                option.textContent = evento.nome;
                eventoSelect.appendChild(option);
            });
        })
        .catch(function(error) {
            console.error("Erro:", error);
        });
}

carregarBtn.addEventListener("click", function() {
    var eventoId = eventoSelect.value;
    if (!eventoId) {
        alert("Selecione um evento");
        return;
    }

    fetch("../backend/relatorio_ingressos.php?evento_id=" + eventoId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.erro) {
                alert(data.erro);
                return;
            }

            relatorioDiv.innerHTML = "<h3>Relatório de Ingressos</h3>";

            if (data.length == 0) {
                relatorioDiv.innerHTML += "<p>Nenhum dado disponível para este evento.</p>";
                return;
            }

            var html = "<table><thead><tr>" +
                "<th>Tipo</th>" +
                "<th>Descrição</th>" +
                "<th>Vendidos</th>" +
                "<th>Não Utilizados</th>" +
                "<th>Receita</th>" +
                "</tr></thead><tbody>";

            data.forEach(function(item) {
                var receita = item.receita !== "" ? "R$ " + Number(item.receita).toFixed(2) : "";
                var vendidos = item.ingressos_vendidos !== "" ? item.ingressos_vendidos : "";
                var naoUtilizados = item.ingressos_nao_utilizados !== "" ? item.ingressos_nao_utilizados : "";

                html += "<tr>" +
                    "<td>" + item.tipo + "</td>" +
                    "<td>" + item.descricao + "</td>" +
                    "<td>" + vendidos + "</td>" +
                    "<td>" + naoUtilizados + "</td>" +
                    "<td>" + receita + "</td>" +
                    "</tr>";
            });

            html += "</tbody></table>";
            relatorioDiv.innerHTML += html;
        })
        .catch(function(error) {
            console.error("Erro:", error);
            alert("Erro ao carregar relatório de ingressos.");
        });
});

carregarEventos();
