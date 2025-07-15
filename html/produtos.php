<?php
    // Conexão com o banco de dados
    // Inicia a sessão do usuário
    session_start();
    require 'conexao.php';
    
    // Inserção de produto
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = // Dados enviados via formulário
        $_POST['nome'];
        $descricao = // Dados enviados via formulário
        $_POST['descricao'];
        $preco = // Dados enviados via formulário
        $_POST['preco'];
        
        $imagem = '';
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../imgs/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $nomeArquivo = uniqid() . '.' . $extensao;
            $caminhoImagem = $uploadDir . $nomeArquivo;
            
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoImagem)) {
                $imagem = $caminhoImagem;
            } else {
                echo "<script>alert('Erro ao enviar a imagem!');</script>";
            }
        }
        
        if ($imagem) {
            $stmt = $pdo->prepare("INSERT INTO produtos (nome, descricao, preco, imagem) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $descricao, $preco, $imagem]);
        }
    }
    
    // Buscar produtos do banco
    $stmt = $pdo->query("SELECT * FROM produtos ORDER BY id DESC");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Íris &ssence - Beauty Clinic</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link rel="stylesheet" href="../css/style.css" />
<script>
function validarFormulario() {
    const nome = document.forms["formProduto"]["nome"].value.trim();
    const descricao = document.forms["formProduto"]["descricao"].value.trim();
    const preco = document.forms["formProduto"]["preco"].value;
    const imagem = document.forms["formProduto"]["imagem"].files[0];
    
    if (nome === "" || descricao === "" || preco === "") {
        alert("Preencha todos os campos.");
        return false;
    }
    
    if (isNaN(preco) || parseFloat(preco) <= 0) {
        alert("Digite um preço válido.");
        return false;
    }
    
    if (!imagem) {
        alert("Selecione uma imagem.");
        return false;
    }
    
    const extensaoValida = /\.(jpg|jpeg|png|gif)$/i;
    if (!extensaoValida.test(imagem.name)) {
        alert("A imagem deve ser .jpg, .jpeg, .png ou .gif");
        return false;
    }
    
    return true;
}
</script>
</head>
<body>

<!-- HEADER (menu de navegação) -->
<header>
    <nav>
        <ul>
            <a href="../html/index.html">
                <img src="../imgs/logo.jpg" class="logo" alt="Logo">
            </a>
            <li><a href="../html/index.html">HOME</a></li>
            <li>
                <a href="#">PROCEDIMENTOS FACIAIS</a>
                <div class="submenu">
                    <a href="../html/limpezapele.html">Limpeza de Pele</a>
                    <a href="../html/labial.html">Preenchimento labial</a>
                    <a href="../html/microagulhamento.html">Microagulhamento</a>
                    <a href="../html/botoxfacial.html">Botox</a>
                    <a href="../html/acne.html">Tratamento para Acne</a>
                    <a href="../html/rinomodelacao.html">Rinomodelação</a>
                </div>
            </li>
            <li>
                <a href="#">PROCEDIMENTOS CORPORAIS</a>
                <div class="submenu">
                    <a href="../html/massagemmodeladora.html">Massagem Modeladora</a>
                    <a href="../html/drenagemlinfatica.html">Drenagem Linfática</a>
                    <a href="../html/depilacaolaser.html">Depilação a Laser</a>
                    <a href="../html/depilacaocera.html">Depilação de cera</a>
                    <a href="../html/massagemrelaxante.html">Massagem Relaxante</a>
                </div>
            </li>
            <li><a href="../html/produtos.php">PRODUTOS</a></li>|
            <li><a href="../html/login.php">LOGIN</a></li>|
            <li><a href="../html/cadastro.html">CADASTRO</a></li>|

        </ul>
    </nav>
</header>

<!-- TÍTULO -->
<br><br><br>
<h2 class="tcentral">A venda de produtos é feita apenas de forma presencial!</h2>
<br><br>

<!-- FORMULÁRIO DE CADASTRO -->
<?php if (isset($_SESSION['perfil']) && $_SESSION['perfil'] == 1): ?>
<div class="container">
<h3 style="color: black;">Cadastrar novo produto</h3>
<form name="formProduto" method="POST" enctype="multipart/form-data" onsubmit="return validarFormulario()">
<div class="mb-3">
<label class="form-label">Nome do Produto</label>
<input type="text" name="nome" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">Descrição</label>
<textarea name="descricao" class="form-control" required></textarea>
</div>
<div class="mb-3">
<label class="form-label">Preço (ex: 120.00)</label>
<input type="number" step="0.01" name="preco" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">Imagem do Produto</label>
<input type="file" name="imagem" class="form-control" accept="image/*" required>
</div>
<button type="submit" class="btn btn-primary">Cadastrar</button>
</form>
</div>
<?php endif; ?>


<br><br>

<!-- LISTAGEM DE PRODUTOS -->
<div class="product-container">
<?php foreach ($produtos as $produto): ?>
<div class="product-box">
<img src="<?= htmlspecialchars($produto['imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>" class="product-image">
<h2 class="product-title"><?= htmlspecialchars($produto['nome']) ?></h2>
<p class="product-description"><?= htmlspecialchars($produto['descricao']) ?></p>
<p class="product-price">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></p>

<?php if (isset($_SESSION['perfil']) && $_SESSION['perfil'] == 1): ?>
<div class="btn-group">
<a href="editar_produto.php?id=<?= $produto['id'] ?>" class="btn-editar">
<i class="fas fa-pen"></i>
</a>
<a href="excluir_produto.php?id=<?= $produto['id'] ?>" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir este produto?');">
<i class="fas fa-trash"></i>
</a>
</div>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<br>
<button type="button" class="voltar-button" onclick="window.location.href='principal.php'">Voltar</button>
<br><br><br>

<!-- FOOTER -->
<footer>
<p>&copy; 2025 Iris Essence - Beauty Clinic. Todos os direitos reservados.</p>
<div class="footer-contato">
<p><i class="fab fa-whatsapp"></i> <a href="https://wa.me/5547997141208" target="_blank">+55 99714-1208</a></p>
<p><i class="fab fa-instagram"></i> <a href="https://www.instagram.com/irisessence" target="_blank">@irisessence</a></p>
<p><i class="fas fa-envelope"></i> <a href="mailto:contato@irisessence.com">suporteiris@irisessence.com</a></p>
</div>
</footer>

</body>
</html>
