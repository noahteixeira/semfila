var eventoSelect = document.getElementById("evento-select");
var carregarBtn = document.getElementById("carregar-btn");
var relatorioDiv = document.getElementById("relatorio");

// carregar eventos do gestor
fetch("../backend/listar_eventos.php")
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.erro) {
            alert(data.erro);
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
        alert("Erro ao carregar eventos");
    });

// carregar relatorio
carregarBtn.addEventListener("click", function() {
    var eventoId = eventoSelect.value;
    if (!eventoId) {
        alert("Selecione um evento");
        return;
    }

    fetch("../backend/relatorio_bar.php?evento_id=" + eventoId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.erro) {
                alert(data.erro);
                return;
            }

            relatorioDiv.innerHTML = "<h3>Consumos Registrados</h3>";
            if (data.length == 0) {
                relatorioDiv.innerHTML += "<p>Nenhum consumo registrado para este evento.</p>";
                return;
            }

            var table = document.createElement("table");
            table.innerHTML = '<thead><tr>' +
                '<th>Data/Hora</th>' +
                '<th>Cliente</th>' +
                '<th>Funcionário</th>' +
                '<th>Itens</th>' +
                '<th>Total</th>' +
                '</tr></thead><tbody></tbody>';

            var tbody = table.querySelector("tbody");
            data.forEach(function(consumo) {
                var tr = document.createElement("tr");
                tr.innerHTML = '<td>' + new Date(consumo.registrado_em).toLocaleString() + '</td>' +
                    '<td>' + consumo.usuario_nome + '</td>' +
                    '<td>' + consumo.funcionario_nome + '</td>' +
                    '<td>' + (consumo.itens || "N/A") + '</td>' +
                    '<td>R$ ' + parseFloat(consumo.valor_total).toFixed(2) + '</td>';
                tbody.appendChild(tr);
            });

            relatorioDiv.appendChild(table);
        })
        .catch(function(error) {
            console.error("Erro:", error);
            alert("Erro ao carregar relatório");
        });
});