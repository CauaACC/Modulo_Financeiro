<?php
/**
 * Logiciel : exemple d'utilisation de HTML2PDF
 * 
 * Convertisseur HTML => PDF, utilise fpdf de Olivier PLATHEY 
 * Distribu� sous la licence LGPL. 
 *
 * @author		Laurent MINGUET <webmaster@spipu.net>
 *
 * setTestTdInOnePage : permet de desactiver le test sur la taille des TD afin qu'ils rentrent sur une seule page
 * 
 * isset($_GET['vuehtml']) n'est pas obligatoire
 * il permet juste d'afficher le r�sultat au format HTML
 * si le param�tre 'vuehtml' est pass� en param�tre _GET
 */

// r�cup�ration du contenu HTML
ob_start();
include(dirname(__FILE__).'/modelos/modelo-certificado-apresentador.php');
$content = ob_get_clean();

// conversion HTML => PDF
require_once(dirname(__FILE__).'/../include/html2pdf/html2pdf.class.php');
$html2pdf = new HTML2PDF('L','A4','fr');
$html2pdf->setTestTdInOnePage(false);
$html2pdf->pdf->IncludeJS("print(true);");
$html2pdf->WriteHTML($content, isset($_GET['vuehtml']));
$html2pdf->Output(dirname(__FILE__).'/pdf/certificado-apresentador-'.$SESSION[cod].'.pdf');
