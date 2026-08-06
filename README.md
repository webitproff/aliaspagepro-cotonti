
# Alias Page PRO — automatic aliases for Page module pages

**Plugin for Cotonti v1.+**  
Automatically creates readable, transliterated aliases (URL paths) for pages of the `page` module when the user leaves the alias field empty.  
Completely replaces the built-in `autoalias2` plugin (shipped with Cotonti) — simultaneous use is not allowed and may cause conflicts.

**Important:** Do not install this plugin together with the built-in `autoalias2` ([https://github.com/Cotonti/Cotonti/tree/master/plugins/autoalias2](https://github.com/Cotonti/Cotonti/tree/master/plugins/autoalias2)) — they perform the same task, and their coexistence will lead to unstable operation.

---

## Table of contents

- [History of creation and the problem it solves](#history-of-creation-and-the-problem-it-solves)
- [Features](#features)
- [Requirements](#requirements)
- [Plugin structure](#plugin-structure)
- [Installation](#installation)
- [Plugin settings in the admin panel](#plugin-settings-in-the-admin-panel)
- [Usage](#usage)
- [Administration panel (three tabs)](#administration-panel-three-tabs)
- [Detailed description of each file](#detailed-description-of-each-file)
- [Usage example](#usage-example)
- [Troubleshooting](#troubleshooting)
- [License](#license)
- [Links](#links)

---

## History of creation and the problem it solves

The built-in `autoalias2` plugin (shipped with Cotonti) uses a language file for Cyrillic transliteration that depends on the site's interface language. If the interface is, for example, Russian and the page content is Ukrainian, Ukrainian letters (`і`, `є`, `ї`, `ґ`) are not transliterated and may be removed or left unchanged, breaking the URL.

**Alias Page PRO** completely solves this problem:

- Uses a **built-in transliteration function** that contains a single character table for both Russian and Ukrainian alphabets.
- Does not depend on system language files — the result is always predictable.
- Provides a convenient three-tab management panel: statistics, viewing, and bulk editing.
- Allows bulk creation or clearing of aliases for all pages.

Thus, the plugin is a full replacement for the built-in `autoalias2` with improved functionality and control.

---

## Features

- Automatic alias generation from the page title when adding or editing a page, if the alias field is left empty.
- Internal Cyrillic (Russian + Ukrainian) to Latin transliteration — independent of Cotonti language files.
- Removal of all characters except letters, digits, hyphen, underscore and space.
- Flexible settings:
  - Enable/disable transliteration.
  - Prepend page ID to the alias.
  - Behavior on duplicate alias (append ID or random number).
  - Word separator selection (hyphen, underscore, dot).
  - Convert to lowercase.
- Administration panel with three tabs:
  - **Statistics** — total pages, pages with alias, without alias, without Meta Title.
  - **Lists & Aliases** — table of all pages with ID, title, meta title and alias.
  - **Management** — bulk editing of title, Meta Title and alias for multiple pages at once.
- Bulk actions on the "Statistics" tab:
  - **Create aliases** — generate aliases for all pages where alias is missing.
  - **Clear all aliases** — completely erase the `page_alias` field for all pages (with confirmation).
- Pagination in lists and management forms.
- Full interface localization (Russian, English).

---

## Requirements

- **Cotonti v1.+** (tested on the current version)
- **Module `page`** (must be installed and active)
- **PHP 8.5+**
- **MySQL 8.4+**
- Administrator rights to access the plugin's control panel.
- **The built-in `autoalias2` plugin must be disabled or removed** to avoid conflicts.

---

## Plugin structure

```
aliaspagepro/
├── aliaspagepro.setup.php           # Plugin registration and settings description
├── aliaspagepro.page.php            # Hook for adding/editing a page
├── aliaspagepro.admin.php           # Administration panel
├── inc/
│   └── aliaspagepro.functions.php   # Transliteration, conversion, update functions
├── lang/
│   ├── aliaspagepro.ru.lang.php     # Russian language file
│   └── aliaspagepro.en.lang.php     # English language file
└── tpl/
    └── aliaspagepro.admin.tpl       # Admin panel template
```

---

## Installation

1. **Disable or remove the built-in `autoalias2` plugin** if it is active (Extensions → AutoAlias 2 → Deactivate).
2. Download the ZIP archive of the `aliaspagepro` plugin from the [official repository](https://github.com/webitproff/aliaspagepro-cotonti).
3. Extract the archive into the `plugins/` folder of your site.
4. Go to the Cotonti admin panel → **Extensions**.
5. Find **"Alias Page PRO"** and click **"Install"**.

After installation, the plugin starts working immediately — when adding or editing pages, an alias will be generated automatically if the field is left empty.

---

## Plugin settings in the admin panel

All settings are available at **Extensions → Alias Page PRO → Configuration**.

| Parameter | Type | Default | Description |
|----------|-----|-------------|----------|
| `translit` | radio | `1` (on) | Enables transliteration of non-Latin characters (Cyrillic) to Latin. |
| `prepend_id` | radio | `0` (off) | Prepend page ID to the alias. Example: `123-page-name`. |
| `on_duplicate` | select | `ID` | Action when a duplicate alias is found (if `prepend_id` is off): `ID` — append page ID at the end; `Random` — append a random number from 2 to 99. |
| `sep` | select | `-` | Word separator: hyphen (`-`), underscore (`_`), dot (`.`). |
| `lowercase` | radio | `1` (on) | Convert alias to lowercase. |

---

## Usage

### Automatic alias creation

When you add or edit a page in the `page` module and leave the "Alias" field empty, the plugin triggers on the hooks `page.add.add.done` and `page.edit.update.done`. The alias generation process:

1. Takes the page title (`page_title`).
2. If transliteration is enabled, the title is processed by the internal function `aliaspagepro_transliterate()`, which replaces all Cyrillic characters (Russian and Ukrainian) according to a single table.
3. Removes all characters except letters, digits, hyphen, underscore and space.
4. Replaces spaces with the selected separator.
5. If "Convert to lowercase" is enabled, all letters become lowercase.
6. If "Prepend ID" is enabled, `{id}{separator}` is added before the alias.
7. Checks for uniqueness: if the generated alias already exists for another page and `prepend_id` is off, the page ID or a random number (depending on `on_duplicate`) is appended at the end.
8. The finished alias is written to the `page_alias` field.

---

## Administration panel (three tabs)

The panel is accessible via **Admin → Tools → Alias Page PRO**.

### "Statistics" tab
Shows four numbers:

- **Total pages** — number of pages with a non-empty title.
- **With alias** — pages where the `page_alias` field is filled.
- **Without alias** — pages with an empty alias.
- **Without Meta Title** — pages where the meta title (`page_metatitle`) is not filled.

Action buttons:
- **Create aliases** — starts alias generation for all pages where alias is missing.
- **Clear all aliases** — erases aliases for all pages (requires confirmation).
- **Configuration** — goes to the plugin settings.

### "Lists & Aliases" tab
Displays a table of all pages (paginated, 25 records per page) with columns:

- **ID**
- **Title** (is a link to the page on the site)
- **Meta Title**
- **Alias**

Intended for a quick overview of alias status.

### "Management" tab
Allows editing the title, Meta Title and alias for several pages on one page (10 records per page). The **ID** field is not editable.

To change data:
1. Make edits in the required fields.
2. Click the **"Update"** button.

After saving, you remain on the same pagination page from which you started.

---

## Detailed description of each file

Below is a detailed description of all plugin files.

---

### `aliaspagepro.setup.php`

**Purpose:** registers the plugin in the Cotonti system and describes its settings.

Contains two mandatory sections:

- **`[BEGIN_COT_EXT] … [END_COT_EXT]`** – metadata:  
  - `Code` – unique identifier `aliaspagepro`.  
  - `Name` – display name.  
  - `Category` – `seo` (category in the admin panel).  
  - `Version` – current version (2.5.1).  
  - `Requires_modules` – `page`.  
  - Access rights for guests and users.
- **`[BEGIN_COT_EXT_CONFIG] … [END_COT_EXT_CONFIG]`** – list of configuration parameters:  
  - `translit`, `prepend_id`, `on_duplicate`, `sep`, `lowercase`.  
  - Each line specifies sort order, field type (radio/select), default value and description.

Without this file, Cotonti cannot install the plugin or provide a settings form.

---

### `aliaspagepro.page.php`

**Hooks:** `page.add.add.done`, `page.edit.update.done`

Executes immediately after adding or updating a page. The code is minimal:

```php
if (empty($ritem['page_alias']))
{
    require_once cot_incfile('aliaspagepro', 'plug');
    $ritem['page_alias'] = aliaspagepro_update($ritem['page_title'], $id);
}
```

If the alias field is empty, the plugin functions are loaded and `aliaspagepro_update()` is called, which generates a new alias based on the title and page ID. The result is written directly into the `$ritem` array, and the `page` module saves it to the database.

---

### `aliaspagepro.admin.php`

**Hook:** `tools`

Generates the plugin administration page (in the "Tools" section). Logic:

1. Determines the current tab (`tab`) and action (`a`) from the URL.
2. Assigns navigation variables for the template (active tabs, links).
3. **Special actions handling** (before tab output):
   - `a=create` – selects all pages with an empty alias, calls `aliaspagepro_update()` for each, outputs a message with the number of created aliases.
   - `a=clear_aliases` – sets `page_alias` to empty for all pages, outputs a message about clearing.
   - After execution, redirects back to the same tab (preserving parameters).
4. **Tab content generation**:
   - **Statistics** – executes four SQL queries to calculate indicators, passes them to the template, as well as the URL for the "Create aliases" button.
   - **Lists & Aliases** – fetches paginated data (LIMIT/OFFSET) from the `cot_pages` table. For each page, computes a URL via `cot_page_url()`, assigns `LIST_ROW_*` variables, and parses the `LIST_ROW` block. If there are no pages, outputs `LIST_EMPTY`. Adds pagination.
   - **Management** – similarly fetches paginated data. If a POST request with `a=update` is received, loops over all IDs from the form and updates title, meta title and alias. Uses `$backUrl` with the current page number (`durl`) to return to the same page after saving. The `&amp;` issue in the URL is fixed by replacing `&amp;` → `&` before the redirect.
5. Displays system messages, parses the main `MAIN` block and returns the HTML.

---

### `inc/aliaspagepro.functions.php`

**Contains the three main functions of the plugin.**

#### `aliaspagepro_transliterate($str)`
Internal transliteration function. Accepts a string and passes it through a **single replacement array**, ordered from longest sequences to shortest:

- Unique Ukrainian letters: `Є`, `є`, `Ї`, `ї`, `Ґ`, `ґ`, `І`, `і`.
- Russian letters missing in Ukrainian: `ё`, `Ё`, `ы`, `Ы`, `э`, `Э`, `ъ`, `Ъ`.
- Common multi-character combinations: `Щ` → `Shch`, `Ж` → `Zh`, `Х` → `Kh`, `Ц` → `Ts`, `Ч` → `Ch`, `Ш` → `Sh`, `Ю` → `Yu`, `Я` → `Ya`.
- Single letters (common to Russian and Ukrainian): `А` → `A`, `б` → `b`, etc.
- Soft sign and apostrophes are replaced with an empty string (removed).

Returns the string with replacements. Does not depend on external files.

#### `aliaspagepro_convert($title, $id, $duplicate)`
Main function for converting a title to an alias. Steps:
1. If `translit` is enabled, calls `aliaspagepro_transliterate()`.
2. Removes forbidden characters via `preg_replace('#[^\p{L}0-9\-_ ]#u', '', $title)`.
3. Replaces spaces with the separator from config.
4. If `lowercase=1`, converts to lowercase.
5. If `prepend_id=1` and `$id` is not empty, adds the prefix `$id . $sep`.
6. If `$duplicate=true`, appends the ID (if set and mode is `ID`) or a random number (mode `Random`) at the end.
Returns the finished alias.

#### `aliaspagepro_update($title, $id)`
Called when adding/editing a page and during bulk creation. In a `do...while` loop:
- Generates an alias via `aliaspagepro_convert()`.
- If `prepend_id` is off, checks the database for existence of this alias for another page (`page_id != $id`).
- If a duplicate is found, sets `$duplicate=true`, and on the next iteration `aliaspagepro_convert` will append the ID/random number.
- When a unique alias is obtained, updates the record in the `cot_pages` table.
- Returns the final alias.

---

### Language files (`lang/aliaspagepro.ru.lang.php`, `en.lang.php`)

Contain all text strings: plugin name, description, setting labels (including hints and dropdown values), tab names, messages.  
Used by the `cot_langfile()` function when loading the plugin.

---

### Template `tpl/aliaspagepro.admin.tpl`

HTML template with XTemplate blocks for the admin panel. Uses Bootstrap 5 (included in Cotonti v1.+). Main blocks:

- `MAIN` – page container with tabs.
- `LIST_ROW`, `LIST_EMPTY` – table rows in the "Lists & Aliases" tab.
- `MANAGE_ROW`, `MANAGE_EMPTY` – form rows in the "Management" tab.
- Pagination is included via the system tag `{PAGINATION}`.

Tabs are switched using conditional blocks `<!-- IF {PHP.tab} == '...' -->`.

---

## Usage example

**Scenario:** a site with a Russian interface, but a page is created with the Ukrainian title "Нова сторінка". Alias is left empty.

1. The user saves the page.
2. The plugin calls `aliaspagepro_transliterate('Нова сторінка')`.
3. Characters are replaced:  
   - `Н` → `N`, `о` → `o`, `в` → `v`, `а` → `a`,  
   - `с` → `s`, `т` → `t`, `о` → `o`, `р` → `r`,  
   - `і` → `i`, `н` → `n`, `к` → `k`, `а` → `a`.  
   - Space is replaced with a hyphen (`sep=-`).
4. With `lowercase` enabled, we get `nova-storinka`.
5. The alias is unique — it is written to the database.

If the built-in `autoalias2` with a Russian transliteration table were used, the letter `і` would remain unchanged or be removed, leading to an incorrect URL.

---

## Troubleshooting

- **Alias is not created.** Ensure the plugin is active and the "Alias" field in the page editing form is empty. Verify that the built-in `autoalias2` plugin is disabled.
- **Table rows do not appear in the admin panel.** Check that the template file `aliaspagepro.admin.tpl` matches the latest version, and that blocks `LIST_ROW` and `MANAGE_ROW` are directly inside `MAIN` (not wrapped in other blocks).
- **After saving in "Management", the URL contains `&amp;`.** This issue was fixed in version 2.5.1 by forcibly replacing `&amp;` → `&` in the redirect URL. If it still appears, make sure you have the latest `admin.php`.
- **Transliteration does not work as expected.** The transliteration table is hardcoded in `aliaspagepro_transliterate()`. If a needed character is missing, add it to the `$translit` array (file `functions.php`), observing the order from long to short sequences.
- **The "Create aliases" button has no effect.** Make sure there are records in the `cot_pages` table with an empty `page_alias`. Check write permissions to the database.
- **Conflict with the `autoalias2` plugin.** If both plugins are active, unpredictable behavior may occur. Remove or deactivate the built-in `autoalias2`.

---

## License

BSD. Free use and modification with attribution.

---

## Links

- [Official GitHub repository](https://github.com/webitproff/aliaspagepro-cotonti)
- [Plugin page on Cotonti Marketplace](https://abuyfile.com/ru/market/cotonti/plugs/aliaspagepro)
- [Support forum](https://abuyfile.com/forums/cotonti/custom/plugs/)
- [Built-in autoalias2 plugin (incompatible)](https://github.com/Cotonti/Cotonti/tree/master/plugins/autoalias2)


 
___
> RU
___
# Alias Page PRO — автоматические алиасы для страниц модуля Page

**Плагин для Cotonti v1.+**  
Автоматически создаёт читаемые, транслитерированные алиасы (URL‑пути) для страниц модуля `page`, если пользователь оставил поле алиаса пустым.  
Полностью заменяет штатный плагин `autoalias2` (идущий в комплекте Cotonti) — одновременное использование не допускается и может вызвать конфликты.

**Важно:** Не устанавливайте этот плагин вместе со штатным `autoalias2` ([https://github.com/Cotonti/Cotonti/tree/master/plugins/autoalias2](https://github.com/Cotonti/Cotonti/tree/master/plugins/autoalias2)) — они выполняют одну и ту же задачу, и их сосуществование приведёт к нестабильной работе.

---

## Оглавление

- [История создания и решаемая проблема](#история-создания-и-решаемая-проблема)
- [Возможности](#возможности)
- [Требования](#требования)
- [Структура плагина](#структура-плагина)
- [Установка](#установка)
- [Настройка плагина в админке](#настройка-плагина-в-админке)
- [Использование](#использование)
- [Административная панель (три вкладки)](#административная-панель-три-вкладки)
- [Подробное описание каждого файла](#подробное-описание-каждого-файла)
- [Пример использования](#пример-использования)
- [Устранение неполадок](#устранение-неполадок)
- [Лицензия](#лицензия)
- [Ссылки](#ссылки)

---

## История создания и решаемая проблема

Штатный плагин `autoalias2` (поставляется с Cotonti) использует для транслитерации кириллицы языковой файл, зависящий от языка интерфейса сайта. Если интерфейс, например, русский, а контент страницы — украинский, то украинские буквы (`і`, `є`, `ї`, `ґ`) не транслитерируются и могут быть удалены или остаться без изменений, портя URL.

**Alias Page PRO** полностью решает эту проблему:

- Использует **встроенную функцию транслитерации**, которая содержит единую таблицу символов русского и украинского алфавитов.
- Не зависит от системных языковых файлов — результат всегда предсказуем.
- Предоставляет удобную трёхвкладочную панель управления: статистика, просмотр, массовое редактирование.
- Позволяет массово создавать или очищать алиасы для всех страниц.

Таким образом, плагин является полноценной заменой штатного `autoalias2` с улучшенной функциональностью и контролем.

---

## Возможности

- Автоматическая генерация алиаса из заголовка страницы при её добавлении или редактировании, если поле алиаса не заполнено.
- Внутренняя транслитерация кириллицы (русский + украинский) в латиницу — не зависит от языковых файлов Cotonti.
- Удаление всех символов, кроме букв, цифр, дефиса, подчёркивания и пробела.
- Гибкие настройки:
  - Включение/отключение транслитерации.
  - Добавление ID страницы в начало алиаса.
  - Поведение при дублировании алиаса (добавление ID или случайного числа).
  - Выбор разделителя слов (дефис, подчёркивание, точка).
  - Приведение к нижнему регистру.
- Административная панель с тремя вкладками:
  - **Статистика** — общее количество страниц, сколько с алиасом, без алиаса, без Meta Title.
  - **Списки и Алиасы** — таблица всех страниц с ID, заголовком, мета‑заголовком и алиасом.
  - **Управление** — массовое редактирование заголовка, Meta Title и алиаса сразу для нескольких страниц.
- Массовые действия на вкладке «Статистика»:
  - **Создать алиасы** — генерация алиасов для всех страниц, где алиас отсутствует.
  - **Очистить все алиасы** — полная очистка поля `page_alias` у всех страниц (с подтверждением).
- Пагинация в списках и формах управления.
- Полная локализация интерфейса (русский, английский).

---

## Требования

- **Cotonti v1.+** (протестировано на актуальной версии)
- **Модуль `page`** (должен быть установлен и активен)
- **PHP 8.5+**
- **MySQL 8.4+**
- Права администратора для доступа к панели управления плагином.
- **Штатный плагин `autoalias2` должен быть отключён или удалён**, чтобы избежать конфликтов.

---

## Структура плагина

```
aliaspagepro/
├── aliaspagepro.setup.php           # Регистрация плагина и описание настроек
├── aliaspagepro.page.php            # Хук для добавления/редактирования страницы
├── aliaspagepro.admin.php           # Административная панель
├── inc/
│   └── aliaspagepro.functions.php   # Функции транслитерации, конвертации, обновления
├── lang/
│   ├── aliaspagepro.ru.lang.php     # Русский языковой файл
│   └── aliaspagepro.en.lang.php     # Английский языковой файл
└── tpl/
    └── aliaspagepro.admin.tpl       # Шаблон админ‑панели
```

---

## Установка

1. **Отключите или удалите штатный плагин `autoalias2`**, если он активен (Расширения → AutoAlias 2 → Деактивировать).
2. Скачайте ZIP‑архив плагина `aliaspagepro` из [официального репозитория](https://github.com/webitproff/aliaspagepro-cotonti).
3. Распакуйте архив в папку `plugins/` вашего сайта.
4. Зайдите в админ‑панель Cotonti → **Расширения**.
5. Найдите **«Alias Page PRO»** и нажмите **«Установить»**.

После установки плагин сразу начинает работать — при добавлении или редактировании страниц алиас будет генерироваться автоматически, если поле оставлено пустым.

---

## Настройка плагина в админке

Все параметры доступны по пути **Расширения → Alias Page PRO → Конфигурация**.

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|-------------|----------|
| `translit` | radio | `1` (вкл.) | Включает транслитерацию нелатинских символов (кириллицы) в латиницу. |
| `prepend_id` | radio | `0` (выкл.) | Добавлять ID страницы в начало алиаса. Пример: `123-название-страницы`. |
| `on_duplicate` | select | `ID` | Действие при обнаружении дубликата алиаса (если `prepend_id` выключен): `ID` — добавить ID страницы в конец; `Random` — добавить случайное число от 2 до 99. |
| `sep` | select | `-` | Разделитель слов: дефис (`-`), подчёркивание (`_`), точка (`.`). |
| `lowercase` | radio | `1` (вкл.) | Приводить алиас к нижнему регистру. |

---

## Использование

### Автоматическое создание алиаса

Когда вы добавляете или редактируете страницу в модуле `page` и оставляете поле «Alias» пустым, плагин срабатывает на хуках `page.add.add.done` и `page.edit.update.done`. Процесс генерации алиаса:

1. Берётся заголовок страницы (`page_title`).
2. Если транслитерация включена, заголовок пропускается через внутреннюю функцию `aliaspagepro_transliterate()`, которая заменяет все кириллические символы (русские и украинские) согласно единой таблице.
3. Удаляются все символы, кроме букв, цифр, дефиса, подчёркивания и пробела.
4. Пробелы заменяются на выбранный разделитель.
5. Если включено приведение к нижнему регистру, все буквы становятся строчными.
6. Если включена опция «Добавить ID в начало», перед алиасом ставится `{id}{разделитель}`.
7. Проверяется уникальность: если сгенерированный алиас уже существует у другой страницы и `prepend_id` выключен, то в конец добавляется ID страницы или случайное число (в зависимости от настройки `on_duplicate`).
8. Готовый алиас записывается в поле `page_alias`.

---

## Административная панель (три вкладки)

Панель доступна через **Админка → Инструменты → Alias Page PRO**.

### Вкладка «Статистика»
Показывает четыре числа:

- **Всего страниц** — количество страниц с непустым заголовком.
- **С алиасом** — страницы, у которых заполнено поле `page_alias`.
- **Без алиаса** — страницы с пустым алиасом.
- **Без Meta Title** — страницы, у которых не заполнен мета‑заголовок (`page_metatitle`).

Кнопки действий:
- **Создать алиасы** — запускает генерацию алиасов для всех страниц, где алиас отсутствует.
- **Очистить все алиасы** — стирает алиас у всех страниц (требуется подтверждение).
- **Конфигурация** — переход к настройкам плагина.

### Вкладка «Списки и Алиасы»
Отображает таблицу всех страниц (с пагинацией по 25 записей на странице) со столбцами:

- **ID**
- **Заголовок** (является ссылкой на страницу на сайте)
- **Meta Title**
- **Алиас**

Предназначена для быстрого обзора состояния алиасов.

### Вкладка «Управление»
Позволяет редактировать заголовок, Meta Title и алиас сразу для нескольких страниц на одной странице (по 10 записей). Поле **ID** не редактируется.

Чтобы изменить данные:
1. Внесите правки в нужные поля.
2. Нажмите кнопку **«Обновить»**.

После сохранения вы останетесь на той же странице пагинации, с которой начали.

---

## Подробное описание каждого файла

Ниже — детальное описание всех файлов плагина.

---

### `aliaspagepro.setup.php`

**Назначение:** регистрация плагина в системе Cotonti и описание его настроек.

Содержит две обязательные секции:

- **`[BEGIN_COT_EXT] … [END_COT_EXT]`** – метаданные:  
  - `Code` – уникальный идентификатор `aliaspagepro`.  
  - `Name` – отображаемое имя.  
  - `Category` – `seo` (категория в админке).  
  - `Version` – текущая версия (2.5.1).  
  - `Requires_modules` – `page`.  
  - Права доступа гостей и пользователей.
- **`[BEGIN_COT_EXT_CONFIG] … [END_COT_EXT_CONFIG]`** – список параметров конфигурации:  
  - `translit`, `prepend_id`, `on_duplicate`, `sep`, `lowercase`.  
  - Каждая строка задаёт порядок сортировки, тип поля (radio/select), значение по умолчанию и описание.

Без этого файла Cotonti не сможет установить плагин и предоставить форму настроек.

---

### `aliaspagepro.page.php`

**Хуки:** `page.add.add.done`, `page.edit.update.done`

Выполняется сразу после добавления или обновления страницы. Код минимален:

```php
if (empty($ritem['page_alias']))
{
    require_once cot_incfile('aliaspagepro', 'plug');
    $ritem['page_alias'] = aliaspagepro_update($ritem['page_title'], $id);
}
```

Если поле алиаса пусто, подключаются функции плагина и вызывается `aliaspagepro_update()`, которая генерирует новый алиас на основе заголовка и ID страницы. Результат записывается прямо в массив `$ritem`, и модуль `page` сохраняет его в базу данных.

---

### `aliaspagepro.admin.php`

**Хук:** `tools`

Формирует страницу администрирования плагина (в разделе «Инструменты»). Логика работы:

1. Определяет текущую вкладку (`tab`) и действие (`a`) из URL.
2. Назначает переменные навигации для шаблона (активные вкладки, ссылки).
3. **Обработка специальных действий** (до вывода вкладок):
   - `a=create` – выбирает все страницы с пустым алиасом, для каждой вызывает `aliaspagepro_update()`, выводит сообщение с количеством созданных алиасов.
   - `a=clear_aliases` – обнуляет поле `page_alias` у всех страниц, выводит сообщение об очистке.
   - После выполнения редиректит на ту же вкладку (с сохранением параметров).
4. **Формирование содержимого вкладок**:
   - **Статистика** – выполняет четыре SQL‑запроса для подсчёта показателей, передаёт их в шаблон, а также URL для кнопки «Создать алиасы».
   - **Списки и Алиасы** – получает данные с пагинацией (LIMIT/OFFSET) из таблицы `cot_pages`. Для каждой страницы вычисляет URL через `cot_page_url()`, присваивает переменные `LIST_ROW_*` и парсит блок `LIST_ROW`. При отсутствии страниц выводит `LIST_EMPTY`. Добавляет пагинацию.
   - **Управление** – аналогично получает данные с пагинацией. Если пришёл POST‑запрос с `a=update`, в цикле обновляет заголовок, мета‑заголовок и алиас для всех ID, переданных в форме. Использует `$backUrl` с номером текущей страницы (`durl`), чтобы после сохранения вернуться на ту же страницу. Проблема с `&amp;` в URL решается заменой `&amp;` → `&` перед редиректом.
5. Выводит системные сообщения, парсит главный блок `MAIN` и возвращает HTML.

---

### `inc/aliaspagepro.functions.php`

**Содержит три основные функции плагина.**

#### `aliaspagepro_transliterate($str)`
Внутренняя функция транслитерации. Принимает строку и пропускает через **единый массив замен**, отсортированный от длинных последовательностей к коротким:

- Украинские уникальные буквы: `Є`, `є`, `Ї`, `ї`, `Ґ`, `ґ`, `І`, `і`.
- Русские буквы, отсутствующие в украинском: `ё`, `Ё`, `ы`, `Ы`, `э`, `Э`, `ъ`, `Ъ`.
- Общие многосимвольные комбинации: `Щ` → `Shch`, `Ж` → `Zh`, `Х` → `Kh`, `Ц` → `Ts`, `Ч` → `Ch`, `Ш` → `Sh`, `Ю` → `Yu`, `Я` → `Ya`.
- Одиночные буквы (общие для русского и украинского): `А` → `A`, `б` → `b` и т.д.
- Мягкий знак и апострофы заменяются на пустую строку (удаляются).

Возвращает строку с заменёнными символами. Не зависит от внешних файлов.

#### `aliaspagepro_convert($title, $id, $duplicate)`
Основная функция преобразования заголовка в алиас. Выполняет шаги:
1. Если `translit` включён, вызывает `aliaspagepro_transliterate()`.
2. Удаляет запрещённые символы через `preg_replace('#[^\p{L}0-9\-_ ]#u', '', $title)`.
3. Заменяет пробелы на разделитель из конфига.
4. При `lowercase=1` приводит к нижнему регистру.
5. Если `prepend_id=1` и `$id` не пуст, добавляет префикс `$id . $sep`.
6. Если `$duplicate=true`, добавляет в конец ID (если задан и выбран режим `ID`) или случайное число (режим `Random`).
Возвращает готовый алиас.

#### `aliaspagepro_update($title, $id)`
Вызывается при добавлении/редактировании страницы и при массовом создании. В цикле `do...while`:
- Генерирует алиас через `aliaspagepro_convert()`.
- Если `prepend_id` выключен, проверяет в базе существование такого алиаса у другой страницы (`page_id != $id`).
- Если дубликат найден, устанавливает `$duplicate=true`, и на следующей итерации `aliaspagepro_convert` добавит ID/случайное число.
- Когда получен уникальный алиас, обновляет запись в таблице `cot_pages`.
- Возвращает финальный алиас.

---

### Языковые файлы (`lang/aliaspagepro.ru.lang.php`, `en.lang.php`)

Содержат все текстовые строки: название плагина, описание, подписи к настройкам (включая подсказки и значения выпадающих списков), названия вкладок, сообщения.  
Используются функцией `cot_langfile()` при загрузке плагина.

---

### Шаблон `tpl/aliaspagepro.admin.tpl`

HTML‑шаблон с блоками XTemplate для админ‑панели. Использует Bootstrap 5 (входит в Cotonti v1.+). Основные блоки:

- `MAIN` – контейнер страницы с вкладками.
- `LIST_ROW`, `LIST_EMPTY` – строки таблицы во вкладке «Списки и Алиасы».
- `MANAGE_ROW`, `MANAGE_EMPTY` – строки формы во вкладке «Управление».
- Пагинация подключается через системный тег `{PAGINATION}`.

Вкладки переключаются через условные блоки `<!-- IF {PHP.tab} == '...' -->`.

---

## Пример использования

**Ситуация:** сайт с русским интерфейсом, но страница создана с украинским заголовком «Нова сторінка». Алиас не заполнен.

1. Пользователь сохраняет страницу.
2. Плагин вызывает `aliaspagepro_transliterate('Нова сторінка')`.
3. Символы заменяются:  
   - `Н` → `N`, `о` → `o`, `в` → `v`, `а` → `a`,  
   - `с` → `s`, `т` → `t`, `о` → `o`, `р` → `r`,  
   - `і` → `i`, `н` → `n`, `к` → `k`, `а` → `a`.  
   - Пробел заменяется на дефис (`sep=-`).
4. С включённым `lowercase` получаем `nova-storinka`.
5. Алиас уникален — записывается в базу.

Если бы использовался штатный `autoalias2` с русской таблицей транслитерации, буква `і` осталась бы без изменений или была удалена, что привело бы к некорректному URL.

---

## Устранение неполадок

- **Алиас не создаётся.** Убедитесь, что плагин активен и поле «Alias» в форме редактирования страницы пусто. Проверьте, что штатный плагин `autoalias2` отключён.
- **В админке не отображаются строки таблиц.** Проверьте, что файл шаблона `aliaspagepro.admin.tpl` соответствует последней версии, а блоки `LIST_ROW` и `MANAGE_ROW` находятся непосредственно внутри `MAIN` (не обёрнуты в другие блоки).
- **После сохранения в «Управлении» URL содержит `&amp;`.** Эта проблема исправлена в версии 2.5.1 путём принудительной замены `&amp;` → `&` в URL редиректа. Если всё ещё проявляется, убедитесь, что у вас актуальный `admin.php`.
- **Транслитерация работает не так, как ожидается.** Таблица транслитерации зашита в `aliaspagepro_transliterate()`. Если нужного символа нет, добавьте его в массив `$translit` (файл `functions.php`), соблюдая порядок от длинных последовательностей к коротким.
- **Кнопка «Создать алиасы» не даёт результата.** Убедитесь, что в таблице `cot_pages` есть записи с пустым `page_alias`. Проверьте права на запись в базу данных.
- **Конфликт с плагином `autoalias2`.** Если оба плагина активны, возможна непредсказуемая работа. Удалите или деактивируйте штатный `autoalias2`.

---

## Лицензия

BSD. Свободное использование и модификация при сохранении авторских прав.

---

## Ссылки

- [Официальный репозиторий GitHub](https://github.com/webitproff/aliaspagepro-cotonti)
- [Страница плагина на Cotonti Marketplace](https://abuyfile.com/ru/market/cotonti/plugs/aliaspagepro)
- [Поддержка на форуме](https://abuyfile.com/forums/cotonti/custom/plugs/)
- [Штатный плагин autoalias2 (несовместим)](https://github.com/Cotonti/Cotonti/tree/master/plugins/autoalias2)


  
