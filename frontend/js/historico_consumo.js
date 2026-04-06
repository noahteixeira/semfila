var historicoLista = document.getElementById("historico-lista");

fetch("../backend/historico_consumo.php")
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.erro) {
            alert(data.erro);
            return;
        }

        if (data.length == 0) {
            historicoLista.innerHTML = "<p>Nenhum consumo registrado.</p>";
            return;
        }

        var ul = document.createElement("ul");
        data.forEach(function(consumo) {
            var li = document.createElement("li");
            li.innerHTML = '<strong>' + consumo.evento_nome + '</strong><br>' +
                'Data: ' + new Date(consumo.registrado_em).toLocaleString() + '<br>' +
                'Itens: ' + (consumo.itens || "N/A") + '<br>' +
                'Total: R$ ' + parseFloat(consumo.valor_total).toFixed(2);
            ul.appendChild(li);
        });

        historicoLista.appendChild(ul);
    })
    .catch(function(error) {
        console.error("Erro:", error);
        alert("Erro ao carregar histórico");
    });