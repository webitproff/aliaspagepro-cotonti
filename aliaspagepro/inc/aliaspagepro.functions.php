<?php
/**
 * Alias Page PRO functions
 * File: plugins/aliaspagepro/inc/aliaspagepro.functions.php
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

require_once cot_incfile('page', 'module');

/**
 * Single function for transliteration of Cyrillic (Russian + Ukrainian) to Latin
 * Covers all letters of both alphabets.
 *
 * @param string $str Source string
 * @return string
 */
function aliaspagepro_transliterate($str)
{
    // Universal table: longest sequences first
    $translit = [
        // Ukrainian special characters (unique)
        'Є' => 'Ye',   'є' => 'ye',
        'Ї' => 'Yi',   'ї' => 'yi',
        'Ґ' => 'G',    'ґ' => 'g',
        'І' => 'I',    'і' => 'i',
        // Russian letters missing in Ukrainian
        'ё' => 'yo',   'Ё' => 'Yo',
        'ы' => 'y',    'Ы' => 'Y',
        'э' => 'e',    'Э' => 'E',
        'ъ' => '',     'Ъ' => '',
        // Common multi-character combinations (ru/uk)
        'Щ' => 'Shch', 'щ' => 'shch',
        'Ж' => 'Zh',   'ж' => 'zh',
        'Х' => 'Kh',   'х' => 'kh',
        'Ц' => 'Ts',   'ц' => 'ts',
        'Ч' => 'Ch',   'ч' => 'ch',
        'Ш' => 'Sh',   'ш' => 'sh',
        'Ю' => 'Yu',   'ю' => 'yu',
        'Я' => 'Ya',   'я' => 'ya',
        // Single letters (common to both alphabets)
        'А' => 'A', 'а' => 'a',
        'Б' => 'B', 'б' => 'b',
        'В' => 'V', 'в' => 'v',
        'Г' => 'G', 'г' => 'g',
        'Д' => 'D', 'д' => 'd',
        'Е' => 'E', 'е' => 'e',
        'З' => 'Z', 'з' => 'z',
        'И' => 'I', 'и' => 'i',
        'Й' => 'Y', 'й' => 'y',
        'К' => 'K', 'к' => 'k',
        'Л' => 'L', 'л' => 'l',
        'М' => 'M', 'м' => 'm',
        'Н' => 'N', 'н' => 'n',
        'О' => 'O', 'о' => 'o',
        'П' => 'P', 'п' => 'p',
        'Р' => 'R', 'р' => 'r',
        'С' => 'S', 'с' => 's',
        'Т' => 'T', 'т' => 't',
        'У' => 'U', 'у' => 'u',
        'Ф' => 'F', 'ф' => 'f',
        // Soft sign and apostrophes disappear
        'ь' => '', 'Ь' => '',
        "'" => '', '`' => '', '’' => '',
    ];

    return strtr($str, $translit);
}

/**
 * Converts a title into an alias
 *
 * @param string $title Title
 * @param int $id Page ID
 * @param bool $duplicate TRUE if duplicate alias was previously detected
 * @return string
 */
function aliaspagepro_convert($title, $id = 0, $duplicate = false)
{
    global $cfg;

    // Transliteration (always with internal function)
    if ($cfg['plugin']['aliaspagepro']['translit']) {
        $title = aliaspagepro_transliterate($title);
    }

    // Remove everything except letters, digits, hyphen, underscore and space
    $title = preg_replace('#[^\p{L}0-9\-_ ]#u', '', $title);
    $title = str_replace(' ', $cfg['plugin']['aliaspagepro']['sep'], $title);

    if ($cfg['plugin']['aliaspagepro']['lowercase']) {
        $title = mb_strtolower($title);
    }

    if ($cfg['plugin']['aliaspagepro']['prepend_id'] && !empty($id)) {
        $title = $id . $cfg['plugin']['aliaspagepro']['sep'] . $title;
    } elseif ($duplicate) {
        switch ($cfg['plugin']['aliaspagepro']['on_duplicate']) {
            case 'ID':
                if (!empty($id)) {
                    $title .= $cfg['plugin']['aliaspagepro']['sep'] . $id;
                    break;
                }
            default:
                $title .= $cfg['plugin']['aliaspagepro']['sep'] . rand(2, 99);
                break;
        }
    }

    return $title;
}

/**
 * Updates an alias for a specific page item
 *
 * @param string $title Page title
 * @param int $id Page ID
 */
function aliaspagepro_update($title, $id)
{
    global $cfg, $db, $db_pages;
    $duplicate = false;
    do {
        $alias = aliaspagepro_convert($title, $id, $duplicate);
        if (!$cfg['plugin']['aliaspagepro']['prepend_id']
            && $db->query("SELECT COUNT(*) FROM $db_pages
                WHERE page_alias = '$alias' AND page_id != $id")->fetchColumn() > 0) {
            $duplicate = true;
        } else {
            $db->update($db_pages, ['page_alias' => $alias], "page_id = $id");
            $duplicate = false;
        }
    } while ($duplicate && !$cfg['plugin']['aliaspagepro']['prepend_id']);
    return $alias;
}