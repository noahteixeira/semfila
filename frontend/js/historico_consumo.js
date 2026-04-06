// historico_consumo.js
document.addEventListener('DOMContentLoaded', function() {
    const historicoLista = document.getElementById('historico-lista');

    // Carregar histórico
    fetch('../../backend/baladeiro/historico_consumo.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            if (data.length === 0) {
                historicoLista.innerHTML = '<p>Nenhum consumo registrado.</p>';
                return;
            }

            const ul = document.createElement('ul');
            data.forEach(consumo => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <strong>${consumo.evento_nome}</strong><br>
                    Data: ${new Date(consumo.registrado_em).toLocaleString()}<br>
                    Itens: ${consumo.itens || 'N/A'}<br>
                    Total: R$ ${parseFloat(consumo.valor_total).toFixed(2)}
                `;
                ul.appendChild(li);
            });

            historicoLista.appendChild(ul);
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar histórico');
        });
});