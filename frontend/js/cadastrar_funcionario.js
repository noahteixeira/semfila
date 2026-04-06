var params = new URLSearchParams(window.location.search);

if (params.get("erro") == "1") {
    document.getElementById("msg-erro").textContent = "Preencha todos os campos obrigatórios.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "email") {
    document.getElementById("msg-erro").textContent = "E-mail já cadastrado no sistema.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "balada") {
    document.getElementById("msg-erro").textContent = "Você precisa ter uma balada cadastrada para adicionar funcionários.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "db") {
    document.getElementById("msg-erro").textContent = "Erro ao cadastrar funcionário. Tente novamente.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("sucesso") == "1") {
    var senha = params.get("senha");
    document.getElementById("msg-sucesso").innerHTML = "Funcionário cadastrado com sucesso!<br>Senha gerada: <div class='senha-gerada'>" + senha + "</div><br><small>Anote esta senha. Ela não será exibida novamente.</small>";
    document.getElementById("msg-sucesso").style.display = "block";
    document.getElementById("form-funcionario").style.display = "none";
}
