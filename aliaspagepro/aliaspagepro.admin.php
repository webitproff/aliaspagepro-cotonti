<?php
declare(strict_types=1);
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
 * Date: 2026-11-18
 * @package aliaspagepro
 * @version 3.0.0
 * @author webitproff
 * @copyright Copyright (c) webitproff 2026 | https://github.com/webitproff/aliaspagepro-cotonti
 * @license BSD
 */




defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('aliaspagepro', 'plug');
require_once cot_langfile('aliaspagepro', 'plug');

$tab = cot_import('tab', 'G', 'ALP') ?: 'stats';
$a   = cot_import('a',   'G', 'ALP');

$t = new XTemplate(cot_tplfile('aliaspagepro.admin', 'plug', true));
$adminTitle = Cot::$L['aliaspagepro_name'];

$t->assign([
    'TAB_STATS_ACTIVE'  => $tab === 'stats'  ? 'active' : '',
    'TAB_LIST_ACTIVE'   => $tab === 'list'   ? 'active' : '',
    'TAB_MANAGE_ACTIVE' => $tab === 'manage' ? 'active' : '',
    'URL_STATS'         => cot_url('admin', ['m'=>'other','p'=>'aliaspagepro','tab'=>'stats']),
    'URL_LIST'          => cot_url('admin', ['m'=>'other','p'=>'aliaspagepro','tab'=>'list']),
    'URL_MANAGE'        => cot_url('admin', ['m'=>'other','p'=>'aliaspagepro','tab'=>'manage']),
]);

/* ===== Bulk actions ===== */
if ($a === 'create' || $a === 'clear_aliases') {
    $backTab = cot_import('tab', 'G', 'ALP') ?: 'stats';
    $backUrl = str_replace('&amp;', '&', cot_url('admin', [
        'm' => 'other', 'p' => 'aliaspagepro', 'tab' => $backTab,
    ]));

    if ($a === 'create') {
        $count = 0;
        $res = Cot::$db->query(
            'SELECT page_id, page_title FROM ' . Cot::$db->pages . " WHERE page_alias = ''"
        );
        while ($row = $res->fetch()) {
            aliaspagepro_update((string) $row['page_title'], (int) $row['page_id']);
            $count++;
        }
        cot_message(cot_rc('aliases_written', ['count' => $count]));
    }

    if ($a === 'clear_aliases') {
        Cot::$db->update(Cot::$db->pages, ['page_alias' => ''], '1');
        cot_message(Cot::$L['aliaspagepro_aliases_cleared']);
    }

    cot_redirect($backUrl);
}

/* ===== STATISTICS ===== */
if ($tab === 'stats') {
    $t->assign([
        'STATS_TOTAL_ITEMS' => (int) Cot::$db->query(
            'SELECT COUNT(*) FROM ' . Cot::$db->pages . " WHERE page_title <> ''"
        )->fetchColumn(),
        'STATS_WITH_ALIAS' => (int) Cot::$db->query(
            'SELECT COUNT(*) FROM ' . Cot::$db->pages . " WHERE page_title <> '' AND page_alias <> ''"
        )->fetchColumn(),
    ]);

    $t->assign([
        'STATS_WITHOUT_ALIAS' => (int) Cot::$db->query(
            'SELECT COUNT(*) FROM ' . Cot::$db->pages . " WHERE page_title <> '' AND page_alias = ''"
        )->fetchColumn(),
        'STATS_WITHOUT_METATITLE' => (int) Cot::$db->query(
            'SELECT COUNT(*) FROM ' . Cot::$db->pages
            . " WHERE page_title <> '' AND (page_metatitle = '' OR page_metatitle IS NULL)"
        )->fetchColumn(),
    ]);

    $t->assign('AUTOALIAS_CREATE_URL', cot_url('admin', 'm=other&p=aliaspagepro&a=create'));
}

