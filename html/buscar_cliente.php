<?php
require_once 'conexao.php';
session_start();

// VERIFICA SE A SESSÃO FOI INICIADA CORRETAMENTE E PERFIL ESTÁ DEFINIDO
if (!isset($_SESSION['perfil']) || ($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 2)) {
    echo "<script>alert('Acesso negado!');window.location.href='principal.php';</script>";
    exit();
}


$clientes = []; // INICIALIZA A VARIAVEL PARA EVITAR ERROS

// SE O FORMULÁRIO FOR ENVIADO, BUSCA O USUÁRIO PELO ID OU NOME
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['busca'])) {
    $busca = trim($_POST['busca']);

    // VERIFICA SE A BUSCA É UM NÚMERO(ID) OU UM NOME
    if (is_numeric($busca)) {
        $sql = "SELECT * FROM cliente WHERE id_cliente = :busca ORDER BY nome ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM cliente WHERE nome LIKE :busca_nome ORDER BY nome ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
    }
} else {
    $sql = "SELECT * FROM cliente ORDER BY nome ASC";
    $stmt = $pdo->prepare($sql);
}
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <li><a href="#">PROCEDIMENTOS FACIAIS</a>
                <div class="submenu">
                    <a href="../html/limpezapele.html">Limpeza de Pele</a>
                    <a href="../html/labial.html">Preenchimento labial</a>
                    <a href="../html/microagulhamento.html">Microagulhamento</a>
                    <a href="../html/botoxfacial.html">Botox</a>
                    <a href="../html/acne.html">Tratamento para Acne</a>
                    <a href="../html/rinomodelacao.html">Rinomodelação</a>
                </div>
            </li>
            <li><a href="../html/produtos.html">PRODUTOS</a></li>
            <li><a href="../html/login.php">LOGIN</a></li>
            <li><a href="../html/cadastro.html">CADASTRO</a></li>
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

<!-- FORMULARIO PARA BUSCAR -->
<form action="buscar_cliente.php" method="POST">
    <legend>Listar clientes</legend>
    <label for="busca">Digite o ID ou NOME(opcional):</label>
    <input type="text" id="busca" name="busca">
    <button type="submit">Pesquisar</button>
</form>

<?php if (!empty($clientes)): ?>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Telefone</th>
            <th>Endereço</th>
            <th>Email</th>
            <th>Data de Nascimento</th>
            <th>Genero</th>
            <th>Perfil</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($clientes as $cliente): ?>
        <tr>
            <td><?= htmlspecialchars($cliente['id_cliente']) ?></td>
            <td><?= htmlspecialchars($cliente['nome']) ?></td>
            <td><?= htmlspecialchars($cliente['telefone']) ?></td>
            <td><?= htmlspecialchars($cliente['endereco']) ?></td>
            <td><?= htmlspecialchars($cliente['email']) ?></td>
            <td><?= htmlspecialchars($cliente['data_nascimento']) ?></td>
            <td><?= htmlspecialchars($cliente['genero']) ?></td>
            <td><?= htmlspecialchars($cliente['id_perfil']) ?></td>
            <td>
                <a href="alterar_cliente.php?id=<?= htmlspecialchars($cliente['id_cliente']) ?>">✏️</a>
                <a href="excluir_cliente.php?id=<?= htmlspecialchars($cliente['id_cliente']) ?>" onclick="return confirm('Tem certeza que deseja excluir este cliente?')">🗑️</a>
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
</div>

<br><br>
<footer class="l-footer">&copy; 2025 Iris Essence - Beauty Clinic. Todos os direitos reservados.</footer>

</body>
</html>
