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

            relatorioDiv.innerHTML += "<p><strong>Total de entradas: " + data.length + "</strong></p>";

            // Resumo por método
            var porMetodo = {};
            data.forEach(function(entrada) {
                var metodo = entrada.metodo == "qr_code" ? "QR Code" : "RFID";
                if (porMetodo[metodo]) {
                    porMetodo[metodo] = porMetodo[metodo] + 1;
                } else {
                    porMetodo[metodo] = 1;
                }
            });

            var htmlMetodo = "<h4>Por Método</h4><table><thead><tr><th>Método</th><th>Total</th></tr></thead><tbody>";
            for (var metodo in porMetodo) {
                htmlMetodo += "<tr><td>" + metodo + "</td><td>" + porMetodo[metodo] + "</td></tr>";
            }
            htmlMetodo += "</tbody></table>";
            relatorioDiv.innerHTML += htmlMetodo;

            // Resumo por horário
            var porHora = {};
            data.forEach(function(entrada) {
                var hora = entrada.registrado_em.substring(11, 16);
                if (porHora[hora]) {
                    porHora[hora] = porHora[hora] + 1;
                } else {
                    porHora[hora] = 1;
                }
            });

            var htmlHora = "<h4>Por Horário</h4><table><thead><tr><th>Horário</th><th>Total</th></tr></thead><tbody>";
            for (var hora in porHora) {
                htmlHora += "<tr><td>" + hora + "</td><td>" + porHora[hora] + "</td></tr>";
            }
            htmlHora += "</tbody></table>";
            relatorioDiv.innerHTML += htmlHora;

            // Lista detalhada
            var htmlTabela = "<h4>Detalhamento</h4>";
            htmlTabela += "<table><thead><tr><th>Data/Hora</th><th>Participante</th><th>Funcionário</th><th>Método</th></tr></thead><tbody>";
            data.forEach(function(entrada) {
                var dataFormatada = new Date(entrada.registrado_em).toLocaleString("pt-BR");
                var metodoNome = entrada.metodo == "qr_code" ? "QR Code" : "RFID";
                htmlTabela += "<tr><td>" + dataFormatada + "</td><td>" + entrada.nome + "</td><td>" + entrada.funcionario_nome + "</td><td>" + metodoNome + "</td></tr>";
            });
            htmlTabela += "</tbody></table>";
            relatorioDiv.innerHTML += htmlTabela;
        })
        .catch(function(error) {
            console.error("Erro:", error);
            alert("Erro ao carregar relatório");
        });
});
