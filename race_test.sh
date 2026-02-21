#!/bin/bash

RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'

BASE_URL="http://localhost:5173"
API_URL="$BASE_URL/api"
COOKIE_JAR="cookies.txt"
REQUEST_ID="$1"
MASTER_EMAIL="${2:-master1@example.com}"
MASTER_PASSWORD="${3:-password}"

if [ -z "$REQUEST_ID" ]; then
    echo "Использование: $0 <request_id> [email] [password]"
    echo "Пример: $0 1"
    exit 1
fi

echo "=== Тестирование гонки для заявки ID=$REQUEST_ID ==="
echo "Мастер: $MASTER_EMAIL"

rm -f "$COOKIE_JAR"

# URL-декодирование
urldecode() {
    local data="${*//+/ }"
    printf '%b' "${data//%/\\x}"
}

# 1. CSRF-кука
echo "--- 1. Получение CSRF-куки..."
HTTP_CODE=$(curl -s -c "$COOKIE_JAR" -w "%{http_code}" "$BASE_URL/sanctum/csrf-cookie" -o /dev/null)
if [ "$HTTP_CODE" -ne 204 ] && [ "$HTTP_CODE" -ne 200 ]; then
    echo -e "${RED}Ошибка CSRF: HTTP $HTTP_CODE${NC}"
    exit 1
fi
echo "   OK (HTTP $HTTP_CODE)"

# Извлекаем XSRF-TOKEN и декодируем
XSRF_TOKEN_RAW=$(grep XSRF-TOKEN "$COOKIE_JAR" | tail -1 | awk '{print $7}')
if [ -z "$XSRF_TOKEN_RAW" ]; then
    echo -e "${RED}Не удалось извлечь XSRF-TOKEN${NC}"
    exit 1
fi
XSRF_TOKEN=$(urldecode "$XSRF_TOKEN_RAW")

# 2. Логин мастера
echo "--- 2. Логин мастера..."
RESP_BODY=$(mktemp)
HTTP_CODE=$(curl -s -b "$COOKIE_JAR" -c "$COOKIE_JAR" -X POST "$API_URL/login" \
    -H "Content-Type: application/json" \
    -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
    -H "Origin: $BASE_URL" \
    -H "Referer: $BASE_URL" \
    -d "{\"email\":\"$MASTER_EMAIL\",\"password\":\"$MASTER_PASSWORD\"}" \
    -w "%{http_code}" \
    -o "$RESP_BODY")

if [ "$HTTP_CODE" -ne 200 ]; then
    echo -e "${RED}Ошибка входа: HTTP $HTTP_CODE${NC}"
    echo "Тело ответа:"
    cat "$RESP_BODY"
    rm -f "$RESP_BODY"
    read -n 1 -s -r -p "Press any key to exit..."
    echo ""
    exit 1
fi
rm -f "$RESP_BODY"
echo -e "${GREEN}   Успешный вход${NC}"

# Обновляем XSRF-TOKEN после логина (если изменился)
XSRF_TOKEN_RAW=$(grep XSRF-TOKEN "$COOKIE_JAR" | tail -1 | awk '{print $7}')
XSRF_TOKEN=$(urldecode "$XSRF_TOKEN_RAW")

# 3. Два параллельных запроса на взятие заявки
echo "--- 3. Запуск двух параллельных запросов..."
tmp1=$(mktemp)
tmp2=$(mktemp)

curl -s -b "$COOKIE_JAR" -w "%{http_code}" -X POST "$API_URL/requests/$REQUEST_ID/take" \
    -H "Content-Type: application/json" \
    -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
    -H "Origin: $BASE_URL" \
    -H "Referer: $BASE_URL" \
    -o /dev/null > "$tmp1" &
pid1=$!

curl -s -b "$COOKIE_JAR" -w "%{http_code}" -X POST "$API_URL/requests/$REQUEST_ID/take" \
    -H "Content-Type: application/json" \
    -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
    -H "Origin: $BASE_URL" \
    -H "Referer: $BASE_URL" \
    -o /dev/null > "$tmp2" &
pid2=$!

wait $pid1
wait $pid2

code1=$(cat "$tmp1")
code2=$(cat "$tmp2")
rm -f "$tmp1" "$tmp2"

echo "   Результат 1: HTTP $code1"
echo "   Результат 2: HTTP $code2"

# Проверка: один 200, другой 409
if { [ "$code1" = "200" ] && [ "$code2" = "409" ]; } || { [ "$code1" = "409" ] && [ "$code2" = "200" ]; }; then
    echo -e "${GREEN}SUCCESS: Гонка обработана корректно${NC}"
else
    echo -e "${RED}FAIL: Ожидались 200 и 409, но получены $code1 и $code2${NC}"
fi

read -n 1 -s -r -p "Press any key to exit..."
echo ""