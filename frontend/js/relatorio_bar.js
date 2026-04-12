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

            relatorioDiv.innerHTML = "<h3>Relatório do Bar</h3>";

            // Receita total
            relatorioDiv.innerHTML += "<p><strong>Receita total do bar: R$ " + Number(data.receita_total).toFixed(2) + "</strong></p>";

            // Produtos mais vendidos
            if (data.produtos.length > 0) {
                var htmlProdutos = "<h4>Produtos Mais Vendidos</h4>";
                htmlProdutos += "<table><thead><tr><th>Produto</th><th>Quantidade</th><th>Receita</th></tr></thead><tbody>";
                data.produtos.forEach(function(produto) {
                    htmlProdutos += "<tr><td>" + produto.produto + "</td><td>" + produto.quantidade + "</td><td>R$ " + Number(produto.receita).toFixed(2) + "</td></tr>";
                });
                htmlProdutos += "</tbody></table>";
                relatorioDiv.innerHTML += htmlProdutos;
            }

            // Consumos individuais
            if (data.consumos.length > 0) {
                var htmlConsumos = "<h4>Consumos Registrados</h4>";
                htmlConsumos += "<table><thead><tr><th>Data/Hora</th><th>Cliente</th><th>Funcionário</th><th>Itens</th><th>Total</th></tr></thead><tbody>";
                data.consumos.forEach(function(consumo) {
                    var dataFormatada = new Date(consumo.registrado_em).toLocaleString("pt-BR");
                    htmlConsumos += "<tr>" +
                        "<td>" + dataFormatada + "</td>" +
                        "<td>" + consumo.usuario_nome + "</td>" +
                        "<td>" + consumo.funcionario_nome + "</td>" +
                        "<td>" + (consumo.itens || "N/A") + "</td>" +
                        "<td>R$ " + parseFloat(consumo.valor_total).toFixed(2) + "</td>" +
                        "</tr>";
                });
                htmlConsumos += "</tbody></table>";
                relatorioDiv.innerHTML += htmlConsumos;
            } else {
                relatorioDiv.innerHTML += "<p>Nenhum consumo registrado para este evento.</p>";
            }
        })
        .catch(function(error) {
            console.error("Erro:", error);
            alert("Erro ao carregar relatório");
        });
});