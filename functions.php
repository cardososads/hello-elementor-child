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

function return_acf_introduction_options()
{
    $intros = ACFOptions::get_field('acf_intoducoes');
    $nums_destino = ACFOptions::get_field('acf_numeros_de_destino');
    $data = forms_data('Form1');
    $audio_files = [];
    $subtitles = [];

    foreach ($intros as $option) {
        $audio_files[] = $option['audio_de_introducao_'];
        $legenda_json = $option['legenda_de_introducao_'];
        // Corrigir a formatação do JSON, se necessário
        $legenda_json = str_replace("'", '"', $legenda_json);
        $legenda_json = preg_replace('/\s+/', ' ', $legenda_json);
        echo '<pre>';
        echo 'Legenda JSON: ' . $legenda_json . "\n";
        $legenda = json_decode($legenda_json, true);
        echo 'Legenda Decodificada: ';
        print_r($legenda);
        echo '</pre>';
        if (json_last_error() === JSON_ERROR_NONE) {
            $subtitles[] = $legenda;
        } else {
            $subtitles[] = [];
        }
    }

    foreach ($nums_destino as $option) {
        if ($data['destiny_number'] == $option['numero_destino_']) {
            $audio_files[] = $option['audio_destino_'];
            $legenda_json = $option['legenda_destino_'];
            // Corrigir a formatação do JSON, se necessário
            $legenda_json = str_replace("'", '"', $legenda_json);
            $legenda_json = preg_replace('/\s+/', ' ', $legenda_json);
            echo '<pre>';
            echo 'Legenda JSON: ' . $legenda_json . "\n";
            $legenda = json_decode($legenda_json, true);
            echo 'Legenda Decodificada: ';
            print_r($legenda);
            echo '</pre>';
            if (json_last_error() === JSON_ERROR_NONE) {
                $subtitles[] = $legenda;
            } else {
                $subtitles[] = [];
            }
        }
    }

    // Remover a depuração
    // echo '<pre>';
    // print_r(json_encode($subtitles));
    // echo '</pre>';

    foreach ($audio_files as $index => $audio_src) {
        ?>
        <audio id="audio_player_<?= $index ?>" src="<?= $audio_src ?>" controls <?= $index > 0 ? 'style="display:none;"' : '' ?>></audio>
        <div id="legenda_<?= $index ?>" class="legenda" style="display: none;"></div>
        <?php
    }
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const audioPlayers = document.querySelectorAll('audio');
            const subtitles = <?php echo json_encode($subtitles); ?>;
            const legendaDivs = document.querySelectorAll('.legenda');

            function exibirLegendas(index, audio) {
                const legendasParaAudio = subtitles[index];
                legendasParaAudio.forEach((legenda) => {
                    setTimeout(() => {
                        legendaDivs[index].innerText = legenda.text;
                        legendaDivs[index].style.display = 'block';
                    }, legenda.time * 1000); // Converter tempo para milissegundos
                });
            }

            audioPlayers.forEach((audio, index) => {
                audio.addEventListener('play', function() {
                    exibirLegendas(index, audio);
                });

                audio.addEventListener('ended', function() {
                    audio.style.display = 'none';
                    legendaDivs[index].style.display = 'none';
                    const nextAudio = audioPlayers[index + 1];
                    if (nextAudio) {
                        nextAudio.style.display = 'block';
                        nextAudio.play();
                    }
                });
            });
        });
    </script>
    <style>
        .legenda {
            margin-top: 10px;
            font-size: 14px;
            color: #333;
        }
    </style>
    <?php
}

add_shortcode('return_players', 'return_acf_introduction_options');

