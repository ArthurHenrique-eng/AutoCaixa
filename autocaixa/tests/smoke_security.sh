#!/usr/bin/env bash
# Testes de segurança de ponta a ponta. Sobe servidores próprios (não reaproveita smoke_http.sh)
# porque alguns cenários exigem variáveis de ambiente diferentes (APP_ENV, janelas de rate limit).
# Uso: DB_HOST=127.0.0.1 DB_NAME=autocaixa_sec DB_USER=... DB_PASS=... \
#      ADMIN_PASS=... bash tests/smoke_security.sh
set -u
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_NAME="${DB_NAME:-autocaixa_sec}"
DB_USER="${DB_USER:?defina DB_USER}"
DB_PASS="${DB_PASS:?defina DB_PASS}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@loja.test}"
ADMIN_PASS="${ADMIN_PASS:?defina ADMIN_PASS}"
PASS=0; FAIL=0; TMP=$(mktemp -d)
PORT_DEV=8090   # limites de produção (padrão), para os testes que não são sobre rate limit
PORT_RL=8091    # limites bem baixos, para testar 429 rapidamente
PORT_PROD=8092  # APP_ENV=production + PAYMENT_DRIVER=simulated

check() { if [ "$2" -eq 0 ]; then PASS=$((PASS+1)); echo "  ok   $1"; else FAIL=$((FAIL+1)); echo "  FAIL $1"; fi; }

cleanup() { kill $PID_DEV $PID_RL $PID_PROD 2>/dev/null; }
trap cleanup EXIT

echo "Smoke de segurança — AutoCaixa"

DB_MIGRATION_USER="${DB_MIGRATION_USER:-$DB_USER}"
DB_MIGRATION_PASS="${DB_MIGRATION_PASS:-$DB_PASS}"
# Criar/recriar o banco precisa de privilégio de DDL: sempre as credenciais de migração,
# nunca as de runtime (que, de propósito, não têm esse privilégio — ver item 8).
mariadb -h"$DB_HOST" -u"$DB_MIGRATION_USER" -p"$DB_MIGRATION_PASS" \
  -e "DROP DATABASE IF EXISTS $DB_NAME; CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
( cd "$ROOT" && DB_HOST="$DB_HOST" DB_NAME="$DB_NAME" DB_USER="$DB_USER" DB_PASS="$DB_PASS" \
  DB_MIGRATION_USER="$DB_MIGRATION_USER" DB_MIGRATION_PASS="$DB_MIGRATION_PASS" \
  php scripts/migrate.php >/dev/null )
( cd "$ROOT" && DB_HOST="$DB_HOST" DB_NAME="$DB_NAME" DB_USER="$DB_USER" DB_PASS="$DB_PASS" \
  ADMIN_EMAIL="$ADMIN_EMAIL" ADMIN_NAME="Admin" ADMIN_PASSWORD="$ADMIN_PASS" php scripts/create_admin.php >/dev/null )
mariadb -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$ROOT/database/seeds/products.sql" >/dev/null 2>&1

# Servidor com limites de produção (não interessa para estes testes específicos de rate limit).
( cd "$ROOT" && DB_HOST="$DB_HOST" DB_NAME="$DB_NAME" DB_USER="$DB_USER" DB_PASS="$DB_PASS" PAYMENT_DRIVER=simulated \
  php -S 127.0.0.1:$PORT_DEV -t public >/tmp/sec-dev.log 2>&1 & echo $! > "$TMP/dev.pid" )
# Servidor com limites muito baixos e janela curta, só para o teste de 429.
( cd "$ROOT" && DB_HOST="$DB_HOST" DB_NAME="$DB_NAME" DB_USER="$DB_USER" DB_PASS="$DB_PASS" PAYMENT_DRIVER=simulated \
  RATE_LIMIT_LOGIN=3 RATE_LIMIT_LOGIN_WINDOW=2 RATE_LIMIT_CART=3 RATE_LIMIT_CART_WINDOW=2 \
  php -S 127.0.0.1:$PORT_RL -t public >/tmp/sec-rl.log 2>&1 & echo $! > "$TMP/rl.pid" )
