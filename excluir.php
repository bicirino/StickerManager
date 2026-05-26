<?php
/**
 * Excluir Figurinha - StickerManager
 * 
 * Script que processa a exclusão de uma figurinha da coleção do usuário.
 * 
 * @package StickerManager
 * @subpackage CRUD
 */

// Incluir configurações
require_once 'db/conexao.php';
require_once 'sessao.php';

// ====================================================
// Validar Sessão
// ====================================================
validarSessao(true);

$id_usuario = obterIdUsuario();

// ====================================================
// Buscar e Validar Figurinha
// ====================================================
$id_figurinha = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_figurinha <= 0) {
    // ID inválido
    $_SESSION['erro_mensagem'] = "ID da figurinha inválido.";
    header("Location: index.php");
    exit();
}

try {
    // Verificar se a figurinha pertence ao usuário
    $sql_verif = "SELECT id_colecao FROM Minha_Colecao 
                 WHERE id_usuario = ? AND id_figurinha = ?";
    $stmt = preparar($sql_verif, 'ii', [$id_usuario, $id_figurinha]);
    $resultado = $stmt->get_result();
    $stmt->close();
    
    if ($resultado->num_rows === 0) {
        // Figurinha não pertence ao usuário
        $_SESSION['erro_mensagem'] = "Figurinha não encontrada na sua coleção.";
        header("Location: index.php");
        exit();
    }
    
    // Deletar a figurinha da coleção
    $sql_delete = "DELETE FROM Minha_Colecao 
                  WHERE id_usuario = ? AND id_figurinha = ?";
    $stmt = preparar($sql_delete, 'ii', [$id_usuario, $id_figurinha]);
    
    if ($stmt && $conexao->affected_rows > 0) {
        $_SESSION['sucesso_mensagem'] = "Figurinha removida da sua coleção com sucesso!";
    } else {
        $_SESSION['erro_mensagem'] = "Erro ao remover figurinha.";
    }
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Erro ao excluir figurinha: " . $e->getMessage());
    $_SESSION['erro_mensagem'] = "Erro ao processar exclusão.";
}

// ====================================================
// Redirecionar para Dashboard
// ====================================================
header("Location: index.php");
exit();

?>
