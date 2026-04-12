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

    fetch("../backend/relatorio_geral.php?evento_id=" + eventoId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.erro) {
                alert(data.erro);
                return;
            }

            relatorioDiv.innerHTML = "";

            // Resumo geral
            if (data.resumo && data.resumo.length > 0) {
                var htmlResumo = "<h3>Resumo Geral</h3>";
                htmlResumo += "<table><thead><tr><th>Métrica</th><th>Valor</th></tr></thead><tbody>";
                data.resumo.forEach(function(item) {
                    var valor = item.valor;
                    if (item.metrica.toLowerCase().indexOf("receita") >= 0) {
                        valor = "R$ " + Number(item.valor).toFixed(2);
                    }
                    htmlResumo += "<tr><td>" + item.metrica + "</td><td>" + valor + "</td></tr>";
                });
                htmlResumo += "</tbody></table>";
                relatorioDiv.innerHTML += htmlResumo;
            }

            // Entradas
            if (data.entradas && data.entradas.length > 0) {
                var htmlEntradas = "<h3>Entradas</h3>";
                htmlEntradas += "<table><thead><tr><th>Tipo</th><th>Descrição</th><th>Total</th></tr></thead><tbody>";
                data.entradas.forEach(function(item) {
                    htmlEntradas += "<tr><td>" + item.tipo + "</td><td>" + item.descricao + "</td><td>" + item.total + "</td></tr>";
                });
                htmlEntradas += "</tbody></table>";
                relatorioDiv.innerHTML += htmlEntradas;
            }

            // Ingressos
            if (data.ingressos && data.ingressos.length > 0) {
                var htmlIngressos = "<h3>Ingressos</h3>";
                htmlIngressos += "<table><thead><tr><th>Tipo</th><th>Descrição</th><th>Vendidos</th><th>Não Utilizados</th><th>Receita</th></tr></thead><tbody>";
                data.ingressos.forEach(function(item) {
                    var receita = item.receita !== "" ? "R$ " + Number(item.receita).toFixed(2) : "";
                    var vendidos = item.ingressos_vendidos !== "" ? item.ingressos_vendidos : "";
                    var naoUtilizados = item.ingressos_nao_utilizados !== "" ? item.ingressos_nao_utilizados : "";
                    htmlIngressos += "<tr><td>" + item.tipo + "</td><td>" + item.descricao + "</td><td>" + vendidos + "</td><td>" + naoUtilizados + "</td><td>" + receita + "</td></tr>";
                });
                htmlIngressos += "</tbody></table>";
                relatorioDiv.innerHTML += htmlIngressos;
            }

            // Bar
            if (data.bar && data.bar.length > 0) {
                var htmlBar = "<h3>Consumo do Bar</h3>";
                htmlBar += "<table><thead><tr><th>Tipo</th><th>Descrição</th><th>Quantidade</th><th>Receita</th></tr></thead><tbody>";
                data.bar.forEach(function(item) {
                    var receita = item.receita !== "" ? "R$ " + Number(item.receita).toFixed(2) : "";
                    var quantidade = item.quantidade !== "" ? item.quantidade : "";
                    htmlBar += "<tr><td>" + item.tipo + "</td><td>" + item.descricao + "</td><td>" + quantidade + "</td><td>" + receita + "</td></tr>";
                });
                htmlBar += "</tbody></table>";
                relatorioDiv.innerHTML += htmlBar;
            }

            if (!data.entradas && !data.ingressos && !data.bar && !data.resumo) {
                relatorioDiv.innerHTML = "<p>Nenhum dado disponível para este evento.</p>";
            }
        })
        .catch(function(error) {
            console.error("Erro:", error);
            alert("Erro ao carregar relatório geral.");
        });
});

carregarEventos();
