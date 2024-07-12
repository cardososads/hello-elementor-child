<?php

class NumerologyCalculator {

    // Função para calcular o número de destino
    public function calculateDestinyNumber($birthDate) {
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

    // Função para calcular o número de expressão
    public function calculateExpressionNumber($fullName) {
        $fullName = str_replace(' ', '', $fullName); // Remove espaços no nome completo
        $total = 0;

        // Itera sobre cada caractere do nome completo
        for ($i = 0; $i < strlen($fullName); $i++) {
            $total += $this->charToNumber($fullName[$i]);
        }

        // Reduz o valor total para um único dígito ou número mestre
        return $this->reduceToSingleDigitOrMasterNumber($total);
    }

    // Função para converter um caractere em número conforme a numerologia cabalística
    private function charToNumber($char) {
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

    // Soma os dígitos de um número
    private function sumDigits($number) {
        return array_sum(str_split($number));
    }

    // Reduz um número para um único dígito, exceto os números mestres 11, 22 e 33
    private function reduceToSingleDigitOrMasterNumber($number) {
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
