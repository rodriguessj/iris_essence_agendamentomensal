<?php
// Conexão com o banco de dados
// Inicia a sessão do usuário
session_start();
require 'conexao.php';

    //VERIFICA SE USUARIO TEM PERMISSÃO DE ADM 
    if($_SESSION['perfil'] !=1){
        echo "<script>alert('Acesso negado!');wiondow.location.href='principal.php';</script>";
        exit();
    }

$procedimento = null;

// PROCESSA ALTERAÇÃO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_procedimento']) && isset($_POST['acao']) && $_POST['acao'] === 'alterar') {
    $id_procedimento = $_POST['id_procedimento'];
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);

    $sql = "UPDATE procedimento SET nome = :nome, descricao = :descricao";


    $sql .= " WHERE id_procedimento = :id_procedimento";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':id_procedimento', $id_procedimento, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<script>alert('Procedimento alterado com sucesso!'); window.location.href='alterar_procedimento.php';</script>";
        exit();
    } else {
        echo "<script>alert('Erro ao alterar procedimento!'); window.location.href='alterar_procedimento.php';</script>";
        exit();
    }
}

// Processa busca de procedimento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['busca_procedimento']) && (!isset($_POST['acao']) || $_POST['acao'] !== 'alterar')) {
    $busca = trim($_POST['busca_procedimento']);

    if (is_numeric($busca)) {
        $sql = "SELECT * FROM procedimento WHERE id_procedimento = :busca";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM procedimento WHERE nome LIKE :busca_nome";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
    }

    $stmt->execute();
    $procedimento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$procedimento) {
        echo "<script>alert('Procedimento não encontrado!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Íris Essence - Alterar Usuário</title>
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
    <!-- Formulário para buscar usuário pelo ID ou Nome -->
    <form action="alterar_procedimento.php" method="POST">
        <legend>Alterar Procedimento</legend>
        <label for="busca_procedimento">Digite o ID ou Nome do procedimento:</label>
        <input type="text" id="busca_procedimento" name="busca_procedimento" required>
        <div id="sugestoes"></div>
        <button class="botao_cadastro" type="submit">Buscar</button>

        
    </form>

    <?php if ($procedimento): ?>
        <!-- Formulário para alterar usuário -->
        <form action="alterar_procedimento.php" method="POST">
            <input type="hidden" name="id_procedimento" value="<?= htmlspecialchars($procedimento['id_procedimento']) ?>">
            <input type="hidden" name="acao" value="alterar">

            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($procedimento['nome']) ?>" required>

            <label for="descricao">Descrição:</label>
            <input type="text" id="descricao" name="descricao" value="<?= htmlspecialchars($procedimento['descricao']) ?>" required>

            <div class="botoes">
                <button class="botao_cadastro" type="submit">Alterar</button>
                <button class="botao_limpeza" type="reset">Cancelar</button>
            </div>

        </form>
    <?php endif; ?>
    <br>
    <button type="button" class="voltar-button" onclick="window.location.href='principal.php'">Voltar</button>
</fieldset>
</div>

<br><br>

<footer class="l-footer">&copy; 2025 Íris Essence - Beauty Clinic. Todos os direitos reservados.</footer>

</body>
</html>
