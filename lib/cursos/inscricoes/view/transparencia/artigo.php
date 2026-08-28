<script language="JavaScript" type="text/javascript">
    function visualiza(cod){

        document.getdoc.cod.value=cod;
        document.getdoc.submit();

        //var largura=500;
        //var altura=500;
        //var url="";
        //if(popUpWin3){

        //	if(!popUpWin3.closed) popUpWin3.close();

        //}
        //url = '../frame_pdf.php?cod='+cod;
        //popUpWin3=window.open(url,"popUpWin3","toolbar=no, width="+largura+",height="+largura+",top=0,left=0,directories=no,menubar=no,scrollbars=yes,resizable=yes");
	 
    }
</script>
<form name="getdoc" method="post" action="getdoc.php">
    <input type="hidden" value="" name="cod">
</form>
<?php
//echo file_get_contents('https://api.livedocx.com/2.0/mailmerge.asmx?WSDL');
//exit(0);






if(!is_numeric(@$_GET['cod']))
	die("Erro na solicitacao");
$cod = (int) $_GET['cod'];
$arq = "../artigos/$cod.doc";
if(!is_file($arq))
	die("Artigo não encontrado no servidor");
	
if(is_file("../artigos/$cod.pdf"))
{
	$document = file_get_contents("../artigos/$cod.pdf");
}
else 
{
    echo "<script>javascript: visualiza($cod)</script>";
 
//set_include_path(get_include_path().":../zend/library");
//require_once '../zend/library/Zend/Service/LiveDocx/MailMerge.php';
//require_once '../zend/library/Zend/Service/LiveDocx/Exception.php';
//$mailMerge = new Zend_Service_LiveDocx_MailMerge();
// 
//$mailMerge->setUsername('')
//          ->setPassword('');
//          
// 
//$mailMerge->setLocalTemplate($arq);
// 
//// necessary as of LiveDocx 1.2
//$mailMerge->assign('dummyFieldName', 'dummyFieldValue');
// 
//$mailMerge->createDocument();
// 
//$document = $mailMerge->retrieveDocument('pdf');
//
}

//header("Content-Type: application/save"); 
////header("Content-Length:".filesize($file)); 
////header('Content-Disposition: attachment; filename="' . $cod . '.pdf"'); 
//header('Content-Disposition: attachment; filename="' . $cod . '.doc"'); 
//header("Content-Transfer-Encoding: binary");
//header('Expires: 0'); 
//header('Pragma: no-cache'); 
// 
//echo $document;
////file_put_contents("{$_GET['cod']}.pdf", $document);
// 
//unset($mailMerge);