/* ===== LISTS & ALIASES ===== */
if ($tab === 'list') {
    $perPage = max(5, (int) (Cot::$cfg['plugin']['aliaspagepro']['perpage_list'] ?? 25));
    [$pg, $d, $durl] = cot_import_pagenav('d', $perPage);

    $total = (int) Cot::$db->query(
        'SELECT COUNT(*) FROM ' . Cot::$db->pages . " WHERE page_title <> ''"
    )->fetchColumn();

    $items = Cot::$db->query(
        'SELECT page_id, page_title, page_alias, page_metatitle, page_cat FROM ' . Cot::$db->pages
        . " WHERE page_title <> '' ORDER BY page_id DESC LIMIT $d, $perPage"
    )->fetchAll();

    foreach ($items as $row) {
        $t->assign([
            'LIST_ROW_ID'        => (int) $row['page_id'],
            'LIST_ROW_TITLE'     => htmlspecialchars((string) $row['page_title']),
            'LIST_ROW_TITLE_URL' => cot_page_url($row),
            'LIST_ROW_ALIAS'     => htmlspecialchars((string) $row['page_alias']),
            'LIST_ROW_METATITLE' => htmlspecialchars((string) $row['page_metatitle']),
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
    $perPage = max(5, (int) (Cot::$cfg['plugin']['aliaspagepro']['perpage_manage'] ?? 10));
    [$pg, $d, $durl] = cot_import_pagenav('d', $perPage);

    $backUrl = str_replace('&amp;', '&', cot_url('admin', [
        'm' => 'other', 'p' => 'aliaspagepro', 'tab' => 'manage', 'd' => $durl,
    ]));

    if ($a === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ids        = (array) ($_POST['ids']       ?? []);
        $titles     = (array) ($_POST['title']     ?? []);
        $metatitles = (array) ($_POST['metatitle'] ?? []);
        $aliases    = (array) ($_POST['alias']     ?? []);

        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id <= 0) {
                continue;
            }

            $title     = trim((string) ($titles[$id]     ?? ''));
            $metatitle = trim((string) ($metatitles[$id] ?? ''));
            $alias     = trim((string) ($aliases[$id]    ?? ''));

            // Sanitize alias: keep only allowed characters
            $alias = preg_replace('/[^a-z0-9_\-\.]/i', '', $alias) ?? '';

            Cot::$db->update(Cot::$db->pages, [
                'page_title'     => $title,
                'page_metatitle' => $metatitle,
                'page_alias'     => $alias,
            ], 'page_id = ?', [$id]);
        }

        cot_message(Cot::$L['aliaspagepro_update_success']);
        cot_redirect($backUrl);
    }

    $total = (int) Cot::$db->query(
        'SELECT COUNT(*) FROM ' . Cot::$db->pages . " WHERE page_title <> ''"
    )->fetchColumn();

    $items = Cot::$db->query(
        'SELECT page_id, page_title, page_alias, page_metatitle, page_cat FROM ' . Cot::$db->pages
        . " WHERE page_title <> '' ORDER BY page_id DESC LIMIT $d, $perPage"
    )->fetchAll();

    foreach ($items as $row) {
        $t->assign([
            'MANAGE_ID'         => (int) $row['page_id'],
            'MANAGE_TITLE'      => htmlspecialchars((string) $row['page_title']),
            'MANAGE_TITLE_URL'  => cot_page_url($row),
            'MANAGE_ALIAS'      => htmlspecialchars((string) $row['page_alias']),
            'MANAGE_METATITLE'  => htmlspecialchars((string) $row['page_metatitle']),
        ]);
        $t->parse('MAIN.MANAGE_ROW');
    }
    if (empty($items)) {
        $t->parse('MAIN.MANAGE_EMPTY');
    }

    $pagenav = cot_pagenav('admin', ['m'=>'other','p'=>'aliaspagepro','tab'=>'manage'], $d, $total, $perPage, 'd');
    $t->assign(cot_generatePaginationTags($pagenav));
    $t->assign('MANAGE_FORM_URL', cot_url('admin', [
        'm'=>'other','p'=>'aliaspagepro','tab'=>'manage','a'=>'update','d'=>$durl,
    ]));
}

cot_display_messages($t);
$t->parse('MAIN');
$pluginBody = $t->text('MAIN');
