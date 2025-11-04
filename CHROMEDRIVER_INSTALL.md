# 🔧 Установка ChromeDriver на production сервере

## Ошибка
```
"chromedriver" binary not found. Install it using the package manager of your operating system 
or by running "composer require --dev dbrekelmans/bdi && vendor/bin/bdi detect drivers"
```

## 📋 Решение для Linux хостинга

### Вариант 1: Автоматическая установка через Composer (РЕКОМЕНДУЕТСЯ)

```bash
# 1. Подключитесь к серверу по SSH
ssh your_user@your_server

# 2. Перейдите в директорию проекта
cd /home/g/gamechann2/im-edok_ru

# 3. Найдите путь к composer (попробуйте один из вариантов)
which composer          # Вариант 1
which composer.phar     # Вариант 2
ls -la ~/composer.phar  # Вариант 3
php -r "echo shell_exec('which composer');"  # Вариант 4

# Обычно на хостинге это один из:
# - /usr/local/bin/composer
# - /usr/bin/composer  
# - ~/composer.phar
# - php composer.phar

# 4. Установите BDI (используйте найденный путь к composer)
# Если composer не найден, используйте АЛЬТЕРНАТИВУ ниже!
php composer.phar require --dev dbrekelmans/bdi
# ИЛИ
/usr/local/bin/composer require --dev dbrekelmans/bdi

# 5. Автоматически скачать ChromeDriver
vendor/bin/bdi detect drivers

# 5. Проверка
which chromedriver
# или
ls -la vendor/bin/chromedriver
```

### Вариант 2: Ручная установка

```bash
# 1. Проверьте версию Chrome (если установлен)
google-chrome --version
# или
chromium-browser --version

# 2. Скачайте соответствующую версию ChromeDriver
# Для Chrome 119: https://chromedriver.chromium.org/downloads
cd /tmp
wget https://chromedriver.storage.googleapis.com/LATEST_RELEASE
LATEST=$(cat LATEST_RELEASE)
wget https://chromedriver.storage.googleapis.com/$LATEST/chromedriver_linux64.zip

# 3. Распакуйте
unzip chromedriver_linux64.zip

# 4. Переместите в проект
mv chromedriver /home/g/gamechann2/im-edok_ru/vendor/bin/
chmod +x /home/g/gamechann2/im-edok_ru/vendor/bin/chromedriver

# 5. Проверка
/home/g/gamechann2/im-edok_ru/vendor/bin/chromedriver --version
```

### Вариант 3: Через пакетный менеджер (если есть root доступ)

```bash
# Debian/Ubuntu
sudo apt-get update
sudo apt-get install -y chromium-chromedriver

# CentOS/RHEL
sudo yum install -y chromium-chromedriver
```

## ⚠️ АЛЬТЕРНАТИВА: Использовать обычный парсер БЕЗ браузера (РЕКОМЕНДУЕТСЯ!)

Если на хостинге **нет composer** или **нет возможности** установить ChromeDriver:

### ✅ ПРОСТОЕ РЕШЕНИЕ: Используйте `recipes:parse-infinite`

Эта команда работает **БЕЗ ChromeDriver** и **БЕЗ headless браузера**!

### Измените CRON задачу:

**Было:**
```bash
*/15 * * * * cd /home/g/gamechann2/im-edok_ru && php artisan recipes:collect-urls --count=100 --use-browser
```

**Станет:**
```bash
# Используем бесконечный парсер с пагинацией (БЕЗ браузера)
*/15 * * * * cd /home/g/gamechann2/im-edok_ru && php artisan recipes:parse-infinite --max=30 --batch=5
```

### Преимущества:
- ✅ Не требует ChromeDriver
- ✅ Работает через обычный HTTP
- ✅ Поддерживает пагинацию через offset
- ✅ Меньше нагрузка на сервер

### Недостатки:
- ⚠️ Не работает со страницами требующими JavaScript
- ⚠️ Но для 1000.menu/cooking/all-new это работает!

## 🎯 Рекомендация

Для вашего случая **лучше использовать Вариант с АЛЬТЕРНАТИВОЙ**:

1. Не нужно устанавливать ChromeDriver
2. Меньше ресурсов сервера
3. Команда `recipes:parse-infinite` уже протестирована
4. Работает с пагинацией 1000.menu

## 🔄 Быстрое решение ПРЯМО СЕЙЧАС

**Рекомендация:** Не тратьте время на установку ChromeDriver, используйте готовое решение!

```bash
# 1. SSH на сервер
ssh gamechann2@vh303.timeweb.ru

# 2. Откройте crontab
crontab -e

# 3. Измените строку с recipes:collect-urls на recipes:parse-infinite:

# БЫЛО (НЕ РАБОТАЕТ - требует ChromeDriver):
# */15 * * * * cd /home/g/gamechann2/im-edok_ru && php artisan recipes:collect-urls --count=100 --use-browser

# СТАЛО (РАБОТАЕТ БЕЗ ChromeDriver):
*/15 * * * * cd /home/g/gamechann2/im-edok_ru && php artisan recipes:parse-infinite --max=30 --batch=5

# 4. Сохраните:
# - Нажмите ESC
# - Введите :wq и нажмите Enter
# ИЛИ если используете nano:
# - Ctrl+O (сохранить)
# - Enter
# - Ctrl+X (выход)

# 5. Проверьте что работает прямо сейчас
cd /home/g/gamechann2/im-edok_ru
php artisan recipes:parse-infinite --max=5 --batch=5
```

## 🔍 Если всё же нужен Composer

```bash
# Попробуйте найти composer
which composer
which composer.phar
ls -la ~/composer.phar
ls -la /usr/local/bin/composer
ls -la /usr/bin/composer

# Проверьте версию PHP
php -v

# Если composer не найден, скачайте его
cd /home/g/gamechann2/im-edok_ru
curl -sS https://getcomposer.org/installer | php
# Теперь используйте: php composer.phar вместо composer
```

## 📊 Результат

После изменения в логах будет:
```
[2025-11-04 20:30:00] production.INFO: 🚀 Запуск бесконечного парсинга {"max":30,"batch":5} 
[2025-11-04 20:30:05] production.INFO: 📄 Страница #1 обработана {"url":"https://1000.menu/cooking/all-new?offset=0"} 
[2025-11-04 20:30:10] production.INFO: ✅ Партия из 5 рецептов добавлена в БД
```

Вместо:
```
[2025-11-04 20:11:24] production.ERROR: ❌ Ошибка парсинга с infinite scroll: "chromedriver" binary not found
```

## 🧪 Тестирование

```bash
# Тест на сервере
php artisan recipes:parse-infinite --max=5 --batch=5

# Должен вывести статистику успешного парсинга
```

---

**Вывод:** Используйте `recipes:parse-infinite` вместо `recipes:collect-urls --use-browser` - это проще и не требует ChromeDriver!
