<?php

function hello_elementor_child_enqueue_styles()
{
    // Enfileira o estilo principal do tema pai
    wp_enqueue_style('hello-elementor-style', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles');

function enqueue_jquery()
{
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'enqueue_jquery');

function start_session()
{
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'start_session', 1);

require get_stylesheet_directory() . '/inc/class-acf-options.php';
require get_stylesheet_directory() . '/inc/class-form-data-retriever.php';
require get_stylesheet_directory() . '/inc/class-numerology-calculator.php';

function render_form1()
{
    ob_start();
?>
    <form id="form1" method="post">
        <label for="first_name">Primeiro Nome:</label>
        <input type="text" id="first_name" name="first_name" required>
        <label for="birth_date">Data de Nascimento:</label>
        <input type="date" id="birth_date" name="birth_date" required>
        <input type="submit" name="submit_form1" value="Enviar">
    </form>
<?php
    return ob_get_clean();
}
add_shortcode('form1', 'render_form1');

function render_form2()
{
    ob_start();
?>
    <form id="form2" method="post">
        <label for="gender">Gênero:</label>
        <select id="gender" name="gender" required>
            <option value="male">Masculino</option>
            <option value="female">Feminino</option>
            <option value="other">Outro</option>
        </select>
        <label for="full_name">Nome Completo de Nascimento:</label>
        <input type="text" id="full_name" name="full_name" required>
        <input type="submit" name="submit_form2" value="Enviar">
    </form>
<?php
    return ob_get_clean();
}
add_shortcode('form2', 'render_form2');

function render_form3()
{
    ob_start();
?>
    <form id="form3" method="post">
        <label for="email">Endereço de Email:</label>
        <input type="email" id="email" name="email" required>
        <label for="marital_status">Estado Civil:</label>
        <select id="marital_status" name="marital_status" required>
            <option value="single">Solteiro(a)</option>
            <option value="married">Casado(a)</option>
            <option value="divorced">Divorciado(a)</option>
            <option value="widowed">Viúvo(a)</option>
        </select>
        <input type="submit" name="submit_form3" value="Enviar">
    </form>
<?php
    return ob_get_clean();
}
add_shortcode('form3', 'render_form3');

function process_forms()
{
    if (isset($_POST['submit_form1'])) {
        $first_name = sanitize_text_field($_POST['first_name']);
        $birth_date = sanitize_text_field($_POST['birth_date']);
        // Calcular o número de destino
        $calculator = new NumerologyCalculator();
        $destiny_number = $calculator->calculateDestinyNumber($birth_date);
        // Armazenar os dados na sessão
        session_start();
        $_SESSION['form1_data'] = [
            'first_name' => $first_name,
            'birth_date' => $birth_date,
            'destiny_number' => $destiny_number
        ];
        // Redirecionar para a próxima página do formulário
        wp_redirect(home_url('/form-02'));
        exit();
    }

    if (isset($_POST['submit_form2'])) {
        $gender = sanitize_text_field($_POST['gender']);
        $full_name = sanitize_text_field($_POST['full_name']);
        // Calcular o número de expressão
        $calculator = new NumerologyCalculator();
        $expression_number = $calculator->calculateExpressionNumber($full_name);
        // Armazenar os dados na sessão
        session_start();
        $_SESSION['form2_data'] = [
            'gender' => $gender,
            'full_name' => $full_name,
            'expression_number' => $expression_number
        ];
        // Redirecionar para a próxima página do formulário
        wp_redirect(home_url('/form-03'));
        exit();
    }

    if (isset($_POST['submit_form3'])) {
        $email = sanitize_email($_POST['email']);
        $marital_status = sanitize_text_field($_POST['marital_status']);
        // Calcular o número de motivação
        $calculator = new NumerologyCalculator();
        $motivation_number = $calculator->calculateMotivationNumber($_SESSION['form2_data']['full_name']);
        // Armazenar os dados na sessão
        session_start();
        $_SESSION['form3_data'] = [
            'email' => $email,
            'marital_status' => $marital_status,
            'motivation_number' => $motivation_number
        ];
        // Redirecionar para a página final ou de resultados
        wp_redirect(home_url('/pagina-de-conversao'));
        exit();
    }
}
add_action('init', 'process_forms');

function script_form()
{
?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log('Script Form Loaded');
            var painelExecucao = $('.painel_execucao');
            var secForm = $('.sec_form');
            var playersContainer = $('.players');
            var numeroDestinoStyle = $('#numero_destino_style');
            var sol = $('#sol');
            var players = playersContainer ? playersContainer.find('audio') : [];
            var playersFinished = 0;

            // Oculta sec_form inicialmente
            if (secForm) {
                secForm.hide();
            }
            // Oculta numero_destino_style inicialmente
            if (numeroDestinoStyle) {
                numeroDestinoStyle.hide().css('width', '0');
            }

            // Exibe painel_execucao com fade-in no carregamento da página
            if (painelExecucao) {
                setTimeout(function() {
                    painelExecucao.fadeIn(1000);
                }, 2000); // 2 segundos de atraso
            }

            // Função para verificar se todos os players terminaram
            function checkPlayers() {
                if (playersFinished === players.length) {
                    // Todos os players terminaram
                    if (painelExecucao && secForm) {
                        setTimeout(function() {
                            painelExecucao.fadeOut(1000, function() {
                                setTimeout(function() {
                                    secForm.fadeIn(1000);
                                }, 1000); // 1 segundo de atraso após fade-out
                            });
                        }, 1000); // 1 segundo de atraso antes do fade-out
                    }
                }
            }

            // Adiciona eventos de término e início aos players
            players.each(function(index, player) {
                $(player).on('ended', function() {
                    playersFinished++;
                    checkPlayers();
                });
                if (index === players.length - 1) {
                    $(player).on('play', function() {
                        if (numeroDestinoStyle) {
                            numeroDestinoStyle.show();
                            // Adiciona um pequeno atraso antes de adicionar a classe para garantir que a transição seja aplicada
                            setTimeout(function() {
                                console.log('Adicionando classe show ao numeroDestinoStyle');
                                numeroDestinoStyle.addClass('show');
                            }, 50); // Atraso de 50ms
                        }
                        if (sol) {
                            // Adiciona um pequeno atraso antes de adicionar a classe para garantir que a transição seja aplicada
                            setTimeout(function() {
                                console.log('Adicionando classe show ao sol');
                                sol.addClass('show');
                            }, 50); // Atraso de 50ms
                        }
                    });
                }
            });
        });
    </script>
<?php
}
add_action('wp_footer', 'script_form');

