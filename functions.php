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
function forms_data($form) {
    // Verifique se o formulário é Form1 ou Form2
    if (in_array($form, ['Form1', 'Form2'])) {
        // Obtenha os dados do transient com base no nome do formulário
        $data = get_transient('form' . $form . '_submission_data');
        return $data;
    }

    return null;
}

// Função para retornar opções ACF com um player de áudio e usar os dados dos formulários
// Função para retornar opções ACF com um player de áudio e usar os dados dos formulários
function return_acf_introduction_options() {
    $intros = ACFOptions::get_field('acf_introducoes');
    $nums_destino = ACFOptions::get_field('acf_numeros_de_destino');
    $data = forms_data('Form1');

    // Verificar se os dados foram obtidos corretamente
    if (!$data) {
        echo 'No data available.';
        return;
    }

    $counter = 0;

    // Renderizar players de introdução primeiro
    foreach ($intros as $option) {
        $counter++;
        ?>
        <audio id="audio-<?= $counter ?>" src="<?= esc_url($option['audio_de_introducao_']) ?>" controls style="display: <?= $counter === 1 ? 'block' : 'none' ?>;"></audio>
        <?php
    }

    // Renderizar player de número de destino por último
    foreach ($nums_destino as $option) {
        if ($data['destiny_number'] == $option['numero_destino_']) {
            $counter++;
            ?>
            <audio id="audio-<?= $counter ?>" src="<?= esc_url($option['audio_destino_']) ?>" controls style="display: none;"></audio>
            <?php
        }
    }

    // Inclui o script para controle dos áudios
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let current = 1;
            let total = <?= $counter ?>;

            function playNext() {
                if (current < total) {
                    document.getElementById('audio-' + current).style.display = 'none';
                    current++;
                    document.getElementById('audio-' + current).style.display = 'block';
                }
            }

            for (let i = 1; i <= total; i++) {
                let audioElement = document.getElementById('audio-' + i);
                audioElement.addEventListener('ended', playNext);
            }
        });
    </script>
    <?php
}
add_shortcode('return_players', 'return_acf_introduction_options');