<?php
// Inicia a sessão do usuário
session_start();
require_once 'conexao.php';

// Garante que o usuário esteja logado
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>alert('Acesso Negado!'); window.location.href='login.php';</script>";
    exit();
}

//PROCESSA ALTERAÇÃO
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_SESSION['id_usuario'];
    $nova_senha = $_POST['nova_senha']; // Dados enviados via formulário
    $confirmar_senha = $_POST['confirmar_senha']; // Dados enviados via formulário

    if ($nova_senha !== $confirmar_senha) {
        echo "<script>alert('As senhas não coincidem!');</script>";
    } elseif (strlen($nova_senha) < 8) {
        echo "<script>alert('A senha deve ter pelo menos 8 caracteres!');</script>";
    } elseif ($nova_senha === "temp123") {
        echo "<script>alert('Escolha uma senha diferente da senha temporária!');</script>";
    } else {
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

        // Atualiza a senha e remove o status de temporária
        $sql = "UPDATE usuario SET senha = :senha, senha_temporaria = 0 WHERE id_usuario = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':senha', $senha_hash);
        $stmt->bindParam(':id', $id_usuario);

        if ($stmt->execute()) {
            session_destroy(); // Finaliza a sessão
            echo "<script>alert('Senha alterada com sucesso! Faça login novamente.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Erro ao alterar a senha!');</script>";
        }
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
            <li><a href="../html/produtos.html">PRODUTOS</a></li>|
            <li><a href="../html/login.php">LOGIN</a></li>|
            <li><a href="../html/cadastro.html">CADASTRO</a></li>|
            <li>
                <form action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-sm btn-danger">Logout</button>
                </form>
            </li>
        </ul>
    </nav>
</header>

<br>

<div class="formulario">
    <form action="alterar_senha.php" method="POST">
        <fieldset>
            <legend>Altere sua senha</legend>
            <p>Olá, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong>. Digite sua nova senha abaixo:</p>

            <label for="nova_senha">Nova Senha:</label>
            <input type="password" id="nova_senha" name="nova_senha" required>

            <label for="confirmar_senha">Confirmar Nova Senha:</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" required>

            <label>
                <input type="checkbox" onclick="mostrarSenha()"> Mostrar Senha
            </label>

            <div class="botoes">
                <button class="botao_cadastro" type="submit">Salvar Nova Senha</button>
            </div>
        </fieldset>
    </form>
</div>

<script>
function mostrarSenha(){
    var senha1 = document.getElementById("nova_senha");
    var senha2 = document.getElementById("confirmar_senha");
    var tipo = senha1.type === "password" ? "text" : "password";
    senha1.type = tipo;
    senha2.type = tipo;
}
</script>

<br><br>
<footer class="l-footer">&copy; 2025 Iris Essence - Beauty Clinic. Todos os direitos reservados.</footer>
</body>
</html>
