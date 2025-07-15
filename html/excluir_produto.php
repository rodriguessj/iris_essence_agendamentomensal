<?php
    require 'conexao.php';
    
    if (!isset(// Dados recebidos via URL
    $_GET['id'])) {
        header("Location: produtos.php");
        exit();
    }
    
    $id = // Dados recebidos via URL
    $_GET['id'];
    
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    
    header("Location: produtos.php");
    exit();