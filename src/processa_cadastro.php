<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha_limpa = $_POST['senha'] ?? null;

    if (empty($nome) || empty($senha_limpa)) {
        die("Erro: Nome e Senha são campos obrigatórios.");
    }

    $senha_hash = password_hash($senha_limpa, PASSWORD_DEFAULT);

    if (!$senha_hash) {
        die("Erro ao gerar hash da senha.");
    }

    $sql = "INSERT INTO users_admin (nome_user, password) VALUES (?, ?)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $senha_hash]);

        echo "Usuário <b>{$nome}</b> cadastrado com sucesso! ✅";

        // Redirecionamento opcional
        // header("Location: login.php");
        // exit;

    } catch (PDOException $e) {
        echo "Erro ao cadastrar usuário: " . $e->getMessage();
    }
}
?>
