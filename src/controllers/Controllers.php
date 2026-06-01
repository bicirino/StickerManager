<?php
function ctrl_login() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $st = db()->prepare("SELECT * FROM usuarios WHERE email=?"); $st->execute([$email]);
        $u = $st->fetch();
        if ($u && password_verify($senha, $u['senha_hash'])) {
            $_SESSION['user'] = ['id'=>(int)$u['id'],'nome'=>$u['nome'],'email'=>$u['email'],'is_admin'=>(int)$u['is_admin']];
            flash('Bem-vindo, ' . $u['nome'] . '!');
            header('Location: index.php?r=dashboard'); exit;
        }
        flash('Credenciais inválidas.', 'danger');
    }
    require __DIR__ . '/../views/login.php';
}

function ctrl_registrar() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        if (strlen($nome) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 6) {
            flash('Dados inválidos (senha mínima 6 caracteres).', 'danger');
        } else {
            try {
                $st = db()->prepare("INSERT INTO usuarios (nome,email,senha_hash) VALUES (?,?,?)");
                $st->execute([$nome,$email,password_hash($senha, PASSWORD_DEFAULT)]);
                flash('Cadastro realizado. Faça login.');
                header('Location: index.php?r=login'); exit;
            } catch (PDOException $e) {
                flash('Email já cadastrado.', 'danger');
            }
        }
    }
    require __DIR__ . '/../views/registrar.php';
}

function ctrl_logout() {
    session_destroy();
    header('Location: index.php?r=login'); exit;
}

function ctrl_dashboard() {
    require_login();
    $uid = $_SESSION['user']['id'];
    $stats = Figurinha::stats($uid);
    $porSel = Figurinha::porSelecao($uid);
    $pct = $stats['total'] > 0 ? round($stats['obtidas']*100/$stats['total'],1) : 0;
    require __DIR__ . '/../views/dashboard.php';
}

function ctrl_colecao() {
    require_login();
    $uid = $_SESSION['user']['id'];
    $filters = [
        'uid' => $uid,
        'selecao_id'   => $_GET['selecao'] ?? '',
        'posicao_id'   => $_GET['posicao'] ?? '',
        'categoria_id' => $_GET['categoria'] ?? '',
        'status'       => $_GET['status'] ?? '',
        'busca'        => trim($_GET['q'] ?? ''),
    ];
    $figs = Figurinha::all($filters);
    $selecoes = Lookup::selecoes();
    $posicoes = Lookup::posicoes();
    $categorias = Lookup::categorias();
    require __DIR__ . '/../views/colecao.php';
}

function ctrl_repetidas() {
    require_login();
    $uid = $_SESSION['user']['id'];
    $figs = Figurinha::all(['uid'=>$uid,'status'=>'repetidas']);
    require __DIR__ . '/../views/repetidas.php';
}

function ctrl_toggle() {
    require_login(); csrf_check();
    header('Content-Type: application/json');
    $fid = (int)($_POST['figurinha_id'] ?? 0);
    $q = Colecao::toggle($_SESSION['user']['id'], $fid);
    echo json_encode(['ok'=>true,'quantidade'=>$q]); exit;
}

function ctrl_ajustar() {
    require_login(); csrf_check();
    header('Content-Type: application/json');
    $fid = (int)($_POST['figurinha_id'] ?? 0);
    $delta = (int)($_POST['delta'] ?? 0);
    $q = Colecao::ajustar($_SESSION['user']['id'], $fid, $delta);
    echo json_encode(['ok'=>true,'quantidade'=>$q]); exit;
}

// CRUD admin
function ctrl_figurinhas() {
    require_admin();
    $figs = Figurinha::all(['uid'=>$_SESSION['user']['id']]);
    require __DIR__ . '/../views/figurinhas_list.php';
}

function ctrl_figurinha_form() {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    $fig = $id ? Figurinha::find($id) : ['id'=>0,'numero'=>'','nome_jogador'=>'','categoria_id'=>1,'selecao_id'=>'','posicao_id'=>'','raridade'=>'Comum'];
    if (!$fig) { flash('Figurinha não encontrada.', 'danger'); header('Location: index.php?r=figurinhas'); exit; }
    $selecoes = Lookup::selecoes(); $posicoes = Lookup::posicoes(); $categorias = Lookup::categorias();
    require __DIR__ . '/../views/figurinha_form.php';
}

function ctrl_figurinha_salvar() {
    require_admin(); csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        'numero' => trim($_POST['numero'] ?? ''),
        'nome_jogador' => trim($_POST['nome_jogador'] ?? ''),
        'categoria_id' => (int)$_POST['categoria_id'],
        'selecao_id' => $_POST['selecao_id'] ?: null,
        'posicao_id' => $_POST['posicao_id'] ?: null,
        'raridade' => $_POST['raridade'] ?? 'Comum',
    ];
    if ($data['numero']==='' || $data['nome_jogador']==='') {
        flash('Número e nome são obrigatórios.', 'danger');
        header('Location: index.php?r=figurinha_form' . ($id?"&id=$id":'')); exit;
    }
    try {
        if ($id) { Figurinha::update($id, $data); flash('Figurinha atualizada.'); }
        else { Figurinha::create($data); flash('Figurinha criada.'); }
    } catch (PDOException $e) { flash('Erro: ' . $e->getMessage(), 'danger'); }
    header('Location: index.php?r=figurinhas'); exit;
}

