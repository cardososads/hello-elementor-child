<?php

// Inclui todas as classes necessárias
require get_stylesheet_directory() . '/inc/class-acf-options.php';
require get_stylesheet_directory() . '/inc/class-form-data-retriever.php';
require get_stylesheet_directory() . '/inc/class-numerology-calculator.php';
require get_stylesheet_directory() . '/inc/class-form-renderer.php';
require get_stylesheet_directory() . '/inc/class-form-processor.php';
require get_stylesheet_directory() . '/inc/class-audio-player-renderer.php';

// Enfileira o estilo principal do tema pai
function hello_elementor_child_enqueue_styles()
{
    wp_enqueue_style('hello-elementor-style', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles');

// Enfileira o jQuery
function enqueue_jquery()
{
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'enqueue_jquery');

add_action('init', ['FormProcessor', 'process_forms']);

// Shortcodes para os formulários
add_shortcode('form1', ['FormRenderer', 'render_form1']);
add_shortcode('form2', ['FormRenderer', 'render_form2']);
add_shortcode('form3', ['FormRenderer', 'render_form3']);

function custom_js_to_control_objects()
{
?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var painelExecucao = document.querySelector('.painel_execucao');
            var secForm = document.querySelector('.sec_form');
            var playersContainer = document.querySelector('.players');
            var numeroDestinoStyle = document.getElementById('numero_destino_style');
            var sol = document.getElementById('sol');
            var players = playersContainer ? playersContainer.querySelectorAll('audio') : [];
            var playersFinished = 0;

            // Oculta sec_form inicialmente
            if (secForm) {
                secForm.style.display = 'none';
            }
            // Oculta numero_destino_style inicialmente
            if (numeroDestinoStyle) {
                numeroDestinoStyle.style.display = 'none';
                numeroDestinoStyle.style.width = '0';
            }

            // Exibe painel_execucao com fade-in no carregamento da página
            if (painelExecucao) {
                setTimeout(function() {
                    jQuery(painelExecucao).fadeIn(1000);
                }, 2000); // 2 segundos de atraso
            }

            // Função para verificar se todos os players terminaram
            function checkPlayers() {
                if (playersFinished === players.length) {
                    // Todos os players terminaram
                    if (painelExecucao && secForm) {
                        setTimeout(function() {
                            jQuery(painelExecucao).fadeOut(1000, function() {
                                setTimeout(function() {
                                    jQuery(secForm).fadeIn(1000);
                                }, 1000); // 1 segundo de atraso após fade-out
                            });
                        }, 1000); // 1 segundo de atraso antes do fade-out
                    }
                }
            }

            // Adiciona eventos de término e início aos players
            players.forEach(function(player, index) {
                player.addEventListener('ended', function() {
                    playersFinished++;
                    checkPlayers();
                });
                if (index === players.length - 1) {
                    player.addEventListener('play', function() {
                        if (numeroDestinoStyle) {
                            numeroDestinoStyle.style.display = 'block';
                            // Adiciona um pequeno atraso antes de adicionar a classe para garantir que a transição seja aplicada
                            setTimeout(function() {
                                console.log('Adicionando classe show ao numeroDestinoStyle');
                                numeroDestinoStyle.classList.add('show');
                            }, 1000); // Atraso de 50ms
                        }
                        if (sol) {
                            // Adiciona um pequeno atraso antes de adicionar a classe para garantir que a transição seja aplicada
                            setTimeout(function() {
                                console.log('Adicionando classe show ao sol');
                                sol.classList.add('show');
                            }, 1000); // Atraso de 50ms
                        }
                    });
                }
            });
        });
    </script>
    <style>
        audio {
            width: 80%;
            height: 50px;
            margin: 0 auto;
            display: block;
        }

        .legenda {
            margin-top: 10px;
            font-size: 1.3em;
            /* Tamanho de fonte equivalente a h2 */
            color: white;
            text-align: center;
        }

        .num_dest {
            color: white;
        }

        #numero_destino_style {
            display: none;
            /* Escondido inicialmente */
            width: 0;
            transition: width 1s ease-in-out;
            /* Transição suave para a largura */
        }

        #numero_destino_style.show {
            display: flex!important;
            width: 40% !important;
        }

        #sol {
            width: 50% !important;
            /* Largura inicial maior */
            transition: width 1s ease-in-out;
            /* Transição suave para a largura */
        }

        #sol.show {
            width: 15% !important;
        }
    </style>
<?php
}
add_action('wp_footer', 'custom_js_to_control_objects');

function return_destiny_number()
{
    $destinumber = $_GET['destiny_number'];
    echo '<p class="num_destino">' . $destinumber . '</p>';
}

add_shortcode('destiny_number', 'return_destiny_number');
