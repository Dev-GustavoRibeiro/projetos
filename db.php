<?php
// Configuração de conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "root";
$database = "catalogo_eitamainha";
$port = 5140;

// Criando a conexão
$conn = new mysqli($servername, $username, $password, $database, $port);

// Verificando a conexão
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Caminho para o arquivo db.sql
$db_sql_file = __DIR__ . '/db.sql'; // Ajuste o caminho conforme necessário

if (file_exists($db_sql_file)) {
    // Lê o conteúdo do arquivo SQL
    $db_sql = file_get_contents($db_sql_file);
    if ($db_sql === false) {
        die("Erro ao abrir o arquivo db.sql.");
    }

    // Executa o conteúdo do arquivo SQL
    $queries = explode(';', $db_sql); // Divide as consultas SQL por ponto e vírgula

    foreach ($queries as $query) {
        $query = trim($query); // Remove espaços em branco ao redor
        if (!empty($query)) {
            if ($conn->query($query) === false) {
                die("Erro ao executar a consulta SQL: " . $conn->error . "\nConsulta: " . $query);
            }
        }
    }

    // Verifica se o índice já existe
    $check_index_query = "
        SELECT COUNT(1) AS index_exists
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = '$database'
          AND TABLE_NAME = 'produtos'
          AND INDEX_NAME = 'idx_produtos_nome';
    ";
    $result = $conn->query($check_index_query);
    if ($result) {
        $row = $result->fetch_assoc();
        if ($row['index_exists'] == 0) {
            // Cria o índice se ele não existir
            $create_index_query = "CREATE INDEX idx_produtos_nome ON produtos (nome)";
            if ($conn->query($create_index_query) === false) {
                die("Erro ao criar o índice: " . $conn->error);
            }
        }
    } else {
        die("Erro ao verificar o índice: " . $conn->error);
    }
} else {
    die("Arquivo db.sql não encontrado no caminho especificado: " . $db_sql_file);
}
?>
