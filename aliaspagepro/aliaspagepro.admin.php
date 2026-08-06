<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=tools
[END_COT_EXT]
==================== */

/**
 * Alias Page PRO admin panel – modern multi-tab interface
 *
 * Filename: aliaspagepro.admin.php
 * aliaspagepro plugin for Cotonti v1.+, PHP 8.5+, MySQL 8.4
 *
 * ReadMeMore:       https://abuyfile.com/ru/market/cotonti/plugs/aliaspagepro
 * Support:          https://abuyfile.com/forums/cotonti/custom/plugs/
 * Source:           https://github.com/webitproff/aliaspagepro-cotonti
 *
 * Date: Aug 6Th, 2026
 *
 * @package Alias Page PRO
 * @version 2.5.1
 * @author webitproff
 * @copyright (c) webitproff 2026
 * @license BSD
 */


defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('aliaspagepro', 'plug');
require_once cot_langfile('aliaspagepro', 'plug');

$tab = cot_import('tab', 'G', 'ALP') ?: 'stats';
$a   = cot_import('a',   'G', 'ALP');

$t = new XTemplate(cot_tplfile('aliaspagepro.admin', 'plug', true));
$adminTitle = Cot::$L['aliaspagepro_name'];

/* ===== TABS NAVIGATION ===== */
$t->assign([
    'TAB_STATS_ACTIVE'  => $tab === 'stats'  ? 'active' : '',
    'TAB_LIST_ACTIVE'   => $tab === 'list'   ? 'active' : '',
    'TAB_MANAGE_ACTIVE' => $tab === 'manage' ? 'active' : '',
    'URL_STATS'         => cot_url('admin', ['m'=>'other','p'=>'aliaspagepro','tab'=>'stats']),
    'URL_LIST'          => cot_url('admin', ['m'=>'other','p'=>'aliaspagepro','tab'=>'list']),
    'URL_MANAGE'        => cot_url('admin', ['m'=>'other','p'=>'aliaspagepro','tab'=>'manage']),
]);

/* ===== Handle create / clear_aliases actions ===== */
if ($a === 'create' || $a === 'clear_aliases') {
    $backTab = cot_import('tab', 'G', 'ALP') ?: 'stats';
    $backUrl = cot_url('admin', [
        'm'   => 'other',
        'p'   => 'aliaspagepro',
        'tab' => $backTab,
    ]);
    $backUrl = str_replace('&amp;', '&', $backUrl); // remove HTML entities for redirect

    if ($a === 'create') {
        $count = 0;
        $res = Cot::$db->query(
            "SELECT page_id, page_title FROM $db_pages WHERE page_alias = ''"
        );
        foreach ($res->fetchAll() as $row) {
            aliaspagepro_update($row['page_title'], $row['page_id']);
            $count++;
        }
        $res->closeCursor();
        cot_message(cot_rc('aliases_written', ['count' => $count]));
    }

    if ($a === 'clear_aliases') {
        Cot::$db->update($db_pages, ['page_alias' => ''], '1');
        cot_message(Cot::$L['aliaspagepro_aliases_cleared']);
    }

    cot_redirect($backUrl);
}

/* ===== STATISTICS ===== */
if ($tab === 'stats') {
    $totalItems       = Cot::$db->query("SELECT COUNT(*) FROM $db_pages WHERE page_title != ''")->fetchColumn();
    $withAlias        = Cot::$db->query("SELECT COUNT(*) FROM $db_pages WHERE page_alias != '' AND page_title != ''")->fetchColumn();
    $withoutAlias     = $totalItems - $withAlias;
    $withoutMetatitle = Cot::$db->query("SELECT COUNT(*) FROM $db_pages WHERE (page_metatitle = '' OR page_metatitle IS NULL) AND page_title != ''")->fetchColumn();

    $t->assign([
        'STATS_TOTAL_ITEMS'        => $totalItems,
        'STATS_WITH_ALIAS'         => $withAlias,
        'STATS_WITHOUT_ALIAS'      => $withoutAlias,
        'STATS_WITHOUT_METATITLE'  => $withoutMetatitle,
    ]);

    $t->assign('AUTOALIAS_CREATE_URL', cot_url('admin', 'm=other&p=aliaspagepro&a=create'));
}

