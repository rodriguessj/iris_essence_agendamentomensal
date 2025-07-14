<?php
session_start();
require_once 'conexao.php';

$mensagem = "";

// Verifica se a requisição é POST (formulário enviado)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];
    $email = $_POST['email'];
    $data_nascimento = $_POST['data_nascimento'];
    $genero = $_POST['genero'];
    $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT); // senha criptografada
    $senha_pura = $_POST['senha']; // senha em texto puro para a tabela cliente

    try {
        // BUSCA
        $sql_perfil = "SELECT id_perfil FROM perfil WHERE nome_perfil = :nome_perfil LIMIT 1";
        $stmt_perfil = $pdo->prepare($sql_perfil);
        $stmt_perfil->execute([':nome_perfil' => 'cliente']);
        $id_perfil = $stmt_perfil->fetchColumn();

        if (!$id_perfil) {
            die("Erro: perfil 'cliente' não encontrado.");
        }

        $pdo->beginTransaction();

        // INSERE NA TABELA CLIENTE
        $sql_cliente = "INSERT INTO cliente (nome, telefone, endereco, email, data_nascimento, genero, senha, id_perfil)
                        VALUES (:nome, :telefone, :endereco, :email, :data_nascimento, :genero, :senha, :id_perfil)";
        $stmt_cliente = $pdo->prepare($sql_cliente);
        $stmt_cliente->execute([
            ':nome' => $nome,
            ':telefone' => $telefone,
            ':endereco' => $endereco,
            ':email' => $email,
            ':data_nascimento' => $data_nascimento,
            ':genero' => $genero,
            ':senha' => $senha_pura,
            ':id_perfil' => $id_perfil
        ]);

        // INSERE NA TABELA USUARIO
        $sql_usuario = "INSERT INTO usuario (nome, senha, email, id_perfil)
                        VALUES (:nome, :senha, :email, :id_perfil)";
        $stmt_usuario = $pdo->prepare($sql_usuario);
        $stmt_usuario->execute([
            ':nome' => $nome,
            ':senha' => $senha_hash,
            ':email' => $email,
            ':id_perfil' => $id_perfil
        ]);

        $pdo->commit();
        $mensagem = "Cliente cadastrado com sucesso!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $mensagem = "Erro ao cadastrar cliente: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Íris Essence - Beauty Clinic</title>
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
            <li><a href="../html/produtos.php">PRODUTOS</a></li>|
            <li><a href="../html/login.php">LOGIN</a></li>|
            <li><a href="../html/cadastro_cliente.php">CADASTRO</a></li>|
        </ul>
    </nav>
</header>

<br>

<div class="formulario">
    <fieldset>
      <form action="cadastro_cliente.php" method="POST">
        <legend>Cadastrar cliente</legend>

        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" required>

        <label for="senha">Senha:</label>
        <input type="password" id="senha" name="senha" required>

        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone" required>

        <label for="endereco">Endereço:</label>
        <input type="text" id="endereco" name="endereco" required>

        <label for="data_nascimento">Data de Nascimento:</label>
        <input type="date" id="data_nascimento" name="data_nascimento" required>

        <label for="genero">Gênero:</label>
        <select id="genero" name="genero" required>
          <option value="M">Homem</option>
          <option value="F">Mulher</option>
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
  <?php if ($mensagem === "Cliente cadastrado com sucesso!"): ?>
    document.forms[0].reset();
  <?php endif; ?>
</script>
<?php endif; ?>

<script>
    // Função para bloquear caracteres inválidos no nome (somente letras e espaços)
    document.getElementById('nome').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/[^a-zA-Z\s]/g, ''); // Bloqueia números e caracteres especiais
    });

    // Máscara para o campo de telefone
    document.getElementById('telefone').addEventListener('input', function(e) {
        let telefone = e.target.value;
        telefone = telefone.replace(/\D/g, ''); // Remove tudo que não é número

        // Aplica a máscara no formato (XX) XXXXX-XXXX
        if (telefone.length <= 2) {
            e.target.value = '(' + telefone;
        } else if (telefone.length <= 6) {
            e.target.value = '(' + telefone.substring(0, 2) + ') ' + telefone.substring(2);
        } else {
            e.target.value = '(' + telefone.substring(0, 2) + ') ' + telefone.substring(2, 7) + '-' + telefone.substring(7, 11);
        }
    });

    // Verifica a senha no momento de digitação
    document.getElementById('senha').addEventListener('input', function(e) {
        let senha = e.target.value;
        if (senha.length < 6) {
            // Se a senha for menor que 6 caracteres, impede a digitação de mais caracteres
            e.target.setCustomValidity("A senha deve ter pelo menos 6 caracteres!");
        } else {
            e.target.setCustomValidity(""); // Limpa a validação personalizada
        }
    });
</script>

</body>
</html>
