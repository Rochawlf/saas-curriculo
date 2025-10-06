<?php
function create_template_criativo($data)
{
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // CONFIGURAÇÕES DE MARGEM E ESPAÇAMENTO
    $sidebarWidth = 55;
    $marginLeft = $sidebarWidth + 5;
    $marginRight = 15;
    $marginTop = 15;
    $marginBottom = 20;
    $contentWidth = 210 - $marginLeft - $marginRight;
    
    $pdf->SetMargins($marginLeft, $marginTop, $marginRight);
    $pdf->SetAutoPageBreak(true, $marginBottom);
    $pdf->AddPage();

    // --- CORES MODERNAS ---
    $azul_escuro = [13, 71, 161];    // Azul profissional mais escuro
    $azul_medio = [21, 101, 192];    // Azul principal
    $azul_claro = [66, 165, 245];    // Azul de destaque
    $cinza_escuro = [33, 33, 33];    // Texto principal
    $cinza_medio = [97, 97, 97];     // Texto secundário
    $cinza_claro = [245, 245, 245];  // Fundo claro
    $branco = [255, 255, 255];

    // --- FUNÇÃO PARA VERIFICAR QUEBRA DE PÁGINA ---
    $checkPageBreak = function($neededHeight) use ($pdf, $marginBottom, $azul_escuro, $sidebarWidth, $marginTop) {
        $currentY = $pdf->GetY();
        if (($currentY + $neededHeight) > (297 - $marginBottom)) {
            $pdf->AddPage();
            // Redesenha a sidebar na nova página
            $pdf->SetFillColor($azul_escuro[0], $azul_escuro[1], $azul_escuro[2]);
            $pdf->Rect(0, 0, $sidebarWidth, 297, 'F');
            return $marginTop;
        }
        return $currentY;
    };

    // --- LATERAL AZUL MODERNA ---
    $pdf->SetFillColor($azul_escuro[0], $azul_escuro[1], $azul_escuro[2]);
    $pdf->Rect(0, 0, $sidebarWidth, 297, 'F');

    // --- AVATAR/CÍRCULO MODERNO ---
    $pdf->SetFillColor($azul_medio[0], $azul_medio[1], $azul_medio[2]);
    $pdf->Circle($sidebarWidth/2, 25, 20, 0, 360, 'F');
    $pdf->SetFont('helvetica', 'B', 24);
    $pdf->SetTextColor($branco[0], $branco[1], $branco[2]);
    $inicial = mb_strtoupper(mb_substr($data['nome_completo'] ?? '?', 0, 1, 'UTF-8'));
    $pdf->Text($sidebarWidth/2 - 4, 19, $inicial);

    // --- NOME E CARGO ---
    $pdf->SetTextColor($branco[0], $branco[1], $branco[2]);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetXY(5, 50);
    $nome = $data['nome_completo'] ?? 'Nome não informado';
    if (strlen($nome) > 25) {
        $nome = wordwrap($nome, 25, "\n", true);
    }
    $pdf->MultiCell($sidebarWidth - 10, 6, $nome, 0, 'C');

    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->SetTextColor($azul_claro[0], $azul_claro[1], $azul_claro[2]);
    $pdf->SetXY(5, $pdf->GetY() + 1);
    $cargo = $data['cargo'] ?? 'Profissional';
    if (strlen($cargo) > 25) {
        $cargo = substr($cargo, 0, 22) . '...';
    }
    $pdf->MultiCell($sidebarWidth - 10, 5, $cargo, 0, 'C');

    // Linha decorativa moderna
    $pdf->SetDrawColor($azul_claro[0], $azul_claro[1], $azul_claro[2]);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(10, 70, $sidebarWidth - 10, 70);

    // --- CONTATO ---
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor($branco[0], $branco[1], $branco[2]);
    $pdf->SetXY(5, 77);
    $pdf->Cell($sidebarWidth - 10, 5, 'CONTATO', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor($cinza_claro[0], $cinza_claro[1], $cinza_claro[2]);
    $pdf->SetXY(5, 83);
    
    $contatoItems = array_filter([
        $data['contato']['email'] ?? '',
        $data['contato']['telefone'] ?? '',
        $data['contato']['linkedin'] ?? '',
        $data['contato']['localizacao'] ?? ''
    ]);
    
    foreach ($contatoItems as $item) {
        if (!empty($item)) {
            $texto = $item;
            // Quebra URLs longas e textos
            if (strlen($texto) > 25) {
                $texto = wordwrap($texto, 25, "\n", true);
            }
            $pdf->MultiCell($sidebarWidth - 10, 4, $texto, 0, 'C');
            $pdf->SetX(5);
        }
    }

    // --- HABILIDADES ---
    $skillsY = $pdf->GetY() + 8;
    if (!empty($data['habilidades'])) {
        $pdf->SetXY(5, $skillsY);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor($branco[0], $branco[1], $branco[2]);
        $pdf->Cell($sidebarWidth - 10, 5, 'COMPETÊNCIAS', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor($cinza_claro[0], $cinza_claro[1], $cinza_claro[2]);
        $pdf->SetXY(5, $pdf->GetY() + 2);
        
        foreach ($data['habilidades'] as $habilidade) {
            $habilidade = trim($habilidade);
            if (strlen($habilidade) > 20) {
                $habilidade = substr($habilidade, 0, 17) . '...';
            }
            // Tag style para habilidades
            $pdf->SetFillColor($azul_medio[0], $azul_medio[1], $azul_medio[2]);
            $pdf->SetTextColor($branco[0], $branco[1], $branco[2]);
            $pdf->Cell($sidebarWidth - 10, 5, ' ' . $habilidade . ' ', 0, 1, 'C', true);
            $pdf->SetX(5);
            $pdf->SetTextColor($cinza_claro[0], $cinza_claro[1], $cinza_claro[2]);
        }
    }

    // --- IDIOMAS ---
    $languagesY = $pdf->GetY() + 8;
    if (!empty($data['idiomas'])) {
        $pdf->SetXY(5, $languagesY);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor($branco[0], $branco[1], $branco[2]);
        $pdf->Cell($sidebarWidth - 10, 5, 'IDIOMAS', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor($cinza_claro[0], $cinza_claro[1], $cinza_claro[2]);
        $pdf->SetXY(5, $pdf->GetY() + 2);
        
        foreach ($data['idiomas'] as $idioma) {
            $texto = "{$idioma['idioma']} ({$idioma['nivel']})";
            if (strlen($texto) > 25) {
                $texto = substr($texto, 0, 22) . '...';
            }
            $pdf->Cell(3, 4, '•', 0, 0, 'L');
            $pdf->MultiCell($sidebarWidth - 13, 4, $texto, 0, 'L');
            $pdf->SetX(5);
        }
    }

    // --- CONTEÚDO PRINCIPAL ---
    $pdf->SetY($marginTop);
    $pdf->SetTextColor($cinza_escuro[0], $cinza_escuro[1], $cinza_escuro[2]);

    $drawSection = function ($title, $content, $pdf) use ($azul_medio, $cinza_escuro, $cinza_medio, $checkPageBreak, $contentWidth) {
        if (empty($content)) return;

        // Verifica espaço para nova seção
        $checkPageBreak(25);
        $pdf->Ln(6);

        // Título da seção com design moderno
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->SetTextColor($azul_medio[0], $azul_medio[1], $azul_medio[2]);
        $pdf->Cell(0, 7, mb_strtoupper($title, 'UTF-8'), 0, 1, 'L');
        
        // Linha decorativa sob o título
        $pdf->SetDrawColor($azul_medio[0], $azul_medio[1], $azul_medio[2]);
        $pdf->SetLineWidth(0.6);
        $currentX = $pdf->GetX();
        $currentY = $pdf->GetY();
        $pdf->Line($currentX, $currentY, $currentX + 30, $currentY);
        $pdf->Ln(5);

        $pdf->SetTextColor($cinza_escuro[0], $cinza_escuro[1], $cinza_escuro[2]);

        if (is_array($content)) {
            foreach ($content as $index => $item) {
                $checkPageBreak(25);
                
                // Título do item (cargo/curso)
                $pdf->SetFont('helvetica', 'B', 10);
                $titulo = $item['cargo'] ?? $item['curso'] ?? '';
                // Limita título muito longo
                if (strlen($titulo) > 80) {
                    $titulo = substr($titulo, 0, 77) . '...';
                }
                $pdf->MultiCell($contentWidth, 5, $titulo, 0, 'L');
                
                // Empresa/Instituição e período
                $pdf->SetFont('helvetica', 'I', 9);
                $pdf->SetTextColor($cinza_medio[0], $cinza_medio[1], $cinza_medio[2]);
                $empresaInst = $item['empresa'] ?? $item['instituicao'] ?? '';
                $periodo = $item['periodo'] ?? '';
                $infoLine = $empresaInst . ($empresaInst && $periodo ? ' | ' : '') . $periodo;
                // Limita linha de informação
                if (strlen($infoLine) > 80) {
                    $infoLine = substr($infoLine, 0, 77) . '...';
                }
                $pdf->MultiCell($contentWidth, 4, $infoLine, 0, 'L');
                
                // Descrição (se existir)
                if (!empty($item['descricao'])) {
                    $pdf->SetTextColor($cinza_escuro[0], $cinza_escuro[1], $cinza_escuro[2]);
                    $pdf->SetFont('helvetica', '', 9);
                    $descricao = trim($item['descricao']);
                    // Quebra a descrição em linhas menores
                    $descricao = wordwrap($descricao, 90, "\n", true);
                    $pdf->MultiCell($contentWidth, 4, '• ' . $descricao, 0, 'J');
                }
                
                // Espaço entre itens (menor)
                if ($index < count($content) - 1) {
                    $pdf->Ln(2);
                }
            }
        } else {
            // Conteúdo de texto simples (resumo)
            $pdf->SetFont('helvetica', '', 9);
            $conteudo = trim($content);
            // Quebra o texto para caber na largura
            $conteudo = wordwrap($conteudo, 100, "\n", true);
            $pdf->MultiCell($contentWidth, 4, $conteudo, 0, 'J');
        }
        
        $pdf->Ln(3);
    };

    // --- SEÇÕES PRINCIPAIS ---
    $drawSection('Perfil Profissional', $data['resumo_profissional'] ?? '', $pdf);
    $drawSection('Experiência Profissional', $data['experiencia'] ?? [], $pdf);
    $drawSection('Formação Acadêmica', $data['educacao'] ?? [], $pdf);

    // --- RODAPÉ MODERNO ---
    $pdf->SetY(-12);
    $pdf->SetFont('helvetica', 'I', 7);
    $pdf->SetTextColor($cinza_medio[0], $cinza_medio[1], $cinza_medio[2]);
    $pdf->Cell(0, 8, 'Gerado por Currículo Pro • ' . date('d/m/Y'), 0, 0, 'C');

    return $pdf;
}
?>