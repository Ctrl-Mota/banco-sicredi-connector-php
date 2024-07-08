<?php

namespace crtlmota\BancoSicrediConnector;


class Utils {
    static public function prettyVarDump($var) {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
    
        // Adicionar cores e formatação
        $output = preg_replace(
            ['/(\s*\[\d+\]\=\>\s*\n\s*)/', '/\]\=\>\s*/', '/array\(\d+\)/', '/\n/'],
            ["[\n    ", " => ", "Array", "\n"],
            $output
        );
    
        // Enviar a saída formatada para o terminal
        echo "\033[1;32m" . $output . "\033[0m"; // Verde para texto
    }
    
      /**
     * Assert if value complies with size restriction
     *
     * @param  string $value
     * @param  int    $size
     * @param  bool   $exact
     * @return string | false
     */
    public static function assertSize(string $fieldName, string $value, int $size, bool $exact = false)
    {
        if ($exact && mb_strlen($value, "8bit") != $size) {
            return sprintf("%s '%s' deveria ter %s %s carácter(es)",$fieldName, $value, 'exatamente', $size);
        } elseif (!$exact && mb_strlen($value, "8bit") > $size) {
            return sprintf("%s '%s' deveria ter %s %s carácter(es)",$fieldName, $value, 'no máximo', $size);
        }
        return false;
    }
}