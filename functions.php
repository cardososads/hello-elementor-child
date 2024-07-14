<?php
function hello_elementor_child_enqueue_styles()
{
    wp_enqueue_style('hello-elementor-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('hello-elementor-child-style', get_stylesheet_directory_uri() . '/style.css', array('hello-elementor-style'));
}
add_action('wp_enqueue_scripts', 'hello-elementor_child_enqueue_styles');

require get_stylesheet_directory() . '/inc/class-acf-options.php';
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
            break;
        case 'Form3':
            // Qualquer lógica específica para o Form3
            break;
    }

    // Envia os dados para o JavaScript
?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formName = '<?= $form_name ?>';
            const formData = <?= json_encode($fields) ?>;
            localStorage.setItem(formName, JSON.stringify(formData));
        });
    </script>
<?php
}

// Função para obter os dados dos formulários do localStorage
function forms_data($form)
{
?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formData = JSON.parse(localStorage.getItem('<?= $form ?>')) || {};
            console.log(formData);
        });
    </script>
<?php
    return "<div id='form-data'></div>";
}

// Função para retornar opções de introdução do ACF
function return_acf_introduction_options($form_name = 'Form1')
{
    $intros = ACFOptions::get_field('acf_intoducoes');
    $nums_destino = ACFOptions::get_field('acf_numeros_de_destino');
    $nums_expressao = ACFOptions::get_field('acf_numeros_de_expressao');

?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formName = '<?= $form_name ?>';
            const data = JSON.parse(localStorage.getItem(formName)) || {};
            const intros = <?= json_encode($intros) ?>;
            const nums_destino = <?= json_encode($nums_destino) ?>;
            const nums_expressao = <?= json_encode($nums_expressao) ?>;
            let audio_files = [];
            let subtitles = [];

            if (formName === 'Form1') {
                intros.forEach(option => {
                    audio_files.push(option['audio_de_introducao_']);
                    let legenda_json = option['legenda_de_introducao_'];
                    legenda_json = legenda_json.replace(/(\w+):/g, '"$1":');
                    try {
                        let legenda = JSON.parse(legenda_json);
                        subtitles.push(legenda);
                    } catch (e) {
                        console.error('Erro ao parsear legenda JSON:', e);
                    }
                });
                nums_destino.forEach(option => {
                    if (data.destiny_number == option['numero_destino_']) {
                        audio_files.push(option['audio_destino_']);
                        let legenda_json = option['legenda_destino_'];
                        legenda_json = legenda_json.replace(/(\w+):/g, '"$1":');
                        try {
                            let legenda = JSON.parse(legenda_json);
                            subtitles.push(legenda);
                        } catch (e) {
                            console.error('Erro ao parsear legenda JSON:', e);
                        }
                    }
                });
            } else if (formName === 'Form2') {
                let gender = data.gender;
                let expression_number = data.expression_number;
                let audio_file = '';
                let legenda_json = '';

                nums_expressao.forEach(option => {
                    if (expression_number == option['numero_expressao_'] && option['genero_expressao_'] == gender) {
                        audio_file = option['audio_expressao_'];
                        legenda_json = option['legenda_expressao_'];
                    }
                });

                audio_files.push(audio_file);
                legenda_json = legenda_json.replace(/'/g, '"');
                try {
                    let legenda = JSON.parse(legenda_json);
                    subtitles.push(legenda);
                } catch (e) {
                    console.error('Erro ao parsear legenda JSON:', e);
                }
            } else if (formName === 'Form3') {
                audio_files.push(data['audio']);
                let legenda_json = data['legenda'];
                legenda_json = legenda_json.replace(/'/g, '"');
                try {
                    let legenda = JSON.parse(legenda_json);
                    subtitles.push(legenda);
                } catch (e) {
                    console.error('Erro ao parsear legenda JSON:', e);
                }
            }

            audio_files.forEach((audio_src, index) => {
                document.write(`<audio id="audio_player_${index}" src="${audio_src}" controls ${index > 0 ? 'style="display:none;"' : ''}></audio>`);
                document.write(`<div id="legenda_${index}" class="legenda" style="display: none;"></div>`);
            });

            const audioPlayers = document.querySelectorAll('audio');
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
