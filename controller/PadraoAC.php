<?php

class PadraoAC {
    public function gerarArquivoExcel($nomeArquivo, $colunas, $linhas, $debug = false) {
        $nomeArquivo = $nomeArquivo . "-" . date('d-m-Y') . "-" . date('H') . "h" . date('i');
        header("Content-Disposition: attachment; filename=$nomeArquivo.xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        $sep = "\t";
        foreach ($colunas as $name) {
            print($name . $sep);
        }
        print "\n";
        foreach ($linhas as $objL) {
            $schema_insert = "";
            foreach ($colunas as $name) {
                $value = trim($objL->getPadrao($name));
                if (!isset($value)) {
                    $schema_insert .= "NULL" . $sep;
                } else if ($value != "") {
                    $schema_insert .= "$value" . $sep;
                } else {
                    $schema_insert .= "" . $sep;
                }
            }
            $schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
            $schema_insert .= "\t";
            print(trim($schema_insert));
            print "\n";
        }
        print("\n");
    }
}
