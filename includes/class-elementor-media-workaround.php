<?php

/**
 * Elementor Media Library Workaround
 * Adds custom zen data options to WordPress media library when accessed from Elementor
 * Provides Dynamic Tags functionality without requiring Elementor Pro
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Zen_Elementor_Media_Workaround {
    
    public function __construct() {
        add_action('wp_enqueue_media', array($this, 'enqueue_media_scripts'));
        add_action('wp_ajax_zen_get_services_for_media', array($this, 'ajax_get_services_for_media'));
        add_action('wp_ajax_zen_get_locations_for_media', array($this, 'ajax_get_locations_for_media'));
        add_action('wp_ajax_zen_get_service_image_data', array($this, 'ajax_get_service_image_data'));
        add_action('wp_ajax_zen_get_location_image_data', array($this, 'ajax_get_location_image_data'));
        add_action('print_media_templates', array($this, 'add_zen_media_templates'));
    }
    
    /**
     * Enqueue scripts for media library enhancement
     */
    public function enqueue_media_scripts() {
        // Only load on admin pages where media library might be used
        if (is_admin()) {
            wp_enqueue_script(
                'zen-media-workaround',
                SNEFURU_PLUGIN_URL . 'assets/zen-media-workaround.js',
                array('jquery', 'media-views'),
                SNEFURU_PLUGIN_VERSION,
                true
            );
            
            wp_localize_script('zen-media-workaround', 'zenMediaAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('zen_media_nonce'),
                'plugin_url' => SNEFURU_PLUGIN_URL
            ));
            
            wp_enqueue_style(
                'zen-media-workaround',
                SNEFURU_PLUGIN_URL . 'assets/zen-media-workaround.css',
                array(),
                SNEFURU_PLUGIN_VERSION
            );
        }
    }
    
    /**
     * AJAX handler to get services data for media library
     */
    public function ajax_get_services_for_media() {
        check_ajax_referer('zen_media_nonce', 'nonce');
        
        $services = Ruplin_WP_Database_Horse_Class::get_services(array(
            'orderby' => 'position_in_custom_order',
            'order' => 'ASC'
        ));
        
        $formatted_services = array();
        
        foreach ($services as $service) {
            $image_url = '';
            $image_id = 0;
            
            if ($service->rel_image1_id) {
                $image_url = wp_get_attachment_image_url($service->rel_image1_id, 'medium');
                $image_id = $service->rel_image1_id;
            }
            
            $formatted_services[] = array(
                'id' => $service->service_id,
                'name' => $service->service_name,
                'placard' => $service->service_placard,
                'description' => $service->description1_short,
                'image_id' => $image_id,
                'image_url' => $image_url,
                'is_pinned' => $service->is_pinned_service
            );
        }
        
        wp_send_json_success($formatted_services);
    }
    
    /**
     * AJAX handler to get locations data for media library
     */
    public function ajax_get_locations_for_media() {
        check_ajax_referer('zen_media_nonce', 'nonce');
        
        $locations = Ruplin_WP_Database_Horse_Class::get_locations(array(
            'orderby' => 'position_in_custom_order',
            'order' => 'ASC'
        ));
        
        $formatted_locations = array();
        
        foreach ($locations as $location) {
            $image_url = '';
            $image_id = 0;
            
            if ($location->rel_image1_id) {
                $image_url = wp_get_attachment_image_url($location->rel_image1_id, 'medium');
                $image_id = $location->rel_image1_id;
            }
            
            // Format address
            $address_parts = array();
            if ($location->street) $address_parts[] = $location->street;
            if ($location->city) $address_parts[] = $location->city;
            if ($location->state_code) $address_parts[] = $location->state_code;
            if ($location->zip_code) $address_parts[] = $location->zip_code;
            $address = implode(', ', $address_parts);
            
            $formatted_locations[] = array(
                'id' => $location->location_id,
                'name' => $location->location_name,
                'placard' => $location->location_placard,
                'address' => $address,
                'image_id' => $image_id,
                'image_url' => $image_url,
                'is_pinned' => $location->is_pinned_location
            );
        }
        
        wp_send_json_success($formatted_locations);
    }
    
    /**
     * AJAX handler to get specific service image data for Elementor
     */
    public function ajax_get_service_image_data() {
        check_ajax_referer('zen_media_nonce', 'nonce');
        
        $service_id = intval($_POST['service_id']);
        $size = sanitize_text_field($_POST['size']);
        
        if (!$service_id) {
            wp_send_json_error('Service ID required');
        }
        
        $service = Ruplin_WP_Database_Horse_Class::get_service($service_id);
        
        if (!$service || !$service->rel_image1_id) {
            wp_send_json_error('Service or image not found');
        }
        
        $attachment_id = $service->rel_image1_id;
        $image_url = wp_get_attachment_image_url($attachment_id, $size);
        $full_image_url = wp_get_attachment_image_url($attachment_id, 'full');
        $attachment = get_post($attachment_id);
        
        if (!$image_url) {
            wp_send_json_error('Image not found');
        }
        
        // Return data in format expected by Elementor
        $image_data = array(
            'id' => $attachment_id,
            'url' => $image_url,
            'full_url' => $full_image_url,
            'title' => get_the_title($attachment_id),
            'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
            'caption' => $attachment ? $attachment->post_excerpt : '',
            'description' => $attachment ? $attachment->post_content : '',
            'zen_type' => 'service',
            'zen_id' => $service_id,
            'zen_name' => $service->service_name
        );
        
        wp_send_json_success($image_data);
    }
    
    /**
     * AJAX handler to get specific location image data for Elementor
     */
    public function ajax_get_location_image_data() {
        check_ajax_referer('zen_media_nonce', 'nonce');
        
        $location_id = intval($_POST['location_id']);
        $size = sanitize_text_field($_POST['size']);
        
        if (!$location_id) {
            wp_send_json_error('Location ID required');
        }
        
        $location = Ruplin_WP_Database_Horse_Class::get_location($location_id);
        
        if (!$location || !$location->rel_image1_id) {
            wp_send_json_error('Location or image not found');
        }
        
        $attachment_id = $location->rel_image1_id;
        $image_url = wp_get_attachment_image_url($attachment_id, $size);
        $full_image_url = wp_get_attachment_image_url($attachment_id, 'full');
        $attachment = get_post($attachment_id);
        
        if (!$image_url) {
            wp_send_json_error('Image not found');
        }
        
        // Return data in format expected by Elementor
        $image_data = array(
            'id' => $attachment_id,
            'url' => $image_url,
            'full_url' => $full_image_url,
            'title' => get_the_title($attachment_id),
            'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
            'caption' => $attachment ? $attachment->post_excerpt : '',
            'description' => $attachment ? $attachment->post_content : '',
            'zen_type' => 'location',
            'zen_id' => $location_id,
            'zen_name' => $location->location_name
        );
        
        wp_send_json_success($image_data);
    }
    
    /**
     * Add custom media templates for zen data
     */
    public function add_zen_media_templates() {
        ?>
        <script type="text/html" id="tmpl-zen-services-tab">
            <div class="zen-media-content">
                <h3>🚀 Zen Services</h3>
                <div class="zen-services-grid">
                    <# _.each(data.services, function(service) { #>
                        <div class="zen-service-item" data-service-id="{{service.id}}">
                            <# if (service.image_url) { #>
                                <div class="zen-service-image">
                                    <img src="{{service.image_url}}" alt="{{service.name}}" />
                                </div>
                            <# } else { #>
                                <div class="zen-service-image zen-no-image">
                                    <span>📋</span>
                                </div>
                            <# } #>
                            <div class="zen-service-info">
                                <h4>{{service.name}}</h4>
                                <# if (service.placard) { #>
                                    <p class="zen-placard">{{service.placard}}</p>
                                <# } #>
                                <# if (service.is_pinned) { #>
                                    <span class="zen-pinned">📌 Pinned</span>
                                <# } #>
                                <button type="button" class="button zen-select-service" data-service-id="{{service.id}}">
                                    Select Service Image
                                </button>
                            </div>
                        </div>
                    <# }); #>
                </div>
            </div>
        </script>
        
        <script type="text/html" id="tmpl-zen-locations-tab">
            <div class="zen-media-content">
                <h3>📍 Zen Locations</h3>
                <div class="zen-locations-grid">
                    <# _.each(data.locations, function(location) { #>
                        <div class="zen-location-item" data-location-id="{{location.id}}">
                            <# if (location.image_url) { #>
                                <div class="zen-location-image">
                                    <img src="{{location.image_url}}" alt="{{location.name}}" />
                                </div>
                            <# } else { #>
                                <div class="zen-location-image zen-no-image">
                                    <span>📍</span>
                                </div>
                            <# } #>
                            <div class="zen-location-info">
                                <h4>{{location.name}}</h4>
                                <# if (location.placard) { #>
                                    <p class="zen-placard">{{location.placard}}</p>
                                <# } #>
                                <# if (location.address) { #>
                                    <p class="zen-address">{{location.address}}</p>
                                <# } #>
                                <# if (location.is_pinned) { #>
                                    <span class="zen-pinned">📌 Pinned</span>
                                <# } #>
                                <button type="button" class="button zen-select-location" data-location-id="{{location.id}}">
                                    Select Location Image
                                </button>
                            </div>
                        </div>
                    <# }); #>
                </div>
            </div>
        </script>
        <?php
    }
}