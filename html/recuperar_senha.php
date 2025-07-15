<?php
    // Conexão com o banco de dados
    // Inicia a sessão do usuário
    session_start();
    require_once 'conexao.php';
    require_once 'funcoes_email.php'; //ARQUIVO COM AS FUNCOES QUE GERAM SENHA E SIMULAM O ENVIO
    
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $email = // Dados enviados via formulário
        $_POST['email'];
        
        //VERIFICA SE O EMAIL EXISTE NO BANCO
        $sql = "SELECT * FROM usuario WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($usuario){
            //GERA UMA SENHA TEMPORARIA ALEATÓRIA.
            $senha_temporaria = gerarSenhaTemporaria();
            $senha_hash = password_hash($senha_temporaria, PASSWORD_DEFAULT);
            
            //ATUALIZA A SENHA DO USUÁRIO NO BANCO
            $sql = "UPDATE usuario SET senha = :senha, senha_temporaria = TRUE WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':senha', $senha_hash);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            //SIMULA O ENVIO DO E-MAIL(GRAVA EM TXT)
            simularEnvioEmail($email, $senha_temporaria);
            
            echo "<script>alert('Uma senha temporaria foi gerada e enviada(simulação).Verifique o arquivo emails_simulados.txt');window.location.href='login.php'</script>";
        }else{
            echo "<script>alert('E-mail não encontrado!');</script>";
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

<li><a href="../html/produtos.php">PRODUTOS</a></li>
|<li><a href="../html/cadastro_agendamento.php">AGENDAR</a></li>|
<li><a href="../html/login.php">LOGIN</a></li>|
<li><a href="../html/cadastro_cliente.php">CADASTRO</a></li>|
</ul>
</nav>
</header>
<br>
<div class="formulario">
<form action="recuperar_senha.php" method="POST">
<fieldset>
<legend>Recuparar Senha | Faça login novamente</legend>

<label for="nome"> Digite seu e-mail cadastrado:</label>
<input type="email" id="email" name="email" required>

<div class="botoes">
<button class="botao_cadastro" type="submit">Enviar Senha Temporaria</button>
</div>
</fieldset>
</form>
</div>
<br><br>
<footer class="l-footer">&copy; 2025 Iris Essence - Beauty Clinic. Todos os direitos reservados.</footer>
</body>
</html>