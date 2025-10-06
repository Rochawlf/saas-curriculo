<?php
/**
 * Cria um currículo em PDF com um design profissional e estruturado.
 *
 * @param array $data Os dados estruturados do currículo.
 * @return TCPDF A instância do objeto PDF pronta para ser gerada.
 */
function create_template_profissional($data) {
    // Cria uma nova instância do TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configurações do documento
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(18, 18, 18);
    $pdf->SetAutoPageBreak(TRUE, 18); // Margem inferior
    $pdf->AddPage();

    // --- CABEÇALHO ---
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', 'B', 26);
    $pdf->Cell(0, 12, $data['nome_completo'] ?? 'Nome não encontrado', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetTextColor(45, 52, 54); // Cinza escuro para o cargo
    $pdf->Cell(0, 8, $data['cargo'] ?? 'Cargo não encontrado', 0, 1, 'L');
    $pdf->Ln(2);

    // Linha de contacto
    $pdf->SetFont('helvetica', '', 9);
    $contatoLinha = implode('  |  ', array_filter([
        $data['contato']['email'] ?? null,
        $data['contato']['telefone'] ?? null,
        $data['contato']['linkedin'] ?? null,
        $data['contato']['localizacao'] ?? null
    ]));
    $pdf->SetTextColor(80, 80, 80);
    $pdf->Cell(0, 8, $contatoLinha, 0, 1, 'L');

    // Linha divisória subtil sob o cabeçalho
    $pdf->SetLineStyle(array('width' => 0.2, 'color' => array(200, 200, 200)));
    $pdf->Line(18, $pdf->GetY() + 2, 192, $pdf->GetY() + 2);
    $pdf->Ln(8);

    // Restaura a cor do texto para preto
    $pdf->SetTextColor(0, 0, 0);

    /**
     * Função interna para desenhar uma secção do currículo.
     */
    $drawSection = function($title, $content, $pdf) {
        if (!empty($content)) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetTextColor(45, 52, 54);
            $pdf->Cell(0, 10, mb_strtoupper($title, 'UTF-8'), 0, 1, 'L');
            $pdf->Ln(2);
            
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(0, 0, 0);

            if (is_array($content)) { // Para experiência e educação
                foreach ($content as $item) {
                     $pdf->SetFont('helvetica', 'B', 10.5);
                     $pdf->Cell(0, 6, ($item['cargo'] ?? $item['curso']), 0, 0, 'L');
                     
                     // Período alinhado à direita
                     $pdf->SetFont('helvetica', 'I', 9);
                     $pdf->SetTextColor(100, 100, 100);
                     $pdf->Cell(0, 6, $item['periodo'], 0, 1, 'R');

                     $pdf->SetFont('helvetica', '', 10);
                     $pdf->SetTextColor(50, 50, 50);
                     $pdf->Cell(0, 6, ($item['empresa'] ?? $item['instituicao']), 0, 1, 'L');
                     
                     $pdf->SetTextColor(0, 0, 0);
                     // Usa WriteHTML para renderizar listas de descrição
                     if (!empty($item['descricao'])) {
                        // Converte quebras de linha em itens de lista e adiciona um marcador
                        $descricaoItems = explode("\n", trim($item['descricao']));
                        $descricaoHtml = '<ul>';
                        foreach($descricaoItems as $descItem) {
                            if(trim($descItem) !== '') {
                                // Remove marcadores existentes como "-" ou "*"
                                $cleanItem = ltrim(trim($descItem), '-* ');
                                $descricaoHtml .= '<li>' . htmlspecialchars($cleanItem) . '</li>';
                            }
                        }
                        $descricaoHtml .= '</ul>';
                        $pdf->WriteHTML($descricaoHtml, true, false, true, false, '');
                     }
                     $pdf->Ln(4);
                }
            } else { // Para resumo
                $pdf->MultiCell(0, 5, $content, 0, 'J');
                $pdf->Ln(6);
            }
        }
    };

    // Desenha cada secção
    $drawSection('Resumo Profissional', $data['resumo_profissional'] ?? '', $pdf);
    $drawSection('Experiência Profissional', $data['experiencia'] ?? [], $pdf);
    $drawSection('Educação', $data['educacao'] ?? [], $pdf);
    
    // Secção de Habilidades
     if (!empty($data['habilidades'])) {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(45, 52, 54);
        $pdf->Cell(0, 10, 'HABILIDADES TÉCNICAS E COMPETÊNCIAS', 0, 1, 'L');
        $pdf->Ln(2);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);

        // Apresenta as habilidades em colunas para melhor aproveitamento do espaço
        $pdf->setEqualColumns(3, 55);
        foreach($data['habilidades'] as $habilidade) {
            $pdf->MultiCell(0, 5, '• ' . $habilidade, 0, 'L');
            $pdf->Ln(1);
        }
        $pdf->selectColumn(); // Volta ao layout de uma coluna
    }

    // Retorna o objeto PDF pronto para ser gerado
    return $pdf;
}
?>
