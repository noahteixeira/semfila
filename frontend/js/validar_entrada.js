var qrInput = document.getElementById("qr-code");
var buscarBtn = document.getElementById("buscar-btn");
var participanteInfo = document.getElementById("participante-info");
var liberarBtn = document.getElementById("liberar-btn");
var msgSucesso = document.getElementById("msg-sucesso");
var msgErro = document.getElementById("msg-erro");

var ingressoAtual = null;

buscarBtn.addEventListener("click", function() {
    var qrCode = qrInput.value.trim();
    
    if (!qrCode) {
        mostrarErro("Digite ou escaneie um código de ingresso");
        return;
    }

    fetch("../backend/validar_entrada.php?qr_code=" + encodeURIComponent(qrCode))
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.erro) {
                mostrarErro(data.erro);
                participanteInfo.style.display = "none";
                return;
            }

            if (data.sucesso) {
                ingressoAtual = data;
                document.getElementById("participante-nome").textContent = data.nome;
                document.getElementById("participante-idade").textContent = data.idade;
                document.getElementById("evento-nome").textContent = data.evento_nome;
                
                if (data.foto_perfil) {
                    document.getElementById("participante-foto").src = data.foto_perfil;
                    document.getElementById("foto-container").style.display = "block";
                } else {
                    document.getElementById("foto-container").style.display = "none";
                }

                participanteInfo.style.display = "block";
                msgErro.style.display = "none";
                msgSucesso.style.display = "none";
            }
        })
        .catch(function(error) {
            console.error("Erro:", error);
            mostrarErro("Erro ao validar ingresso");
        });
});

liberarBtn.addEventListener("click", function() {
    if (!ingressoAtual) return;

    var formData = new FormData();
    formData.append("ingresso_id", ingressoAtual.ingresso_id);
    formData.append("evento_id", ingressoAtual.evento_id);

    fetch("../backend/registrar_entrada.php", {
        method: "POST",
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.sucesso) {
            mostrarSucesso(data.mensagem);
            qrInput.value = "";
            participanteInfo.style.display = "none";
            ingressoAtual = null;
            setTimeout(function() { qrInput.focus(); }, 1500);
        } else {
            mostrarErro(data.erro || "Erro ao registrar entrada");
        }
    })
    .catch(function(error) {
        console.error("Erro:", error);
        mostrarErro("Erro ao registrar entrada");
    });
});

function mostrarErro(mensagem) {
    msgErro.textContent = mensagem;
    msgErro.style.display = "block";
    msgSucesso.style.display = "none";
}

function mostrarSucesso(mensagem) {
    msgSucesso.textContent = mensagem;
    msgSucesso.style.display = "block";
    msgErro.style.display = "none";
}

// focar no input ao carregar a página
qrInput.focus();
