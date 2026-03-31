var params = new URLSearchParams(window.location.search);
var id = params.get("id");

if (!id) {
    window.location.href = "listar_gestores.html";
}

if (params.get("erro") == "1") {
    document.getElementById("msg-erro").textContent = "Preencha todos os campos obrigatórios.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "2") {
    document.getElementById("msg-erro").textContent = "CNPJ inválido. Deve conter 14 dígitos numéricos.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "3") {
    document.getElementById("msg-erro").textContent = "E-mail já cadastrado para outro usuário.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "4") {
    document.getElementById("msg-erro").textContent = "CNPJ já cadastrado para outro gestor.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("erro") == "5") {
    document.getElementById("msg-erro").textContent = "Erro ao salvar alterações. Tente novamente.";
    document.getElementById("msg-erro").style.display = "block";
}

if (params.get("sucesso") == "1") {
    document.getElementById("msg-sucesso").style.display = "block";
}

// carregar dados do gestor
fetch("../backend/editar_gestor.php?id=" + id)
    .then(function (response) { return response.json(); })
    .then(function (gestor) {
        if (gestor.erro) {
            alert(gestor.erro);
            window.location.href = "listar_gestores.html";
            return;
        }

        document.getElementById("id").value = gestor.id;
        document.getElementById("nome").value = gestor.nome;
        document.getElementById("email").value = gestor.email;
        document.getElementById("razao_social").value = gestor.razao_social;
        document.getElementById("data_inicio").value = gestor.data_inicio;
        document.getElementById("data_vencimento").value = gestor.data_vencimento;
        document.getElementById("status").value = gestor.status;
        document.getElementById("observacoes").value = gestor.observacoes || "";

        // formatar CNPJ para exibicao
        var cnpj = gestor.cnpj;
        if (cnpj.length == 14) {
            cnpj = cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5");
        }
        document.getElementById("cnpj").value = cnpj;
    })
    .catch(function (error) {
        console.error("Erro ao carregar gestor:", error);
    });

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
