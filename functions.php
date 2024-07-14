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

// Inicializa a sessão
function start_session()
{
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'start_session', 1);

// Limpa os dados dos formulários posteriores
function clear_form1_data()
{
    unset($_SESSION['form1_data']);
    unset($_SESSION['form2_data']);
    unset($_SESSION['form3_data']);
}

// Hook para processar o envio dos formulários
add_action('elementor_pro/forms/new_record', 'process_elementor_form_submission', 10, 2);

function process_elementor_form_submission($record, $handler)
{
    // Verifique qual formulário foi enviado
    $form_name = $record->get_form_settings('form_name');

    // Se o formulário 1 for enviado, limpe os dados dos formulários anteriores
    if ($form_name === 'Form1') {
        clear_form1_data();
    }

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
            $_SESSION['form1_data'] = $fields;
            break;
        case 'Form2':
            $fields['expression_number'] = $calculator->calculateExpressionNumber($fields['full_name']);
            if (isset($_SESSION['form1_data'])) {
                $fields = array_merge($_SESSION['form1_data'], $fields);
            }
            $_SESSION['form2_data'] = $fields;
            break;
        case 'Form3':
            if (isset($_SESSION['form1_data'])) {
                $fields = array_merge($_SESSION['form1_data'], $fields);
            }
            if (isset($_SESSION['form2_data'])) {
                $fields = array_merge($_SESSION['form2_data'], $fields);
            }
            $_SESSION['form3_data'] = $fields;
            break;
    }

    // Atualiza os dados da sessão
    $_SESSION[strtolower($form_name) . '_data'] = $fields;
}

// Função para obter os dados dos formulários
function forms_data($form)
{
    if (in_array($form, ['Form1', 'Form2', 'Form3'])) {
        $data = isset($_SESSION[strtolower($form) . '_data']) ? $_SESSION[strtolower($form) . '_data'] : null;
        if ($form === 'Form2' && isset($_SESSION['form1_data'])) {
            $data = array_merge($_SESSION['form1_data'], $data);
        }
        if ($form === 'Form3') {
            if (isset($_SESSION['form1_data'])) {
                $data = array_merge($_SESSION['form1_data'], $data);
            }
            if (isset($_SESSION['form2_data'])) {
                $data = array_merge($_SESSION['form2_data'], $data);
            }
        }
        return $data;
    }
    return null;
}

function return_acf_introduction_options($form_name = 'Form1')
{
    $intros = ACFOptions::get_field('acf_intoducoes');
    $nums_destino = ACFOptions::get_field('acf_numeros_de_destino');
    $nums_expressao = ACFOptions::get_field('acf_numeros_de_expressao');
    $data = forms_data($form_name);
    $audio_files = [];
    $subtitles = [];

    if ($form_name === 'Form1') {
        foreach ($intros as $option) {
            $audio_files[] = $option['audio_de_introducao_'];
            $legenda_json = $option['legenda_de_introducao_'];
            echo '<pre>';
            var_dump($data);
            echo '</pre>';
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
        $gender = $data['gender'];
        $expression_number = $data['expression_number'];
        echo '<pre>';
        var_dump($data);
        echo '</pre>';

        $audio_file = '';
        $legenda_json = '';

        foreach ($nums_expressao as $option) {
            if ($expression_number == $option['numero_expressao_'] && $option['genero_expressao_'] == $gender) {
                $audio_file = $option['audio_expressao_'];
                $legenda_json = $option['legenda_expressao_'];
                break;
            }
        }

        $audio_files[] = $audio_file;

        // Correção do JSON: adicionar aspas duplas corretamente
        $legenda_json = str_replace("'", '"', $legenda_json);
        $legenda = json_decode($legenda_json, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $subtitles[] = $legenda;
        } else {
            $subtitles[] = [];
        }
    } else if ($form_name === 'Form3') {
        $audio_files[] = $data['audio'];
        $legenda_json = $data['legenda'];
        echo '<pre>';
        var_dump($data);
        echo '</pre>';

        // Correção do JSON: adicionar aspas duplas corretamente
        $legenda_json = str_replace("'", '"', $legenda_json);
        $legenda = json_decode($legenda_json, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $subtitles[] = $legenda;
        } else {
            $subtitles[] = [];
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