function ctrl_figurinha_excluir() {
    require_admin(); csrf_check();
    Figurinha::delete((int)$_POST['id']);
    flash('Figurinha excluída.');
    header('Location: index.php?r=figurinhas'); exit;
}

function ctrl_relatorio() {
    require_login();
    require __DIR__ . '/../../vendor_local/fpdf/fpdf.php';
    $uid = $_SESSION['user']['id'];
    $u = $_SESSION['user'];
    $stats = Figurinha::stats($uid);
    $porSel = Figurinha::porSelecao($uid);
    $faltantes = Figurinha::all(['uid'=>$uid,'status'=>'faltantes']);
    $repetidas = Figurinha::all(['uid'=>$uid,'status'=>'repetidas']);

    $conv = fn($s)=> iconv('UTF-8','ISO-8859-1//TRANSLIT', (string)$s);

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,$conv('StickerManager — Relatório Copa 2026'),0,1,'C');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,6,$conv('Colecionador: ' . $u['nome'] . ' — gerado em ' . date('d/m/Y H:i')),0,1,'C');
    $pdf->Ln(4);

    $pdf->SetFont('Arial','B',12); $pdf->Cell(0,8,$conv('Resumo'),0,1);
    $pdf->SetFont('Arial','',11);
    $pct = $stats['total']>0 ? round($stats['obtidas']*100/$stats['total'],1) : 0;
    $pdf->Cell(0,6,$conv("Total de figurinhas: {$stats['total']}"),0,1);
    $pdf->Cell(0,6,$conv("Obtidas: {$stats['obtidas']} ({$pct}%)"),0,1);
    $pdf->Cell(0,6,$conv("Faltantes: {$stats['faltantes']}"),0,1);
    $pdf->Cell(0,6,$conv("Repetidas (excedentes): {$stats['repetidas']}"),0,1);
    $pdf->Ln(2);
    // Calculadora simples: pacotes restantes (5 figurinhas / pacote, R$ 5,00)
    $pacotes = (int)ceil($stats['faltantes'] / 5);
    $pdf->Cell(0,6,$conv("Estimativa para completar (5 figurinhas/pacote): ~{$pacotes} pacotes (~R$ " . number_format($pacotes*5,2,',','.') . ")"),0,1);
    $pdf->Cell(0,6,$conv("Dica: troque suas {$stats['repetidas']} repetidas antes de comprar mais pacotes!"),0,1);
    $pdf->Ln(4);

    $pdf->SetFont('Arial','B',12); $pdf->Cell(0,8,$conv('Progresso por Seleção'),0,1);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(80,6,$conv('Seleção'),1); $pdf->Cell(30,6,'Obtidas',1,0,'C'); $pdf->Cell(30,6,'Total',1,0,'C'); $pdf->Cell(30,6,'%',1,1,'C');
    $pdf->SetFont('Arial','',10);
    foreach ($porSel as $s) {
        $p = $s['total']>0 ? round($s['obtidas']*100/$s['total'],0) : 0;
        $pdf->Cell(80,5,$conv($s['nome']),1);
        $pdf->Cell(30,5,(string)$s['obtidas'],1,0,'C');
        $pdf->Cell(30,5,(string)$s['total'],1,0,'C');
        $pdf->Cell(30,5,$p.'%',1,1,'C');
    }

    $pdf->AddPage();
    $pdf->SetFont('Arial','B',12); $pdf->Cell(0,8,$conv('Figurinhas Repetidas (para troca)'),0,1);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(20,6,'Num',1); $pdf->Cell(90,6,$conv('Jogador'),1); $pdf->Cell(50,6,$conv('Seleção'),1); $pdf->Cell(25,6,'Qtd',1,1,'C');
    $pdf->SetFont('Arial','',9);
    foreach ($repetidas as $f) {
        $pdf->Cell(20,5,$conv($f['numero']),1);
        $pdf->Cell(90,5,$conv($f['nome_jogador']),1);
        $pdf->Cell(50,5,$conv($f['selecao_nome'] ?? '-'),1);
        $pdf->Cell(25,5,(string)($f['quantidade']-1),1,1,'C');
    }

    $pdf->AddPage();
    $pdf->SetFont('Arial','B',12); $pdf->Cell(0,8,$conv('Faltantes'),0,1);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(20,6,'Num',1); $pdf->Cell(95,6,$conv('Jogador'),1); $pdf->Cell(50,6,$conv('Seleção'),1); $pdf->Cell(25,6,$conv('Posição'),1,1);
    $pdf->SetFont('Arial','',9);
    foreach ($faltantes as $f) {
        $pdf->Cell(20,5,$conv($f['numero']),1);
        $pdf->Cell(95,5,$conv($f['nome_jogador']),1);
        $pdf->Cell(50,5,$conv($f['selecao_nome'] ?? '-'),1);
        $pdf->Cell(25,5,$conv($f['posicao_nome'] ?? '-'),1,1);
    }

    $pdf->Output('I','stickermanager-relatorio.pdf');
    exit;
}
