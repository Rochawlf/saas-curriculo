<?php
function create_template_moderno($data) {
    // Cria uma nova instância do TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // CONFIGURAÇÕES DE MARGEM E ESPAÇAMENTO
    $sidebarWidth = 65;
    $marginLeft = $sidebarWidth + 10;
    $marginRight = 15;
    $marginTop = 15;
    $marginBottom = 20;
    $contentWidth = 210 - $marginLeft - $marginRight;

    // Remove header e footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Configura margens e quebra de página
    $pdf->SetMargins($marginLeft, $marginTop, $marginRight);
    $pdf->SetAutoPageBreak(TRUE, $marginBottom);
    $pdf->AddPage();

    // --- CORES MODERNAS ---
    $azul_escuro = [13, 71, 161];    // Azul profissional
    $azul_medio = [21, 101, 192];    // Azul principal
    $azul_claro = [66, 165, 245];    // Azul de destaque
    $cinza_escuro = [45, 52, 54];    // Cinza escuro para sidebar
    $cinza_medio = [100, 100, 100];  // Cinza médio
    $cinza_claro = [150, 150, 150];  // Cinza claro
    $branco = [255, 255, 255];
    $preto = [30, 30, 30];

    // --- FUNÇÃO PARA VERIFICAR QUEBRA DE PÁGINA ---
    $checkPageBreak = function($neededHeight) use ($pdf, $marginBottom, $cinza_escuro, $sidebarWidth, $marginTop) {
        $currentY = $pdf->GetY();
        if (($currentY + $neededHeight) > (297 - $marginBottom)) {
            $pdf->AddPage();
            // Redesenha a sidebar na nova página
            $pdf->SetFillColor($cinza_escuro[0], $cinza_escuro[1], $cinza_escuro[2]);
            $pdf->Rect(0, 0, $sidebarWidth, 297, 'F');
            return $marginTop;
        }
        return $currentY;
    };

    // --- SIDEBAR MODERNA ---
    $pdf->SetFillColor($cinza_escuro[0], $cinza_escuro[1], $cinza_escuro[2]);
    $pdf->Rect(0, 0, $sidebarWidth, 297, 'F');

    // --- CONTEÚDO DA SIDEBAR (Texto Branco) ---
    $pdf->SetTextColor($branco[0], $branco[1], $branco[2]);
    $pdf->SetXY(10, 25);

    // Foto/Inicial Moderna
    $pdf->SetFillColor($azul_medio[0], $azul_medio[1], $azul_medio[2]);
    $pdf->Circle($sidebarWidth/2, 45, 22, 0, 360, 'F');
    $pdf->SetFont('helvetica', 'B', 28);
    $inicial = mb_strtoupper(mb_substr($data['nome_completo'] ?? '?', 0, 1, 'UTF-8'));
    $pdf->Text($sidebarWidth/2 - 5, 38, $inicial);

    // Nome na Sidebar
    $pdf->SetY(75);
    $pdf->SetFont('helvetica', 'B', 16);
    $nome = $data['nome_completo'] ?? 'Nome não encontrado';
    if (strlen($nome) > 25) {
        $nome = wordwrap($nome, 25, "\n", true);
    }
    $pdf->MultiCell($sidebarWidth - 20, 8, $nome, 0, 'C');

    // Cargo na Sidebar
    $pdf->SetFont('helvetica', 'I', 11);
    $pdf->SetTextColor($azul_claro[0], $azul_claro[1], $azul_claro[2]);
    $cargo = $data['cargo'] ?? 'Profissional';
    if (strlen($cargo) > 25) {
        $cargo = substr($cargo, 0, 22) . '...';
    }
    $pdf->MultiCell($sidebarWidth - 20, 6, $cargo, 0, 'C');

    // Linha decorativa
    $pdf->SetDrawColor($azul_claro[0], $azul_claro[1], $azul_claro[2]);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(15, $pdf->GetY() + 5, $sidebarWidth - 15, $pdf->GetY() + 5);
    $pdf->SetY($pdf->GetY() + 12);

    // Contato
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor($branco[0], $branco[1], $branco[2]);
    $pdf->Cell($sidebarWidth - 20, 8, 'CONTATO', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor($branco[0], $branco[1], $branco[2]);
    
    $contatoItems = array_filter([
        $data['contato']['email'] ?? '',
        $data['contato']['telefone'] ?? '',
        $data['contato']['linkedin'] ?? '',
        $data['contato']['localizacao'] ?? ''
    ]);
    
    foreach ($contatoItems as $item) {
        if (!empty($item)) {
            $texto = $item;
            if (strlen($texto) > 28) {
                $texto = wordwrap($texto, 28, "\n", true);
            }
            $pdf->MultiCell($sidebarWidth - 20, 4, $texto, 0, 'C');
            $pdf->SetX(10);
        }
    }

    // Habilidades
    $pdf->SetY($pdf->GetY() + 10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell($sidebarWidth - 20, 8, 'COMPETÊNCIAS', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetX(10);
    
    if (!empty($data['habilidades'])) {
        foreach ($data['habilidades'] as $habilidade) {
            $habilidade = trim($habilidade);
            if (strlen($habilidade) > 22) {
                $habilidade = substr($habilidade, 0, 19) . '...';
            }
            // Tag style para habilidades
            $pdf->SetFillColor($azul_medio[0], $azul_medio[1], $azul_medio[2]);
            $pdf->SetTextColor($branco[0], $branco[1], $branco[2]);
            $pdf->Cell($sidebarWidth - 20, 6, ' ' . $habilidade . ' ', 0, 1, 'C', true);
            $pdf->SetX(10);
        }
    }

    // Idiomas
    if (!empty($data['idiomas'])) {
        $pdf->SetY($pdf->GetY() + 8);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor($branco[0], $branco[1], $branco[2]);
        $pdf->Cell($sidebarWidth - 20, 8, 'IDIOMAS', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetX(10);
        
        foreach ($data['idiomas'] as $idioma) {
            $texto = "• {$idioma['idioma']} ({$idioma['nivel']})";
            if (strlen($texto) > 25) {
                $texto = substr($texto, 0, 22) . '...';
            }
            $pdf->MultiCell($sidebarWidth - 20, 4, $texto, 0, 'L');
            $pdf->SetX(10);
        }
    }

    // --- CONTEÚDO PRINCIPAL ---
    $pdf->SetTextColor($preto[0], $preto[1], $preto[2]);
    $pdf->SetY($marginTop);

    // Função para desenhar seções do conteúdo principal
    $drawContentSection = function($title, $content, $isArray = true) use ($pdf, $checkPageBreak, $contentWidth, $azul_medio, $preto, $cinza_medio, $cinza_claro) {
        $checkPageBreak(25);
        
        // Título da seção
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor($azul_medio[0], $azul_medio[1], $azul_medio[2]);
        $pdf->Cell(0, 12, mb_strtoupper($title, 'UTF-8'), 0, 1, 'L');
        
        // Linha decorativa
        $currentX = $pdf->GetX();
        $currentY = $pdf->GetY();
        $pdf->SetDrawColor($azul_medio[0], $azul_medio[1], $azul_medio[2]);
        $pdf->SetLineWidth(0.8);
        $pdf->Line($currentX, $currentY, $currentX + 40, $currentY);
        $pdf->Ln(10);

        $pdf->SetTextColor($preto[0], $preto[1], $preto[2]);

        if ($isArray && is_array($content)) {
            foreach ($content as $index => $item) {
                $checkPageBreak(20);

                // Título do item
                $pdf->SetFont('helvetica', 'B', 13);
                $titulo = $item['cargo'] ?? $item['curso'] ?? '';
                if (strlen($titulo) > 75) {
                    $titulo = substr($titulo, 0, 72) . '...';
                }
                $pdf->MultiCell($contentWidth, 7, $titulo, 0, 'L');
                
                // Informações secundárias
                $pdf->SetFont('helvetica', 'I', 11);
                $pdf->SetTextColor($cinza_medio[0], $cinza_medio[1], $cinza_medio[2]);
                
                $empresaInst = $item['empresa'] ?? $item['instituicao'] ?? '';
                $periodo = $item['periodo'] ?? '';
                $infoLine = $empresaInst . ($empresaInst && $periodo ? ' | ' : '') . $periodo;
                
                if (strlen($infoLine) > 75) {
                    $infoLine = substr($infoLine, 0, 72) . '...';
                }
                $pdf->MultiCell($contentWidth, 6, $infoLine, 0, 'L');
                
                // Descrição
                if (!empty($item['descricao'])) {
                    $pdf->SetTextColor($preto[0], $preto[1], $preto[2]);
                    $pdf->SetFont('helvetica', '', 11);
                    $descricao = trim($item['descricao']);
                    $descricao = wordwrap($descricao, 90, "\n", true);
                    $pdf->MultiCell($contentWidth, 6, '• ' . $descricao, 0, 'J');
                }
                
                $pdf->SetTextColor($preto[0], $preto[1], $preto[2]);
                
                // Espaço entre itens
                if ($index < count($content) - 1) {
                    $pdf->Ln(6);
                    $pdf->SetDrawColor($cinza_claro[0], $cinza_claro[1], $cinza_claro[2]);
                    $pdf->SetLineWidth(0.1);
                    $pdf->Line($currentX, $pdf->GetY(), $currentX + $contentWidth, $pdf->GetY());
                    $pdf->Ln(6);
                }
            }
        } else {
            // Conteúdo de texto simples
            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetTextColor($preto[0], $preto[1], $preto[2]);
            $conteudo = trim($content);
            $conteudo = wordwrap($conteudo, 95, "\n", true);
            $pdf->MultiCell($contentWidth, 6, $conteudo, 0, 'J');
        }
        
        $pdf->Ln(12);
    };

    // --- SEÇÕES PRINCIPAIS ---

    // Resumo Profissional
    if (!empty($data['resumo_profissional'])) {
        $drawContentSection('Perfil Profissional', $data['resumo_profissional'], false);
    }

    // Experiência Profissional
    if (!empty($data['experiencia'])) {
        $drawContentSection('Experiência', $data['experiencia'], true);
    }

    // Educação
    if (!empty($data['educacao'])) {
        $drawContentSection('Formação', $data['educacao'], true);
    }

    // --- RODAPÉ MODERNO ---
    $pdf->SetY(-15);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor($cinza_medio[0], $cinza_medio[1], $cinza_medio[2]);
    $pdf->Cell(0, 10, 'Currículo Moderno • ' . date('d/m/Y'), 0, 0, 'C');

    return $pdf;
}
?>