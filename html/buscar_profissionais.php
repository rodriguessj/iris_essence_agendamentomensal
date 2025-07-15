<?php
require_once 'conexao.php';

if (!isset($_GET['id_procedimento'])) {
    echo json_encode([]);
    exit;
}

$id = $_GET['id_procedimento'];

$stmt = $pdo->prepare("SELECT nome FROM funcionario WHERE id_procedimento = ?");
$stmt->execute([$id]);
$profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($profissionais);
