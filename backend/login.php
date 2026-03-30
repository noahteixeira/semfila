<?php
session_start();
include("conexao.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $senha = $_POST["senha"];

    if (empty($email) || empty($senha)) {
        header("Location: ../frontend/login.html?erro=1");
        exit();
    }

    $sql = "SELECT id, nome, senha, tipo, ativo FROM usuarios WHERE email = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if ($usuario = mysqli_fetch_assoc($resultado)) {

        if ($usuario["ativo"] == 0) {
            header("Location: ../frontend/login.html?erro=1");
            exit();
        }

        if (password_verify($senha, $usuario["senha"])) {

            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["usuario_nome"] = $usuario["nome"];
            $_SESSION["usuario_tipo"] = $usuario["tipo"];

            if ($usuario["tipo"] == "baladeiro") {
                header("Location: ../frontend/area_baladeiro.php");
            } elseif ($usuario["tipo"] == "gestor") {
                header("Location: ../frontend/area_gestor.php");
            } elseif ($usuario["tipo"] == "funcionario") {
                header("Location: ../frontend/area_funcionario.php");
            } elseif ($usuario["tipo"] == "admin") {
                header("Location: ../frontend/area_admin.php");
            }
            exit();

        } else {
            header("Location: ../frontend/login.html?erro=1");
            exit();
        }

    } else {
        header("Location: ../frontend/login.html?erro=1");
        exit();
    }

} else {
    header("Location: ../frontend/login.html");
    exit();
}

?>