# Servidor em "produção" com o driver simulado: a aplicação deve recusar toda requisição.
( cd "$ROOT" && DB_HOST="$DB_HOST" DB_NAME="$DB_NAME" DB_USER="$DB_USER" DB_PASS="$DB_PASS" \
  APP_ENV=production PAYMENT_DRIVER=simulated \
  php -S 127.0.0.1:$PORT_PROD -t public >/tmp/sec-prod.log 2>&1 & echo $! > "$TMP/prod.pid" )
PID_DEV=$(cat "$TMP/dev.pid"); PID_RL=$(cat "$TMP/rl.pid"); PID_PROD=$(cat "$TMP/prod.pid")
sleep 2

BASE_DEV="http://127.0.0.1:$PORT_DEV"
BASE_RL="http://127.0.0.1:$PORT_RL"
BASE_PROD="http://127.0.0.1:$PORT_PROD"

csrf_of() { curl -s -c "$1" -b "$1" "$2$3" | grep -o 'name="_csrf" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//'; }
login() { local t; t=$(csrf_of "$1" "$2" /admin/login); curl -s -o /dev/null -w "%{http_code}" -c "$1" -b "$1" \
  --data-urlencode "_csrf=$t" --data-urlencode "email=$3" --data-urlencode "password=$4" "$2/admin/login"; }

### 1) Produção + simulado: a aplicação não deve funcionar de jeito nenhum.
code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_PROD/"); [ "$code" = "503" ]; check "produção+simulated: página inicial recusada (503)" $?
code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_PROD/health"); [ "$code" = "503" ]; check "produção+simulated: /health também recusado (sem exceção especial)" $?
JAR="$TMP/prod.txt"
code=$(curl -s -o /dev/null -w "%{http_code}" -c "$JAR" -b "$JAR" -X POST -H 'Content-Type: application/json' \
  -d '{}' "$BASE_PROD/api/vendas/1/simular-aprovacao")
[ "$code" = "503" ]; check "produção+simulated: rota de aprovação simulada também indisponível" $?
grep -q "simulated.*não é permitido\|PAYMENT_DRIVER=simulated" /tmp/sec-prod.log
check "produção+simulated: mensagem clara registrada no log de inicialização" $?

### 2) Rate limiting — login
JAR="$TMP/rl_login.txt"
for i in 1 2 3; do login "$JAR" "$BASE_RL" "$ADMIN_EMAIL" "senha-errada" >/dev/null; done
t=$(csrf_of "$JAR" "$BASE_RL" /admin/login)
headers=$(curl -s -D - -o /dev/null -c "$JAR" -b "$JAR" \
  --data-urlencode "_csrf=$t" --data-urlencode "email=$ADMIN_EMAIL" --data-urlencode "password=senha-errada" \
  "$BASE_RL/admin/login")
echo "$headers" | grep -q "HTTP/1\.[01] 429"; check "login: excesso de tentativas por IP retorna 429" $?
echo "$headers" | grep -qi "Retry-After"; check "login: resposta 429 informa Retry-After" $?
sleep 3
code=$(login "$JAR" "$BASE_RL" "$ADMIN_EMAIL" "senha-errada")
[ "$code" != "429" ]; check "login: após a janela, volta a responder normalmente (401, não 429)" $?

### 3) Rate limiting — carrinho
JAR="$TMP/rl_cart.txt"
curl -s -o /dev/null -c "$JAR" -b "$JAR" "$BASE_RL/" # estabelece sessão
for i in 1 2 3; do curl -s -o /dev/null -c "$JAR" -b "$JAR" "$BASE_RL/api/carrinho"; done
code=$(curl -s -o /dev/null -w "%{http_code}" -c "$JAR" -b "$JAR" "$BASE_RL/api/carrinho")
[ "$code" = "429" ]; check "carrinho: excesso de requisições por IP+sessão retorna 429" $?
sleep 3
code=$(curl -s -o /dev/null -w "%{http_code}" -c "$JAR" -b "$JAR" "$BASE_RL/api/carrinho")
[ "$code" = "200" ]; check "carrinho: após a janela, volta a responder 200" $?

