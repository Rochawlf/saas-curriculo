<?php
function create_template_criativo($data)
{
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(65, 15, 15); // margem esquerda ajustada para caber a barra lateral
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();

    // --- CORES ---
    $azul = [10, 50, 90];
    $cinza_texto = [40, 40, 40];

    // --- LATERAL AZUL ---
    $pdf->SetFillColor($azul[0], $azul[1], $azul[2]);
    $pdf->Rect(0, 0, 60, 297, 'F');

    // --- FOTO / INICIAL ---
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Circle(30, 30, 18, 0, 360, 'F');
    $pdf->SetFont('helvetica', 'B', 26);
    $pdf->SetTextColor($azul[0], $azul[1], $azul[2]);
    $inicial = mb_strtoupper(mb_substr($data['nome_completo'] ?? '?', 0, 1, 'UTF-8'));
    $pdf->Text(25, 22, $inicial);

    // --- DADOS LATERAIS ---
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetXY(8, 60);
    $pdf->MultiCell(50, 8, $data['nome_completo'] ?? 'Nome não informado', 0, 'C');

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetXY(8, 75);
    $pdf->MultiCell(50, 6, $data['cargo'] ?? '', 0, 'C');

    // Linha decorativa
    $pdf->SetDrawColor(255, 255, 255);
    $pdf->Line(10, 90, 50, 90);

    // --- CONTATO ---
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetXY(10, 95);
    $pdf->Cell(50, 6, 'CONTATO', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(240, 240, 240);
    $pdf->SetXY(10, 103);
    $contatoStr = implode("\n", array_filter([
        $data['contato']['email'] ?? '',
        $data['contato']['telefone'] ?? '',
        $data['contato']['linkedin'] ?? '',
        $data['contato']['localizacao'] ?? ''
    ]));
    $pdf->MultiCell(50, 5, $contatoStr, 0, 'C');

    // --- HABILIDADES ---
    if (!empty($data['habilidades'])) {
        $pdf->SetXY(10, 145);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(50, 8, 'COMPETÊNCIAS', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(230, 230, 230);
        $pdf->MultiCell(50, 5, implode("\n", $data['habilidades']), 0, 'C');
    }

    // --- CONTEÚDO PRINCIPAL ---
    $pdf->SetY(20);
    $pdf->SetTextColor($cinza_texto[0], $cinza_texto[1], $cinza_texto[2]);

    $drawSection = function ($title, $content, $pdf) use ($azul) {
        if (empty($content)) return;

        $pdf->Ln(4);
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->SetTextColor($azul[0], $azul[1], $azul[2]);
        $pdf->Cell(0, 8, mb_strtoupper($title, 'UTF-8'), 0, 1, 'L');
        $pdf->SetLineStyle(['width' => 0.3, 'color' => [$azul[0], $azul[1], $azul[2]]]);
        $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + 30, $pdf->GetY());
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(40, 40, 40);

        if (is_array($content)) {
            foreach ($content as $item) {
                $titulo = $item['cargo'] ?? $item['curso'] ?? '';
                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->Cell(0, 6, $titulo, 0, 1, 'L');
                $pdf->SetFont('helvetica', '', 9);
                $pdf->SetTextColor(90, 90, 90);
                $pdf->Cell(0, 5, ($item['empresa'] ?? $item['instituicao'] ?? '') . ' | ' . ($item['periodo'] ?? ''), 0, 1, 'L');
                if (!empty($item['descricao'])) {
                    $pdf->SetTextColor(40, 40, 40);
                    $pdf->MultiCell(0, 5, "• " . str_replace("\n", "\n• ", trim($item['descricao'])), 0, 'J');
                }
                $pdf->Ln(2);
            }
        } else {
            $pdf->MultiCell(0, 5, trim($content), 0, 'J');
            $pdf->Ln(4);
        }
    };

    $drawSection('SOBRE MIM', $data['resumo_profissional'] ?? '', $pdf);
    $drawSection('EXPERIÊNCIA', $data['experiencia'] ?? [], $pdf);
    $drawSection('FORMAÇÃO', $data['educacao'] ?? [], $pdf);

    // --- RODAPÉ ---
    $pdf->SetY(-15);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor(130, 130, 130);
    $pdf->Cell(0, 10, 'Gerado automaticamente por SaasCurrículo', 0, 0, 'C');

    return $pdf;
}
?>
