function carregarHistoricoTransacoes() {
    var historicoLista = document.getElementById("historico-lista");
    if (!historicoLista) return;

    fetch("../backend/historico_transacoes.php")
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.erro) {
                historicoLista.innerHTML = "<p>Erro ao carregar histórico: " + data.erro + "</p>";
                return;
            }

            if (!data || data.length == 0) {
                historicoLista.innerHTML = "<p>Nenhuma transação registrada.</p>";
                return;
            }

            var ul = document.createElement("ul");
            ul.style.listStyle = "none";
            ul.style.padding = "0";

            data.forEach(function(transacao) {
                var li = document.createElement("li");
                var tipo = transacao.tipo == "recarga" ? "Recarga" : "Consumo";
                var sinal = transacao.tipo == "recarga" ? "+" : "-";
                var cor = transacao.tipo == "recarga" ? "#2ecc71" : "#e94560";

                li.style.padding = "15px";
                li.style.marginBottom = "10px";
                li.style.backgroundColor = "rgba(255,255,255,0.05)";
                li.style.borderRadius = "8px";
                li.style.borderLeft = "4px solid " + cor;

                li.innerHTML = '<strong style="color: ' + cor + '">' + tipo + '</strong><br>' +
                    'Valor: <span style="color: ' + cor + '">' + sinal + 'R$ ' + parseFloat(transacao.valor).toFixed(2) + '</span><br>' +
                    'Descrição: ' + (transacao.descricao || 'N/A') + '<br>' +
                    '<small style="color: #888;">Data: ' + new Date(transacao.registrado_em).toLocaleString('pt-BR') + '</small>';
                ul.appendChild(li);
            });

            historicoLista.innerHTML = "";
            historicoLista.appendChild(ul);
        })
        .catch(function(error) {
            console.error("Erro ao carregar histórico:", error);
            historicoLista.innerHTML = "<p>Erro de conexão ao carregar histórico.</p>";
        });
}

// Carregar ao iniciar
document.addEventListener('DOMContentLoaded', carregarHistoricoTransacoes);
carregarHistoricoTransacoes();