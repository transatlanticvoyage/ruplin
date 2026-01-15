-- Insert comprehensive test data for page ID 2196 - CORRECTED VERSION
-- Uses actual database schema column names from wp_pylons table
-- Run this SQL to test the dioptra interface

-- First, check if a pylon record exists for this post
INSERT INTO wp_pylons (rel_wp_post_id) 
SELECT 2196 
WHERE NOT EXISTS (SELECT 1 FROM wp_pylons WHERE rel_wp_post_id = 2196);

-- Update all fields with test data using correct column names
UPDATE wp_pylons SET 
    -- Main content fields
    hero_mainheading = 'Expert Bathroom Remodeling Services in Lakeland',
    hero_subheading = 'Transform Your Bathroom with Professional Design & Installation',
    
    -- Serena FAQ fields
    serena_faq_box_q1 = 'How long does a typical bathroom remodel take?',
    serena_faq_box_a1 = 'Most bathroom remodels take 2-3 weeks depending on the scope of work. Complete gut renovations may take 3-4 weeks, while cosmetic updates can be completed in 1-2 weeks.',
    serena_faq_box_q2 = 'What is the average cost of bathroom remodeling?',
    serena_faq_box_a2 = 'Bathroom remodel costs vary widely based on materials and scope. Basic updates start around $8,000, mid-range remodels range $15,000-25,000, and luxury renovations can exceed $35,000.',
    serena_faq_box_q3 = 'Do I need permits for bathroom remodeling?',
    serena_faq_box_a3 = 'Permits are required for most bathroom remodels that involve plumbing, electrical, or structural changes. We handle all permit applications and ensure your project meets local building codes.',
    serena_faq_box_q4 = 'Can you work with my existing plumbing layout?',
    serena_faq_box_a4 = 'Yes, we can work with existing plumbing to minimize costs. However, relocating fixtures may be beneficial for better functionality and may be worth the additional investment.',
    serena_faq_box_q5 = 'What bathroom materials do you recommend?',
    serena_faq_box_a5 = 'We recommend porcelain tiles for durability, quartz countertops for low maintenance, and quality fixtures from brands like Kohler, American Standard, and Delta for longevity.',
    serena_faq_box_q6 = 'Do you offer financing options?',
    serena_faq_box_a6 = 'Yes, we partner with several financing companies to offer flexible payment plans. We can help you find financing options that fit your budget and timeline.',
    serena_faq_box_q7 = 'What warranty do you provide?',
    serena_faq_box_a7 = 'We provide a comprehensive 2-year warranty on all workmanship and honor manufacturer warranties on all materials and fixtures. Your satisfaction is our priority.',
    serena_faq_box_q8 = 'Can you help with design and material selection?',
    serena_faq_box_a8 = 'Absolutely! Our design team will help you select materials, colors, and layouts that match your style and budget. We provide 3D renderings for major renovations.',
    
    -- Chen Cards (3 service cards)
    chenblock_card1_title = 'Complete Bathroom Renovation',
    chenblock_card1_desc = 'Full-service bathroom remodeling from design to completion. We handle everything: demolition, plumbing, electrical, tiling, and finishing touches.',
    chenblock_card2_title = 'Shower & Tub Installation',
    chenblock_card2_desc = 'Expert installation of walk-in showers, tub-to-shower conversions, soaking tubs, and accessibility modifications for aging in place.',
    chenblock_card3_title = 'Vanity & Storage Solutions',
    chenblock_card3_desc = 'Custom vanity installation, countertop replacement, and smart storage solutions to maximize your bathroom''s functionality and style.',
    
    -- Liz Pricing fields
    liz_pricing_heading = 'Transparent Bathroom Remodeling Pricing',
    liz_pricing_description = 'Get honest, upfront pricing for your bathroom renovation project.',
    liz_pricing_body = 'We believe in transparent pricing with no hidden fees. Every estimate includes detailed breakdowns of materials, labor, and timeline. Request your free consultation today.',
    
    -- Brook Video fields
    brook_video_heading = 'See Our Bathroom Transformations',
    brook_video_subheading = 'Watch real customer bathroom makeovers',
    brook_video_description = 'Discover how we transform outdated bathrooms into stunning, functional spaces that homeowners love.',
    brook_video_1 = 'Before & After: Modern Master Bathroom Renovation',
    brook_video_2 = 'Small Bathroom Big Impact: Space-Saving Solutions',
    brook_video_3 = 'Luxury Shower Installation Process',
    brook_video_4 = 'Accessible Bathroom Design for Aging in Place',
    brook_video_outro = 'Ready to see your bathroom transformed? Contact us for your free consultation and estimate.',
    
    -- Olivia Authlinks fields
    olivia_authlinks_heading = 'Trusted Bathroom Remodeling Resources',
    olivia_authlinks_subheading = 'Expert advice and inspiration for your project',
    olivia_authlinks_description = 'Explore our curated collection of bathroom design ideas, maintenance tips, and industry insights.',
    olivia_authlinks_1 = 'Bathroom Design Trends 2024: What''s Popular This Year',
    olivia_authlinks_2 = 'How to Choose the Right Tile for Your Bathroom',
    olivia_authlinks_3 = 'Bathroom Lighting Ideas: Creating the Perfect Ambiance',
    olivia_authlinks_4 = 'Small Bathroom Storage Solutions That Actually Work',
    olivia_authlinks_5 = 'Understanding Bathroom Ventilation Requirements',
    olivia_authlinks_6 = 'Bathroom Safety: Essential Features for All Ages',
    olivia_authlinks_7 = 'Eco-Friendly Bathroom Materials and Fixtures',
    olivia_authlinks_8 = 'Bathroom Maintenance Tips for Long-Lasting Results',
    olivia_authlinks_9 = 'Planning Your Bathroom Remodel: A Step-by-Step Guide',
    olivia_authlinks_10 = 'Understanding Bathroom Plumbing: What You Need to Know',
    olivia_authlinks_outro = 'Have questions about your bathroom project? Our experts are here to help guide you through every decision.',
    
    -- Ava Why Choose Us fields
    ava_why_choose_us_heading = 'Why Choose Our Bathroom Remodeling Services?',
    ava_why_choose_us_subheading = 'Experience the difference of working with true professionals',
    ava_why_choose_us_description = 'We''ve been transforming bathrooms in Lakeland for over 15 years, earning trust through quality work and exceptional service.',
    ava_why_choose_us_reason_1 = 'Licensed & Insured Contractors',
    ava_why_choose_us_reason_2 = 'Free Design Consultation & 3D Renderings',
    ava_why_choose_us_reason_3 = '2-Year Warranty on All Workmanship',
    ava_why_choose_us_reason_4 = 'Local Lakeland Family-Owned Business',
    ava_why_choose_us_reason_5 = 'Transparent Pricing - No Hidden Fees',
    ava_why_choose_us_reason_6 = 'High-Quality Materials from Trusted Brands',
    ava_why_choose_us_reason_7 = 'Clean, Professional Installation Process',
    ava_why_choose_us_reason_8 = 'Flexible Financing Options Available',
    ava_why_choose_us_reason_9 = '500+ Satisfied Lakeland Customers',
    ava_why_choose_us_reason_10 = 'Emergency Repair Services Available',
    
    -- Kendall Our Process fields (using correct column names from schema)
    kendall_our_process_heading = 'Our Proven Bathroom Remodeling Process',
    kendall_our_process_subheading = 'From consultation to completion in 5 simple steps',
    kendall_our_process_description = 'We''ve perfected our remodeling process to ensure every project is completed on time, within budget, and to your exact specifications.',
    kendall_our_process_step_1 = 'Free In-Home Consultation: We visit your home to assess your space, discuss your vision, and provide expert recommendations tailored to your needs and budget.',
    kendall_our_process_step_2 = 'Design & Material Selection: Our design team creates detailed plans and helps you select materials, fixtures, and finishes that bring your vision to life.',
    kendall_our_process_step_3 = 'Transparent Pricing & Contracts: We provide detailed estimates with no hidden fees and clear timelines. Once approved, we schedule your project start date.',
    kendall_our_process_step_4 = 'Professional Installation: Our skilled craftsmen complete your renovation with precision and care, keeping you informed throughout the entire process.',
    kendall_our_process_step_5 = 'Final Walkthrough & Warranty: We conduct a thorough final inspection with you and provide comprehensive warranty documentation for your peace of mind.',
    
    -- Sara Custom HTML (bathroom-specific HTML content)
    sara_customhtml_datum = '<div class="bathroom-showcase">
    <h3>Featured Bathroom Transformations</h3>
    <div class="transformation-grid">
        <div class="before-after">
            <h4>Master Bathroom Renovation</h4>
            <p>Transformed a cramped 1980s bathroom into a spa-like retreat with walk-in shower, double vanity, and luxury finishes.</p>
        </div>
        <div class="testimonial-highlight">
            <blockquote>"The team exceeded our expectations. Our new bathroom is absolutely beautiful and functional. The project was completed on time and within budget."</blockquote>
            <cite>- Sarah M., Lakeland Homeowner</cite>
        </div>
    </div>
</div>',
    
    -- Location and contact fields
    locpage_city = 'Lakeland',
    locpage_state_code = 'FL',
    locpage_state_full = 'Florida',
    locpage_gmaps_string = 'Lakeland, FL',
    
    -- Trust and sidebar elements
    trustblock_vezzy_title = 'Locally Trusted Since 2008',
    trustblock_vezzy_desc = 'Over 15 years serving Lakeland homeowners',
    sidebar_zebby_wait_time = '2-3 weeks typical completion',
    
    -- CTA and content blocks
    baynar1_main = 'Transform your bathroom into the space you''ve always wanted. Call today for your free consultation!',
    baynar2_main = 'Licensed, insured, and locally owned. Your satisfaction is our guarantee.'
    
WHERE rel_wp_post_id = 2196;

-- Verify the data was inserted using correct column name
SELECT rel_wp_post_id, hero_mainheading, serena_faq_box_q1, chenblock_card1_title, liz_pricing_heading 
FROM wp_pylons 
WHERE rel_wp_post_id = 2196;