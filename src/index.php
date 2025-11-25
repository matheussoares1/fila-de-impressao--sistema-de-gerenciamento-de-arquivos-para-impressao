<?php
session_start();

// Puxa configuração de conexão - cria $pdo
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = $_POST['nome_solicitante'];
    $setor = $_POST['setor_requisitante'];
    $motivo = $_POST['motivo_requisicao'];
    $descricao = $_POST['descricao'];
    $tipo_papel = $_POST['tipo_papel'];
    $quantidade = $_POST['quant_copias'];
    $prazo = $_POST['prazo_estimado'];

    // Receber arquivo e o nome original
    if (!empty($_FILES['arquivo_impressao']['tmp_name'])) {
        $arquivo = file_get_contents($_FILES['arquivo_impressao']['tmp_name']);
        $nome_original = $_FILES['arquivo_impressao']['name'];
    } else {
        $arquivo = null;
        $nome_original = null;
    }

    // SQL atualizado COM nome_arquivo
    $sql = "
        INSERT INTO arquivo 
            (nome_solicitante, setor_requisitante, motivo_requisicao, descricao, tipo_papel, quant_copias, prazo_estimado, arquivo_impressao, nome_arquivo)
        VALUES 
            (:nome, :setor, :motivo, :descricao, :tipo, :quant, :prazo, :arquivo, :nome_arquivo)
    ";

    $stmt = $pdo->prepare($sql);

    // Bind correto no PDO
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':setor', $setor);
    $stmt->bindParam(':motivo', $motivo);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':tipo', $tipo_papel);
    $stmt->bindParam(':quant', $quantidade);
    $stmt->bindParam(':prazo', $prazo);
    $stmt->bindParam(':arquivo', $arquivo, PDO::PARAM_LOB);
    $stmt->bindParam(':nome_arquivo', $nome_original);

    if ($stmt->execute()) {
        echo "<script>alert('Solicitação enviada com sucesso!'); window.location='index.php';</script>";
        exit;
    } else {
        echo "Erro ao enviar solicitação.";
    }
}
?>





<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Impressão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles_form.css">
</head>

<body class="bg-light">

    <div class="container py-5">

        <div class="text-center mb-4">
            <i class="fas fa-print text-primary mb-3" style="font-size: 2.5rem;"></i>
            <h2 class="fw-bold">Solicitação de Impressão</h2>
            <p class="text-muted small">Preencha o formulário abaixo para solicitar uma impressão</p>
        </div>

        <div class="card p-4 p-md-5 shadow-sm mx-auto" style="max-width: 600px;">

            <form action="" method="POST" enctype="multipart/form-data">

                <h5 class="fw-bold">Dados da Solicitação</h5>
                <p class="small text-muted mb-4">Todos os campos são obrigatórios</p>

                <h6 class="fw-bold text-muted mb-3">Informações do Solicitante</h6>

                <div class="mb-3">
                    <label class="form-label">Nome Completo</label>
                    <input type="text" class="form-control" name="nome_solicitante" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Setor</label>
                    <input type="text" class="form-control" name="setor_requisitante" required>
                </div>

                <hr class="mb-4">

                <h6 class="fw-bold text-muted mb-3">Detalhes da Impressão</h6>

                <div class="mb-3">
                    <label class="form-label">Motivo</label>
                    <input type="text" class="form-control" name="motivo_requisicao" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Descrição</label>
                    <textarea class="form-control" name="descricao" rows="3" required></textarea>
                </div>

                <hr class="mb-4">

                <h6 class="fw-bold text-muted mb-3">Especificações</h6>

                <div class="mb-3">
                    <label class="form-label">Tipo de Papel</label>
                    <select class="form-select" name="tipo_papel" required>
                        <option value="" disabled selected>Selecione o tipo de papel</option>
                        <option value="a4_comum">A4 Comum</option>
                        <option value="a4_couche">A4 Couchê (Fotográfico)</option>
                        <option value="a4_120g">A4 120g (40kg Fosco)</option>
                    </select>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Quantidade de Cópias</label>
                        <input type="number" class="form-control" name="quant_copias" value="1" min="1" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Prazo Estimado</label>
                        <input type="text" class="form-control" name="prazo_estimado" required>
                    </div>
                </div>

                <hr class="mb-4">

                <h6 class="fw-bold text-muted mb-3">Arquivo para Impressão</h6>

                <div class="mb-4">
                    <label class="form-label">Upload do Arquivo</label>
                    <input type="file" class="form-control" name="arquivo_impressao" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Enviar Solicitação</button>

            </form>
        </div>
    </div>

</body>

</html>