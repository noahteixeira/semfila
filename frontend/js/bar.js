var rfidInput = document.getElementById("rfid");
var buscarBtn = document.getElementById("buscar-btn");
var clienteInfo = document.getElementById("cliente-info");
var clienteNome = document.getElementById("cliente-nome");
var clienteSaldo = document.getElementById("cliente-saldo");
var consumoForm = document.getElementById("consumo-form");
var eventoSelect = document.getElementById("evento-select");
var produtoSelect = document.getElementById("produto-select");
var quantidadeInput = document.getElementById("quantidade");
var adicionarBtn = document.getElementById("adicionar-produto-btn");
var itensLista = document.getElementById("itens-lista");
var totalSpan = document.getElementById("total");
var confirmarBtn = document.getElementById("confirmar-btn");

var currentRfid = "";
var currentSaldo = 0;
var itens = [];
var total = 0;

// carregar eventos
fetch("../backend/listar_eventos_funcionario.php")
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.erro) {
            alert(data.erro);
            return;
        }

        eventoSelect.innerHTML = '<option value="">Selecione um evento</option>';
        data.forEach(function(evento) {
            var option = document.createElement("option");
            option.value = evento.id;
            option.textContent = evento.nome;
            eventoSelect.appendChild(option);
        });
    })
    .catch(function(error) {
        console.error("Erro:", error);
        alert("Erro ao carregar eventos");
    });

// buscar cliente por RFID
buscarBtn.addEventListener("click", function() {
    var rfid = rfidInput.value.trim();
    if (!rfid) {
        alert("Digite o código RFID");
        return;
    }

    fetch("../backend/buscar_pulseira.php?rfid=" + encodeURIComponent(rfid))
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.erro) {
                alert(data.erro);
                clienteInfo.style.display = "none";
                consumoForm.style.display = "none";
                return;
            }

            currentRfid = rfid;
            currentSaldo = parseFloat(data.saldo);
            clienteNome.textContent = data.nome;
            clienteSaldo.textContent = data.saldo;
            clienteInfo.style.display = "block";
            consumoForm.style.display = "block";
            itens = [];
            total = 0;
            atualizarItens();
        })
        .catch(function(error) {
            console.error("Erro:", error);
            alert("Erro ao buscar cliente");
        });
});

// adicionar produto
adicionarBtn.addEventListener("click", function() {
    var selected = produtoSelect.value;
    var quantidade = parseInt(quantidadeInput.value);

    if (!selected || quantidade < 1) {
        alert("Selecione um produto e quantidade válida");
        return;
    }

    var partes = selected.split("|");
    var nome = partes[0];
    var preco = parseFloat(partes[1]);

    itens.push({
        nome: nome,
        quantidade: quantidade,
        valor_unitario: preco
    });

    total += quantidade * preco;
    atualizarItens();
});

// confirmar venda
confirmarBtn.addEventListener("click", function() {
    if (itens.length == 0) {
        alert("Adicione pelo menos um produto");
        return;
    }

    if (total > currentSaldo) {
        alert("Saldo insuficiente");
        return;
    }

    var eventoId = eventoSelect.value;
    if (!eventoId) {
        alert("Selecione um evento");
        return;
    }

    var formData = new FormData();
    formData.append("rfid", currentRfid);
    formData.append("evento_id", eventoId);
    formData.append("produtos_json", JSON.stringify(itens));
    formData.append("valor_total", total);

    fetch("../backend/registrar_consumo.php", {
        method: "POST",
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.erro) {
            alert(data.erro);
            return;
        }

        alert(data.mensagem);
        rfidInput.value = "";
        clienteInfo.style.display = "none";
        consumoForm.style.display = "none";
        itens = [];
        total = 0;
    })
    .catch(function(error) {
        console.error("Erro:", error);
        alert("Erro ao registrar consumo");
    });
});

function atualizarItens() {
    itensLista.innerHTML = "";
    itens.forEach(function(item) {
        var li = document.createElement("li");
        li.textContent = item.nome + " - " + item.quantidade + "x R$ " + item.valor_unitario.toFixed(2) + " = R$ " + (item.quantidade * item.valor_unitario).toFixed(2);
        itensLista.appendChild(li);
    });
    totalSpan.textContent = total.toFixed(2);
}