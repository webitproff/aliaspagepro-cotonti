<?php
/**
 * Russian language file for plugin Alias Page PRO
 * File: plugins/aliaspagepro/lang/aliaspagepro.ru.lang.php
 *
 * All text strings used by the plugin in Cotonti interface:
 * - plugin name and description (info_name, info_desc)
 * - admin panel settings (cfg_…)
 * - field hints (cfg_…_hint)
 * - dropdown values (cfg_…_params)
 *
 * aliaspagepro plugin for Cotonti v1.+, PHP 8.5+, MySQL 8.4
 *
 * ReadMeMore:       https://abuyfile.com/ru/market/cotonti/plugs/aliaspagepro
 * Support:          https://abuyfile.com/forums/cotonti/custom/plugs/
 * Source:           https://github.com/webitproff/aliaspagepro-cotonti
 * 
 * Date: Aug 6Th, 2026
 * @package aliaspagepro
 * @version 2.5.1
 * @author webitproff
 * @copyright Copyright (c) webitproff 2026 | https://github.com/webitproff/aliaspagepro-cotonti
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL');

// ========================
// PLUGIN INFO (ADMIN)
// ========================
$L['info_name']  = 'Alias Page PRO';
$L['info_desc']  = 'Автоматическое создание алиасов страниц из заголовков, если они не заполнены.';
$L['info_notes'] = 'Новичкам обязательно читать <strong>документацию <a target="_blank" href="https://github.com/webitproff/aliaspagepro-cotonti">в README.md</a> из репозитория плагина</strong>.';

// ========================
// TITLES AND DESCRIPTIONS (same values, pulled by other keys)
// ========================
$L['aliaspagepro_title'] = $L['info_name'];
$L['aliaspagepro_desc']  = $L['info_desc'];
$L['aliaspagepro_name']  = $L['info_name'];

// ========================
// PLUGIN SETTINGS (ADMIN)
// ========================

// --- Transliteration ---
$L['cfg_translit']      = 'Транслитерировать нелатинские символы';
$L['cfg_translit_hint'] = 'Если включено, кириллические заголовки будут преобразованы в латиницу.';

// --- Prepend ID ---
$L['cfg_prepend_id']      = 'Добавить ID страницы в начало алиаса';
$L['cfg_prepend_id_hint'] = 'Например, "123-название-страницы" вместо простого "название-страницы".';

// --- Duplicate handling ---
$L['cfg_on_duplicate']        = 'Поведение при обнаружении дубликата алиаса';
$L['cfg_on_duplicate_hint']   = 'Что добавить к алиасу, если такой уже существует (если ID не добавляется в начало).';
$L['cfg_on_duplicate_params'] = [
    'ID'     => 'ID страницы',
    'Random' => 'Случайное число (2-99)',
];

// --- Word separator ---
$L['cfg_sep']        = 'Разделитель слов в алиасе';
$L['cfg_sep_hint']   = 'Символ, которым заменяются пробелы между словами.';
$L['cfg_sep_params'] = [
    '-' => 'Дефис (-)',
    '_' => 'Подчёркивание (_)',
    '.' => 'Точка (.)',
];

// --- Lowercase ---
$L['cfg_lowercase']      = 'Приводить алиас к нижнему регистру';
$L['cfg_lowercase_hint'] = 'Все буквы алиаса станут строчными.';

// ========================
// MESSAGES AND ADMIN INTERFACE
// ========================

// --- Tabs ---
$L['aliaspagepro_tab_stats']   = 'Статистика';
$L['aliaspagepro_tab_list']    = 'Списки и Алиасы';
$L['aliaspagepro_tab_manage']  = 'Управление';

// --- Statistics ---
$L['aliaspagepro_stats_total_items']       = 'Всего страниц';
$L['aliaspagepro_stats_with_alias']        = 'С алиасом';
$L['aliaspagepro_stats_without_alias']     = 'Без алиаса';
$L['aliaspagepro_stats_without_metatitle'] = 'Без Meta Title';

// --- Table columns ---
$L['aliaspagepro_col_title']      = 'Заголовок';
$L['aliaspagepro_col_metatitle']  = 'Meta Title';
$L['aliaspagepro_col_alias']      = 'Алиас';
$L['aliaspagepro_no_items']       = 'Страниц нет';

// --- Actions ---
$L['create_aliases'] = 'Создать алиасы';
$L['aliases_written'] = 'Алиасов записано: {$count}';
$L['aliaspagepro_update_success'] = 'Данные успешно обновлены';
$L['aliaspagepro_aliases_cleared'] = 'Все алиасы очищены';
$L['aliaspagepro_clear_all'] = 'Очистить все алиасы';
$L['aliaspagepro_confirm_clear'] = 'Вы уверены, что хотите очистить все алиасы?';