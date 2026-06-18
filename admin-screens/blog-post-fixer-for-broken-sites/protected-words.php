<?php
/**
 * Protected-word lists for f8372 — Part 1, METHOD B (fallback only).
 *
 * METHOD B removes a RANDOM word from the slug, but it must never remove:
 *   - any token from the single zen_sitespren row's driggs_brand_name /
 *     driggs_city / driggs_state_code (fetched at runtime, not listed here), or
 *   - any token belonging to a US state name or a top English-speaking country.
 *
 * The caller tokenizes every entry below (e.g. "New York" -> "new", "york")
 * and merges them with the driggs tokens into one protected lookup set.
 *
 * @package Ruplin
 */

if (!defined('ABSPATH')) {
    exit;
}

return array(

    // All 50 US states ("top 50").
    'us_states' => array(
        'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California',
        'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia',
        'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa',
        'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland',
        'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri',
        'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey',
        'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio',
        'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina',
        'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont',
        'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming',
    ),

    // Top 5 English-speaking countries, with common name variants so the
    // tokenizer protects every form that might appear in a slug.
    'countries' => array(
        'United States', 'United States of America', 'America', 'USA',
        'United Kingdom', 'Great Britain', 'Britain', 'England', 'Scotland', 'Wales',
        'Canada',
        'Australia',
        'Ireland',
        // bonus near-misses (harmless to protect)
        'New Zealand',
    ),
);
