-- Add Silkweaver menu anchor columns to sitespren table in Supabase
-- These columns will store custom anchor text for the Services and Locations dropdown menus

ALTER TABLE sitespren 
ADD COLUMN home_anchor_for_silkweaver_services TEXT,
ADD COLUMN home_anchor_for_silkweaver_locations TEXT;

-- Optional: Add comments to document the purpose of these columns
COMMENT ON COLUMN sitespren.home_anchor_for_silkweaver_services IS 'Custom anchor text for Services dropdown in Silkweaver menu system';
COMMENT ON COLUMN sitespren.home_anchor_for_silkweaver_locations IS 'Custom anchor text for Locations dropdown in Silkweaver menu system';