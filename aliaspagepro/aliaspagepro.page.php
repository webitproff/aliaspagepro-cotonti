<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=page.add.add.done,page.edit.update.done
[END_COT_EXT]
==================== */

/**
 * Creates alias when adding or updating a page
 *
 * Filename: plugins/aliaspagepro/aliaspagepro.page.php
 *
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

if (empty($rpage['page_alias'])) {
    require_once cot_incfile('aliaspagepro', 'plug');
    $rpage['page_alias'] = aliaspagepro_update(
        (string) $rpage['page_title'],
        (int) $id
    );
}
