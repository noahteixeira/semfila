var params = new URLSearchParams(window.location.search);
var msgErro = document.getElementById("msg-erro");

if (params.get("erro") == "1") {
    msgErro.textContent = "E-mail ou senha incorretos.";
    msgErro.style.display = "block";
}

if (params.get("contrato") == "1") {
    msgErro.textContent = "Login do gestor bloqueado: contrato inexistente, inativo ou vencido.";
    msgErro.style.display = "block";
}

if (params.get("desativada") == "1") {
    document.getElementById("msg-sucesso").style.display = "block";
}
