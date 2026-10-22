#!/usr/bin/env bash
# Testes de ponta a ponta contra o servidor em execução. Uso: BASE=http://127.0.0.1:8080 ADMIN_PASS=... OPER_PASS=... bash tests/smoke_http.sh
set -u
BASE="${BASE:-http://127.0.0.1:8080}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@loja.test}"; ADMIN_PASS="${ADMIN_PASS:?defina ADMIN_PASS}"
OPER_EMAIL="${OPER_EMAIL:-operador@loja.test}"; OPER_PASS="${OPER_PASS:?defina OPER_PASS}"
PASS=0; FAIL=0; TMP=$(mktemp -d)
BC=$(date +%s%N | cut -c1-13)

check() { # nome, condição (0 = ok)
  if [ "$2" -eq 0 ]; then PASS=$((PASS+1)); echo "  ok   $1"; else FAIL=$((FAIL+1)); echo "  FAIL $1"; fi
}
csrf_of() { curl -s -c "$1" -b "$1" "$BASE$2" | grep -o 'name="_csrf" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//'; }
login() { # jar, email, senha
  local t; t=$(csrf_of "$1" /admin/login)
  curl -s -o /dev/null -w "%{http_code}" -c "$1" -b "$1" --data-urlencode "_csrf=$t" \
       --data-urlencode "email=$2" --data-urlencode "password=$3" "$BASE/admin/login"
}
body() { curl -s -c "$1" -b "$1" "$BASE$2"; }
post() { # jar, path, extra form args...
  local jar="$1" path="$2"; shift 2
  local t; t=$(csrf_of "$jar" /admin/login)
  curl -s -c "$jar" -b "$jar" -H "Cookie: $(grep -h PHPSESSID "$jar" | awk '{print "PHPSESSID="$7}')" \
       --data-urlencode "_csrf=$(csrf_of "$jar" /admin/login)" "$@" "$BASE$path"
}

echo "Smoke HTTP — painel AutoCaixa"
ADM="$TMP/admin.txt"; OPR="$TMP/oper.txt"; ANON="$TMP/anon.txt"

code=$(curl -s -o /dev/null -w "%{http_code}" -c "$ANON" -b "$ANON" "$BASE/admin"); [ "$code" = "303" ] || [ "$code" = "302" ]; check "sem login, /admin redireciona" $?

code=$(login "$ANON" "$ADMIN_EMAIL" "senha-errada-xyz"); [ "$code" = "401" ]; check "senha errada retorna 401" $?

code=$(login "$ADM" "$ADMIN_EMAIL" "$ADMIN_PASS"); [ "$code" = "303" ]; check "login do administrador redireciona" $?

curl -s -c "$ADM" -b "$ADM" "$BASE/admin/produtos" | grep -q "Produtos"; check "administrador acessa a lista de produtos" $?

t=$(csrf_of "$ADM" /admin/produtos)
curl -s -c "$ADM" -b "$ADM" --data-urlencode "_csrf=$t" -d "barcode=ABC&name=Teste&price=1,00" "$BASE/admin/produtos" -o /dev/null
body "$ADM" /admin/produtos | grep -q "Código de barras deve conter apenas números"; check "código com letras é recusado no servidor" $?

t=$(csrf_of "$ADM" /admin/produtos)
curl -s -c "$ADM" -b "$ADM" --data-urlencode "_csrf=$t" -d "barcode=$BC&name=Teste%20Smoke&price=4,50&min_stock=2&active=1" "$BASE/admin/produtos" -o /dev/null
body "$ADM" /admin/produtos | grep -q "Produto cadastrado"; check "produto válido é cadastrado" $?

PID=$(mariadb -N autocaixa -e "SELECT id FROM products WHERE barcode='$BC'")
t=$(csrf_of "$ADM" /admin/produtos)
curl -s -c "$ADM" -b "$ADM" --data-urlencode "_csrf=$t" -d "delta=-5&reason=entrada" "$BASE/admin/produtos/$PID/estoque" -o /dev/null
body "$ADM" /admin/produtos | grep -q "Entrada deve ter quantidade positiva"; check "entrada negativa é recusada" $?

t=$(csrf_of "$ADM" /admin/produtos)
curl -s -c "$ADM" -b "$ADM" --data-urlencode "_csrf=$t" -d "delta=-999&reason=perda" "$BASE/admin/produtos/$PID/estoque" -o /dev/null
body "$ADM" /admin/produtos | grep -q "não pode ficar negativo"; check "estoque não pode ficar negativo" $?

