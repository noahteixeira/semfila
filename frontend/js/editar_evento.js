var params = new URLSearchParams(window.location.search);
var id = params.get("id");

if (!id) {
    window.location.href = "listar_eventos.html";
}

if (params.get("erro") == "1") {
    document.getElementById("msg-erro").textContent = "Preencha todos os campos obrigatórios.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "data") {
    document.getElementById("msg-erro").textContent = "A data do evento não pode ser no passado.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "db") {
    document.getElementById("msg-erro").textContent = "Erro ao atualizar evento. Tente novamente.";
    document.getElementById("msg-erro").style.display = "block";
}

// carregar dados do evento
fetch("../../backend/gestor/editar_evento.php?id=" + id)
    .then(function (response) { return response.json(); })
    .then(function (evento) {
        if (evento.erro) {
            alert(evento.erro);
            window.location.href = "listar_eventos.html";
            return;
        }

        document.getElementById("evento_id").value = evento.id;
        document.getElementById("nome").value = evento.nome;
        document.getElementById("descricao").value = evento.descricao;
        document.getElementById("data_evento").value = evento.data_evento;
        document.getElementById("horario_abertura").value = evento.horario_abertura;
        document.getElementById("idade_minima").value = evento.idade_minima;
        document.getElementById("capacidade_maxima").value = evento.capacidade_maxima;
    })
    .catch(function (error) {
        console.error("Erro ao carregar evento:", error);
        alert("Erro ao carregar dados do evento.");
        window.location.href = "listar_eventos.html";
    });