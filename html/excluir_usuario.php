<?php
    // Conexão com o banco de dados
    // Inicia a sessão do usuário
    session_start();
    require 'conexao.php';
    
    // Verifica se o usuário tem permissão de ADM
    if ($_SESSION['perfil'] != 1) {
        echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
        exit();
    }
    
    // Inicializa variável para armazenar usuários
    $usuarios = [];
    
    // BUSCA EM ORDEM ALFABETICA
    $sql = "SELECT * FROM usuario ORDER BY nome ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Se um ID for passado via GET, exclui o usuário
    if (isset(// Dados recebidos via URL
    $_GET['id']) && is_numeric(// Dados recebidos via URL
    $_GET['id'])) {
        $id_usuario = // Dados recebidos via URL
        $_GET['id'];
        
        // EXCLUI DO BANCO
        $sql = "DELETE FROM usuario WHERE id_usuario = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            echo "<script>alert('Usuário excluído com sucesso!'); window.location.href='excluir_usuario.php';</script>";
        } else {
            echo "<script>alert('Erro ao excluir usuário!');</script>";
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

            <div class="logout">
                <form action = "logout.php" method= "POST">
                <button type="submit">Logout</button>
            </div>
        </ul>
    </nav>
</header>
<br>

<fieldset class="excluir">
<legend>Excluir usuário</legend>
<?php if (!empty($usuarios)): ?>
<table border="1">
<tr>
<th>ID</th>
<th>Nome</th>
<th>Email</th>
<th>Perfil</th>
<th>Ações</th>
</tr>
<?php foreach ($usuarios as $usuario): ?>
<tr>
<td><?= htmlspecialchars($usuario['id_usuario']) ?></td>
<td><?= htmlspecialchars($usuario['nome']) ?></td>
<td><?= htmlspecialchars($usuario['email']) ?></td>
<td><?= htmlspecialchars($usuario['id_perfil']) ?></td>
<td>
<a href="excluir_usuario.php?id=<?= htmlspecialchars($usuario['id_usuario']) ?>" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">🗑️</a>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p>Nenhum usuário encontrado.</p>
<?php endif; ?>

<br>
<button type="button" class="voltar-button" onclick="window.location.href='principal.php'">Voltar</button>
</fieldset>

<br><br>
<footer class="l-footer">&copy; 2025 Iris Essence - Beauty Clinic. Todos os direitos reservados.</footer>
</body>
</html>