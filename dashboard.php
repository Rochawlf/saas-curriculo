<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gerador de Currículo IA</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Estilos específicos para a nova preview dos PDFs */
        #ia-response-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .pdf-preview-card {
            border: 1px solid var(--input-border-color);
            border-radius: 8px;
            background-color: var(--card-bg);
            box-shadow: 0 4px 6px var(--shadow-color);
            transition: transform 0.2s, box-shadow 0.2s;
            text-align: center;
            padding: 1rem;
        }

        .pdf-preview-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px var(--shadow-color);
        }

        .pdf-preview-card .thumbnail {
            width: 100%;
            height: 250px;
            background-color: #e9ecef;
            border-radius: 4px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #adb5bd;
        }
        
        .dark-theme .pdf-preview-card .thumbnail {
            background-color: #3a3a3a;
            color: #6c757d;
        }

        .pdf-preview-card h3 {
            font-size: 1rem;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .pdf-preview-card a {
            display: inline-block;
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-color);
            color: var(--button-text-color);
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: filter 0.2s;
        }
        
        .pdf-preview-card a:hover {
            filter: brightness(1.1);
        }
    </style>
</head>
<body class="dark-theme">

    <div class="top-bar">
        <span class="welcome-message">Olá, <?php echo htmlspecialchars($nome_usuario); ?></span>
        <button id="theme-toggle">☀️</button>
        <a href="logout.php" class="logout-link">Sair</a>
    </div>

    <div id="dashboard-layout-single">
        <main id="main-content">
            <div class="interaction-card">
                <h1>Crie seu Currículo Moderno com IA</h1>
                <p>Cole o seu currículo antigo ou descreva suas experiências. Anexe um currículo existente (PDF/Imagem) para melhores resultados. A nossa IA irá analisar, estruturar e criar 5 modelos visuais para você escolher.</p>
                
                <textarea id="user-input" placeholder="Ex: João Silva, Desenvolvedor Web em São Paulo. Trabalhei na Empresa X de 2020 a 2022..."></textarea>
                
                 <div class="file-upload-area">
                    <input type="file" id="file-input" accept=".pdf, image/*" multiple>
                    <label for="file-input" class="file-upload-label">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0z"/></svg>
                        Anexar Currículo (PDF, Imagem)
                    </label>
                </div>
                <div id="file-list"></div>

                <div class="action-buttons">
                    <button id="generate-btn">
                        <span class="btn-icon">✨</span> Gerar Currículos com IA
                    </button>
                </div>
            </div>

            <div id="ia-response-container">
                <!-- As prévias dos PDFs aparecerão aqui -->
            </div>
        </main>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Lógica do tema e dos ficheiros (igual à anterior)
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;
    const savedTheme = localStorage.getItem('theme') || 'dark';
    if (savedTheme === 'dark') { body.classList.add('dark-theme'); themeToggle.textContent = '☀️'; } 
    else { body.classList.remove('dark-theme'); themeToggle.textContent = '🌙'; }
    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-theme');
        const isDark = body.classList.contains('dark-theme');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        themeToggle.textContent = isDark ? '☀️' : '🌙';
    });

    const fileInput = document.getElementById('file-input');
    const fileList = document.getElementById('file-list');
    fileInput.addEventListener('change', () => {
        fileList.innerHTML = '';
        if (fileInput.files.length > 0) {
            for (const file of fileInput.files) {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.textContent = file.name;
                fileList.appendChild(fileItem);
            }
        }
    });

    const generateBtn = document.getElementById('generate-btn');
    const userInput = document.getElementById('user-input');
    const iaResponseContainer = document.getElementById('ia-response-container');

    generateBtn.addEventListener('click', async () => {
        if (userInput.value.trim() === '' && fileInput.files.length === 0) {
            alert('Por favor, insira ou anexe as informações do seu currículo.');
            return;
        }

        generateBtn.disabled = true;
        iaResponseContainer.innerHTML = '<div class="loader-container"><div class="loader"></div><p>A IA está a analisar e a desenhar os seus currículos... Isto pode demorar um pouco.</p></div>';

        try {
            const formData = new FormData();
            formData.append('userInput', userInput.value);
            for (const file of fileInput.files) {
                formData.append('files[]', file);
            }

            const response = await fetch('generate.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || `Erro HTTP: ${response.status}`);
            }

            const result = await response.json();
            
            iaResponseContainer.innerHTML = '';
            if (result.files && result.files.length > 0) {
                result.files.forEach((fileInfo) => {
                    const cardHTML = `
                        <div class="pdf-preview-card">
                            <div class="thumbnail">📄</div>
                            <h3>${fileInfo.template_name}</h3>
                            <a href="${fileInfo.url}" target="_blank" download>Baixar PDF</a>
                        </div>
                    `;
                    iaResponseContainer.insertAdjacentHTML('beforeend', cardHTML);
                });
            } else {
                iaResponseContainer.innerHTML = '<p>A IA não conseguiu gerar os modelos de currículo. Tente novamente.</p>';
            }

        } catch (error) {
            console.error('Erro ao gerar currículos:', error);
            iaResponseContainer.innerHTML = `<p style="color: #d9534f; text-align: center;">Ocorreu um erro: ${error.message}</p>`;
        }

        generateBtn.disabled = false;
    });
});
</script>
</body>
</html>

