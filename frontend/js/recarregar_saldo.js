var msgErro = document.getElementById("msg-erro");
var msgSucesso = document.getElementById("msg-sucesso");
var saldoAtual = document.getElementById("saldo-atual");

function carregarSaldo() {
    fetch("../backend/ver_pulseira.php")
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.erro) {
                msgErro.textContent = data.erro;
                msgErro.style.display = "block";
                msgSucesso.style.display = "none";
                saldoAtual.textContent = "0.00";
                return;
            }

            saldoAtual.textContent = parseFloat(data.saldo).toFixed(2);
        })
        .catch(function(error) {
            console.error("Erro:", error);
            msgErro.textContent = "Erro ao carregar saldo.";
            msgErro.style.display = "block";
            msgSucesso.style.display = "none";
        });
}

function limparQueryPagamento() {
    if (window.history && window.history.replaceState) {
        window.history.replaceState({}, document.title, "recarregar_saldo.html");
    }
}

function confirmarPagamentoStripe(sessionId) {
    fetch("../backend/stripe_confirmar_recarga.php?session_id=" + encodeURIComponent(sessionId))
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.erro) {
                msgErro.textContent = data.erro;
                msgErro.style.display = "block";
                msgSucesso.style.display = "none";
                limparQueryPagamento();
                return;
            }

            if (data.sucesso) {
                msgSucesso.textContent = "Pagamento confirmado! Novo saldo: R$ " + parseFloat(data.novo_saldo).toFixed(2);
                msgSucesso.style.display = "block";
                msgErro.style.display = "none";
                carregarSaldo();
                document.getElementById("valor").value = "";
                limparQueryPagamento();
            }
        })
        .catch(function(error) {
            console.error("Erro:", error);
            msgErro.textContent = "Erro ao confirmar pagamento.";
            msgErro.style.display = "block";
            msgSucesso.style.display = "none";
            limparQueryPagamento();
        });
}

function verificarRetornoPagamento() {
    var params = new URLSearchParams(window.location.search);
    var statusPagamento = params.get("pagamento");
    var sessionId = params.get("session_id");

    if (statusPagamento == "cancelado") {
        msgErro.textContent = "Pagamento cancelado.";
        msgErro.style.display = "block";
        msgSucesso.style.display = "none";
        limparQueryPagamento();
        return;
    }

    if (statusPagamento == "sucesso" && sessionId) {
        confirmarPagamentoStripe(sessionId);
    }
}

document.getElementById("form-recarregar").addEventListener("submit", function(e) {
    e.preventDefault();

    var valor = parseFloat(document.getElementById("valor").value);
    if (isNaN(valor) || valor <= 0) {
        msgErro.textContent = "Valor inválido.";
        msgErro.style.display = "block";
        msgSucesso.style.display = "none";
        return;
    }

    var formData = new FormData();
    formData.append("valor", valor.toFixed(2));
    formData.append("origem", "recarregar");

    fetch("../backend/stripe_criar_checkout_recarga.php", {
        method: "POST",
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.erro) {
            msgErro.textContent = data.erro;
            msgErro.style.display = "block";
            msgSucesso.style.display = "none";
            return;
        }

        if (data.sucesso && data.checkout_url) {
            window.location.href = data.checkout_url;
        }
    })
    .catch(function(error) {
        console.error("Erro:", error);
        msgErro.textContent = "Erro ao iniciar checkout.";
        msgErro.style.display = "block";
        msgSucesso.style.display = "none";
    });
});

// carregar saldo ao iniciar
carregarSaldo();
verificarRetornoPagamento();