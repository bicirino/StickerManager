<?php
/**
 * Configuração de Conexão - StickerManager
 * 
 * Arquivo centralizado que gerencia a instância de conexão
 * com o banco de dados MySQL. Utiliza mysqli para maior segurança.
 * 
 * @package StickerManager
 * @subpackage Database
 */

// ====================================================
// Configurações de Conexão
// ====================================================

// DESENVOLVIMENTO - Ajuste conforme necessário
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';  // MySQL sem senha por padrão em desenvolvimento
$DB_NAME = 'sticker_manager';
$DB_PORT = 3306;

// ====================================================
// Criar Conexão com Tratamento de Erro
// ====================================================

try {
    // Criar conexão usando mysqli
    $conexao = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
    
    // Verificar conexão
    if ($conexao->connect_error) {
        throw new Exception("Erro ao conectar ao banco de dados: " . $conexao->connect_error);
    }
    
    // Definir charset UTF-8
    $conexao->set_charset("utf8mb4");
    
    // Habilitar tratamento de erros
    $conexao->report_mode = MYSQLI_REPORT_STRICT;
    
} catch (Exception $erro) {
    // Em produção, não exibir detalhes do erro
    // Apenas registrar em log
    error_log("Erro de conexão BD: " . $erro->getMessage());
    
    // Redirecionar para página de erro ou exibir mensagem genérica
    die("Erro: Não foi possível conectar ao banco de dados. Tente novamente mais tarde.");
}

/**
 * Função auxiliar para escapar strings (proteção contra SQL Injection)
 * 
 * @param string $valor String a ser escapada
 * @param mysqli $conexao Instância da conexão
 * @return string String escapada
 */
function escapar($valor, $conexao = null) {
    global $conexao;
    if ($conexao instanceof mysqli) {
        return $conexao->real_escape_string($valor);
    }
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

/**
 * Função auxiliar para preparar consultas seguras
 * 
 * @param string $sql Consulta SQL com placeholders (?)
 * @param string $tipos Tipos dos parâmetros (s=string, i=int, d=double, b=blob)
 * @param array $parametros Array com os valores dos parâmetros
 * @param mysqli $conexao Instância da conexão
 * @return mysqli_stmt|false Statement preparada ou false em erro
 */
function preparar($sql, $tipos, $parametros, $conexao = null) {
    global $conexao;
    
    try {
        $stmt = $conexao->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro ao preparar: " . $conexao->error);
        }
        
        if (!empty($parametros)) {
            $stmt->bind_param($tipos, ...$parametros);
        }
        
        $stmt->execute();
        return $stmt;
        
    } catch (Exception $erro) {
        error_log("Erro em preparar: " . $erro->getMessage());
        return false;
    }
}

?>
