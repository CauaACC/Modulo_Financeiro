<div class='row' style='margin-bottom:5px;'>
    <div class='col-sm-12' style='size:12px;padding:10px;background-color: #FAFAFA;'>
        3º DIA - 26 de Julho de 2024 (Sexta-Feira)
        <br>Período da Manhã - 09h00 - 12h00
    </div>
</div>                  
<div class="row" style="margin-bottom: 5px">
    <div class="col-sm-12" style="size: 12px; padding: 10px; background-color: #fafafa">
      Bloco de Apresentação de Trabalhos Indicados ao Prêmio
    </div>
  </div>
  <div class="row">
    <div class="col-sm-12">
    <?php
    $objAreaTematica = new AreaTematica();
    $objAreaTematica->carregarListaAreaTematica($debug);
    foreach ($objAreaTematica->getListaAreasTematicas() as $objL) {
        echo "<div class='row' style='margin-bottom:10px;'>";
        echo "<div class='col-sm-11' style='font-size:16px'>";
        //                    echo lpad($objL->getOrdem(),2,0)." - ".$objL->getTitulo()."<br>";
        echo $objL->getPrefixo() . " - " . $objL->getTitulo() . "<br>";
        //                    echo $objL->getDataIni()? $objL->getDataIni()." - ".$objL->getHorarioIni()." ás ".$objL->getHorarioFim() : "";
        //                    if($OP_EVENTO_ONLINE_LINK_AREA_TEMATICA_LIBERADO){
        if ($objL->getLinkAula() != '' and $objL->getLinkAulaPublicar() == '1') {
            echo "  <a href='" . $objL->getLinkAula() . "' "
                . "target='_blank'  style='width: 100%; color: #008b45;margin-left:35px;font-size:18px'>"
                . " <i class='fa fa-group'></i> Acessar Sala</a><br>";
        } else {
            echo "<a href='javascript:alert(\"Aguardando horário para liberação\");' style='margin-left:35px;font-size:14px'>"
                . " <i class='fa fa-group'></i> Aguardando horário para liberação</a><br>";
        }
        //                    }
        echo "</div>";
        echo "</div>";
    }
    ?>
    </div>
  </div>
