// bar.js
document.addEventListener('DOMContentLoaded', function() {
    const rfidInput = document.getElementById('rfid');
    const buscarBtn = document.getElementById('buscar-btn');
    const clienteInfo = document.getElementById('cliente-info');
    const clienteNome = document.getElementById('cliente-nome');
    const clienteSaldo = document.getElementById('cliente-saldo');
    const consumoForm = document.getElementById('consumo-form');
    const eventoSelect = document.getElementById('evento-select');
    const produtoSelect = document.getElementById('produto-select');
    const quantidadeInput = document.getElementById('quantidade');
    const adicionarBtn = document.getElementById('adicionar-produto-btn');
    const itensLista = document.getElementById('itens-lista');
    const totalSpan = document.getElementById('total');
    const confirmarBtn = document.getElementById('confirmar-btn');

    let currentRfid = '';
    let currentSaldo = 0;
    let itens = [];
    let total = 0;

    // Carregar eventos
    fetch('../../backend/funcionario/listar_eventos.php')
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

    // Buscar cliente por RFID
    buscarBtn.addEventListener('click', function() {
        const rfid = rfidInput.value.trim();
        if (!rfid) {
            alert('Digite o código RFID');
            return;
        }

        fetch(`../../backend/funcionario/buscar_pulseira.php?rfid=${encodeURIComponent(rfid)}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    clienteInfo.style.display = 'none';
                    consumoForm.style.display = 'none';
                    return;
                }

                currentRfid = rfid;
                currentSaldo = parseFloat(data.saldo);
                clienteNome.textContent = data.nome;
                clienteSaldo.textContent = data.saldo;
                clienteInfo.style.display = 'block';
                consumoForm.style.display = 'block';
                itens = [];
                total = 0;
                updateItens();
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao buscar cliente');
            });
    });

    // Adicionar produto
    adicionarBtn.addEventListener('click', function() {
        const selected = produtoSelect.value;
        const quantidade = parseInt(quantidadeInput.value);

        if (!selected || quantidade < 1) {
            alert('Selecione um produto e quantidade válida');
            return;
        }

        const [nome, precoStr] = selected.split('|');
        const preco = parseFloat(precoStr);

        itens.push({
            nome: nome,
            quantidade: quantidade,
            valor_unitario: preco
        });

        total += quantidade * preco;
        updateItens();
    });

    // Confirmar venda
    confirmarBtn.addEventListener('click', function() {
        if (itens.length === 0) {
            alert('Adicione pelo menos um produto');
            return;
        }

        if (total > currentSaldo) {
            alert('Saldo insuficiente');
            return;
        }

        const eventId = eventoSelect.value;
        if (!eventId) {
            alert('Selecione um evento');
            return;
        }

        const data = {
            rfid: currentRfid,
            event_id: eventId,
            produtos: itens,
            valor_total: total
        };

        fetch('../../backend/funcionario/registrar_consumo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            alert(data.message);
            // Reset
            rfidInput.value = '';
            clienteInfo.style.display = 'none';
            consumoForm.style.display = 'none';
            itens = [];
            total = 0;
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao registrar consumo');
        });
    });

    function updateItens() {
        itensLista.innerHTML = '';
        itens.forEach(item => {
            const li = document.createElement('li');
            li.textContent = `${item.nome} - ${item.quantidade}x R$ ${item.valor_unitario.toFixed(2)} = R$ ${(item.quantidade * item.valor_unitario).toFixed(2)}`;
            itensLista.appendChild(li);
        });
        totalSpan.textContent = total.toFixed(2);
    }
});