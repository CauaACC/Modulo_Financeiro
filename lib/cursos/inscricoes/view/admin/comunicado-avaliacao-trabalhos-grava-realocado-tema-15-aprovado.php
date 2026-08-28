<?php
/**
 * @deprecated 2026-05-02 — fluxo legado de evento muito antigo.
 * @todo remover
 */
die('O arquivo ou função está descontinuado, entrar em contato com a administração.');

session_start();

#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");

require_once(dirname(__FILE__)."/../../include/config.php");

require_once(dirname(__FILE__)."/../../view/admin/comunicado-avaliacao-trabalhos-cabecalho.php");

#require_once(dirname(__FILE__)."/../../view/admin/comunicado-avaliacao-trabalhos-busca.php");

$FWS_Debug=false;

If($FWS_Debug==1){
  echo $_POST['total_comunidado'];
  echo $_POST['cod_trabalho_1'];
}

#-------------------------------------------
#Capturar codigo dos trabalhos a serem informados
For($i=1; $i<=$_POST['total_comunidado']; $i++){
  $indice = "cod_trabalho_".$i;
  If (!empty($_POST[$indice])){
    $ii = $ii+1;
    $cod_trabalho_[$ii] = $_POST[$indice];
    If($FWS_Debug==1){ echo $cod_trabalho_[$ii]."-".$i."<br>  "; }
  }
  If($FWS_Debug==1){
    echo $indice;
  }
}
#-------------------------------------------

  $FWS_Data_Ini  = "200604010001"; #aaaammddhhii
  $FWS_Data_Fim  = "200606232359"; #aaaammddhhii
  $FWS_Data_Hoje = Date('YmdHi');

  @$FWS_['data_alteracao']  = Date("YmdHi");
  @$FWS_['data_inclusao']   = Date("YmdHi");
  @$FWS_['status']          = "ATIVADO";
  @$FWS_['session_id']      = $_SESSION['_login'];#session_id();
  @$FWS_['http_user_agent'] = $_SERVER["HTTP_USER_AGENT"];
  @$FWS_['ip_cliente']      = $_SERVER["REMOTE_ADDR"];

  $FWS_['cod_evento'] = COD_EVENTO;
  $FWS_['acao'] = $_POST['acao'];

