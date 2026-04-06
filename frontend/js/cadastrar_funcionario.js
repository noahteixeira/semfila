var form = document.getElementById("form-funcionario");

form.addEventListener("submit", function(e) {
    e.preventDefault();

    var formData = new FormData(form);

    fetch("../backend/cadastrar_funcionario.php", {
        method: "POST",
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.erro) {
            document.getElementById("msg-erro").textContent = data.erro;
            document.getElementById("msg-erro").style.display = "block";
            document.getElementById("msg-sucesso").style.display = "none";
            return;
        }

        if (data.sucesso) {
            document.getElementById("msg-sucesso").innerHTML = "Funcionário cadastrado com sucesso!<br>Senha gerada: <div class='senha-gerada'>" + data.senha + "</div><br><small>Anote esta senha. Ela não será exibida novamente.</small>";
            document.getElementById("msg-sucesso").style.display = "block";
            document.getElementById("msg-erro").style.display = "none";
            form.style.display = "none";
        }
    })
    .catch(function(error) {
        console.error("Erro:", error);
        document.getElementById("msg-erro").textContent = "Erro ao cadastrar funcionário";
        document.getElementById("msg-erro").style.display = "block";
    });
});
