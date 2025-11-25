<?php
require_once "config.php";

session_start();

if (!isset($_SESSION['autorizado']) || $_SESSION['autorizado'] !== true) {
    header("Location: login.php");
    exit;
}

// ======================================================
// FUNÇÃO: Buscar registro por ID
// ======================================================
function getArquivo($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM arquivo WHERE id_arquivo = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$action = $_GET['action'] ?? 'painel';

// diretório temporário
$tempDirRel = 'temp';
$tempDir = __DIR__ . DIRECTORY_SEPARATOR . $tempDirRel;
if (!is_dir($tempDir)) {
    @mkdir($tempDir, 0755, true);
}

// ======================================================
// AÇÃO: Atualizar status
// ======================================================
if ($action === 'update_status' && isset($_POST['id_arquivo'])) {
    $stmt = $pdo->prepare("UPDATE arquivo SET status = ? WHERE id_arquivo = ?");
    $stmt->execute([$_POST['status'], $_POST['id_arquivo']]);
    header("Location: painel.php");
    exit;
}

// ======================================================
// AÇÃO: Deletar
// ======================================================
if ($action === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM arquivo WHERE id_arquivo = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: painel.php");
    exit;
}

// ======================================================
// AÇÃO: Imprimir (somente PDF)
// ======================================================
if ($action === 'print' && isset($_GET['id'])) {
    $s = getArquivo($pdo, $_GET['id']);
    if (!$s) { echo "Solicitação não encontrada"; exit; }

    $nomeArquivo = $s['nome_arquivo'] ?? '';
    $ext = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));

    $conteudo = $s['arquivo_impressao'];

    // detecta mime por buffer
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->buffer($conteudo) ?: 'application/octet-stream';

    if ($mime !== 'application/pdf' && $ext !== 'pdf') {
        echo "<h3 style='font-family:Arial;margin:20px;color:red'>
                Este arquivo não é PDF e não pode ser impresso automaticamente.
              </h3>";
        exit;
    }

    $tempName = 'impressao_' . $s['id_arquivo'] . '_' . uniqid() . '.pdf';
    $tempPath = $tempDir . DIRECTORY_SEPARATOR . $tempName;
    file_put_contents($tempPath, $conteudo);

    $tempUrl = $tempDirRel . '/' . rawurlencode($tempName);
    ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Imprimir PDF</title>
<style>html,body{margin:0;height:100%;}</style>
</head>
<body>
<iframe id="f" src="<?= $tempUrl ?>" style="width:100%;height:100vh;border:none;"></iframe>

<script>
const f = document.getElementById("f");
f.onload = () => {
    try {
        f.contentWindow.print();
    } catch (e) {
        window.open("<?= $tempUrl ?>", "_blank");
    }
    setTimeout(() => fetch("remove_temp.php?file=<?= rawurlencode($tempName) ?>"), 3000);
};
</script>

</body>
</html>
<?php
exit;
}

// ======================================================
// LISTA GERAL
// ======================================================
$stmt = $pdo->query("SELECT * FROM arquivo ORDER BY id_arquivo DESC");
$arquivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// contadores
$total = count($arquivos);
$pend = $and = $conc = 0;

