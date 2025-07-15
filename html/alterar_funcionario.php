<?php
session_start();
require_once 'conexao.php';

// VERIFICA SE USUARIO TEM PERMISSÃO DE ADM
if ($_SESSION['perfil'] != 1) {
    echo "<script>alert('Acesso negado!');window.location.href='principal.php';</script>";
    exit();
}

// Busca cargos do banco
$sql_cargos = "SELECT id_cargo, nome FROM cargo ORDER BY nome";
$stmt_cargos = $pdo->prepare($sql_cargos);
$stmt_cargos->execute();
$cargos = $stmt_cargos->fetchAll(PDO::FETCH_ASSOC);

// Busca procedimentos do banco
$sql_procedimentos = "SELECT id_procedimento, nome FROM procedimento ORDER BY nome";
$stmt_procedimentos = $pdo->prepare($sql_procedimentos);
$stmt_procedimentos->execute();
$procedimentos = $stmt_procedimentos->fetchAll(PDO::FETCH_ASSOC);

$funcionario = null;

// PROCESSA ALTERAÇÃO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_funcionario'], $_POST['acao']) && $_POST['acao'] === 'alterar') {
    $id_funcionario = $_POST['id_funcionario'];
    $nome = trim($_POST['nome']);
    $data_nascimento = trim($_POST['data_nascimento']);
    $telefone = preg_replace('/\D/', '', $_POST['telefone']);
    $endereco = trim($_POST['endereco']);
    $email = trim($_POST['email']);
    $genero = trim($_POST['genero']);
    $id_cargo = $_POST['id_cargo'];
    $id_perfil = $_POST['id_perfil'];
    $id_procedimento = $_POST['id_procedimento'] ?? null;

    $sql = "UPDATE funcionario SET 
                nome = :nome, 
                data_nascimento = :data_nascimento,
                telefone = :telefone, 
                endereco = :endereco, 
                email = :email, 
                genero = :genero, 
                id_cargo = :id_cargo, 
                id_perfil = :id_perfil,
                id_procedimento = :id_procedimento
            WHERE id_funcionario = :id_funcionario";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':data_nascimento', $data_nascimento);
    $stmt->bindParam(':telefone', $telefone);
    $stmt->bindParam(':endereco', $endereco);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':genero', $genero);
    $stmt->bindParam(':id_cargo', $id_cargo, PDO::PARAM_INT);
    $stmt->bindParam(':id_perfil', $id_perfil, PDO::PARAM_INT);

    if ($id_procedimento === null || $id_procedimento === '') {
        $stmt->bindValue(':id_procedimento', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindParam(':id_procedimento', $id_procedimento, PDO::PARAM_INT);
    }

    $stmt->bindParam(':id_funcionario', $id_funcionario, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<script>alert('Funcionário alterado com sucesso!'); window.location.href='alterar_funcionario.php';</script>";
        exit();
    } else {
        echo "<script>alert('Erro ao alterar funcionário!'); window.location.href='alterar_funcionario.php';</script>";
        exit();
    }
}

