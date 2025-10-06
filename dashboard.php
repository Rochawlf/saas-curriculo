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
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 2rem;
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

        .form-section {
            background-color: var(--light-card);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }

        .dark-theme .form-section {
            background-color: var(--dark-card);
        }

        .form-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 20px -5px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--primary-color);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .section-toggle {
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
            font-size: 1.1rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--light-text);
        }

        .dark-theme label {
            color: var(--dark-text);
        }

        input, textarea, select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: var(--radius);
            background-color: var(--light-bg);
            color: var(--light-text);
            font-size: 1rem;
            transition: var(--transition);
        }

        .dark-theme input, 
        .dark-theme textarea, 
        .dark-theme select {
            background-color: var(--dark-bg);
            color: var(--dark-text);
            border-color: #475569;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .add-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: none;
            border: 1px dashed var(--primary-color);
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
            margin-top: 0.5rem;
        }

        .add-btn:hover {
            background-color: rgba(99, 102, 241, 0.1);
        }

        .remove-btn {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: var(--transition);
        }

        .remove-btn:hover {
            background-color: rgba(239, 68, 68, 0.1);
        }

        .experience-item, .education-item, .skill-item {
            background-color: var(--light-bg);
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 3px solid var(--primary-color);
        }

        .dark-theme .experience-item, 
        .dark-theme .education-item, 
        .dark-theme .skill-item {
            background-color: var(--dark-bg);
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .skills-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .skill-tag {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .file-upload-area {
            margin: 1rem 0;
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
            
            .form-section {
                padding: 1.5rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
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
            <p>Preencha as informações abaixo para gerar currículos profissionais e personalizados</p>
        </div>

        <form id="resume-form">
            <!-- Seção 1: Dados Pessoais -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="section-title">Dados Pessoais</h2>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">Nome Completo *</label>
                        <input type="text" id="nome" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="tel" id="telefone" name="telefone">
                    </div>
                    <div class="form-group">
                        <label for="localizacao">Localização</label>
                        <input type="text" id="localizacao" name="localizacao" placeholder="Cidade, Estado">
                    </div>
                    <div class="form-group full-width">
                        <label for="linkedin">LinkedIn / Portfolio</label>
                        <input type="url" id="linkedin" name="linkedin" placeholder="https://">
                    </div>
                </div>
            </div>

            <!-- Seção 2: Resumo Profissional -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="section-title">Resumo Profissional</h2>
                </div>
                <div class="form-group">
                    <label for="resumo">Descreva brevemente sua experiência e objetivos profissionais</label>
                    <textarea id="resumo" name="resumo" placeholder="Ex: Desenvolvedor Full Stack com 5 anos de experiência..."></textarea>
                </div>
            </div>

            <!-- Seção 3: Experiência Profissional -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="section-title">Experiência Profissional</h2>
                    <button type="button" class="add-btn" id="add-experience">
                        <i class="fas fa-plus"></i> Adicionar
                    </button>
                </div>
                <div id="experiences-container">
                    <div class="experience-item">
                        <div class="item-header">
                            <h3>Experiência #1</h3>
                            <button type="button" class="remove-btn" onclick="this.parentElement.parentElement.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Cargo</label>
                                <input type="text" name="cargo[]" required>
                            </div>
                            <div class="form-group">
                                <label>Empresa</label>
                                <input type="text" name="empresa[]" required>
                            </div>
                            <div class="form-group">
                                <label>Data de Início</label>
                                <input type="month" name="inicio[]" required>
                            </div>
                            <div class="form-group">
                                <label>Data de Término</label>
                                <input type="month" name="fim[]">
                            </div>
                            <div class="form-group full-width">
                                <label>Descrição das Atividades</label>
                                <textarea name="descricao_experiencia[]" placeholder="Descreva suas principais responsabilidades e conquistas"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção 4: Formação Acadêmica -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="section-title">Formação Acadêmica</h2>
                    <button type="button" class="add-btn" id="add-education">
                        <i class="fas fa-plus"></i> Adicionar
                    </button>
                </div>
                <div id="education-container">
                    <div class="education-item">
                        <div class="item-header">
                            <h3>Formação #1</h3>
                            <button type="button" class="remove-btn" onclick="this.parentElement.parentElement.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Curso</label>
                                <input type="text" name="curso[]" required>
                            </div>
                            <div class="form-group">
                                <label>Instituição</label>
                                <input type="text" name="instituicao[]" required>
                            </div>
                            <div class="form-group">
                                <label>Data de Início</label>
                                <input type="month" name="inicio_curso[]" required>
                            </div>
                            <div class="form-group">
                                <label>Data de Conclusão</label>
                                <input type="month" name="fim_curso[]">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção 5: Habilidades -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="section-title">Habilidades</h2>
                </div>
                <div class="form-group">
                    <label>Adicione suas principais habilidades (separadas por vírgula)</label>
                    <input type="text" id="skills-input" placeholder="Ex: JavaScript, React, Node.js, Python, Gestão de Projetos">
                </div>
                <div id="skills-container" class="skills-container">
                    <!-- Skills serão adicionadas aqui dinamicamente -->
                </div>
            </div>

            <!-- Seção 6: Idiomas -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="section-title">Idiomas</h2>
                    <button type="button" class="add-btn" id="add-language">
                        <i class="fas fa-plus"></i> Adicionar
                    </button>
                </div>
                <div id="languages-container">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Idioma</label>
                            <input type="text" name="idioma[]" placeholder="Ex: Inglês">
                        </div>
                        <div class="form-group">
                            <label>Nível</label>
                            <select name="nivel_idioma[]">
                                <option value="Básico">Básico</option>
                                <option value="Intermediário">Intermediário</option>
                                <option value="Avançado">Avançado</option>
                                <option value="Fluente">Fluente</option>
                                <option value="Nativo">Nativo</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção 7: Anexos -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="section-title">Anexar Currículo Existente (Opcional)</h2>
                </div>
                <div class="file-upload-area">
                    <input type="file" id="file-input" accept=".pdf, image/*" multiple>
                    <label for="file-input" class="file-upload-label">
                        <i class="fas fa-cloud-upload-alt"></i> Anexar Arquivos (PDF, Imagem)
                    </label>
                </div>
                <div id="file-list"></div>
            </div>

            <div class="action-buttons">
                <button type="button" id="generate-btn">
                    <span class="btn-icon">✨</span> Gerar Currículos com IA
                </button>
            </div>
        </form>

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

            // --- LÓGICA DAS HABILIDADES ---
            const skillsInput = document.getElementById('skills-input');
            const skillsContainer = document.getElementById('skills-container');
            
            skillsInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    const skill = skillsInput.value.trim().replace(',', '');
                    if (skill) {
                        addSkill(skill);
                        skillsInput.value = '';
                    }
                }
            });

            function addSkill(skill) {
                const skillTag = document.createElement('div');
                skillTag.className = 'skill-tag';
                skillTag.innerHTML = `
                    ${skill}
                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                    <input type="hidden" name="habilidades[]" value="${skill}">
                `;
                skillsContainer.appendChild(skillTag);
            }

            // --- LÓGICA DAS EXPERIÊNCIAS ---
            let experienceCount = 1;
            document.getElementById('add-experience').addEventListener('click', () => {
                experienceCount++;
                const newExperience = document.createElement('div');
                newExperience.className = 'experience-item';
                newExperience.innerHTML = `
                    <div class="item-header">
                        <h3>Experiência #${experienceCount}</h3>
                        <button type="button" class="remove-btn" onclick="this.parentElement.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Cargo</label>
                            <input type="text" name="cargo[]" required>
                        </div>
                        <div class="form-group">
                            <label>Empresa</label>
                            <input type="text" name="empresa[]" required>
                        </div>
                        <div class="form-group">
                            <label>Data de Início</label>
                            <input type="month" name="inicio[]" required>
                        </div>
                        <div class="form-group">
                            <label>Data de Término</label>
                            <input type="month" name="fim[]">
                        </div>
                        <div class="form-group full-width">
                            <label>Descrição das Atividades</label>
                            <textarea name="descricao_experiencia[]" placeholder="Descreva suas principais responsabilidades e conquistas"></textarea>
                        </div>
                    </div>
                `;
                document.getElementById('experiences-container').appendChild(newExperience);
            });

            // --- LÓGICA DA FORMAÇÃO ---
            let educationCount = 1;
            document.getElementById('add-education').addEventListener('click', () => {
                educationCount++;
                const newEducation = document.createElement('div');
                newEducation.className = 'education-item';
                newEducation.innerHTML = `
                    <div class="item-header">
                        <h3>Formação #${educationCount}</h3>
                        <button type="button" class="remove-btn" onclick="this.parentElement.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Curso</label>
                            <input type="text" name="curso[]" required>
                        </div>
                        <div class="form-group">
                            <label>Instituição</label>
                            <input type="text" name="instituicao[]" required>
                        </div>
                        <div class="form-group">
                            <label>Data de Início</label>
                            <input type="month" name="inicio_curso[]" required>
                        </div>
                        <div class="form-group">
                            <label>Data de Conclusão</label>
                            <input type="month" name="fim_curso[]">
                        </div>
                    </div>
                `;
                document.getElementById('education-container').appendChild(newEducation);
            });

            // --- LÓGICA DOS IDIOMAS ---
            document.getElementById('add-language').addEventListener('click', () => {
                const newLanguage = document.createElement('div');
                newLanguage.className = 'form-grid';
                newLanguage.innerHTML = `
                    <div class="form-group">
                        <label>Idioma</label>
                        <input type="text" name="idioma[]" placeholder="Ex: Inglês">
                    </div>
                    <div class="form-group">
                        <label>Nível</label>
                        <select name="nivel_idioma[]">
                            <option value="Básico">Básico</option>
                            <option value="Intermediário">Intermediário</option>
                            <option value="Avançado">Avançado</option>
                            <option value="Fluente">Fluente</option>
                            <option value="Nativo">Nativo</option>
                        </select>
                    </div>
                `;
                document.getElementById('languages-container').appendChild(newLanguage);
            });

            // --- LÓGICA DE GERAÇÃO ---
            const generateBtn = document.getElementById('generate-btn');
            const iaResponseContainer = document.getElementById('ia-response-container');

            const handleGenerate = async () => {
                const form = document.getElementById('resume-form');
                const formData = new FormData(form);
                
                // Adicionar arquivos ao FormData
                for (const file of fileInput.files) {
                    formData.append('files[]', file);
                }

                generateBtn.disabled = true;
                iaResponseContainer.innerHTML = '<div class="loader-container"><div class="loader"></div><p>A IA está a analisar e a desenhar os seus currículos... Isto pode demorar um pouco.</p></div>';

                try {
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
        });
    </script>
</body>

</html>