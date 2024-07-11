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
add_action('elementor_pro/forms/new_record', function ($record, $handler) {
    // Verifique qual formulário foi enviado
    $form_name = $record->get_form_settings('form_name');

    // Obtenha os dados do formulário
    $raw_fields = $record->get('fields');
    $fields = [];
    foreach ($raw_fields as $id => $field) {
        $fields[$id] = $field['value'];
    }

    // Instancia a classe de cálculo
    require_once get_stylesheet_directory() . '/inc/class-numerology-calculator.php';
    $calculator = new NumerologyCalculator();

    // Armazena os dados do formulário usando transients para acesso global
    switch ($form_name) {
        case 'Form1':
            // Realiza o cálculo do número de destino
            $fields['destiny_number'] = $calculator->calculateDestinyNumber($fields['birth_date']);
            set_transient('form1_submission_data', $fields, 60 * 60); // Armazena por 1 hora
            break;
        case 'Form2':
            // Realiza o cálculo do número de expressão
            $fields['expression_number'] = $calculator->calculateExpressionNumber($fields['full_name']);
            set_transient('form2_submission_data', $fields, 60 * 60); // Armazena por 1 hora
            break;
        case 'Form3':
            // Armazena os dados do formulário 3
            set_transient('form3_submission_data', $fields, 60 * 60); // Armazena por 1 hora
            break;
    }

}, 10, 2);

// Classe para recuperar dados dos formulários
require get_stylesheet_directory() . '/inc/class-form-data-retriever.php';

// Função para exibir dados dos formulários
function form_data_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'form' => '', // Parâmetro para identificar qual formulário
        ),
        $atts,
        'form_data'
    );

    return FormDataRetriever::display_form_data($atts['form']);
}
add_shortcode('form_data', 'form_data_shortcode');



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

