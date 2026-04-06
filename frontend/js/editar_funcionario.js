var params = new URLSearchParams(window.location.search);
var id = params.get("id");

if (!id) {
    window.location.href = "listar_funcionarios.html";
}

if (params.get("erro") == "1") {
    document.getElementById("msg-erro").textContent = "Preencha todos os campos obrigatórios.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "email") {
    document.getElementById("msg-erro").textContent = "E-mail já cadastrado para outro usuário.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "db") {
    document.getElementById("msg-erro").textContent = "Erro ao salvar alterações. Tente novamente.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("sucesso") == "1") {
    document.getElementById("msg-sucesso").style.display = "block";
}

// carregar dados do funcionario
fetch("../backend/editar_funcionario.php?id=" + id)
    .then(function (response) { return response.json(); })
    .then(function (funcionario) {
        if (funcionario.erro) {
            alert(funcionario.erro);
            window.location.href = "listar_funcionarios.html";
            return;
        }

        document.getElementById("id").value = funcionario.id;
        document.getElementById("nome").value = funcionario.nome;
        document.getElementById("email").value = funcionario.email;
    })
    .catch(function (error) {
        console.error("Erro ao carregar funcionário:", error);
    });