function return_acf_introduction_options($form_name = 'Form1')
{
    $intros = ACFOptions::get_field('acf_intoducoes');
    $nums_destino = ACFOptions::get_field('acf_numeros_de_destino');
    $nums_expressao = ACFOptions::get_field('acf_numeros_de_expressao');
    $nums_motivacao = ACFOptions::get_field('acf_numeros_de_motivacao');
?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log('Introduction Options Script Loaded');
            var form1_data = JSON.parse(localStorage.getItem('Form1_data') || '{}');
            var form2_data = JSON.parse(localStorage.getItem('Form2_data') || '{}');
            var form3_data = JSON.parse(localStorage.getItem('Form3_data') || '{}');
            var form_data = {};

            switch ('<?= $form_name ?>') {
                case 'Form1':
                    form_data = form1_data;
                    break;
                case 'Form2':
                    form_data = $.extend({}, form1_data, form2_data);
                    break;
                case 'Form3':
                    form_data = $.extend({}, form1_data, form2_data, form3_data);
                    break;
            }

            localStorage.setItem('<?= $form_name ?>_final_data', JSON.stringify(form_data));
            console.log('Form Data:', form_data);

            var audio_files = [];
            var subtitles = [];

            <?php if ($form_name === 'Form1') : ?>
                <?php foreach ($intros as $option) : ?>
                    audio_files.push('<?= $option['audio_de_introducao_'] ?>');
                    var legenda_json = '<?= addslashes($option['legenda_de_introducao_']) ?>';
                    legenda_json = legenda_json.replace(/(\w+):/g, '"$1":');
                    var legenda = JSON.parse(legenda_json);

                    if (legenda) {
                        subtitles.push(legenda);
                    } else {
                        subtitles.push([]);
                    }
                <?php endforeach; ?>
                <?php foreach ($nums_destino as $option) : ?>
                    if (form_data['destiny_number'] == <?= $option['numero_destino_'] ?>) {
                        audio_files.push('<?= $option['audio_destino_'] ?>');
                        var legenda_json = '<?= addslashes($option['legenda_destino_']) ?>';
                        legenda_json = legenda_json.replace(/(\w+):/g, '"$1":');
                        var legenda = JSON.parse(legenda_json);

                        if (legenda) {
                            subtitles.push(legenda);
                        } else {
                            subtitles.push([]);
                        }
                    }
                <?php endforeach; ?>
            <?php elseif ($form_name === 'Form2') : ?>
                var gender = form_data['gender'];
                var expression_number = form_data['expression_number'];
                <?php foreach ($nums_expressao as $option) : ?>
                    if (expression_number == <?= $option['numero_expressao_'] ?> && gender == '<?= $option['genero_expressao_'] ?>') {
                        audio_files.push('<?= $option['audio_expressao_'] ?>');
                        var legenda_json = '<?= addslashes($option['legenda_expressao_']) ?>';
                        legenda_json = legenda_json.replace(/(\w+):/g, '"$1":');
                        var legenda = JSON.parse(legenda_json);

                        if (legenda) {
                            subtitles.push(legenda);
                        } else {
                            subtitles.push([]);
                        }
                    }
                <?php endforeach; ?>
            <?php elseif ($form_name === 'Form3') : ?>
                var motivation_number = form_data['motivation_number'];
                var relationship_status = form_data['marital_status'];
                <?php foreach ($nums_motivacao as $option) : ?>
                    if (motivation_number == <?= $option['numero_motivacao_'] ?> && relationship_status == '<?= $option['estado_civil_motivacao_'] ?>') {
                        audio_files.push('<?= $option['audio_motivacao_'] ?>');
                        var legenda_json = '<?= addslashes($option['legenda_motivacao_']) ?>';
                        legenda_json = legenda_json.replace(/(\w+):/g, '"$1":');
                        var legenda = JSON.parse(legenda_json);

                        if (legenda) {
                            subtitles.push(legenda);
                        } else {
                            subtitles.push([]);
                        }
                    }
                <?php endforeach; ?>
            <?php endif; ?>

            // Renderizar os players de áudio e legendas
            var audioContainer = $('#audio_container');
            for (var i = 0; i < audio_files.length; i++) {
                var audioPlayer = $('<audio>', {
                    id: 'audio_player_' + i,
                    src: audio_files[i],
                    controls: true,
                    style: i > 0 ? 'display:none;' : ''
                });
                var legendaDiv = $('<div>', {
                    id: 'legenda_' + i,
                    class: 'legenda',
                    style: 'display: none;'
                });
                audioContainer.append(audioPlayer).append(legendaDiv);
            }

            // Atualizar as legendas durante a reprodução do áudio
            $('audio').on('timeupdate', function() {
                var index = $(this).attr('id').split('_')[2];
                var currentTime = this.currentTime;
                var legendasParaAudio = subtitles[index];
                var legendaDiv = $('#legenda_' + index);
                var displayed = false;

                for (var j = 0; j < legendasParaAudio.length; j++) {
                    if (legendasParaAudio[j].time <= currentTime && (!legendasParaAudio[j + 1] || legendasParaAudio[j + 1].time > currentTime)) {
                        legendaDiv.text(legendasParaAudio[j].text).show();
                        displayed = true;
                        break;
                    }
                }

                if (!displayed) {
                    legendaDiv.hide();
                }
            });

            // Iniciar o próximo áudio ao término do atual
            $('audio').on('ended', function() {
                var index = $(this).attr('id').split('_')[2];
                $(this).hide();
                $('#legenda_' + index).hide();
                var nextAudio = $('#audio_player_' + (parseInt(index) + 1));
                if (nextAudio.length) {
                    nextAudio.show().trigger('play');
                }
            });

            // Iniciar o primeiro áudio automaticamente
            if ($('audio').length > 0) {
                $('#audio_player_0').trigger('play');
            }
        });
    </script>
    <div id="audio_container"></div>
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

