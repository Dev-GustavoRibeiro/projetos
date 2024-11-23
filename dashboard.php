<?php
include '../config/db.php';


session_start();
if (!isset($_SESSION['employee_id'])) {
    header('Location: login.php'); 
    exit();
}


$edit_mode = false;
$edit_id = null;
$edit_nome = '';
$edit_preco = '';
$edit_descricao = '';
$edit_categoria_id = '';
$edit_imagem_url = '';


$message = '';
$message_type = '';



if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $produto = $result->fetch_assoc();
        $edit_nome = $produto['nome'];
        $edit_preco = $produto['preco'];
        $edit_descricao = $produto['descricao'];
        $edit_categoria_id = $produto['categoria_id'];
        $edit_imagem_url = $produto['imagem_url'];
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'];

    if ($acao === 'adicionar') {
        $nome = $_POST['nome'];
        $preco = $_POST['preco'];
        $descricao = $_POST['descricao'];
        $categoria_id = $_POST['categoria_id'];
        $imagem_url = null;

        if (!empty($_POST['imagem_url'])) {
            $imagem_url = filter_var($_POST['imagem_url'], FILTER_VALIDATE_URL);
        } elseif (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $imagem_nome = uniqid() . '_' . $_FILES['imagem']['name'];
            $imagem_caminho = '../uploads/' . $imagem_nome;
            move_uploaded_file($_FILES['imagem']['tmp_name'], $imagem_caminho);
            $imagem_url = $imagem_caminho;
        }

        $stmt = $conn->prepare("INSERT INTO produtos (nome, preco, imagem_url, descricao, categoria_id) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sdssi", $nome, $preco, $imagem_url, $descricao, $categoria_id);
            if ($stmt->execute()) {
                $message = 'Produto adicionado com sucesso!';
                $message_type = 'success';
            } else {
                $message = 'Erro ao adicionar produto: ' . $stmt->error;
                $message_type = 'danger';
            }
        }
    } elseif ($acao === 'editar') { 
        $id = $_POST['id'];
        $nome = $_POST['nome'];
        $preco = $_POST['preco'];
        $descricao = $_POST['descricao'];
        $categoria_id = $_POST['categoria_id'];
        $imagem_url = $_POST['imagem_url'];

        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $imagem_nome = uniqid() . '_' . $_FILES['imagem']['name'];
            $imagem_caminho = '../uploads/' . $imagem_nome;
            move_uploaded_file($_FILES['imagem']['tmp_name'], $imagem_caminho);
            $imagem_url = $imagem_caminho;
        }

        $stmt = $conn->prepare("UPDATE produtos SET nome = ?, preco = ?, imagem_url = ?, descricao = ?, categoria_id = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("sdssii", $nome, $preco, $imagem_url, $descricao, $categoria_id, $id);
            if ($stmt->execute()) {
                $message = 'Produto atualizado com sucesso!';
                $message_type = 'success';
            } else {
                $message = 'Erro ao atualizar produto: ' . $stmt->error;
                $message_type = 'danger';
            }
        }
    } elseif ($acao === 'excluir') { 
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = 'Produto excluído com sucesso!';
                $message_type = 'success';
            } else {
                $message = 'Erro ao excluir produto: ' . $stmt->error;
                $message_type = 'danger';
            }
        }
    } elseif ($acao === 'adicionar_funcionario') { 
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $nome_completo = $_POST['nome_completo'];
        $email = $_POST['email'];
        $cargo = $_POST['cargo'];
        $data_contratacao = $_POST['data_contratacao'];

        $stmt = $conn->prepare("INSERT INTO funcionarios (username, password, nome_completo, email, cargo, data_contratacao) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssss", $username, $password, $nome_completo, $email, $cargo, $data_contratacao);
            if ($stmt->execute()) {
                $message = 'Funcionário adicionado com sucesso!';
                $message_type = 'success';
            } else {
                $message = 'Erro ao adicionar funcionário: ' . $stmt->error;
                $message_type = 'danger';
            }
        }
    }
}


