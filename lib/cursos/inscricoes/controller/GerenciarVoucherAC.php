<?php

class GerenciarVoucherAC {

    function __construct(){
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
       $this->codEvento = COD_EVENTO;
       $objConf = new GerenciarConfiguracaoEventoAC();
       $objConf->carregarConfiguracao($this);
    }
    
    /**
     * @param type $fws_
     * @param type $debug
     * @return string
     * @throws Exception
     */
    function carregarTexto($fws_, $debug = false) {
        
        $fws_['tipoVoucher'] = 'etiqueta';
        
        switch ($fws_['tipoVoucher']) {
            case 'etiqueta':
                $fws_['nomeArquivo'] = 'voucher.pdf';
                $fws_['texto_voucher'] = ""
                . "<p style='margin-top:5px;'><h4>65465147917616468JHUIGUTYRD</h4></p>";
                $fws_['modelo_template'] = "modelo-voucher.php";
                break;
            default:
                throw new Exception("Tipo de voucher não informado.");
                break;
        }
        
        return $fws_;
    }
    public function gerarVoucher($fws_, $debug = false) {
        $objVoucher = new Voucher();
        $objVoucher->setCodVoucherEvento($_GET['cod_voucher_evento']);
        $objVoucher->getVoucherById();
        try {
            $objVoucher->carregarListaVouchersByCodVoucherEvento();
        } catch (Exception $exc) { }

        if(!$objVoucher->getListaVouchers()){
            echo "Não existe lista de vouchers cadastrados, ou todos os vouchers desta lita foram utilizados.";
            exit;
        }
        /*carregando texto*/
//        $this->gerarVoucherFpdf($fws_, $debug = false);
        $this->gerarVoucherHtml2Pdf($fws_,$objVoucher, $debug = false);
    }
    public function gerarVoucherHtml2Pdf($fws_, $objVoucher=false, $debug = false) {
        /*carregando texto*/
        try {
            $fws_ = $this->carregarTexto($fws_);
        } catch (Exception $exc) {
            echo $exc->getMessage();
            exit();
        }
        $objEv = $objVoucher->getEvento();
        $fws_['nomeArquivo'] = $objVoucher->getSigla().'-'.count($objVoucher->getListaVouchers()).'-'.$fws_['nomeArquivo'];
        /*mesclando com o modelo*/
        ob_start();
//        header('Content-Type: text/html; charset=ISO-8859-1');
//        header("Content-Type: application/pdf");
        //  header('Content-Disposition: attachment; filename='.$fws_['nomeArquivo']); 
//        include(dirname(__FILE__) . '/../view/voucher/voucher/modelos/'.$fws_['modelo_template']);
        echo "<head><title>".$fws_['nomeArquivo']."</title></head>";
        include(dirname(__FILE__) . '/../view/voucher/voucher/modelos/modelo-voucher-table.php');
        $htmlContent = ob_get_clean();
        // echo $htmlContent;
        // exit();
        /*gerando pdf*/
        require_once(dirname(__FILE__) . '/../lib/vendor/autoload.php');

        // $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'pt');
        // $html2pdf->WriteHTML($content, 1);
        // $html2pdf->Output($fws_['nomeArquivo']); // grava o arquivo em disco na primeira vez que ? visualizado
        // $htmlContent = mb_convert_encoding($htmlContent, 'UTF-8', 'ISO-8859-1');
        echo $htmlContent;
        exit();
        $mpdf = new \Mpdf\Mpdf(['format' => 'A4','orientation' => 'P','unit'=>'pt',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0]
        );
        // $mpdf->SetAutoPageBreak(false);
        // Definir a codificação de entrada
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->WriteHTML($htmlContent);
        $mpdf->Output($fws_['nomeArquivo'], 'I');
        unset($mpdf);
        exit();
    }  
    
    public function gerarVoucherFpdf($fws_, $debug = false) {
        /*carregando texto*/
        try {
            $fws_ = $this->carregarTexto($fws_);
        } catch (Exception $exc) {
            echo $exc->getMessage();
            exit();
        }
//        require_once(dirname(__FILE__).'/../_fpdf/fpdf.php');
        require_once(dirname(__FILE__) . '/../lib/html2pdf/_fpdf/fpdf.php');

        //$pdf = new FPDF();
        $pdf=new FPDF('P','mm','A4'); // Cria um arquivo novo tipo carta, na vertical.
//        $pdf->AddPage();
        $pdf->SetAutoPageBreak('auto',2);
        $pdf->SetMargins('15','6','3'); // Define as margens do documento
        $pdf->SetXY(3,40);
        $pdf->SetFont('Helvetica','B',18);
//        $pdf->Cell(0,10,'Title',1,2,'C');
        $pdf->MultiCell(0,8, utf8Convert($fws_['nome_inscrito']),0,C);
        $pdf->SetFont('Helvetica','',18);
        $pdf->SetX(3);
        $pdf->MultiCell(0,8, utf8Convert($fws_['cidade-estado']),0,C);
//        $pdf->Cell(50,30,'HelloHelloHelloHelloHelloHello World!');

        $pdf->Output();
        exit();
    }
}
