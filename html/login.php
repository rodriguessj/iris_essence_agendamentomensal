<?php
    // Conexão com o banco de dados
    // Inicia a sessão do usuário
    session_start();
    require_once 'conexao.php';
    
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        
        $sql= 'SELECT * FROM usuario WHERE email = :email';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($usuario && password_verify($senha, $usuario['senha'])){
            //LOGIN BEM SUCEDIDO DEFINE VARIAVEIS DE SESSAO
            $_SESSION['usuario'] = $usuario['nome'];
            $_SESSION['perfil'] = $usuario['id_perfil'];
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            
            //VERIFICA SE A SENHA É TEMPORARIA
            if($usuario['senha_temporaria']){
                //REDIRECIONA PARA A TROCA DE SENHA
                header('Location: alterar_senha.php');
                exit();
            }else{
                //REDIRECIONA PARA A PÁGINA PRINCIPAL
                header('Location: principal.php');
                exit();
            }
        }else{
            //LOGIN INVALIDO
            echo"<script>alert('E-mail ou senha incorretos');window.location.href='login.php';</script>";
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Íris &ssence - Beauty Clinic</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
<!--  <script src="script.js"></script> -->
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

|<li><a href="../html/produtos.php">PRODUTOS</a></li>|
<li><a href="../html/login.php">LOGIN</a></li>|
<li><a href="../html/cadastro_cliente.php">CADASTRO</a></li>|
</ul>
</nav>
</header>
<br>
<div class="formulario">
<form action="login.php" method="POST">
<fieldset>
<legend>Login | Entrar na conta</legend>


<label for="nome">E-mail:</label>
<input type="email" id="email" name="email" required>

<label for="senha">Senha</label>
<input type="password" id="senha" name="senha" required>

<div class="botoes">
<button class="botao_cadastro" type="submit">Entrar</button>
<button class="botao_limpeza" type="reset">Limpar</button>
</div>
</fieldset>
</form>
<br>
<p><a href="recuperar_senha.php">Esqueci minha Senha</a></p>
<button type="button" class="voltar-button" onclick="window.history.back();">Voltar</button>
</div>
<br><br>
<footer class="l-footer">&copy; 2025 Iris Essence - Beauty Clinic. Todos os direitos reservados.</footer>
</body>
</html>
