<?php
/**
 * Phone Formatter Class
 * Handles phone number formatting and HTML generation for headers
 * Test comment to show in VSCode source control
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ruplin_Phone_Formatter {
    
    /**
     * Get formatted phone HTML for specific header type
     */
    public function get_phone_html_for_header($header_type = 'header2') {
        $phone_data = $this->get_phone_data();
        
        if (empty($phone_data['raw'])) {
            return '';
        }
        
        $classes = $this->get_phone_classes($header_type);
        $icon_svg = $this->get_phone_icon();
        
        return '<a href="tel:' . esc_attr($phone_data['tel']) . '" class="' . $classes['button'] . '">' .
               '<svg class="' . $classes['icon'] . '" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">' .
               $icon_svg .
               '</svg>' .
               esc_html($phone_data['formatted']) .
               '</a>';
    }
    
    /**
     * Get phone data from various sources
     */
    private function get_phone_data() {
        // Try theme function first
        $phone_raw = '';
        $phone_formatted = '';
        
        if (function_exists('staircase_get_header_phone')) {
            $phone_raw = staircase_get_header_phone();
        }
        
        if (function_exists('staircase_get_formatted_phone')) {
            $phone_formatted = staircase_get_formatted_phone();
        }
        
        // Fallback to options
        if (empty($phone_raw)) {
            $phone_raw = get_option('staircase_header_phone', '');
        }
        
        if (empty($phone_formatted)) {
            $phone_formatted = $this->format_phone_number($phone_raw);
        }
        
        return array(
            'raw' => $phone_raw,
            'formatted' => $phone_formatted,
            'tel' => $this->clean_phone_for_tel($phone_raw)
        );
    }
    
    /**
     * Format phone number for display
     */
    private function format_phone_number($phone_number) {
        if (empty($phone_number)) {
            return '';
        }
        
        // Remove all non-numeric characters except +
        $cleaned = preg_replace('/[^\d+]/', '', $phone_number);
        
        // Handle US phone numbers
        if (preg_match('/^(\+?1)?(\d{3})(\d{3})(\d{4})$/', $cleaned, $matches)) {
            $country_code = !empty($matches[1]) ? '+1 ' : '';
            return $country_code . '(' . $matches[2] . ') ' . $matches[3] . '-' . $matches[4];
        }
        
        // Handle international numbers
        if (preg_match('/^\+(\d{1,3})(\d+)$/', $cleaned, $matches)) {
            return '+' . $matches[1] . ' ' . $this->format_international_number($matches[2]);
        }
        
        // Return original if no pattern matches
        return $phone_number;
    }
    
    /**
     * Format international phone number
     */
    private function format_international_number($number) {
        $length = strlen($number);
        
        if ($length >= 10) {
            // Format as groups of 3-3-4 for 10+ digits
            return preg_replace('/(\d{3})(\d{3})(\d{4})(.*)/', '$1 $2 $3$4', $number);
        } elseif ($length >= 7) {
            // Format as groups of 3-4 for 7-9 digits
            return preg_replace('/(\d{3})(\d+)/', '$1 $2', $number);
        }
        
        return $number;
    }
    
    /**
     * Clean phone number for tel: attribute
     */
    private function clean_phone_for_tel($phone_number) {
        return preg_replace('/[^0-9+]/', '', $phone_number);
    }
    
    /**
     * Get CSS classes for different header types
     */
    private function get_phone_classes($header_type) {
        $classes = array(
            'header1' => array(
                'button' => 'header1-phone-button',
                'icon' => 'header1-phone-icon'
            ),
            'header2' => array(
                'button' => 'hs2-phone-button',
                'icon' => 'hs2-phone-icon'
            ),
            'header3' => array(
                'button' => 'header3-phone-button',
                'icon' => 'header3-phone-icon'
            )
        );
        
        return isset($classes[$header_type]) ? $classes[$header_type] : $classes['header2'];
    }
    
    /**
     * Get phone icon SVG path
     */
    private function get_phone_icon() {
        return '<path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>';
    }
    
    /**
     * Get phone number data for admin
     */
    public function get_phone_admin_data() {
        return array(
            'raw' => function_exists('staircase_get_header_phone') ? staircase_get_header_phone() : get_option('staircase_header_phone', ''),
            'formatted' => function_exists('staircase_get_formatted_phone') ? staircase_get_formatted_phone() : '',
            'option_key' => 'staircase_header_phone'
        );
    }
    
    /**
     * Save phone number
     */
    public function save_phone_number($phone_number) {
        update_option('staircase_header_phone', sanitize_text_field($phone_number));
        return true;
    }
    
    /**
     * Validate phone number format
     */
    public function validate_phone_number($phone_number) {
        if (empty($phone_number)) {
            return true; // Empty is valid
        }
        
        // Basic validation - should contain numbers
        if (!preg_match('/\d/', $phone_number)) {
            return false;
        }
        
        // Should not be too short or too long
        $digits_only = preg_replace('/\D/', '', $phone_number);
        $length = strlen($digits_only);
        
        return $length >= 7 && $length <= 15;
    }
    
    /**
     * Get phone number suggestions based on partial input
     */
    public function get_phone_suggestions($partial_phone) {
        $suggestions = array();
        
        // Remove non-digits for processing
        $digits = preg_replace('/\D/', '', $partial_phone);
        $length = strlen($digits);
        
        if ($length >= 3 && $length <= 10) {
            // US format suggestions
            if ($length == 10) {
                $suggestions[] = $this->format_phone_number($digits);
                $suggestions[] = $this->format_phone_number('+1' . $digits);
            } elseif ($length < 10) {
                $remaining = 10 - $length;
                $example_digits = str_repeat('X', $remaining);
                $suggestions[] = 'Example: ' . $this->format_phone_number($digits . str_repeat('0', $remaining));
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Get phone analytics data
     */
    public function get_phone_analytics() {
        return array(
            'click_tracking' => get_option('phone_click_tracking', false),
            'total_clicks' => get_option('phone_total_clicks', 0),
            'last_click' => get_option('phone_last_click', ''),
            'most_clicked_page' => get_option('phone_most_clicked_page', '')
        );
    }
    
    /**
     * Track phone click
     */
    public function track_phone_click($page_url = '') {
        if (!get_option('phone_click_tracking', false)) {
            return;
        }
        
        $total_clicks = get_option('phone_total_clicks', 0);
        update_option('phone_total_clicks', $total_clicks + 1);
        update_option('phone_last_click', current_time('mysql'));
        
        if (!empty($page_url)) {
            update_option('phone_most_clicked_page', $page_url);
        }
    }
}