<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS Currículo IA - Crie seu Futuro Profissional</title>
    <!-- Link para o arquivo CSS externo -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="top-bar">
        <button id="theme-toggle">🌙</button>
        
        <div id="login-container">
            <div id="login-trigger" class="auth-button">Login</div>
            
            <div id="login-flyout">
                <h2>Acessar Plataforma</h2>
                <form action="dashboard.php" method="POST">
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
                <p>
                    Basta fornecer as informações básicas sobre sua carreira, educação e habilidades.
                    Nossa IA irá analisar, otimizar e gerar um texto profissional e cativante para cada seção do seu currículo,
                    destacando seus pontos fortes para impressionar qualquer recrutador.
                </p>
                <br>
                <a href="#login-container" class="cta-button">Comece Agora Gratuitamente</a>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.body;

            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                body.classList.add('dark-theme');
                themeToggle.textContent = '☀️';
            } else {
                body.classList.remove('dark-theme');
                themeToggle.textContent = '🌙';
            }

            themeToggle.addEventListener('click', () => {
                body.classList.toggle('dark-theme');

                if (body.classList.contains('dark-theme')) {
                    localStorage.setItem('theme', 'dark');
                    themeToggle.textContent = '☀️';
                } else {
                    localStorage.setItem('theme', 'light');
                    themeToggle.textContent = '🌙';
                }
            });
        });
    </script>

</body>
</html>