#-------------------------------------------
  If (!$Conexao_db = FWA_Conexao("OpcTec")) { //Conectando-se ao banco de dados
    $FWS_Execucao = false;
    $MSG          = "Operação-Não-Executada,-Erro-de-Conexão";
  }Else{


    If($FWS_Debug==1){ echo  $FWS_['acao']."<br><br>"; }
    If($FWS_['acao']=="Enviar-Comunicado") {


      For($i=1; $i<=Count($cod_trabalho_); $i++){

        If($FWS_Debug==1){ echo $cod_trabalho_[$i]."-".$i."<br>  "; }

        $FWS_[cod_trabalho] = $cod_trabalho_[$i];

        #Consulta-1-Retorna-dados-do-inscrito-e-do-trabalho-para-formular-email-e-posterior-envio.
        $consql = "
                 SELECT
                       ti.cod_inscrito
                     , ti.nome_inscrito
                     , ti.e_mail
                     , tt.autor_apresentador_e_mail
                     , tt.autor_apresentador_nome
                     , tt.nome_co_autor_1
                     , tt.nome_co_autor_2
                     , tt.nome_co_autor_3
                     , tt.nome_co_autor_4
                     , tt.nome_co_autor_5
                     , tt.cod_trabalho
                     , tt.titulo_trabalho
                     , tt.instituicao_trabalho
                     , tt.palavras_chave
                     , toa.cod_area_tematica
                     , toa.titulo
                     , (SELECT distinct(ta.avaliacao)      FROM tab_trabalhos_avaliacao ta WHERE (ta.cod_trabalho=tt.cod_trabalho)) AS avaliacao
                     , (SELECT distinct(ta.e_mail_enviado) FROM tab_trabalhos_avaliacao ta WHERE (ta.cod_trabalho=tt.cod_trabalho)) AS e_mail_enviado
                 FROM tab_opcoes_areas_tematicas toa, tab_trabalhos tt, tab_inscritos ti
                 WHERE (tt.cod_evento       =".$FWS_['cod_evento'].")
                    and(tt.cod_trabalho     =".$FWS_['cod_trabalho'].")
                    and(tt.cod_area_tematica=toa.cod_area_tematica)
                    and(tt.cod_inscrito     =ti.cod_inscrito)
                    and(ti.status           <>'EXCLUÍDA')
                 ORDER BY ti.nome_inscrito
        ";

        If ($RS=FWA_Executa_SQL($FWS_Debug,$Conexao_db,$consql)) {

          If($RS->fields[3]!=""){#Se Consulta-1-Retorna-dados-do-inscrito-e-do-trabalho-para-formular-email-e-posterior-envio.

            $MSG .="OK-Se-Consulta-1-Retorna-dados-do-inscrito-e-do-trabalho-para-formular-email-e-posterior-envio.";
            $FWS_Execucao = true;

            $FWS_['cod_inscrito']             =$RS->fields[0];
            $FWS_['nome_inscrito']            =$RS->fields[1];
            $FWS_['e_mail_inscrito']          =$RS->fields[2];
            $FWS_['autor_apresentador_e_mail']=$RS->fields[3];
            $FWS_['autor_apresentador_nome']  =$RS->fields[4];
            $FWS_['nome_co_autor_1']          =$RS->fields[5];
            $FWS_['nome_co_autor_2']          =$RS->fields[6];
            $FWS_['nome_co_autor_3']          =$RS->fields[7];
            $FWS_['nome_co_autor_4']          =$RS->fields[8];
            $FWS_['nome_co_autor_5']          =$RS->fields[9];
            $FWS_['cod_trabalho']             =$RS->fields[10];
            $FWS_['titulo_trabalho']          =$RS->fields[11];
            $FWS_['instituicao_trabalho']     =$RS->fields[12];
            $FWS_['palavras_chave']           =$RS->fields[13];

            $FWS_['cod_area_tematica']        =$RS->fields[14];
            $FWS_['titulo']                   =$RS->fields[15];

            $FWS_['avaliacao']                =$RS->fields[16];
            $FWS_['e_mail_enviado']           =$RS->fields[17];


            If($FWS_['nome_co_autor_1']!=" ")
              $FWS_['nome_co_autores'] = $FWS_['nome_co_autores'].$FWS_['nome_co_autor_1']."<br>";

            If($FWS_['nome_co_autor_2']!=" ")
              $FWS_['nome_co_autores'] = $FWS_['nome_co_autores'].$FWS_['nome_co_autor_2']."<br>";

            If($FWS_['nome_co_autor_3']!=" ")
              $FWS_['nome_co_autores'] = $FWS_['nome_co_autores'].$FWS_['nome_co_autor_3']."<br>";

            If($FWS_['nome_co_autor_4']!=" ")
              $FWS_['nome_co_autores'] = $FWS_['nome_co_autores'].$FWS_['nome_co_autor_4']."<br>";

            If($FWS_['nome_co_autor_5']!=" ")
              $FWS_['nome_co_autores'] = $FWS_['nome_co_autores'].$FWS_['nome_co_autor_5']."<br>";


            If($FWS_['e_mail_enviado']!="SIM"){
              #Montar E-mail
              require("comunicado-avaliacao-trabalhos-aprovado-corpo-email-realocado-tema-15-aprovado.htm");
              
              If($FWS_Debug==1){ echo  $FWS_['corpo_email']."<br><br>"; }

              $Var_Pagina_Proprietaria = "[SCRIPT-RedeUnida]";
              #$Var_Url_Erro, $Var_Url_Ok,

              $Var_Endereco_Destinatario =strtolower($FWS_['e_mail_inscrito']);
              #$Var_Endereco_Destinatario ="zaka_cc@hotmail.com";
              $Var_Nome_Destinatario     =$FWS_['nome_inscrito'];

              $Var_Endereco_Remetente    ="congresso7@sercomtel.com.br";
              $Var_Nome_Remetente        ="VII Congresso Nacional da Rede Unida";

              #$Var_caminho_arquivo       ="";
              $Var_Htm                   =true;
              $Var_Subject               ="[Rede Unida] Comunicado de Avaliação, Realocação de Trabalho";
              $Var_Corpo                 =$FWS_['corpo_email'];

              $FWS_['corpo_email']    = "";
              $FWS_['nome_co_autores']= "";


              If (FWA_Manda_Email(
                  $Var_Pagina_Proprietaria,
                  $Var_Url_Erro, $Var_Url_Ok,
                  $Var_Endereco_Destinatario,
                  $Var_Nome_Destinatario,
                  $Var_Endereco_Remetente,
                  $Var_Nome_Remetente,
                  $Var_caminho_arquivo,
                  $Var_Htm,
                  $Var_Subject,
                  $Var_Corpo
                 )==1){

                   #echo "<br>Email e arquivo enviado com sucesso!<br>";
                   #header("Location:http://www.opctec.com.br/eventos/teste/index.php?content=sucesso.htm");
                   $FWS_['e_mail_enviado'] = "SIM";

              }Else{
                   #echo "<br>Erro ao enviar e-mail!<br>";
                   #header("Location:http://www.opctec.com.br/eventos/teste/index.php?content=sucesso.htm");
                   $FWS_['e_mail_enviado'] = "NÃO";
              }

              $consql  = "
              UPDATE tab_trabalhos_avaliacao SET
                 e_mail_enviado ='".$FWS_['e_mail_enviado']."'
                ,data_alteracao ='".$FWS_['data_alteracao']."'
                ,status         ='".$FWS_['status']."'
                ,session_id     ='".$FWS_['session_id']."'
                ,http_user_agent='".$FWS_['http_user_agent']."'
                ,ip_cliente     ='".$FWS_['ip_cliente']."'
              WHERE (cod_trabalho=".$FWS_['cod_trabalho'].")and(cod_evento =".$FWS_['cod_evento'].")
              ";

              If (FWA_Executa_SQL($FWS_Debug,$Conexao_db,$consql)) {//Se login inserido
                $MSG         .="--OK-Update-Gravação Comunicado!";
                $FWS_Execucao = true;
              }Else{
                $MSG         .="--ERRO-Update-Gravação Comunicado";
                $FWS_Execucao = false;
                $FWS_['e_mail_enviado'] = "Comunicado Enviado, não gravado no banco";
              }

            }Else{#Comunicados Ja foi enviado
              $MSG         .="--OK-Campo-e-mail-enviado-alterado-com-sucesso!.";
              $FWS_Execucao = true;
              $FWS_['e_mail_enviado'] = "Ja havia sido Enviado";
            }

            $FWS_Linha_Resultado .= "<tr><td>".$FWS_['cod_inscrito']."</td><td>".$FWS_['cod_trabalho']."</td><td>".$FWS_['e_mail_inscrito']."</td><td>".$FWS_['e_mail_enviado']."</td></tr>";

          }Else{//ERRO-Se-Consulta-1-NÃO-Retorna-dados-do-inscrito-e-do-trabalho-para-formular-email-e-posterior-envio.
              $MSG         .="ERRO-Consulta-1";
              $FWS_Execucao = false;
          }
        }Else{
          $MSG         .="ERRO-Consulta-1--Consulta-dados-do-inscrito-e-do-trabalho-para-formular-email-e-posterior-envio.";
          $FWS_Execucao = false;
        }

      }#Fim FOR
    }Else{
      $MSG          = "Operação-Não-Executada,-Acao-Diferente-Ativar--";
      $FWA_Execucao = false;
    }
  }#End if Conexao
