// carregar eventos disponíveis
fetch("../backend/ver_eventos.php")
    .then(function (response) { return response.json(); })
    .then(function (eventos) {
        var container = document.getElementById("lista-eventos");

        if (eventos.length == 0) {
            container.innerHTML = '<p>Nenhum evento disponível no momento. Aguarde os gestores criarem eventos!</p>';
            return;
        }

        eventos.forEach(function (e) {
            var dataFormatada = e.data_evento.split("-").reverse().join("/");

            var eventoDiv = document.createElement("div");
            eventoDiv.className = "evento";
            eventoDiv.innerHTML = '<h3>' + e.nome + '</h3>' +
                '<p><strong>Balada:</strong> ' + e.balada_nome + '</p>' +
                '<p><strong>Data:</strong> ' + dataFormatada + ' às ' + e.horario_abertura + '</p>' +
                '<p><strong>Local:</strong> ' + e.endereco + ', ' + e.cidade + '</p>' +
                '<p><strong>Idade mínima:</strong> ' + e.idade_minima + ' anos</p>' +
                '<p><strong>Capacidade:</strong> ' + e.capacidade_maxima + ' pessoas</p>' +
                (e.descricao ? '<p><strong>Descrição:</strong> ' + e.descricao + '</p>' : '') +
                '<a href="comprar_ingresso.html?id=' + e.id + '" class="btn-comprar">Comprar Ingresso</a>';

            container.appendChild(eventoDiv);
        });
    })
    .catch(function (error) {
        console.error("Erro ao carregar eventos:", error);
        document.getElementById("lista-eventos").innerHTML = '<p>Erro ao carregar eventos.</p>';
    });