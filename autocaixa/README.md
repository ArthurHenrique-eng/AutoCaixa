# AutoCaixa Local

Sistema web de autoatendimento para pequenos comércios. O cliente registra os produtos com leitor de código de barras, confere o carrinho e paga. O totem é um computador Windows com Microsoft Edge em modo quiosque. A aplicação roda em PHP com MySQL.

> **Estado atual:** o fluxo de compra e o painel administrativo estão completos e testados.
> **Os pagamentos ainda estão em modo simulado.** Nenhuma cobrança real é feita.
> A integração bancária (Cielo: cartão pelo Cielo Conecta e Pix pela API Pix) é a última etapa do projeto, pela interface `PaymentGateway`.

## O que está pronto

**Totem (público)**
- Leitura de código de barras pelo campo de leitor, com Enter como terminador.
- Carrinho guardado no servidor. Preços, nomes e estoque são recalculados a cada operação.
- Limite de quantidade pelo estoque disponível, considerando reservas temporárias, e por item. Produtos inativos são recusados.
- Estoque é reservado durante o pagamento para evitar overselling entre checkouts concorrentes.
- Venda só é finalizada após confirmação do provedor, e o estoque é baixado uma única vez.
- Venda pertence à sessão que a criou. Outra sessão recebe 404.
- Carrinho é limpo após pagamento aprovado.
- Inatividade é aplicada no servidor e no navegador; após 120 segundos sem requisições do totem, o estado transitório da sessão é limpo.

**Painel administrativo (`/admin`)**
- Login com senha em hash (bcrypt), bloqueio após 5 falhas por 15 minutos, e nova sessão a cada login.
- Sessão do painel expira sozinha: 15 minutos sem uso ou 8 horas desde o login, o que vier primeiro. A sessão expirada é destruída (cookie limpo) e fica registrada na auditoria.
- Usuário criado pelo administrador recebe senha provisória; no primeiro acesso, antes de qualquer outra tela, precisa trocar a senha.
- Três perfis, com permissões verificadas no servidor a cada ação:
  - **Administrador:** tudo, inclusive usuários e auditoria.
  - **Gerente:** produtos, estoque, vendas, cancelamentos e relatórios.
  - **Operador:** somente consulta de vendas e início.
- Início com vendas do dia, aguardando pagamento, falhas e estoque baixo.
- Produtos: cadastro, edição, ativação e desativação. Validação de código, nome, preço e estoque mínimo.
- Estoque: entradas, ajustes de inventário e perdas, com motivo obrigatório. Estoque nunca fica negativo. Cada movimento registra o usuário.
- Vendas: filtro por período e situação, detalhes com pagamentos, e cancelamento de vendas aguardando pagamento com liberação da reserva.
- Relatórios: total por produto no período (somente vendas pagas), com exportação CSV protegida contra injeção de fórmulas em planilhas.
- Usuários: criação e desativação. Não é possível desativar a própria conta.
- Auditoria: login, falhas de login, alterações de produtos, estoque, vendas, usuários e exportações. Campos sensíveis são removidos antes de gravar.

**Infraestrutura**
- Proteção CSRF em todas as operações de escrita.
- Cabeçalhos de segurança: CSP, X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy e HSTS em HTTPS.
- Sessão endurecida: `use_strict_mode` e `use_only_cookies` ligados, cookie `HttpOnly`/`SameSite`/`Secure` em HTTPS.
- `Cache-Control: no-store` nas respostas da API e nas páginas de admin, carrinho, pagamento e comprovante.
- Pagamento simulado é bloqueado com `APP_ENV=production`: a aplicação recusa iniciar nessa combinação, e a rota de aprovação simulada só existe quando o driver simulado está mesmo em uso.
- Limitação de requisições (rate limiting) por IP em login, carrinho, checkout e geral, configurável por variável de ambiente — ver `docs/hospedagem.md`.
- Usuário de banco de runtime sem privilégio de DDL; só `scripts/migrate.php` usa credenciais com permissão de alterar schema.
- Erros da API num formato único: `{"error":{"code":"...","message":"..."}}`, sem detalhes internos.
- Configuração de Apache em `public/.htaccess`, com front controller e bloqueio de listagem de diretórios/arquivos ocultos.
- Endpoint `/health` para monitoramento.
- Script de backup com retenção, que recusa gravar dentro de `public/`.
- Código, configurações e logs ficam fora de `public/`.

## Requisitos

- PHP 8.3 ou superior. A versão alvo é 8.5.12, ou uma atualização de segurança posterior.
- MySQL 8 ou MariaDB 10.11 ou superior, com InnoDB.
- Extensões: `pdo_mysql` e `mbstring`.

## Instalação local

```bash
# 1. Banco e dois usuários: um de runtime (sem DDL) e um de migração (com DDL).
#    Em desenvolvimento local, usar só o de runtime com privilégios de DDL também é aceitável
#    (não defina DB_MIGRATION_USER/DB_MIGRATION_PASS); em produção, use os dois.
mysql -u root -p -e "CREATE DATABASE autocaixa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER 'autocaixa'@'localhost' IDENTIFIED BY 'troque-esta-senha';
  GRANT SELECT,INSERT,UPDATE,DELETE ON autocaixa.* TO 'autocaixa'@'localhost';
  CREATE USER 'autocaixa_migrate'@'localhost' IDENTIFIED BY 'troque-esta-outra-senha';
  GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,ALTER,INDEX,REFERENCES,DROP ON autocaixa.* TO 'autocaixa_migrate'@'localhost';"

# 2. Migrações (com o usuário de migração) e dados de desenvolvimento
export DB_PASS='troque-esta-senha' DB_MIGRATION_USER=autocaixa_migrate DB_MIGRATION_PASS='troque-esta-outra-senha'
php scripts/migrate.php
mysql -u autocaixa -p autocaixa < database/seeds/products.sql

# 3. Primeiro administrador (a senha vem do ambiente, não de argumentos)
ADMIN_EMAIL=dono@loja.com ADMIN_NAME="Dono" ADMIN_PASSWORD='senha-com-12-ou-mais' php scripts/create_admin.php

# 4. Servidor de desenvolvimento (não usar em produção)
PAYMENT_DRIVER=simulated php -S 127.0.0.1:8080 -t public
```