// BUSCA FUNCIONÁRIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['busca_funcionario']) && (!isset($_POST['acao']) || $_POST['acao'] !== 'alterar')) {
    $busca = trim($_POST['busca_funcionario']);

    if (is_numeric($busca)) {
        $sql = "SELECT * FROM funcionario WHERE id_funcionario = :busca";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM funcionario WHERE nome LIKE :busca_nome";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
    }

    $stmt->execute();
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$funcionario) {
        echo "<script>alert('Funcionário não encontrado!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Íris Essence - Alterar Funcionário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="../css/style.css" rel="stylesheet" />
    <link rel="icon" href="../imgs/logo.jpg" type="image/x-icon" />
</head>
<body class="cadastro-fundo">

<header>
<nav>
        <ul>
            <a href="../html/index.html"><img src="../imgs/logo.jpg" class="logo" alt="Logo"></a>
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
            <li><a href="#">PROCEDIMENTOS CORPORAIS</a>
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
        <form action="alterar_funcionario.php" method="POST">
            <legend>Alterar Funcionário</legend>
            <label for="busca_funcionario">Digite o ID ou Nome do Funcionário:</label>
            <input type="text" id="busca_funcionario" name="busca_funcionario" required>
            <button class="botao_cadastro" type="submit">Buscar</button>
            <br><br>
            <button type="button" class="voltar-button" onclick="window.location.href='principal.php'">Voltar</button>
        </form>

        <?php if ($funcionario): ?>
        <form action="alterar_funcionario.php" method="POST">
            <input type="hidden" name="id_funcionario" value="<?= htmlspecialchars($funcionario['id_funcionario']) ?>">
            <input type="hidden" name="acao" value="alterar">

            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($funcionario['nome']) ?>" required>

            <label for="data_nascimento">Data de Nascimento:</label>
            <input type="date" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars($funcionario['data_nascimento']) ?>" required>

            <label for="telefone">Telefone:</label>
            <input type="tel" id="telefone" name="telefone" value="<?= htmlspecialchars($funcionario['telefone']) ?>" required placeholder="(11) 90000-0000">

            <label for="endereco">Endereço:</label>
            <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($funcionario['endereco']) ?>" required>

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($funcionario['email']) ?>" required>

            <label for="genero">Gênero:</label>
            <select id="genero" name="genero" required>
                <option value="M" <?= $funcionario['genero'] == "M" ? 'selected' : '' ?>>Homem</option>
                <option value="F" <?= $funcionario['genero'] == "F" ? 'selected' : '' ?>>Mulher</option>
            </select>

            <label for="id_cargo">Cargo:</label>
            <select id="id_cargo" name="id_cargo" required>
                <option value="">Selecione um cargo</option>
                <?php foreach ($cargos as $cargo): ?>
                    <option value="<?= $cargo['id_cargo'] ?>" <?= $cargo['id_cargo'] == $funcionario['id_cargo'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cargo['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="id_perfil">Perfil:</label>
            <select id="id_perfil" name="id_perfil" required>
                <option value="1" <?= $funcionario['id_perfil'] == 1 ? 'selected' : '' ?>>Administrador</option>
                <option value="2" <?= $funcionario['id_perfil'] == 2 ? 'selected' : '' ?>>Recepcionista/Esteticista</option>
            </select>

            <!-- Select de procedimentos, só aparece se cargo = 1 (Esteticista) -->
            <div id="procedimentos-container" style="display:none; margin-top: 15px;">
                <label for="id_procedimento">Procedimento Responsável:</label>
                <select id="id_procedimento" name="id_procedimento">
                    <option value="">--Selecione o procedimento responsável--</option>
                    <?php foreach ($procedimentos as $proc): ?>
                        <option value="<?= $proc['id_procedimento'] ?>" <?= $proc['id_procedimento'] == $funcionario['id_procedimento'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($proc['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="botoes">
                <button class="botao_cadastro" type="submit">Alterar</button>
                <button class="botao_limpeza" type="reset">Cancelar</button>
            </div>

            <button type="button" class="voltar-button" onclick="window.location.href='principal.php'">Voltar</button>
        </form>
        <?php endif; ?>
    </fieldset>
</div>

<br><br>

<footer class="l-footer">&copy; 2025 Íris Essence - Beauty Clinic. Todos os direitos reservados.</footer>

<script>
// Exibe ou oculta o select de procedimentos dependendo do cargo selecionado
document.getElementById('id_cargo').addEventListener('change', function() {
    const procedimentosContainer = document.getElementById('procedimentos-container');
    if (this.value === '1') { // cargo Esteticista
        procedimentosContainer.style.display = 'block';
        document.getElementById('id_procedimento').setAttribute('required', 'required');
    } else {
        procedimentosContainer.style.display = 'none';
        document.getElementById('id_procedimento').removeAttribute('required');
        document.getElementById('id_procedimento').value = '';
    }
});

// Dispara o evento change ao carregar para manter estado
window.addEventListener('DOMContentLoaded', function() {
    const selectCargo = document.getElementById('id_cargo');
    if (selectCargo) {
        selectCargo.dispatchEvent(new Event('change'));
    }
});

// Máscara para telefone
document.getElementById('telefone').addEventListener('input', function(e) {
    let valor = e.target.value.replace(/\D/g, '');
    if (valor.length > 11) valor = valor.slice(0, 11);

    if (valor.length > 6) {
        e.target.value = `(${valor.slice(0, 2)}) ${valor.slice(2, 7)}-${valor.slice(7)}`;
    } else if (valor.length > 2) {
        e.target.value = `(${valor.slice(0, 2)}) ${valor.slice(2)}`;
    } else if (valor.length > 0) {
        e.target.value = `(${valor}`;
    }
});
</script>

</body>
</html>
