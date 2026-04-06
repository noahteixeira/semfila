let fila = [];

function renderizar() {
  const lista = document.getElementById("lista");
  lista.innerHTML = "";

  fila.forEach((pessoa, index) => {
    lista.innerHTML += `
      <li>
        ${index + 1}º - ${pessoa}
        <button onclick="editar(${index})">Editar</button>
        <button onclick="remover(${index})">Remover</button>
      </li>
    `;
  });
}

function adicionar() {
  const nome = document.getElementById("nome").value;

  if (nome !== "") {
    fila.push(nome);
    document.getElementById("nome").value = "";
    renderizar();
  }
}

function editar(index) {
  const novoNome = prompt("Editar nome:", fila[index]);
  if (novoNome !== null) {
    fila[index] = novoNome;
    renderizar();
  }
}

function remover(index) {
  fila.splice(index, 1);
  renderizar();
}