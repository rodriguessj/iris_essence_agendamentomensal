<?php
require_once 'conexao.php';
session_start();

// Verifica se o usuário tem permissão de ADM
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != 1) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit();
}

// Exclui funcionário se for passado um ID via GET
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_funcionario = $_GET['id'];

    $sql = "DELETE FROM funcionario WHERE id_funcionario = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id_funcionario, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<script>alert('Funcionário excluído com sucesso!'); window.location.href='excluir_funcionario.php';</script>";
        exit();
    } else {
        echo "<script>alert('Erro ao excluir funcionário!');</script>";
    }
}

// Busca todos os funcionários com JOIN para nome do cargo, perfil e procedimento
$sql = "SELECT f.*, c.nome AS nome_cargo, p.nome_perfil AS nome_perfil, pr.nome AS nome_procedimento
        FROM funcionario f
        LEFT JOIN cargo c ON f.id_cargo = c.id_cargo
        LEFT JOIN perfil p ON f.id_perfil = p.id_perfil
        LEFT JOIN procedimento pr ON f.id_procedimento = pr.id_procedimento
        ORDER BY f.nome ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <a href="../html/index.html"><img src="../imgs/logo.jpg" class="logo" alt="Logo"></a>
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

<br><br>

<fieldset class="excluir">
<legend>Excluir Funcionário</legend>

<?php if (!empty($funcionarios)): ?>
<table border="1" class="table table-bordered table-striped">
<tr>
<th>ID</th>
<th>Nome</th>
<th>Data de Nascimento</th>
<th>Telefone</th>
<th>Endereço</th>
<th>Email</th>
<th>Gênero</th>
<th>Cargo</th>
<th>Perfil</th>
<th>Procedimento</th>
<th>Ações</th>
</tr>
<?php foreach ($funcionarios as $funcionario): ?>
<tr>
    <td><?= htmlspecialchars($funcionario['id_funcionario']) ?></td>
    <td><?= htmlspecialchars($funcionario['nome']) ?></td>
    <td><?= htmlspecialchars($funcionario['data_nascimento']) ?></td>
    <td><?= htmlspecialchars($funcionario['telefone']) ?></td>
    <td><?= htmlspecialchars($funcionario['endereco']) ?></td>
    <td><?= htmlspecialchars($funcionario['email']) ?></td>
    <td><?= htmlspecialchars($funcionario['genero']) ?></td>
    <td><?= htmlspecialchars($funcionario['nome_cargo'] ?? '-') ?></td>
    <td><?= htmlspecialchars($funcionario['nome_perfil'] ?? '-') ?></td>
    <td>
        <?= $funcionario['id_perfil'] == 2 ? htmlspecialchars($funcionario['nome_procedimento'] ?? '-') : '-' ?>
    </td>
    <td>
        <a href="excluir_funcionario.php?id=<?= $funcionario['id_funcionario'] ?>" onclick="return confirm('Tem certeza que deseja excluir este funcionário?')">🗑️</a>
    </td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p>Nenhum funcionário encontrado.</p>
<?php endif; ?>

<br>
<button type="button" class="voltar-button" onclick="window.location.href='principal.php'">Voltar</button>
</fieldset>

<br><br>
<footer class="l-footer">&copy; 2025 Iris Essence - Beauty Clinic. Todos os direitos reservados.</footer>
</body>
</html>
