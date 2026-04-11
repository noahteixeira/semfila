var eventoSelect = document.getElementById("evento-select");
var carregarBtn = document.getElementById("carregar-btn");
var relatorioDiv = document.getElementById("relatorio");

function carregarEventos() {
    fetch("../backend/listar_eventos.php")
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.erro) {
                relatorioDiv.innerHTML = '<p class="msg-erro">' + data.erro + '</p>';
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
            console.error("Erro ao carregar eventos:", error);
            relatorioDiv.innerHTML = '<p class="msg-erro">Não foi possível carregar os eventos.</p>';
        });
}

function formatValue(key, value) {
    if (value === null || value === undefined) {
        return "";
    }

    if (typeof value === "number") {
        if (String(key).toLowerCase().includes("valor") || String(key).toLowerCase().includes("receita") || String(key).toLowerCase().includes("total")) {
            return "R$ " + value.toFixed(2);
        }
        return value;
    }

    if (String(key).toLowerCase().includes("data") || String(key).toLowerCase().includes("hora")) {
        var date = new Date(value);
        return isNaN(date.getTime()) ? value : date.toLocaleString("pt-BR");
    }

    return value;
}

function criarTabela(data) {
    var table = document.createElement("table");
    var campos = Object.keys(data[0] || {});

    var thead = document.createElement("thead");
    var tr = document.createElement("tr");
    campos.forEach(function(campo) {
        var th = document.createElement("th");
        th.textContent = campo.replace(/_/g, " ").replace(/\b\w/g, function(l) { return l.toUpperCase(); });
        tr.appendChild(th);
    });
    thead.appendChild(tr);
    table.appendChild(thead);

    var tbody = document.createElement("tbody");
    data.forEach(function(item) {
        var tr = document.createElement("tr");
        campos.forEach(function(campo) {
            var td = document.createElement("td");
            td.textContent = formatValue(campo, item[campo]);
            tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    return table;
}

function criarSecao(titulo, conteudo) {
    var section = document.createElement("div");
    var heading = document.createElement("h3");
    heading.textContent = titulo;
    section.appendChild(heading);
    section.appendChild(conteudo);
    return section;
}

function montarResposta(data) {
    relatorioDiv.innerHTML = "<h3>Relatório Geral do Evento</h3>";

    if (!data || (Array.isArray(data) && data.length === 0) || (Object.keys(data).length === 0 && data.constructor === Object)) {
        relatorioDiv.innerHTML += "<p>Nenhum dado disponível para este evento.</p>";
        return;
    }

    if (data.entradas) {
        var entradasSection = criarSecao("Entradas", criarTabela(Array.isArray(data.entradas) ? data.entradas : [data.entradas]));
        relatorioDiv.appendChild(entradasSection);
    }

    if (data.ingressos) {
        var ingressosSection = criarSecao("Ingressos", criarTabela(Array.isArray(data.ingressos) ? data.ingressos : [data.ingressos]));
        relatorioDiv.appendChild(ingressosSection);
    }

    if (data.bar) {
        var barSection = criarSecao("Consumo do Bar", criarTabela(Array.isArray(data.bar) ? data.bar : [data.bar]));
        relatorioDiv.appendChild(barSection);
    }

    if (data.resumo) {
        var resumoContainer = document.createElement("div");
        if (Array.isArray(data.resumo) && data.resumo.length > 0) {
            resumoContainer.appendChild(criarTabela(data.resumo));
        } else {
            var table = document.createElement("table");
            var tbody = document.createElement("tbody");
            Object.keys(data.resumo || {}).forEach(function(chave) {
                var tr = document.createElement("tr");
                var tdLabel = document.createElement("td");
                var tdValue = document.createElement("td");
                tdLabel.textContent = chave.replace(/_/g, " ").replace(/\b\w/g, function(l) { return l.toUpperCase(); });
                tdValue.textContent = formatValue(chave, data.resumo[chave]);
                tr.appendChild(tdLabel);
                tr.appendChild(tdValue);
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);
            resumoContainer.appendChild(table);
        }
        relatorioDiv.appendChild(criarSecao("Resumo Geral", resumoContainer));
    }

    if (!data.entradas && !data.ingressos && !data.bar && !data.resumo) {
        relatorioDiv.innerHTML += "<p>O servidor retornou um formato de relatório não reconhecido.</p>";
    }
}

carregarBtn.addEventListener("click", function() {
    var eventoId = eventoSelect.value;
    if (!eventoId) {
        alert("Selecione um evento");
        return;
    }

    fetch("../backend/relatorio_geral.php?evento_id=" + eventoId)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.erro) {
                alert(data.erro);
                return;
            }
            montarResposta(data);
        })
        .catch(function(error) {
            console.error("Erro ao carregar relatório geral:", error);
            alert("Erro ao carregar relatório geral.");
        });
});

carregarEventos();
