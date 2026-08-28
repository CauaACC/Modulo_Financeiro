<?php

class ReferenciaTemplateHelper {

    private static $meses = [
        1  => 'Janeiro',
        2  => 'Fevereiro',
        3  => 'Março',
        4  => 'Abril',
        5  => 'Maio',
        6  => 'Junho',
        7  => 'Julho',
        8  => 'Agosto',
        9  => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro',
    ];

    public static function substituirTags($template, $datBase) {
        if ($template === '' || $template === null) {
            return '';
        }
        if (empty($datBase)) {
            return $template;
        }
        $ts = is_numeric($datBase) ? (int) $datBase : strtotime($datBase);
        if ($ts === false) {
            return $template;
        }
        $mesNum    = (int) date('n', $ts);
        $mesNome   = isset(self::$meses[$mesNum]) ? self::$meses[$mesNum] : '';
        $mesNumPad = str_pad($mesNum, 2, '0', STR_PAD_LEFT);
        $ano       = date('Y', $ts);
        $ano2      = date('y', $ts);

        $r = $template;
        $r = str_replace('{MES}',     $mesNome,   $r);
        $r = str_replace('{MES_NUM}', $mesNumPad, $r);
        $r = str_replace('{ANO}',     $ano,       $r);
        $r = str_replace('{ANO2}',    $ano2,      $r);
        return $r;
    }

    public static function padraoDefault() {
        return '{MES}/{ANO}';
    }
}
