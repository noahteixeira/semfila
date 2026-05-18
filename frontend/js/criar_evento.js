var params = new URLSearchParams(window.location.search);

if (params.get("erro") == "1") {
    document.getElementById("msg-erro").textContent = "Preencha todos os campos obrigatórios.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "data") {
    document.getElementById("msg-erro").textContent = "A data do evento não pode ser no passado.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "db") {
    document.getElementById("msg-erro").textContent = "Erro ao criar evento. Tente novamente.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "capacidade") {
    document.getElementById("msg-erro").textContent = "A capacidade do evento não pode ser maior que a capacidade da balada.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "lote") {
    document.getElementById("msg-erro").textContent = "Preencha os dados do lote corretamente.";
    document.getElementById("msg-erro").style.display = "block";
}