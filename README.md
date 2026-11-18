# Alias Page PRO for Cotonti

A plugin for CMS Cotonti that automatically generates a page alias (URL slug) from its title if the author leaves the alias field empty.  

--- 
[![Version](https://img.shields.io/badge/version-3.0.0-green.svg)](https://github.com/webitproff/aliaspagepro-cotonti/releases)
[![Cotonti Compatibility](https://img.shields.io/badge/Cotonti-v.1-orange.svg)](https://github.com/Cotonti/Cotonti)
[![PHP](https://img.shields.io/badge/PHP-8.5-purple.svg)](https://www.php.net/releases/8_5_0.php)
[![MySQL](https://img.shields.io/badge/MySQL-8.4-blue.svg)](https://www.mysql.com/)
[![Bootstrap v5.3.8](https://img.shields.io/badge/Bootstrap-v5.3.8-blueviolet.svg)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/license-BSD-blue.svg)](https://github.com/webitproff/aliaspagepro-cotonti/blob/main/LICENSE)

---  

<img width="1536" height="1024" alt="Alias Page PRO — automatic aliases for Page module pages Cotonti by webitproff" src="https://github.com/user-attachments/assets/96c06085-9e8e-4127-85cc-9062e2903bc8" />

- **Repository:** https://github.com/webitproff/aliaspagepro-cotonti
- **Support:** https://abuyfile.com/forums/cotonti/custom/plugs/
- **Marketplace:** https://abuyfile.com/ru/market/cotonti/plugs/aliaspagepro

---

## Table of Contents

1. [What the plugin does](#1-what-the-plugin-does)
2. [Who it's for](#2-whos-it-for)
3. [Requirements](#3-requirements)
4. [Installation](#4-installation)
5. [Plugin settings](#5-plugin-settings)
6. [Alias generation logic](#6-alias-generation-logic)
7. [Examples](#7-examples)
8. [Admin panel](#8-admin-panel)
9. [Cotonti hook](#9-cotonti-hook)
10. [API for developers](#10-api-for-developers)
11. [File structure](#11-file-structure)
12. [Scenario: mass article import](#12-scenario-mass-article-import)
13. [Troubleshooting](#13-troubleshooting)
14. [Glossary](#14-glossary)
15. [How to use `AliasPagePro::assign()`](#15-how-to-use-aliaspageproassign)
16. [License](#16-license)

---

## 1. What the plugin does

The plugin hooks into two events of the `page` module:

- **`page.add.add.done`** — after a new page is added.
- **`page.edit.update.done`** — after an existing page is updated.

In both cases, if the `page_alias` field of the page being saved is **empty**, the plugin:

1. Takes the `page_title` value.
2. Transliterates Cyrillic to Latin (if enabled in settings).
3. Converts to lowercase (if enabled).
4. Removes everything that is not a letter, digit, space, `_` or `-`.
5. Replaces spaces with the selected separator (`-`, `_` or `.`).
6. Truncates the result to the configured length at the last **whole** word.
7. Checks uniqueness in the pages table and adds a suffix if needed.
8. Writes the final string to `page_alias` and returns it.

If `page_alias` is already filled in (by the author manually or by an import) — the plugin **does not interfere**. This allows manual aliases to be used where needed while automatically covering all other cases.

---

## 2. Who it's for

- **Cotonti site owners** tired of manually coming up with URLs for every article.
- **Editors** publishing Cyrillic content: the plugin transliterates titles to Latin automatically.
- **Developers** who need a predictable, documented way to generate slugs with length and uniqueness control.
- **Sites after a mass import**, where titles are long and pages have no aliases.

---

## 3. Requirements

| Component | Requirement |
|---|---|
| CMS | Cotonti v1.x or higher |
| PHP | **8.5** or higher (the code uses `enum`, `match`, `declare(strict_types=1)`, `random_int`) |
| MySQL | **8.4** or higher |
| `page` module | Required — the plugin works only with its table and hooks |
| `mbstring` extension | Required — multi-byte string handling |
| `pcre` extension (built-in) | Required — regular expressions with `/u` |

The plugin **does not create its own tables** in the database — it uses the existing fields `page_alias`, `page_title`, `page_metatitle` of the `page` module.

---

## 4. Installation

### Method 1. Via Cotonti (recommended)

1. Download the plugin archive from the repository or marketplace.
2. In the Cotonti admin panel, open **Extensions → Install**.
3. Click **Upload package** and select the archive.
4. Click **Install**.
5. Go to **Extensions → aliaspagepro → Settings**, configure the parameters.
6. Done.

### Method 2. Manual

```bash
cd /path/to/cotonti/plugins/
git clone https://github.com/webitproff/aliaspagepro-cotonti.git aliaspagepro
```

Then in the Cotonti admin panel: **Extensions → aliaspagepro → Install**.

### What happens during installation

- A row with the plugin code `aliaspagepro`, its version, access rights and the "active" flag is written to the `cot_core` table.
- Default values for all settings from `aliaspagepro.setup.php` are written to the `cot_config` table.
- No database fields are modified.

### What happens after installation

As soon as the plugin is activated, every time a page with an empty `page_alias` is saved, the generated alias is written into that field.

> ⚠️ Do not install it alongside the `autoalias2lance` plugin — both plugins hook into the same events and will generate aliases twice. This condition is fixed in the `Notes` field of the `aliaspagepro.setup.php` file.

---

## 5. Plugin settings

All settings are stored in `Cot::$cfg['plugin']['aliaspagepro']` and are available at:

- **Admin → Extensions → aliaspagepro → Settings**
- or directly: `admin.php?m=config&n=edit&o=plug&p=aliaspagepro`

| # | Key | Type | Default | Values |
|---|---|---|---|---|
| 01 | `translit` | radio | `1` | `0` / `1` |
| 02 | `lowercase` | radio | `1` | `0` / `1` |
| 03 | `sep` | select | `-` | `-`, `_`, `.` |
| 04 | `max_length` | select | `36` | `24`, `36`, `52`, `78` |
| 05 | `prepend_id` | radio | `0` | `0` / `1` |
| 06 | `on_duplicate` | select | `ID` | `ID`, `Hash`, `Random` |
| 07 | `perpage_list` | string | `25` | any integer ≥ 5 |
| 08 | `perpage_manage` | string | `10` | any integer ≥ 5 |

### 5.1. `translit` — Transliteration

- **What it does:** if enabled (`1`), before all other operations the title is transliterated from Cyrillic to Latin using the internal `TRANSLIT_MAP` table.
- **Why:** URLs with Latin characters work correctly in any browser, do not require percent-encoding, index better, and are easier to read.
- **If disabled (`0`):** Cyrillic stays in the alias as-is. It works, but the URL will look like `%D0%BF%D1%80%D0%B8%D0%B2%D0%B5%D1%82` in the address bar.
- **Supported alphabets:** Russian and Ukrainian (including the unique Ukrainian letters `Є`, `Ї`, `Ґ`, `І`).

### 5.2. `lowercase` — Lowercase conversion

- **What it does:** applies `mb_strtolower()` to the string after transliteration.
- **Why:** lowercase is the URL standard. Some servers (especially Linux) treat `Page` and `page` as different, causing 404 errors when the case is changed in a link.
- **Applied only to the title.** The ID prefix and suffixes are digits/hex and are case-insensitive.

### 5.3. `sep` — Word separator

- **What it does:** replaces all spaces between words with the selected character.
- **Options:**
  - `-` (hyphen) — the recommended default, standard for URLs;
  - `_` (underscore) — if that's the convention in your project;
  - `.` (dot) — rare, but acceptable.
- **Important:** the same character is used in the **truncation at last word** logic. The setting affects not only appearance but also where the truncation happens (the last `sep` within the budget is looked up).

### 5.4. `max_length` — Maximum alias length

- **What it does:** sets the upper bound of the final alias length in characters (after transliteration and truncation).
- **What counts toward the limit:** absolutely everything — the ID prefix, the body from the title, and the uniqueness suffix.
- **Options:** `24`, `36` (default), `52`, `78`.
- **Why limit it:** very long URLs are inconvenient for sharing, may be truncated on social media, and look visually overloaded. 36 characters is a practical balance between readability and compactness.
- **How it works on overflow:** see [“Alias generation logic”](#6-alias-generation-logic).

### 5.5. `prepend_id` — ID prefix

- **What it does:** if enabled, `{page_id}{sep}` is added **in front** of the alias.
- **Example:** `123-kak-nastroit-server`.
- **Why:** guarantees uniqueness without any suffix — because `page_id` is unique at the database level. This is the simplest and most predictable way to ensure uniqueness.
- **Side effect:** with the prefix enabled, the uniqueness suffix is **not added** — even if another alias with the same body is found in the DB (which is unlikely, since the ID is already unique).

### 5.6. `on_duplicate` — Uniqueness strategy

- **What it does:** determines how to build the suffix if the base alias is already taken **and** `prepend_id` is disabled.
- **Options:**

| Value | How the suffix is built | Attempts | Feature |
|---|---|---|---|
| `ID` | `(string)$page_id` | 1 | Fully deterministic. One attempt — either the ID is free, or fallback is used. |
| `Hash` | `substr(md5($title . '|' . $attempt), 0, 6)` | up to 10 | Deterministic by title. First 6 chars of MD5. |
| `Random` | `random_int(2, 9999)` | up to 10 | **Non-deterministic.** Regenerating at a different time may produce a different suffix. |

- **How the loop works:** see [“Uniqueness and saving”](#64-uniqueness-and-saving).

### 5.7. `perpage_list` — Items per page in “Lists & Aliases”

- **What it does:** sets the pagination page size in the second admin tab (read-only).
- **Default:** `25`. Minimum forcibly limited to `5` (in code: `max(5, (int) ...)`).

### 5.8. `perpage_manage` — Items per page in “Manage”

- **What it does:** sets the pagination page size in the third admin tab (editing).
- **Default:** `10`. Minimum `5`.

---

## 6. Alias generation logic

All logic is implemented in the `AliasPagePro` class (`plugins/aliaspagepro/inc/aliaspagepro.functions.php`). The class is `final`, cannot be instantiated — only static methods are used.

Overall page-saving flow:

```
Hook (page.add.add.done / page.edit.update.done)
    ↓
if (empty($rpage['page_alias']))
    ↓
aliaspagepro_update($title, $id)         ← BC wrapper
    ↓
AliasPagePro::assign($title, $id)
    ↓
┌── title normalization
├── base candidate assembly
├── uniqueness check in DB
│       ├── if free → save and return
│       └── if taken → add suffix, retry (up to 10 times)
├── if all attempts fail → fallback (md5)
└── save to page_alias and return
```

### 6.1. Title normalization

Private method `normalizeTitle($title, $config)`. Steps:

1. **`trim()`** — trims whitespace at both ends.
2. If the string is empty — returns an empty string. It will later be replaced with `'page'` (see 6.2).
3. **Transliteration** (if `translit = 1`): `strtr($str, TRANSLIT_MAP)`. All Cyrillic characters (RU + UK) are replaced with Latin.
4. **Lowercase** (if `lowercase = 1`): `mb_strtolower($str, 'UTF-8')`.
5. **Whitespace collapse:** `preg_replace('/\s+/u', ' ', $str)`. Multiple spaces, tabs, line breaks — all become a single space.
6. **Remove excess characters:** `preg_replace('/[^\p{L}\p{N}\s_\-]/u', '', $str)`. Only Unicode letters (`\p{L}`), Unicode digits (`\p{N}`), space (`\s`), `_` and `-` are kept. Everything else is removed: `?`, `!`, `«», (, ), /`, `.`, `:`, `;`, `№`, `#`, etc.
7. **Replace spaces with the separator:** `str_replace(' ', $sep, $str)`.
8. **Collapse repeated separators:** `preg_replace('/(' . preg_quote($sep) . ')+/u', $sep, $str)`. If the original string had multiple consecutive spaces (which is not true after step 5, but could appear after other transformations) — they won't turn into `----`.
9. **Trim separators and underscores at the edges:** `trim($str, $sep . '_-.')`.

**Result of the step:** a "clean" alias body, without punctuation, with a single separator, in lowercase.

### 6.2. Candidate assembly

Private method `assemble($prefix, $body, $suffix, $maxLen, $sep)`.

1. If `body` is empty — the string `'page'` is substituted (done before calling `assemble`, inside `convert()` and `assign()`).
2. **Prefix is formed:** if `prepend_id = 1` and `$id > 0` → `"{$id}{$sep}"`, otherwise an empty string.
3. **Suffix is formed:** if the suffix is not empty → `"{$sep}{$suffix}"`, otherwise an empty string.
4. **Budget is calculated:** `budget = maxLen − mb_strlen(prefix) − mb_strlen(suffixPart)` — that is, how many characters remain for the body.
5. If **`budget < 1`** — prefix and suffix together already exceed the limit. In this case:
   - the body is discarded entirely;
   - result = `mb_substr(prefix . suffixPart, 0, maxLen)` — hard truncation of the concatenation.
6. If **`budget ≥ 1`** — the body is truncated via `truncateBody()` (see 6.3).
7. `prefix + body + suffixPart` is concatenated.
8. If the result is empty — a rare fallback: `substr(md5(random_bytes(8)), 0, min(8, maxLen))`. Normally does not trigger; needed only in case of total failure of the previous steps.
9. The method returns a string.

### 6.3. Body truncation

Private method `truncateBody($body, $budget, $sep)`.

1. If `body` is empty or `budget ≤ 0` — returns an empty string.
2. If `mb_strlen($body) ≤ $budget` — no truncation needed, returns the body as-is.
3. Otherwise:
   - `cut = mb_substr($body, 0, $budget)` — hard-cut to budget.
   - Look up the **last `sep`** in `cut` via `mb_strrpos($cut, $sep)`.
   - If found and its position `> 0` (i.e., not at the very beginning) — cut at it: `cut = mb_substr($cut, 0, $pos)`.
   - Additionally trim `sep` and `_-.` at the edges via `rtrim`.
4. If after all manipulations `cut === ''` (one very long word without separators) — hard truncation without word consideration: `rtrim(mb_substr($body, 0, $budget), $sep . ' _-.')`.
5. Returns the result.

**Why truncate at a word boundary:** to avoid chopping a word in the URL — `kak-nastroit-ser` instead of `kak-nastroit-server`. The algorithm "steps back" to the last separator that fits within the budget.

### 6.4. Uniqueness and saving

Public method `AliasPagePro::assign($title, $id)`.

1. **Normalization:** body = `normalizeTitle($title, $c)`.
2. If the body is empty — `body = 'page'`.
3. **Prefix formation:** if `prepend_id = 1` and `$id > 0` → `"{$id}{$sep}"`, otherwise empty.
4. **Attempt 0 (without suffix):**
   - `candidate = assemble(prefix, body, '', maxLen, sep)`
   - `aliasExists($candidate, $id)` — DB check:
     ```sql
     SELECT COUNT(*) FROM cot_pages WHERE page_alias = ? AND page_id <> ?
     ```
   - If `0` — save and return.
5. **Attempts with suffix:**
   - `maxAttempts = 1` for the `ID` strategy, `10` for `Hash`/`Random`.
   - For each iteration `i = 1..maxAttempts`:
     - `suffix = makeSuffix(strategy, $id, $title, $i)`:
       - `ID` → `(string)$id`
       - `Hash` → `substr(md5($title . '|' . $i), 0, 6)` — deterministic, depends on title and attempt number
       - `Random` → `(string)random_int(2, 9999)`
     - `candidate = assemble(prefix, body, suffix, maxLen, sep)`
     - If `!aliasExists($candidate, $id)` — save and return.
6. **Final fallback** (if all attempts fail):
   - `candidate = mb_substr(md5($id . '|' . $title), 0, min(12, $maxLen))`
   - Save and return.
   - This hash is guaranteed to be deterministic for the specific `id + title` pair and unique at the `page_id` level, but computationally almost impossible to collide with another page.

### 6.5. Saving to database

Private method `saveAlias($id, $alias)`:

```php
Cot::$db->update(
    Cot::$db->pages,
    ['page_alias' => $alias],
    'page_id = ?',
    [$id]
);
```

A prepared statement is used — SQL injection is impossible. The `$alias` value has already passed through a regular expression in `normalizeTitle` and contains only allowed characters.

---

## 7. Examples

All examples below are for the default configuration:

```
translit = 1
lowercase = 1
sep = -
max_length = 36
prepend_id = 0
on_duplicate = ID
```

### 7.1. Short titles (no truncation)

| Title | Alias | Length |
|---|---|---:|
| `Привет` | `privet` | 6 |
| `Как настроить сервер` | `kak-nastroit-server` | 19 |
| `Про кота` | `pro-kota` | 8 |

Here only `normalizeTitle` was involved: transliteration, lowercase, space-to-hyphen replacement.

### 7.2. Medium titles (no truncation)

| Title | Alias | Length |
|---|---|---:|
| `Як правильно скасувати замовлення` | `yak-pravilno-skasuvati-zamovlennya` | 33 |
| `Как настроить сервер nginx` | `kak-nastroit-server-nginx` | 25 |

33 and 25 — both strings fit within the 36-character budget. No truncation.

### 7.3. Long titles (truncation at last word)

**Title:** `Як оцінити результати CPC кампаній? Розбір метрик`

After `normalizeTitle`:

```
yak-otsiniti-rezultati-cpc-kampaniy-rozbir-metrik
```

Budget = 36. Hard-cut to 36 characters yields:

```
yak-otsiniti-rezultati-cpc-kampaniy-
```

The last `-` is at position 35. Cut there:

```
yak-otsiniti-rezultati-cpc-kampaniy
```

**Final alias (35 characters):**

```
yak-otsiniti-rezultati-cpc-kampaniy
```

**Title:** `Функция «Не звонить для подтверждения заказа»`

After `normalizeTitle`:

```
funktsiya-ne-zvonit-dlya-podtverzhdeniya-zakaza
```

Budget = 36. Cut to 36:

```
funktsiya-ne-zvonit-dlya-podtverzhde
```

The last `-` is at position 25. Cut there:

```
funktsiya-ne-zvonit-dlya
```

**Final alias (24 characters):**

```
funktsiya-ne-zvonit-dlya
```

### 7.4. A single very long word

**Title:** `Оченьдлинноесловобезпробеловисовсембезпробелов`

After `normalizeTitle` (not a single separator, since the source had no spaces):

```
ochendlinnoeslovobezprobelovisovsembesprobelov
```

46 characters, budget exceeded. Hard-cut to 36:

```
ochendlinnoeslovobezprobelovisovsemb
```

`mb_strrpos` returns `false` (no separators). The body remains truncated by character. `rtrim` additionally finds nothing at the edges.

**Final alias (36 characters):**

```
ochendlinnoeslovobezprobelovisovsemb
```

### 7.5. Title with punctuation and extra spaces

**Title:** `Что   делать???  (когда — всё  плохо!)`

`normalizeTitle` steps:
1. `trim` → `Что   делать???  (когда — всё  плохо!)`
2. transliteration → `Chto   delat???  (kogda — vsyo  plokho!)`
3. lowercase → `chto   delat???  (kogda — vsyo  plokho!)`
4. whitespace collapse → `chto delat??? (kogda — vsyo plokho!)`
5. punctuation removal → `chto delat kogda vsyo plokho`
6. space → `-` → `chto-delat-kogda-vsyo-plokho`
7. repeated `-` collapse → (unchanged)
8. separator `trim` → (unchanged)

**Final alias (28 characters):**

```
chto-delat-kogda-vsyo-plokho
```

### 7.6. Duplicate under `ID` strategy

Suppose there is already a page with `page_id = 42` and alias `kupit-slona`. A new page with title `Купить слона` and `page_id = 152` is added.

- `normalizeTitle` → `kupit-slona`
- Attempt 0: `kupit-slona` is already taken (`page_id <> 152`)
- Attempt 1 (`ID` strategy): suffix = `152`, candidate = `kupit-slona-152`
- Check: free — saved

**Final alias:**

```
kupit-slona-152
```

### 7.7. Duplicate under `Hash` strategy

Same situation: `kupit-slona` is taken. `on_duplicate = Hash`.

- Attempt 1: suffix = `substr(md5('Купить слона|1'), 0, 6)` = e.g. `a1b2c3`. Candidate: `kupit-slona-a1b2c3`. If free — saved.

**Final alias:**

```
kupit-slona-a1b2c3
```

Important: the suffix depends only on `title` and `attempt`. Regenerating on a different day for the same title produces the **same suffix**. This makes URLs stable.

### 7.8. With `prepend_id` enabled

`prepend_id = 1`, `max_length = 36`.

| Title | page_id | Alias | Length |
|---|---:|---|---:|
| `Как настроить сервер` | 152 | `152-kak-nastroit-server` | 25 |
| `Привет` | 7 | `7-privet` | 9 |

Prefix `152-` takes 4 characters. Body budget: `36 − 4 − 0 = 32`. Body `kak-nastroit-server` = 19 characters — fits without truncation.

### 7.9. Truncation with `max_length = 24`

`max_length = 24`, `prepend_id = 0`, no suffix.

| Title | Alias | Length |
|---|---|---:|
| `Як правильно скасувати замовлення` | `yak-pravilno-skasuvati` | 22 |
| `Как настроить сервер nginx` | `kak-nastroit-server` | 19 |
| `Функция «Не звонить для подтверждения»` | `funktsiya-ne-zvonit` | 18 |

Budget = 24. For the first example:
- `yak-pravilno-skasuvati-zamovlennya` (33) → cut to 24 → `yak-pravilno-skasuvati-za` → last `-` at position 22 → `yak-pravilno-skasuvati`.

For the third:
- `funktsiya-ne-zvonit-dlya-podtverzhdeniya` (41) → cut to 24 → `funktsiya-ne-zvonit-dlya` → last `-` at position 18 → `funktsiya-ne-zvonit`.

---

## 8. Admin panel

**Address:** `admin.php?m=other&p=aliaspagepro&tab=stats`  
**Menu:** Admin → aliaspagepro

The plugin connects to Cotonti through the `tools` hook, so it appears in the admin tools section.

### 8.1. “Statistics” tab (`tab=stats`)

Shows four counters:

| Metric | SQL condition |
|---|---|
| Total pages | `page_title <> ''` |
| With alias | `page_title <> '' AND page_alias <> ''` |
| Without alias | `page_title <> '' AND page_alias = ''` |
| Without Meta Title | `page_title <> '' AND (page_metatitle = '' OR page_metatitle IS NULL)` |

The counters are integers displayed in four colored cards. Useful as a “health indicator”: if “Without alias” is greater than zero, it’s worth running “Create aliases”.

Three buttons:

- **Create aliases** (`a=create`) — iterates over all pages with an empty `page_alias` and generates aliases for them via `aliaspagepro_update()`. Upon completion shows: `Aliases written: N`.
- **Configuration** — opens the plugin settings page (`admin.php?m=config&n=edit&o=plug&p=aliaspagepro`).
- **Clear all aliases** (`a=clear_aliases`) — executes `UPDATE cot_pages SET page_alias = ''`. Asks for confirmation via JavaScript `confirm()`. Upon completion shows: `All aliases cleared`.

**Caution with the “Clear all aliases” button:** it clears aliases of **all** pages without exception. If page URLs depend on the alias, until the next “Create aliases” run, pages may open by ID or return 404. Use only as a pair: clear → then immediately create.

### 8.2. “Lists & Aliases” tab (`tab=list`)

Read-only table:

| Column | Source |
|---|---|
| `ID` | `page_id` |
| Title | `page_title`, rendered as a link to the page (opens in a new tab) |
| Meta Title | `page_metatitle` |
| Alias | `page_alias` |

Pagination is controlled by the `perpage_list` setting (default 25). Rows are sorted by `page_id DESC`.

If there are no pages — the message “No pages” is shown.

### 8.3. “Manage” tab (`tab=manage`)

Editable form. Each row contains three `<input>` fields:

- Title (`page_title`)
- Meta Title (`page_metatitle`)
- Alias (`page_alias`)

A hidden `ids[]` field passes `page_id`. The **Update** button sends `POST` with `a=update`.

**Server-side processing** (on `a=update` and `REQUEST_METHOD === 'POST'`):

1. Arrays `ids`, `title`, `metatitle`, `alias` are extracted from `$_POST`.
2. For each `id`:
   - `trim()` of all string values.
   - The alias is additionally cleaned by a regex: `preg_replace('/[^a-z0-9_\-\.]/i', '', $alias)` — this protects against `?`, `#`, `&`, `/` and other URL-breaking characters.
   - Executes `UPDATE cot_pages SET page_title = ?, page_metatitle = ?, page_alias = ? WHERE page_id = ?` via prepared statement.
3. The notification `Data updated successfully` is shown and the user is redirected to the same page with the current `d` (pagination offset).

Pagination is controlled by the `perpage_manage` setting (default 10).

**Important:** here the plugin **does not regenerate** the alias from the title. If you changed the title but left the alias empty — after saving, the alias stays empty. To regenerate it, run “Create aliases” on the “Statistics” tab.

---

## 9. Cotonti hook

File `plugins/aliaspagepro/aliaspagepro.page.php`.

```php
if (empty($rpage['page_alias'])) {
    require_once cot_incfile('aliaspagepro', 'plug');
    $rpage['page_alias'] = aliaspagepro_update(
        (string) $rpage['page_title'],
        (int) $id
    );
}
```

- **Hooks:** `page.add.add.done`, `page.edit.update.done`.
- **Trigger condition:** empty `$rpage['page_alias']`.
- **What it does:** calls the BC wrapper `aliaspagepro_update()`, which delegates to `AliasPagePro::assign()`. The `assign()` method checks uniqueness and saves the result to the DB. The returned string is assigned to `$rpage['page_alias']` so that the next code in the chain can use it.
- **If the field is already filled in** — the `if` block doesn’t trigger, and the plugin silently skips the page.

---

## 10. API for developers

All methods are static, the class is `final` with a private constructor (cannot be instantiated).

```php
require_once cot_incfile('aliaspagepro', 'plug');
```

### 10.1. `AliasPagePro::transliterate(string $str): string`

Transliterates Cyrillic (RU + UK) to Latin. Does not touch other characters — digits, Latin letters, punctuation — returns them as-is.

```php
AliasPagePro::transliterate('Привет');
// → 'Privet'

AliasPagePro::transliterate('Привіт');
// → 'Privit'

AliasPagePro::transliterate('Hello, мир!');
// → 'Hello, mir!'
```

### 10.2. `AliasPagePro::convert(string $title, int $id = 0): string`

Generates an alias deterministically: normalizes the title, applies the `prepend_id` prefix (if enabled and `$id > 0`), truncates to `max_length`.

**Does not touch the database.** Does not check uniqueness. Does not save the result.

Use case: for previews, for displaying the “expected” alias in your own forms, for debugging.

```php
AliasPagePro::convert('Как настроить сервер', 0);
// → 'kak-nastroit-server'

AliasPagePro::convert('Длинный заголовок про настройку', 152);
// with prepend_id = 1 → '152-dlinnyy-zagolovok-pro'
```

> For scenarios requiring a uniqueness check and a `page_alias` write — use `assign()`.
> See the detailed walkthrough below in the section **How to use `AliasPagePro::assign()`**.

### 10.3. `AliasPagePro::assign(string $title, int $id): string`

Full cycle: generates the alias, checks uniqueness in the DB, adds a suffix if necessary, saves to `page_alias` and returns the final string.

```php
$alias = AliasPagePro::assign('Как настроить сервер', 152);
// → 'kak-nastroit-server'
//   or 'kak-nastroit-server-152' if already taken
```

This is the method invoked by the hook through the BC wrapper `aliaspagepro_update()`. If you call alias generation from your own code — call `assign()`.

### 10.4. Enum `AliasPagePro_DuplicateStrategy`

```php
enum AliasPagePro_DuplicateStrategy: string
{
    case Id     = 'ID';
    case Hash   = 'Hash';
    case Random = 'Random';
}
```

Used in the config to safely convert the setting string into a strict type (via `tryFrom()`).

### 10.5. BC functions (for backward compatibility)

Wrappers over class methods. Kept so that third-party plugins/templates calling functions by name continue to work.

| Function | What it does | Equivalent |
|---|---|---|
| `aliaspagepro_transliterate(string $str): string` | Transliteration | `AliasPagePro::transliterate()` |
| `aliaspagepro_convert(string $title, int $id = 0, bool $duplicate = false): string` | Generation without saving | `AliasPagePro::convert()`. The third argument `$duplicate` is ignored. |
| `aliaspagepro_update(string $title, int $id): string` | Generation + check + save | `AliasPagePro::assign()` |

### 10.6. Internal class constants

| Constant | Value | Purpose |
|---|---|---|
| `DEFAULT_MAX_LENGTH` | `36` | Fallback if `max_length` is not set in config |
| `DEFAULT_SEPARATOR` | `-` | Fallback for the separator |
| `MAX_ATTEMPTS` | `10` | Uniqueness attempt limit (only for `Hash`/`Random`) |
| `RANDOM_MIN` | `2` | Lower bound of `Random` |
| `RANDOM_MAX` | `9999` | Upper bound of `Random` |
| `HASH_LENGTH` | `6` | Length of the hash suffix for `Hash` |

### 10.7. Example: batch generation from your own code

```php
require_once cot_incfile('aliaspagepro', 'plug');

$res = Cot::$db->query(
    "SELECT page_id, page_title FROM cot_pages WHERE page_alias = ''"
);

while ($row = $res->fetch()) {
    AliasPagePro::assign($row['page_title'], (int) $row['page_id']);
}
```

---

## 11. File structure

```
plugins/aliaspagepro/
├── aliaspagepro.setup.php              # Registration in Cotonti, settings declaration
├── aliaspagepro.page.php               # Hook on page.add / page.edit events
├── aliaspagepro.admin.php              # Admin panel (3 tabs: statistics, list, manage)
├── inc/
│   └── aliaspagepro.functions.php      # Core: AliasPagePro class, enum, BC functions
├── lang/
│   ├── aliaspagepro.ru.lang.php        # Russian localization
│   └── aliaspagepro.en.lang.php        # English localization
└── tpl/
    └── aliaspagepro.admin.tpl          # Admin panel HTML template
```

| File | Responsibility |
|---|---|
| `aliaspagepro.setup.php` | Plugin metadata (code, version, dependencies) and declaration of all settings in the `[BEGIN_COT_EXT_CONFIG]` block |
| `aliaspagepro.page.php` | Hook — entry point on page save |
| `aliaspagepro.admin.php` | All admin panel logic: statistics, lists, alias updates, bulk operations |
| `inc/aliaspagepro.functions.php` | All generation business logic |
| `lang/*.lang.php` | Localization: setting titles, hints, UI texts |
| `tpl/aliaspagepro.admin.tpl` | Admin panel markup using `{TAB_*}`, `{STATS_*}`, `{LIST_ROW_*}`, `{MANAGE_*}`, `{PAGINATION}` tags |

---

## 12. Scenario: mass article import

The plugin is convenient precisely for the case “imported N articles with long titles and no aliases”.

### Recommended settings for import

| Key | Recommendation | Why |
|---|---|---|
| `translit` | `1` | Imported titles in Cyrillic → Latin |
| `lowercase` | `1` | URL standard |
| `sep` | `-` | Most readable |
| `max_length` | `36` | Balance between readability and compactness |
| `prepend_id` | `0` | Not needed if uniqueness is solved via Hash/ID |
| `on_duplicate` | `Hash` | Deterministic suffix — URLs stable on regeneration |

### Workflow after import

1. **If the import saves `page_alias = ''`** — the `page.add.add.done` hook has already fired, and aliases are generated at each page insert. Verify on the **“Lists & Aliases”** tab that all “Alias” columns are filled in.
2. **If the import writes `page_alias = NULL`** or inserts data directly via SQL bypassing Cotonti hooks:
   - Go to **Admin → aliaspagepro → Statistics**.
   - Verify that the “Without alias” counter shows the expected number.
   - Click **Create aliases**.
   - Wait for the notification `Aliases written: N`.
3. Verify the result on the **“Lists & Aliases”** tab: alias length should not exceed `max_length`, and all “Alias” columns should be filled.
4. If needed, make targeted edits on the **“Manage”** tab.

### Integrity check via SQL

Confirm there are no duplicate aliases in the DB:

```sql
SELECT page_alias, COUNT(*) AS cnt
FROM cot_pages
WHERE page_alias <> ''
GROUP BY page_alias
HAVING cnt > 1;
```

The query should return 0 rows. Uniqueness is guaranteed by the `assign()` method — the check is performed before each write.

Verify that all aliases fit within `max_length`:

```sql
SELECT page_id, page_alias, CHAR_LENGTH(page_alias) AS len
FROM cot_pages
WHERE page_alias <> '' AND CHAR_LENGTH(page_alias) > 36
ORDER BY len DESC;
```

Where `36` is your `max_length` value.

---

## 13. Troubleshooting

### Aliases are not generated when adding a page

Possible causes:

1. **The plugin is not installed or disabled.** Check **Admin → Extensions** — the `aliaspagepro` row must be active.
2. **The `page_alias` field is already filled in.** The plugin does not overwrite non-empty aliases — this is its normal behavior.
3. **The `page` module is disabled or replaced.** `aliaspagepro.setup.php` requires `Requires_modules=page`, but if the module does not actually load — the hooks won’t fire.
4. **Cache.** If Cotonti caching is enabled, clear it: **Admin → Tools → Clear cache**.

### Aliases come out too short

Too small a `max_length` value (e.g. `24`) with `prepend_id = 1` enabled:

- ID prefix = e.g. `152-` (4 characters).
- Body remainder = `24 − 4 = 20`.
- A 5-word title is truncated almost immediately.

**Solution:** increase `max_length` to `36` or `52`, or disable `prepend_id`.

### Identical titles produce identical aliases, but the DB rejects the second one

The `ID` strategy guarantees uniqueness through the `-{id}` suffix, but **only one attempt**. If even `kupit-slona-152` is taken (an extremely rare case — e.g., someone manually assigned such an alias to another page) — the deterministic fallback kicks in: `substr(md5(id . '|' . title), 0, 12)`. Uniqueness is preserved.

If you see duplicate aliases in the DB — it means someone (not the plugin) wrote them directly or disabled the check. The plugin cannot do that.

### The “Create aliases” button does nothing

Check the statistics: if the **“Without alias”** counter shows `0` — there is nothing to generate. The plugin iterated over an empty list of pages.

### After “Clear all aliases” pages became inaccessible

This is expected behavior. The button executes `UPDATE cot_pages SET page_alias = ''` — clearing **all** aliases. If Cotonti routing uses `page_alias`, pages may return 404 until the next “Create aliases” run.

**Solution:** immediately after clearing, click **Create aliases** to restore aliases for all pages.

### Transliteration doesn't work

Check:

1. `translit = 1` in settings.
2. Test via API:
   ```php
   AliasPagePro::transliterate('Привет');
   // should return 'Privet'
   ```
3. If it returns the original string — the plugin is not connected via `cot_incfile('aliaspagepro', 'plug')`.

### In the logs: `PHP Fatal error: Cannot instantiate final class`

The `AliasPagePro` class is declared as `final` with a **private** constructor. Instances cannot be created. Use only static methods:

- `AliasPagePro::transliterate()`
- `AliasPagePro::convert()`
- `AliasPagePro::assign()`

### Cotonti shows the error `Requires_modules=page`

The plugin requires an active `page` module. Enable it in **Admin → Extensions → page**.

### Conflict with `autoalias2lance`

The plugin explicitly warns: `Notes=DO NOT INSTALL with autoalias2lance`. Both hook into the same events and will generate aliases twice, causing the last-installed one to overwrite. Remove or disable one of them.

---

## 14. Glossary

| Term | Meaning in the context of this plugin |
|---|---|
| **Alias** | The string that becomes part of a page URL. Stored in Cotonti in the `page_alias` field. For `example.com/kak-nastroit-server` the alias is `kak-nastroit-server`. |
| **Slug** | A synonym for “alias” borrowed from Western practice (WordPress, Django). Denotes the human-readable part of a URL. |
| **Title** | The `page_title` field in the pages table. The text the user sees. The alias is generated from it. |
| **Transliteration** | Replacing characters of one writing system with those of another. Here — Cyrillic → Latin. For example, `Привет` → `Privet`. |
| **Separator (`sep`)** | The character that spaces are replaced with. Standard — hyphen `-`. Also used in the “truncate at last word” logic. |
| **Budget** | An internal algorithm variable: how many characters remain for the alias “body” (i.e., without prefix and suffix). `budget = max_length − len(prefix) − len(suffix)`. |
| **Body** | The normalized part of the alias derived from the title. Sits between the prefix and suffix. For example, in `152-kak-nastroit-server-4729` the body is `kak-nastroit-server`. |
| **Prefix** | The part of the alias added **in front**. In the plugin — only the page ID, when `prepend_id = 1`. |
| **Suffix** | The part of the alias added **at the end**. In the plugin — the uniqueness suffix (`-152`, `-a1b2c3`, `-4729`). |
| **Uniqueness resolution** | The process of selecting a unique alias when the base one is taken. Includes a `SELECT COUNT(*)` check and suffix addition. |
| **Uniqueness strategy (`on_duplicate`)** | The rule for building the suffix: `ID` (by page ID), `Hash` (by title hash), or `Random` (random number). |
| **Deterministic** | Yielding the same output for the same input. The `Hash` strategy is deterministic: for `Купить слона` it always gives the same suffix. `Random` is not. |
| **Fallback** | A backup algorithm triggered when the primary one fails. In the plugin — `md5(id . title)` for uniqueness if all attempts fail, and `'page'` for an empty title. |
| **Normalization** | Bringing a string to a “clean” form: transliteration, lowercase, punctuation removal, space replacement, repeated-separator collapse, edge `trim`. |
| **Hook** | A Cotonti mechanism for extending module behavior. The plugin “hangs” on an event and runs its code when the event occurs. |
| **`page.add.add.done`** | Cotonti event: fires after a new page has been added to the DB. |
| **`page.edit.update.done`** | Cotonti event: fires after an existing page has been updated. |
| **`tools`** | Cotonti event used to plug into the admin panel “Tools” section. Used to embed plugin management tabs. |
| **BC (backward compatibility)** | Compatibility with older code. BC functions are wrappers that keep the old code working after refactoring. |
| **`prepend_id`** | Plugin setting. If enabled — guarantees alias uniqueness via a `{id}-` prefix; the uniqueness suffix is not applied. |
| **`max_length`** | Plugin setting. Maximum length of the **final** alias (including prefix and suffix). |
| **`\p{L}` / `\p{N}`** | PCRE character classes for Unicode letters and Unicode digits respectively. Used in `normalizeTitle` to correctly handle Cyrillic and other alphabets. |
| **`mb_strtolower` / `mb_strlen` / `mb_substr`** | PHP multi-byte functions for handling UTF-8 strings. The plain `strtolower` / `strlen` / `substr` **do not work** with Cyrillic — they operate on bytes rather than characters. |
| **`strtr`** | PHP function performing table-based replacement. Used for transliteration: order in the table matters, longer sequences are processed before shorter ones. |
| **`random_int`** | Cryptographically secure random number generator in PHP 7+. Used in the `Random` strategy — unlike `rand()`, it is unpredictable. |
| **Prepared statement** | A way of executing an SQL query with parameters substituted separately from the query text. Protects against SQL injection. In Cotonti implemented as `Cot::$db->update(..., 'page_id = ?', [$id])`. |
| **MD5** | A cryptographic hash function. Used in the plugin to generate deterministic suffixes (`Hash`) and the final fallback. Not for security — only for producing a short unique identifier from a string. |
| **Meta Title** | The `page_metatitle` field in the pages table. Used in the page’s `<title>` HTML tag. The plugin shows and allows editing it in the admin panel, but **does not generate** it — that’s a separate task. |
| **Pagination** | Page-by-page navigation. In the admin panel it’s controlled by the `perpage_list` and `perpage_manage` settings. |
| **Tab** | A section of the plugin admin panel. Three tabs: “Statistics”, “Lists & Aliases”, “Manage”. |

---

## 15. How to use `AliasPagePro::assign()`

This is a regular static method. It’s called with a single line. It returns a string — the resulting alias.

### Minimal example

```php
require_once cot_incfile('aliaspagepro', 'plug');

$alias = AliasPagePro::assign('Купить слона', 152);
// $alias = 'kupit-slona'   (or 'kupit-slona-152' if already taken)
```

Two arguments:

| Argument | Type | What to pass |
|---|---|---|
| `$title` | `string` | The page title — what to generate from |
| `$id` | `int` | The `page_id` of the page being generated for |

The method **saves the result** to `page_alias` via `UPDATE` itself. You don’t need to write anything to the DB.

### Where to put this

`assign()` is called from **your own** code — when the plugin did not fire automatically (e.g., during an import that bypasses hooks) or when you write your own script.

#### Example 1: one-off generation for a single page

Open the Cotonti console (or a temporary php file in the root, then delete it) and run:

```php
<?php
define('COT_CODE', TRUE);
require_once './datas/config.php';
require_once $cfg['system_dir'] . '/common.php';
require_once cot_incfile('aliaspagepro', 'plug');

$alias = AliasPagePro::assign('Купить слона', 152);
var_dump($alias);
```

Open this file in the browser — you’ll see the generated alias, and it will already be in the DB.

#### Example 2: generation for all pages with an empty alias

This is exactly what the “Create aliases” button in the admin panel does. If you want to do it yourself — here’s how:

```php
<?php
define('COT_CODE', TRUE);
require_once './datas/config.php';
require_once $cfg['system_dir'] . '/common.php';
require_once cot_incfile('aliaspagepro', 'plug');

$res = Cot::$db->query(
    "SELECT page_id, page_title FROM cot_pages WHERE page_alias = ''"
);

$count = 0;
while ($row = $res->fetch()) {
    AliasPagePro::assign((string)$row['page_title'], (int)$row['page_id']);
    $count++;
}

echo "Processed: $count";
```

#### Example 3: from your own plugin

```php
// plugins/myplugin/myplugin.page.php
if (empty($rpage['page_alias'])) {
    require_once cot_incfile('aliaspagepro', 'plug');
    $rpage['page_alias'] = AliasPagePro::assign(
        (string)$rpage['page_title'],
        (int)$id
    );
}
```

This is literally what the standard aliaspagepro hook does — just called from your own place.

#### Example 4: preview WITHOUT saving (use `convert()`)

If you only need to **show** the alias and don’t need to save it:

```php
$preview = AliasPagePro::convert('Купить слона', 152);
// $preview = 'kupit-slona'
// Nothing was written to the DB
```

---

### Key points

1. **`assign()` writes to the DB.** After the call, the result is already in the page’s `page_alias`. No need to write it again.

2. **`assign()` is idempotent** for the `ID` and `Hash` strategies — calling it again for the same page with the same title produces the same alias (if it’s still free). `Random` — is not.

3. **The uniqueness check is inside.** `assign()` performs the `SELECT COUNT(*)` itself, adds a suffix itself, and retries up to 10 times. You don’t have to worry about it.

4. **`assign()` does not touch the title or meta title.** It only changes `page_alias`.

5. **The return type is `string`.** Always. Even if the title is empty (returns `'page'`). Even if everything goes wrong (returns the `md5` fallback).

---

### Cheat sheet: which method to call

| Task | Method |
|---|---|
| Just transliterate a string | `AliasPagePro::transliterate($str)` |
| Show an alias preview, without writing to the DB | `AliasPagePro::convert($title, $id)` |
| **Generate + check + save** | **`AliasPagePro::assign($title, $id)`** |
| Batch-fill empty aliases | The “Create aliases” button in the admin panel or a loop with `assign()` |

If you are using the plugin’s standard hook (it’s always active when the plugin is enabled) — you **don’t need to call** `assign()` manually at all. It’s already called automatically when each page is saved. Manual calls are only needed for your own scripts, imports, or debugging.

---

## 16. License

BSD. See the headers of the plugin files.

```
Copyright (c) 2026 webitproff
https://github.com/webitproff/aliaspagepro-cotonti
```

**Author:** webitproff — https://github.com/webitproff

**Links:**

- 🏠 Repository: https://github.com/webitproff/aliaspagepro-cotonti
- 💬 Support: https://abuyfile.com/forums/cotonti/custom/plugs/
- 🛒 Marketplace: https://abuyfile.com/ru/market/cotonti/plugs/aliaspagepro
- 🌐 Cotonti: https://www.cotonti.com



---
# RU
---


# Alias Page PRO для Cotonti

[![Version](https://img.shields.io/badge/version-3.0.0-green.svg)](https://github.com/webitproff/aliaspagepro-cotonti/releases)
[![Cotonti Compatibility](https://img.shields.io/badge/Cotonti-v.1-orange.svg)](https://github.com/Cotonti/Cotonti)
[![PHP](https://img.shields.io/badge/PHP-8.5-purple.svg)](https://www.php.net/releases/8_5_0.php)
[![MySQL](https://img.shields.io/badge/MySQL-8.4-blue.svg)](https://www.mysql.com/)
[![Bootstrap v5.3.8](https://img.shields.io/badge/Bootstrap-v5.3.8-blueviolet.svg)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/license-BSD-blue.svg)](https://github.com/webitproff/aliaspagepro-cotonti/blob/main/LICENSE)

Плагин для CMS Cotonti, который автоматически генерирует алиас (URL-слаг) страницы из её заголовка, если автор оставил поле алиаса пустым.

- **Репозиторий:** https://github.com/webitproff/aliaspagepro-cotonti
- **Поддержка:** https://abuyfile.com/forums/cotonti/custom/plugs/
- **Маркетплейс:** https://abuyfile.com/ru/market/cotonti/plugs/aliaspagepro

---

## Оглавление

1. [Что делает плагин](#1-что-делает-плагин)
2. [Кому подойдёт](#2-кому-подойдёт)
3. [Требования](#3-требования)
4. [Установка](#4-установка)
5. [Настройки плагина](#5-настройки-плагина)
6. [Логика генерации алиаса](#6-логика-генерации-алиаса)
7. [Примеры работы](#7-примеры-работы)
8. [Административная панель](#8-административная-панель)
9. [Хук Cotonti](#9-хук-cotonti)
10. [API для разработчиков](#10-api-для-разработчиков)
11. [Структура файлов](#11-структура-файлов)
12. [Сценарий: массовый импорт статей](#12-сценарий-массовый-импорт-статей)
13. [Решение проблем](#13-решение-проблем)
14. [Глоссарий](#14-глоссарий)
15. [Лицензия](#15-лицензия)

---

## 1. Что делает плагин

Плагин подключается к двум событиям модуля `page`:

- **`page.add.add.done`** — после добавления новой страницы.
- **`page.edit.update.done`** — после редактирования существующей.

В обоих случаях, если поле `page_alias` у сохраняемой страницы **пустое**, плагин:

1. Берёт значение `page_title`.
2. Транслитерирует кириллицу в латиницу (если включено в настройках).
3. Приводит к нижнему регистру (если включено).
4. Удаляет всё, что не является буквой, цифрой, пробелом, `_` или `-`.
5. Заменяет пробелы на выбранный разделитель (`-`, `_` или `.`).
6. Обрезает результат до заданной длины по последнему **целому** слову.
7. Проверяет уникальность в таблице страниц и, если нужно, добавляет суффикс.
8. Записывает итоговую строку в `page_alias` и возвращает её.

Если поле `page_alias` уже заполнено (автором вручную или импортом) — плагин **не вмешивается**. Это позволяет использовать ручные алиасы там, где они нужны, и автоматически закрывать все остальные случаи.

---

## 2. Кому подойдёт

- **Владельцам сайта на Cotonti**, которые устали вручную придумывать URL для каждой статьи.
- **Редакторам**, публикующим материалы на кириллице: плагин сам транслитерирует заголовки в латиницу.
- **Разработчикам**, которым нужен предсказуемый, документированный способ генерации слагов с контролем длины и уникальности.
- **Сайтам после массового импорта**, где заголовки длинные, а у страниц нет алиасов.

---

## 3. Требования

| Компонент | Требование |
|---|---|
| CMS | Cotonti v1.x или выше |
| PHP | **8.5** и выше (код использует `enum`, `match`, `declare(strict_types=1)`, `random_int`) |
| MySQL | **8.4** и выше |
| Модуль `page` | Обязателен — плагин работает только с его таблицей и хуками |
| Расширение `mbstring` | Обязательно — работа с многобайтовыми строками |
| Расширение `pcre` (встроено) | Обязательно — регулярные выражения с `/u` |

Плагин **не создаёт собственных таблиц** в БД — он использует существующие поля `page_alias`, `page_title`, `page_metatitle` модуля `page`.

---

## 4. Установка

### Способ 1. Через Cotonti (рекомендуется)

1. Скачайте архив плагина из репозитория или маркетплейса.
2. В админ-панели Cotonti откройте **Расширения → Установить**.
3. Нажмите **Загрузить пакет** и выберите архив.
4. Нажмите **Установить**.
5. Перейдите в **Расширения → aliaspagepro → Настройки**, выставьте параметры.
6. Готово.

### Способ 2. Вручную

```bash
cd /path/to/cotonti/plugins/
git clone https://github.com/webitproff/aliaspagepro-cotonti.git aliaspagepro
```

Затем в админ-панели Cotonti: **Расширения → aliaspagepro → Установить**.

### Что происходит при установке

- В таблицу `cot_core` записывается строка с кодом плагина `aliaspagepro`, его версией, правами доступа и флагом «активен».
- В таблицу `cot_config` записываются значения по умолчанию для всех настроек из `aliaspagepro.setup.php`.
- Никакие поля БД не модифицируются.

### Что происходит после установки

Как только плагин активирован, при каждом сохранении страницы с пустым `page_alias` в поле будет записан сгенерированный алиас.

> ⚠️ Не устанавливайте одновременно с плагином `autoalias2lance` — оба плагина подключаются к одинаковым хукам и будут генерировать алиасы дважды. Это условие зафиксировано в поле `Notes` файла `aliaspagepro.setup.php`.

---

## 5. Настройки плагина

Все настройки хранятся в `Cot::$cfg['plugin']['aliaspagepro']` и доступны по адресу:

- **Админ → Расширения → aliaspagepro → Настройки**
- или напрямую: `admin.php?m=config&n=edit&o=plug&p=aliaspagepro`

| # | Ключ | Тип | По умолчанию | Значения |
|---|---|---|---|---|
| 01 | `translit` | radio | `1` | `0` / `1` |
| 02 | `lowercase` | radio | `1` | `0` / `1` |
| 03 | `sep` | select | `-` | `-`, `_`, `.` |
| 04 | `max_length` | select | `36` | `24`, `36`, `52`, `78` |
| 05 | `prepend_id` | radio | `0` | `0` / `1` |
| 06 | `on_duplicate` | select | `ID` | `ID`, `Hash`, `Random` |
| 07 | `perpage_list` | string | `25` | любое целое ≥ 5 |
| 08 | `perpage_manage` | string | `10` | любое целое ≥ 5 |

### 5.1. `translit` — Транслитерация

- **Что делает:** если включено (`1`), перед всеми остальными операциями заголовок транслитерируется из кириллицы в латиницу по внутренней таблице `TRANSLIT_MAP`.
- **Зачем:** URL с латиницей корректно работают в любом браузере, не требуют процентного кодирования, лучше индексируются и легче читаются человеком.
- **Если выключено (`0`):** кириллица сохранится в алиасе как есть. Работает, но URL будет выглядеть как `%D0%BF%D1%80%D0%B8%D0%B2%D0%B5%D1%82` в адресной строке.
- **Поддерживаемые алфавиты:** русский и украинский (включая уникальные украинские буквы `Є`, `Ї`, `Ґ`, `І`).

### 5.2. `lowercase` — Приведение к нижнему регистру

- **Что делает:** применяет `mb_strtolower()` к строке после транслитерации.
- **Зачем:** стандарт для URL — нижний регистр. Некоторые серверы (особенно на Linux) различают `Page` и `page`, что приводит к 404 при смене регистра в ссылке.
- **Применяется только к заголовку.** Префикс ID и суффиксы — цифры/hex — регистронезависимы.

### 5.3. `sep` — Разделитель слов

- **Что делает:** заменяет все пробелы между словами на выбранный символ.
- **Варианты:**
  - `-` (дефис) — рекомендация по умолчанию, стандарт для URL;
  - `_` (подчёркивание) — если у вас в проекте принято так;
  - `.` (точка) — редко, но допустимо.
- **Важно:** этот же символ используется в логике **обрезки по последнему слову**. То есть настройка влияет не только на внешний вид, но и на то, где именно произойдёт обрезка (ищется последний `sep` в бюджете).

### 5.4. `max_length` — Максимальная длина алиаса

- **Что делает:** задаёт верхнюю границу итоговой длины алиаса в символах (после транслитерации и обрезки).
- **Что учитывается в лимите:** абсолютно всё — префикс ID, тело из заголовка, суффикс уникализации.
- **Варианты:** `24`, `36` (по умолчанию), `52`, `78`.
- **Зачем ограничивать:** очень длинные URL неудобны для шаринга, могут обрезаться в соцсетях, а также визуально перегружены. 36 символов — практический баланс между читаемостью и компактностью.
- **Как работает при превышении:** см. раздел [«Логика генерации»](#6-логика-генерации-алиаса).

### 5.5. `prepend_id` — Префикс с ID страницы

- **Что делает:** если включено, к алиасу **спереди** добавляется `{page_id}{sep}`.
- **Пример:** `123-kak-nastroit-server`.
- **Зачем:** гарантирует уникальность без какого-либо суффикса — потому что `page_id` уникален на уровне БД. Это самый простой и предсказуемый способ обеспечить уникальность.
- **Побочный эффект:** при включённом префиксе суффикс уникализации **не добавляется** — даже если в БД обнаружится другой алиас с таким же телом (что маловероятно, т.к. ID уже уникален).

### 5.6. `on_duplicate` — Стратегия уникализации

- **Что делает:** определяет, как строить суффикс, если базовый алиас уже занят **и** `prepend_id` выключен.
- **Варианты:**

| Значение | Как строится суффикс | Кол-во попыток | Особенность |
|---|---|---|---|
| `ID` | `(string)$page_id` | 1 | Полностью детерминирован. Одна попытка — либо ID свободен, либо используется fallback. |
| `Hash` | `substr(md5($title . '|' . $attempt), 0, 6)` | до 10 | Детерминирован по заголовку. Первые 6 символов MD5. |
| `Random` | `random_int(2, 9999)` | до 10 | **Недетерминирован.** При пересоздании в другой раз может дать другой суффикс. |

- **Как работает цикл:** см. раздел [«Уникализация»](#6-4-уникализация-и-сохранение).

### 5.7. `perpage_list` — Записей на страницу в «Списки и Алиасы»

- **Что делает:** задаёт размер страницы пагинации во второй вкладке админки (только просмотр).
- **По умолчанию:** `25`. Минимум принудительно ограничен значением `5` (в коде `max(5, (int) ...)`).

### 5.8. `perpage_manage` — Записей на страницу в «Управление»

- **Что делает:** задаёт размер страницы пагинации в третьей вкладке админки (редактирование).
- **По умолчанию:** `10`. Минимум `5`.

---

## 6. Логика генерации алиаса

Вся логика реализована в классе `AliasPagePro` (файл `plugins/aliaspagepro/inc/aliaspagepro.functions.php`). Класс `final`, инстанцировать нельзя — используются статические методы.

Общий поток сохранения страницы:

```
Хук (page.add.add.done / page.edit.update.done)
    ↓
if (empty($rpage['page_alias']))
    ↓
aliaspagepro_update($title, $id)         ← BC-обёртка
    ↓
AliasPagePro::assign($title, $id)
    ↓
┌── нормализация заголовка
├── сборка базового кандидата
├── проверка уникальности в БД
│       ├── если свободен → сохранить и вернуть
│       └── если занят → добавить суффикс, повторить (до 10 раз)
├── если все попытки провалились → fallback (md5)
└── сохранить в page_alias и вернуть
```

### 6.1. Нормализация заголовка

Приватный метод `normalizeTitle($title, $config)`. Шаги:

1. **`trim()`** — обрезаются пробелы по краям.
2. Если строка пуста — возвращается пустая строка. Дальше по коду она будет заменена на `'page'` (см. 6.2).
3. **Транслитерация** (если `translit = 1`): `strtr($str, TRANSLIT_MAP)`. Заменяются все кириллические символы (RU + UK) на латиницу.
4. **Приведение к нижнему регистру** (если `lowercase = 1`): `mb_strtolower($str, 'UTF-8')`.
5. **Схлопывание пробелов:** `preg_replace('/\s+/u', ' ', $str)`. Множественные пробелы, табы, переносы — всё превращается в один пробел.
6. **Удаление лишних символов:** `preg_replace('/[^\p{L}\p{N}\s_\-]/u', '', $str)`. Оставляются только Unicode-буквы (`\p{L}`), Unicode-цифры (`\p{N}`), пробел (`\s`), `_` и `-`. Всё остальное удаляется: `?`, `!`, `«», (, ), /`, `.`, `:`, `;`, `№`, `#` и т.д.
7. **Замена пробелов на разделитель:** `str_replace(' ', $sep, $str)`.
8. **Схлопывание повторных разделителей:** `preg_replace('/(' . preg_quote($sep) . ')+/u', $sep, $str)`. Если в исходной строке было несколько пробелов подряд (после шага 5 это не так, но после других преобразований может появиться) — они не превратятся в `----`.
9. **Обрезка разделителей и подчёркиваний на краях:** `trim($str, $sep . '_-.')`.

**Результат шага:** «чистое» тело алиаса, без пунктуации, с единым разделителем, в нижнем регистре.

### 6.2. Сборка кандидата

Приватный метод `assemble($prefix, $body, $suffix, $maxLen, $sep)`.

1. Если `body` пуст — подставляется строка `'page'` (делается до вызова `assemble`, в `convert()` и `assign()`).
2. **Формируется префикс:** если `prepend_id = 1` и `$id > 0` → `"{$id}{$sep}"`, иначе пустая строка.
3. **Формируется суффикс:** если суффикс не пустой → `"{$sep}{$suffix}"`, иначе пустая строка.
4. **Считается бюджет:** `budget = maxLen − mb_strlen(prefix) − mb_strlen(suffixPart)` — то есть сколько символов остаётся на тело.
5. Если **`budget < 1`** — префикс и суффикс вместе уже превышают лимит. В этом случае:
   - тело полностью отбрасывается;
   - итог = `mb_substr(prefix . suffixPart, 0, maxLen)` — жёсткая обрезка склейки.
6. Если **`budget ≥ 1`** — тело обрезается через `truncateBody()` (см. 6.3).
7. Склеивается `prefix + body + suffixPart`.
8. Если итог пуст — редкий fallback: `substr(md5(random_bytes(8)), 0, min(8, maxLen))`. Обычно не срабатывает, нужен только на случай полного отказа предыдущих шагов.
9. Метод возвращает строку.

### 6.3. Обрезка тела

Приватный метод `truncateBody($body, $budget, $sep)`.

1. Если `body` пуст или `budget ≤ 0` — возвращает пустую строку.
2. Если `mb_strlen($body) ≤ $budget` — обрезка не нужна, возвращает тело как есть.
3. Иначе:
   - `cut = mb_substr($body, 0, $budget)` — жёстко обрезаем до бюджета.
   - Ищем **последний `sep`** в `cut` через `mb_strrpos($cut, $sep)`.
   - Если найден и его позиция `> 0` (то есть не в самом начале) — отрезаем по нему: `cut = mb_substr($cut, 0, $pos)`.
   - Дополнительно чистим `sep` и `_-.` по краям через `rtrim`.
4. Если после всех манипуляций `cut === ''` (одно очень длинное слово без разделителей) — жёсткая обрезка без учёта слов: `rtrim(mb_substr($body, 0, $budget), $sep . ' _-.')`.
5. Возвращает результат.

**Зачем обрезать именно по слову:** чтобы не получить в URL обрубок слова — `kak-nastroit-ser` вместо `kak-nastroit-server`. Алгоритм «отступает» до последнего разделителя, который влезает в бюджет.

### 6.4. Уникализация и сохранение

Публичный метод `AliasPagePro::assign($title, $id)`.

1. **Нормализация:** тело = `normalizeTitle($title, $c)`.
2. Если тело пустое — `body = 'page'`.
3. **Формирование префикса:** если `prepend_id = 1` и `$id > 0` → `"{$id}{$sep}"`, иначе пусто.
4. **Попытка 0 (без суффикса):**
   - `candidate = assemble(prefix, body, '', maxLen, sep)`
   - `aliasExists($candidate, $id)` — проверка через БД:
     ```sql
     SELECT COUNT(*) FROM cot_pages WHERE page_alias = ? AND page_id <> ?
     ```
   - Если `0` — сохранить и вернуть.
5. **Попытки с суффиксом:**
   - `maxAttempts = 1` для стратегии `ID`, `10` для `Hash`/`Random`.
   - На каждой итерации `i = 1..maxAttempts`:
     - `suffix = makeSuffix(strategy, $id, $title, $i)`:
       - `ID` → `(string)$id`
       - `Hash` → `substr(md5($title . '|' . $i), 0, 6)` — детерминированный, зависит от заголовка и номера попытки
       - `Random` → `(string)random_int(2, 9999)`
     - `candidate = assemble(prefix, body, suffix, maxLen, sep)`
     - Если `!aliasExists($candidate, $id)` — сохранить и вернуть.
6. **Финальный fallback** (если все попытки провалились):
   - `candidate = mb_substr(md5($id . '|' . $title), 0, min(12, $maxLen))`
   - Сохранить и вернуть.
   - Этот хэш гарантированно детерминирован для конкретной пары «id + title» и уникален на уровне `page_id`, но вычислительно почти невозможно, чтобы он совпал у другой страницы.

### 6.5. Сохранение в БД

Приватный метод `saveAlias($id, $alias)`:

```php
Cot::$db->update(
    Cot::$db->pages,
    ['page_alias' => $alias],
    'page_id = ?',
    [$id]
);
```

Используется prepared statement — SQL-инъекция невозможна. Значение `$alias` уже прошло через регулярное выражение в `normalizeTitle` и содержит только разрешённые символы.

---

## 7. Примеры работы

Все примеры ниже даны для конфигурации по умолчанию:

```
translit = 1
lowercase = 1
sep = -
max_length = 36
prepend_id = 0
on_duplicate = ID
```

### 7.1. Короткие заголовки (без обрезки)

| Заголовок | Алиас | Длина |
|---|---|---:|
| `Привет` | `privet` | 6 |
| `Как настроить сервер` | `kak-nastroit-server` | 19 |
| `Про кота` | `pro-kota` | 8 |

Здесь сработал только `normalizeTitle`: транслитерация, lowercase, замена пробелов на `-`.

### 7.2. Средние заголовки (без обрезки)

| Заголовок | Алиас | Длина |
|---|---|---:|
| `Як правильно скасувати замовлення` | `yak-pravilno-skasuvati-zamovlennya` | 33 |
| `Как настроить сервер nginx` | `kak-nastroit-server-nginx` | 25 |

33 и 25 — обе строки помещаются в бюджет 36 символов. Обрезки нет.

### 7.3. Длинные заголовки (обрезка по последнему слову)

**Заголовок:** `Як оцінити результати CPC кампаній? Розбір метрик`

После `normalizeTitle`:

```
yak-otsiniti-rezultati-cpc-kampaniy-rozbir-metrik
```

Бюджет = 36. Жёсткая обрезка до 36 символов даёт:

```
yak-otsiniti-rezultati-cpc-kampaniy-
```

Последний `-` — на позиции 35. Отсекаем по нему:

```
yak-otsiniti-rezultati-cpc-kampaniy
```

**Итоговый алиас (35 символов):**

```
yak-otsiniti-rezultati-cpc-kampaniy
```

**Заголовок:** `Функция «Не звонить для подтверждения заказа»`

После `normalizeTitle`:

```
funktsiya-ne-zvonit-dlya-podtverzhdeniya-zakaza
```

Бюджет = 36. Обрезка до 36:

```
funktsiya-ne-zvonit-dlya-podtverzhde
```

Последний `-` — на позиции 25. Отсекаем:

```
funktsiya-ne-zvonit-dlya
```

**Итоговый алиас (24 символа):**

```
funktsiya-ne-zvonit-dlya
```

### 7.4. Одно очень длинное слово

**Заголовок:** `Оченьдлинноесловобезпробеловисовсембезпробелов`

После `normalizeTitle` (без единого разделителя, потому что в исходнике не было пробелов):

```
ochendlinnoeslovobezprobelovisovsembesprobelov
```

46 символов, бюджета не хватает. Жёсткая обрезка до 36:

```
ochendlinnoeslovobezprobelovisovsemb
```

`mb_strrpos` возвращает `false` (разделителей нет). Тело остаётся обрезанным посимвольно. Дополнительно `rtrim` не находит ничего на краях.

**Итоговый алиас (36 символов):**

```
ochendlinnoeslovobezprobelovisovsemb
```

### 7.5. Заголовок с пунктуацией и лишними пробелами

**Заголовок:** `Что   делать???  (когда — всё  плохо!)`

Шаги `normalizeTitle`:
1. `trim` → `Что   делать???  (когда — всё  плохо!)`
2. транслит → `Chto   delat???  (kogda — vsyo  plokho!)`
3. lowercase → `chto   delat???  (kogda — vsyo  plokho!)`
4. схлопывание пробелов → `chto delat??? (kogda — vsyo plokho!)`
5. удаление пунктуации → `chto delat kogda vsyo plokho`
6. замена пробелов на `-` → `chto-delat-kogda-vsyo-plokho`
7. схлопывание повторных `-` → (без изменений)
8. `trim` разделителей → (без изменений)

**Итоговый алиас (28 символов):**

```
chto-delat-kogda-vsyo-plokho
```

### 7.6. Дубликат при стратегии `ID`

Допустим, уже есть страница `page_id = 42` с алиасом `kupit-slona`. Добавляют новую страницу с заголовком `Купить слона` и `page_id = 152`.

- `normalizeTitle` → `kupit-slona`
- Попытка 0: `kupit-slona` уже занят (`page_id <> 152`)
- Попытка 1 (стратегия `ID`): суффикс = `152`, кандидат = `kupit-slona-152`
- Проверка: свободен — сохраняется

**Итоговый алиас:**

```
kupit-slona-152
```

### 7.7. Дубликат при стратегии `Hash`

Та же ситуация: `kupit-slona` занят. `on_duplicate = Hash`.

- Попытка 1: суффикс = `substr(md5('Купить слона|1'), 0, 6)` = например `a1b2c3`. Кандидат: `kupit-slona-a1b2c3`. Если свободен — сохраняется.

**Итоговый алиас:**

```
kupit-slona-a1b2c3
```

Важно: суффикс зависит только от `title` и `attempt`. При повторной генерации в другой день для того же заголовка получится **тот же суффикс**. Это делает URL стабильными.

### 7.8. С включённым `prepend_id`

`prepend_id = 1`, `max_length = 36`.

| Заголовок | page_id | Алиас | Длина |
|---|---:|---|---:|
| `Как настроить сервер` | 152 | `152-kak-nastroit-server` | 25 |
| `Привет` | 7 | `7-privet` | 9 |

Префикс `152-` занимает 4 символа. Бюджет на тело: `36 − 4 − 0 = 32`. Тело `kak-nastroit-server` = 19 символов — влезает без обрезки.

### 7.9. Обрезка при `max_length = 24`

`max_length = 24`, `prepend_id = 0`, без суффикса.

| Заголовок | Алиас | Длина |
|---|---|---:|
| `Як правильно скасувати замовлення` | `yak-pravilno-skasuvati` | 22 |
| `Как настроить сервер nginx` | `kak-nastroit-server` | 19 |
| `Функция «Не звонить для подтверждения»` | `funktsiya-ne-zvonit` | 18 |

Бюджет = 24. Для первого примера:
- `yak-pravilno-skasuvati-zamovlennya` (33) → обрезка до 24 → `yak-pravilno-skasuvati-za` → последний `-` на позиции 22 → `yak-pravilno-skasuvati`.

Для третьего:
- `funktsiya-ne-zvonit-dlya-podtverzhdeniya` (41) → обрезка до 24 → `funktsiya-ne-zvonit-dlya` → последний `-` на позиции 18 → `funktsiya-ne-zvonit`.

---

## 8. Административная панель

**Адрес:** `admin.php?m=other&p=aliaspagepro&tab=stats`  
**Меню:** Админ → aliaspagepro

Плагин подключается к Cotonti через хук `tools`, поэтому появляется в разделе инструментов администратора.

### 8.1. Вкладка «Статистика» (`tab=stats`)

Показывает четыре счётчика:

| Показатель | SQL-условие |
|---|---|
| Всего страниц | `page_title <> ''` |
| С алиасом | `page_title <> '' AND page_alias <> ''` |
| Без алиаса | `page_title <> '' AND page_alias = ''` |
| Без Meta Title | `page_title <> '' AND (page_metatitle = '' OR page_metatitle IS NULL)` |

Счётчики — целые числа, выводятся в четырёх цветных карточках. Полезны как «индикатор здоровья»: если «Без алиаса» больше нуля, стоит запустить «Создать алиасы».

Три кнопки:

- **Создать алиасы** (`a=create`) — проходит по всем страницам с пустым `page_alias` и генерирует для них алиасы через `aliaspagepro_update()`. По завершении показывает уведомление: `Алиасов записано: N`.
- **Конфигурация** — открывает страницу настроек плагина (`admin.php?m=config&n=edit&o=plug&p=aliaspagepro`).
- **Очистить все алиасы** (`a=clear_aliases`) — выполняет `UPDATE cot_pages SET page_alias = ''`. Спрашивает подтверждение через JavaScript `confirm()`. По завершении показывает уведомление: `Все алиасы очищены`.

**Осторожно с кнопкой «Очистить все алиасы»:** она обнуляет алиасы у **всех** страниц без исключения. Если URL страниц зависят от алиаса, до следующего запуска «Создать алиасы» страницы могут открываться по ID или возвращать 404. Используйте только в паре: очистить → сразу создать.

### 8.2. Вкладка «Списки и Алиасы» (`tab=list`)

Таблица только для просмотра:

| Колонка | Источник данных |
|---|---|
| `ID` | `page_id` |
| Заголовок | `page_title`, оформлен ссылкой на страницу (открывается в новой вкладке) |
| Meta Title | `page_metatitle` |
| Алиас | `page_alias` |

Пагинация управляется настройкой `perpage_list` (по умолчанию 25). Строки отсортированы по `page_id DESC`.

Если страниц нет — выводится сообщение «Страниц нет».

### 8.3. Вкладка «Управление» (`tab=manage`)

Редактируемая форма. Каждая строка содержит три поля `<input>`:

- Заголовок (`page_title`)
- Meta Title (`page_metatitle`)
- Алиас (`page_alias`)

Скрытое поле `ids[]` передаёт `page_id`. Кнопка **Update** отправляет `POST` с `a=update`.

**Серверная обработка** (при `a=update` и `REQUEST_METHOD === 'POST'`):

1. Из `$_POST` извлекаются массивы `ids`, `title`, `metatitle`, `alias`.
2. Для каждого `id`:
   - `trim()` всех строковых значений.
   - Алиас дополнительно очищается регуляркой: `preg_replace('/[^a-z0-9_\-\.]/i', '', $alias)` — это защита от попыток внести `?`, `#`, `&`, `/` и другие символы, ломающие URL.
   - Выполняется `UPDATE cot_pages SET page_title = ?, page_metatitle = ?, page_alias = ? WHERE page_id = ?` через prepared statement.
3. Показывается уведомление `Данные успешно обновлены` и редирект на ту же страницу с текущим `d` (смещение пагинации).

Пагинация управляется настройкой `perpage_manage` (по умолчанию 10).

**Важно:** здесь плагин **не пересоздаёт** алиас из заголовка. Если вы изменили заголовок, но оставили алиас пустым — после сохранения алиас останется пустым. Чтобы сгенерировать его заново, запустите «Создать алиасы» на вкладке «Статистика».

---

## 9. Хук Cotonti

Файл `plugins/aliaspagepro/aliaspagepro.page.php`.

```php
if (empty($rpage['page_alias'])) {
    require_once cot_incfile('aliaspagepro', 'plug');
    $rpage['page_alias'] = aliaspagepro_update(
        (string) $rpage['page_title'],
        (int) $id
    );
}
```

- **Хуки:** `page.add.add.done`, `page.edit.update.done`.
- **Условие срабатывания:** пустой `$rpage['page_alias']`.
- **Что делает:** вызывает BC-обёртку `aliaspagepro_update()`, которая передаёт управление `AliasPagePro::assign()`. Метод `assign()` проверяет уникальность и сохраняет результат в БД. Возвращённая строка присваивается `$rpage['page_alias']`, чтобы быть доступной следующему в цепочке коду.
- **Если поле уже заполнено** — блок `if` не срабатывает, плагин молча пропускает страницу.

---

## 10. API для разработчиков

Все методы — статические, класс `final` с приватным конструктором (инстанцировать нельзя).

```php
require_once cot_incfile('aliaspagepro', 'plug');
```

### 10.1. `AliasPagePro::transliterate(string $str): string`

Транслитерирует кириллицу (RU + UK) в латиницу. Не трогает остальные символы — цифры, латиницу, пунктуацию — возвращает как есть.

```php
AliasPagePro::transliterate('Привет');
// → 'Privet'

AliasPagePro::transliterate('Привіт');
// → 'Privit'

AliasPagePro::transliterate('Hello, мир!');
// → 'Hello, mir!'
```

### 10.2. `AliasPagePro::convert(string $title, int $id = 0): string`

Генерирует алиас детерминированно: нормализует заголовок, применяет префикс `prepend_id` (если включён и `$id > 0`), обрезает до `max_length`.

**Не обращается к БД.** Не проверяет уникальность. Не сохраняет результат.

Применение: для превью, для отображения «предполагаемого» алиаса в собственных формах, для отладки.

```php
AliasPagePro::convert('Как настроить сервер', 0);
// → 'kak-nastroit-server'

AliasPagePro::convert('Длинный заголовок про настройку', 152);
// при prepend_id = 1 → '152-dlinnyy-zagolovok-pro'
```

> Для сценария, где нужно проверить уникальность и записать в `page_alias` — используйте `assign()`
> подробно в смотрите ниже в разделе **Как пользоваться `AliasPagePro::assign()`**.

### 10.3. `AliasPagePro::assign(string $title, int $id): string`

Полный цикл: генерирует алиас, проверяет уникальность в БД, при необходимости добавляет суффикс, сохраняет в `page_alias` и возвращает итоговую строку.

```php
$alias = AliasPagePro::assign('Как настроить сервер', 152);
// → 'kak-nastroit-server'
//   либо 'kak-nastroit-server-152', если уже был занят
```

Именно этот метод вызывается хуком через BC-обёртку `aliaspagepro_update()`. Если вы вызываете генерацию алиаса из своего кода — вызывайте `assign()`.

### 10.4. Enum `AliasPagePro_DuplicateStrategy`

```php
enum AliasPagePro_DuplicateStrategy: string
{
    case Id     = 'ID';
    case Hash   = 'Hash';
    case Random = 'Random';
}
```

Используется в конфиге, чтобы безопасно перевести строку настройки в строгий тип (через `tryFrom()`).

### 10.5. BC-функции (для обратной совместимости)

Обёртки над методами класса. Сохранены для того, чтобы сторонние плагины/шаблоны, которые вызывают функции по именам, продолжали работать.

| Функция | Что делает | Эквивалент |
|---|---|---|
| `aliaspagepro_transliterate(string $str): string` | Транслитерация | `AliasPagePro::transliterate()` |
| `aliaspagepro_convert(string $title, int $id = 0, bool $duplicate = false): string` | Генерация без сохранения | `AliasPagePro::convert()`. Третий аргумент `$duplicate` игнорируется. |
| `aliaspagepro_update(string $title, int $id): string` | Генерация + проверка + сохранение | `AliasPagePro::assign()` |

### 10.6. Внутренние константы класса

| Константа | Значение | Назначение |
|---|---|---|
| `DEFAULT_MAX_LENGTH` | `36` | Fallback, если `max_length` в конфиге не задан |
| `DEFAULT_SEPARATOR` | `-` | Fallback для разделителя |
| `MAX_ATTEMPTS` | `10` | Лимит попыток уникализации (только для `Hash`/`Random`) |
| `RANDOM_MIN` | `2` | Нижняя граница `Random` |
| `RANDOM_MAX` | `9999` | Верхняя граница `Random` |
| `HASH_LENGTH` | `6` | Длина хэш-суффикса для `Hash` |

### 10.7. Пример: массовая генерация из своего кода

```php
require_once cot_incfile('aliaspagepro', 'plug');

$res = Cot::$db->query(
    "SELECT page_id, page_title FROM cot_pages WHERE page_alias = ''"
);

while ($row = $res->fetch()) {
    AliasPagePro::assign($row['page_title'], (int) $row['page_id']);
}
```

---

## 11. Структура файлов

```
plugins/aliaspagepro/
├── aliaspagepro.setup.php              # Регистрация в Cotonti, объявление настроек
├── aliaspagepro.page.php               # Хук на события page.add / page.edit
├── aliaspagepro.admin.php              # Админ-панель (3 вкладки: статистика, список, управление)
├── inc/
│   └── aliaspagepro.functions.php      # Ядро: класс AliasPagePro, enum, BC-функции
├── lang/
│   ├── aliaspagepro.ru.lang.php        # Русская локализация
│   └── aliaspagepro.en.lang.php        # Английская локализация
└── tpl/
    └── aliaspagepro.admin.tpl          # HTML-шаблон админ-панели
```

| Файл | Ответственность |
|---|---|
| `aliaspagepro.setup.php` | Метаданные плагина (код, версия, зависимости) и объявление всех настроек в блоке `[BEGIN_COT_EXT_CONFIG]` |
| `aliaspagepro.page.php` | Хук — точка входа при сохранении страницы |
| `aliaspagepro.admin.php` | Вся логика админ-панели: статистика, списки, обновление алиасов, массовые операции |
| `inc/aliaspagepro.functions.php` | Вся бизнес-логика генерации |
| `lang/*.lang.php` | Локализация: заголовки настроек, подсказки, тексты интерфейса |
| `tpl/aliaspagepro.admin.tpl` | Разметка админ-панели, использует теги `{TAB_*}`, `{STATS_*}`, `{LIST_ROW_*}`, `{MANAGE_*}`, `{PAGINATION}` |

---

## 12. Сценарий: массовый импорт статей

Плагин удобен именно в кейсе «импортировали N статей с длинными заголовками и без алиасов».

### Рекомендуемые настройки для импорта

| Ключ | Рекомендация | Почему |
|---|---|---|
| `translit` | `1` | Импортированные заголовки на кириллице → латиница |
| `lowercase` | `1` | Стандарт для URL |
| `sep` | `-` | Наиболее читаемо |
| `max_length` | `36` | Баланс между читаемостью и компактностью |
| `prepend_id` | `0` | Не нужен, если уникальность решается через Hash/ID |
| `on_duplicate` | `Hash` | Детерминированный суффикс — URL стабильны при пересоздании |

### Порядок работы после импорта

1. **Если импорт сохраняет `page_alias = ''`** — хук `page.add.add.done` сработал, и алиасы уже сгенерированы при каждой вставке страницы. Проверьте на вкладке **«Списки и Алиасы»**, что все колонки «Алиас» заполнены.
2. **Если импорт записывает `page_alias = NULL`** или вставляет данные напрямую через SQL в обход Cotonti-хуков:
   - Перейдите в **Админ → aliaspagepro → Статистика**.
   - Убедитесь, что счётчик «Без алиаса» показывает ожидаемое число.
   - Нажмите **Создать алиасы**.
   - Дождитесь уведомления `Алиасов записано: N`.
3. Проверьте результат на вкладке **«Списки и Алиасы»**: длина каждого алиаса не должна превышать `max_length`, все колонки «Алиас» заполнены.
4. При необходимости внесите точечные правки на вкладке **«Управление»**.

### Проверка целостности через SQL

Убедиться, что дубликатов алиасов в БД нет:

```sql
SELECT page_alias, COUNT(*) AS cnt
FROM cot_pages
WHERE page_alias <> ''
GROUP BY page_alias
HAVING cnt > 1;
```

Запрос должен вернуть 0 строк. Уникальность гарантирована методом `assign()` — проверка выполняется перед каждой записью.

Проверить, что все алиасы укладываются в `max_length`:

```sql
SELECT page_id, page_alias, CHAR_LENGTH(page_alias) AS len
FROM cot_pages
WHERE page_alias <> '' AND CHAR_LENGTH(page_alias) > 36
ORDER BY len DESC;
```

Где `36` — ваше значение `max_length`.

---

## 13. Решение проблем

### Алиасы не генерируются при добавлении страницы

Возможные причины:

1. **Плагин не установлен или отключён.** Проверьте **Админ → Расширения** — строка `aliaspagepro` должна быть активна.
2. **Поле `page_alias` уже заполнено.** Плагин не перезаписывает непустые алиасы — это его штатное поведение.
3. **Модуль `page` отключён или заменён.** `aliaspagepro.setup.php` требует `Requires_modules=page`, но если модуль фактически не загружается — хуки не сработают.
4. **Кэш.** Если включён кэш Cotonti, очистите его: **Админ → Инструменты → Очистить кэш**.

### Алиасы получаются слишком короткими

Слишком маленькое значение `max_length` (например, `24`) при включённом `prepend_id = 1`:

- Префикс ID = например `152-` (4 символа).
- Остаток на тело = `24 − 4 = 20`.
- Заголовок из 5 слов обрезается почти сразу.

**Решение:** увеличьте `max_length` до `36` или `52`, либо отключите `prepend_id`.

### Одинаковые заголовки дают одинаковые алиасы, но БД не принимает второй

Стратегия `ID` гарантирует уникальность через суффикс `-{id}`, но **одна попытка**. Если даже `kupit-slona-152` занят (крайне редкий случай, например, кто-то вручную установил такой алиас другой странице) — срабатывает детерминированный fallback: `substr(md5(id . '|' . title), 0, 12)`. Уникальность сохраняется.

Если вы видите в БД одинаковые алиасы — это означает, что кто-то (не плагин) записал их напрямую или отключил проверку. Плагин так сделать не может.

### Кнопка «Создать алиасы» ничего не делает

Проверьте статистику: если счётчик **«Без алиаса»** показывает `0` — генерировать нечего. Плагин прошёл по пустому списку страниц.

### После «Очистить все алиасы» страницы стали недоступны

Это ожидаемое поведение. Кнопка выполняет `UPDATE cot_pages SET page_alias = ''` — обнуляет **все** алиасы. Если маршрутизация Cotonti использует `page_alias`, страницы могут отдавать 404 до следующего запуска «Создать алиасы».

**Решение:** сразу после очистки нажмите **Создать алиасы**, чтобы восстановить алиасы для всех страниц.

### Транслитерация не работает

Проверьте:

1. `translit = 1` в настройках.
2. Тест через API:
   ```php
   AliasPagePro::transliterate('Привет');
   // должно вернуть 'Privet'
   ```
3. Если возвращает исходную строку — плагин не подключён через `cot_incfile('aliaspagepro', 'plug')`.

### В логах `PHP Fatal error: Cannot instantiate final class`

Класс `AliasPagePro` объявлен как `final` с **приватным** конструктором. Создавать экземпляры нельзя. Используйте только статические методы:

- `AliasPagePro::transliterate()`
- `AliasPagePro::convert()`
- `AliasPagePro::assign()`

### Cotonti показывает ошибку `Requires_modules=page`

Плагин требует активный модуль `page`. Включите его в **Админ → Расширения → page**.

### Конфликт с `autoalias2lance`

Плагин явно предупреждает: `Notes=DO NOT INSTALL with autoalias2lance`. Оба подключаются к одним и тем же хукам и будут генерировать алиасы дважды, что приводит к перезаписи последним установленным. Удалите или отключите один из них.

---

## 14. Глоссарий

| Термин | Что означает в контексте этого плагина |
|---|---|
| **Алиас** | Строка, которая становится частью URL страницы. В Cotonti хранится в поле `page_alias`. Например, для `example.com/kak-nastroit-server` алиас — это `kak-nastroit-server`. |
| **Слаг (slug)** | Синоним «алиаса», заимствованный из западной практики (WordPress, Django). Означает human-readable часть URL. |
| **Заголовок (title)** | Поле `page_title` в таблице страниц. Текст, который видит пользователь. Из него генерируется алиас. |
| **Транслитерация** | Замена символов одной письменности на символы другой. В данном плагине — кириллица → латиница. Например, `Привет` → `Privet`. |
| **Разделитель (separator, `sep`)** | Символ, которым заменяются пробелы между словами. Стандарт — дефис `-`. Плагин также использует его в логике обрезки «по последнему слову». |
| **Бюджет (budget)** | Внутренняя переменная алгоритма: сколько символов остаётся на «тело» алиаса (т.е. без префикса и суффикса). `budget = max_length − len(prefix) − len(suffix)`. |
| **Тело (body)** | Нормализованная часть алиаса, полученная из заголовка. Стоит между префиксом и суффиксом. Например, в `152-kak-nastroit-server-4729` тело — `kak-nastroit-server`. |
| **Префикс** | Часть алиаса, добавляемая **спереди**. В плагине — только ID страницы, если `prepend_id = 1`. |
| **Суффикс** | Часть алиаса, добавляемая **в конце**. В плагине — суффикс уникализации (`-152`, `-a1b2c3`, `-4729`). |
| **Уникализация** | Процесс подбора уникального алиаса, если базовый уже занят. Включает проверку через `SELECT COUNT(*)` и добавление суффикса. |
| **Стратегия уникализации (`on_duplicate`)** | Правило формирования суффикса: `ID` (по ID страницы), `Hash` (по хэшу заголовка) или `Random` (случайное число). |
| **Детерминированный** | Такой, который при одинаковом входе даёт одинаковый выход. Например, `Hash`-стратегия детерминирована: для `Купить слона` всегда даст один и тот же суффикс. `Random` — нет. |
| **Fallback** | Резервный алгоритм, который срабатывает, если основной не справился. В плагине — `md5(id . title)` для уникализации, если все попытки провалились, и `'page'` для пустого заголовка. |
| **Нормализация** | Приведение строки к «чистому» виду: транслитерация, lowercase, удаление пунктуации, замена пробелов, схлопывание повторных разделителей, `trim` по краям. |
| **Хук (hook)** | Механизм Cotonti для расширения поведения модулей. Плагин «вешается» на событие и выполняет свой код при его возникновении. |
| **`page.add.add.done`** | Событие Cotonti: срабатывает после того, как новая страница добавлена в БД. |
| **`page.edit.update.done`** | Событие Cotonti: срабатывает после того, как существующая страница обновлена. |
| **`tools`** | Событие Cotonti для подключения к разделу «Инструменты» админ-панели. Используется для встраивания вкладок управления плагином. |
| **BC (backward compatibility)** | Обратная совместимость. BC-функции — обёртки, сохраняющие работоспособность старого кода после рефакторинга. |
| **`prepend_id`** | Настройка плагина. Если включена — гарантирует уникальность алиаса за счёт префикса `{id}-`, суффикс уникализации при этом не применяется. |
| **`max_length`** | Настройка плагина. Максимальная длина **итогового** алиаса (включая префикс и суффикс). |
| **`\p{L}` / `\p{N}`** | Символьные классы PCRE для Unicode-букв и Unicode-цифр соответственно. Используются в `normalizeTitle`, чтобы правильно работать с кириллицей и другими алфавитами. |
| **`mb_strtolower` / `mb_strlen` / `mb_substr`** | Многобайтовые функции PHP для работы со строками в UTF-8. Обычные `strtolower` / `strlen` / `substr` **не работают** с кириллицей — они оперируют байтами, а не символами. |
| **`strtr`** | Функция PHP, выполняющая замену по таблице. Применяется для транслитерации: порядок в таблице имеет значение, длинные последовательности обрабатываются раньше коротких. |
| **`random_int`** | Криптографически стойкий генератор случайных чисел в PHP 7+. Используется в стратегии `Random` — в отличие от `rand()`, не предсказуем. |
| **Prepared statement** | Способ выполнения SQL-запроса с подстановкой параметров отдельно от текста запроса. Защищает от SQL-инъекций. В Cotonti реализуется через `Cot::$db->update(..., 'page_id = ?', [$id])`. |
| **MD5** | Криптографическая хэш-функция. В плагине используется для генерации детерминированных суффиксов (`Hash`) и финального fallback. Не для безопасности — только для получения короткого уникального идентификатора из строки. |
| **Meta Title** | Поле `page_metatitle` в таблице страниц. Используется в `<title>` HTML-страницы. Плагин показывает и позволяет редактировать его в админке, но **не генерирует** — это отдельная задача. |
| **Пагинация** | Постраничная навигация. В админке управляется настройками `perpage_list` и `perpage_manage`. |
| **Вкладка (tab)** | Раздел админ-панели плагина. Три вкладки: «Статистика», «Списки и Алиасы», «Управление». |

---


## Как пользоваться `AliasPagePro::assign()`

Это обычный статический метод. Вызывается одной строкой. Возвращает строку — итоговый алиас.

## Минимальный пример

```php
require_once cot_incfile('aliaspagepro', 'plug');

$alias = AliasPagePro::assign('Купить слона', 152);
// $alias = 'kupit-slona'   (или 'kupit-slona-152', если уже занят)
```

Два аргумента:

| Аргумент | Тип | Что передать |
|---|---|---|
| `$title` | `string` | Заголовок страницы — из чего генерировать |
| `$id` | `int` | `page_id` страницы, для которой генерируем |

Метод **сам** сохранит результат в `page_alias` через `UPDATE`. Вам ничего записывать в БД не нужно.

---

## Куда это вставлять

`assign()` вызывают из **своего** кода — когда плагин не сработал автоматически (например, при импорте в обход хуков) или когда вы пишете собственный скрипт.

### Пример 1: разовая генерация для одной страницы

Откройте консоль Cotonti (или временный php-файл в корне, потом удалите) и выполните:

```php
<?php
define('COT_CODE', TRUE);
require_once './datas/config.php';
require_once $cfg['system_dir'] . '/common.php';
require_once cot_incfile('aliaspagepro', 'plug');

$alias = AliasPagePro::assign('Купить слона', 152);
var_dump($alias);
```

Откройте этот файл в браузере — увидите сгенерированный алиас, и он уже будет в БД.

### Пример 2: генерация для всех страниц с пустым алиасом

Это ровно то, что делает кнопка «Создать алиасы» в админке. Если хочется сделать своим скриптом — вот как:

```php
<?php
define('COT_CODE', TRUE);
require_once './datas/config.php';
require_once $cfg['system_dir'] . '/common.php';
require_once cot_incfile('aliaspagepro', 'plug');

$res = Cot::$db->query(
    "SELECT page_id, page_title FROM cot_pages WHERE page_alias = ''"
);

$count = 0;
while ($row = $res->fetch()) {
    AliasPagePro::assign((string)$row['page_title'], (int)$row['page_id']);
    $count++;
}

echo "Обработано: $count";
```

### Пример 3: из своего плагина

```php
// plugins/myplugin/myplugin.page.php
if (empty($rpage['page_alias'])) {
    require_once cot_incfile('aliaspagepro', 'plug');
    $rpage['page_alias'] = AliasPagePro::assign(
        (string)$rpage['page_title'],
        (int)$id
    );
}
```

Это буквально то, что делает штатный хук aliaspagepro — только вы вызываете из своего места.

### Пример 4: для превью БЕЗ сохранения (используйте `convert()`)

Если нужно **только показать** алиас, а записывать не нужно:

```php
$preview = AliasPagePro::convert('Купить слона', 152);
// $preview = 'kupit-slona'
// В БД ничего не записано
```

---

## Ключевые моменты

1. **`assign()` пишет в БД.** После вызова в `page_alias` страницы уже лежит результат. Повторно записывать не нужно.

2. **`assign()` идемпотентен** для стратегии `ID` и `Hash` — при повторном вызове для той же страницы с тем же заголовком получите тот же алиас (если он свободен). `Random` — нет.

3. **Проверка уникальности внутри.** `assign()` сам делает `SELECT COUNT(*)`, сам добавляет суффикс, сам повторяет до 10 раз. Вам об этом думать не надо.

4. **`assign()` не трогает заголовок и meta title.** Меняет только `page_alias`.

5. **Тип возврата — `string`.** Всегда. Даже если заголовок пустой (вернёт `'page'`). Даже если всё плохо (вернёт `md5` fallback).

---

## Шпаргалка: что вызывать

| Задача | Метод |
|---|---|
| Просто транслитерировать строку | `AliasPagePro::transliterate($str)` |
| Показать превью алиаса, без записи в БД | `AliasPagePro::convert($title, $id)` |
| **Сгенерировать + проверить + сохранить** | **`AliasPagePro::assign($title, $id)`** |
| Массово заполнить пустые алиасы | Кнопка «Создать алиасы» в админке или цикл с `assign()` |

Если пользуетесь штатным хуком плагина (включён всегда при активном плагине) — вызывать `assign()` руками **не нужно вообще**. Он уже вызывается автоматически при сохранении каждой страницы. Ручной вызов нужен только для своих скриптов, импорта или отладки.


---  

## 15. Лицензия

BSD. См. заголовки файлов плагина.

```
Copyright (c) 2026 webitproff
https://github.com/webitproff/aliaspagepro-cotonti
```

**Автор:** webitproff — https://github.com/webitproff

**Ссылки:**

- 🏠 Репозиторий: https://github.com/webitproff/aliaspagepro-cotonti
- 💬 Поддержка: https://abuyfile.com/forums/cotonti/custom/plugs/
- 🛒 Маркетплейс: https://abuyfile.com/ru/market/cotonti/plugs/aliaspagepro
- 🌐 Cotonti: https://www.cotonti.com




  
