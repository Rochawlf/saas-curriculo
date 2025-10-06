<?php
function create_template_moderno($data) {
    // Cria uma nova instância do TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Remove header e footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Adiciona uma página
    $pdf->AddPage();
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);

    // --- Coluna Esquerda (Cor de Fundo) ---
    $pdf->SetFillColor(45, 52, 54); // Cinza escuro
    $pdf->Rect(0, 0, 70, 297, 'F'); // Desenha o retângulo (x, y, largura, altura, estilo)

    // --- Nome e Cargo (na parte branca) ---
    $pdf->SetTextColor(0, 0, 0); // Preto
    $pdf->SetFont('helvetica', 'B', 24);
    $pdf->SetXY(75, 20); // Posição
    $pdf->Cell(0, 10, $data['nome_completo'] ?? 'Nome não encontrado', 0, 1);
    
    $pdf->SetFont('helvetica', '', 14);
    $pdf->SetTextColor(85, 85, 85);
    $pdf->SetX(75);
    $pdf->Cell(0, 10, $data['cargo'] ?? 'Cargo não encontrado', 0, 1);
    
    // --- Conteúdo da Coluna Esquerda (Texto Branco) ---
    $pdf->SetTextColor(255, 255, 255); // Branco
    $pdf->SetXY(15, 20);
    
    // Contato
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'CONTATO', 0, 1);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->MultiCell(50, 5, 
        ($data['contato']['email'] ?? '') . "\n" .
        ($data['contato']['telefone'] ?? '') . "\n" .
        ($data['contato']['linkedin'] ?? '') . "\n" .
        ($data['contato']['localizacao'] ?? ''), 0, 'L');

    // Habilidades
    $pdf->SetY($pdf->GetY() + 10);
    $pdf->SetX(15);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'HABILIDADES', 0, 1);
    $pdf->SetFont('helvetica', '', 9);
    $habilidades = implode("\n", $data['habilidades'] ?? []);
    $pdf->MultiCell(50, 5, $habilidades, 0, 'L');
    
    // --- Conteúdo da Coluna Direita (Texto Preto) ---
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY(75, 50);

    // Resumo
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'RESUMO PROFISSIONAL', 0, 1);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 5, $data['resumo_profissional'] ?? 'Resumo não encontrado', 0, 'J');

    // Experiência
    $pdf->SetY($pdf->GetY() + 10);
    $pdf->SetX(75);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'EXPERIÊNCIA PROFISSIONAL', 0, 1);
    
    foreach ($data['experiencia'] ?? [] as $exp) {
        $pdf->SetX(75);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, $exp['cargo'] . ' | ' . $exp['empresa'], 0, 1);
        $pdf->SetX(75);
        $pdf->SetFont('helvetica', 'I', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 6, $exp['periodo'], 0, 1);
        $pdf->SetX(75);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 5, "- " . $exp['descricao'], 0, 'L');
        $pdf->Ln(5); // Espaço
    }
    
    // Educação
    $pdf->SetY($pdf->GetY() + 5);
    $pdf->SetX(75);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'EDUCAÇÃO', 0, 1);
    
    foreach ($data['educacao'] ?? [] as $edu) {
        $pdf->SetX(75);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, $edu['curso'], 0, 1);
        $pdf->SetX(75);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $edu['instituicao'], 0, 1);
        $pdf->SetX(75);
        $pdf->SetFont('helvetica', 'I', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 6, $edu['periodo'], 0, 1);
        $pdf->Ln(5);
    }


    return $pdf;
}
?>