#-------------------------------------------
$CONTENT      = "view/admin/comunicado-avaliacao-trabalhos-listagem.php";
$_SESSION['MSG'] = $MSG;
$URL          = WB_URL_VIEW."?content=".$CONTENT."&cod_trabalho=".$cod_trabalho;

#--------------------------------------------------------------
$MSG_Imprimir = $MSG;

$loc   = "/--/";
$subst = "<br>";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);

$loc   = "/-/";
$subst = " ";
$MSG_Imprimir = preg_replace($loc,$subst,$MSG_Imprimir);
#--------------------------------------------------------------
If($FWS_Debug==true){
  echo $MSG_Imprimir;
  #echo "<br><a href='{$URL}'>{$URL}</a><br>";
}Else{
  /*
  echo "<br>Aguarde um momento - ".date("d/m/Y - H:i:s");
  echo "<br>Gravando seus dados no sistema<BR>";
  echo "<br>Este procedimento pode demorar algun tempo caso a lista de trabalhos a serem avaliados ainda seja grande<BR>";
  echo "<br><a href='{$URL}'>{$URL}</a><br>";
  echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=".$URL."\">";
  */
}
echo "<br>
      <hr size='1' width='100%'>
      <span class='subtitulo'>Status do Envio de Comunicado de <b>REALOCADOS TEMA 15 - APROVADO</b>, atual</span><br><br>
      <table border='1'>
        <tr><td>Código de Inscrito</td><td>Código do Trabalho</td><td>E-mail Inscrito</td><td>Comunicado Enviado</td></tr>
     ";
echo $FWS_Linha_Resultado;
echo "</table>";

?>