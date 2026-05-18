function carregarSaldoNavbar() {
    var saldoEl = document.getElementById("saldo-topo");
    if (!saldoEl) return;

    fetch("../backend/perfil_baladeiro.php")
        .then(function(response) { return response.json(); })
        .then(function(data) {
            var saldo = 0;
            if (data && !data.erro && data.saldo !== null && data.saldo !== undefined) {
                saldo = parseFloat(data.saldo);
                if (isNaN(saldo)) {
                    saldo = 0;
                }
            }
            saldoEl.textContent = saldo.toFixed(2);
        })
        .catch(function(error) {
            console.error("Erro ao carregar saldo da navbar:", error);
            saldoEl.textContent = "0.00";
        });
}

carregarSaldoNavbar();