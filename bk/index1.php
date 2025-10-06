<?php
// O único PHP necessário aqui: iniciar a sessão para saber o status do usuário.
// Isso DEVE ser a primeira coisa no arquivo.
session_start();

// Variáveis para facilitar o uso no HTML
$usuario_logado = isset($_SESSION['logado']) && $_SESSION['logado'];
$nome_usuario = $_SESSION['usuario_nome'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Currículo IA - Crie seu Futuro Profissional</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Estilos CSS - Incorporados para facilitar */
        :root {
            --cor-primaria: #007bff;
            --cor-primaria-hover: #0056b3;
            --cor-fundo: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            --cor-texto: #333;
            --cor-fundo-card: #ffffff;
            --sombra-card: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        body.dark-mode {
            --cor-primaria: #4dabf7;
            --cor-primaria-hover: #1c7ed6;
            --cor-fundo: linear-gradient(135deg, #2c3e50 0%, #4ca1af 100%);
            --cor-texto: #f8f9fa;
            --cor-fundo-card: #34495e;
            --sombra-card: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--cor-fundo);
            color: var(--cor-texto);
            line-height: 1.6;
            transition: background-color 0.3s, color 0.3s;
            overflow-x: hidden;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 0 auto;
        }

        /* Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--cor-primaria);
        }

        nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .theme-switcher {
            background: none;
            border: none;
            color: var(--cor-texto);
            font-size: 1.2rem;
            cursor: pointer;
        }

        .btn {
            background-color: var(--cor-primaria);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover {
            background-color: var(--cor-primaria-hover);
        }

        .btn:disabled {
            background-color: #aaa;
            cursor: not-allowed;
        }

        .btn-secondary {
            background-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        /* Main Content */
        main {
            text-align: center;
            padding: 60px 0;
        }

        main h1 {
            font-size: 2.8rem;
            margin-bottom: 20px;
        }

        main p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto 30px auto;
        }

        main .hero-image {
            max-width: 500px;
            margin-top: 40px;
            border-radius: 10px;
        }

        /* Estilos para a Ferramenta de IA */
        .ai-tool-container {
            background-color: var(--cor-fundo-card);
            padding: 40px;
            border-radius: 8px;
            box-shadow: var(--sombra-card);
            max-width: 700px;
            margin: 20px auto;
        }

        .ai-tool-container textarea {
            width: 100%;
            min-height: 150px;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 1rem;
            margin-bottom: 20px;
            resize: vertical;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 20px 0;
            margin-top: 50px;
            border-top: 1px solid rgba(150, 150, 150, 0.2);
        }

        /* Modal de Login */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: var(--cor-fundo-card);
            padding: 40px;
            border-radius: 8px;
            box-shadow: var(--sombra-card);
            width: 90%;
            max-width: 400px;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.8rem;
            color: var(--cor-texto);
            cursor: pointer;
            border: none;
            background: none;
        }

        .modal-content h2 {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            background: #fff;
            color: #333;
        }

        .register-link {
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .register-link a {
            color: var(--cor-primaria);
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        #login-message {
            margin-top: 15px;
            color: #e74c3c;
        }
    </style>
</head>

<body class="light-mode">

    <header class="container">
        </header>

    <main class="container">
        <?php if ($usuario_logado): ?>
            <div class="chat-container">
                <div id="chat-history" class="chat-history">
                    <div class="ai-message">Olá, <?php echo htmlspecialchars(explode(' ', $nome_usuario)[0]); ?>! Como posso te ajudar a otimizar seu currículo hoje?</div>
                </div>
                <div class="chat-input-area">
                    <form id="ai-form">
                        <label for="file-input" id="attach-file-btn" title="Anexar imagem">
                            <i class="fas fa-paperclip"></i>
                        </label>
                        <input type="file" id="file-input" accept="image/*" style="display: none;">
                        
                        <textarea id="chat-input" placeholder="Digite sua mensagem ou anexe uma imagem..." rows="1"></textarea>
                        
                        <button id="send-btn" type="submit" title="Enviar">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                    </form>
                    <div id="file-preview"></div>
                </div>
            </div>
        <?php else: ?>
            <div class="hero-section">
                </div>
        <?php endif; ?>
    </main>
    
    <footer class="container">
        </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- Lógica do Tema, Modal, Login e Logout (sem alterações) ---
            // ... (cole aqui todo o script de tema, modal, login e logout que já funciona)

            // ---------- NOVA LÓGICA PARA O CHAT DE IA ----------
            const aiForm = document.getElementById('ai-form');
            if (aiForm) {
                const chatHistory = document.getElementById('chat-history');
                const chatInput = document.getElementById('chat-input');
                const fileInput = document.getElementById('file-input');
                const filePreview = document.getElementById('file-preview');
                let selectedFile = null;

                // Mostra o nome do arquivo selecionado
                fileInput.addEventListener('change', () => {
                    if (fileInput.files.length > 0) {
                        selectedFile = fileInput.files[0];
                        filePreview.textContent = `Anexo: ${selectedFile.name}`;
                    }
                });
                
                // Enviar com Enter, quebrar linha com Shift+Enter
                chatInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        aiForm.requestSubmit();
                    }
                });

                aiForm.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    
                    const userText = chatInput.value.trim();
                    if (!userText && !selectedFile) return;

                    // Adiciona a mensagem do usuário ao histórico
                    appendMessage(userText, 'user-message');
                    if (selectedFile) {
                        appendMessage(`(Anexo: ${selectedFile.name})`, 'user-message');
                    }
                    
                    const loadingEl = appendMessage('<i class="fas fa-spinner fa-spin"></i>', 'ai-message loading-indicator');

                    // Prepara os dados para envio (voltamos a usar FormData para enviar arquivos)
                    const formData = new FormData();
                    formData.append('user_text', userText);
                    if (selectedFile) {
                        formData.append('attachment', selectedFile);
                    }
                    
                    // Limpa os campos após o envio
                    chatInput.value = '';
                    fileInput.value = null;
                    selectedFile = null;
                    filePreview.textContent = '';

                    try {
                        const response = await fetch('/saascurriculo/api/generate.php', {
                            method: 'POST',
                            body: formData // Browser define o Content-Type automaticamente para FormData
                        });

                        const result = await response.json();
                        loadingEl.remove(); // Remove o indicador de carregamento

                        if (result.success) {
                            appendMessage(result.generated_text, 'ai-message');
                        } else {
                            appendMessage(`Erro: ${result.message}`, 'ai-message');
                        }
                    } catch (error) {
                        loadingEl.remove();
                        appendMessage('Erro de conexão. Tente novamente.', 'ai-message');
                        console.error('Erro na requisição para a IA:', error);
                    }
                });

                function appendMessage(text, className) {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `chat-message ${className}`;
                    messageDiv.innerHTML = text; // Usamos innerHTML para renderizar o ícone de spinner
                    chatHistory.appendChild(messageDiv);
                    chatHistory.scrollTop = chatHistory.scrollHeight; // Auto-scroll
                    return messageDiv;
                }
            }
        });
    </script>
</body>
</html>