// Script para carregar saldo na navbar e inicializar dados
function inicializarDados() {
    // Se primeira vez, criar dados de teste
    if (!localStorage.getItem("saldo")) {
        localStorage.setItem("saldo", "0.00");
        localStorage.setItem("historico_transacoes", JSON.stringify([
            {
                tipo: "recarga",
                valor: 50.00,
                descricao: "Recarga de saldo de teste",
                registrado_em: new Date(Date.now() - 3600000).toISOString()
            },
            {
                tipo: "consumo",
                valor: 15.00,
                descricao: "Consumo no bar",
                registrado_em: new Date(Date.now() - 1800000).toISOString()
            },
            {
                tipo: "recarga",
                valor: 100.00,
                descricao: "Recarga de saldo",
                registrado_em: new Date().toISOString()
            }
        ]));
    }
}

function carregarSaldoNavbar() {
    var saldo = localStorage.getItem("saldo") || "0.00";
    if (document.getElementById("saldo-topo")) {
        document.getElementById("saldo-topo").textContent = parseFloat(saldo).toFixed(2);
    }
}

// Inicializar dados e carregar saldo ao iniciar
inicializarDados();
carregarSaldoNavbar();