<?php

/*
 * CleanDeck for CMD-Auth (https://link133.com) and other similar applications
 *
 * Copyright (c) 2023-2024 Iotu Nicolae, nicolae.g.iotu@link133.com
 * Licensed under the terms of the MIT License (MIT)
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Framework\Libraries\SEO;

final class SEOUtils
{
    // Add here words which should not be used as keywords.
    // These are specific for your application
    public const APP_STOP_WORDS = [
        'adjusted', 'admin100k', 'administrative',
        'agree', 'and/or', 'applied',
        'assign', 'author', 'bottom',
        'business', 'case-insensitive', 'change',
        'change', 'changing', 'characters',
        'cleandeck.environment',
        'conditions', 'confirmation',
        'connected', 'data', 'delivering',
        'details', 'document', 'done',
        'employee', 'env', 'extra',
        'file', 'first', 'google',
        'httponly', 'information', 'instance',
        'integer', 'keywords', 'last',
        'loading', 'local', 'machine',
        'main', 'name', 'names',
        'newsletter', 'non-business', 'one',
        'password', 'policy', 'price',
        'privacy', 'promotions', 'remember',
        'reset', 'responses', 'right',
        'run', 'sign', 'simulate',
        'special', 'specified', 'started',
        'subscribe', 'successful', 'terms',
        'test', 'type', 'uses',
        'values', 'variable', 'window',
    ];

    /**
     * @param string $main_tag Searching for keywords will be conducted inside this tag only. Default 'main'.
     * @param string $keywords_meta_location ,
     */
    public static function build(
        string $response_body,
        int    $max_keywords = 10,
        string $main_tag = 'main',
        string $keywords_meta_location = '##SEO_KEYWORDS##',
        string $show_keywords_location = '##DEVELOPMENT_PRINT_SEO_KEYWORDS##',
    ): string {
        $keywords = self::getKeywords(
            \preg_replace('/<!--START-SEO-IGNORE-->.*?<!--END-SEO-IGNORE-->/is', '', $response_body),
            $max_keywords,
            $main_tag
        );
        if ($keywords === []) {
            return $response_body;
        }

        // add meta keywords
        $response_body = \str_replace($keywords_meta_location, \implode(',', $keywords), $response_body);
        // show SEO keywords during development
        return \str_replace($show_keywords_location, \implode(', ', $keywords), $response_body);
    }

    /**
     * @param string $main_tag Searching for keywords will be conducted inside this tag only. Default 'main'.
     * @return string[]
     */
    public static function getKeywords(
        string $response_body,
        int    $max_keywords = 10,
        string $main_tag = 'main',
        string $keywords_meta_location = '##SEO_KEYWORDS##',
        string $show_keywords_location = '##DEVELOPMENT_PRINT_SEO_KEYWORDS##',
    ): array {
        $core_stop_words = [
            \strtolower($keywords_meta_location),
            \strtolower($show_keywords_location),
            '&gt;', '&lt;', 'a', 'about',
            'above', 'act', 'actions', 'after',
            'all', 'an', 'and', 'are',
            'as', 'at', 'available', 'be',
            'before', 'below', 'builds', 'by',
            'can', 'cannot', 'changed',
            'cleandeck.oauth2_google.local_development',
            'cleandeck.oauth2_google.local_development_account',
            'com', 'consideration', 'contents',
            'custom', 'de', 'default', 'development',
            'do', "don't", 'en', 'enter',
            'etc', 'external', 'false', 'follow',
            'following', 'for', 'from', 'general',
            'has', 'having', 'high', 'higher',
            'highest', 'how', 'http', 'https',
            'i', 'in', 'internal', 'iotu',
            'is', 'it', 'la', 'left', 'length',
            'local-development@local-development.ldcom',
            'log', 'low', 'lowest', 'make',
            'max', 'may', 'me', 'min', 'minimum',
            'mode', 'modifying', 'must', 'my',
            'nicolae', 'no', 'non', 'not',
            'of', 'on', 'only', 'options',
            'or', 'order', 'other', 'our',
            'per', 'perform', 'public', 'range',
            'reason', 'regarding', 'require', 'required',
            'requirements', 'requires', 'restart', 'running',
            'seo', 'set', 'should', 'shown',
            'shows', 'standard', 'string', 'super',
            'supplied', 'supply', 'that', 'the',
            'the', 'them', 'they', 'this',
            'through', 'to', 'top', 'true',
            'und', 'url', 'usable', 'use',
            'used', 'using', 'various', 'view',
            'warning', 'was', 'what', 'when',
            'where', 'which', 'who', 'whom',
            'will', 'with', 'would', 'www',
            'yes', 'you', 'your',
        ];
        // Make this list specific for your application. Adjust self::APP_STOP_WORDS.
        $app_stop_words = self::APP_STOP_WORDS;


        $mc_regexp = '/<' . $main_tag . '[^>]*>(.*)<\/' . $main_tag . '>/is';

        $pm_result = \preg_match($mc_regexp, $response_body, $main_matches);
        if ($pm_result === false || \count($main_matches) < 2) {
            return [];
        }

        $main = \trim(
            (string) \preg_replace(['/\s\s+/is', '/\n/s'], ' ', \strtolower($main_matches[1]))
        );
        // the contents of some tags must be deleted entirely
        $main_clean_tags = \preg_replace('/<(code|script|style).*>.*<\/\1>/smi', ' ', $main);
        // keep only the wording
        if (\is_string($main_clean_tags)) {
            $main = \strip_tags($main_clean_tags);
        } else {
            $main = \strip_tags($main);
        }

        // final cleanup
        $raw_words_array = \array_map(static function ($word): string {
            return \trim($word, '.,\'"[](){} /:');
        }, \explode(' ', $main));

        $match_words = \array_filter(
            $raw_words_array,
            static function ($item) use ($core_stop_words, $app_stop_words): bool {
                return (
                    \is_string($item) &&
                    \strlen($item) > 2 &&
                    !\in_array($item, $core_stop_words) &&
                    !\in_array($item, $app_stop_words) &&
                    \preg_match('/^[a-zA-Z0-9_-]+$/', $item) === 1 &&
                    \preg_match('/\d{2,}/', $item) !== 1
                );
            }
        );

        $word_count_arr = \array_count_values($match_words);
        \arsort($word_count_arr);

        return \array_keys(\array_slice($word_count_arr, 0, $max_keywords));
    }
}
