<?php

class AudioPlayerRenderer
{
    public static function render_audio_players($form_id)
    {
        $intros = ACFOptions::get_field('acf_intoducoes');
        $nums_destino = ACFOptions::get_field('acf_numeros_de_destino');
        $nums_expressao = ACFOptions::get_field('acf_numeros_de_expressao');
        $nums_motivacao = ACFOptions::get_field('acf_numeros_de_motivacao');

        $audio_files = [];
        $subtitles = [];

        // Função para limpar e converter legenda em JSON válido
        function prepare_subtitle($legenda_text)
        {
            $legenda_json = preg_replace('/(\w+):/i', '"$1":', $legenda_text);
            return json_decode($legenda_json, true);
        }

        // Renderização dos áudios e legendas baseado no formulário
        if ($form_id === 'form1') {
            foreach ($intros as $option) {
                $audio_files[] = $option['audio_de_introducao_'];
                $subtitles[] = prepare_subtitle($option['legenda_de_introducao_']);
            }

            foreach ($nums_destino as $option) {
                if ($_GET['destiny_number'] == $option['numero_destino_']) {
                    $audio_files[] = $option['audio_destino_'];
                    $subtitles[] = prepare_subtitle($option['legenda_destino_']);
                }
            }
        } elseif ($form_id === 'form2') {
            $gender = $_GET['gender'] ?? '';
            $expression_number = $_GET['expression_number'] ?? '';

            foreach ($nums_expressao as $option) {
                if ($expression_number == $option['numero_expressao_'] && $gender == $option['genero_expressao_']) {

                    $audio_files[] = $option['audio_expressao_'];
                    $subtitles[] = prepare_subtitle($option['legenda_expressao_']);
                    break; // Parar após encontrar a correspondência
                }
            }
        } elseif ($form_id === 'form3') {
            $motivation_number = $_GET['motivation_number'] ?? '';
            $relationship_status = $_GET['marital_status'] ?? '';
            $gender = $_GET['gender'] ?? '';
            foreach ($nums_motivacao as $option) {
                if ($motivation_number == $option['numero_motivacao_'] && $gender == $option['genero_motivacao_'] && $relationship_status == $option['estado_civil_motivacao_']) {
                    $audio_files[] = $option['audio_motivacao_'];
                    $subtitles[] = prepare_subtitle($option['legenda_motivacao_']);
                    break; // Parar após encontrar a correspondência
                }
            }
        }

        // Renderizar áudios e preparar para JavaScript
        foreach ($audio_files as $index => $audio_src) {
?>
            <div id="legenda_<?= $index ?>" class="legenda" style="display: none;"><?= json_encode($subtitles[$index]); ?></div>
            <audio id="audio_player_<?= $index ?>" src="<?= esc_url($audio_src) ?>" controls <?= $index > 0 ? 'style="display:none;"' : '' ?>></audio>
        <?php
        }
    }

    public static function render_audio_players_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'form' => 'form1',
        ), $atts, 'return_players');

        ob_start();
        self::render_audio_players($atts['form']);
        $output = ob_get_clean();

        echo $output;
        ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const audioPlayers = document.querySelectorAll("audio");
                const subtitles = Array.from(document.querySelectorAll(".legenda")).map(div => JSON.parse(div.textContent));

                let subtitleTimeout;

                function updateSubtitle(index, currentTime) {
                    const subtitleDiv = document.getElementById(`legenda_${index}`);
                    const subtitlesForAudio = subtitles[index];

                    if (!subtitlesForAudio) return;

                    for (let i = 0; i < subtitlesForAudio.length; i++) {
                        const subtitle = subtitlesForAudio[i];
                        if (currentTime >= subtitle.time && (i === subtitlesForAudio.length - 1 || currentTime < subtitlesForAudio[i + 1].time)) {
                            clearTimeout(subtitleTimeout);
                            subtitleDiv.innerText = subtitle.text;
                            subtitleDiv.style.display = "block";
                            if (i < subtitlesForAudio.length - 1) {
                                const nextSubtitleTime = subtitlesForAudio[i + 1].time - currentTime;
                                subtitleTimeout = setTimeout(() => {
                                    subtitleDiv.innerText = '';
                                }, nextSubtitleTime * 1000);
                            }
                            return;
                        }
                    }

                    subtitleDiv.innerText = '';
                }

                audioPlayers.forEach((audio, index) => {
                    audio.addEventListener("play", function() {
                        subtitles.forEach((sub, i) => {
                            if (i !== index) {
                                document.getElementById(`legenda_${i}`).innerText = '';
                            }
                        });
                    });

                    audio.addEventListener("timeupdate", function() {
                        updateSubtitle(index, audio.currentTime);
                    });

                    audio.addEventListener("ended", function() {
                        audio.style.display = "none";
                        document.getElementById(`legenda_${index}`).style.display = "none";
                        const nextAudio = audioPlayers[index + 1];
                        if (nextAudio) {
                            nextAudio.style.display = "block";
                            nextAudio.play();
                        }
                    });
                });

                // Iniciar automaticamente o primeiro áudio
                if (audioPlayers.length > 0) {
                    audioPlayers[0].style.display = "block";
                    audioPlayers[0].play();
                }
            });
        </script>
        <style>
            .legenda {
                margin-top: 10px;
                font-size: 14px;
                color: white;
                min-height: 100px;
                /* Ajustar conforme necessário para a altura mínima */
                display: flex;
                width: 100%;
                justify-content: center;
                position: absolute;
                top: -50px;
                /* Garante que a área esteja presente mesmo sem texto */
            }
        </style>
<?php
    }
}

add_shortcode('return_players', ['AudioPlayerRenderer', 'render_audio_players_shortcode']);
