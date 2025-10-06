<?php
session_start();
$usuario_logado = isset($_SESSION['logado']) && $_SESSION['logado'];
$nome_usuario = $_SESSION['usuario_nome'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Currículo IA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="light-mode">

    <header class="container">
        <div class="logo">Currículo IA</div>
        <nav>
            <button id="theme-switcher"><i class="fas fa-moon"></i></button>
            <?php if ($usuario_logado): ?>
                <div class="user-info">
                    <span>Olá, <?php echo htmlspecialchars($nome_usuario); ?>!</span>
                    <button id="btn-logout" class="btn btn-secondary">Sair</button>
                </div>
            <?php else: ?>
                <button id="btn-acessar" class="btn">Acessar</button>
            <?php endif; ?>
        </nav>
    </header>

    <main class="container">
        <?php if ($usuario_logado): ?>
            <div class="generator-container">
                <h2>Preencha os dados para gerar seu currículo</h2>
                <p>Nossa IA irá otimizar as descrições para criar um currículo de alto impacto.</p>
                <form id="resume-form">
                    <!-- Informações Pessoais -->
                    <fieldset>
                        <legend><i class="fas fa-user"></i> Informações Pessoais</legend>
                        <div class="form-grid">
                            <div class="form-group"><label for="nome">Nome Completo</label><input type="text" id="nome" name="nome" required></div>
                            <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" required></div>
                            <div class="form-group"><label for="telefone">Telefone</label><input type="tel" id="telefone" name="telefone"></div>
                            <div class="form-group"><label for="linkedin">Perfil LinkedIn (URL)</label><input type="url" id="linkedin" name="linkedin"></div>
                        </div>
                    </fieldset>

                    <!-- Resumo Profissional -->
                    <fieldset>
                        <legend><i class="fas fa-briefcase"></i> Resumo Profissional</legend>
                        <div class="form-group">
                            <label for="resumo">Fale um pouco sobre você e seus objetivos (a IA vai aprimorar)</label>
                            <textarea id="resumo" name="resumo" rows="4" placeholder="Ex: Profissional de marketing com 5 anos de experiência em..."></textarea>
                        </div>
                    </fieldset>

                    <!-- Experiência Profissional -->
                    <fieldset>
                        <legend><i class="fas fa-building"></i> Experiência Profissional</legend>
                        <div id="experiencia-wrapper">
                            <!-- JS vai adicionar experiências aqui -->
                        </div>
                        <button type="button" id="add-experiencia" class="btn btn-secondary"><i class="fas fa-plus"></i> Adicionar Experiência</button>
                    </fieldset>

                    <!-- Educação -->
                    <fieldset>
                        <legend><i class="fas fa-graduation-cap"></i> Educação</legend>
                        <div id="educacao-wrapper">
                           <!-- JS vai adicionar formação aqui -->
                        </div>
                         <button type="button" id="add-educacao" class="btn btn-secondary"><i class="fas fa-plus"></i> Adicionar Formação</button>
                    </fieldset>
                    
                    <!-- Habilidades -->
                     <fieldset>
                        <legend><i class="fas fa-cogs"></i> Habilidades</legend>
                        <div class="form-group">
                            <label for="habilidades">Liste suas habilidades separadas por vírgula</label>
                            <input type="text" id="habilidades" name="habilidades" placeholder="Ex: Pacote Office, Inglês Fluente, Liderança...">
                        </div>
                    </fieldset>

                    <!-- Idiomas -->
                    <fieldset>
                        <legend><i class="fas fa-language"></i> Idiomas</legend>
                        <div id="idiomas-wrapper">
                           <!-- JS vai adicionar idiomas aqui -->
                        </div>
                         <button type="button" id="add-idioma" class="btn btn-secondary"><i class="fas fa-plus"></i> Adicionar Idioma</button>
                    </fieldset>

                    <!-- Projetos -->
                    <fieldset>
                        <legend><i class="fas fa-lightbulb"></i> Projetos ou Portfólio</legend>
                        <div id="projetos-wrapper">
                           <!-- JS vai adicionar projetos aqui -->
                        </div>
                         <button type="button" id="add-projeto" class="btn btn-secondary"><i class="fas fa-plus"></i> Adicionar Projeto</button>
                    </fieldset>

                    <button type="submit" id="generate-btn" class="btn btn-main"><i class="fas fa-file-pdf"></i> Gerar Currículo com IA</button>
                </form>
                <div id="result-area" class="hidden">
                    <h3>Seu currículo está pronto!</h3>
                    <p>A IA analisou e otimizou suas informações.</p>
                    <a href="#" id="download-link" class="btn btn-success" download>
                        <i class="fas fa-download"></i> Baixar PDF
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="hero-section">
                 <h1>Seu Currículo, Potencializado por IA</h1>
                <p>Preencha suas informações e deixe nossa Inteligência Artificial criar um currículo profissional em PDF para você em segundos.</p>
                <button id="btn-start-now" class="btn">Começar Agora</button>
                <img src="https://images.unsplash.com/photo-1556742502-ec7c0e9f34b1?ixlib=rb-4.0.3&q=85&fm=jpg&crop=entropy&cs=srgb&w=1200" alt="Pessoa criando um currículo em um laptop" class="hero-image">
            </div>
        <?php endif; ?>
    </main>
    
    <div id="loading-overlay" class="hidden">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Aguarde, nossa IA está construindo seu currículo...</p>
        </div>
    </div>

    <?php if (!$usuario_logado): ?>
    <div id="login-modal" class="modal">
        <div class="modal-content">
            <button id="close-modal-btn" class="close-btn">&times;</button>
            <h2>Acessar Plataforma</h2>
            <form id="login-form">
                <div class="form-group"><label for="email">Email</label><input type="email" name="email" id="email" required></div>
                <div class="form-group"><label for="senha">Senha</label><input type="password" name="senha" id="senha" required></div>
                <button type="submit" class="btn">Entrar</button>
                <p id="login-message"></p>
            </form>
            <p class="register-link">Não tem uma conta? <a href="cadastro.html">Cadastre-se</a></p>
        </div>
    </div>
    <?php endif; ?>

    <script src="scripts.js?v=1.1"></script>
</body>
</html>

