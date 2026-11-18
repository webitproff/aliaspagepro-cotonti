<?php
declare(strict_types=1);

/**
 * Alias Page PRO functions — core logic.
 * File: plugins/aliaspagepro/inc/aliaspagepro.functions.php
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

require_once cot_incfile('page', 'module');

/**
 * Duplicate alias resolution strategies.
 */
enum AliasPagePro_DuplicateStrategy: string
{
    case Id     = 'ID';
    case Hash   = 'Hash';
    case Random = 'Random';
}

/**
 * Alias generation engine.
 *
 * Public API:
 *   AliasPagePro::transliterate(string): string
 *   AliasPagePro::convert(string, int): string          — deterministic, no uniqueness suffix
 *   AliasPagePro::assign(string, int): string           — ensures uniqueness against DB
 */
final class AliasPagePro
{
    private const DEFAULT_MAX_LENGTH = 36;
    private const DEFAULT_SEPARATOR  = '-';
    private const MAX_ATTEMPTS       = 10;
    private const RANDOM_MIN         = 2;
    private const RANDOM_MAX         = 9999;
    private const HASH_LENGTH        = 6;

    /** @var array<string, string> */
    private const TRANSLIT_MAP = [
        // Ukrainian unique
        'Є' => 'Ye', 'є' => 'ye',
        'Ї' => 'Yi', 'ї' => 'yi',
        'Ґ' => 'G',  'ґ' => 'g',
        'І' => 'I',  'і' => 'i',
        // Russian unique
        'ё' => 'yo', 'Ё' => 'Yo',
        'ы' => 'y',  'Ы' => 'Y',
        'э' => 'e',  'Э' => 'E',
        'ъ' => '',   'Ъ' => '',
        // Multi-char combos
        'Щ' => 'Shch', 'щ' => 'shch',
        'Ж' => 'Zh',   'ж' => 'zh',
        'Х' => 'Kh',   'х' => 'kh',
        'Ц' => 'Ts',   'ц' => 'ts',
        'Ч' => 'Ch',   'ч' => 'ch',
        'Ш' => 'Sh',   'ш' => 'sh',
        'Ю' => 'Yu',   'ю' => 'yu',
        'Я' => 'Ya',   'я' => 'ya',
        // Single letters
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
        // Silent
        'ь' => '', 'Ь' => '',
        "'" => '', '`' => '', '’' => '',
    ];

    private function __construct() {}

    /* ---------------------------------------------------------------
     * Public API
     * ------------------------------------------------------------- */

    public static function transliterate(string $str): string
    {
        return strtr($str, self::TRANSLIT_MAP);
    }

    /**
     * Deterministic alias: normalizes title, applies prefix, truncates to limit.
     * Does NOT check uniqueness — for that use assign().
     */
    public static function convert(string $title, int $id = 0): string
    {
        $c    = self::config();
        $body = self::normalizeTitle($title, $c);

        if ($body === '') {
            $body = 'page';
        }

        $prefix = ($c['prepend_id'] && $id > 0) ? $id . $c['sep'] : '';

        return self::assemble($prefix, $body, '', $c['max_length'], $c['sep']);
    }

    /**
     * Produces a unique alias for page $id and persists it to DB.
     * Falls back to a hashed value if a pathological collision occurs.
     */
    public static function assign(string $title, int $id): string
    {
        $c    = self::config();
        $body = self::normalizeTitle($title, $c);

        if ($body === '') {
            $body = 'page';
        }

        $prefix = ($c['prepend_id'] && $id > 0) ? $id . $c['sep'] : '';

        // Attempt 0 — no suffix
        $candidate = self::assemble($prefix, $body, '', $c['max_length'], $c['sep']);
        if (!self::aliasExists($candidate, $id)) {
            self::saveAlias($id, $candidate);
            return $candidate;
        }

        // Attempts 1..N — with suffix
        $maxAttempts = $c['on_duplicate'] === AliasPagePro_DuplicateStrategy::Id
            ? 1
            : self::MAX_ATTEMPTS;

        for ($i = 1; $i <= $maxAttempts; $i++) {
            $suffix    = self::makeSuffix($c['on_duplicate'], $id, $title, $i);
            $candidate = self::assemble(
                $prefix, $body, $suffix,
                $c['max_length'], $c['sep']
            );
            if (!self::aliasExists($candidate, $id)) {
                self::saveAlias($id, $candidate);
                return $candidate;
            }
        }

        // Deterministic final fallback — guaranteed unique per page_id
        $candidate = mb_substr(
            md5($id . '|' . $title),
            0,
            min(12, $c['max_length']),
            'UTF-8'
        );
        self::saveAlias($id, $candidate);
        return $candidate;
    }

    /* ---------------------------------------------------------------
     * Internals
     * ------------------------------------------------------------- */

    /**
     * @return array{
     *   translit: bool, lowercase: bool, sep: string,
     *   max_length: int, prepend_id: bool,
     *   on_duplicate: AliasPagePro_DuplicateStrategy
     * }
     */
    private static function config(): array
    {
        $raw = Cot::$cfg['plugin']['aliaspagepro'] ?? [];

        return [
            'translit'     => (bool) ($raw['translit'] ?? true),
            'lowercase'    => (bool) ($raw['lowercase'] ?? true),
            'sep'          => (string) ($raw['sep'] ?? self::DEFAULT_SEPARATOR),
            'max_length'   => max(8, (int) ($raw['max_length'] ?? self::DEFAULT_MAX_LENGTH)),
            'prepend_id'   => (bool) ($raw['prepend_id'] ?? false),
            'on_duplicate' => AliasPagePro_DuplicateStrategy::tryFrom(
                (string) ($raw['on_duplicate'] ?? 'ID')
            ) ?? AliasPagePro_DuplicateStrategy::Id,
        ];
    }

