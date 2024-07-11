<?php
function hello_elementor_child_enqueue_styles() {
    wp_enqueue_style('hello-elementor-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('hello-elementor-child-style', get_stylesheet_directory_uri() . '/style.css', array('hello-elementor-style'));
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles');

require get_stylesheet_directory() . '/inc/class-acf-options.php';

function return_acf_options()
{
    $options = ACFOptions::get_field('acf_intoducoes');

    foreach ($options as $option) {
        ?>
        <audio src="<?= $option['audio_de_introducao_'] ?>" controls>
        <?php
    }
}

add_shortcode('return_options', 'return_acf_options');
