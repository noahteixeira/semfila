var eventoSelect = document.getElementById("evento-select");
var carregarBtn = document.getElementById("carregar-btn");
var relatorioDiv = document.getElementById("relatorio");

// carregar eventos do gestor
fetch("../backend/listar_eventos.php")
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.erro) {
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

// carregar relatorio
carregarBtn.addEventListener("click", function() {
    var eventoId = eventoSelect.value;
    if (!eventoId) {
        alert("Selecione um evento");
        return;
    }

    fetch("../backend/relatorio_entradas.php?evento_id=" + eventoId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.erro) {
                alert(data.erro);
                return;
            }

            relatorioDiv.innerHTML = "<h3>Entradas Registradas</h3>";
            
            if (data.length == 0) {
                relatorioDiv.innerHTML += "<p>Nenhuma entrada registrada para este evento.</p>";
                return;
            }

            var totalEntradas = data.length;
            relatorioDiv.innerHTML += "<p><strong>Total de entradas: " + totalEntradas + "</strong></p>";

            var table = document.createElement("table");
            table.innerHTML = '<thead><tr>' +
                '<th>Data/Hora</th>' +
                '<th>Participante</th>' +
                '<th>Funcionário</th>' +
                '<th>Método</th>' +
                '</tr></thead><tbody></tbody>';

            var tbody = table.querySelector("tbody");
            data.forEach(function(entrada) {
                var tr = document.createElement("tr");
                var dataFormatada = new Date(entrada.registrado_em).toLocaleString("pt-BR");
                var metodo = entrada.metodo == "qr_code" ? "QR Code" : "RFID";
                
                tr.innerHTML = '<td>' + dataFormatada + '</td>' +
                    '<td>' + entrada.nome + '</td>' +
                    '<td>' + entrada.funcionario_nome + '</td>' +
                    '<td>' + metodo + '</td>';
                tbody.appendChild(tr);
            });

            relatorioDiv.appendChild(table);
        })
        .catch(function(error) {
            console.error("Erro:", error);
            alert("Erro ao carregar relatório");
        });
});
