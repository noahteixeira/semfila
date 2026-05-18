var dadosPagamento = JSON.parse(localStorage.getItem("pagamento_ingresso_pendente") || "null");
var msgErro = document.getElementById("msg-erro");
var msgSucesso = document.getElementById("msg-sucesso");
var resumoPagamento = document.getElementById("resumo-pagamento");
var btnPagar = document.getElementById("btn-pagar");
var btnCancelar = document.getElementById("btn-cancelar");

function mostrarErro(texto) {
    msgErro.textContent = texto;
    msgErro.style.display = "block";
    msgSucesso.style.display = "none";
}

function mostrarSucesso(texto) {
    msgSucesso.textContent = texto;
    msgSucesso.style.display = "block";
    msgErro.style.display = "none";
}

function carregarResumo() {
    if (!dadosPagamento) {
        mostrarErro("Nenhum pagamento pendente encontrado.");
        btnPagar.style.display = "none";
        return;
    }

    if (dadosPagamento.tipo == "agora") {
        resumoPagamento.innerHTML = "<p><strong>Tipo:</strong> Compra rápida</p>" +
            "<p><strong>Quantidade:</strong> " + dadosPagamento.quantidade + " ingresso(s)</p>";
        return;
    }

    var totalIngressos = 0;
    var totalGeral = 0;

    dadosPagamento.carrinho.forEach(function(item) {
        totalIngressos += item.quantidade;
        totalGeral += (item.preco + item.taxa) * item.quantidade;
    });

    resumoPagamento.innerHTML = "<p><strong>Tipo:</strong> Compra pelo carrinho</p>" +
        "<p><strong>Ingressos:</strong> " + totalIngressos + "</p>" +
        "<p><strong>Total:</strong> R$ " + totalGeral.toFixed(2) + "</p>";
}

btnPagar.addEventListener("click", function() {
    if (!dadosPagamento) {
        mostrarErro("Nenhum pagamento pendente encontrado.");
        return;
    }

    var formData = new FormData();
    formData.append("pagamento_status", "aprovado");

    var url = "../backend/comprar_ingresso.php";
    if (dadosPagamento.tipo == "agora") {
        formData.append("lote_id", dadosPagamento.lote_id);
        formData.append("evento_id", dadosPagamento.evento_id);
        formData.append("quantidade", dadosPagamento.quantidade);
    } else {
        url = "../backend/finalizar_compra.php";
        formData.append("carrinho_json", JSON.stringify(dadosPagamento.carrinho));
    }

    fetch(url, {
        method: "POST",
        body: formData
    })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.erro) {
                mostrarErro(data.erro);
                return;
            }

            mostrarSucesso(data.mensagem || "Pagamento aprovado com sucesso!");
            localStorage.removeItem("pagamento_ingresso_pendente");
            localStorage.removeItem("carrinho");
            setTimeout(function() {
                window.location.href = "meus_ingressos.html";
            }, 1200);
        })
        .catch(function(error) {
            console.error("Erro:", error);
            mostrarErro("Erro ao confirmar pagamento");
        });
});

btnCancelar.addEventListener("click", function() {
    localStorage.removeItem("pagamento_ingresso_pendente");
    if (dadosPagamento && dadosPagamento.tipo == "carrinho") {
        window.location.href = "carrinho.html";
    } else {
        window.location.href = "ver_eventos.html";
    }
});

carregarResumo();