Acesse `http://127.0.0.1:8080` para o totem e `http://127.0.0.1:8080/admin` para o painel.

Códigos de teste: `7890000000011` (café), `7890000000028` (leite), `7890000000035` (pão) e `7890000000042` (açúcar).

## Testes

Cinco suítes, todas sem cobrança real:

```bash
# Regras de venda, estoque e finalização (15 testes) — usa o banco autocaixa_test
DB_NAME=autocaixa_test DB_PASS='...' php tests/run_tests.php

# Login, bloqueio, permissões e auditoria (11 testes) — usa o banco autocaixa_test
DB_PASS='...' php tests/run_admin_tests.php

# Guard de produção e resolução de IP, sem banco nem servidor (8 testes)
php tests/run_security_tests.php

# Ponta a ponta contra o servidor em execução (20 testes)
ADMIN_PASS='...' OPER_PASS='...' bash tests/smoke_http.sh

# Segurança de ponta a ponta: produção+simulado, rate limiting, sessões, no-store,
# erros padronizados e senha provisória (22 testes). Sobe os próprios servidores PHP.
DB_USER='...' DB_PASS='...' ADMIN_PASS='...' bash tests/smoke_security.sh
```

Para os testes de ponta a ponta, crie antes um administrador e um operador. O operador pode ser criado pelo painel, com o perfil "Operador". O `smoke_security.sh` cuida sozinho do próprio banco, administrador e servidores — só precisa de credenciais de banco.

**Observação:** `run_admin_tests.php` emite um aviso de `session_regenerate_id` quando executado no terminal, porque a CLI já imprimiu saída. O aviso não ocorre no servidor web.

## Estrutura

```
public/                       ponto de entrada, .htaccess, CSS e JS separados
app/Core/                     banco, CSRF, autenticação, auditoria, permissões, HTTP, views
app/Controllers/              páginas, API do totem e painel administrativo
app/Services/                 carrinho e finalização (regras de negócio)
app/Repositories/             acesso a produtos
app/Integrations/Payments/    interface PaymentGateway e driver simulado
app/Views/                    templates PHP (totem e admin/)
database/migrations/          001 (vendas/estoque), 002 (usuários/auditoria), 003 (reservas de estoque),
                               004 (senha provisória e limitador de requisições)
database/seeds/               produtos de desenvolvimento
config/                       configuração lida do ambiente (inclui rate limiting e credenciais de migração)
scripts/                      migração, criação de administrador e backup
tests/                        suítes automatizadas, smoke de ponta a ponta e smoke de segurança
docs/                         hospedagem, totem Windows e operação
```

## Documentação

- `docs/hospedagem.md`: requisitos, estrutura no servidor, publicação, backup, atualização e diagnóstico na Hostinger.
- `docs/totem-windows.md`: Edge em modo quiosque e Acesso Atribuído. **Não testado no equipamento real; confirme os argumentos na documentação da Microsoft.**
- `docs/operacao.md`: rotina diária e situações comuns da loja.
- `Prompt/`: políticas para agentes de IA, segurança, privacidade, arquitetura, testes, banco, pagamentos, documentação e workflow.

## Limitações conhecidas

- **Pagamentos simulados.** Cartão e Pix não cobram nada real. A integração bancária é a última etapa.
- **Sem webhook.** A confirmação é por consulta. O driver real deve adicionar notificações e validação de origem.
- **Sem estorno de venda paga.** Só cancelamento de venda aguardando pagamento. Estorno depende da integração bancária.
- **Resultado de pagamento desconhecido.** Se a comunicação com o provedor falhar em um ponto ambíguo, a cobrança fica em estado `unknown` e a reserva permanece até confirmação ou expiração; o sistema não marca a venda como paga nem libera a reserva prematuramente.
- **Sem comprovante fiscal.** Apenas comprovante interno.
- **Sem feedback sonoro.**
- **Sem recuperação de senha por e-mail.** Só a troca obrigatória da senha provisória; se a pessoa esquecer a senha definitiva, outro administrador precisa recriar a conta.
- **Rate limiting por banco, não por memória compartilhada (Redis).** Funciona bem para o volume de uma loja; se houver múltiplos servidores de aplicação atrás de um balanceador, todos compartilham o mesmo banco, então o limite continua correto — só não é tão rápido quanto um contador em memória.
- **Testes de interface não feitos:** navegador real, leitor físico, tela touch, Windows, Edge e quiosque.
- **Testado em PHP 8.3.6 neste ambiente.** Falta testar em PHP 8.5.12 e na hospedagem real.

## Próximas etapas

1. **Validação na hospedagem** (`docs/hospedagem.md`).
2. **Configuração e testes do totem** (`docs/totem-windows.md`), com leitor e tela touch.
3. **Integração bancária, por último:** Cielo para cartão e Pix, com credenciais de Sandbox, aderência e webhook.