    /**
     * @param array{translit:bool,lowercase:bool,sep:string} $c
     */
    private static function normalizeTitle(string $title, array $c): string
    {
        $title = trim($title);
        if ($title === '') {
            return '';
        }

        if ($c['translit']) {
            $title = self::transliterate($title);
        }

        if ($c['lowercase']) {
            $title = mb_strtolower($title, 'UTF-8');
        }

        // Collapse whitespace
        $title = preg_replace('/\s+/u', ' ', $title) ?? '';
        $title = trim($title);
        if ($title === '') {
            return '';
        }

        // Keep only letters, numbers, whitespace, hyphen, underscore
        $title = preg_replace('/[^\p{L}\p{N}\s_\-]/u', '', $title) ?? '';
        $title = trim($title);
        if ($title === '') {
            return '';
        }

        // Spaces → separator
        $title = str_replace(' ', $c['sep'], $title);

        // Collapse repeated separators
        $sepPattern = preg_quote($c['sep'], '/');
        $title = preg_replace('/(' . $sepPattern . ')+/u', $c['sep'], $title) ?? $title;

        // Trim separators at boundaries
        return trim($title, $c['sep'] . '_-.');
    }

    private static function assemble(
        string $prefix,
        string $body,
        string $suffix,
        int $maxLen,
        string $sep
    ): string {
        $suffixPart = $suffix !== '' ? $sep . $suffix : '';
        $budget     = $maxLen
                    - mb_strlen($prefix, 'UTF-8')
                    - mb_strlen($suffixPart, 'UTF-8');

        if ($budget < 1) {
            return mb_substr($prefix . $suffixPart, 0, $maxLen, 'UTF-8');
        }

        $body = self::truncateBody($body, $budget, $sep);

        $result = $prefix . $body . $suffixPart;

        if ($result === '') {
            // Extremely pathological — empty title with no prefix/suffix
            $result = substr(md5(random_bytes(8)), 0, min(8, $maxLen));
        }

        return $result;
    }

    /**
     * Cut at last separator within budget; fall back to hard cut for a single word.
     */
    private static function truncateBody(string $body, int $budget, string $sep): string
    {
        if ($body === '' || $budget <= 0) {
            return '';
        }

        if (mb_strlen($body, 'UTF-8') <= $budget) {
            return $body;
        }

        $cut = mb_substr($body, 0, $budget, 'UTF-8');
        $pos = mb_strrpos($cut, $sep, 0, 'UTF-8');

        if ($pos !== false && $pos > 0) {
            $cut = mb_substr($cut, 0, $pos, 'UTF-8');
        }

        $cut = rtrim($cut, $sep . ' _-.');

        if ($cut === '') {
            // Single long word — hard cut
            $cut = rtrim(mb_substr($body, 0, $budget, 'UTF-8'), $sep . ' _-.');
        }

        return $cut;
    }

    private static function makeSuffix(
        AliasPagePro_DuplicateStrategy $strategy,
        int $id,
        string $title,
        int $attempt
    ): string {
        return match ($strategy) {
            AliasPagePro_DuplicateStrategy::Id     => (string) $id,
            AliasPagePro_DuplicateStrategy::Hash   => substr(md5($title . '|' . $attempt), 0, self::HASH_LENGTH),
            AliasPagePro_DuplicateStrategy::Random => (string) random_int(self::RANDOM_MIN, self::RANDOM_MAX),
        };
    }

    private static function aliasExists(string $alias, int $excludeId): bool
    {
        $stmt = Cot::$db->query(
            'SELECT COUNT(*) FROM ' . Cot::$db->pages . ' WHERE page_alias = ? AND page_id <> ?',
            [$alias, $excludeId]
        );

        return (int) $stmt->fetchColumn() > 0;
    }

    private static function saveAlias(int $id, string $alias): void
    {
        Cot::$db->update(
            Cot::$db->pages,
            ['page_alias' => $alias],
            'page_id = ?',
            [$id]
        );
    }
}

/* ---------------------------------------------------------------
 * Backward-compatible global functions
 * ------------------------------------------------------------- */

/**
 * Transliteration of Cyrillic (RU + UK) to Latin.
 *
 * @deprecated Use AliasPagePro::transliterate() directly. Kept for BC.
 */
function aliaspagepro_transliterate(string $str): string
{
    return AliasPagePro::transliterate($str);
}

/**
 * Deterministic alias without uniqueness check.
 *
 * @deprecated Use AliasPagePro::convert() directly. Kept for BC.
 */
function aliaspagepro_convert(string $title, int $id = 0, bool $duplicate = false): string
{
    return AliasPagePro::convert($title, $id);
}

/**
 * Assigns a unique alias for the page and saves it to DB.
 */
function aliaspagepro_update(string $title, int $id): string
{
    return AliasPagePro::assign($title, $id);
}
