<?php
require_once 'conexao.php';

$procedimento = $_GET['procedimento'] ?? '';

if (!$procedimento) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id_funcionario, nome FROM funcionario 
        INNER JOIN procedimento ON funcionario.id_procedimento = procedimento.id_procedimento 
        WHERE procedimento.nome = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$procedimento]);

$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($dados);