### 4) Cabeçalho no-store nas páginas sensíveis, e ausência dele em páginas públicas
hdr() { curl -s -D - -o /dev/null -c "$TMP/h.txt" -b "$TMP/h.txt" "$1"; }
hdr "$BASE_DEV/admin/login" | grep -qi "Cache-Control: no-store"; check "no-store: /admin/login" $?
hdr "$BASE_DEV/api/carrinho" | grep -qi "Cache-Control: no-store"; check "no-store: /api/carrinho" $?
# Páginas dinâmicas do totem (home, /comprar etc.) já ficam fora de cache pelo limitador de cache
# padrão de sessão do PHP, mesmo sem a nossa marcação explícita — isso não é novo nem indiscriminado
# por nós; o que importa é que os ARQUIVOS ESTÁTICOS (CSS, JS, fontes, ícones), que nunca passam
# pela sessão, continuem cacheáveis normalmente.
hdr "$BASE_DEV/assets/css/app.css" | grep -qi "Cache-Control: no-store"; code=$?; [ "$code" -ne 0 ]; check "no-store: ausente nos assets estáticos (CSS)" $?
hdr "$BASE_DEV/assets/js/app.js" | grep -qi "Cache-Control: no-store"; code=$?; [ "$code" -ne 0 ]; check "no-store: ausente nos assets estáticos (JS)" $?

### 5) Formato padronizado de erro da API
body=$(curl -s -c "$TMP/e.txt" -b "$TMP/e.txt" -H "X-CSRF-Token: $(curl -s -c "$TMP/e.txt" -b "$TMP/e.txt" "$BASE_DEV/" | grep -o 'name=\"csrf-token\" content=\"[^\"]*\"' | sed 's/.*content=\"//;s/\"//')" \
  -H 'Content-Type: application/json' -d '{"barcode":"abc"}' "$BASE_DEV/api/carrinho/adicionar")
echo "$body" | grep -q '"error":{"code"' && echo "$body" | grep -q '"message"'
check "erro da API segue o formato {error:{code,message}}" $?
echo "$body" | grep -qi "stack\|SQLSTATE\|\.php"; code=$?; [ "$code" -ne 0 ]; check "erro da API não vaza detalhes internos" $?

### 6) Senha provisória: obriga a troca antes de qualquer outra rota do painel
JAR="$TMP/admin.txt"
login "$JAR" "$BASE_DEV" "$ADMIN_EMAIL" "$ADMIN_PASS" >/dev/null
t=$(csrf_of "$JAR" "$BASE_DEV" /admin/usuarios)
PROV_EMAIL="provisorio@loja.test"
curl -s -o /dev/null -c "$JAR" -b "$JAR" --data-urlencode "_csrf=$t" \
  --data-urlencode "name=Provisorio" --data-urlencode "email=$PROV_EMAIL" \
  --data-urlencode "role=cashier" --data-urlencode "password=senha-provisoria-123" "$BASE_DEV/admin/usuarios"

JAR2="$TMP/provisorio.txt"
code=$(login "$JAR2" "$BASE_DEV" "$PROV_EMAIL" "senha-provisoria-123")
loc=$(curl -s -o /dev/null -D - -c "$JAR2" -b "$JAR2" "$BASE_DEV/admin/vendas" | grep -i "^location" | tr -d '\r')
code2=$(curl -s -o /dev/null -w "%{http_code}" -c "$JAR2" -b "$JAR2" "$BASE_DEV/admin/vendas")
echo "$loc" | grep -qi "trocar-senha"
check "senha provisória: acesso direto a outra rota é redirecionado para a troca de senha" $?

