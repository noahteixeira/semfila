var msgErro = document.getElementById("msg-erro");
var msgSucesso = document.getElementById("msg-sucesso");
var saldoAtual = document.getElementById("saldo-atual");

function carregarSaldo() {
    // Simulação: carregar saldo do localStorage
    var saldo = localStorage.getItem("saldo") || "0.00";
    saldoAtual.textContent = parseFloat(saldo).toFixed(2);
}

document.getElementById("form-recarregar").addEventListener("submit", function(e) {
    e.preventDefault();

    var valor = parseFloat(document.getElementById("valor").value);
    if (isNaN(valor) || valor <= 0) {
        msgErro.textContent = "Valor inválido.";
        msgErro.style.display = "block";
        msgSucesso.style.display = "none";
        return;
    }

    // Simulação: adicionar ao saldo
    var saldoAtualValor = parseFloat(localStorage.getItem("saldo") || "0.00");
    var novoSaldo = saldoAtualValor + valor;
    localStorage.setItem("saldo", novoSaldo.toFixed(2));

    // Simulação: adicionar ao histórico
    var historico = JSON.parse(localStorage.getItem("historico_transacoes") || "[]");
    historico.unshift({
        tipo: "recarga",
        valor: valor,
        descricao: "Recarga de saldo",
        registrado_em: new Date().toISOString()
    });
    localStorage.setItem("historico_transacoes", JSON.stringify(historico));

    console.log("Saldo atualizado para:", novoSaldo);
    console.log("Histórico atualizado:", historico);

    msgSucesso.textContent = "Saldo recarregado com sucesso! Novo saldo: R$ " + novoSaldo.toFixed(2);
    msgSucesso.style.display = "block";
    msgErro.style.display = "none";
    document.getElementById("valor").value = "";
    carregarSaldo();
    
    // Atualizar navbar em todas as abas abertas
    window.dispatchEvent(new Event('storage'));
});

// carregar saldo ao iniciar
carregarSaldo();