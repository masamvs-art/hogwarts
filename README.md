# Hogwarts CRUD

Учебный PHP CRUD-проект в стиле Hogwarts для работы с БД `hogwarts` без фреймворков и без ORM.

## Стек

- PHP 8.x (процедурный стиль)
- MySQL 8.0
- `mysqli` (без PDO)
- Bootstrap 5 (CDN)
- Кастомный CSS (`assets/css/hogwarts.css`)

## Структура проекта

```text
hogwarts.local/
├── .env
├── .env.example
├── config.php
├── dump-hogwarts-202604011352.sql
├── index.php
├── README.md
├── assets/
│   ├── css/hogwarts.css
│   └── img/hogwarts-bg.png
├── spell/
│   ├── index.php
│   ├── add.php
│   ├── edit.php
│   └── delete.php
├── student/
│   ├── index.php
│   ├── add.php
│   ├── edit.php
│   └── delete.php
└── mastery/
    ├── index.php
    ├── add.php
    ├── edit.php
    └── delete.php
```

## Настройка окружения

1. Разместите проект в `c:\OSPanel\home\hogwarts.local`.
2. Убедитесь, что домен `hogwarts.local` добавлен в `OSPanel`.
3. Скопируйте `.env.example` в `.env` и заполните параметры:

```env
DB_HOST=MySQL-8.0
DB_NAME=hogwarts
DB_USER=root
DB_PASS=
DB_PORT=3306
```

4. Запустите MySQL и веб-сервер в OSPanel.
5. Откройте в браузере: `http://hogwarts.local/`.

## Импорт базы данных

В репозитории есть готовый дамп `dump-hogwarts-202604011352.sql` со структурой и тестовыми данными для БД `hogwarts`.

1. Создайте базу данных `hogwarts` в MySQL 8.0.
2. Откройте `http://phpmyadmin/`.
3. Выберите базу `hogwarts`.
4. Перейдите на вкладку Import.
5. Импортируйте файл `dump-hogwarts-202604011352.sql`.

После импорта будут созданы таблицы `spell`, `student`, `mastery`, а также триггер для пересчёта `spell_count`.

## Схема БД

### `spell`

- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name` VARCHAR(120) UNIQUE NOT NULL

### `student`

- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name` VARCHAR(100) NOT NULL
- `surname` VARCHAR(100) NOT NULL
- `house` ENUM('Гриффиндор','Слизерин','Когтевран','Пуффендуй') NOT NULL
- `course` TINYINT UNSIGNED NULL
- `is_deleted` TINYINT NOT NULL DEFAULT 0
- `spell_count` INT UNSIGNED NOT NULL DEFAULT 0

### `mastery`

- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `student_id` INT UNSIGNED NOT NULL (FK -> `student.id`, CASCADE)
- `spell_id` INT UNSIGNED NOT NULL (FK -> `spell.id`, CASCADE)
- `mastered_at` DATE NULL
- `UNIQUE (student_id, spell_id)`

## Дамп БД

- Файл: `dump-hogwarts-202604011352.sql`
- Содержимое: структура таблиц, тестовые данные и триггер `trg_after_insert_mastery`
- Назначение: быстрый разворот локальной базы без ручного создания таблиц

## Что реализовано

- Полный CRUD для `spell`, `student`, `mastery`
- Prepared statements (`mysqli_prepare` + bind)
- Защита от дубликатов при добавлении/редактировании
- Таблицы без вывода PK/FK ID на UI
- Нумерация строк на index-страницах
- Фильтры в `student/index.php` по всем полям
- Фильтры в `mastery/index.php` по ученику и заклинанию
- Модальные окна подтверждения удаления (Bootstrap Modal)
- Стилизованный UI в тематике Hogwarts

## Маршруты

- Главная: `/`
- Заклинания: `/spell/index.php`
- Студенты: `/student/index.php`
- Освоение: `/mastery/index.php`

## Быстрая проверка

1. Создать/изменить/удалить запись в `spell`.
2. Создать/изменить/удалить студента в `student`.
3. Создать/изменить/удалить связь студент-заклинание в `mastery`.
4. Проверить, что дубликаты не добавляются.
5. Проверить фильтры в `student` и `mastery`.
6. Проверить модальные окна удаления в трёх разделах.

## Примечания

- `push` в удалённый репозиторий выполняется вручную.
- `.env` не коммитится (секреты только локально).
