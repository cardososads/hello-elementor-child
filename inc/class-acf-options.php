<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class ACFOptions {
    public function __construct() {
        add_action('acf/init', array($this, 'add_acf_options_page'));
    }

    public function add_acf_options_page() {
        if ( function_exists('acf_add_options_page') ) {
            acf_add_options_page(array(
                'page_title'    => 'Theme General Settings',
                'menu_title'    => 'Theme Settings',
                'menu_slug'     => 'theme-general-settings',
                'capability'    => 'edit_posts',
                'redirect'      => false
            ));
        }
    }

    public static function get_field($field_name, $default = '') {
        $value = get_field($field_name, 'option');
        return $value ? $value : $default;
    }
}

new ACFOptions();
