# README — Ferret Header Injection Deprecation

**Date:** 2026-04-10
**Status:** Frontend output hooks DISABLED — class retained for utility methods and cache clearing

---

## What Was Done

The three `wp_head` / `wp_footer` output hooks in `Ruplin_Ferret_Header_Injection::init_hooks()`
were commented out on 2026-04-10:

```php
// add_action('wp_head', array($this, 'inject_header_code'), 1);
// add_action('wp_head', array($this, 'inject_header_code_2'), 2);
// add_action('wp_footer', array($this, 'inject_footer_code'), 99);
```

`wp_enqueue_scripts` was left active because it handles `ferret_inline_css` and
`ferret_inline_js` columns which the replacement class does NOT cover.

---

## Why

Two separate PHP systems were both outputting `ferret_header_code` into `<head>` on every
singular page:

1. This class (`Ruplin_Ferret_Header_Injection`) — priority 1
2. `Ferret_Snippets_Frontend` — priority 999

Both queried `wp_zen_orbitposts` for the same post and echoed the same content.
Result: every `<script>` and `<link>` tag in `ferret_header_code` loaded **twice**.

The practical symptom: accordion JS on peanut FAQ pages was broken because
`initAccordion()` ran twice — each trigger got two click listeners that cancelled each other.

---

## Why This Class Is Flawed (compared to Ferret_Snippets_Frontend)

1. **Dangerous `LIMIT 1` fallback** — if no orbitposts row exists for the current post,
   this class falls back to grabbing the first row in the table (any post's ferret code).
   This can accidentally inject another page's CSS/JS. `Ferret_Snippets_Frontend` returns
   empty instead — correct behavior.

2. **`get_queried_object_id()` vs `$post->ID`** — this class uses `get_queried_object_id()`
   which can return unexpected values on archives, taxonomy pages, etc.
   `Ferret_Snippets_Frontend` guards with `is_singular()` first, then uses `$post->ID`.

3. **Fires on non-singular pages** — this class ran on archives, search, 404, etc.
   Per-post ferret codes only make sense on singular pages. `Ferret_Snippets_Frontend`
   correctly restricts to `is_singular()` only.

4. **Deep fallback chain** — falls back to `Ferret_Snippets::get_instance()` then
   `wp_options`. Creates unpredictable behavior. `Ferret_Snippets_Frontend` has no
   fallbacks — either the data is there or nothing outputs.

5. **Priority 1** — firing at priority 1 in `wp_head` is extremely early and before
   most WP infrastructure is ready. Priority 999 (used by the new class) is safer.

---

## What This Class Still Does (actively used)

- **`enqueue_ferret_assets()`** — still hooked to `wp_enqueue_scripts`. Handles
  `ferret_inline_css` and `ferret_inline_js` columns via `wp_add_inline_style` /
  `wp_add_inline_script`. The new class does not handle these.

- **`clear_cache()`** — called by `class-shared-header-loader.php` when a "ferret"
  cache clear is triggered in the admin UI. Deletes transients.

- **`getInstance()`** — class is instantiated at boot by `class-shared-header-loader.php`.
  With hooks disabled this is mostly a no-op but the instantiation still happens.

---

## What This Class Has That Is Never Called

The following methods exist but are not called anywhere in the codebase:

- `save_ferret_codes()`
- `get_all_ferret_codes()`
- `inject_header_specific_code()`
- `get_header_specific_code()`
- `add_header_inline_style()`
- `add_header_inline_script()`
- `output_header_styles()`
- `output_header_scripts()`
- `inject_header_code()` (still exists, just no longer hooked)
- `inject_header_code_2()` (still exists, just no longer hooked)
- `inject_footer_code()` (still exists, just no longer hooked)

These are safe to delete if/when full removal is decided.

---

## Risks to Watch During Observation Period

- Pages that were relying on the `LIMIT 1` fallback (no orbitposts row) to accidentally
  load another post's ferret code — those will now get nothing. This is correct behavior
  but could surface pages that were silently broken before.
- Non-singular pages (archives, 404) where Class 1 was firing — Class 2 doesn't run there.
  Again correct, but worth watching.
- `ferret_inline_css` / `ferret_inline_js` columns — still handled by `enqueue_ferret_assets`,
  no change there.

---

## Intention

Full removal of this class is the goal. Before removing:

1. Confirm `ferret_inline_css` / `ferret_inline_js` handling is moved to
   `Ferret_Snippets_Frontend` or another dedicated class
2. Confirm `clear_cache()` logic is moved to `class-shared-header-loader.php` directly
3. Remove the `getInstance()` call from `class-shared-header-loader.php`
4. Delete this file and update `ruplin.php` / any loader that requires it

Do not remove until the observation period (watching for broken pages) is complete.

---

*Created: 2026-04-10 | ruplin/shared-header-logic/*
