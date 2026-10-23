# Contexto do projeto — AutoCaixa

> Fonte de contexto técnico para agentes de IA. Atualize este arquivo quando arquitetura, comandos ou ambientes mudarem. Não registre segredos nem credenciais reais.

## Produto
- **Nome do projeto:** AutoCaixa Local
- **Objetivo principal:** sistema web de autoatendimento para pequenos comércios, permitindo leitura de código de barras, carrinho, pagamento e operação administrativa.
- **Usuários e casos de uso principais:** cliente no totem; administradores, gerentes e operadores no painel `/admin`.
- **O que está fora do escopo atual:** cobrança bancária real, estorno automático, comprovante fiscal e testes completos com equipamento físico de totem.
- **Ambientes em uso:** desenvolvimento local; pagamento atualmente simulado. Produção real deve ser tratada como etapa futura.

## Stack
- **Backend:** PHP 8.3+; versão alvo 8.5.12 ou patch de segurança posterior.
- **Frontend:** HTML5 + templates PHP, CSS e JavaScript sem framework de build.
- **Banco de dados:** MySQL 8 ou MariaDB 10.11+, InnoDB, utf8mb4.
- **Infraestrutura/hospedagem:** Apache é suportado pela configuração em `public/.htaccess`; desenvolvimento também pode usar o servidor embutido do PHP.
- **Dependências críticas:** PDO MySQL, mbstring e o provedor implementado pela interface `PaymentGateway`.

## Arquitetura
- **Ponto de entrada:** `autocaixa/public/index.php`.
- **Módulos/camadas principais:** `app/Core`, `app/Controllers`, `app/Services`, `app/Repositories`, `app/Integrations/Payments`, `app/Views`.
- **Fluxos sensíveis:** autenticação administrativa, permissões, CSRF, checkout, estado de pagamento, reserva/baixa de estoque, cancelamento e auditoria.
- **Fonte de verdade das decisões:** código atual + `autocaixa/Prompt/` + documentação em `autocaixa/docs/` e README quando não houver conflito. Fatos sobre infraestrutura devem ser confirmados antes de serem tratados como definitivos.
- **Convenções importantes:** valores monetários em centavos; consultas parametrizadas; decisões de preço, pagamento e estoque no servidor; permissões verificadas por ação; alterações críticas transacionais.

## Execução e validação
- **Instalação:** conforme `autocaixa/README.md`.
- **Desenvolvimento:** `PAYMENT_DRIVER=simulated php -S 127.0.0.1:8080 -t public` dentro de `autocaixa/`.
- **Testes:** `DB_NAME=autocaixa_test DB_PASS='...' php tests/run_tests.php`; `DB_PASS='...' php tests/run_admin_tests.php`; e `ADMIN_PASS='...' OPER_PASS='...' bash tests/smoke_http.sh` com servidor ativo.
- **Lint/estático:** não há ferramenta de lint/estático configurada no repositório neste momento.
- **Build:** não há etapa de build frontend.
- **Migrations:** `php scripts/migrate.php` dentro de `autocaixa/`.
- **Observação:** os comandos acima são os comandos documentados pelo próprio projeto; a execução real deve ser registrada pelo agente quando realizada.

## Segurança e privacidade
- **Autenticação:** sessão PHP no painel administrativo; senhas armazenadas com hash bcrypt conforme documentação do projeto.
- **Autorização/perfis:** permissões verificadas no servidor em cada ação administrativa.
- **Isolamento de dados:** vendas públicas ficam vinculadas aos IDs de venda armazenados na sessão que as criou.
- **Dados pessoais ou sensíveis tratados:** dados administrativos de conta e registros de auditoria; não copiar credenciais reais para documentação ou testes.
- **Segredos e configuração:** variáveis de ambiente/configuração local; nunca registrar valores de credenciais em Git.
- **Política de cookies/analytics:** sem analytics de terceiros identificado no código atual; cookie de sessão é HttpOnly e SameSite=Lax, com Secure em HTTPS.
- **Riscos conhecidos:** pagamento ainda simulado; integração real, webhook e estorno ainda não implementados; ambiente físico do totem ainda requer validação; testes em PHP 8.5.12 ainda precisam ser confirmados.

## Regras operacionais
- **Quem pode autorizar releases:** decisão do responsável pelo projeto; agentes não devem publicar ou executar operações destrutivas sem autorização explícita.
- **Operações que exigem confirmação:** deploy/publicação, alterações de infraestrutura, migrações destrutivas, exclusões, alterações de branch e integração com pagamentos reais.
- **Backup/recuperação:** `scripts/backup.sh`, com retenção definida pelo script e backups fora de `public/`.
- **Restrições de ambiente:** pagamento real não deve ser ativado nesta fase; o driver simulado permanece intencionalmente durante desenvolvimento.

## Decisões pendentes e suposições
- Integração Cielo para cartão/Pix e webhook serão definidos em fase posterior.
- A política de estorno automático ainda depende do provedor real.
- O tempo de reserva de estoque e de inatividade do totem está padronizado em 120 segundos nesta implementação e deve ser revisto se o fluxo operacional exigir outro prazo.
