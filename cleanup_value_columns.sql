-- Cleanup SQL for removing unnecessary *_value columns from wp_pylons table
-- These columns were created by mistake and should not exist
-- Run this in phpMyAdmin or via WP-CLI

-- Get the correct table prefix (replace wp_ with your actual prefix if different)
SET @table_name = 'wp_pylons';

-- Drop all the mistakenly created *_value columns
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS chenblock_card1_title_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS chenblock_card1_desc_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS chenblock_card2_title_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS chenblock_card2_desc_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS chenblock_card3_title_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS chenblock_card3_desc_value;

ALTER TABLE wp_pylons DROP COLUMN IF EXISTS serena_faq_box_q1_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS serena_faq_box_a1_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS serena_faq_box_q2_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS serena_faq_box_a2_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS serena_faq_box_q3_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS serena_faq_box_a3_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS serena_faq_box_q4_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS serena_faq_box_a4_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS serena_faq_box_q5_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS serena_faq_box_a5_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS serena_faq_box_q6_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS serena_faq_box_a6_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS serena_faq_box_q7_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS serena_faq_box_a7_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS serena_faq_box_q8_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS serena_faq_box_a8_value;

ALTER TABLE wp_pylons DROP COLUMN IF EXISTS brook_video_heading_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS brook_video_subheading_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS brook_video_description_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS brook_video_1_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS brook_video_2_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS brook_video_3_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS brook_video_4_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS brook_video_outro_value;

ALTER TABLE wp_pylons DROP COLUMN IF EXISTS olivia_authlinks_heading_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS olivia_authlinks_subheading_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS olivia_authlinks_description_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS olivia_authlinks_1_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS olivia_authlinks_2_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS olivia_authlinks_3_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS olivia_authlinks_4_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS olivia_authlinks_5_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS olivia_authlinks_6_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS olivia_authlinks_7_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS olivia_authlinks_8_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS olivia_authlinks_9_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS olivia_authlinks_10_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS olivia_authlinks_outro_value;

ALTER TABLE wp_pylons DROP COLUMN IF EXISTS ava_why_choose_us_heading_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS ava_why_choose_us_subheading_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS ava_why_choose_us_description_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS ava_why_choose_us_reason_1_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS ava_why_choose_us_reason_2_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS ava_why_choose_us_reason_3_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS ava_why_choose_us_reason_4_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS ava_why_choose_us_reason_5_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS ava_why_choose_us_reason_6_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS ava_why_choose_us_reason_7_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS ava_why_choose_us_reason_8_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS ava_why_choose_us_reason_9_value;
ALTER TABLE wp_pylons DROP COLUMN IF EXISTS ava_why_choose_us_reason_10_value;

-- Verify cleanup by showing remaining columns
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'wp_pylons' 
  AND TABLE_SCHEMA = DATABASE()
  AND COLUMN_NAME LIKE '%_value'
ORDER BY COLUMN_NAME;

-- This query should return 0 rows if cleanup was successful