var params = new URLSearchParams(window.location.search);

if (params.get("erro") == "1") {
    document.getElementById("msg-erro").textContent = "Preencha todos os campos obrigatórios.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "2") {
    document.getElementById("msg-erro").textContent = "Você precisa ter pelo menos 16 anos para se cadastrar.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "3") {
    document.getElementById("msg-erro").textContent = "A senha deve ter no mínimo 6 caracteres.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "4") {
    document.getElementById("msg-erro").textContent = "As senhas não são iguais.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "5") {
    document.getElementById("msg-erro").textContent = "E-mail ou CPF já cadastrado.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "6") {
    document.getElementById("msg-erro").textContent = "Foto de perfil inválida. Envie JPG ou PNG (máx 2MB).";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "7") {
    document.getElementById("msg-erro").textContent = "Documento inválido. Envie JPG, PNG ou PDF (máx 5MB).";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("sucesso") == "1") {
    document.getElementById("msg-sucesso").style.display = "block";
    document.getElementById("form-cadastro").style.display = "none";
}

// mascara simples pro CPF
var campoCpf = document.getElementById("cpf");
campoCpf.addEventListener("input", function () {
    var valor = campoCpf.value.replace(/\D/g, "");
    if (valor.length > 11) {
        valor = valor.substring(0, 11);
    }
    if (valor.length > 9) {
        valor = valor.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, "$1.$2.$3-$4");
    } else if (valor.length > 6) {
        valor = valor.replace(/(\d{3})(\d{3})(\d{1,3})/, "$1.$2.$3");
    } else if (valor.length > 3) {
        valor = valor.replace(/(\d{3})(\d{1,3})/, "$1.$2");
    }
    campoCpf.value = valor;
});
