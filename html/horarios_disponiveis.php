<?php
    // Conexão com o banco de dados
    require_once 'conexao.php';
    
    $data = // Dados recebidos via URL
    $_GET['data'] ?? '';
    $ignorar = // Dados recebidos via URL
    $_GET['ignorar'] ?? '';
    
    if (!$data) {
        echo json_encode([]);
        exit;
    }
    
    $grade = [
    "08:00", "09:00", "10:00", "11:00",
    "13:00", "14:00", "15:00", "16:00", "17:00"
    ];
    
    // Pega os horários ocupados no banco para a data informada
    $sql = "SELECT hora FROM agendamentos WHERE data = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data]);
    $ocupados_brutos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Normaliza para HH:MM (remove segundos, espaços)
    $ocupados = array_map(function($h) {
        return substr(trim($h), 0, 5);
    }, $ocupados_brutos);
    
    // Remove o horário atual da edição, se existir
    if ($ignorar !== '') {
        $ignorar = substr(trim($ignorar), 0, 5);
        $ocupados = array_filter($ocupados, function($h) use ($ignorar) {
            return $h !== $ignorar;
        });
    }
    
    $disponiveis = array_values(array_diff($grade, $ocupados));
    
    echo json_encode($disponiveis);
    