<?php

class PadraoAC {

    /**
     * 
     * @param string $nomeArquivo
     * @param type $colunas
     * @param type $linhas
     * @param type $debug
     */
    public function gerarArquivoExcel($nomeArquivo,$colunas,$linhas, $debug = false) {

        $nomeArquivo = $nomeArquivo."-".date('d-m-Y')."-".date('H')."h".date('i'); 
        $file_ending = "xls";
        header("Content-Type: application/xls");
        header("Content-Disposition: attachment; filename=$nomeArquivo.xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        $sep = "\t";
        foreach ($colunas as $name) {
            print ($name . $sep);
        }
        foreach ($linhas as $objL) {
            $schema_insert = "";
            foreach ($colunas as $name) {
                $value = $objL->getPadrao($name);
                if (!isset($value))
                    $schema_insert .= "NULL" . $sep;
                elseif ($value != "")
                    $schema_insert .= "$value" . $sep;
                else
                    $schema_insert .= "" . $sep;
            }
            $schema_insert = str_replace($sep . "$", "", $schema_insert);
            $schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
            $schema_insert .= "\t";
            print(trim($schema_insert));
            print "\n";
        }
        print("\n");
    }

}

?>
