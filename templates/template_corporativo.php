<?php
/**
 * Cria um currículo em PDF com um design corporativo e elegante.
 * Versão melhorada com controle de margens e quebra de texto.
 *
 * @param array $data Os dados estruturados do currículo.
 * @return TCPDF A instância do objeto PDF pronta para ser gerada.
 */
function create_template_corporativo($data) {
    // Cria uma nova instância do TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // CONFIGURAÇÕES DE MARGEM E ESPAÇAMENTO
    $marginLeft = 20;
    $marginRight = 20;
    $marginTop = 20;
    $marginBottom = 20;
    $contentWidth = 170; // 210 - 20 - 20
    $leftColumnWidth = 40;
    $rightColumnWidth = 125;
    $lineHeight = 5;
    $sectionSpacing = 8;

    // Configurações do documento
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins($marginLeft, $marginTop, $marginRight);
    $pdf->SetAutoPageBreak(TRUE, $marginBottom);
    $pdf->AddPage();
    $pdf->SetTextColor(34, 34, 34);

    // --- FUNÇÃO PARA VERIFICAR QUEBRA DE PÁGINA ---
    $checkPageBreak = function($neededHeight) use ($pdf, $marginBottom) {
        $currentY = $pdf->GetY();
        if (($currentY + $neededHeight) > (297 - $marginBottom)) {
            $pdf->AddPage();
        }
        return $currentY;
    };

    // --- CABEÇALHO ---
    $pdf->SetFont('times', 'B', 30);
    $checkPageBreak(30);
    $pdf->Cell(0, 16, $data['nome_completo'] ?? 'Nome não encontrado', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $contatoLinha = implode('  •  ', array_filter([
        $data['contato']['localizacao'] ?? null,
        $data['contato']['telefone'] ?? null,
        $data['contato']['email'] ?? null,
        $data['contato']['linkedin'] ?? null,
    ]));
    
    // Verifica espaço para linha de contato
    $checkPageBreak(15);
    $pdf->Cell(0, 8, $contatoLinha, 0, 1, 'C');
    $pdf->Ln(5);

    // Linha divisória dupla
    $currentY = $pdf->GetY();
    $checkPageBreak(10);
    $pdf->SetLineStyle(array('width' => 0.3, 'color' => array(180, 180, 180)));
    $pdf->Line($marginLeft, $currentY, $marginLeft + $contentWidth, $currentY);
    $pdf->SetY($currentY + 0.8);
    $pdf->SetLineStyle(array('width' => 0.1, 'color' => array(180, 180, 180)));
    $pdf->Line($marginLeft, $pdf->GetY(), $marginLeft + $contentWidth, $pdf->GetY());
    $pdf->Ln(8);

    // Restaura a cor do texto principal
    $pdf->SetTextColor(34, 34, 34);

    /**
     * Função interna para desenhar uma secção com título à esquerda e conteúdo à direita.
     */
    $drawSectionTwoColumns = function($title, $content, $pdf) use ($checkPageBreak, $leftColumnWidth, $rightColumnWidth, $marginLeft, $lineHeight, $sectionSpacing) {
        if (empty($content)) return;

        // Verifica espaço mínimo para nova seção
        $checkPageBreak(20);

        // Guarda a posição Y inicial
        $startY = $pdf->GetY();

        // Coluna da Esquerda (Título)
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->MultiCell($leftColumnWidth, $lineHeight, mb_strtoupper($title, 'UTF-8'), 0, 'R', false, 1, $marginLeft, $startY);

        // Coluna da Direita (Conteúdo)
        $contentX = $marginLeft + $leftColumnWidth + 5;
        $pdf->SetTextColor(34, 34, 34);

        if (is_array($content)) {
            // Para experiência e educação
            $currentContentY = $startY;
            
            foreach ($content as $index => $item) {
                // Verifica espaço para novo item
                $checkPageBreak(25);
                
                // Cargo/Curso
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetXY($contentX, $currentContentY);
                $pdf->MultiCell($rightColumnWidth, $lineHeight, $item['cargo'] ?? $item['curso'] ?? 'Cargo/Curso não informado', 0, 'L');
                $currentContentY = $pdf->GetY();
                
                // Empresa/Instituição e Período
                $pdf->SetFont('helvetica', '', 9);
                $pdf->SetTextColor(80, 80, 80);
                $pdf->SetXY($contentX, $currentContentY);
                $empresaInstituicao = $item['empresa'] ?? $item['instituicao'] ?? '';
                $periodo = $item['periodo'] ?? '';
                $pdf->MultiCell($rightColumnWidth, $lineHeight, $empresaInstituicao . ($empresaInstituicao && $periodo ? ' | ' : '') . $periodo, 0, 'L');
                $currentContentY = $pdf->GetY();
                
                // Descrição (se existir)
                $pdf->SetTextColor(34, 34, 34);
                if (!empty($item['descricao'])) {
                    $pdf->SetFont('helvetica', '', 9);
                    $pdf->SetXY($contentX, $currentContentY);
                    $pdf->MultiCell($rightColumnWidth, $lineHeight, $item['descricao'], 0, 'J');
                    $currentContentY = $pdf->GetY();
                }
                
                // Espaço entre itens (exceto o último)
                if ($index < count($content) - 1) {
                    $currentContentY += 3;
                    $pdf->SetY($currentContentY);
                }
            }
            
            $finalY = $currentContentY;
            
        } else {
            // Para resumo (texto simples)
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetXY($contentX, $startY);
            $pdf->MultiCell($rightColumnWidth, $lineHeight, $content, 0, 'J');
            $finalY = $pdf->GetY();
        }
        
        // Define a posição Y final com espaçamento
        $pdf->SetY($finalY + $sectionSpacing);
    };

    // --- SEÇÕES PRINCIPAIS ---
    
    // Resumo Profissional
    if (!empty($data['resumo_profissional'])) {
        $drawSectionTwoColumns('Resumo', $data['resumo_profissional'], $pdf);
    }

    // Experiência Profissional
    if (!empty($data['experiencia'])) {
        $drawSectionTwoColumns('Experiência', $data['experiencia'], $pdf);
    }

    // Educação
    if (!empty($data['educacao'])) {
        $drawSectionTwoColumns('Educação', $data['educacao'], $pdf);
    }

    // --- SEÇÃO DE HABILIDADES ---
    if (!empty($data['habilidades'])) {
        $checkPageBreak(20);
        $startY = $pdf->GetY();
        
        // Título
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->MultiCell($leftColumnWidth, $lineHeight, 'HABILIDADES', 0, 'R', false, 1, $marginLeft, $startY);
        
        // Conteúdo
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(34, 34, 34);
        $habilidadesStr = implode('  •  ', $data['habilidades']);
        
        // Quebra automática de texto para habilidades
        $pdf->SetXY($marginLeft + $leftColumnWidth + 5, $startY);
        $pdf->MultiCell($rightColumnWidth, $lineHeight, $habilidadesStr, 0, 'L');
        
        $pdf->SetY($pdf->GetY() + $sectionSpacing);
    }

    // --- SEÇÃO DE IDIOMAS (NOVO) ---
    if (!empty($data['idiomas'])) {
        $checkPageBreak(20);
        $startY = $pdf->GetY();
        
        // Título
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->MultiCell($leftColumnWidth, $lineHeight, 'IDIOMAS', 0, 'R', false, 1, $marginLeft, $startY);
        
        // Conteúdo
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(34, 34, 34);
        
        $idiomasContent = '';
        foreach ($data['idiomas'] as $index => $idioma) {
            if ($index > 0) $idiomasContent .= '  •  ';
            $idiomasContent .= $idioma['idioma'] . ' (' . $idioma['nivel'] . ')';
        }
        
        $pdf->SetXY($marginLeft + $leftColumnWidth + 5, $startY);
        $pdf->MultiCell($rightColumnWidth, $lineHeight, $idiomasContent, 0, 'L');
        
        $pdf->SetY($pdf->GetY() + $sectionSpacing);
    }

    return $pdf;
}
?>