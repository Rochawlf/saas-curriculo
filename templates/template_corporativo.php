<?php
/**
 * Cria um currículo em PDF com um design corporativo e elegante.
 *
 * @param array $data Os dados estruturados do currículo.
 * @return TCPDF A instância do objeto PDF pronta para ser gerada.
 */
function create_template_corporativo($data) {
    // Cria uma nova instância do TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configurações do documento
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(20, 20, 20);
    $pdf->SetAutoPageBreak(TRUE, 20);
    $pdf->AddPage();
    $pdf->SetTextColor(34, 34, 34); // Um preto mais suave que o 100%

    // --- CABEÇALHO ---
    $pdf->SetFont('times', 'B', 30);
    $pdf->Cell(0, 16, $data['nome_completo'] ?? 'Nome não encontrado', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $contatoLinha = implode('  •  ', array_filter([
        $data['contato']['localizacao'] ?? null,
        $data['contato']['telefone'] ?? null,
        $data['contato']['email'] ?? null,
        $data['contato']['linkedin'] ?? null,
    ]));
    $pdf->Cell(0, 8, $contatoLinha, 0, 1, 'C');
    $pdf->Ln(5);

    // Linha divisória dupla, um detalhe de design elegante
    $pdf->SetLineStyle(array('width' => 0.3, 'color' => array(180, 180, 180)));
    $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
    $pdf->SetY($pdf->GetY() + 0.8);
    $pdf->SetLineStyle(array('width' => 0.1, 'color' => array(180, 180, 180)));
    $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
    $pdf->Ln(8);

    // Restaura a cor do texto principal
    $pdf->SetTextColor(34, 34, 34);

    /**
     * Função interna para desenhar uma secção com título à esquerda e conteúdo à direita.
     */
    $drawSectionTwoColumns = function($title, $content, $pdf) {
        if (!empty($content)) {
            // Guarda a posição Y atual
            $startY = $pdf->GetY();

            // Coluna da Esquerda (Título)
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->MultiCell(40, 10, mb_strtoupper($title, 'UTF-8'), 0, 'R', false, 1, 20, $startY);

            // Coluna da Direita (Conteúdo)
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(34, 34, 34);

            if (is_array($content)) { // Para experiência e educação
                foreach ($content as $item) {
                    $pdf->SetFont('helvetica', 'B', 10);
                    $pdf->MultiCell(130, 6, ($item['cargo'] ?? $item['curso'] ?? 'Cargo/Curso não informado'), 0, 'L', false, 1, 65, $pdf->GetY() - 10); // Usa MultiCell para evitar sobreposição

                    $pdf->SetFont('helvetica', '', 9);
                    $pdf->SetTextColor(80, 80, 80);
                    // CORREÇÃO: Adicionado '?? '' para evitar erro se 'periodo' não existir
                    $empresaInstituicao = ($item['empresa'] ?? $item['instituicao'] ?? '');
                    $periodo = $item['periodo'] ?? '';
                    $pdf->MultiCell(130, 6, $empresaInstituicao . ' | ' . $periodo, 0, 'L', false, 1, 65, $pdf->GetY());

                    $pdf->SetTextColor(34, 34, 34);
                    // CORREÇÃO: A verificação !empty já é segura, mas usamos '??' por consistência
                    if (!empty($item['descricao'])) {
                        $pdf->SetFont('helvetica', '', 10);
                        $pdf->MultiCell(130, 5, $item['descricao'] ?? '', 0, 'J', false, 1, 65, $pdf->GetY());
                    }
                    $pdf->Ln(4);
                }
            } else { // Para resumo
                $pdf->MultiCell(130, 5, $content, 0, 'J', false, 1, 65, $startY);
            }
            
            // Determina o Y final e adiciona um espaçamento
            $endY = $pdf->GetY();
            $pdf->SetY($endY + 6);
        }
    };

    // Desenha as secções principais
    $drawSectionTwoColumns('Resumo', $data['resumo_profissional'] ?? '', $pdf);
    $drawSectionTwoColumns('Experiência', $data['experiencia'] ?? [], $pdf);
    $drawSectionTwoColumns('Educação', $data['educacao'] ?? [], $pdf);

    // Secção de Habilidades
    if (!empty($data['habilidades'])) {
        $startY = $pdf->GetY();
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->MultiCell(40, 10, 'HABILIDADES', 0, 'R', false, 1, 20, $startY);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(34, 34, 34);
        $habilidadesStr = implode('  •  ', $data['habilidades']);
        $pdf->MultiCell(130, 5, $habilidadesStr, 0, 'L', false, 1, 65, $startY);
    }
    
    return $pdf;
}
?>

