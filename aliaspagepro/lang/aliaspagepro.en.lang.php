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
$L['info_desc']  = 'Automatically creates page aliases from titles if they are empty.';
$L['info_notes'] = 'Beginners must read the <strong>documentation <a target="_blank" href="https://github.com/webitproff/aliaspagepro-cotonti">in README.md</a> from the plugin repository</strong>.';

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
$L['cfg_translit']      = 'Transliterate non-Latin characters';
$L['cfg_translit_hint'] = 'If enabled, Cyrillic titles will be converted to Latin script.';

// --- Prepend ID ---
$L['cfg_prepend_id']      = 'Prepend page ID to alias';
$L['cfg_prepend_id_hint'] = 'E.g., "123-page-name" instead of just "page-name".';

// --- Duplicate handling ---
$L['cfg_on_duplicate']        = 'Behavior when duplicate alias is found';
$L['cfg_on_duplicate_hint']   = 'What to append to the alias if it already exists (when ID is not prepended).';
$L['cfg_on_duplicate_params'] = [
    'ID'     => 'Page ID',
    'Random' => 'Random number (2-99)',
];

// --- Word separator ---
$L['cfg_sep']        = 'Word separator in alias';
$L['cfg_sep_hint']   = 'Character that replaces spaces between words.';
$L['cfg_sep_params'] = [
    '-' => 'Hyphen (-)',
    '_' => 'Underscore (_)',
    '.' => 'Dot (.)',
];

// --- Lowercase ---
$L['cfg_lowercase']      = 'Convert alias to lowercase';
$L['cfg_lowercase_hint'] = 'All letters of the alias will become lowercase.';

// ========================
// MESSAGES AND ADMIN INTERFACE
// ========================

// --- Tabs ---
$L['aliaspagepro_tab_stats']   = 'Statistics';
$L['aliaspagepro_tab_list']    = 'Lists & Aliases';
$L['aliaspagepro_tab_manage']  = 'Management';

// --- Statistics ---
$L['aliaspagepro_stats_total_items']       = 'Total pages';
$L['aliaspagepro_stats_with_alias']        = 'With alias';
$L['aliaspagepro_stats_without_alias']     = 'Without alias';
$L['aliaspagepro_stats_without_metatitle'] = 'Without Meta Title';

// --- Table columns ---
$L['aliaspagepro_col_title']      = 'Title';
$L['aliaspagepro_col_metatitle']  = 'Meta Title';
$L['aliaspagepro_col_alias']      = 'Alias';
$L['aliaspagepro_no_items']       = 'No pages found';

// --- Actions ---
$L['create_aliases']              = 'Create aliases';
$L['aliases_written']             = 'Aliases written: {$count}';
$L['aliaspagepro_update_success'] = 'Data updated successfully';
$L['aliaspagepro_aliases_cleared'] = 'All aliases cleared';
$L['aliaspagepro_clear_all']       = 'Clear all aliases';
$L['aliaspagepro_confirm_clear']   = 'Are you sure you want to clear all aliases?';