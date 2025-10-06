// ATENÇÃO: Substitua apenas a função de fetch/submit do seu formulário.
// O resto do seu arquivo scripts.js deve permanecer o mesmo.

// Exemplo de como sua função de submit pode ficar:
document.getElementById('resume-form').addEventListener('submit', function(event) {
    event.preventDefault();
    
    // Mostra o overlay de carregamento
    const loadingOverlay = document.getElementById('loadingOverlay');
    loadingOverlay.classList.remove('hidden');

    // Coleta dos dados do formulário (adapte para a sua lógica)
    const formData = {
        nome: document.getElementById('nome').value,
        email: document.getElementById('email').value,
        telefone: document.getElementById('telefone').value,
        endereco: document.getElementById('endereco').value,
        linkedin: document.getElementById('linkedin').value,
        resumo: document.getElementById('resumo').value,
        habilidades: document.getElementById('habilidades').value,
        experiencias: [], // Adicione sua lógica para pegar experiências
        formacoes: [] // Adicione sua lógica para pegar formações
    };

    fetch('api/curriculo/create_resume.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        // Primeiro, verificamos se a resposta tem o formato JSON esperado
        if (!response.headers.get('content-type')?.includes('application/json')) {
            // Se não for JSON, lemos como texto para ver o erro HTML
            return response.text().then(text => {
                throw new Error('O servidor não respondeu com JSON. Resposta: ' + text);
            });
        }
        return response.json();
    })
    .then(result => {
        loadingOverlay.classList.add('hidden');
        
        // Verifica a resposta do servidor
        if (result.success) {
            alert('Currículo gerado com sucesso!');
            // Abre o currículo em uma nova aba
            window.open(result.file_path, '_blank');
        } else {
            // Se success for false, exibe a mensagem de erro detalhada do PHP
            console.error('Erro retornado pelo servidor:', result);
            let errorMessage = 'Erro ao gerar currículo: ' + result.message;
            if (result.debug_php_output) {
                errorMessage += '\n\nDetalhes (do PHP): ' + result.debug_php_output;
            }
            alert(errorMessage);
        }
    })
    .catch(error => {
        loadingOverlay.classList.add('hidden');
        console.error("Fetch Error:", error);
        alert('Ocorreu um erro de comunicação com o servidor. Verifique o console para detalhes.');
    });
});
