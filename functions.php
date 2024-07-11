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

// Função para exibir var_dump dos dados dos formulários
function form_data_var_dump_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'form' => '', // Parâmetro para identificar qual formulário
        ),
        $atts,
        'form_data_var_dump'
    );

    // Obter os dados do transient com base no formulário especificado
    $data = get_transient('form' . $atts['form'] . '_submission_data');

    // Se não houver dados, retorna uma mensagem
    if (!$data) {
        return 'No data available.';
    }

    // Capturar a saída do var_dump
    ob_start();
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    return ob_get_clean();
}
add_shortcode('form_data_var_dump', 'form_data_var_dump_shortcode');



function return_acf_introduction_options()
{
    $options = ACFOptions::get_field('acf_intoducoes');

    foreach ($options as $option) {
        ?>
        <audio src="<?= $option['audio_de_introducao_'] ?>" controls></audio>
        <?php
    }
}

add_shortcode('return_players', 'return_acf_introduction_options');

