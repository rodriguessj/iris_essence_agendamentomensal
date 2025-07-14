<?php
    // Inicia a sessão do usuário
    session_start();
    require_once 'conexao.php';
    
    //VERIFICA SE USUARIO TEM PERMISSÃO DE ADM 
    if($_SESSION['perfil'] !=1){
        echo "<script>alert('Acesso negado!');wiondow.location.href='principal.php';</script>";
        exit();
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nome = // Dados enviados via formulário
        $_POST['nome'];
        $descricao = // Dados enviados via formulário
        $_POST['descricao'];
        $preco = str_replace(',', '.', // Dados enviados via formulário
        $_POST['preco']);
        
        // Verifica se foi enviado um arquivo de imagem
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $nomeImagem = basename($_FILES['imagem']['name']);
            $caminho = '../uploads/' . $nomeImagem;
            
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
                $sql = "INSERT INTO produtos(nome, descricao, preco, imagem) VALUES(:nome, :descricao, :preco, :imagem)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':descricao', $descricao);
                $stmt->bindParam(':preco', $preco);
                $stmt->bindParam(':imagem', $nomeImagem);
                
                if ($stmt->execute()) {
                    echo "<script>alert('Produto cadastrado com sucesso!');</script>";
                } else {
                    echo "<script>alert('Erro ao cadastrar Produto!');</script>";
                }
            } else {
                echo "<script>alert('Erro ao fazer upload da imagem.');</script>";
            }
        } else {
            echo "<script>alert('Imagem não enviada corretamente.');</script>";
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Íris Essence - Cadastrar Produto</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
<script src="script.js"></script>
<link rel="icon" href="../imgs/logo.jpg" type="image/x-icon">
</head>
<body class="cadastro-fundo">
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
            <li><a href="../html/produtos.html">PRODUTOS</a></li>|
            <li><a href="../html/login.php">LOGIN</a></li>|
            <li><a href="../html/cadastro.html">CADASTRO</a></li>|

            <div class="logout">
                <form action = "logout.php" method= "POST">
                <button type="submit">Logout</button>
            </div>
        </ul>
    </nav>
</header>
<br>

<div class="formulario">
<fieldset>
<form action="cadastro_produto.php" method="POST" enctype="multipart/form-data">
<legend>Cadastrar Produto</legend>
<label for="nome">Nome: </label>
<input type="text" id="nome" name="nome" required>

<label for="descricao">Descrição: </label>
<textarea id="descricao" name="descricao" required></textarea>

<label for="preco">Preço: </label>
<input type="text" id="preco" name="preco" required>

<label for="imagem">Imagem: </label>
<input type="file" id="imagem" name="imagem" accept="image/*" required>

<div class="botoes">
<button class="botao_cadastro" type="submit">Salvar</button>
<button class="botao_limpeza" type="reset">Cancelar</button>
</div>

<br>
<button type="button" class="voltar-button" onclick="window.location.href='principal.php'">Voltar</button>
</fieldset>
</div>

<br><br><br><br>
<footer class="l-footer">&copy; 2025 Iris Essence - Beauty Clinic. Todos os direitos reservados.</footer>
</body>
</html>
