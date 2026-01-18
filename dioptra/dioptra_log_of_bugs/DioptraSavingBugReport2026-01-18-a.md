# Dioptra VectorNode Save Bug Report
**Date:** 2026-01-18  
**Issue Type:** Database Save Failure  
**Severity:** Critical  
**Status:** RESOLVED ✅

## Executive Summary
VectorNode SEO fields (meta title, meta description, override RankMath, enabled) were not saving to the database from the Dioptra admin interface. The root cause was that the save function attempted to update non-existent database columns, causing the entire UPDATE query to fail.

## The Problem

### Symptoms
1. VectorNode fields appeared to save successfully (UI showed "Saved!" message)
2. Database values remained unchanged despite successful AJAX response
3. Frontend continued showing old meta values
4. No visible errors in WordPress debug logs

### User Experience
- User fills out VectorNode fields in Dioptra admin
- Clicks save button
- Gets success message
- Data doesn't actually save to database
- Frontend shows old/empty values

## Investigation Process

### Phase 1: Frontend Debugging
- Verified fields were being collected correctly in JavaScript
- Confirmed AJAX request was sending correct data
- Found duplicate field submissions (each field sent twice with different values)

### Phase 2: AJAX Transmission
- Confirmed data reaching server via POST
- VectorNode fields present with correct values:
  ```
  field_vectornode_meta_title = aaaa
  field_vectornode_meta_description = bbbbb
  field_vectornode_override_rankmath = 1
  field_vectornode_enabled = 1
  ```

### Phase 3: Backend Processing
- Added debug logging to `handle_dioptra_save_data()` function
- Discovered fields were being processed correctly
- Found they were added to `$update_data` array properly

### Phase 4: Database Update
- Created custom debug file to log SQL queries
- **CRITICAL FINDING:** Database UPDATE returning `false`
- Error message: `Unknown column 'home_anchor_for_silkweaver_services' in 'field list'`

## Root Cause

The save function was attempting to update ALL fields received from the frontend, including fields that don't have corresponding database columns. When WordPress's `$wpdb->update()` encounters a non-existent column, it fails the ENTIRE update operation.

### The Failing SQL Query
```sql
UPDATE `wp_pylons` SET 
  `pylon_id` = 434, 
  `vectornode_meta_title` = 'aaaa',
  `vectornode_meta_description` = 'bbbbb',
  ... 
  `home_anchor_for_silkweaver_services` = '',  -- THIS COLUMN DOESN'T EXIST!
  `home_anchor_for_silkweaver_locations` = ''   -- THIS COLUMN DOESN'T EXIST!
WHERE `rel_wp_post_id` = 2215
```

## The Solution

Added the non-existent fields to the `$fields_to_skip` array in `class-admin.php`:

```php
$fields_to_skip = [
    'begin-now-vn-system-area',
    'vec_disable_vn_system_sitewide', 
    'vec_disable_vn_system_on_post',
    'vec_meta_title',
    'vec_meta_description',
    'meta-title-actual-output',
    'meta-description-actual-output',
    'home_anchor_for_silkweaver_services',  // ADDED
    'home_anchor_for_silkweaver_locations'   // ADDED
];
```

## Where Fields Are Defined

### 1. Database Table Schema
**File:** `/ruplin/ruplin.php` (lines 470-604)
```php
vectornode_meta_title TEXT DEFAULT NULL,
vectornode_meta_description TEXT DEFAULT NULL,
vectornode_override_rankmath BOOLEAN DEFAULT FALSE,
vectornode_enabled BOOLEAN DEFAULT TRUE
```

### 2. Frontend Form Fields
**File:** `/ruplin/dioptra/dioptra_screen.php`

#### Main Dioptra Tab Fields (lines 400-900)
```php
// Standard field generation loop
<?php foreach ($dioptra_field_rows as $row): ?>
    <?php
    $field_name = $row['field_name'];
    $value = isset($row_data[$field_name]) ? $row_data[$field_name] : '';
    ?>
    <input type="text" name="field_<?php echo esc_attr($field_name); ?>" 
           value="<?php echo esc_attr($value); ?>" />
<?php endforeach; ?>
```

