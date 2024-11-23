-- Criação do banco de dados (apenas se não existir)
CREATE DATABASE IF NOT EXISTS catalogo_eitamainha;

-- Seleciona o banco de dados para uso
USE catalogo_eitamainha;

-- Criação da tabela para funcionários
CREATE TABLE IF NOT EXISTS funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nome_completo VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    data_contratacao DATE,
    cargo VARCHAR(50)
);

-- Criação da tabela para categorias
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE
);

-- Criação da tabela para produtos
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    preco FLOAT NOT NULL,
    imagem_url TEXT NOT NULL,
    descricao TEXT,
    categoria_id INT NOT NULL,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
);

-- Insere um usuário administrador somente se não existir
INSERT IGNORE INTO funcionarios (username, password, nome_completo, email, data_contratacao, cargo)
VALUES ('admin', 'admin', 'Administrador', 'admin@eitamainha.com', CURDATE(), 'Administrador');

-- População da tabela categorias com as categorias mencionadas
INSERT IGNORE INTO categorias (nome) VALUES 
('Bolos'),
('Doces'),
('Salgados'),
('Kit Festas'),
('Coffee Break'),
('Bebidas');
