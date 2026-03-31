var params = new URLSearchParams(window.location.search);

if (params.get("erro") == "1") {
    document.getElementById("msg-erro").textContent = "Preencha todos os campos obrigatórios.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "2") {
    document.getElementById("msg-erro").textContent = "CNPJ inválido. Deve conter 14 dígitos numéricos.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "3") {
    document.getElementById("msg-erro").textContent = "E-mail já cadastrado no sistema.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "4") {
    document.getElementById("msg-erro").textContent = "CNPJ já cadastrado no sistema.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "5") {
    document.getElementById("msg-erro").textContent = "Erro ao cadastrar gestor. Tente novamente.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("sucesso") == "1") {
    var senha = params.get("senha");
    document.getElementById("msg-sucesso").innerHTML = "Gestor cadastrado com sucesso!<br>Senha gerada: <div class='senha-gerada'>" + senha + "</div><br><small>Anote esta senha. Ela não será exibida novamente.</small>";
    document.getElementById("msg-sucesso").style.display = "block";
    document.getElementById("form-gestor").style.display = "none";
}

// mascara CNPJ
var campoCnpj = document.getElementById("cnpj");
campoCnpj.addEventListener("input", function () {
    var valor = campoCnpj.value.replace(/\D/g, "");
    if (valor.length > 14) {
        valor = valor.substring(0, 14);
    }
    if (valor.length > 12) {
        valor = valor.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{1,2})/, "$1.$2.$3/$4-$5");
    } else if (valor.length > 8) {
        valor = valor.replace(/(\d{2})(\d{3})(\d{3})(\d{1,4})/, "$1.$2.$3/$4");
    } else if (valor.length > 5) {
        valor = valor.replace(/(\d{2})(\d{3})(\d{1,3})/, "$1.$2.$3");
    } else if (valor.length > 2) {
        valor = valor.replace(/(\d{2})(\d{1,3})/, "$1.$2");
    }
    campoCnpj.value = valor;
});
