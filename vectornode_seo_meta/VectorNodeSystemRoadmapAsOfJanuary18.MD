# VectorNode System Roadmap - January 18, 2025

## Executive Summary

VectorNode is a custom SEO meta tag management system built for the Ruplin WordPress plugin, designed to replace RankMath functionality with a lightweight, database-driven solution. The system is currently **disabled by default** via settings toggle while other SEO plugins are used temporarily.

## Project Goals

### Primary Objectives
- Replace RankMath SEO plugin with custom solution
- Store all SEO meta data in custom `wp_pylons` database table
- Provide granular control over meta tags per post/page
- Support template variables for dynamic content
- Integrate seamlessly with existing Ruplin infrastructure

### Current Status
- ✅ Core system architecture complete
- ✅ Database schema implemented
- ✅ Admin interface integrated (Dioptra page)
- ✅ Frontend output system functional
- ✅ Settings page with enable/disable toggle
- ⚠️ Template variable replacement partially implemented
- ⏸️ System disabled pending further development

## Database Schema

All VectorNode data is stored in the `wp_pylons` table with the following columns:

### Meta Tag Fields
- `vectornode_meta_title` - Custom SEO title
- `vectornode_meta_description` - Meta description
- `vectornode_robots` - Robots directives (index/noindex, follow/nofollow, etc.)
- `vectornode_canonical_url` - Canonical URL override
- `vectornode_focus_keywords` - Target keywords for content optimization
- `vectornode_breadcrumb_title` - Custom breadcrumb text

### Open Graph Fields
- `vectornode_og_title` - Open Graph title
- `vectornode_og_description` - Open Graph description  
- `vectornode_og_image_id` - Featured image ID for social sharing

### Twitter Card Fields
- `vectornode_twitter_title` - Twitter Card title
- `vectornode_twitter_description` - Twitter Card description

### Schema Fields
- `vectornode_schema_type` - Schema.org markup type

### Removed Fields (deprecated)
- ~~`vectornode_enabled`~~ - Removed in favor of global setting
- ~~`vectornode_override_rankmath`~~ - No longer needed
- ~~`vectornode_disable_rankmath_title`~~ - Obsolete

## System Architecture

### Core Components

#### 1. VectorNode Core (`class-vectornode-core.php`)
- Main initialization class
- Manages dependency loading
- Controls frontend/backend initialization
- Checks global enable/disable setting
- Database query methods

#### 2. VectorNode Frontend (`class-vectornode-frontend.php`)
- Handles all frontend output
- Hooks into `wp_head` at priority 1
- Removes WordPress default title rendering
- Outputs all meta tags
- Implements title filters for compatibility

#### 3. VectorNode Generator (`class-vectornode-generator.php`)
- Determines page type (singular, archive, search, etc.)
- Delegates to appropriate resolver
- Returns complete meta data array

#### 4. Resolvers (in `/resolvers/` directory)
- `class-resolver-singular.php` - Posts/Pages
- `class-resolver-archive.php` - Category/Tag/Archive pages
- `class-resolver-author.php` - Author archives
- `class-resolver-search.php` - Search results
- `class-resolver-404.php` - 404 error pages

#### 5. Template Variables (`class-vectornode-template-vars.php`)
- Replaces dynamic variables like `%title%`, `%sitename%`, `%sep%`
- Supports post-specific variables
- Handles date/time replacements

#### 6. Settings (`class-vectornode-settings.php`)
- Manages VectorNode configuration
- Integrates with WordPress settings API

## Admin Interface

### Dioptra Integration (`/dioptra/dioptra_screen.php`)
- Custom meta box on post edit screen
- All VectorNode fields displayed in organized rows
- Checkbox-based enable/disable per field
- Visual alignment with existing Dioptra UI

### Settings Page (`ruplin_settings_mar.php`)
- Located at: WP Admin → Ruplin Hub → Ruplin Settings
- Single checkbox: "Enable VectorNode System"
- Default: Unchecked (system disabled)
- Controls global VectorNode activation

## Template Variables System

