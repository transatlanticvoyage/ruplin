# README — Ferret Snippets Frontend (Authoritative Output System)

**Date:** 2026-04-10
**Status:** Active — sole authoritative system for frontend ferret code injection

---

## What This Class Does

`Ferret_Snippets_Frontend` (`class-ferret-snippets-frontend.php`) is the current
authoritative system for injecting per-page ferret code into the frontend `<head>`
and footer on singular WordPress pages.

It replaced the output functionality of `Ruplin_Ferret_Header_Injection`
(`shared-header-logic/class-ferret-header-injection.php`) whose equivalent hooks
were disabled on 2026-04-10.

---

## DB Columns It Reads

All from `wp_zen_orbitposts`, matched by `rel_wp_post_id = $post->ID`:

| Column | Hook | Priority |
|---|---|---|
| `ferret_header_code` | `wp_head` | 999 |
| `ferret_header_code_2` | `wp_head` | 1000 |
| `ferret_footer_code` | `wp_footer` | 999 |

---

## What It Does NOT Handle

- **`ferret_inline_css`** — still handled by `Ruplin_Ferret_Header_Injection::enqueue_ferret_assets()`
  via `wp_enqueue_scripts`. If that class is ever fully removed, this must be migrated here first.
- **`ferret_inline_js`** — same as above.
- **Non-singular pages** — deliberately excluded via `is_singular()` guard. Per-post ferret
  codes only make sense on singular posts/pages.
- **Fallback behavior** — if no `wp_zen_orbitposts` row exists for the current post, this
  class returns empty. There is no LIMIT 1 fallback, no options fallback. This is intentional
  and correct.

---

## Why This Class Is Better Than Its Predecessor

1. **No dangerous fallbacks** — returns empty if no matching row. No risk of accidentally
   injecting another page's ferret code.
2. **`is_singular()` guard** — only fires on actual singular post/page contexts.
3. **`$post->ID`** — uses the most reliable post ID source available.
4. **Late priority (999/1000)** — fires after WordPress infrastructure is fully ready.
5. **Simple and focused** — does one thing only. No utility methods, no save logic, no
   cache management mixed in.

---

## Future Improvements Needed Before Class 1 Can Be Fully Removed

1. Add handling for `ferret_inline_css` and `ferret_inline_js` columns (currently owned
   by the old class's `enqueue_ferret_assets()` method)
2. Add `clear_cache()` equivalent if transient caching is ever added here
3. Confirm nothing in the codebase calls `Ruplin_Ferret_Header_Injection` methods directly
   before the old class file is deleted

---

## HTML Output Format

**Header code:**
```html
<!-- Ferret Snippets: Header Code -->
{ferret_header_code content}
<!-- End Ferret Snippets: Header Code -->
```

**Header code 2:**
```html
<!-- Ferret Snippets: Header Code 2 -->
{ferret_header_code_2 content}
<!-- End Ferret Snippets: Header Code 2 -->
```

**Footer code:**
```html
<!-- Ferret Snippets: Footer Code -->
{ferret_footer_code content}
<!-- End Ferret Snippets: Footer Code -->
```

---

## Known Issue — JS Double-Load Safety Net

All peanut page `scripts.js` files should include a one-time init guard at the top
of their IIFE as a safety net against any future accidental double-load:

```js
if (window.pnt{NUMBER}_initialized) return;
window.pnt{NUMBER}_initialized = true;
```

This was added to `114 - chimney-exp_com/faqpage/assets/js/scripts.js` on 2026-04-10.
Future peanut pages should include this guard by default.

---

*Created: 2026-04-10 | ruplin/includes/ferret-snippets/*
