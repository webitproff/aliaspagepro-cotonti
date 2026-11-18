<?php
/**
 * English language file for plugin Alias Page PRO
 * File: plugins/aliaspagepro/lang/aliaspagepro.en.lang.php
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


defined('COT_CODE') or die('Wrong URL');


$L['info_name']  = 'Alias Page PRO';
$L['info_desc']  = 'Generates SEO-friendly page aliases from titles with configurable length.';
$L['info_notes'] = 'Documentation: <a target="_blank" href="https://github.com/webitproff/aliaspagepro-cotonti">README.md</a>.';

$L['aliaspagepro_title'] = $L['info_name'];
$L['aliaspagepro_desc']  = $L['info_desc'];
$L['aliaspagepro_name']  = $L['info_name'];

$L['cfg_translit']      = 'Transliterate non-Latin characters';
$L['cfg_translit_hint'] = 'Cyrillic will be transliterated to Latin before alias generation.';

$L['cfg_lowercase']      = 'Lowercase alias';
$L['cfg_lowercase_hint'] = 'Convert all letters to lowercase.';

$L['cfg_sep']        = 'Word separator';
$L['cfg_sep_hint']   = 'Character used to replace spaces between words.';
$L['cfg_sep_params'] = [
    '-' => 'Hyphen (-)',
    '_' => 'Underscore (_)',
    '.' => 'Dot (.)',
];

$L['cfg_max_length']        = 'Maximum alias length';
$L['cfg_max_length_hint']   = 'Characters in the final alias, including ID prefix and uniqueness suffix. Truncation happens at the last whole word.';
$L['cfg_max_length_params'] = [
    '24' => '24 characters',
    '36' => '36 characters',
    '52' => '52 characters',
    '78' => '78 characters',
];

$L['cfg_prepend_id']      = 'Prepend page ID to alias';
$L['cfg_prepend_id_hint'] = 'E.g. <code>123-page-title</code>. Guarantees uniqueness without a suffix.';

$L['cfg_on_duplicate']        = 'Duplicate resolution strategy';
$L['cfg_on_duplicate_hint']   = 'What to append to the alias when it already exists (with prepend ID off). <strong>Hash</strong> — deterministic 6-char hash; <strong>ID</strong> — page ID; <strong>Random</strong> — random number 2–9999.';
$L['cfg_on_duplicate_params'] = [
    'ID'     => 'Page ID (deterministic)',
    'Hash'   => 'Title hash (deterministic)',
    'Random' => 'Random number (2–9999)',
];

$L['cfg_perpage_list']        = 'Items per page — List tab';
$L['cfg_perpage_list_hint']   = 'Number of items per list page.';
$L['cfg_perpage_manage']      = 'Items per page — Manage tab';
$L['cfg_perpage_manage_hint'] = 'Number of items per edit page.';

$L['aliaspagepro_tab_stats']  = 'Statistics';
$L['aliaspagepro_tab_list']   = 'Lists & Aliases';
$L['aliaspagepro_tab_manage'] = 'Manage';

$L['aliaspagepro_stats_total_items']       = 'Total pages';
$L['aliaspagepro_stats_with_alias']        = 'With alias';
$L['aliaspagepro_stats_without_alias']     = 'Without alias';
$L['aliaspagepro_stats_without_metatitle'] = 'Without Meta Title';

$L['aliaspagepro_col_title']     = 'Title';
$L['aliaspagepro_col_metatitle'] = 'Meta Title';
$L['aliaspagepro_col_alias']     = 'Alias';
$L['aliaspagepro_no_items']      = 'No pages';

$L['create_aliases']               = 'Create aliases';
$L['aliases_written']              = 'Aliases written: {$count}';
$L['aliaspagepro_update_success']  = 'Data updated successfully';
$L['aliaspagepro_aliases_cleared'] = 'All aliases cleared';
$L['aliaspagepro_clear_all']       = 'Clear all aliases';
$L['aliaspagepro_confirm_clear']   = 'Are you sure you want to clear all aliases?';
