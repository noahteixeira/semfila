var params = new URLSearchParams(window.location.search);
var msgErro = document.getElementById("msg-erro");
var formBalada = document.getElementById("form-balada");
var gestorSelect = document.getElementById("gestor_id");
var btnSubmit = document.getElementById("btn-submit");

if (params.get("erro") == "1") {
    msgErro.textContent = "Preencha todos os campos obrigatórios corretamente.";
    msgErro.style.display = "block";
}

if (params.get("erro") == "2") {
    msgErro.textContent = "CNPJ inválido. Deve conter 14 dígitos numéricos.";
    msgErro.style.display = "block";
}

if (params.get("erro") == "3") {
    msgErro.textContent = "Selecione um gestor ativo com contrato válido.";
    msgErro.style.display = "block";
}

if (params.get("erro") == "4") {
    msgErro.textContent = "Este gestor já possui uma balada ativa cadastrada.";
    msgErro.style.display = "block";
}

if (params.get("erro") == "5") {
    msgErro.textContent = "CNPJ já cadastrado para outra balada.";
    msgErro.style.display = "block";
}

if (params.get("erro") == "6") {
    msgErro.textContent = "Erro ao cadastrar balada. Tente novamente.";
    msgErro.style.display = "block";
}

if (params.get("sucesso") == "1") {
    document.getElementById("msg-sucesso").style.display = "block";
    formBalada.style.display = "none";
}

fetch("../backend/listar_gestores_para_balada.php")
    .then(function(response) { return response.json(); })
    .then(function(gestores) {
        if (gestores.erro) {
            msgErro.textContent = gestores.erro;
            msgErro.style.display = "block";
            gestorSelect.innerHTML = '<option value="">Erro ao carregar gestores</option>';
            btnSubmit.disabled = true;
            return;
        }

        gestorSelect.innerHTML = '<option value="">Selecione um gestor</option>';

        if (gestores.length == 0) {
            gestorSelect.innerHTML = '<option value="">Nenhum gestor disponível</option>';
            msgErro.textContent = "Nenhum gestor disponível para vincular uma nova balada.";
            msgErro.style.display = "block";
            btnSubmit.disabled = true;
            return;
        }

        for (var i = 0; i < gestores.length; i++) {
            var gestor = gestores[i];
            var option = document.createElement("option");
            option.value = gestor.id;
            option.textContent = gestor.nome + " - " + gestor.email;
            gestorSelect.appendChild(option);
        }
    })
    .catch(function(error) {
        console.error("Erro ao carregar gestores:", error);
        gestorSelect.innerHTML = '<option value="">Erro ao carregar gestores</option>';
        msgErro.textContent = "Erro ao carregar lista de gestores.";
        msgErro.style.display = "block";
        btnSubmit.disabled = true;
    });

var campoCnpj = document.getElementById("cnpj");
campoCnpj.addEventListener("input", function() {
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