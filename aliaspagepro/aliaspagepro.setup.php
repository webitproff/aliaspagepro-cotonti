<?php
/* ====================
[BEGIN_COT_EXT]
Code=aliaspagepro
Name=Alias Page PRO
Category=seo
Description=Generates SEO-friendly page aliases from titles with configurable length and duplicate resolution
Version=3.0.0
Date=2026-11-18
Author=webitproff
Copyright=(c) webitproff 2026
Notes=DO NOT INSTALL with autoalias2lance
Auth_guests=R
Lock_guests=W12345A
Auth_members=RW
Lock_members=12345
Requires_modules=page
[END_COT_EXT]

[BEGIN_COT_EXT_CONFIG]
translit=01:radio::1:Transliterate Cyrillic to Latin
lowercase=02:radio::1:Cast alias to lowercase
sep=03:select:-,_,.:-:Word separator
max_length=04:select:24,36,52,78:36:Maximum alias length (characters)
prepend_id=05:radio::0:Prepend page ID to alias
on_duplicate=06:select:ID,Hash,Random:ID:Unique suffix strategy for duplicate aliases
perpage_list=07:string::25:Items per page in "Lists & Aliases" tab
perpage_manage=08:string::10:Items per page in "Manage" tab
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
 * Date: 2026-11-18
 * @package aliaspagepro
 * @version 3.0.0
 * @author webitproff
 * @copyright Copyright (c) webitproff 2026 | https://github.com/webitproff/aliaspagepro-cotonti
 * @license BSD
 */