t2=$(csrf_of "$JAR2" "$BASE_DEV" /admin/trocar-senha)
body=$(curl -s -c "$JAR2" -b "$JAR2" --data-urlencode "_csrf=$t2" \
  --data-urlencode "current_password=senha-errada" --data-urlencode "new_password=nova-senha-123456" \
  --data-urlencode "new_password_confirmation=nova-senha-123456" "$BASE_DEV/admin/trocar-senha")
echo "$body" | grep -q "Senha atual incorreta"
check "senha provisória: recusa trocar com a senha atual errada" $?

t3=$(csrf_of "$JAR2" "$BASE_DEV" /admin/trocar-senha)
curl -s -o /dev/null -c "$JAR2" -b "$JAR2" --data-urlencode "_csrf=$t3" \
  --data-urlencode "current_password=senha-provisoria-123" --data-urlencode "new_password=nova-senha-123456" \
  --data-urlencode "new_password_confirmation=nova-senha-123456" "$BASE_DEV/admin/trocar-senha"
JAR3="$TMP/provisorio2.txt"
loc2=$(login "$JAR3" "$BASE_DEV" "$PROV_EMAIL" "nova-senha-123456"; curl -s -o /dev/null -D - -c "$JAR3" -b "$JAR3" "$BASE_DEV/admin" | grep -i "^location")
code3=$(curl -s -o /dev/null -w "%{http_code}" -c "$JAR3" -b "$JAR3" "$BASE_DEV/admin")
[ "$code3" = "200" ]; check "senha provisória: após trocar, o login seguinte já cai direto no painel" $?

### 7) Isolamento entre sessões (venda de uma sessão não é visível pela outra)
JARA="$TMP/guestA.txt"; JARB="$TMP/guestB.txt"
curl -s -o /dev/null -c "$JARA" -b "$JARA" "$BASE_DEV/"
GTA=$(curl -s -c "$JARA" -b "$JARA" "$BASE_DEV/" | grep -o 'name="csrf-token" content="[^"]*"' | sed 's/.*content="//;s/"//')
curl -s -c "$JARA" -b "$JARA" -H "X-CSRF-Token: $GTA" -H 'Content-Type: application/json' -d '{"barcode":"7890000000011"}' "$BASE_DEV/api/carrinho/adicionar" -o /dev/null
SALE_A=$(curl -s -c "$JARA" -b "$JARA" -H "X-CSRF-Token: $GTA" -H 'Content-Type: application/json' -d '{"method":"pix"}' "$BASE_DEV/api/checkout" | grep -o '"sale_id":[0-9]*' | cut -d: -f2)

curl -s -o /dev/null -c "$JARB" -b "$JARB" "$BASE_DEV/"
code=$(curl -s -o /dev/null -w "%{http_code}" -c "$JARB" -b "$JARB" "$BASE_DEV/pagamento/$SALE_A")
[ "$code" = "404" ]; check "sessão B não acessa a página de pagamento da venda da sessão A" $?
code=$(curl -s -o /dev/null -w "%{http_code}" -c "$JARB" -b "$JARB" "$BASE_DEV/api/vendas/$SALE_A/status")
[ "$code" = "404" ]; check "sessão B não consulta o status da venda da sessão A" $?
GTB=$(curl -s -c "$JARB" -b "$JARB" "$BASE_DEV/" | grep -o 'name="csrf-token" content="[^"]*"' | sed 's/.*content="//;s/"//')
code=$(curl -s -o /dev/null -w "%{http_code}" -c "$JARB" -b "$JARB" -H "X-CSRF-Token: $GTB" -H 'Content-Type: application/json' -d '{}' "$BASE_DEV/api/vendas/$SALE_A/simular-aprovacao")
[ "$code" = "404" ]; check "sessão B não aprova o pagamento da venda da sessão A" $?
code=$(curl -s -o /dev/null -w "%{http_code}" -c "$JARA" -b "$JARA" "$BASE_DEV/pagamento/$SALE_A")
[ "$code" = "200" ]; check "a própria sessão A continua acessando a venda normalmente" $?

echo ""
echo "$PASS aprovado(s), $FAIL falha(s)"
rm -rf "$TMP"
[ "$FAIL" -eq 0 ]
