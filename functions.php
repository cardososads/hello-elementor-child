<?php

function hello_elementor_child_enqueue_styles()
{
    wp_enqueue_style('hello-elementor-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('hello-elementor-child-style', get_stylesheet_directory_uri() . '/style.css', array('hello-elementor-style'));
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles');

require get_stylesheet_directory() . '/inc/class-acf-options.php';
require get_stylesheet_directory() . '/inc/class-form-data-retriever.php';
require get_stylesheet_directory() . '/inc/class-numerology-calculator.php';

// Hook para processar o envio dos formulários
add_action('elementor_pro/forms/new_record', 'process_elementor_form_submission', 10, 2);

function process_elementor_form_submission($record, $handler)
{
    // Verifique qual formulário foi enviado
    $form_name = $record->get_form_settings('form_name');

    // Obtenha os dados do formulário
    $fields = array_map(function ($field) {
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
            $form1_data = get_transient('formForm1_submission_data');
            if ($form1_data) {
                $fields = array_merge($form1_data, $fields);
            }
            break;
        case 'Form3':
            $fields['motivation_number'] = $calculator->calculateMotivationNumber($fields['full_name']);
            $form1_data = get_transient('formForm1_submission_data');
            $form2_data = get_transient('formForm2_submission_data');
            if ($form1_data) {
                $fields = array_merge($form1_data, $fields);
            }
            if ($form2_data) {
                $fields = array_merge($form2_data, $fields);
            }
            break;
    }

    // Armazena os dados do formulário usando transients para acesso global
    set_transient("form{$form_name}_submission_data", $fields, HOUR_IN_SECONDS);
}

// Função para obter os dados dos formulários
function forms_data($form)
{
    // Verifique se o formulário é Form1, Form2 ou Form3
    if (in_array($form, ['Form1', 'Form2', 'Form3'])) {
        // Obtenha os dados do transient com base no nome do formulário
        $form1_data = get_transient('formForm1_submission_data');
        $form2_data = get_transient('formForm2_submission_data');
        $form_data = get_transient('form' . $form . '_submission_data');

        if ($form === 'Form2' && $form1_data) {
            $form_data = array_merge($form1_data, $form_data);
        }

        if ($form === 'Form3') {
            if ($form1_data) {
                $form_data = array_merge($form1_data, $form_data);
            }
            if ($form2_data) {
                $form_data = array_merge($form2_data, $form_data);
            }
        }

        return $form_data;
    }

    return null;
}

function return_acf_introduction_options($form_name = 'Form1')
{
    $intros = ACFOptions::get_field('acf_intoducoes');
    $nums_destino = ACFOptions::get_field('acf_numeros_de_destino');
    $nums_expressao = ACFOptions::get_field('acf_numeros_de_expressao');
    $nums_motivacao = ACFOptions::get_field('acf_numeros_de_motivacao');
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
            if ($data['destiny_number'] == $option['numero_destino_']) {
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
        $gender = $data['gender']; // Supondo que 'gender' está disponível nos dados do formulário
        $expression_number = $data['expression_number']; // Supondo que 'expression_number' está disponível nos dados do formulário

        foreach ($nums_expressao as $option) {
            if ($expression_number == $option['numero_expressao_'] && $option['genero_expressao_'] == $gender) {
                $audio_files[] = $option['audio_expressao_'];
                $legenda_json = $option['legenda_expressao_'];

                $legenda_json = preg_replace('/(\w+):/i', '"$1":', $legenda_json);
                $legenda = json_decode($legenda_json, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $subtitles[] = $legenda;
                } else {
                    $subtitles[] = [];
                }
            }
        }
    } else if ($form_name === 'Form3') {
        $calculator = new NumerologyCalculator();
        $motivation_number = $calculator->calculateMotivationNumber($data['full_name']); // Supondo que 'motivation_number' está disponível nos dados do formulário
        var_dump($data, $motivation_number);
        $relationship_status = $data['relationship_status']; // Supondo que 'relationship_status' está disponível nos dados do formulário
        foreach ($nums_motivacao as $option) {
            if ($motivation_number == $option['numero_motivacao_'] && $option['estado_civil_motivacao_'] == $relationship_status) {
                $audio_files[] = $option['audio_motivacao_'];
                $legenda_json = $option['legenda_motivacao_'];
                print_r($option);

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
                    audio.style.display = 'none';
                    legendaDivs[index].style.display = 'none';
                    const nextAudio = audioPlayers[index + 1];
                    if (nextAudio) {
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
