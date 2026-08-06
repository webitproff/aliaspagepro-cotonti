<?php
/* ====================
[BEGIN_COT_EXT]
Code=aliaspagepro
Name=Alias Page PRO
Category=seo
Description=Creates page alias from title if a user leaves it empty
Version=2.5.1
Date=Aug 6Th, 2026
Author=webitproff
Copyright=(c) webitproff 2026
Notes=DO NOT INSTALL with autoalias2
Auth_guests=R
Lock_guests=W12345A
Auth_members=RW
Lock_members=12345
Requires_modules=page
[END_COT_EXT]

[BEGIN_COT_EXT_CONFIG]
translit=01:radio::1:Transliterate non-latinic characters if possible
prepend_id=02:radio::0:Prepend page ID to alias
on_duplicate=03:select:ID,Random:ID:Number appended on duplicate alias (if prepend ID is off)
sep=04:select:-,_,.:-:Word separator
lowercase=05:radio::1:Cast to lower case
[END_COT_EXT_CONFIG]
==================== */

defined('COT_CODE') or die('Wrong URL');

/**
 * aliaspagepro.setup.php - Register data in $db_core and $db_config.
 * Setup & Config File for the Plugin aliaspagepro for module page
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