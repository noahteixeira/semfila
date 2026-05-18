// Carregar e exibir carrinho

function carregarCarrinho() {
    var carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];

    if (carrinho.length == 0) {
        document.getElementById("msg-vazio").style.display = "block";
        document.getElementById("carrinho-content").style.display = "none";
        return;
    }

    document.getElementById("msg-vazio").style.display = "none";
    document.getElementById("carrinho-content").style.display = "block";

    var tbody = document.getElementById("itens-carrinho");
    tbody.innerHTML = "";

    var subtotal = 0;
    var totalTaxas = 0;

    carrinho.forEach(function(item, index) {
        var precoItem = item.preco * item.quantidade;
        var taxaItem = item.taxa * item.quantidade;
        var total = precoItem + taxaItem;

        subtotal += precoItem;
        totalTaxas += taxaItem;

        var tr = document.createElement("tr");
        tr.innerHTML = '<td><strong>' + item.eventoNome + '</strong></td>' +
            '<td>' + item.loteName + '</td>' +
            '<td>R$ ' + item.preco.toFixed(2) + '</td>' +
            '<td>R$ ' + item.taxa.toFixed(2) + '</td>' +
            '<td><input type="number" value="' + item.quantidade + '" min="1" onchange="atualizarQuantidade(' + index + ', this.value)" class="qtd-input"></td>' +
            '<td><strong>R$ ' + total.toFixed(2) + '</strong></td>' +
            '<td><button onclick="removerDoCarrinho(' + index + ')" class="btn-remover">Remover</button></td>';
        tbody.appendChild(tr);
    });

    // atualizar totais
    var totalGeral = subtotal + totalTaxas;
    document.getElementById("subtotal").textContent = "R$ " + subtotal.toFixed(2);
    document.getElementById("total-taxas").textContent = "R$ " + totalTaxas.toFixed(2);
    document.getElementById("total-geral").textContent = "R$ " + totalGeral.toFixed(2);
}

function removerDoCarrinho(index) {
    if (confirm("Tem certeza que deseja remover este item?")) {
        var carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];
        carrinho.splice(index, 1);
        localStorage.setItem("carrinho", JSON.stringify(carrinho));
        atualizarIndicadorCarrinho();
        carregarCarrinho();
    }
}

function atualizarQuantidade(index, novaQuantidade) {
    novaQuantidade = parseInt(novaQuantidade);

    if (novaQuantidade <= 0) {
        alert("Quantidade deve ser maior que 0");
        carregarCarrinho();
        return;
    }

    var carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];
    carrinho[index].quantidade = novaQuantidade;
    localStorage.setItem("carrinho", JSON.stringify(carrinho));
    carregarCarrinho();
}

function finalizarCompra() {
    var carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];

    if (carrinho.length == 0) {
        alert("Carrinho vazio!");
        return;
    }

    localStorage.setItem("pagamento_ingresso_pendente", JSON.stringify({
        tipo: "carrinho",
        carrinho: carrinho
    }));

    window.location.href = "pagamento_ingresso.html";
}

// Carregar ao abrir página
carregarCarrinho();