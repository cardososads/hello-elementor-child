<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class FormProcessor
{
    public static function process_forms()
    {
        if (isset($_POST['submit_form1'])) {
            // Coleta os dados do formulário
            $first_name = sanitize_text_field($_POST['first_name']);
            $birth_date = sanitize_text_field($_POST['birth_date']);

            // Calcula o número de destino
            $numerology_calculator = new NumerologyCalculator();
            $destiny_number = $numerology_calculator->calculateDestinyNumber($birth_date);

            // Constrói a query string com os dados
            $query_string = http_build_query([
                'first_name' => $first_name,
                'birth_date' => $birth_date,
                'destiny_number' => $destiny_number, // Adiciona o número de destino
            ]);

            // Redireciona para o próximo formulário com a query string
            wp_redirect(home_url('/form-02?' . $query_string));
            exit();
        } elseif (isset($_POST['submit_form2'])) {
            // Coleta os dados do formulário
            $gender = sanitize_text_field($_POST['gender']);
            $full_name = sanitize_text_field($_POST['full_name']);

            // Calcula o número de expressão
            $numerology_calculator = new NumerologyCalculator();
            $expression_number = $numerology_calculator->calculateExpressionNumber($full_name);

            // Constrói a query string com os dados
            $query_string = http_build_query([
                'first_name' => $_GET['first_name'] ?? '',
                'birth_date' => $_GET['birth_date'] ?? '',
                'destiny_number' => $_GET['destiny_number'] ?? '',
                'gender' => $gender,
                'full_name' => $full_name,
                'expression_number' => $expression_number, // Adiciona o número de expressão
            ]);
            wp_redirect(home_url('/form-03?' . $query_string));
            exit();
        } elseif (isset($_POST['submit_form3'])) {
            // Coleta os dados do formulário
            $email = sanitize_email($_POST['email']);
            $marital_status = sanitize_text_field($_POST['marital_status']);

            // Calcula o número de motivação
            $full_name = $_GET['full_name'] ?? '';
            $numerology_calculator = new NumerologyCalculator();
            $motivation_number = $numerology_calculator->calculateMotivationNumber($full_name);

            // Constrói a query string com os dados
            $query_string = http_build_query([
                'first_name' => $_GET['first_name'] ?? '',
                'birth_date' => $_GET['birth_date'] ?? '',
                'gender' => $_GET['gender'] ?? '',
                'full_name' => $full_name,
                'expression_number' => $_GET['expression_number'] ?? '',
                'email' => $email,
                'marital_status' => $marital_status,
                'motivation_number' => $motivation_number, // Adiciona o número de motivação
            ]);
            wp_redirect(home_url('/pagina-de-conversao?' . $query_string));
            exit();
        }
    }
}
