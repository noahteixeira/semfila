<?php
include("auth_check.php");
include("conexao.php");

if ($_SESSION["usuario_tipo"] != "admin") {
    header("Location: ../frontend/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $gestor_id = (int)$_POST["gestor_id"];
    $nome = trim($_POST["nome"]);
    $cnpj = trim($_POST["cnpj"]);
    $endereco = trim($_POST["endereco"]);
    $cidade = trim($_POST["cidade"]);
    $capacidade_maxima = (int)$_POST["capacidade_maxima"];
    $logo_url = trim($_POST["logo_url"]);

    if ($gestor_id <= 0 || empty($nome) || empty($cnpj) || empty($endereco) || empty($cidade) || $capacidade_maxima <= 0) {
        header("Location: ../frontend/cadastrar_balada.html?erro=1");
        exit();
    }

    $cnpj_limpo = preg_replace("/\D/", "", $cnpj);
    if (strlen($cnpj_limpo) != 14) {
        header("Location: ../frontend/cadastrar_balada.html?erro=2");
        exit();
    }

    $sql_gestor = "SELECT u.id
                   FROM usuarios u
                   INNER JOIN contratos_gestores c ON c.usuario_id = u.id
                   WHERE u.id = ?
                   AND u.tipo = 'gestor'
                   AND u.ativo = 1
                   AND c.status = 'ativo'
                   AND c.data_vencimento >= CURDATE()";

    $stmt_gestor = mysqli_prepare($conexao, $sql_gestor);
    mysqli_stmt_bind_param($stmt_gestor, "i", $gestor_id);
    mysqli_stmt_execute($stmt_gestor);
    $resultado_gestor = mysqli_stmt_get_result($stmt_gestor);
    $gestor = mysqli_fetch_assoc($resultado_gestor);
    mysqli_stmt_close($stmt_gestor);

    if (!$gestor) {
        header("Location: ../frontend/cadastrar_balada.html?erro=3");
        exit();
    }

    $sql_balada = "SELECT id FROM baladas WHERE gestor_id = ? AND ativo = 1";
    $stmt_balada = mysqli_prepare($conexao, $sql_balada);
    mysqli_stmt_bind_param($stmt_balada, "i", $gestor_id);
    mysqli_stmt_execute($stmt_balada);
    $resultado_balada = mysqli_stmt_get_result($stmt_balada);
    $balada = mysqli_fetch_assoc($resultado_balada);
    mysqli_stmt_close($stmt_balada);

    if ($balada) {
        header("Location: ../frontend/cadastrar_balada.html?erro=4");
        exit();
    }

    $sql_cnpj = "SELECT id FROM baladas WHERE cnpj = ?";
    $stmt_cnpj = mysqli_prepare($conexao, $sql_cnpj);
    mysqli_stmt_bind_param($stmt_cnpj, "s", $cnpj_limpo);
    mysqli_stmt_execute($stmt_cnpj);
    $resultado_cnpj = mysqli_stmt_get_result($stmt_cnpj);

    if (mysqli_num_rows($resultado_cnpj) > 0) {
        mysqli_stmt_close($stmt_cnpj);
        header("Location: ../frontend/cadastrar_balada.html?erro=5");
        exit();
    }
    mysqli_stmt_close($stmt_cnpj);

    if (empty($logo_url)) {
        $logo_url = null;
    }

    $sql = "INSERT INTO baladas (gestor_id, nome, cnpj, endereco, cidade, capacidade_maxima, logo_url)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "issssis", $gestor_id, $nome, $cnpj_limpo, $endereco, $cidade, $capacidade_maxima, $logo_url);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../frontend/cadastrar_balada.html?sucesso=1");
    } else {
        header("Location: ../frontend/cadastrar_balada.html?erro=6");
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conexao);
    exit();

} else {
    header("Location: ../frontend/cadastrar_balada.html");
    exit();
}
?>