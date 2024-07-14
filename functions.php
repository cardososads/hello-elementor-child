<?php
// Certifique-se de que não há espaços antes deste início de PHP

// 1. Enqueue Styles
function hello_elementor_child_enqueue_styles()
{
    wp_enqueue_style('hello-elementor-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('hello-elementor-child-style', get_stylesheet_directory_uri() . '/style.css', array('hello-elementor-style'));
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles');

// 2. Requisitos de Arquivos
require get_stylesheet_directory() . '/inc/class-acf-options.php';
require get_stylesheet_directory() . '/inc/class-form-data-retriever.php';
require get_stylesheet_directory() . '/inc/class-numerology-calculator.php';

// 3. Processamento de Submissão de Formulários
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
            $form1_data = isset($_POST['formForm1_submission_data']) ? json_decode(stripslashes($_POST['formForm1_submission_data']), true) : null;
            if ($form1_data) {
                $fields = array_merge($form1_data, $fields);
            }
            break;
        case 'Form3':
            $form1_data = isset($_POST['formForm1_submission_data']) ? json_decode(stripslashes($_POST['formForm1_submission_data']), true) : null;
            $form2_data = isset($_POST['formForm2_submission_data']) ? json_decode(stripslashes($_POST['formForm2_submission_data']), true) : null;
            if ($form1_data) {
                $fields = array_merge($form1_data, $fields);
            }
            if ($form2_data) {
                $fields = array_merge($form2_data, $fields);
            }
            break;
    }

    // Retorne os dados processados como resposta JSON
    wp_send_json_success($fields);
}

// 4. Função para Obter Dados dos Formulários
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

// 5. Função para Retornar Opções de Introdução
function return_acf_introduction_options($form_name = 'Form1')
{
    $intros = ACFOptions::get_field('acf_intoducoes');
    $nums_destino = ACFOptions::get_field('acf_numeros_de_destino');
    $nums_expressao = ACFOptions::get_field('acf_numeros_de_expressao');
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
    }

    foreach ($audio_files as $index => $audio_src) {
        echo '<audio id="audio_player_' . $index . '" src="' . $audio_src . '" controls ' . ($index > 0 ? 'style="display:none;"' : '') . '></audio>';
        echo '<div id="legenda_' . $index . '" class="legenda" ' . ($index > 0 ? 'style="display: none;"' : '') . '></div>';
    }

    echo '<script>localStorage.setItem("subtitles", ' . json_encode($subtitles) . ');</script>';
}

// 6. Shortcode para Retornar as Opções de Introdução
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
?>

<!-- 7. JavaScript para Processar o Formulário e Atualizar Legendas -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Função para enviar dados do formulário e processar a resposta
        async function handleFormSubmission(event) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor');
                }

                const result = await response.json();

                if (result.success) {
                    const formName = form.querySelector('input[name="form_name"]').value;
                    localStorage.setItem(`form${formName}_submission_data`, JSON.stringify(result.data));
                    alert('Formulário processado e dados armazenados no localStorage.');
                } else {
                    alert('Erro ao processar o formulário.');
                }
            } catch (error) {
                console.error('Erro ao processar o formulário:', error);
                alert('Erro ao processar o formulário.');
            }
        }

        // Adiciona evento de envio do formulário
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', handleFormSubmission);
        });

        // Função para atualizar legendas conforme o áudio toca
        const audioPlayers = document.querySelectorAll('audio');
        const legendaDivs = document.querySelectorAll('.legenda');

        function updateLegenda(index, currentTime) {
            const subtitles = JSON.parse(localStorage.getItem('subtitles'));
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