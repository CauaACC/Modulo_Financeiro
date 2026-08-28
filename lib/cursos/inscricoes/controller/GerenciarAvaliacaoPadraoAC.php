<?php

class GerenciarAvaliacaoPadraoAC {
    
    /**
     * 
     * @param type $objL
     * @return $objL atributo $objL->getAvaliadorPremiacaoAtrasado()
     */
    public static function imprimirDiasEmAtraso($objL) {
        $data_inicio = new DateTime($objL->getDataTramitacao());
        $data_fim    = new DateTime(Date('Y-m-d H:i:s'));
        $dateInterval = $data_inicio->diff($data_fim);
        if ($dateInterval->days >= 7) {
            echo "<br><font color=red>".$dateInterval->days." dias de comunicado</font><br>";
//            $objL->setAva(1);
        }   
    }
    public static function descCriterio($vlrNotaCriterio){
        switch ($vlrNotaCriterio) {
            case 2:
                return "Atendeu Plenamente";
            break;
            case 1:
                return "Atendeu Parcialmente";
            break;
            case 0:
                return "Não Atendeu";
            break;
            default:
            break;
        }
    }
    public static function mostrarCriterios($objL){
        for ($i = 1;$i <= 6;$i++) {
            $getMetodo = 'getCriterio'.$i;
            $vlrNotaCriterio = $objL->$getMetodo();
            $vlrNotaTotal = $vlrNotaTotal + $vlrNotaCriterio;
            echo  "<br>".$i."°: ".GerenciarAvaliacaoPadraoAC::descCriterio($vlrNotaCriterio);
        }
        echo "<br><b>Soma das notas: ".$vlrNotaTotal."</b>";
    }
}
