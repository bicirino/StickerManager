<?php
/**
 * Relatório em PDF - StickerManager
 * 
 * Script que gera um relatório consolidado em formato PDF
 * com o status da coleção e figurinhas do usuário.
 * 
 * Dependência: FPDF (http://www.fpdf.org/)
 * Instalação: composer require setasign/fpdf
 * 
 * @package StickerManager
 * @subpackage Reports
 */

// Incluir configurações
require_once 'db/conexao.php';
require_once 'sessao.php';

// ====================================================
// Validar Sessão
// ====================================================
validarSessao(true);

$id_usuario = obterIdUsuario();
$nome_usuario = obterNomeUsuario();

// ====================================================
// Buscar Dados para o Relatório
// ====================================================
try {
    // Estatísticas gerais
    $sql_obtidas = "SELECT COUNT(*) as total FROM Minha_Colecao 
                    WHERE id_usuario = ? AND status = 'obtida'";
    $stmt = preparar($sql_obtidas, 'i', [$id_usuario]);
    $resultado = $stmt->get_result();
    $total_obtidas = $resultado->fetch_assoc()['total'];
    $stmt->close();
    
    $sql_faltantes = "SELECT COUNT(*) as total FROM Minha_Colecao 
                      WHERE id_usuario = ? AND status = 'faltante'";
    $stmt = preparar($sql_faltantes, 'i', [$id_usuario]);
    $resultado = $stmt->get_result();
    $total_faltantes = $resultado->fetch_assoc()['total'];
    $stmt->close();
    
    $sql_repetidas = "SELECT COUNT(*) as total FROM Minha_Colecao 
                      WHERE id_usuario = ? AND status = 'repetida'";
    $stmt = preparar($sql_repetidas, 'i', [$id_usuario]);
    $resultado = $stmt->get_result();
    $total_repetidas = $resultado->fetch_assoc()['total'];
    $stmt->close();
    
    $total_figurinhas = $total_obtidas + $total_faltantes + $total_repetidas;
    $percentual_conclusao = $total_figurinhas > 0 ? round(($total_obtidas / $total_figurinhas) * 100) : 0;
    
    // Buscar figurinhas obtidas detalhadas
    $sql_figurinhas = "SELECT f.numero_figurinha, f.nome_jogador, s.nome_selecao, p.nome_posicao,
                              c.nome_categoria, mc.status, mc.quantidade_obtida, mc.quantidade_repetida
                       FROM Minha_Colecao mc
                       INNER JOIN Figurinhas f ON mc.id_figurinha = f.id_figurinha
                       LEFT JOIN Selecoes s ON f.id_selecao = s.id_selecao
                       LEFT JOIN Posicao p ON f.id_posicao = p.id_posicao
                       LEFT JOIN Categoria c ON f.id_categoria = c.id_categoria
                       WHERE mc.id_usuario = ?
                       ORDER BY f.numero_figurinha ASC";
    
    $stmt = preparar($sql_figurinhas, 'i', [$id_usuario]);
    $resultado = $stmt->get_result();
    $figurinhas = $resultado->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Erro ao buscar dados para relatório: " . $e->getMessage());
    die("Erro ao gerar relatório.");
}

// ====================================================
// Criar PDF com FPDF
// ====================================================

// Verificar se FPDF está disponível
$fpdf_path = __DIR__ . '/vendor/autoload.php';
if (file_exists($fpdf_path)) {
    require_once $fpdf_path;
    use FPDF\FPDF;
} else {
    // Se FPDF não estiver instalado, usar classe simples
    // Baixar de: http://www.fpdf.org/
    // Salvar em: /fpdf/fpdf.php
    if (!file_exists(__DIR__ . '/fpdf/fpdf.php')) {
        die("FPDF não encontrado. Instale com: composer require setasign/fpdf");
    }
    require_once __DIR__ . '/fpdf/fpdf.php';
    if (!class_exists('FPDF')) {
        die("Classe FPDF não carregada");
    }
}

/**
 * Classe PDF customizada
 */
class PDF extends FPDF {
    private $titulo;
    
    public function __construct($titulo = 'Relatório') {
        parent::__construct();
        $this->titulo = $titulo;
    }
    
    public function header() {
        // Logo/Título
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(102, 126, 234);
        $this->Cell(0, 10, 'StickerManager', 0, 1, 'C');
        
        // Subtítulo
        $this->SetFont('Arial', 'I', 12);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 8, $this->titulo, 0, 1, 'C');
        
