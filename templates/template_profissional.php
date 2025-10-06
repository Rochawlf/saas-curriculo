<?php
/**
 * Cria um currículo em PDF com um design profissional e estruturado.
 * Versão moderna com controle de margens e layout responsivo.
 *
 * @param array $data Os dados estruturados do currículo.
 * @return TCPDF A instância do objeto PDF pronta para ser gerada.
 */
function create_template_profissional($data) {
    // Cria uma nova instância do TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // CONFIGURAÇÕES DE MARGEM E ESPAÇAMENTO
    $marginLeft = 20;
    $marginRight = 20;
    $marginTop = 25;
    $marginBottom = 25;
    $contentWidth = 170; // 210 - 20 - 20
    $lineHeight = 5;
    $sectionSpacing = 10;

    // Configurações do documento
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins($marginLeft, $marginTop, $marginRight);
    $pdf->SetAutoPageBreak(TRUE, $marginBottom);
    $pdf->AddPage();

    // --- CORES PROFISSIONAIS ---
    $azul_escuro = [13, 71, 161];    // Azul corporativo
    $azul_medio = [21, 101, 192];    // Azul principal
    $cinza_escuro = [45, 52, 54];    // Cinza escuro
    $cinza_medio = [100, 100, 100];  // Cinza médio
    $cinza_claro = [150, 150, 150];  // Cinza claro
    $preto = [30, 30, 30];           // Preto suave

    // --- FUNÇÃO PARA VERIFICAR QUEBRA DE PÁGINA ---
    $checkPageBreak = function($neededHeight) use ($pdf, $marginBottom, $marginTop) {
        $currentY = $pdf->GetY();
        if (($currentY + $neededHeight) > (297 - $marginBottom)) {
            $pdf->AddPage();
            return $marginTop;
        }
        return $currentY;
    };

    // --- CABEÇALHO PROFISSIONAL ---
    $checkPageBreak(40);
    
    // Nome
    $pdf->SetTextColor($preto[0], $preto[1], $preto[2]);
    $pdf->SetFont('helvetica', 'B', 28);
    $nome = $data['nome_completo'] ?? 'Nome não encontrado';
    if (strlen($nome) > 40) {
        $nome = substr($nome, 0, 37) . '...';
    }
    $pdf->Cell(0, 12, $nome, 0, 1, 'L');
    
    // Cargo
    $pdf->SetFont('helvetica', '', 14);
    $pdf->SetTextColor($azul_medio[0], $azul_medio[1], $azul_medio[2]);
    $cargo = $data['cargo'] ?? 'Profissional';
    if (strlen($cargo) > 50) {
        $cargo = substr($cargo, 0, 47) . '...';
    }
    $pdf->Cell(0, 8, $cargo, 0, 1, 'L');
    $pdf->Ln(2);

    // Linha de contacto moderna
    $pdf->SetFont('helvetica', '', 10);
    $contatoItems = array_filter([
        $data['contato']['email'] ?? null,
        $data['contato']['telefone'] ?? null,
        $data['contato']['linkedin'] ?? null,
        $data['contato']['localizacao'] ?? null
    ]);
    
    if (!empty($contatoItems)) {
        $contatoLinha = implode('  •  ', $contatoItems);
        $contatoLinha = wordwrap($contatoLinha, 80, "\n", true);
        $pdf->SetTextColor($cinza_medio[0], $cinza_medio[1], $cinza_medio[2]);
        $pdf->MultiCell(0, 6, $contatoLinha, 0, 'L');
    }

    // Linha divisória profissional
    $pdf->SetDrawColor($azul_medio[0], $azul_medio[1], $azul_medio[2]);
    $pdf->SetLineWidth(0.8);
    $pdf->Line($marginLeft, $pdf->GetY() + 4, $marginLeft + $contentWidth, $pdf->GetY() + 4);
    $pdf->Ln(12);

    // Restaura a cor do texto principal
    $pdf->SetTextColor($preto[0], $preto[1], $preto[2]);

    /**
     * Função interna para desenhar uma secção do currículo.
     */
    $drawSection = function($title, $content, $pdf) use ($checkPageBreak, $contentWidth, $azul_medio, $cinza_escuro, $cinza_medio, $preto) {
        if (empty($content)) return;

        $checkPageBreak(25);

        // Título da seção
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor($azul_medio[0], $azul_medio[1], $azul_medio[2]);
        $pdf->Cell(0, 10, mb_strtoupper($title, 'UTF-8'), 0, 1, 'L');
        
        // Linha decorativa
        $currentX = $pdf->GetX();
        $currentY = $pdf->GetY();
        $pdf->SetDrawColor($azul_medio[0], $azul_medio[1], $azul_medio[2]);
        $pdf->SetLineWidth(0.6);
        $pdf->Line($currentX, $currentY, $currentX + 35, $currentY);
        $pdf->Ln(8);

        $pdf->SetTextColor($preto[0], $preto[1], $preto[2]);

        if (is_array($content)) {
            // Para experiência e educação
            foreach ($content as $index => $item) {
                $checkPageBreak(25);

                // Container do item
                $itemStartY = $pdf->GetY();
                
                // Linha superior: Cargo/Curso + Período
                $pdf->SetFont('helvetica', 'B', 12);
                $titulo = $item['cargo'] ?? $item['curso'] ?? '';
                if (strlen($titulo) > 60) {
                    $titulo = substr($titulo, 0, 57) . '...';
                }
                
                // Primeiro escreve o título
                $pdf->Cell($contentWidth - 50, 7, $titulo, 0, 0, 'L');
                
                // Depois o período alinhado à direita
                $pdf->SetFont('helvetica', 'I', 10);
                $pdf->SetTextColor($cinza_medio[0], $cinza_medio[1], $cinza_medio[2]);
                $periodo = $item['periodo'] ?? '';
                if (strlen($periodo) > 20) {
                    $periodo = substr($periodo, 0, 17) . '...';
                }
                $pdf->Cell(50, 7, $periodo, 0, 1, 'R');
                
                // Empresa/Instituição
                $pdf->SetFont('helvetica', '', 11);
                $pdf->SetTextColor($cinza_escuro[0], $cinza_escuro[1], $cinza_escuro[2]);
                $empresaInst = $item['empresa'] ?? $item['instituicao'] ?? '';
                if (strlen($empresaInst) > 70) {
                    $empresaInst = substr($empresaInst, 0, 67) . '...';
                }
                $pdf->MultiCell($contentWidth, 6, $empresaInst, 0, 'L');
                
                // Descrição
                if (!empty($item['descricao'])) {
                    $pdf->SetTextColor($preto[0], $preto[1], $preto[2]);
                    $pdf->SetFont('helvetica', '', 10);
                    $descricao = trim($item['descricao']);
                    $descricao = wordwrap($descricao, 85, "\n", true);
                    
                    // Adiciona bullets manualmente
                    $linhas = explode("\n", $descricao);
                    foreach ($linhas as $linha) {
                        if (trim($linha) !== '') {
                            $pdf->MultiCell($contentWidth, 5, '• ' . trim($linha), 0, 'J');
                        }
                    }
                }
                
                // Espaço entre itens
                if ($index < count($content) - 1) {
                    $pdf->Ln(6);
                    // Linha divisória sutil
                    $pdf->SetDrawColor(220, 220, 220);
                    $pdf->SetLineWidth(0.1);
                    $pdf->Line($currentX, $pdf->GetY(), $currentX + $contentWidth, $pdf->GetY());
                    $pdf->Ln(6);
                }
                
                $pdf->SetTextColor($preto[0], $preto[1], $preto[2]);
            }
        } else {
            // Para resumo
            $pdf->SetFont('helvetica', '', 11);
            $pdf->SetTextColor($preto[0], $preto[1], $preto[2]);
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
        $checkPageBreak(30);
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor($azul_medio[0], $azul_medio[1], $azul_medio[2]);
        $pdf->Cell(0, 10, 'HABILIDADES E COMPETÊNCIAS', 0, 1, 'L');
        
        // Linha decorativa
        $currentX = $pdf->GetX();
        $currentY = $pdf->GetY();
        $pdf->SetDrawColor($azul_medio[0], $azul_medio[1], $azul_medio[2]);
        $pdf->SetLineWidth(0.6);
        $pdf->Line($currentX, $currentY, $currentX + 45, $currentY);
        $pdf->Ln(8);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor($preto[0], $preto[1], $preto[2]);

        // Layout em colunas para habilidades
        $colCount = 2;
        $colWidth = $contentWidth / $colCount;
        $skillsPerCol = ceil(count($data['habilidades']) / $colCount);
        
        $currentCol = 0;
        $currentSkill = 0;
        
        foreach ($data['habilidades'] as $index => $habilidade) {
            if ($currentSkill % $skillsPerCol == 0 && $currentCol < $colCount) {
                $pdf->SetX($marginLeft + ($currentCol * $colWidth));
                $currentCol++;
            }
            
            $habilidade = trim($habilidade);
            if (strlen($habilidade) > 35) {
                $habilidade = substr($habilidade, 0, 32) . '...';
            }
            
            $pdf->MultiCell($colWidth - 5, 6, '• ' . $habilidade, 0, 'L');
            $currentSkill++;
        }
        
        $pdf->Ln($sectionSpacing);
    }

    // --- SEÇÃO DE IDIOMAS ---
    if (!empty($data['idiomas'])) {
        $checkPageBreak(25);
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor($azul_medio[0], $azul_medio[1], $azul_medio[2]);
        $pdf->Cell(0, 10, 'IDIOMAS', 0, 1, 'L');
        
        // Linha decorativa
        $currentX = $pdf->GetX();
        $currentY = $pdf->GetY();
        $pdf->SetDrawColor($azul_medio[0], $azul_medio[1], $azul_medio[2]);
        $pdf->SetLineWidth(0.6);
        $pdf->Line($currentX, $currentY, $currentX + 25, $currentY);
        $pdf->Ln(8);

        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor($preto[0], $preto[1], $preto[2]);
        
        foreach ($data['idiomas'] as $idioma) {
            $textoIdioma = "• {$idioma['idioma']} - {$idioma['nivel']}";
            if (strlen($textoIdioma) > 60) {
                $textoIdioma = substr($textoIdioma, 0, 57) . '...';
            }
            $pdf->MultiCell($contentWidth, 6, $textoIdioma, 0, 'L');
        }
    }

    // --- RODAPÉ PROFISSIONAL ---
    $pdf->SetY(-15);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->SetTextColor($cinza_medio[0], $cinza_medio[1], $cinza_medio[2]);
    $pdf->Cell(0, 10, 'Currículo Profissional • ' . date('d/m/Y'), 0, 0, 'C');

    return $pdf;
}
?>