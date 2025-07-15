<?php
session_start();
require_once 'conexao.php';

$mensagem = "";

    //VERIFICA SE USUARIO TEM PERMISSÃO DE ADM 
    if($_SESSION['perfil'] !=1){
        echo "<script>alert('Acesso negado!');wiondow.location.href='principal.php';</script>";
        exit();
    }

//INSERE NA TABELA
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $id_perfil = $_POST['id_perfil'];

    $sql = "INSERT INTO usuario (nome, email, senha, id_perfil) 
            VALUES (:nome, :email, :senha, :id_perfil)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha', $senha);
    $stmt->bindParam(':id_perfil', $id_perfil);

    if ($stmt->execute()) {
        $mensagem = "Usuário cadastrado com sucesso!";
    } else {
        $mensagem = "Erro ao cadastrar usuário!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Íris &ssence - Beauty Clinic</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../css/style.css">
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
        <form action="logout.php" method="POST">
          <button type="submit">Logout</button>
        </form>
      </div>
    </ul>
  </nav>
</header>

<br>

<div class="formulario">
  <fieldset>
    <form action="cadastro_usuario.php" method="POST">
      <legend>Cadastrar usuário</legend>

      <label for="nome">Nome:</label>
      <input type="text" id="nome" name="nome" required>

      <label for="email">E-mail:</label>
      <input type="email" id="email" name="email" required>

      <label for="senha">Senha:</label>
      <input type="password" id="senha" name="senha" required>

      <label for="id_perfil">Perfil:</label>
      <select id="id_perfil" name="id_perfil">
        <option value="1">Administrador</option>
        <option value="2">Recepcionista</option>
        <option value="3">Cliente</option>
      </select>

      <div class="botoes">
        <button class="botao_cadastro" type="submit">Salvar</button>
        <button class="botao_limpeza" type="reset">Cancelar</button>
      </div>

      <br>
      <button type="button" class="voltar-button" onclick="window.location.href='principal.php'">Voltar</button>
    </form>
  </fieldset>
</div>

<br><br>
<footer class="l-footer">&copy; 2025 Iris Essence - Beauty Clinic. Todos os direitos reservados.</footer>

<?php if (!empty($mensagem)): ?>
<script>
  alert("<?= $mensagem ?>");
  <?php if ($mensagem === "Usuário cadastrado com sucesso!"): ?>
    document.forms[0].reset();
  <?php endif; ?>
</script>
<?php endif; ?>

</body>
</html>
