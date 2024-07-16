<?php

class FormRenderer
{
    public static function render_form1()
    {
        ob_start();
        $form_data = $_GET;
?>
        <form id="form1" method="post">
            <label for="first_name">Primeiro Nome:</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($form_data['first_name'] ?? ''); ?>" required>
            <label for="birth_date">Data de Nascimento:</label>
            <input type="date" id="birth_date" name="birth_date" value="<?php echo esc_attr($form_data['birth_date'] ?? ''); ?>" required>
            <input type="submit" name="submit_form1" value="Enviar">
        </form>
    <?php
        return ob_get_clean();
    }

    public static function render_form2()
    {
        ob_start();
        $form_data = $_GET;
        $gender_options = array(
            'Masculino' => 'Masculino',
            'Feminino' => 'Feminino',
            'Outro' => 'Outro'
        );
    ?>
        <form id="form2" method="post">
            <label for="gender">Gênero:</label>
            <select id="gender" name="gender" required>
                <?php foreach ($gender_options as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($form_data['gender'], $value); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="full_name">Nome Completo de Nascimento:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo esc_attr($form_data['full_name']); ?>" required>
            <input type="submit" name="submit_form2" value="Enviar">
        </form>
    <?php
        return ob_get_clean();
    }

    public static function render_form3()
    {
        ob_start();
        $form_data = $_GET;
        $marital_status_options = array(
            'Casado' => 'Casado',
            'Solteiro' => 'Solteiro',
            'Outro' => 'Outro'
        );
    ?>
        <form id="form3" method="post">
            <label for="email">Endereço de Email:</label>
            <input type="email" id="email" name="email" value="<?php echo esc_attr($form_data['email']); ?>" required>

            <label for="marital_status">Estado Civil:</label>
            <select id="marital_status" name="marital_status" required>
                <?php foreach ($marital_status_options as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($form_data['marital_status'], $value); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="submit" name="submit_form3" value="Enviar">
        </form>
<?php
        return ob_get_clean();
    }
}
