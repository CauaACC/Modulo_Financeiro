<?php
include($_SERVER['DOCUMENT_ROOT'].WB_PATH.'/lib/util/LeitorArquivo.php');
class carregarArquivoAC {
    
    public function carregarArquivoInscritosAptos($fws_) {

        $objArquivo = new LeitorArquivo();
        $objArquivo->setNmArquivo("/log/eventos/6/inscritosAptos/carregamento-inscritos-aptos-projeto-araucaria.csv"); //carregamento-inscritos-aptos-ao-transporte.csv
        $objArquivo->carregarArquivo(false);

        $listaLinhas = $objArquivo->getListaLinhas();
        $totalLinhas = $objArquivo->getTotalLinhas();
        
        $objArquivo->imprimirTituloColunas();
                
        $objInscApto = new InscInscritoApto();

        //Começar com contador inverso igual a 1 para não gravar a primeira linha onde deve constar o titulo das colunas
        $contadorInverso = 1;
        //Loop em cima das linhas retornadas do arquivo        
        while($totalLinhas > 0){
            $linha = $listaLinhas[$contadorInverso];
            $arrayColunas = split(';', $linha);

           //Loop em cima das colunas retornadas das linhas do arquivo
//            foreach ($arrayColunas as $key => $value) {
//                echo $value."-";
//            }
            try{
                       
                $objInscApto->setCodEvento('342'); //1
                $objInscApto->setCpf($arrayColunas[1]);
                $objInscApto->setNomeInscrito($arrayColunas[0]);
                $objInscApto->setEmail($arrayColunas[2]);
                $objInscApto->setCodFormaPagamento('444'); //1
                $objInscApto->salvar();
                
            }catch (OKException $e){
                $message .= "<br>linha:".$contadorInverso.":".$e->getMessage();
            }catch (Exception $e){
                $message .= "<br>linha:".$contadorInverso.":".$e->getMessage();
            }
            $objInscApto->limparObj();

            //$objInscApto = NULL;
                
            $totalLinhas--;
            $contadorInverso++;
        }
        $objInscApto = NULL;
        echo "Critica:".$message;
    }
    
    
    public function carregarArquivoInscritos($fws_) {

        $objArquivo = new LeitorArquivo();
        $objArquivo->setNmArquivo("/log/eventos/6/teste.csv"); //carregamento-inscritos-aptos-ao-transporte.csv
        $objArquivo->carregarArquivo(false);

        $listaLinhas = $objArquivo->getListaLinhas();
        $totalLinhas = $objArquivo->getTotalLinhas();
        
        $objArquivo->imprimirTituloColunas();
                
        $objInscApto = new InscInscritoApto();

        //Começar com contador inverso igual a 1 para não gravar a primeira linha onde deve constar o titulo das colunas
        $contadorInverso = 1;
        //Loop em cima das linhas retornadas do arquivo        
        while($totalLinhas > 0){
            $linha = $listaLinhas[$contadorInverso];
            $arrayColunas = split(';', $linha);

           //Loop em cima das colunas retornadas das linhas do arquivo
//            foreach ($arrayColunas as $key => $value) {
//                echo $value."-";
//            }
            try{
                       
                $objInscApto->setCodEvento('342'); //1
                $objInscApto->setCpf($arrayColunas[1]);
                $objInscApto->setNomeInscrito($arrayColunas[0]);
                $objInscApto->setEmail($arrayColunas[2]);
                $objInscApto->setCodFormaPagamento('444'); //1
                $objInscApto->salvar();
                
            }catch (OKException $e){
                $message .= "<br>linha:".$contadorInverso.":".$e->getMessage();
            }catch (Exception $e){
                $message .= "<br>linha:".$contadorInverso.":".$e->getMessage();
            }
            $objInscApto->limparObj();

            //$objInscApto = NULL;
                
            $totalLinhas--;
            $contadorInverso++;
        }
        $objInscApto = NULL;
        echo "Critica:".$message;
    }    
}
?>