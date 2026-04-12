var semPulseira = document.getElementById("sem-pulseira");
var comPulseira = document.getElementById("com-pulseira");
var msgErro = document.getElementById("msg-erro");
var msgSucesso = document.getElementById("msg-sucesso");

function formatarData(dataStr) {
    var partes = dataStr.split("-");
    return partes[2] + "/" + partes[1] + "/" + partes[0];
}

function carregarPulseira() {
    fetch("../backend/ver_pulseira.php")
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.erro) {
                semPulseira.style.display = "block";
                comPulseira.style.display = "none";
                return;
            }

            semPulseira.style.display = "none";
            comPulseira.style.display = "block";

            document.getElementById("rfid").textContent = data.codigo_rfid;
            document.getElementById("saldo").textContent = "R$ " + parseFloat(data.saldo).toFixed(2);
            document.getElementById("status").textContent = data.status == "ativa" ? "Ativa" : "Inativa";
            document.getElementById("assinatura-fim").textContent = formatarData(data.assinatura_fim);
            document.getElementById("criado-em").textContent = formatarData(data.criado_em.split(" ")[0]);

            // esconder acoes se pulseira inativa
            if (data.status == "inativa") {
                document.getElementById("area-saldo").style.display = "none";
                document.getElementById("btn-cancelar").style.display = "none";
            }
        })
        .catch(function(error) {
            console.error("Erro:", error);
        });
}

// assinar pulseira
document.getElementById("btn-assinar").addEventListener("click", function() {
    if (!confirm("Deseja assinar a pulseira por R$ 19,90/mês?")) {
        return;
    }

    fetch("../backend/assinar_pulseira.php", {
        method: "POST"
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.erro) {
            msgErro.textContent = data.erro;
            msgErro.style.display = "block";
            msgSucesso.style.display = "none";
            return;
        }

        if (data.sucesso) {
            msgSucesso.textContent = "Pulseira assinada! Código RFID: " + data.codigo_rfid;
            msgSucesso.style.display = "block";
            msgErro.style.display = "none";
            carregarPulseira();
        }
    })
    .catch(function(error) {
        console.error("Erro:", error);
    });
});

// adicionar saldo
document.getElementById("form-saldo").addEventListener("submit", function(e) {
    e.preventDefault();

    var formData = new FormData();
    formData.append("acao", "adicionar_saldo");
    formData.append("valor", document.getElementById("valor").value);

    fetch("../backend/atualizar_pulseira.php", {
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

        if (data.sucesso) {
            msgSucesso.textContent = "Saldo adicionado! Novo saldo: R$ " + parseFloat(data.novo_saldo).toFixed(2);
            msgSucesso.style.display = "block";
            msgErro.style.display = "none";
            document.getElementById("valor").value = "";
            carregarPulseira();
        }
    })
    .catch(function(error) {
        console.error("Erro:", error);
    });
});

// renovar assinatura
document.getElementById("btn-renovar").addEventListener("click", function() {
    if (!confirm("Deseja renovar a assinatura por R$ 19,90?")) {
        return;
    }

    var formData = new FormData();
    formData.append("acao", "renovar");

    fetch("../backend/atualizar_pulseira.php", {
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

        if (data.sucesso) {
            msgSucesso.textContent = "Assinatura renovada até " + formatarData(data.assinatura_fim);
            msgSucesso.style.display = "block";
            msgErro.style.display = "none";
            carregarPulseira();
        }
    })
    .catch(function(error) {
        console.error("Erro:", error);
    });
});

// cancelar pulseira
document.getElementById("btn-cancelar").addEventListener("click", function() {
    if (!confirm("Tem certeza? O saldo será zerado e não é reembolsável.")) {
        return;
    }

    fetch("../backend/cancelar_pulseira.php", {
        method: "POST"
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.erro) {
            msgErro.textContent = data.erro;
            msgErro.style.display = "block";
            msgSucesso.style.display = "none";
            return;
        }

        if (data.sucesso) {
            msgSucesso.textContent = "Pulseira cancelada.";
            msgSucesso.style.display = "block";
            msgErro.style.display = "none";
            carregarPulseira();
        }
    })
    .catch(function(error) {
        console.error("Erro:", error);
    });
});

carregarPulseira();