        // Linha separadora
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $this->GetY() + 3, 200, $this->GetY() + 3);
        
        $this->Ln(5);
    }
    
    public function footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'Página ' . $this->GetPageNumber(), 0, 0, 'C');
        $this->Cell(0, 10, 'Gerado em: ' . date('d/m/Y H:i:s'), 0, 0, 'R');
    }
    
    public function sectionTitle($titulo) {
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(102, 126, 234);
        $this->Cell(0, 10, $titulo, 0, 1);
        $this->SetTextColor(0, 0, 0);
    }
    
    public function tableHeader($headers) {
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(102, 126, 234);
        $this->SetTextColor(255, 255, 255);
        
        $col_widths = [15, 30, 40, 25, 25, 30];
        
        for ($i = 0; $i < count($headers); $i++) {
            $this->Cell($col_widths[$i], 8, $headers[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        $this->SetTextColor(0, 0, 0);
    }
    
    public function tableRow($dados, $col_widths) {
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(50, 50, 50);
        
        foreach ($dados as $i => $valor) {
            $this->Cell($col_widths[$i], 7, substr($valor, 0, 25), 1, 0, 'L');
        }
        $this->Ln();
    }
}

// ====================================================
// Gerar PDF
// ====================================================

$pdf = new PDF('Relatório de Coleção - ' . $nome_usuario);
$pdf->SetFont('Arial', '', 11);
$pdf->AddPage();

// ====================================================
// Seção 1: Estatísticas
// ====================================================
$pdf->sectionTitle('Estatísticas da Coleção');
$pdf->SetFont('Arial', '', 10);

$y_inicial = $pdf->GetY();
$pdf->MultiCell(45, 6, 'Total de Figurinhas: ' . $total_figurinhas);
$pdf->SetY($y_inicial);
$pdf->SetX(65);
$pdf->MultiCell(45, 6, 'Obtidas: ' . $total_obtidas . ' (' . round(($total_obtidas / max(1, $total_figurinhas)) * 100) . '%)');
$pdf->SetY($y_inicial);
$pdf->SetX(120);
$pdf->MultiCell(45, 6, 'Faltantes: ' . $total_faltantes . ' (' . round(($total_faltantes / max(1, $total_figurinhas)) * 100) . '%)');

$pdf->Ln(5);

$y_inicial = $pdf->GetY();
$pdf->SetX(10);
$pdf->MultiCell(45, 6, 'Repetidas: ' . $total_repetidas . ' (' . round(($total_repetidas / max(1, $total_figurinhas)) * 100) . '%)');
$pdf->SetY($y_inicial);
$pdf->SetX(65);
$pdf->MultiCell(45, 6, 'Progresso: ' . $percentual_conclusao . '%');
$pdf->SetY($y_inicial);
$pdf->SetX(120);
$pdf->MultiCell(45, 6, 'Data: ' . date('d/m/Y H:i:s'));

$pdf->Ln(8);

// ====================================================
// Seção 2: Tabela de Figurinhas
// ====================================================
$pdf->sectionTitle('Detalhamento das Figurinhas');

$headers = ['#', 'Jogador', 'Seleção', 'Posição', 'Status', 'Qtd'];
$pdf->tableHeader($headers);

$col_widths = [15, 30, 40, 25, 25, 30];

foreach ($figurinhas as $fig) {
    // Quebra de página se necessário
    if ($pdf->GetY() > 250) {
        $pdf->AddPage();
        $pdf->tableHeader($headers);
    }
    
    $status_display = $fig['status'] === 'obtida' ? 'Obtida' : 
                     ($fig['status'] === 'repetida' ? 'Repetida' : 'Faltante');
    $qtd = $fig['status'] === 'obtida' ? $fig['quantidade_obtida'] : 
          ($fig['status'] === 'repetida' ? $fig['quantidade_repetida'] : '-');
    
    $dados = [
        $fig['numero_figurinha'],
        substr($fig['nome_jogador'], 0, 25),
        substr($fig['nome_selecao'] ?? 'N/A', 0, 20),
        substr($fig['nome_posicao'] ?? 'N/A', 0, 15),
        $status_display,
        $qtd
    ];
    
    $pdf->tableRow($dados, $col_widths);
}

// ====================================================
// Enviar PDF para download
// ====================================================
$pdf->Output('D', 'relatorio_sticker_' . $nome_usuario . '_' . date('Y-m-d_H-i-s') . '.pdf');

?>
