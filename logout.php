<?php
/**
 * Logout - StickerManager
 * 
 * Script responsável por fazer logout seguro do usuário,
 * destruindo a sessão e cookies associados.
 * 
 * @package StickerManager
 * @subpackage Authentication
 */

// Incluir configurações
require_once 'sessao.php';

// ====================================================
// Encerrar Sessão
// ====================================================

// Chamar função que destrói a sessão de forma segura
encerrarSessao();

// ====================================================
// Redirecionar para Login
// ====================================================

// Redirecionar para página de login com mensagem
$_SESSION['mensagem_sucesso'] = "Você foi desconectado com sucesso!";

// Usar location com redirecionamento seguro
header("Location: logar.php?logout=true");
exit();

?>
