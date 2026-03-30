var params = new URLSearchParams(window.location.search);

if (params.get("erro") == "1") {
    document.getElementById("msg-erro").textContent = "Preencha todos os campos obrigatórios.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "2") {
    document.getElementById("msg-erro").textContent = "Você precisa ter pelo menos 16 anos.";
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
    document.getElementById("msg-erro").textContent = "Erro ao atualizar dados.";
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