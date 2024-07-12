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
    $form_name = $record->get_form_settings('form_name');
    $fields = [];
    foreach ($record->get('fields') as $field) {
        $fields[$field['name']] = $field['value'];
    }

    $calculator = new NumerologyCalculator();

    switch ($form_name) {
        case 'Form1':
            if (isset($fields['birth_date'])) {
                $fields['destiny_number'] = $calculator->calculateDestinyNumber($fields['birth_date']);
            }
            break;
        case 'Form2':
            if (isset($fields['full_name'])) {
                $fields['expression_number'] = $calculator->calculateExpressionNumber($fields['full_name']);
                $fields['motivation_number'] = $calculator->calculateMotivationNumber($fields['full_name']);
            }
            break;
        case 'Form3':
            if (isset($fields['full_name'])) {
                $fields['motivation_number'] = $calculator->calculateMotivationNumber($fields['full_name']);
            }
            break;
    }

    set_transient("form{$form_name}_submission_data", $fields, HOUR_IN_SECONDS);
}

// Função para obter os dados dos formulários
function forms_data($form) {
    // Verifique se o formulário é Form1, Form2 ou Form3
    if (in_array($form, ['Form1', 'Form2', 'Form3'])) {
        // Obtenha os dados do transient com base no nome do formulário
        $data = get_transient('form' . $form . '_submission_data');
        return $data;
    }

    return null;
}

function return_acf_introduction_options($form_name = 'Form1')
{
    $intros = ACFOptions::get_field('acf_intoducoes');
    $nums_destino = ACFOptions::get_field('acf_numeros_de_destino');
    $nums_expressao = ACFOptions::get_field('acf_numeros_de_expressao');
    $data = forms_data($form_name); // Use o nome do formulário passado como parâmetro
    $audio_files = [];
    $subtitles = [];

    if ($form_name === 'Form1') {
        foreach ($intros as $option) {
            $audio_files[] = $option['audio_de_introducao_'];
            $legenda_json = $option['legenda_de_introducao_'];

            // Correção do JSON: adicionar aspas duplas corretamente
            $legenda_json = preg_replace('/(\w+):/i', '"$1":', $legenda_json);
            $legenda = json_decode($legenda_json, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $subtitles[] = $legenda;
            } else {
                $subtitles[] = [];
            }
        }

        foreach ($nums_destino as $option) {
            if (isset($data['destiny_number']) && $data['destiny_number'] == $option['numero_destino_']) {
                $audio_files[] = $option['audio_destino_'];
                $legenda_json = $option['legenda_destino_'];

                // Correção do JSON: adicionar aspas duplas corretamente
                $legenda_json = preg_replace('/(\w+):/i', '"$1":', $legenda_json);
                $legenda = json_decode($legenda_json, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $subtitles[] = $legenda;
                } else {
                    $subtitles[] = [];
                }
            }
        }
    } else if ($form_name === 'Form2') {
        // Verifique o gênero e selecione o áudio e a legenda apropriados
        $gender = isset($data['gender']) ? $data['gender'] : ''; // Supondo que 'gender' está disponível nos dados do formulário
        $expression_number = isset($data['expression_number']) ? $data['expression_number'] : ''; // Supondo que 'expression_number' está disponível nos dados do formulário

        $audio_file = '';
        $legenda_json = '';

        foreach ($nums_expressao as $option) {
            if ($expression_number == $option['numero_expressao_'] && $option['genero_expressao_'] == $gender) {
                $audio_file = $option['audio_expressao_'];
                $legenda_json = $option['legenda_expressao_'];
                break;
            }
        }

        if ($audio_file) {
            $audio_files[] = $audio_file;

            // Correção do JSON: adicionar aspas duplas corretamente
            $legenda_json = preg_replace('/(\w+):/i', '"$1":', $legenda_json);
            $legenda = json_decode($legenda_json, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $subtitles[] = $legenda;
            } else {
                $subtitles[] = [];
            }
        }
    } else if ($form_name === 'Form3') {
        // Assumindo que existe apenas um áudio e uma legenda para Form3
        if (isset($data['audio'])) {
            $audio_files[] = $data['audio'];
            $legenda_json = $data['legenda'];

            // Correção do JSON: adicionar aspas duplas corretamente
            $legenda_json = preg_replace('/(\w+):/i', '"$1":', $legenda_json);
            $legenda = json_decode($legenda_json, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $subtitles[] = $legenda;
            } else {
                $subtitles[] = [];
            }
        }
    }

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

            function updateLegenda(index, currentTime) {
                const legendasParaAudio = subtitles[index];
                const legendaDiv = legendaDivs[index];
                let displayed = false;

                for (let i = 0; i < legendasParaAudio.length; i++) {
                    if (legendasParaAudio[i].time <= currentTime && (!legendasParaAudio[i + 1] || legendasParaAudio[i + 1].time > currentTime)) {
                        legendaDiv.innerText = legendasParaAudio[i].text;
                        legendaDiv.style.display = 'block';
                        displayed = true;
                        break;
                    }
                }

                if (!displayed) {
                    legendaDiv.style.display = 'none';
                }
            }

            audioPlayers.forEach((audio, index) => {
                audio.addEventListener('play', function() {
                    legendaDivs.forEach(div => div.style.display = 'none');
                });

                audio.addEventListener('timeupdate', function() {
                    updateLegenda(index, audio.currentTime);
                });

                audio.addEventListener('ended', function() {
                    legendaDivs[index].style.display = 'none';
                    const nextAudio = audioPlayers[index + 1];
                    if (nextAudio) {
                        audio.style.display = 'none';
                        nextAudio.style.display = 'block';
                        nextAudio.play();
                    }
                });
            });

            // Start playing the first audio automatically
            if (audioPlayers.length > 0) {
                audioPlayers[0].play();
            }
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

function return_acf_introduction_options_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'form' => 'Form1',
    ), $atts, 'return_players');

    ob_start();
    return_acf_introduction_options($atts['form']);
    return ob_get_clean();
}

add_shortcode('return_players', 'return_acf_introduction_options_shortcode');
