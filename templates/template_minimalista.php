<?php
/**
 * Cria um currículo em PDF com um design minimalista e moderno.
 * Versão corrigida com controle de texto e função checkPageBreak funcionando.
 *
 * @param array $data Os dados estruturados do currículo.
 * @return TCPDF A instância do objeto PDF pronta para ser gerada.
 */
function create_template_minimalista($data) {
    // Cria uma nova instância do TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // CONFIGURAÇÕES DE MARGEM E ESPAÇAMENTO
    $marginLeft = 20;
    $marginRight = 20;
    $marginTop = 25;
    $marginBottom = 25;
    $contentWidth = 170; // 210 - 20 - 20
    $lineHeight = 5;
    $sectionSpacing = 8;

    // Configurações do documento
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins($marginLeft, $marginTop, $marginRight);
    $pdf->SetAutoPageBreak(TRUE, $marginBottom);
    $pdf->AddPage();

    // --- CORES MODERNAS ---
    $preto = [30, 30, 30];
    $cinza_escuro = [80, 80, 80];
    $cinza_medio = [120, 120, 120];
    $cinza_claro = [200, 200, 200];
    $azul = [59, 130, 246]; // Azul moderno

    // --- FUNÇÃO PARA VERIFICAR QUEBRA DE PÁGINA (CORRIGIDA) ---
    $checkPageBreak = function($neededHeight) use ($pdf, $marginBottom, $marginTop) {
        $currentY = $pdf->GetY(); // CORREÇÃO: GetY() em vez de SetY()
        if (($currentY + $neededHeight) > (297 - $marginBottom)) {
            $pdf->AddPage(); // CORREÇÃO: AddPage() em vez de AdHpage()
            return $marginTop;
        }
        return $currentY;
    };

    // --- CABEÇALHO MODERNO ---
    $pdf->SetFont('helvetica', 'B', 32);
    $pdf->SetTextColor($preto[0], $preto[1], $preto[2]);
    
    // Verifica espaço para o cabeçalho
    $checkPageBreak(40);
    $pdf->Cell(0, 12, $data['nome_completo'] ?? 'Nome não encontrado', 0, 1, 'C');
    
    // Linha decorativa sob o nome
    $pdf->SetDrawColor($azul[0], $azul[1], $azul[2]);
    $pdf->SetLineWidth(1);
    $pdf->Line($marginLeft + 40, $pdf->GetY(), $marginLeft + $contentWidth - 40, $pdf->GetY());
    $pdf->Ln(6);

    // Cargo/Profissão
    if (!empty($data['cargo'])) {
        $pdf->SetFont('helvetica', 'I', 14);
        $pdf->SetTextColor($azul[0], $azul[1], $azul[2]);
        $pdf->Cell(0, 8, $data['cargo'], 0, 1, 'C');
        $pdf->Ln(4);
    }

    // Contactos em layout moderno
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor($cinza_escuro[0], $cinza_escuro[1], $cinza_escuro[2]);
    
    $contatoItems = array_filter([
        $data['contato']['email'] ?? null,
        $data['contato']['telefone'] ?? null,
        $data['contato']['linkedin'] ?? null,
        $data['contato']['localizacao'] ?? null
    ]);

    if (!empty($contatoItems)) {
        $contatoLinha = implode('  •  ', $contatoItems);
        // Quebra automática para contactos longos
        $contatoLinha = wordwrap($contatoLinha, 80, "\n", true);
        $pdf->MultiCell(0, 6, $contatoLinha, 0, 'C');
    }

    $pdf->Ln(12);

    // --- FUNÇÃO PARA DESENHAR SEÇÕES ---
    $drawSection = function($title, $content, $pdf) use ($checkPageBreak, $contentWidth, $lineHeight, $sectionSpacing, $azul, $preto, $cinza_medio, $cinza_escuro) {
        if (empty($content)) return;

        // Verifica espaço para nova seção
        $checkPageBreak(25);

        // Título da seção com design moderno
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor($azul[0], $azul[1], $azul[2]);
        $pdf->Cell(0, 10, mb_strtoupper($title, 'UTF-8'), 0, 1, 'L');
        
        // Linha decorativa moderna
        $currentX = $pdf->GetX();
        $currentY = $pdf->GetY();
        $pdf->SetDrawColor($azul[0], $azul[1], $azul[2]);
        $pdf->SetLineWidth(0.8);
        $pdf->Line($currentX, $currentY, $currentX + 35, $currentY);
        $pdf->Ln(8);

        $pdf->SetTextColor($preto[0], $preto[1], $preto[2]);

        if (is_array($content)) {
            // Para experiência e educação
            foreach ($content as $index => $item) {
                $checkPageBreak(20);

                // Título principal (Cargo/Curso)
                $pdf->SetFont('helvetica', 'B', 12);
                $titulo = $item['cargo'] ?? $item['curso'] ?? '';
                // Limita título muito longo
                if (strlen($titulo) > 70) {
                    $titulo = substr($titulo, 0, 67) . '...';
                }
                $pdf->MultiCell($contentWidth, 6, $titulo, 0, 'L');
                
                // Empresa/Instituição e período
                $pdf->SetFont('helvetica', 'I', 10);
                $pdf->SetTextColor($cinza_medio[0], $cinza_medio[1], $cinza_medio[2]);
                
                $empresaInst = $item['empresa'] ?? $item['instituicao'] ?? '';
                $periodo = $item['periodo'] ?? '';
                $infoLine = $empresaInst . ($empresaInst && $periodo ? ' | ' : '') . $periodo;
                
                if (strlen($infoLine) > 70) {
                    $infoLine = substr($infoLine, 0, 67) . '...';
                }
                $pdf->MultiCell($contentWidth, 5, $infoLine, 0, 'L');
                
                // Descrição (se existir)
                if (!empty($item['descricao'])) {
                    $pdf->SetTextColor($cinza_escuro[0], $cinza_escuro[1], $cinza_escuro[2]);
                    $pdf->SetFont('helvetica', '', 10);
                    $descricao = trim($item['descricao']);
                    $descricao = wordwrap($descricao, 85, "\n", true);
                    $pdf->MultiCell($contentWidth, 5, '• ' . $descricao, 0, 'J');
                }
                
                $pdf->SetTextColor($preto[0], $preto[1], $preto[2]);
                
                // Espaço entre itens (exceto o último)
                if ($index < count($content) - 1) {
                    $pdf->Ln(4);
                    // Linha divisória sutil entre itens
                    $pdf->SetDrawColor($cinza_claro[0], $cinza_claro[1], $cinza_claro[2]);
                    $pdf->SetLineWidth(0.1);
                    $pdf->Line($currentX, $pdf->GetY(), $currentX + $contentWidth, $pdf->GetY());
                    $pdf->Ln(4);
                }
            }
        } else {
            // Para resumo (texto simples)
            $pdf->SetFont('helvetica', '', 11);
            $pdf->SetTextColor($cinza_escuro[0], $cinza_escuro[1], $cinza_escuro[2]);
            $conteudo = trim($content);
            $conteudo = wordwrap($conteudo, 90, "\n", true);
            $pdf->MultiCell($contentWidth, 6, $conteudo, 0, 'J');
        }
        
        $pdf->Ln($sectionSpacing);
    };

    // --- SEÇÕES PRINCIPAIS ---
    
    // Resumo Profissional
    if (!empty($data['resumo_profissional'])) {
        $drawSection('Perfil Profissional', $data['resumo_profissional'], $pdf);
    }

    // Experiência Profissional
    if (!empty($data['experiencia'])) {
        $drawSection('Experiência Profissional', $data['experiencia'], $pdf);
    }

    // Educação
    if (!empty($data['educacao'])) {
        $drawSection('Formação Acadêmica', $data['educacao'], $pdf);
    }

    // --- SEÇÃO DE HABILIDADES MODERNA ---
    if (!empty($data['habilidades'])) {
        $checkPageBreak(25);
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor($azul[0], $azul[1], $azul[2]);
        $pdf->Cell(0, 10, 'HABILIDADES', 0, 1, 'L');
        
        // Linha decorativa
        $currentX = $pdf->GetX();
        $currentY = $pdf->GetY();
        $pdf->SetDrawColor($azul[0], $azul[1], $azul[2]);
        $pdf->SetLineWidth(0.8);
        $pdf->Line($currentX, $currentY, $currentX + 35, $currentY);
        $pdf->Ln(8);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor($cinza_escuro[0], $cinza_escuro[1], $cinza_escuro[2]);
        
        // Layout moderno para habilidades - tags
        $skillsPerLine = 3;
        $skillCount = count($data['habilidades']);
        $currentLineSkills = 0;
        
        foreach ($data['habilidades'] as $index => $habilidade) {
            if ($currentLineSkills == 0) {
                $pdf->SetX($marginLeft);
            }
            
            // Tag style para habilidades
            $pdf->SetFillColor($azul[0], $azul[1], $azul[2]);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', '', 9);
            
            $skillText = ' ' . trim($habilidade) . ' ';
            $skillWidth = min(strlen($skillText) * 2.5, 40); // Largura dinâmica
            
            $pdf->Cell($skillWidth, 6, $skillText, 0, 0, 'C', true);
            
            $currentLineSkills++;
            
            // Espaço entre tags ou quebra de linha
            if ($currentLineSkills >= $skillsPerLine || $index == $skillCount - 1) {
                $pdf->Ln(8);
                $currentLineSkills = 0;
            } else {
                $pdf->Cell(5, 6, ''); // Espaço entre tags
            }
        }
        
        $pdf->Ln($sectionSpacing);
    }

    // --- SEÇÃO DE IDIOMAS (CORRIGIDA) ---
    if (!empty($data['idiomas'])) {
        $checkPageBreak(25);
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor($azul[0], $azul[1], $azul[2]);
        $pdf->Cell(0, 10, 'IDIOMAS', 0, 1, 'L');
        
        // Linha decorativa
        $currentX = $pdf->GetX();
        $currentY = $pdf->GetY();
        $pdf->SetDrawColor($azul[0], $azul[1], $azul[2]);
        $pdf->SetLineWidth(0.8);
        $pdf->Line($currentX, $currentY, $currentX + 25, $currentY);
        $pdf->Ln(8);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor($cinza_escuro[0], $cinza_escuro[1], $cinza_escuro[2]);
        
        foreach ($data['idiomas'] as $idioma) {
            // CORREÇÃO: Limita o texto dos idiomas para não ultrapassar
            $textoIdioma = "• {$idioma['idioma']} - {$idioma['nivel']}";
            if (strlen($textoIdioma) > 60) {
                $textoIdioma = substr($textoIdioma, 0, 57) . '...';
            }
            $pdf->MultiCell($contentWidth, 6, $textoIdioma, 0, 'L');
        }
    }

    // --- RODAPÉ DISCRETO ---
    $pdf->SetY(-15);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor($cinza_medio[0], $cinza_medio[1], $cinza_medio[2]);
    $pdf->Cell(0, 10, 'Currículo gerado em ' . date('d/m/Y'), 0, 0, 'C');

    return $pdf;
}
?>