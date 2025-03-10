## Запуск приложения

```
docker compose up -d
```

При первом запуске docker-контейнеров автоматически генерируются 500000 промокодов.
Дополнительные промокоды можно сгенерировать командой:

```
docker compose exec php php /var/www/app/cli/generate_promocodes.php <count>
```

где `<count>` количество промокодов от 1 до 1000000.  

## Веб интерфейс

- Страница выдачи промокодов: http://localhost:8000
- Список промокодов (в начале списка выданные промокоды): http://localhost:8000/promocodes.php

## База данных

### Таблица `promocode`
```
code            BINARY(6) NOT NULL PRIMARY KEY
issue_date      TIMESTAMP NULL
user_uuid       BINARY(16) NULL
user_ip         VARBINARY(16) NULL
```
- `code` промокод из 8 символов из алфавита Base64, который состоит из символов `A-Za-z0-9/+`. 
Base64 обеспечивает неплохую читаемость для пользователя, а также простоту генерации:
любые 6 байтов являются валидной 8-символьной строкой Base64.
- `issue_date` дата выдачи промокода пользователю.  
- `user_uuid` идентификатор пользователя.
- `user_ip` ip пользователя, поддерживаются IPv4 и IPv6.

Индексы:
```
UNIQUE INDEX    idx_user_uuid (user_uuid),
INDEX           idx_user_ip (user_ip)
```
### Процедура `GENERATE_PROMOCODES`
Генерирует промокоды с использованием временной таблицы.
Один из наиболее быстрых вариантов генерации большого количества записей. Вызов:
```
CALL GENERATE_PROMOCODES(5e5);
```