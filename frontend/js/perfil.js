fetch("../backend/perfil_baladeiro.php")
    .then(function (response) { return response.json(); })
    .then(function (data) {
        document.getElementById("nome").textContent = data.nome;
        document.getElementById("email").textContent = data.email;
        document.getElementById("cpf").textContent = data.cpf;
        document.getElementById("data_nascimento").textContent = data.data_nascimento;
        document.getElementById("criado_em").textContent = data.criado_em;
        if (data.foto_perfil) {
            document.getElementById("foto_perfil").src = data.foto_perfil;
            document.getElementById("foto_perfil").style.display = "inline";
        }
        if (data.documento_url) {
            document.getElementById("documento_url").href = data.documento_url;
            document.getElementById("documento_url").style.display = "inline";
        }
    })
    .catch(function (error) {
        console.error("Erro ao carregar perfil:", error);
        alert("Erro ao carregar dados do perfil.");
    });