$produtos = $conn->query("SELECT p.*, c.nome AS categoria FROM produtos p JOIN categorias c ON p.categoria_id = c.id ORDER BY p.id DESC");
$categorias = $conn->query("SELECT * FROM categorias");
$funcionarios = $conn->query("SELECT * FROM funcionarios");
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gerenciar Funcionários e Produtos</title>
    <link rel="icon" href="../icons/icon-logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <header class="header d-flex justify-content-between align-items-center">

        <a href="../index.php">
            <img src="../images/logo.jpeg" alt="Logo da Empresa" style="height: 100px;">
        </a>
        <h1 class="text-center flex-grow-1">Dashboard</h1>
        <a href="login.php" class="btn btn-danger">Sair</a>
    </header>

    <div class="container my-4">

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>" role="alert">
                <?= $message ?>
            </div>
        <?php endif; ?>


        <h2 class="text-center">Gerenciar Funcionários</h2>
        <form method="POST" class="mb-4">
            <input type="hidden" name="acao" value="adicionar_funcionario">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="col-md-4">
                    <input type="password" name="password" class="form-control" placeholder="Senha" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="nome_completo" class="form-control" placeholder="Nome Completo" required>
                </div>
                <div class="col-md-4">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="cargo" class="form-control" placeholder="Cargo" required>
                </div>
                <div class="col-md-4">
                    <input type="date" name="data_contratacao" class="form-control" required>
                </div>
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-success">Adicionar Funcionário</button>
                </div>
            </div>
        </form>

        <h3 class="text-center">Lista de Funcionários</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Nome Completo</th>
                    <th>Email</th>
                    <th>Cargo</th>
                    <th>Data de Contratação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($funcionario = $funcionarios->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($funcionario['username']) ?></td>
                        <td><?= htmlspecialchars($funcionario['nome_completo']) ?></td>
                        <td><?= htmlspecialchars($funcionario['email']) ?></td>
                        <td><?= htmlspecialchars($funcionario['cargo']) ?></td>
                        <td><?= htmlspecialchars($funcionario['data_contratacao']) ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="acao" value="excluir_funcionario">
                                <input type="hidden" name="id" value="<?= $funcionario['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>


        <h2 class="text-center">Gerenciar Produtos</h2>
        <form method="POST" enctype="multipart/form-data" class="mb-4">
            <input type="hidden" name="acao" value="<?= $edit_mode ? 'editar' : 'adicionar' ?>">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="id" value="<?= $edit_id ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="nome" class="form-control" placeholder="Nome do Produto" value="<?= $edit_nome ?>" required>
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" name="preco" class="form-control" placeholder="Preço" value="<?= $edit_preco ?>" required>
                </div>
                <div class="col-md-4">
                    <select name="categoria_id" class="form-select" required>
                        <option value="" disabled>Selecione a Categoria</option>
                        <?php while ($categoria = $categorias->fetch_assoc()): ?>
                            <option value="<?= $categoria['id'] ?>" <?= $categoria['id'] == $edit_categoria_id ? 'selected' : '' ?>><?= $categoria['nome'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="file" name="imagem" class="form-control">
                </div>
                <div class="col-md-6">
                    <input type="url" name="imagem_url" class="form-control" placeholder="URL da Imagem" value="<?= $edit_imagem_url ?>">
                </div>
                <div class="col-md-12">
                    <textarea name="descricao" class="form-control" placeholder="Descrição do Produto" rows="3"><?= $edit_descricao ?></textarea>
                </div>
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-success"><?= $edit_mode ? 'Atualizar Produto' : 'Adicionar Produto' ?></button>
                    <?php if ($edit_mode): ?>
                        <a href="dashboard.php" class="btn btn-secondary">Cancelar Edição</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Imagem</th>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Categoria</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($produto = $produtos->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if (!empty($produto['imagem_url'])): ?>
                                <img src="<?= htmlspecialchars($produto['imagem_url']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>" width="80">
                            <?php else: ?>
                                <img src="../images/default-image.jpg" alt="Imagem padrão" width="80">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($produto['nome']) ?></td>
                        <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($produto['categoria']) ?></td>
                        <td>
                            <a href="?edit_id=<?= $produto['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= $produto['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
