USE autorizador_db;
CREATE TABLE IF NOT EXISTS users_admin (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    nome_user VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS arquivo (
id_arquivo INT AUTO_INCREMENT PRIMARY KEY,
nome_solicitante VARCHAR(255),
setor_requisitante VARCHAR(255),
motivo_requisicao TEXT,
descricao TEXT,
tipo_papel VARCHAR(255),
quant_copias INT,
prazo_estimado VARCHAR(255),
arquivo_impressao LONGBLOB,
nome_arquivo VARCHAR(255),
data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP

    
);