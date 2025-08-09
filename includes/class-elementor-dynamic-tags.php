<?php

/**
 * Elementor Dynamic Tags for Zen Services and Locations
 * Integrates zen_services and zen_locations data with Elementor's Dynamic Tags system
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Main Dynamic Tags Manager Class
 */
class Zen_Elementor_Dynamic_Tags {
    
    public function __construct() {
        add_action('elementor/dynamic_tags/register_tags', array($this, 'register_dynamic_tags'));
        add_action('elementor/dynamic_tags/register_groups', array($this, 'register_dynamic_groups'));
    }
    
    /**
     * Register our custom dynamic tag group
     */
    public function register_dynamic_groups($dynamic_tags) {
        $dynamic_tags->register_group('zen-garage', array(
            'title' => 'Zen Garage'
        ));
    }
    
    /**
     * Register our custom dynamic tags with Elementor
     */
    public function register_dynamic_tags($dynamic_tags) {
        // Register our custom dynamic tags
        $dynamic_tags->register_tag('Zen_Service_Image_Tag');
        $dynamic_tags->register_tag('Zen_Service_Name_Tag');
        $dynamic_tags->register_tag('Zen_Location_Image_Tag');
        $dynamic_tags->register_tag('Zen_Location_Name_Tag');
    }
}

/**
 * Zen Service Image Dynamic Tag
 */
class Zen_Service_Image_Tag extends \Elementor\Core\DynamicTags\Data_Tag {
    
    public function get_name() {
        return 'zen-service-image';
    }
    
    public function get_title() {
        return 'Zen Service Image';
    }
    
    public function get_group() {
        return 'zen-garage';
    }
    
    public function get_categories() {
        return [\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY];
    }
    
    protected function _register_controls() {
        $this->add_control(
            'service_id',
            [
                'label' => 'Service ID',
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1,
                'min' => 1,
                'description' => 'Enter the ID of the service from your zen_services table',
            ]
        );
        
        $this->add_control(
            'image_size',
            [
                'label' => 'Image Size',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'thumbnail' => 'Thumbnail (150x150)',
                    'medium' => 'Medium (300x300)',
                    'large' => 'Large (1024x1024)',
                    'full' => 'Full Size',
                ],
                'default' => 'medium',
            ]
        );
    }
    
    public function get_value(array $options = []) {
        $service_id = $this->get_settings('service_id');
        $image_size = $this->get_settings('image_size');
        
        if (!$service_id) {
            return [];
        }
        
        // Get service data
        $service = Ruplin_WP_Database_Horse_Class::get_service($service_id);
        
        if (!$service || !$service->rel_image1_id) {
            return [];
        }
        
        // Get WordPress attachment data
        $attachment_id = $service->rel_image1_id;
        $image_url = wp_get_attachment_image_url($attachment_id, $image_size);
        
        if (!$image_url) {
            return [];
        }
        
        return [
            'id' => $attachment_id,
            'url' => $image_url,
        ];
    }
}

/**
 * Zen Service Name Dynamic Tag
 */
class Zen_Service_Name_Tag extends \Elementor\Core\DynamicTags\Tag {
    
    public function get_name() {
        return 'zen-service-name';
    }
    
    public function get_title() {
        return 'Zen Service Name';
    }
    
    public function get_group() {
        return 'zen-garage';
    }
    
    public function get_categories() {
        return [\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY];
    }
    
