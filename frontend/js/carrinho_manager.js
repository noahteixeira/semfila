// Gerenciador de Carrinho de Ingressos

function adicionarAoCarrinho(loteId, loteName, eventoId, eventoNome, preco, taxa) {
    var quantidade = parseInt(document.getElementById("qtd_" + loteId).value);

    if (quantidade <= 0) {
        alert("Digite uma quantidade válida!");
        return;
    }

    // obter carrinho do localStorage
    var carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];

    // verificar se já existe esse item no carrinho
    var itemExistente = carrinho.find(function(item) { return item.loteId == loteId && item.eventoId == eventoId; });

    if (itemExistente) {
        itemExistente.quantidade += quantidade;
    } else {
        carrinho.push({
            loteId: loteId,
            loteName: loteName,
            eventoId: eventoId,
            eventoNome: eventoNome,
            preco: parseFloat(preco),
            taxa: parseFloat(taxa),
            quantidade: quantidade
        });
    }

    localStorage.setItem("carrinho", JSON.stringify(carrinho));
    alert("✓ " + quantidade + " ingresso(s) adicionado(s) ao carrinho!");
}

function obterQuantidadeCarrinho() {
    var carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];
    var total = 0;
    carrinho.forEach(function(item) {
        total += item.quantidade;
    });
    return total;
}

function atualizarIndicadorCarrinho() {
    var quantidade = obterQuantidadeCarrinho();
    var badge = document.getElementById("badge-carrinho");
    if (badge) {
        if (quantidade > 0) {
            badge.textContent = quantidade;
            badge.style.display = "inline-block";
        } else {
            badge.style.display = "none";
        }
    }
}

// atualizar ao carregar página
document.addEventListener("DOMContentLoaded", atualizarIndicadorCarrinho);