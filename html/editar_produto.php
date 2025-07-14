<?php
    require 'conexao.php';
    
    if (!isset(// Dados recebidos via URL
    $_GET['id'])) {
        header("Location: produtos.php");
        exit();
    }
    
    $id = // Dados recebidos via URL
    $_GET['id'];
    
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    $produto = $stmt->fetch();
    
    if (!$produto) {
        echo "Produto não encontrado.";
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = // Dados enviados via formulário
        $_POST['nome'];
        $descricao = // Dados enviados via formulário
        $_POST['descricao'];
        $preco = // Dados enviados via formulário
        $_POST['preco'];
        $novaImagem = $_FILES['nova_imagem'];
        
        $imagemFinal = $produto['imagem']; // imagem atual por padrão
        
        if ($novaImagem && $novaImagem['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../imgs/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extensao = pathinfo($novaImagem['name'], PATHINFO_EXTENSION);
            $nomeArquivo = uniqid() . '.' . $extensao;
            $caminhoImagem = $uploadDir . $nomeArquivo;
            
            if (move_uploaded_file($novaImagem['tmp_name'], $caminhoImagem)) {
                // Apaga imagem antiga
                if (file_exists($produto['imagem'])) {
                    unlink($produto['imagem']);
                }
                $imagemFinal = $caminhoImagem;
            } else {
                echo "<script>alert('Erro ao enviar nova imagem!');</script>";
            }
        }
        
        $stmt = $pdo->prepare("UPDATE produtos SET nome = ?, descricao = ?, preco = ?, imagem = ? WHERE id = ?");
        $stmt->execute([$nome, $descricao, $preco, $imagemFinal, $id]);
        
        header("Location: produtos.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Editar Produto</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
<nav>
<ul>
<a href="../html/index.html">
<img src="../imgs/logo.jpg" class="logo" alt="Logo">
</a>
<li><a href="../html/index.html">HOME</a></li>
<li><a href="produtos.php">PRODUTOS</a></li>
|<li><a href="../html/agendamento.html">AGENDAR</a></li>|
<li><a href="../html/login.php">LOGIN</a></li>|
<li><a href="../html/cadastro.html">CADASTRO</a></li>|
</ul>
</nav>
</header>

<br>

<div class="formulario">
<form method="POST" enctype="multipart/form-data">
<fieldset>
<legend>Editar Produto</legend>

<label for="nome">Nome:</label>
<input type="text" name="nome" id="nome" value="<?= htmlspecialchars($produto['nome']) ?>" required>

<label for="descricao">Descrição:</label>
<textarea name="descricao" id="descricao" required><?= htmlspecialchars($produto['descricao']) ?></textarea>

<label for="preco">Preço:</label>
<input type="number" step="0.01" name="preco" id="preco" value="<?= $produto['preco'] ?>" required>

<label>Imagem Atual:</label>
<img src="<?= htmlspecialchars($produto['imagem']) ?>" alt="Imagem atual" style="max-width: 100%; border-radius: 8px; margin-bottom: 10px;">

<label for="nova_imagem">Nova Imagem (opcional):</label>
<input type="file" name="nova_imagem" id="nova_imagem" accept="image/*">

<div class="botoes">
<button type="submit" class="botao_cadastro">Salvar alterações</button>
<a href="produtos.php" class="botao_limpeza">Cancelar</a>
</div>
</fieldset>
</form>
</div>

<br><br><br><br><br>
<footer class="l-footer">&copy; 2025 Iris Essence - Beauty Clinic. Todos os direitos reservados.</footer>
</body>
</html>
