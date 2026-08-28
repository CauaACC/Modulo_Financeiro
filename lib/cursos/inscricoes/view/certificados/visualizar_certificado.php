<?php
die("Os certificados não estão disponíveis ainda!");
@session_start();
//setlocale(LC_CTYPE,"pt_BR");
if($_SERVER['REMOTE_ADDR']=="10.90.10.888")
{
    $debug=true;
}else{
    $debug=false;
}
include '../conecta/bd.php';

if($_GET['apresentadorIE']){
    $_GET[cod]=$_GET[cod];
}else{
    $_GET[cod]=$_SESSION[cod];
}
if($_GET[cod]){
     if($debug){ echo "entrou codigo<br>";}
     if($_GET['apresentadorIE'])
     {
         if($debug){ echo "entrou apresentador<br>";}
         include '../tecnico/controle.php';
         $select = "select a.*,pessoa.*, pr.nome as nome_projeto,ap.cod_projeto
         , ap.cod_professor, i.sigla, ha.cod_status, nao_apresentou
         from artigo a
         inner join apresentador ap on ap.cod_participante=a.cod_participante
         inner join pessoa          on a.cod_participante=pessoa.cod_pessoa
         inner join projeto pr      on ap.cod_projeto=pr.cod_projeto
         inner join participante_evento pe on a.cod_participante=pe.cod_participante
         inner join instituicao i   on i.cod_instituicao=pe.cod_instituicao
         inner join historico_artigo ha on ha.cod_artigo=a.cod_artigo
            and ha.cod_historico in (select max(cod_historico) from historico_artigo group by cod_artigo)
         where a.cod_participante = $_GET[cod]
           and a.cod_evento = 2";

         if($debug){ echo $select."<br>";}

         $qry=pg_query($select);

         if (pg_num_rows($qry)>0){

             $certificado=pg_fetch_array($qry);
             extract($certificado);
             if($cod_status==7){
                 if(!$nao_apresentou){
                     if($cod_professor!=""){
                         $qry_prof=pg_query("select * from pessoa where cod_pessoa=$cod_professor");
                         $prof=pg_fetch_array($qry_prof);
                     }
                     require_once('gera-certificado-apresentador.php');
                 }else{
                     $fws[codErro]="006";
                 }
             }else{
                 $fws[codErro]="004";
             }
         }else{
             $fws[codErro]="001";
         }
     }
     /*
      * Gerando certificado Apresentador
      */
     if($_GET['apresentador'])
     {
         if($debug){ echo "entrou apresentador<br>";}
         include '../apresentador/controle.php';
         $select = "select a.*,pessoa.*, pr.nome as nome_projeto,ap.cod_projeto
        , ap.cod_professor, i.sigla, ha.cod_status, nao_apresentou
         from artigo a
         inner join apresentador ap on ap.cod_participante=a.cod_participante
         inner join pessoa          on a.cod_participante=pessoa.cod_pessoa
         inner join projeto pr      on ap.cod_projeto=pr.cod_projeto
         inner join participante_evento pe on a.cod_participante=pe.cod_participante
         inner join instituicao i   on i.cod_instituicao=pe.cod_instituicao
        inner join historico_artigo ha on ha.cod_artigo=a.cod_artigo
            and ha.cod_historico in (select max(cod_historico) from historico_artigo group by cod_artigo)
         where a.cod_participante = $_GET[cod]
           and a.cod_evento = 2";

         if($debug){ echo $select."<br>";}

         $qry=pg_query($select);

         if (pg_num_rows($qry)>0){

             $certificado=pg_fetch_array($qry);
             extract($certificado);
             if($cod_status==7){
                 if(!$nao_apresentou){
                     if($cod_professor!=""){
                         $qry_prof=pg_query("select * from pessoa where cod_pessoa=$cod_professor");
                         $prof=pg_fetch_array($qry_prof);
                     }
                     require_once('gera-certificado-apresentador.php');
                     pg_query("update participante_evento set emitiu_certificado=true, dat_alter=('now'::text)::date where cod_participante=$_GET[cod]");
                 }else{
                     $fws[codErro]="006";
                 }
             }else{
                 $fws[codErro]="004";
             }
         }else{
             $fws[codErro]="001";
         }
     }
     /*
      * Gerando certificado Ouvinte
      */
     if($_GET[ouvinte]){
         if($debug){ echo "entrou ouvinte<br>";}
         include '../ouvinte/controle.php';
         $select = "select p.*,o.pago from pessoa p
         inner join ouvinte o on p.cod_pessoa=o.cod_participante
         where p.cod_pessoa=$_GET[cod]
         ";

         if($debug){ echo $select."<br>";}

         $qry=pg_query($select);
         if(pg_num_rows($qry)>0){
             $certificado=pg_fetch_array($qry);
             extract($certificado);
             if($pago){
                 require_once('gera-certificado-ouvinte.php');
                 pg_query("update participante_evento set emitiu_certificado=true, dat_alter=('now'::text)::date where cod_participante=$_GET[cod]");
             }else{
                 $fws[codErro]="005";
             }
         }else{
             $fws[codErro]="001";
         }
     }
}else{
    $fws[codErro]="002";
}


switch ($fws[codErro]) {
     case "001":
         echo "<br><br>Código de inscrição ->$_GET[cod]<- não encontrada!<br>";
         break;
     case "002":
         echo "<br><br>Código de inscrição não informado!<br>";
         break;
     case "003":
         echo "<br><br>Perfil do inscrito não foi informado!<br>";
         break;
     case "004":
         echo "<br><br>Trabalho não aprovado!<br>";
         break;
     case "005":
         echo "<br><br>Pagamento da inscrição, ainda não consta no sistema.<br>";
         break;
     case "005":
         echo "<br><br>O apresentador faltou.<br>";
         break;
     default:
         break;
}
if(($_GET[ouvinte])and($fws[codErro]))
     echo "<br><br><a href='/evento/ouvinte/principal.php'>clique aqui para voltar ao sistema (ouvinte)</a>";

if(($_GET[apresentador])and($fws[codErro]))
     echo "<br><br><a href='/evento/apresentador/principal.php'>clique aqui para voltar ao sistema (apresentador)</a>";

if((!$_GET[apresentador])and(!$_GET[ouvinte])){
     $fws[codErro]="003";
     echo "<br><br><a href='/evento/'>clique aqui para voltar ao sistema</a>";
}
?>