/* ===== LISTS & ALIASES ===== */
if ($tab === 'list') {
    $perPage = Cot::$cfg['plugin']['aliaspagepro']['perpage_list'] ?? 25;
    list($pg, $d, $durl) = cot_import_pagenav('d', $perPage);
    $total = Cot::$db->query("SELECT COUNT(*) FROM $db_pages WHERE page_title != ''")->fetchColumn();
    $items = Cot::$db->query(
        "SELECT page_id, page_title, page_alias, page_metatitle, page_cat
         FROM $db_pages WHERE page_title != ''
         ORDER BY page_id DESC LIMIT $d, $perPage"
    )->fetchAll();

    foreach ($items as $row) {
        $itemUrl = cot_page_url($row);
        $t->assign([
            'LIST_ROW_ID'         => $row['page_id'],
            'LIST_ROW_TITLE'      => htmlspecialchars($row['page_title']),
            'LIST_ROW_TITLE_URL'  => $itemUrl,
            'LIST_ROW_ALIAS'      => htmlspecialchars($row['page_alias']),
            'LIST_ROW_METATITLE'  => htmlspecialchars($row['page_metatitle']),
        ]);
        $t->parse('MAIN.LIST_ROW');
    }
    if (empty($items)) {
        $t->parse('MAIN.LIST_EMPTY');
    }

    $pagenav = cot_pagenav('admin', ['m'=>'other','p'=>'aliaspagepro','tab'=>'list'], $d, $total, $perPage, 'd');
    $t->assign(cot_generatePaginationTags($pagenav));
}

/* ===== MANAGEMENT (editing) ===== */
if ($tab === 'manage') {
    $perPage = Cot::$cfg['plugin']['aliaspagepro']['perpage_manage'] ?? 10;
    list($pg, $d, $durl) = cot_import_pagenav('d', $perPage);

    $backUrl = cot_url('admin', [
        'm'   => 'other',
        'p'   => 'aliaspagepro',
        'tab' => 'manage',
        'd'   => $durl
    ]);
    $backUrl = str_replace('&amp;', '&', $backUrl);

    if ($a === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ids        = $_POST['ids']        ?? [];
        $titles     = $_POST['title']      ?? [];
        $metatitles = $_POST['metatitle']  ?? [];
        $aliases    = $_POST['alias']      ?? [];

        foreach ($ids as $id) {
            $id        = (int)$id;
            $title     = trim($titles[$id]     ?? '');
            $metatitle = trim($metatitles[$id] ?? '');
            $alias     = trim($aliases[$id]    ?? '');

            if ($id > 0) {
                Cot::$db->update($db_pages, [
                    'page_title'     => $title,
                    'page_metatitle' => $metatitle,
                    'page_alias'     => $alias,
                ], "page_id = $id");
            }
        }
        cot_message(Cot::$L['aliaspagepro_update_success']);
        cot_redirect($backUrl);
    }

    $total = Cot::$db->query("SELECT COUNT(*) FROM $db_pages WHERE page_title != ''")->fetchColumn();
    $items = Cot::$db->query(
        "SELECT page_id, page_title, page_alias, page_metatitle
         FROM $db_pages WHERE page_title != ''
         ORDER BY page_id DESC LIMIT $d, $perPage"
    )->fetchAll();

    foreach ($items as $row) {
        $t->assign([
            'MANAGE_ID'         => $row['page_id'],
            'MANAGE_TITLE'      => htmlspecialchars($row['page_title']),
            'MANAGE_ALIAS'      => htmlspecialchars($row['page_alias']),
            'MANAGE_METATITLE'  => htmlspecialchars($row['page_metatitle']),
        ]);
        $t->parse('MAIN.MANAGE_ROW');
    }
    if (empty($items)) {
        $t->parse('MAIN.MANAGE_EMPTY');
    }

    $pagenav = cot_pagenav('admin', ['m'=>'other','p'=>'aliaspagepro','tab'=>'manage'], $d, $total, $perPage, 'd');
    $t->assign(cot_generatePaginationTags($pagenav));
    $t->assign('MANAGE_FORM_URL', cot_url('admin', ['m'=>'other','p'=>'aliaspagepro','tab'=>'manage','a'=>'update','d'=>$durl]));
}

cot_display_messages($t);
$t->parse('MAIN');
$pluginBody = $t->text('MAIN');