<?php
class carregarArquivo {
    
    public function carregarArquivoInscritosAptos($fws_) {

        $objArquivo = new LeitorArquivo();
        $objArquivo->setNmArquivo("/log/eventos/6/inscritosAptos/carregamento-inscritos-aptos-ao-transporte.csv");
        $objArquivo->carregarArquivo(false);

        $listaLinhas = $objArquivo->getListaLinhas();
        $totalLinhas = $objArquivo->getTotalLinhas();
        
        //Loop em cima das linhas retornadas do arquivo
        while($totalLinhas > 0){
            $linha = $listaLinhas[$totalLinhas];
            $arrayColuna = split(';', $linha);

            //Loop em cima das colunas retornadas das linhas do arquivo
            foreach ($array as $key => $value) {
                echo $value."-";
            }
            echo "<hr>";
            $totalLinhas--;
        }
    }
}
?>