function get_destiny_number()
{
?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log('Destiny Number Script Loaded');
            var form1_data = JSON.parse(localStorage.getItem('Form1_final_data') || '{}');
            var destiny_number = form1_data['destiny_number'] || 'No destiny number found';
            $('.num_destino').text(destiny_number);
        });
    </script>
    <p class="num_destino"></p>
<?php
}

add_shortcode('destiny_number', 'get_destiny_number');

function render_results()
{
    session_start();
    $form1_data = isset($_SESSION['form1_data']) ? $_SESSION['form1_data'] : [];
    $form2_data = isset($_SESSION['form2_data']) ? $_SESSION['form2_data'] : [];
    $form3_data = isset($_SESSION['form3_data']) ? $_SESSION['form3_data'] : [];

    ob_start();
?>
    <h2>Resultados</h2>
    <p><strong>Primeiro Nome:</strong> <?php echo esc_html($form1_data['first_name']); ?></p>
    <p><strong>Data de Nascimento:</strong> <?php echo esc_html($form1_data['birth_date']); ?></p>
    <p><strong>Número de Destino:</strong> <?php echo esc_html($form1_data['destiny_number']); ?></p>
    <p><strong>Gênero:</strong> <?php echo esc_html($form2_data['gender']); ?></p>
    <p><strong>Nome Completo de Nascimento:</strong> <?php echo esc_html($form2_data['full_name']); ?></p>
    <p><strong>Número de Expressão:</strong> <?php echo esc_html($form2_data['expression_number']); ?></p>
    <p><strong>Email:</strong> <?php echo esc_html($form3_data['email']); ?></p>
    <p><strong>Estado Civil:</strong> <?php echo esc_html($form3_data['marital_status']); ?></p>
    <p><strong>Número de Motivação:</strong> <?php echo esc_html($form3_data['motivation_number']); ?></p>
<?php
    return ob_get_clean();
}
add_shortcode('resultados', 'render_results');

