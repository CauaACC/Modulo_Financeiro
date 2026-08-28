<?php

Function FWA_Select_Oficina($COD_OFICINA, $titulo_area_tematica, $cod_evento) {
    $debug = false;

    $OPTION_Oficina = "<option value='-1'>Selecione Atividade</option>";

    try {
        $objOf = new Oficina();
        $objOf->setCodEvento(COD_EVENTO);
        if (in_array(COD_EVENTO, array(35,52,72,79,90,105,119))) {
            $observacao = "";
            $orderBy = ' data_inicio, ordem, cod_oficina, titulo ';
        } else {
            $observacao = "(ordem de mostragem - titulo da oficina)<br>";
            $orderBy = ' ordem, cod_oficina, titulo ';
        }
        $objOf->carregarListaOficina($orderBy,$debug);
        if ($objOf->getListaOficina()) {
            $FWA_Select_Oficina .= $observacao."<select name='COD_OFICINA'>{$OPTION_Oficina}";
            foreach ($objOf->getListaOficina() as $objL) {
                #Abreviar titulos com nº de caracteres maiores q 50
                if ($objL->getCodEvento() == 35 or $objL->getCodEvento() == 52) {
                    $TITULO = formatDateHoraTrans($objL->getDataInicio(), $formatIn='YYYYMM-DD', $formatOut='DD/MM/YYYY').' - '.$objL->titulo;
                } else {
                    $NOME_OFICINA = substr($objL->getTitulo(), 0, 90);
                    If (strlen($NOME_OFICINA) == 90) {
                        $NOME_OFICINA = $NOME_OFICINA . "...";
                    }

                    $COD_OFICINA_Z = FWA_Zero_Esquerda($objL->getCodOficina(), 2);
                    $ORDEM_Z = FWA_Zero_Esquerda($objL->getOrdem(), 2);

                    $TITULO = $TITULO_CERTIFICADO;
                    if ($TITULO_CERTIFICADO == '') {
                        $TITULO = $NOME_OFICINA;
                    }
                    if($objL->getNumeroOficial()!=''){
                        $TITULO = $objL->getNumeroOficial() . " - ".$TITULO;
                        $COD_OFICINA_Z = '';
                    }
                    if($objL->getTipo()!=''){
                        $TITULO = $objL->getTipo() . " - ".$TITULO;
                    }
                    if($COD_OFICINA_Z!=''){
                        $TITULO = $COD_OFICINA_Z. " - ".$TITULO;
                    }
                }
                $FWA_Select_Oficina .= "<option value='{$objL->getCodOficina()}' "
                . verifItemSelected($objL->getCodOficina(), $COD_OFICINA)
                . ">{$TITULO}</option>";
            }
            $FWA_Select_Oficina .= "</select>";
        }
    } catch (Exception $exc) {
        $FWA_Select_Oficina .= "<br>" . $exc->getMessage();
    }

    return $FWA_Select_Oficina;
}