    protected function _register_controls() {
        $this->add_control(
            'service_id',
            [
                'label' => 'Service ID',
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1,
                'min' => 1,
                'description' => 'Enter the ID of the service from your zen_services table',
            ]
        );
        
        $this->add_control(
            'field_type',
            [
                'label' => 'Service Field',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'service_name' => 'Service Name',
                    'service_placard' => 'Service Placard',
                    'service_moniker' => 'Service Moniker',
                    'service_sobriquet' => 'Service Sobriquet',
                    'description1_short' => 'Short Description',
                    'description1_long' => 'Long Description',
                ],
                'default' => 'service_name',
            ]
        );
    }
    
    public function render() {
        $service_id = $this->get_settings('service_id');
        $field_type = $this->get_settings('field_type');
        
        if (!$service_id) {
            return '';
        }
        
        // Get service data
        $service = Ruplin_WP_Database_Horse_Class::get_service($service_id);
        
        if (!$service) {
            return '';
        }
        
        $value = isset($service->{$field_type}) ? $service->{$field_type} : '';
        echo esc_html($value);
    }
}

/**
 * Zen Location Image Dynamic Tag
 */
class Zen_Location_Image_Tag extends \Elementor\Core\DynamicTags\Data_Tag {
    
    public function get_name() {
        return 'zen-location-image';
    }
    
    public function get_title() {
        return 'Zen Location Image';
    }
    
    public function get_group() {
        return 'zen-garage';
    }
    
    public function get_categories() {
        return [\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY];
    }
    
    protected function _register_controls() {
        $this->add_control(
            'location_id',
            [
                'label' => 'Location ID',
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1,
                'min' => 1,
                'description' => 'Enter the ID of the location from your zen_locations table',
            ]
        );
        
        $this->add_control(
            'image_size',
            [
                'label' => 'Image Size',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'thumbnail' => 'Thumbnail (150x150)',
                    'medium' => 'Medium (300x300)',
                    'large' => 'Large (1024x1024)',
                    'full' => 'Full Size',
                ],
                'default' => 'medium',
            ]
        );
    }
    
    public function get_value(array $options = []) {
        $location_id = $this->get_settings('location_id');
        $image_size = $this->get_settings('image_size');
        
        if (!$location_id) {
            return [];
        }
        
        // Get location data
        $location = Ruplin_WP_Database_Horse_Class::get_location($location_id);
        
        if (!$location || !$location->rel_image1_id) {
            return [];
        }
        
        // Get WordPress attachment data
        $attachment_id = $location->rel_image1_id;
        $image_url = wp_get_attachment_image_url($attachment_id, $image_size);
        
        if (!$image_url) {
            return [];
        }
        
        return [
            'id' => $attachment_id,
            'url' => $image_url,
        ];
    }
}

/**
 * Zen Location Name Dynamic Tag
 */
class Zen_Location_Name_Tag extends \Elementor\Core\DynamicTags\Tag {
    
    public function get_name() {
        return 'zen-location-name';
    }
    
    public function get_title() {
        return 'Zen Location Name';
    }
    
    public function get_group() {
        return 'zen-garage';
    }
    
    public function get_categories() {
        return [\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY];
    }
    
    protected function _register_controls() {
        $this->add_control(
            'location_id',
            [
                'label' => 'Location ID',
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1,
                'min' => 1,
                'description' => 'Enter the ID of the location from your zen_locations table',
            ]
        );
        
        $this->add_control(
            'field_type',
            [
                'label' => 'Location Field',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'location_name' => 'Location Name',
                    'location_placard' => 'Location Placard',
                    'location_moniker' => 'Location Moniker',
                    'location_sobriquet' => 'Location Sobriquet',
                    'street' => 'Street Address',
                    'city' => 'City',
                    'state_code' => 'State Code',
                    'zip_code' => 'Zip Code',
                    'country' => 'Country',
                ],
                'default' => 'location_name',
            ]
        );
    }
    
    public function render() {
        $location_id = $this->get_settings('location_id');
        $field_type = $this->get_settings('field_type');
        
        if (!$location_id) {
            return '';
        }
        
        // Get location data
        $location = Ruplin_WP_Database_Horse_Class::get_location($location_id);
        
        if (!$location) {
            return '';
        }
        
        $value = isset($location->{$field_type}) ? $location->{$field_type} : '';
        echo esc_html($value);
    }
}