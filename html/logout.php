<?php
    // Inicia a sessão do usuário
    session_start();
    session_destroy();
    header("Location: login.php");
    exit();
?>