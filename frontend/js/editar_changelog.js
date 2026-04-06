var params = new URLSearchParams(window.location.search);
var id = params.get("id");

if (!id) {
    window.location.href = "listar_changelog.html";
}

if (params.get("erro") == "1") {
    document.getElementById("msg-erro").textContent = "Preencha todos os campos obrigatórios.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "db") {
    document.getElementById("msg-erro").textContent = "Erro ao atualizar changelog. Tente novamente.";
    document.getElementById("msg-erro").style.display = "block";
}

// carregar dados do changelog
fetch("../../backend/admin/editar_changelog.php?id=" + id)
    .then(function (response) { return response.json(); })
    .then(function (changelog) {
        if (changelog.erro) {
            alert(changelog.erro);
            window.location.href = "listar_changelog.html";
            return;
        }

        document.getElementById("id").value = changelog.id;
        document.getElementById("versao").value = changelog.versao;
        document.getElementById("data").value = changelog.data;
        document.getElementById("descricao").value = changelog.descricao;
    })
    .catch(function (error) {
        console.error("Erro ao carregar changelog:", error);
        alert("Erro ao carregar changelog.");
        window.location.href = "listar_changelog.html";
    });