foreach ($arquivos as $s) {
    if ($s['status'] === 'Pendente') $pend++;
    if ($s['status'] === 'Em andamento') $and++;
    if ($s['status'] === 'Concluído') $conc++;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Painel do Autorizador</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<!-- NAV -->
<nav class="navbar navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid mx-3">
        <span class="navbar-brand mb-0 h1">
            <i class="fas fa-file me-2 text-primary"></i> Painel do Autorizador
        </span>
        <a href="logout.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-right-from-bracket"></i> Sair
        </a>
    </div>
</nav>

<div class="container mt-4">

<!-- CONTADORES -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <small class="text-muted">Total</small>
            <div class="fs-3 fw-bold"><?= $total ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <small class="text-muted">Pendente</small>
            <div class="fs-3"><?= $pend ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <small class="text-muted">Em andamento</small>
            <div class="fs-3 text-primary"><?= $and ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm">
            <small class="text-muted">Concluído</small>
            <div class="fs-3 text-success"><?= $conc ?></div>
        </div>
    </div>
</div>

<!-- TABELA -->
<div class="card p-4 shadow-sm">
    <h5 class="fw-bold mb-3">Solicitações</h5>

    <?php if ($total === 0): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-file fs-1 mb-3"></i>
            <p>Nenhuma solicitação cadastrada</p>
        </div>
    <?php else: ?>

    <table class="table table-hover">
        <thead>
            <tr>
                <th>Solicitante</th>
                <th>Setor</th>
                <th>Papel</th>
                <th>Cópias</th>
                <th>Status</th>
                <th class="text-center">Ações</th>
            </tr>
        </thead>
        <tbody>

        <?php foreach ($arquivos as $s): ?>
            <?php 
                $ext = strtolower(pathinfo($s["nome_arquivo"], PATHINFO_EXTENSION));
            ?>

            <tr>
                <td><?= htmlspecialchars($s['nome_solicitante']) ?></td>
                <td><?= htmlspecialchars($s['setor_requisitante']) ?></td>
                <td><?= htmlspecialchars($s['tipo_papel']) ?></td>
                <td><?= (int)$s['quant_copias'] ?></td>
                <td><span class="badge bg-secondary"><?= $s['status'] ?></span></td>

                <td class="text-center">

                    <!-- Editar status -->
                    <button class="btn btn-sm btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#editModal<?= $s['id_arquivo'] ?>">
                        <i class="fas fa-pen"></i>
                    </button>

                    <!-- Visualizar -->
                    <button class="btn btn-sm btn-info text-white"
                        data-bs-toggle="modal"
                        data-bs-target="#viewModal<?= $s['id_arquivo'] ?>">
                        <i class="fas fa-eye"></i>
                    </button>

                    <!-- Imprimir PDF -->
                    <?php if ($ext === "pdf"): ?>
                        <a class="btn btn-sm btn-success"
                           href="painel.php?action=print&id=<?= $s['id_arquivo'] ?>" target="_blank">
                            <i class="fas fa-print"></i>
                        </a>
                    <?php endif; ?>

                    <!-- Deletar -->
                    <a class="btn btn-sm btn-danger"
                        onclick="return confirm('Excluir esta solicitação?')"
                        href="painel.php?action=delete&id=<?= $s['id_arquivo'] ?>">
                        <i class="fas fa-trash"></i>
                    </a>

                </td>
            </tr>

        <?php endforeach; ?>

        </tbody>
    </table>
    <?php endif; ?>
</div>
</div>

<!-- ======================================================
     MODAIS (FORA DA TABELA) — AGORA FUNCIONAM CORRETAMENTE
======================================================= -->

<?php foreach ($arquivos as $s): ?>
<?php $ext = strtolower(pathinfo($s["nome_arquivo"], PATHINFO_EXTENSION)); ?>

<!-- MODAL EDITAR STATUS -->
<div class="modal fade" id="editModal<?= $s['id_arquivo'] ?>">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="painel.php?action=update_status">
            <div class="modal-header">
                <h5 class="modal-title">Editar Status</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="id_arquivo" value="<?= $s['id_arquivo'] ?>">

                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option <?= $s['status']=='Pendente'?'selected':'' ?>>Pendente</option>
                    <option <?= $s['status']=='Em andamento'?'selected':'' ?>>Em andamento</option>
                    <option <?= $s['status']=='Concluído'?'selected':'' ?>>Concluído</option>
                </select>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL VISUALIZAR -->
<div class="modal fade" id="viewModal<?= $s['id_arquivo'] ?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Detalhes da Solicitação</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p><b>Solicitante:</b> <?= htmlspecialchars($s['nome_solicitante']) ?></p>
                <p><b>Setor:</b> <?= htmlspecialchars($s['setor_requisitante']) ?></p>
                <p><b>Motivo:</b> <?= htmlspecialchars($s['motivo_requisicao']) ?></p>
                <p><b>Descrição:</b> <?= nl2br(htmlspecialchars($s['descricao'])) ?></p>
                <p><b>Papel:</b> <?= htmlspecialchars($s['tipo_papel']) ?></p>
                <p><b>Cópias:</b> <?= (int)$s['quant_copias'] ?></p>
                <p><b>Prazo:</b> <?= htmlspecialchars($s['prazo_estimado']) ?></p>

                <hr>

                <h6>Arquivo enviado:</h6>

                <?php if ($ext === 'pdf'): ?>

                    <?php  
                        $viewName = 'view_' . $s['id_arquivo'] . '_' . uniqid() . '.pdf';
                        $viewPath = $tempDir . "/" . $viewName;
                        file_put_contents($viewPath, $s['arquivo_impressao']);
                        $viewUrl = $tempDirRel . '/' . $viewName;
                    ?>

                    <embed src="<?= $viewUrl ?>" type="application/pdf" width="100%" height="400px">

                <?php else: ?>

                    <p class="text-muted">
                        Este arquivo não pode ser visualizado diretamente.<br>
                        Apenas PDFs possuem pré-visualização no sistema.
                    </p>

                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
