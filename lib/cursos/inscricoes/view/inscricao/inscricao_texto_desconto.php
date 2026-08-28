<?php
If($desconto!=""){
$_SESSION['desconto']=$desconto;
}
Switch($_SESSION['desconto']){
  Case "SIM":
        @$FWS_Desconto = $FWS_Desconto."
        Obs: Estudantes de graduação e lideranças comunitárias terão direito a desconto de 50% no valor da inscrição no congresso, desde que comprovem essa condição.
        <br>
        <br>SIM, você optou em se beneficiar do desconto, comprometendo-se a comprovar a veracidade dessa informação junto a secretaria do evento, estando ciente de que a não apresentação dos devidos comprovantes poderá acarretar o cancelamento de sua inscrição.

       ";
    break;
  Case "NÃO":
        @$FWS_Desconto = $FWS_Desconto."
        Obs: Estudantes de graduação e lideranças comunitárias terão direito a desconto de 50% no valor da inscrição no congresso, desde que comprovem essa condição.
        <br>
        <br>NÃO, você optou em NÃO se beneficiar do desconto.
       ";
    break;
  Case "":
        @$FWS_Desconto = $FWS_Desconto."
        Obs: Estudantes de graduação e lideranças comunitárias terão direito a desconto de 50% no valor da inscrição no congresso, desde que comprovem essa condição.
        <br>
        <br>NÃO, você optou em NÃO se beneficiar do desconto.
       ";
    break;

  Default;
        $FWS_Desconto = "";
    break;
}

If($_SESSION['content']=="view/inscricao/inscricao_trabalhos_inicio.php"){
  echo $FWS_Desconto;
}
?>  