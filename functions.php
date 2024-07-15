<?php

function hello_elementor_child_enqueue_styles()
{
    // Enfileira o estilo principal do tema pai
    wp_enqueue_style('hello-elementor-style', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles');

function start_session()
{
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'start_session', 1);

function script_form()
{
?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
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

require get_stylesheet_directory() . '/inc/class-acf-options.php';
require get_stylesheet_directory() . '/inc/class-form-data-retriever.php';
require get_stylesheet_directory() . '/inc/class-numerology-calculator.php';

add_action('elementor_pro/forms/new_record', 'process_elementor_form_submission', 10, 2);

function process_elementor_form_submission($record, $handler)
{
    $form_name = $record->get_form_settings('form_name');
    $fields = array_map(function ($field) {
        return $field['value'];
    }, $record->get('fields'));
    $calculator = new NumerologyCalculator();

    switch ($form_name) {
        case 'Form1':
            $fields['destiny_number'] = $calculator->calculateDestinyNumber($fields['birth_date']);
            break;
        case 'Form2':
            $fields['expression_number'] = $calculator->calculateExpressionNumber($fields['full_name']);
            break;
        case 'Form3':
            $fields['motivation_number'] = $calculator->calculateMotivationNumber($fields['full_name']);
            break;
    }

    // Passe os dados do formulário para o JavaScript
    echo '<script type="text/javascript">
        var formData = ' . json_encode($fields) . ';
        localStorage.setItem("' . $form_name . '_data", JSON.stringify(formData));
    </script>';
}

function return_acf_introduction_options($form_name = 'Form1')
{
    $intros = ACFOptions::get_field('acf_intoducoes');
    $nums_destino = ACFOptions::get_field('acf_numeros_de_destino');
    $nums_expressao = ACFOptions::get_field('acf_numeros_de_expressao');
    $nums_motivacao = ACFOptions::get_field('acf_numeros_de_motivacao');
?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
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
            var form1_data = JSON.parse(localStorage.getItem('Form1_final_data') || '{}');
            var destiny_number = form1_data['destiny_number'] || 'No destiny number found';
            $('.num_destino').text(destiny_number);
        });
    </script>
    <p class="num_destino"></p>
<?php
}

add_shortcode('destiny_number', 'get_destiny_number');