#### VectorNode Tab Fields (lines 1976-2132)
```php
<textarea name="field_vectornode_meta_title" ...>
<textarea name="field_vectornode_meta_description" ...>
<input type="checkbox" name="field_vectornode_override_rankmath" ...>
<input type="checkbox" name="field_vectornode_enabled" ...>
```

#### Additional Tab Fields
- **FAQ fields** (lines 800-1200): `field_serena_faq_box_q1`, `field_serena_faq_box_a1`, etc.
- **Pricing fields** (lines 1300-1400): `field_liz_pricing_heading`, `field_liz_pricing_body`
- **Process fields** (lines 1500-1700): `field_kendall_our_process_step_1`, etc.

### 3. JavaScript Field Collection
**File:** `/ruplin/dioptra/dioptra_screen.php` (lines 2514-2515)
```javascript
const inputs = document.querySelectorAll('input[name^="field_"], textarea[name^="field_"], select[name^="field_"]');
```
This collects ALL fields that start with "field_" prefix.

### 4. Backend Processing
**File:** `/ruplin/includes/class-admin.php` (lines 11160-11230)

```php
foreach ($_POST as $key => $value) {
    if (strpos($key, 'field_') === 0) {
        $field_name = str_replace('field_', '', $key);
        
        // Check if field should be skipped
        if (in_array($field_name, $fields_to_skip)) {
            continue;
        }
        
        // Process field based on type
        if (in_array($field_name, ['vectornode_override_rankmath', 'vectornode_enabled'])) {
            $update_data[$field_name] = ($value === '1') ? 1 : 0;
        } else {
            $update_data[$field_name] = $value;
        }
    }
}
```

## Field Source Definitions

### Fields That Don't Exist in Database (Must Be Skipped)
1. **Legacy VectorNode fields**: `vec_meta_title`, `vec_meta_description` (old naming)
2. **Display-only fields**: `meta-title-actual-output`, `meta-description-actual-output`
3. **Silkweaver fields**: `home_anchor_for_silkweaver_services`, `home_anchor_for_silkweaver_locations`
4. **System fields**: `begin-now-vn-system-area`, `vec_disable_vn_system_sitewide`

### How Fields Are Added to Forms
New fields can be added in multiple ways:

1. **Direct HTML in tabs** (like VectorNode fields)
2. **Through `$dioptra_field_rows` array** (main tab)
3. **Dynamic generation** (FAQ fields, process steps)

## Lessons Learned

1. **Silent Failures Are Dangerous**: The system returned "success" even when the database update failed completely
2. **Database Schema Must Match Form Fields**: Any mismatch causes total update failure
3. **Debug Logging Is Essential**: WordPress debug logs weren't capturing the SQL errors - custom logging was needed
4. **Field Filtering Is Critical**: Must maintain `$fields_to_skip` array when adding new form fields without database columns
5. **Testing Must Verify Database**: UI success doesn't mean data is saved

## Prevention Recommendations

1. **Add Database Error Checking**: Check `$wpdb->last_error` after updates and report failures
2. **Maintain Field Registry**: Create central registry of all fields and their database status
3. **Add Schema Validation**: Before save, verify all fields exist in database
4. **Improve Error Reporting**: Surface database errors to admin users
5. **Add Unit Tests**: Test save functionality with mock data

## Fixed Files

1. `/ruplin/includes/class-admin.php` - Added missing fields to skip list
2. `/ruplin/dioptra/dioptra_screen.php` - Fixed duplicate field submission
3. Added debug logging (to be removed in production)

## Verification

After fix, confirmed:
- VectorNode fields save successfully
- Database UPDATE returns `1` (success)
- Frontend displays updated meta values
- No SQL errors in debug logs

---

**Resolution Time:** ~4 hours  
**Root Cause:** Missing database columns in UPDATE query  
**Fix:** Skip non-existent fields during save  
**Impact:** All VectorNode functionality restored