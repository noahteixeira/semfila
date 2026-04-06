fetch("../backend/listar_meus_ingressos.php")
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        var container = document.getElementById("lista-ingressos");

        if (data.erro) {
            container.innerHTML = '<p style="color: #e94560;">Erro ao carregar ingressos: ' + data.erro + '</p>';
            return;
        }

        if (data.length == 0) {
            container.innerHTML = '<p style="color: #ccc;">Nenhum ingresso encontrado.</p>';
            return;
        }

        container.innerHTML = "";
        data.forEach(function(ingresso) {
            var card = document.createElement("div");
            card.className = "card-ingresso";
            card.innerHTML = '<h3>' + ingresso.evento_nome + '</h3>' +
                '<p><strong>Lote:</strong> ' + ingresso.nome_lote + '</p>' +
                '<p><strong>QR Code:</strong> ' + ingresso.qr_code + '</p>' +
                '<p><strong>Data do evento:</strong> ' + formatDate(ingresso.data_evento) + ' às ' + ingresso.horario_abertura + '</p>' +
                '<p><strong>Comprado em:</strong> ' + formatDateTime(ingresso.comprado_em) + '</p>' +
                '<p><strong>Status:</strong> <span class="status-' + ingresso.status + '">' + capitalize(ingresso.status) + '</span></p>';
            container.appendChild(card);
        });
    })
    .catch(function(error) {
        console.error("Erro ao carregar ingressos:", error);
        document.getElementById("lista-ingressos").innerHTML = '<p style="color: #e94560;">Erro ao carregar ingressos.</p>';
    });

function formatDate(dateString) {
    if (!dateString) return "-";
    var parts = dateString.split("-");
    return parts[2] + "/" + parts[1] + "/" + parts[0];
}

function formatDateTime(dateTimeString) {
    if (!dateTimeString) return "-";
    var date = new Date(dateTimeString);
    return date.toLocaleDateString("pt-BR") + " " + date.toLocaleTimeString("pt-BR", { hour: "2-digit", minute: "2-digit" });
}

function capitalize(value) {
    return value.charAt(0).toUpperCase() + value.slice(1);
}
