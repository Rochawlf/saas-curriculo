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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* O seu novo e belo CSS está aqui - sem alterações */
        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --secondary-color: #f8fafc;
            --dark-bg: #0f172a;
            --dark-card: #1e293b;
            --dark-text: #f1f5f9;
            --light-bg: #ffffff;
            --light-card: #f8fafc;
            --light-text: #334155;
            --border-color: #334155;
            --success-color: #10b981;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 12px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background-color: var(--light-bg);
            color: var(--light-text);
            transition: var(--transition);
            line-height: 1.6;
        }

        body.dark-theme {
            background-color: var(--dark-bg);
            color: var(--dark-text);
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background-color: var(--light-card);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .dark-theme .top-bar {
            background-color: var(--dark-card);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .welcome-message {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .top-bar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        #theme-toggle {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: var(--transition);
        }

        #theme-toggle:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .dark-theme #theme-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .logout-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            transition: var(--transition);
        }

        .logout-link:hover {
            background-color: rgba(99, 102, 241, 0.1);
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(90deg, var(--primary-color), #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .dashboard-header p {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.8;
        }

        .interaction-card {
            background-color: var(--light-card);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .dark-theme .interaction-card {
            background-color: var(--dark-card);
        }

        .interaction-card h2 {
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            font-weight: 600;
        }

        #user-input {
            width: 100%;
            min-height: 120px;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            background-color: var(--light-bg);
            color: var(--light-text);
            font-size: 1rem;
            resize: vertical;
            transition: var(--transition);
            margin-bottom: 1rem;
        }

        .dark-theme #user-input {
            background-color: var(--dark-bg);
            color: var(--dark-text);
            border-color: #475569;
        }

        #user-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .file-upload-area {
            margin-bottom: 1rem;
        }

        #file-input {
            display: none;
        }

        .file-upload-label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background-color: #f1f5f9;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
        }

        .dark-theme .file-upload-label {
            background-color: #334155;
            color: #cbd5e1;
        }

        .file-upload-label:hover {
            background-color: #e2e8f0;
        }

        .dark-theme .file-upload-label:hover {
            background-color: #475569;
        }

        #file-list {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .file-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: #f1f5f9;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .dark-theme .file-item {
            background-color: #334155;
        }

        .file-item i {
            color: var(--primary-color);
        }

        .action-buttons {
            margin-top: 2rem;
            text-align: center;
        }

        #generate-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 6px rgba(99, 102, 241, 0.3);
        }

        #generate-btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(99, 102, 241, 0.4);
        }

        #generate-btn:disabled {
            background-color: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-icon {
            font-size: 1.2rem;
        }

        #ia-response-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .pdf-preview-card {
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            background-color: var(--light-card);
            box-shadow: var(--shadow);
            transition: var(--transition);
            text-align: center;
            padding: 1.5rem;
            overflow: hidden;
        }

        .dark-theme .pdf-preview-card {
            background-color: var(--dark-card);
        }

        .pdf-preview-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .pdf-preview-card .thumbnail {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            color: white;
        }

        .pdf-preview-card h3 {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            color: var(--light-text);
            font-weight: 600;
        }

        .dark-theme .pdf-preview-card h3 {
            color: var(--dark-text);
        }

        .pdf-preview-card a {
            display: inline-block;
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: var(--radius);
            font-weight: 600;
            transition: var(--transition);
        }

        .pdf-preview-card a:hover {
            background-color: var(--primary-hover);
        }

        .loader-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            grid-column: 1 / -1;
        }

        .loader {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(99, 102, 241, 0.2);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s linear infinite;
            margin-bottom: 1.5rem;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 768px) {
            .dashboard-header h1 {
                font-size: 2rem;
            }

            .interaction-card {
                padding: 1.5rem;
            }

            #ia-response-container {
                grid-template-columns: 1fr;
            }

            .top-bar {
                padding: 1rem;
            }
        }
    </style>
</head>

<body class="dark-theme">

    <div class="top-bar">
        <span class="welcome-message">Olá, <?php echo htmlspecialchars($nome_usuario); ?></span>
        <div class="top-bar-actions">
            <button id="theme-toggle">☀️</button>
            <a href="logout.php" class="logout-link">Sair</a>
        </div>
    </div>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Crie seu Currículo Moderno com IA</h1>
            <p>Cole o seu currículo antigo ou descreva suas experiências. Anexe um currículo existente (PDF/Imagem) para melhores resultados. A nossa IA irá analisar, estruturar e criar modelos visuais para você escolher.</p>
        </div>

        <div class="interaction-card">
            <h2>Forneça as suas informações</h2>
            <textarea id="user-input" placeholder="Ex: João Silva, Desenvolvedor Web em São Paulo. Trabalhei na Empresa X de 2020 a 2022..."></textarea>

            <div class="file-upload-area">
                <input type="file" id="file-input" accept=".pdf, image/*" multiple>
                <label for="file-input" class="file-upload-label">
                    <i class="fas fa-cloud-upload-alt"></i> Anexar Currículo (PDF, Imagem)
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
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- LÓGICA DO TEMA ---
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.body;
            const savedTheme = localStorage.getItem('theme') || 'dark';
            if (savedTheme === 'dark') {
                body.classList.add('dark-theme');
                themeToggle.textContent = '☀️';
            } else {
                body.classList.remove('dark-theme');
                themeToggle.textContent = '🌙';
            }
            themeToggle.addEventListener('click', () => {
                body.classList.toggle('dark-theme');
                const isDark = body.classList.contains('dark-theme');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                themeToggle.textContent = isDark ? '☀️' : '🌙';
            });

            // --- LÓGICA DOS FICHEIROS ---
            const fileInput = document.getElementById('file-input');
            const fileList = document.getElementById('file-list');
            fileInput.addEventListener('change', () => {
                fileList.innerHTML = '';
                if (fileInput.files.length > 0) {
                    for (const file of fileInput.files) {
                        const fileItem = document.createElement('div');
                        fileItem.className = 'file-item';
                        fileItem.innerHTML = `<i class="fas fa-file"></i> ${file.name}`;
                        fileList.appendChild(fileItem);
                    }
                }
            });

            // --- LÓGICA DE GERAÇÃO ---
            const generateBtn = document.getElementById('generate-btn');
            const userInput = document.getElementById('user-input');
            const iaResponseContainer = document.getElementById('ia-response-container');

            const handleGenerate = async () => {
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

                    // **CORREÇÃO**: A chamada real ao backend foi restaurada aqui.
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
                            // **CORREÇÃO**: O link agora usa a URL real retornada pelo backend.
                            const cardHTML = `
                        <div class="pdf-preview-card">
                            <div class="thumbnail">
                                <i class="fas fa-file-pdf"></i>
                            </div>
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
                    iaResponseContainer.innerHTML = `<p style="color: #ef4444; text-align: center;">Ocorreu um erro: ${error.message}</p>`;
                }

                generateBtn.disabled = false;
            };

            generateBtn.addEventListener('click', handleGenerate);

            // Permitir enviar com Enter (Ctrl+Enter para nova linha)
            userInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.ctrlKey) {
                    e.preventDefault();
                    handleGenerate();
                }
            });
        });
    </script>
</body>

</html>