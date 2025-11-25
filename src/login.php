<?php
session_start();
require_once "config.php";

$erro = "";

// Se o usuário já estiver logado, manda para o painel
if (isset($_SESSION['autorizado']) && $_SESSION['autorizado'] === true) {
    header("Location: painel.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario = trim($_POST['usuario']);
    $senha = trim($_POST['senha']);

    // Busca usuário no BD
    $sql = "SELECT * FROM users_admin WHERE nome_user = :user LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":user", $usuario);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        // Verifica senha
        if (password_verify($senha, $user['password'])) {

            // Login OK
            $_SESSION['autorizado'] = true;
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_name'] = $user['nome_user'];

            header("Location: painel.php");
            exit;

        } else {
            $erro = "Senha incorreta!";
        }

    } else {
        $erro = "Usuário não encontrado!";
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso do Autorizador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="styles_login.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">

    <div class="card p-4 p-md-5 shadow-sm" style="max-width: 400px; width: 90%;">

        <div class="text-center mb-4">
            <div class="rounded-circle bg-primary-subtle d-inline-flex align-items-center justify-content-center mb-3" 
                 style="width: 70px; height: 70px;">
                <i class="fas fa-lock fs-3 text-primary"></i>
            </div>

            <h4 class="fw-bold mb-1">Acesso do Autorizador</h4>
            <p class="text-muted small">Área restrita para gerenciamento de solicitações</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger text-center py-2"><?= $erro ?></div>
        <?php endif; ?>

        <form action="" method="POST">

            <div class="mb-3">
                <label class="form-label fw-bold">Usuário</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-user text-muted"></i>
                    </span>
                    <input type="text" name="usuario" class="form-control border-start-0 ps-0" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Senha</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-lock text-muted"></i>
                    </span>
                    <input type="password" name="senha" class="form-control border-start-0 ps-0" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 p-2 fw-bold mb-3">
                Entrar
            </button>

            <div class="text-center small text-muted">
                Credenciais padrão: superuser / admin123@
            </div>

        </form>
    </div>

</body>
</html>
