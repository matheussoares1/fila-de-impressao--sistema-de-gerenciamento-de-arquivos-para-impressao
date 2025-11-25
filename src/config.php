<?php
// Carrega o arquivo .env
require_once __DIR__ . '/env.php';
loadEnv(__DIR__ . '/.env');

// =============================================
//  CONFIGURAÇÃO GLOBAL DO BANCO DE DADOS
// =============================================

$DB_HOST    = getenv("DB_HOST");
$DB_NAME    = getenv("DB_NAME");
$DB_USER    = getenv("DB_USER");
$DB_PASS    = getenv("DB_PASS");
$DB_CHARSET = getenv("DB_CHARSET");

// =============================================
//  CRIAÇÃO DA CONEXÃO PDO
// =============================================

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
