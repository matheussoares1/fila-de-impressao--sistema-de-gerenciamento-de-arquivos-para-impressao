<?php
require_once "config.php";

if (!isset($_GET['id'])) {
    die("ID inválido");
}

$stmt = $pdo->prepare("SELECT arquivo_impressao FROM arquivo WHERE id_arquivo = ?");
$stmt->execute([$_GET['id']]);

$dados = $stmt->fetch();

if (!$dados) {
    die("Arquivo não encontrado");
}

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=arquivo_enviado");
echo $dados['arquivo_impressao'];
exit;
?>
