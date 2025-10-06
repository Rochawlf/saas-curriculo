<?php
// Bloco PHP para processar mensagens de erro e sucesso da URL
$feedback_message = '';
$feedback_type = ''; // 'success' ou 'error'

if (isset($_GET['error'])) {
    $feedback_type = 'error';
    switch ($_GET['error']) {
        // Erros de Login
        case 'login_invalido': $feedback_message = 'Email ou senha inválidos.'; break;
        // Erros de Registo
        case 'campos_vazios': $feedback_message = 'Todos os campos são obrigatórios.'; break;
        case 'email_invalido': $feedback_message = 'O formato do email é inválido.'; break;
        case 'senha_curta': $feedback_message = 'A senha deve ter no mínimo 6 caracteres.'; break;
        case 'senhas_nao_conferem': $feedback_message = 'As senhas não coincidem.'; break;
        case 'email_existente': $feedback_message = 'Este email já está registado.'; break;
        default: $feedback_message = 'Ocorreu um erro inesperado. Tente novamente.'; break;
    }
}

if (isset($_GET['success']) && $_GET['success'] == 'cadastro_sucesso') {
    $feedback_type = 'success';
    $feedback_message = 'Registo realizado com sucesso! Pode agora fazer login.';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS Currículo IA - Crie seu Futuro Profissional</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
            --border-color: #e2e8f0;
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
            overflow-x: hidden;
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
            background-color: var(--light-bg);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .dark-theme .top-bar {
            background-color: var(--dark-bg);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
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

        #login-container {
            position: relative;
        }

        .auth-button {
            padding: 0.75rem 1.5rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .auth-button:hover {
            background-color: var(--primary-hover);
        }

        #login-flyout {
            position: absolute;
            top: 100%;
            right: 0;
            width: 320px;
            background-color: var(--light-card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-top: 0.5rem;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
            z-index: 1000;
        }

        .dark-theme #login-flyout {
            background-color: var(--dark-card);
        }

        #login-container:hover #login-flyout {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        #login-flyout h2 {
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            background-color: var(--light-bg);
            color: var(--light-text);
            transition: var(--transition);
        }

        .dark-theme .form-group input {
            background-color: var(--dark-bg);
            color: var(--dark-text);
            border-color: #475569;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        #login-flyout button {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 0.5rem;
        }

        #login-flyout button:hover {
            background-color: var(--primary-hover);
        }

        .index-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        header {
            text-align: center;
            margin-bottom: 4rem;
            position: relative;
            z-index: 10;
        }

        header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(90deg, var(--primary-color), #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }

        header p {
            font-size: 1.25rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .features { padding: 2rem 0; }
        .features h2 { font-size: 2rem; margin-bottom: 1.5rem; font-weight: 700; }
        .features p { font-size: 1.1rem; margin-bottom: 2rem; opacity: 0.9; }

        .cta-button {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
            box-shadow: 0 4px 6px rgba(99, 102, 241, 0.3);
        }
        .cta-button:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(99, 102, 241, 0.4);
        }

        .image-carousel { position: relative; height: 500px; border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow); }
        .carousel-image { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 1s ease-in-out; border-radius: var(--radius); }
        .carousel-image.active { opacity: 1; }

        .benefits { grid-column: 1 / -1; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-top: 4rem; }
        .benefit-card { background-color: var(--light-card); border-radius: var(--radius); padding: 2rem; box-shadow: var(--shadow); text-align: center; transition: var(--transition); }
        .dark-theme .benefit-card { background-color: var(--dark-card); }
        .benefit-card:hover { transform: translateY(-5px); }
        .benefit-icon { font-size: 2.5rem; margin-bottom: 1.5rem; color: var(--primary-color); }
        .benefit-card h3 { font-size: 1.25rem; margin-bottom: 1rem; font-weight: 600; }
        .benefit-card p { opacity: 0.8; }

        footer { text-align: center; padding: 3rem 0; margin-top: 4rem; border-top: 1px solid var(--border-color); opacity: 0.7; }
        
        /* --- ESTILOS PARA O MODAL DE REGISTO --- */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); backdrop-filter: blur(5px); display: flex; align-items: center; justify-content: center; z-index: 2000; opacity: 0; visibility: hidden; transition: opacity 0.3s ease, visibility 0.3s ease; }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        .modal-content { background-color: var(--light-bg); border-radius: var(--radius); padding: 2.5rem; width: 90%; max-width: 450px; box-shadow: var(--shadow); position: relative; transform: scale(0.95); transition: transform 0.3s ease; }
        .modal-overlay.active .modal-content { transform: scale(1); }
        .dark-theme .modal-content { background-color: var(--dark-card); }
        .modal-close-btn { position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--light-text); opacity: 0.7; transition: var(--transition); }
        .dark-theme .modal-close-btn { color: var(--dark-text); }
        .modal-close-btn:hover { opacity: 1; transform: rotate(90deg); }
        .modal-content h2 { text-align: center; margin-bottom: 2rem; font-size: 1.75rem; }
        .feedback-message { padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; text-align: center; font-weight: 500; }
        .feedback-message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .feedback-message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        @media (max-width: 768px) {
            main { grid-template-columns: 1fr; gap: 2rem; }
            header h1 { font-size: 2.5rem; }
            .image-carousel { height: 300px; }
            .benefits { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="dark-theme">

    <div class="top-bar">
        <button id="theme-toggle">☀️</button>
        
        <div id="login-container">
            <div id="login-trigger" class="auth-button">Login</div>
            
            <div id="login-flyout">
                <h2>Acessar Plataforma</h2>

                <!-- Exibe mensagem de erro/sucesso para o LOGIN -->
                <?php if (!empty($feedback_message) && (!isset($_GET['error']) || !str_contains($_GET['error'], 'senha') && !str_contains($_GET['error'], 'email') && !str_contains($_GET['error'], 'campos')) ): ?>
                    <div class="feedback-message <?php echo $feedback_type; ?>">
                        <?php echo $feedback_message; ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required placeholder="seu@email.com">
                    </div>
                    <div class="form-group">
                        <label for="senha">Senha:</label>
                        <input type="password" id="senha" name="senha" required placeholder="********">
                    </div>
                    <button type="submit">Entrar</button>
                </form>
            </div>
        </div>
    </div>

    <div class="index-container">
        <header>
            <h1>Gerador de Currículo com IA</h1>
            <p>Transforme sua experiência profissional em um currículo irresistível com o poder da Inteligência Artificial Gemini.</p>
        </header>

        <main>
            <section class="features">
                <h2>Como Funciona?</h2>
                <p>Basta fornecer as informações básicas sobre sua carreira, educação e habilidades. Nossa IA irá analisar, otimizar e gerar um texto profissional e cativante para o seu currículo.</p>
                <br>
                <a href="#register-modal" id="cta-button" class="cta-button">
                    <i class="fas fa-rocket"></i>
                    Comece Agora Gratuitamente
                </a>
            </section>

            <div class="image-carousel">
                <img src="https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Profissional em reunião" class="carousel-image active">
                <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1374&q=80" alt="Profissional trabalhando" class="carousel-image">
                <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1376&q=80" alt="Profissional em entrevista" class="carousel-image">
            </div>
        </main>

        <section class="benefits">
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-brain"></i>
                </div>
                <h3>IA Avançada</h3>
                <p>Utilizamos a tecnologia Gemini AI para criar currículos otimizados e personalizados.</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3>Rápido e Eficiente</h3>
                <p>Gere currículos profissionais em minutos, não em horas.</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Maiores Oportunidades</h3>
                <p>Aumente suas chances de ser notado pelos recrutadores.</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <h3>Modelos Profissionais</h3>
                <p>Escolha entre diversos templates modernos e ATS-friendly.</p>
            </div>
        </section>
    </div>

    <footer>
        <p>&copy; 2025 SaaS Currículo IA. Todos os direitos reservados.</p>
    </footer>

    <!-- --- O NOVO MODAL DE REGISTO --- -->
    <div id="register-modal" class="modal-overlay">
        <div class="modal-content">
            <button id="modal-close" class="modal-close-btn">&times;</button>
            <h2>Crie sua Conta Gratuita</h2>
            
            <!-- Exibe mensagem de erro/sucesso para o REGISTO -->
            <?php if (!empty($feedback_message) && (isset($_GET['error']) || isset($_GET['success'])) ): ?>
                <div class="feedback-message <?php echo $feedback_type; ?>">
                    <?php echo $feedback_message; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="reg-nome">Nome Completo:</label>
                    <input type="text" id="reg-nome" name="nome_completo" required>
                </div>
                <div class="form-group">
                    <label for="reg-email">Email:</label>
                    <input type="email" id="reg-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="reg-senha">Senha (mín. 6 caracteres):</label>
                    <input type="password" id="reg-senha" name="senha" required>
                </div>
                <div class="form-group">
                    <label for="reg-confirma-senha">Confirmar Senha:</label>
                    <input type="password" id="reg-confirma-senha" name="confirma_senha" required>
                </div>
                <button type="submit" class="auth-button" style="width: 100%; margin-top: 1rem;">Criar Conta</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
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

            const images = document.querySelectorAll('.carousel-image');
            let currentIndex = 0;
            function showNextImage() {
                images[currentIndex].classList.remove('active');
                currentIndex = (currentIndex + 1) % images.length;
                images[currentIndex].classList.add('active');
            }
            setInterval(showNextImage, 5000);

            // --- LÓGICA PARA O MODAL DE REGISTO ---
            const ctaButton = document.getElementById('cta-button');
            const registerModal = document.getElementById('register-modal');
            const modalCloseBtn = document.getElementById('modal-close');
            const openModal = () => registerModal.classList.add('active');
            const closeModal = () => registerModal.classList.remove('active');

            ctaButton.addEventListener('click', (e) => {
                e.preventDefault();
                openModal();
            });
            modalCloseBtn.addEventListener('click', closeModal);
            registerModal.addEventListener('click', (e) => {
                if (e.target === registerModal) closeModal();
            });

            // Se a página carregar com um erro de registo ou sucesso, abre o modal/flyout.
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('error') || urlParams.has('success')) {
                // Modificado para verificar se o erro pertence ao registo
                const errorType = urlParams.get('error');
                if (errorType && ['campos_vazios', 'email_invalido', 'senha_curta', 'senhas_nao_conferem', 'email_existente'].includes(errorType) || urlParams.get('success') === 'cadastro_sucesso') {
                    openModal();
                }
            }
        });
    </script>

</body>
</html>

