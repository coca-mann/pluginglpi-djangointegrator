# CONTEXTO DO PROJETO: Integração GLPI + Django (SSO)

## 1. Visão Geral
O objetivo é criar um plugin para GLPI 11 chamado `djangointegrator`.
Este plugin cria um menu que carrega um Iframe apontando para uma aplicação Django externa.
A autenticação deve ser transparente (SSO) usando HMAC-SHA256.

## 2. Estrutura de Pastas Atual
Estamos na raiz do plugin: `glpi/plugins/djangointegrator/`.
- Documentação técnica: `glpi-developer-documentation.pdf` (Disponível para leitura).
- Backend Django: Está em OUTRO diretório (não acessível para escrita direta).

## 3. Regras de Comportamento (CRÍTICO)
- **PHP:** Você PODE e DEVE ler/editar/criar arquivos `.php` nesta pasta.
- **Python:** Você NÃO PODE criar arquivos `.py` aqui. Se precisar gerar código Django (views, models), exiba-o no chat em blocos de código para cópia manual.
- **Estilo:** Mantenha o plugin GLPI minimalista. Sem tabelas de configuração complexas agora. Use constantes no código.

## 4. Arquitetura da Solução
### Lado GLPI (PHP)
- Hook para criar menu em "Ferramentas".
- Página principal (`front/main.php` ou similar):
    - Pega ID do usuário (`Session::getLoginUserID`) e dados básicos.
    - Gera JSON: `{'uid': 1, 'email': '...', 'ts': 17000000}`.
    - Assina com HMAC-SHA256 usando `SECRET_KEY`.
    - Renderiza Iframe: `<iframe src="DJANGO_URL?payload=...&sig=...">`.

### Lado Django (Python)
- **Model:** `GlpiProfile` (OneToOne com User + campo `glpi_id`).
- **View:**
    - Valida assinatura e Timestamp.
    - Busca `GlpiProfile` pelo ID.
    - Se não existir: Cria User (senha random) + Profile.
    - Loga o usuário (`login(request, user)`).

## 5. Tarefa Atual
Analise a estrutura de arquivos atual e o PDF de documentação.
Implemente o `setup.php` e a página do Iframe conforme as regras acima.