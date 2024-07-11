<?php
function hello_elementor_child_enqueue_styles() {
    wp_enqueue_style('hello-elementor-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('hello-elementor-child-style', get_stylesheet_directory_uri() . '/style.css', array('hello-elementor-style'));
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles');

require get_stylesheet_directory() . '/inc/class-acf-options.php';
require get_stylesheet_directory() . '/inc/class-form-data-retriever.php';
require get_stylesheet_directory() . '/inc/class-numerology-calculator.php';

// Hook para processar o envio dos formulários
add_action('elementor_pro/forms/new_record', 'process_elementor_form_submission', 10, 2);

function process_elementor_form_submission($record, $handler) {
    // Verifique qual formulário foi enviado
    $form_name = $record->get_form_settings('form_name');

    // Obtenha os dados do formulário
    $fields = array_map(function($field) {
        return $field['value'];
    }, $record->get('fields'));

    // Instancia a classe de cálculo
    $calculator = new NumerologyCalculator();

    // Processa os dados conforme o formulário enviado
    switch ($form_name) {
        case 'Form1':
            $fields['destiny_number'] = $calculator->calculateDestinyNumber($fields['birth_date']);
            break;
        case 'Form2':
            $fields['expression_number'] = $calculator->calculateExpressionNumber($fields['full_name']);
            break;
    }

    // Armazena os dados do formulário usando transients para acesso global
    set_transient("form{$form_name}_submission_data", $fields, HOUR_IN_SECONDS);
}

// Variáveis globais para armazenar os dados dos formulários
global $form1_data, $form2_data, $form3_data;

// Função para tornar os dados dos formulários globais
function make_form_data_global() {
    global $form1_data, $form2_data, $form3_data;

    $form1_data = get_transient('form1_submission_data');
    $form2_data = get_transient('form2_submission_data');
    $form3_data = get_transient('form3_submission_data');
}
add_action('init', 'make_form_data_global');

// Função para exibir os dados dos formulários e tornar os dados globais
function form_data_var_dump_shortcode($atts) {
    global $form1_data, $form2_data, $form3_data;

    $atts = shortcode_atts(
        array(
            'form' => '', // Parâmetro para identificar qual formulário
        ),
        $atts,
        'form_data_var_dump'
    );

    switch ($atts['form']) {
        case '1':
            $data = $form1_data;
            break;
        case '2':
            $data = $form2_data;
            break;
        case '3':
            $data = $form3_data;
            break;
        default:
            return 'No data available.';
    }

    if (!$data) {
        return 'No data available.';
    }

    // Capturar a saída do var_dump
    ob_start();
    echo '<pre>';
    var_dump($data['destiny_number'] ?? $data);
    echo '</pre>';
    return ob_get_clean();
}
add_shortcode('form_data_var_dump', 'form_data_var_dump_shortcode');

// Função para retornar opções ACF com um player de áudio
function return_acf_introduction_options() {
    global $form1_data, $form2_data, $form3_data;

    // Exemplo de uso dos dados do Form1
    if (!empty($form1_data)) {
        echo 'Dados do Form1:';
        foreach ($form1_data as $key => $value) {
            echo '<p>' . esc_html($key) . ': ' . esc_html($value) . '</p>';
        }
    }

    $options = ACFOptions::get_field('acf_introducoes');

    foreach ($options as $option) {
        ?>
        <audio src="<?= esc_url($option['audio_de_introducao_']); ?>" controls></audio>
        <?php
    }
}
add_shortcode('return_players', 'return_acf_introduction_options');