t=$(csrf_of "$ADM" /admin/produtos)
curl -s -c "$ADM" -b "$ADM" --data-urlencode "_csrf=$t" -d "delta=10&reason=entrada" "$BASE/admin/produtos/$PID/estoque" -o /dev/null
st=$(mariadb -N autocaixa -e "SELECT stock FROM products WHERE id=$PID"); [ "$st" = "10" ]; check "entrada de 10 unidades atualiza o estoque para 10" $?

mov=$(mariadb -N autocaixa -e "SELECT COUNT(*) FROM stock_movements WHERE product_id=$PID AND user_id IS NOT NULL"); [ "$mov" -ge 1 ]; check "movimentação de estoque registra o usuário" $?

t=$(csrf_of "$ADM" /admin/produtos)
curl -s -c "$ADM" -b "$ADM" -d "barcode=1&name=Sem%20token&price=1" "$BASE/admin/produtos" -o /dev/null
body "$ADM" /admin/produtos | grep -q "Sessão expirada"; check "POST sem token CSRF é recusado" $?

code=$(login "$OPR" "$OPER_EMAIL" "$OPER_PASS"); [ "$code" = "303" ]; check "operador consegue entrar" $?
code=$(curl -s -o /dev/null -w "%{http_code}" -c "$OPR" -b "$OPR" "$BASE/admin/produtos"); [ "$code" = "403" ]; check "operador recebe 403 em produtos" $?
code=$(curl -s -o /dev/null -w "%{http_code}" -c "$OPR" -b "$OPR" "$BASE/admin/usuarios"); [ "$code" = "403" ]; check "operador recebe 403 em usuários" $?
code=$(curl -s -o /dev/null -w "%{http_code}" -c "$OPR" -b "$OPR" "$BASE/admin/auditoria"); [ "$code" = "403" ]; check "operador recebe 403 em auditoria" $?
body "$OPR" /admin/vendas | grep -q "Vendas"; check "operador consegue ver vendas" $?

csv=$(curl -s -c "$ADM" -b "$ADM" "$BASE/admin/relatorios/csv"); echo "$csv" | grep -q "Produto;Quantidade;Receita"; check "exportação CSV gera o cabeçalho esperado" $?

body "$ADM" /admin/auditoria | grep -q "product_created"; check "auditoria registra o cadastro do produto" $?

# Venda aguardando pagamento cancelada pelo administrador, sem alterar estoque
GUEST="$TMP/guest.txt"; curl -s -o /dev/null -c "$GUEST" -b "$GUEST" "$BASE/"
GT=$(curl -s -c "$GUEST" -b "$GUEST" "$BASE/" | grep -o 'name="csrf-token" content="[^"]*"' | sed 's/.*content="//;s/"//')
curl -s -c "$GUEST" -b "$GUEST" -H "X-CSRF-Token: $GT" -H 'Content-Type: application/json' -d '{"barcode":"7890000000011"}' "$BASE/api/carrinho/adicionar" -o /dev/null
SALE=$(curl -s -c "$GUEST" -b "$GUEST" -H "X-CSRF-Token: $GT" -H 'Content-Type: application/json' -d '{"method":"pix"}' "$BASE/api/checkout" | grep -o '"sale_id":[0-9]*' | cut -d: -f2)
before=$(mariadb -N autocaixa -e "SELECT stock FROM products WHERE barcode='7890000000011'")
t=$(csrf_of "$ADM" /admin/vendas)
curl -s -c "$ADM" -b "$ADM" --data-urlencode "_csrf=$t" "$BASE/admin/vendas/$SALE/cancelar" -o /dev/null
s=$(mariadb -N autocaixa -e "SELECT status FROM sales WHERE id=$SALE"); [ "$s" = "cancelled" ]; check "venda pendente pode ser cancelada pelo administrador" $?
after=$(mariadb -N autocaixa -e "SELECT stock FROM products WHERE barcode='7890000000011'"); [ "$before" = "$after" ]; check "cancelamento não altera o estoque" $?

echo ""
echo "$PASS aprovado(s), $FAIL falha(s)"
rm -rf "$TMP"
[ "$FAIL" -eq 0 ]
