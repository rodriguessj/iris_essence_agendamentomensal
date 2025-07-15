<?php
session_start();
require_once 'conexao.php';

    //VERIFICA SE USUARIO TEM PERMISSÃO DE ADM 
    if($_SESSION['perfil'] !=1){
        echo "<script>alert('Acesso negado!');wiondow.location.href='principal.php';</script>";
        exit();
    }

$mensagem = "";
$tipoMensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $endereco = trim($_POST['endereco']);
    $telefone = preg_replace('/\D/', '', $_POST['telefone']); // remove máscara
    $produto = trim($_POST['produto']);

    // Validação mínima no servidor
    if ($nome && $endereco && preg_match('/^[0-9]{10,11}$/', $telefone) && $produto) {
        $sql = "INSERT INTO fornecedor (nome, endereco, telefone, produto) 
                VALUES (:nome, :endereco, :telefone, :produto)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':endereco', $endereco);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':produto', $produto);

        if ($stmt->execute()) {
            $mensagem = "Fornecedor cadastrado com sucesso!";
            $tipoMensagem = "sucesso";
        } else {
            $mensagem = "Erro ao cadastrar fornecedor!";
            $tipoMensagem = "erro";
        }
    } else {
        $mensagem = "Por favor, preencha todos os campos corretamente!";
        $tipoMensagem = "erro";
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
<link rel="stylesheet" href="../css/style.css">
<link rel="icon" href="../imgs/logo.jpg" type="image/x-icon">
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
<form id="formFornecedor" action="cadastro_fornecedor.php" method="POST" onsubmit="return validarFormulario();">
    <legend>Cadastrar fornecedor</legend>

    <label for="nome">Nome: </label>
    <input type="text" id="nome" name="nome" required>

    <label for="endereco">Endereço: </label>
    <input type="text" id="endereco" name="endereco" required>

    <label for="telefone">Telefone: </label>
    <input type="tel" id="telefone" name="telefone" placeholder="(11) 99999-9999" required>

    <label for="produto">Produto: </label>
    <input type="text" id="produto" name="produto" required>

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

<script>
// Bloquear números no nome e produto
document.getElementById('nome').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
});
document.getElementById('produto').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
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

// Validação final no envio
function validarFormulario() {
    const nome = document.getElementById('nome').value.trim();
    const endereco = document.getElementById('endereco').value.trim();
    const telefone = document.getElementById('telefone').value.replace(/\D/g, '');
    const produto = document.getElementById('produto').value.trim();

    if (!nome || !endereco || !telefone || !produto) return false;
    if (!/^\d{10,11}$/.test(telefone)) return false;

    return true;
}

// Mensagem do PHP após envio
<?php if (!empty($mensagem)): ?>
    window.onload = function() {
        alert("<?= $mensagem ?>");
        <?php if ($tipoMensagem === 'sucesso'): ?>
            document.getElementById('formFornecedor').reset();
        <?php endif; ?>
    };
<?php endif; ?>
</script>

</body>
</html>
