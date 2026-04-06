// relatorio_bar.js
document.addEventListener('DOMContentLoaded', function() {
    const eventoSelect = document.getElementById('evento-select');
    const carregarBtn = document.getElementById('carregar-btn');
    const relatorioDiv = document.getElementById('relatorio');

    // Carregar eventos do gestor
    fetch('../../backend/gestor/listar_eventos.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            eventoSelect.innerHTML = '<option value="">Selecione um evento</option>';
            data.forEach(evento => {
                const option = document.createElement('option');
                option.value = evento.id;
                option.textContent = evento.nome;
                eventoSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar eventos');
        });

    // Carregar relatório
    carregarBtn.addEventListener('click', function() {
        const eventId = eventoSelect.value;
        if (!eventId) {
            alert('Selecione um evento');
            return;
        }

        fetch(`../../backend/gestor/relatorio_bar.php?event_id=${eventId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                relatorioDiv.innerHTML = '<h3>Consumos Registrados</h3>';
                if (data.length === 0) {
                    relatorioDiv.innerHTML += '<p>Nenhum consumo registrado para este evento.</p>';
                    return;
                }

                const table = document.createElement('table');
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Cliente</th>
                            <th>Funcionário</th>
                            <th>Itens</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                `;

                const tbody = table.querySelector('tbody');
                data.forEach(consumo => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${new Date(consumo.registrado_em).toLocaleString()}</td>
                        <td>${consumo.usuario_nome}</td>
                        <td>${consumo.funcionario_nome}</td>
                        <td>${consumo.itens || 'N/A'}</td>
                        <td>R$ ${parseFloat(consumo.valor_total).toFixed(2)}</td>
                    `;
                    tbody.appendChild(tr);
                });

                relatorioDiv.appendChild(table);
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao carregar relatório');
            });
    });
});