### Supported Variables (Planned)
- `%title%` - Post/Page title
- `%sitename%` - Site name
- `%sitedesc%` - Site description  
- `%sep%` - Separator character
- `%page%` - Page number
- `%date%` - Post date
- `%modified%` - Modified date
- `%author%` - Author name
- `%category%` - Primary category
- `%tag%` - Primary tag
- `%term%` - Current term name
- `%excerpt%` - Post excerpt
- `%currentyear%` - Current year
- `%currentmonth%` - Current month
- `%currentday%` - Current day

## Hook System

### WordPress Hooks Used
- `wp_head` - Main output hook (priority 1)
- `wp` - Frontend initialization
- `admin_menu` - Settings page registration
- `admin_init` - Settings registration
- `wp_title` - Title filter for older themes
- `pre_get_document_title` - Title filter for modern themes

### Custom Hooks
- `vectornode/head` - Custom action for extensibility
- Follows RankMath architectural pattern

## Implementation Notes

### Key Decisions Made
1. **Global Function Approach**: Used `vectornode_output_meta_tags()` global function to avoid WordPress hook timing issues
2. **RankMath Architecture**: Adopted RankMath's approach of removing default WP title and using custom action hooks
3. **Settings-Based Toggle**: Simple checkbox in settings rather than per-post enable/disable
4. **Database-Driven**: All data stored in custom table rather than post meta for performance

### Known Issues
1. Template variable replacement not fully implemented
2. Schema.org markup generation incomplete
3. Breadcrumb integration pending
4. Multi-site compatibility untested

## Testing Checklist

When re-enabling development:

- [ ] Enable VectorNode in settings
- [ ] Verify custom title appears in `<title>` tag
- [ ] Check meta description output
- [ ] Confirm Open Graph tags present
- [ ] Test Twitter Card tags
- [ ] Verify robots meta directives
- [ ] Check canonical URL output
- [ ] Test on different page types (post, page, archive, search)
- [ ] Verify no duplicate title tags
- [ ] Check for JavaScript conflicts
- [ ] Test with caching plugins

## Future Development

### Phase 1: Core Completion
- Complete template variable system
- Add all variable replacements
- Test on all page types
- Fix any edge cases

### Phase 2: Schema Integration  
- Implement Schema.org JSON-LD output
- Support multiple schema types
- Add schema validation

### Phase 3: Advanced Features
- Sitemap integration
- Redirect management
- Social media preview
- SEO analysis tools
- Bulk editing interface

### Phase 4: Performance & Polish
- Query optimization
- Caching layer
- Migration tools from other SEO plugins
- Documentation
- Unit tests

## Migration Path

When ready to activate:

1. Disable current SEO plugin (RankMath/Yoast/etc.)
2. Enable VectorNode in Ruplin Settings
3. Verify meta output on key pages
4. Run migration script (to be developed) to import existing SEO data
5. Monitor for issues
6. Gradually phase out legacy SEO plugin

## Code Locations

- **Main Plugin**: `/wp-content/plugins/ruplin/ruplin.php`
- **VectorNode Core**: `/wp-content/plugins/ruplin/vectornode_seo_meta/`
- **Admin UI**: `/wp-content/plugins/ruplin/dioptra/dioptra_screen.php`
- **Settings**: `/wp-content/plugins/ruplin/ruplin_settings_mar.php`
- **Save Logic**: `/wp-content/plugins/ruplin/includes/class-admin.php`

## Database Queries

### Get VectorNode data for a post:
```sql
SELECT vectornode_meta_title, vectornode_meta_description, ...
FROM wp_pylons 
WHERE rel_wp_post_id = %d
```

### Check if enabled:
```php
$options = get_option('ruplin_settings');
$enabled = isset($options['enable_vectornode']) && $options['enable_vectornode'] == 1;
```

## Notes for Resuming Development

1. **System is functional** - Core architecture works, just needs polish
2. **Database ready** - All columns exist and save properly
3. **UI complete** - Admin interface fully integrated
4. **Output working** - When enabled, outputs all meta tags correctly
5. **Main task** - Complete template variable replacements
6. **Testing needed** - Comprehensive testing on production-like environment

## Contact & Resources

- Plugin Location: `/wp-content/plugins/ruplin/`
- Test URL: `http://saltwater.local/residential-pest-control/`
- Test Post ID: 2215 (has "aaa" as title, "bbb" as description for testing)

---

*Last Updated: January 18, 2025*
*Status: Disabled pending further development*
*Next Review: February 2025*