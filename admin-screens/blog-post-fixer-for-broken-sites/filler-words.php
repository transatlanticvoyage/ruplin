<?php
/**
 * Filler-word seed list for f8372 — Part 1, METHOD A (preferred).
 *
 * When a post slug contains any of these words, one is removed to make the
 * URL "slightly different" from the original. Returned as a flat array of
 * lowercase tokens; the caller flips it into a lookup set.
 *
 * Keep this list to genuine connective/filler words (articles, conjunctions,
 * prepositions, auxiliaries, common pronouns/adverbs) so that removing one
 * still leaves a sensible slug.
 *
 * @package Ruplin
 */

if (!defined('ABSPATH')) {
    exit;
}

return array(
    // articles
    'a', 'an', 'the',
    // coordinating / common conjunctions
    'and', 'or', 'nor', 'but', 'yet', 'so', 'for',
    'because', 'although', 'though', 'while', 'whereas', 'since',
    'unless', 'until', 'if', 'else', 'than', 'then',
    // prepositions
    'in', 'on', 'at', 'to', 'from', 'with', 'within', 'without',
    'by', 'of', 'off', 'as', 'into', 'onto', 'upon', 'out',
    'about', 'over', 'under', 'above', 'below', 'beneath', 'beside',
    'after', 'before', 'between', 'among', 'amongst', 'through',
    'throughout', 'during', 'along', 'across', 'around', 'near',
    'toward', 'towards', 'up', 'down', 'per', 'via', 'vs', 'versus',
    'against', 'amid', 'amidst', 'behind', 'beyond', 'inside',
    'outside', 'past', 'plus', 'regarding', 'concerning',
    // auxiliaries / linking verbs
    'is', 'are', 'was', 'were', 'be', 'been', 'being', 'am',
    'do', 'does', 'did', 'has', 'have', 'had',
    'will', 'would', 'shall', 'should', 'can', 'could',
    'may', 'might', 'must', 'ought',
    // determiners / quantifiers
    'this', 'that', 'these', 'those', 'all', 'any', 'some',
    'no', 'not', 'each', 'every', 'either', 'neither',
    'more', 'most', 'much', 'many', 'few', 'several', 'such',
    // common pronouns
    'it', 'its', 'they', 'them', 'their', 'theirs',
    'we', 'us', 'our', 'ours', 'you', 'your', 'yours',
    'he', 'she', 'his', 'her', 'hers', 'him',
    'i', 'me', 'my', 'mine', 'who', 'whom', 'whose',
    'which', 'what', 'whatever', 'whichever',
    // common adverbs / fillers
    'very', 'just', 'also', 'too', 'only', 'even', 'still',
    'here', 'there', 'when', 'where', 'why', 'how',
    'again', 'once', 'ever', 'never', 'always', 'often',
    'quite', 'rather', 'somewhat', 'really',
);
