<img style="position:absolute; margin-left:10;" border="1" width="1050"
src="modelos/img/certificado.jpg">
<table style="position: absolute;
font-family: times;
font-size: 16px;
text-align: justify;
width: 740px;
margin-left: 160px;
margin-top: 220px;
"><tr><td width="740"><p align="center">
Certificamos que <strong><em><?php echo strtoupper($nome)?></em></strong>
apresentou o trabalho abaixo mencionado no
XVIII Encontro Anual de Iniciação Científica, realizado no
período de 20 a 22 de Outubro de 2011,
na <br><strong>Universidade Estadual de Ponta Grossa</strong>, com 20 horas de duração.
<br>
<br> Apresentador: <strong><?php echo strtoupper($nome)?></strong>
<br> Co-autor(es):
<?php if($co_autor1!="")echo "  ".strtoupper($co_autor1); ?>
<?php if($co_autor2!="")echo ", ".strtoupper($co_autor2); ?>
<?php if($co_autor3!="")echo ", ".strtoupper($co_autor3); ?>
<?php if($co_autor4!="")echo ", ".strtoupper($co_autor4); ?>
<?php if($co_autor5!="")echo ", ".strtoupper($co_autor5); ?>
<?php if($cod_projeto==1 && $co_autor6!="")echo ", ".strtoupper($co_autor6); ?>
<?php if($cod_projeto!=1){echo "<br> Orientador(a): <strong>".strtoupper($prof['nome'])."</strong>";}?>

<?php
$titulo = strtoupper($titulo);
//if($_SERVER['REMOTE_ADDR']=="10.90.10.88"){
//$titulo= iconv("iso-8859-9","utf-8",$titulo);    
$titulo=str_replace('&#946;', '&szlig;', $titulo);
//}
?>

<br><br> Título: <strong><?php echo $titulo; ?></strong>
<br>
</p>
</td></tr></table>