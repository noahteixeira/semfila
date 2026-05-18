var formProduto = document.getElementById("form-produto");
var listaProdutos = document.getElementById("lista-produtos");
var msgErro = document.getElementById("msg-erro");
var msgSucesso = document.getElementById("msg-sucesso");

function mostrarErro(texto) {
    msgErro.textContent = texto;
    msgErro.style.display = "block";
    msgSucesso.style.display = "none";
}

function mostrarSucesso(texto) {
    msgSucesso.textContent = texto;
    msgSucesso.style.display = "block";
    msgErro.style.display = "none";
}

function carregarProdutos() {
    fetch("../backend/listar_produtos_bar.php")
        .then(function(response) { return response.json(); })
        .then(function(data) {
            listaProdutos.innerHTML = "";

            if (data.erro) {
                mostrarErro(data.erro);
                return;
            }

            if (data.length == 0) {
                listaProdutos.innerHTML = '<tr><td colspan="3">Nenhum produto cadastrado.</td></tr>';
                return;
            }

            data.forEach(function(produto) {
                var tr = document.createElement("tr");
                tr.innerHTML = '<td>' + produto.nome + '</td>' +
                    '<td>R$ ' + parseFloat(produto.preco).toFixed(2) + '</td>' +
                    '<td><button class="btn-acao btn-encerrar" data-id="' + produto.id + '">Remover</button></td>';
                listaProdutos.appendChild(tr);
            });
        })
        .catch(function(error) {
            console.error("Erro:", error);
            mostrarErro("Erro ao carregar produtos");
        });
}

formProduto.addEventListener("submit", function(e) {
    e.preventDefault();

    var nome = document.getElementById("nome").value.trim();
    var preco = parseFloat(document.getElementById("preco").value);

    if (nome == "" || isNaN(preco) || preco <= 0) {
        mostrarErro("Preencha os dados corretamente");
        return;
    }

    var formData = new FormData();
    formData.append("nome", nome);
    formData.append("preco", preco.toFixed(2));

    fetch("../backend/cadastrar_produto_bar.php", {
        method: "POST",
        body: formData
    })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.erro) {
                mostrarErro(data.erro);
                return;
            }

            mostrarSucesso(data.mensagem);
            formProduto.reset();
            carregarProdutos();
        })
        .catch(function(error) {
            console.error("Erro:", error);
            mostrarErro("Erro ao cadastrar produto");
        });
});

listaProdutos.addEventListener("click", function(e) {
    if (!e.target.classList.contains("btn-encerrar")) {
        return;
    }

    if (!confirm("Deseja remover este produto?")) {
        return;
    }

    var formData = new FormData();
    formData.append("id", e.target.getAttribute("data-id"));

    fetch("../backend/remover_produto_bar.php", {
        method: "POST",
        body: formData
    })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.erro) {
                mostrarErro(data.erro);
                return;
            }

            mostrarSucesso(data.mensagem);
            carregarProdutos();
        })
        .catch(function(error) {
            console.error("Erro:", error);
            mostrarErro("Erro ao remover produto");
        });
});

carregarProdutos();
