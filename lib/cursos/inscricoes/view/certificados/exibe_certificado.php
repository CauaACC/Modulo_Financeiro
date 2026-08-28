<?php
setlocale(LC_CTYPE,"pt_BR");
include '../conecta/bd.php';
include 'controle.php';
$qry=pg_query("select a.*,pessoa.*, pr.nome as nome_projeto,ap.cod_projeto, ap.cod_professor, i.sigla 
                from artigo a
                inner join apresentador ap on ap.cod_participante=a.cod_participante
                inner join pessoa on a.cod_participante=pessoa.cod_pessoa
                inner join projeto pr on ap.cod_projeto=pr.cod_projeto
                inner join participante_evento pe on a.cod_participante=pe.cod_participante
                inner join instituicao i on i.cod_instituicao=pe.cod_instituicao
                where a.cod_artigo=$_GET[cod]");
$certificado=pg_fetch_array($qry);
extract($certificado);

if($cod_professor!=""){
$qry_prof=pg_query("select * from pessoa where cod_pessoa=$cod_professor");
$prof=pg_fetch_array($qry_prof);
}
require_once('gera-certificado-apresentador.php');
