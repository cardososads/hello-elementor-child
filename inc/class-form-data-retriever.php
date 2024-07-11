<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class FormDataRetriever {

    public static function get_form_data($form_name) {
        $transient_key = 'form' . $form_name . '_submission_data';
        $data = get_transient($transient_key);

        if (!$data) {
            return 'No data available.';
        }

        return $data;
    }

    public static function display_form_data($form_name) {
        $data = self::get_form_data($form_name);

        if (is_string($data)) {
            return $data; // No data available message
        }

        $output = '<div class="form-data">';
        foreach ($data as $key => $value) {
            $output .= '<p><strong>' . esc_html($key) . ':</strong> ' . esc_html($value) . '</p>';
        }
        $output .= '</div>';

        return $output;
    }
}
