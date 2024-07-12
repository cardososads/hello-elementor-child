<?php

class NumerologyCalculator {

    /**
     * Calcula o número de destino a partir da data de nascimento.
     *
     * @param string $birthDate Data de nascimento no formato 'DD-MM-YYYY'
     * @return int Número de destino
     */
    public function calculateDestinyNumber(string $birthDate): int {
        // Verifica se a data está no formato esperado
        if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $birthDate)) {
            throw new InvalidArgumentException("Data de nascimento deve estar no formato 'DD-MM-YYYY'.");
        }

        // Supondo que a data de nascimento esteja no formato 'DD-MM-YYYY'
        $parts = explode('-', $birthDate);

        // Extrai dia, mês e ano
        $day = intval($parts[0]);
        $month = intval($parts[1]);
        $year = intval($parts[2]);

        // Reduz cada parte a um único dígito ou número mestre
        $day = $this->reduceToSingleDigitOrMasterNumber($this->sumDigits($day));
        $month = $this->reduceToSingleDigitOrMasterNumber($this->sumDigits($month));
        $year = $this->reduceToSingleDigitOrMasterNumber($this->sumDigits($year));

        // Soma as reduções e reduz a um único dígito ou número mestre
        $destinyNumber = $this->reduceToSingleDigitOrMasterNumber($day + $month + $year);

        return $destinyNumber;
    }

    /**
     * Calcula o número de expressão a partir do nome completo.
     *
     * @param string $fullName Nome completo
     * @return int Número de expressão
     */
    public function calculateExpressionNumber(string $fullName): int {
        // Remove espaços no nome completo
        $fullName = str_replace(' ', '', $fullName);
        $total = 0;

        // Itera sobre cada caractere do nome completo
        for ($i = 0; $i < strlen($fullName); $i++) {
            $total += $this->charToNumber($fullName[$i]);
        }

        // Reduz o valor total para um único dígito ou número mestre
        return $this->reduceToSingleDigitOrMasterNumber($total);
    }

    /**
     * Calcula o número de motivação a partir do nome completo.
     *
     * @param string $fullName Nome completo
     * @return int Número de motivação
     */
    public function calculateMotivationNumber(string $fullName): int {
        // Remove espaços no nome completo
        $fullName = str_replace(' ', '', $fullName);
        $total = 0;

        // Define as vogais
        $vowels = ['A', 'E', 'I', 'O', 'U'];

        // Itera sobre cada caractere do nome completo
        for ($i = 0; $i < strlen($fullName); $i++) {
            if (in_array(strtoupper($fullName[$i]), $vowels)) {
                $total += $this->charToNumber($fullName[$i]);
            }
        }

        // Reduz o valor total para um único dígito ou número mestre
        return $this->reduceToSingleDigitOrMasterNumber($total);
    }

    /**
     * Converte um caractere em número conforme a numerologia cabalística.
     *
     * @param string $char Caractere a ser convertido
     * @return int Número correspondente
     */
    private function charToNumber(string $char): int {
        $char = strtoupper($char);

        if (strpos('AJS', $char) !== false) {
            return 1;
        } elseif (strpos('BKT', $char) !== false) {
            return 2;
        } elseif (strpos('CLU', $char) !== false) {
            return 3;
        } elseif (strpos('DMV', $char) !== false) {
            return 4;
        } elseif (strpos('ENW', $char) !== false) {
            return 5;
        } elseif (strpos('FOX', $char) !== false) {
            return 6;
        } elseif (strpos('GPY', $char) !== false) {
            return 7;
        } elseif (strpos('HQZ', $char) !== false) {
            return 8;
        } elseif (strpos('IR', $char) !== false) {
            return 9;
        } else {
            return 0;
        }
    }

    /**
     * Soma os dígitos de um número.
     *
     * @param int $number Número a ter os dígitos somados
     * @return int Soma dos dígitos
     */
    private function sumDigits(int $number): int {
        return array_sum(str_split($number));
    }

    /**
     * Reduz um número para um único dígito, exceto os números mestres 11, 22 e 33.
     *
     * @param int $number Número a ser reduzido
     * @return int Número reduzido
     */
    private function reduceToSingleDigitOrMasterNumber(int $number): int {
        // Verifica se o número é um número mestre (11, 22, 33)
        if (in_array($number, [11, 22, 33])) {
            return $number; // Retorna o número mestre sem redução
        }

        // Reduz o número para um único dígito
        while ($number > 9) {
            $number = $this->sumDigits($number);
        }
        return $number;
    }
}
