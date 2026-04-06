var params = new URLSearchParams(window.location.search);
var eventoId = params.get("id");

if (!eventoId) {
    window.location.href = "ver_eventos.html";
}

// carregar detalhes do evento
fetch("../../backend/baladeiro/ver_eventos.php")
    .then(function (response) { return response.json(); })
    .then(function (eventos) {
        var evento = eventos.find(e => e.id == eventoId);
        if (!evento) {
            document.getElementById("detalhes-evento").innerHTML = '<p>Evento não encontrado.</p>';
            return;
        }

        var dataFormatada = evento.data_evento.split("-").reverse().join("/");
        var detalhes = '<h3>' + evento.nome + '</h3>' +
            '<p><strong>Balada:</strong> ' + evento.balada_nome + '</p>' +
            '<p><strong>Data:</strong> ' + dataFormatada + '</p>' +
            '<p><strong>Horário:</strong> ' + evento.horario_abertura + '</p>' +
            '<p><strong>Local:</strong> ' + evento.endereco + '</p>' +
            '<p><strong>Cidade:</strong> ' + evento.cidade + '</p>' +
            '<p><strong>Idade mínima:</strong> ' + evento.idade_minima + ' anos</p>' +
            '<p><strong>Capacidade:</strong> ' + evento.capacidade_maxima + ' pessoas</p>' +
            (evento.descricao ? '<p><strong>Descrição:</strong><br>' + evento.descricao + '</p>' : '');

        document.getElementById("detalhes-evento").innerHTML = detalhes;

        // carregar lotes de ingressos
        carregarLotes(eventoId);
    })
    .catch(function (error) {
        console.error("Erro ao carregar evento:", error);
        document.getElementById("detalhes-evento").innerHTML = '<p>Erro ao carregar detalhes do evento.</p>';
    });

function carregarLotes(eventoId) {
    fetch("../../backend/baladeiro/listar_lotes.php?evento_id=" + eventoId)
        .then(function (response) { return response.json(); })
        .then(function (lotes) {
            var container = document.getElementById("lotes-ingressos");

            if (lotes.length == 0) {
                container.innerHTML = '<p style="color: #ccc;">Nenhum lote de ingressos disponível no momento.</p>';
                return;
            }

            container.innerHTML = '';
            lotes.forEach(function (lote) {
                var disponivel = lote.quantidade_disponivel > 0;
                var precoTotal = (parseFloat(lote.preco) + parseFloat(lote.taxa_plataforma)).toFixed(2);

                var loteDiv = document.createElement("div");
                loteDiv.className = "lote" + (disponivel ? "" : " indisponivel");

                loteDiv.innerHTML = '<h4>' + lote.nome_lote + '</h4>' +
                    '<p><strong>Preço unitário:</strong> R$ ' + parseFloat(lote.preco).toFixed(2) + '</p>' +
                    '<p class="taxa"><strong>Taxa plataforma:</strong> R$ ' + parseFloat(lote.taxa_plataforma).toFixed(2) + '</p>' +
                    '<p class="preco"><strong>Total por ingresso:</strong> R$ ' + precoTotal + '</p>' +
                    '<p><strong>Disponível:</strong> ' + lote.quantidade_disponivel + ' de ' + lote.quantidade_total + ' ingressos</p>' +
                    (disponivel ? 
                        '<div class="form-compra">' +
                        '<input type="number" id="qtd_' + lote.id + '" min="1" max="' + lote.quantidade_disponivel + '" value="1" placeholder="Qtd">' +
                        '<div class="botoes-lote">' +
                        '<button class="btn-acao" onclick="adicionarAoCarrinho(' + lote.id + ', \'' + lote.nome_lote.replace(/'/g, "\\'") + '\', ' + eventoId + ', \'' + document.getElementById("detalhes-evento").querySelector("h3").textContent.replace(/'/g, "\\'") + '\', ' + lote.preco + ', ' + lote.taxa_plataforma + ')">Adicionar ao Carrinho</button>' +
                        '<button class="btn-acao btn-comprar" onclick="comprarIngresso(' + lote.id + ', ' + eventoId + ')">Comprar Agora</button>' +
                        '</div>' +
                        '</div>'
                    : '<p style="color: #e94560; margin-top: 10px;"><strong>Indisponível</strong></p>');

                container.appendChild(loteDiv);
            });
        })
        .catch(function (error) {
            console.error("Erro ao carregar lotes:", error);
            document.getElementById("lotes-ingressos").innerHTML = '<p style="color: #e94560;">Erro ao carregar lotes de ingressos.</p>';
        });
}

function comprarIngresso(loteId, eventoId) {
    var quantidade = parseInt(document.getElementById("qtd_" + loteId).value);

    if (quantidade <= 0) {
        alert("Digite uma quantidade válida!");
        return;
    }

    var formData = new FormData();
    formData.append("lote_id", loteId);
    formData.append("evento_id", eventoId);
    formData.append("quantidade", quantidade);

    fetch("../../backend/baladeiro/comprar_ingresso.php", {
        method: "POST",
        body: formData
    })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data.sucesso) {
                document.getElementById("msg-sucesso").textContent = data.mensagem;
                document.getElementById("msg-sucesso").style.display = "block";
                document.getElementById("msg-erro").style.display = "none";
                
                // recarregar lotes após compra
                setTimeout(function() {
                    carregarLotes(eventoId);
                }, 2000);
            } else {
                document.getElementById("msg-erro").textContent = data.erro || "Erro ao comprar ingresso";
                document.getElementById("msg-erro").style.display = "block";
                document.getElementById("msg-sucesso").style.display = "none";
            }
        })
        .catch(function (error) {
            console.error("Erro:", error);
            document.getElementById("msg-erro").textContent = "Erro ao processar compra";
            document.getElementById("msg-erro").style.display = "block";
        });
}