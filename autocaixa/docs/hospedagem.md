# Hospedagem — Hostinger (PHP gerenciado)

## Requisitos a confirmar antes de contratar ou publicar

- [ ] Versão PHP 8.5 selecionada no painel. Confirmar com o suporte se o patch 8.5.12 (ou posterior) já está aplicado. O painel mostra apenas a série.
- [ ] Extensões `pdo_mysql` e `mbstring` ativas.
- [ ] MySQL criado no painel, com usuário dedicado e permissões mínimas (SELECT, INSERT, UPDATE, DELETE, e DDL apenas durante a migração).
- [ ] SSL ativo no domínio (certificado gratuito do plano).
- [ ] Saída HTTPS a partir do servidor funcionando (teste com um `curl` para uma URL pública).
- [ ] Cron ou agendador disponível para o backup diário.
- [ ] Acesso ao arquivo de logs de erro do PHP fora de `public/`.

## Estrutura no servidor

Coloque o projeto **acima** de `public_html`, e aponte o domínio para a pasta `public/`:

```
home/usuario/
├── autocaixa/            ← código, config, migrações, logs (NÃO público)
│   ├── app/
│   ├── config/
│   ├── database/
│   └── ...
├── backups/autocaixa/    ← backups (NÃO público)
└── public_html/          ← conteúdo de autocaixa/public/ (apenas os arquivos públicos)
```

Se a Hostinger não permitir apontar o domínio para `public/`, copie apenas o conteúdo de `public/` para `public_html/` e mantenha o restante fora. Nesse caso, ajuste os caminhos `require` do `public/index.php` para apontar para `../autocaixa`.

## Variáveis de ambiente

Defina no painel (ou em `.htaccess` com `SetEnv`, sem versionar):

| Variável | Valor de produção |
|---|---|
| `APP_DEBUG` | `false` |
| `DB_HOST` | host fornecido pela Hostinger |
| `DB_NAME` | nome do banco criado |
| `DB_USER` | usuário dedicado |
| `DB_PASS` | senha forte, guardada somente no painel |
| `PAYMENT_DRIVER` | `simulated` até a integração bancária (Fase 4) |

**Atenção:** com `PAYMENT_DRIVER=simulated`, o sistema NÃO cobra nada real. Não use em produção para vendas reais enquanto a Fase 4 não for concluída.

## Publicação

1. Envie o projeto (por Git, se disponível, ou pelo gerenciador de arquivos).
2. Rode as migrações: `php scripts/migrate.php` (com as variáveis de ambiente de produção).
3. Crie o primeiro administrador com `ADMIN_PASSWORD` em variável de ambiente, e não como argumento:
   `ADMIN_EMAIL=... ADMIN_NAME="..." ADMIN_PASSWORD='...' php scripts/create_admin.php`
4. Carregue os produtos pelo painel, ou rode `database/seeds/products.sql` apenas em homologação.
5. Verifique `https://seu-dominio/health` — deve retornar `{"status":"ok"}`.
6. Acesse `https://seu-dominio/admin` e troque o acesso inicial.

## Backup

Agende diariamente, pelo cron da Hostinger:

```
DB_PASS='...' DB_NAME=autocaixa DB_USER=autocaixa BACKUP_DIR=/home/usuario/backups/autocaixa \
RETENTION_DAYS=14 bash /home/usuario/autocaixa/scripts/backup.sh
```

**Restauração:** `gzip -dc arquivo.sql.gz | mysql -h HOST -u USUARIO -p NOME_DO_BANCO`.
Teste a restauração em um banco separado, pelo menos uma vez antes de confiar no backup.

## Atualização

1. Faça backup antes de qualquer atualização.
2. Envie os arquivos alterados.
3. Rode `php scripts/migrate.php`. As migrações usam `CREATE TABLE IF NOT EXISTS`, mas migrações de alteração (como a 002) devem ser conferidas antes de rodar de novo.
4. Verifique `/health` e faça uma compra de teste em `PAYMENT_DRIVER=simulated`.

## Diagnóstico

- Erros de aplicação ficam no log de erros do PHP, sem detalhes para o cliente.
- Falhas de banco resultam em `/health` diferente de `ok` e tela de "Serviço indisponível".
- Bloqueio de login: aguarde 15 minutos ou corrija diretamente pelo painel (perfil administrador).
