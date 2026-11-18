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
 *
 * Date: 2026-11-18
 * @package aliaspagepro
 * @version 3.0.0
 * @author webitproff
 * @copyright Copyright (c) webitproff 2026 | https://github.com/webitproff/aliaspagepro-cotonti
 * @license BSD
 */



// ===== PLUGIN INFO =====
$L['info_name']  = 'Alias Page PRO';
$L['info_desc']  = 'Автоматическое создание алиасов страниц из заголовков с настраиваемой длиной.';
$L['info_notes'] = 'Документация: <a target="_blank" href="https://github.com/webitproff/aliaspagepro-cotonti">README.md</a>.';

$L['aliaspagepro_title'] = $L['info_name'];
$L['aliaspagepro_desc']  = $L['info_desc'];
$L['aliaspagepro_name']  = $L['info_name'];

// ===== SETTINGS =====
$L['cfg_translit']      = 'Транслитерировать нелатинские символы';
$L['cfg_translit_hint'] = 'Кириллица будет преобразована в латиницу перед генерацией алиаса.';

$L['cfg_lowercase']      = 'Приводить алиас к нижнему регистру';
$L['cfg_lowercase_hint'] = 'Все буквы алиаса станут строчными.';

$L['cfg_sep']        = 'Разделитель слов';
$L['cfg_sep_hint']   = 'Символ, которым заменяются пробелы между словами.';
$L['cfg_sep_params'] = [
    '-' => 'Дефис (-)',
    '_' => 'Подчёркивание (_)',
    '.' => 'Точка (.)',
];

$L['cfg_max_length']        = 'Максимальная длина алиаса';
$L['cfg_max_length_hint']   = 'Символов в итоговом алиасе, включая префикс ID и суффикс уникализации. При превышении алиас обрезается по последнему целому слову.';
$L['cfg_max_length_params'] = [
    '24' => '24 символа',
    '36' => '36 символов',
    '52' => '52 символа',
    '78' => '78 символов',
];

$L['cfg_prepend_id']      = 'Добавлять ID страницы в начало алиаса';
$L['cfg_prepend_id_hint'] = 'Например, <code>123-nazvanie-stranicy</code>. Гарантирует уникальность без суффикса.';

$L['cfg_on_duplicate']        = 'Стратегия уникализации при дубликате';
$L['cfg_on_duplicate_hint']   = 'Что добавлять в конец алиаса, если такой уже существует (при выключенном «prepend ID»). <strong>Hash</strong> — детерминированный 6-символьный хэш; <strong>ID</strong> — ID страницы; <strong>Random</strong> — случайное 2–9999.';
$L['cfg_on_duplicate_params'] = [
    'ID'     => 'ID страницы (детерминированный)',
    'Hash'   => 'Хэш заголовка (детерминированный)',
    'Random' => 'Случайное число (2–9999)',
];

$L['cfg_perpage_list']        = 'Записей на страницу — вкладка «Списки и Алиасы»';
$L['cfg_perpage_list_hint']   = 'Число записей на одной странице списка.';
$L['cfg_perpage_manage']      = 'Записей на страницу — вкладка «Управление»';
$L['cfg_perpage_manage_hint'] = 'Число записей на одной странице редактирования.';

// ===== ADMIN UI =====
$L['aliaspagepro_tab_stats']  = 'Статистика';
$L['aliaspagepro_tab_list']   = 'Списки и Алиасы';
$L['aliaspagepro_tab_manage'] = 'Управление';

$L['aliaspagepro_stats_total_items']       = 'Всего страниц';
$L['aliaspagepro_stats_with_alias']        = 'С алиасом';
$L['aliaspagepro_stats_without_alias']     = 'Без алиаса';
$L['aliaspagepro_stats_without_metatitle'] = 'Без Meta Title';

$L['aliaspagepro_col_title']     = 'Заголовок';
$L['aliaspagepro_col_metatitle'] = 'Meta Title';
$L['aliaspagepro_col_alias']     = 'Алиас';
$L['aliaspagepro_no_items']      = 'Страниц нет';

$L['create_aliases']               = 'Создать алиасы';
$L['aliases_written']              = 'Алиасов записано: {$count}';
$L['aliaspagepro_update_success']  = 'Данные успешно обновлены';
$L['aliaspagepro_aliases_cleared'] = 'Все алиасы очищены';
$L['aliaspagepro_clear_all']       = 'Очистить все алиасы';
$L['aliaspagepro_confirm_clear']   = 'Вы уверены, что хотите очистить все алиасы?';
