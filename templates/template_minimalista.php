<?php
/**
 * Cria um currículo em PDF com um design minimalista e limpo.
 *
 * @param array $data Os dados estruturados do currículo.
 * @return TCPDF A instância do objeto PDF pronta para ser gerada.
 */
function create_template_minimalista($data) {
    // Cria uma nova instância do TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configurações do documento
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(20, 20, 20);
    $pdf->SetAutoPageBreak(TRUE, 20); // Margem inferior para quebra de página
    $pdf->AddPage();

    // --- CABEÇALHO COM NOME E CONTACTOS ---
    $pdf->SetFont('times', 'B', 28); // Fonte clássica para o nome
    $pdf->Cell(0, 15, $data['nome_completo'] ?? 'Nome não encontrado', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 9); // Fonte moderna e limpa para os contactos
    // Junta os detalhes de contacto numa única linha, separados por um "bullet"
    $contatoLinha = implode('  •  ', array_filter([
        $data['contato']['email'] ?? null,
        $data['contato']['telefone'] ?? null,
        $data['contato']['linkedin'] ?? null,
        $data['contato']['localizacao'] ?? null
    ]));
    $pdf->SetTextColor(80, 80, 80); // Cinza escuro para os detalhes
    $pdf->Cell(0, 8, $contatoLinha, 0, 1, 'C');
    
    // Linha divisória horizontal para separar o cabeçalho do conteúdo
    $pdf->Line(20, $pdf->GetY() + 2, 190, $pdf->GetY() + 2);
    $pdf->Ln(8); // Avança a posição vertical

    // Restaura a cor do texto para preto
    $pdf->SetTextColor(0, 0, 0);

    /**
     * Função interna para desenhar uma secção padrão do currículo.
     * Isto evita a repetição de código.
     *
     * @param string $title O título da secção.
     * @param mixed $content O conteúdo (string para resumo, array para experiência/educação).
     * @param TCPDF $pdf A instância do PDF.
     */
    $drawSection = function($title, $content, $pdf) {
        if (!empty($content)) {
            $pdf->SetFont('times', 'B', 12);
            $pdf->Cell(0, 10, mb_strtoupper($title, 'UTF-8'), 0, 1, 'L');
            $pdf->Line($pdf->GetX(), $pdf->GetY(), 190, $pdf->GetY());
            $pdf->Ln(2);
            
            $pdf->SetFont('helvetica', '', 10);
            if (is_array($content)) { // Para experiência e educação
                foreach ($content as $item) {
                     $pdf->SetFont('helvetica', 'B', 10);
                     $pdf->Cell(0, 6, ($item['cargo'] ?? $item['curso']) . ' | ' . ($item['empresa'] ?? $item['instituicao']), 0, 1);
                     $pdf->SetFont('helvetica', 'I', 9);
                     $pdf->SetTextColor(100, 100, 100);
                     $pdf->Cell(0, 6, $item['periodo'], 0, 1);
                     $pdf->SetFont('helvetica', '', 10);
                     $pdf->SetTextColor(0, 0, 0);
                     $pdf->MultiCell(0, 5, ($item['descricao'] ?? ''), 0, 'J'); // 'J' para justificar o texto
                     $pdf->Ln(4);
                }
            } else { // Para resumo
                $pdf->MultiCell(0, 5, $content, 0, 'J');
                $pdf->Ln(6);
            }
        }
    };

    // Desenha cada secção usando a função auxiliar
    $drawSection('Resumo Profissional', $data['resumo_profissional'] ?? '', $pdf);
    $drawSection('Experiência Profissional', $data['experiencia'] ?? [], $pdf);
    $drawSection('Educação', $data['educacao'] ?? [], $pdf);
    
    // Secção de Habilidades (tratada separadamente para um layout diferente)
     if (!empty($data['habilidades'])) {
        $pdf->SetFont('times', 'B', 12);
        $pdf->Cell(0, 10, 'HABILIDADES', 0, 1, 'L');
        $pdf->Line($pdf->GetX(), $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(2);
        
        $pdf->SetFont('helvetica', '', 10);
        $habilidadesStr = implode('  •  ', $data['habilidades']);
        $pdf->MultiCell(0, 5, $habilidadesStr, 0, 'L');
    }

    // Retorna o objeto PDF pronto para ser gerado
    return $pdf;
}
?>

