<?php

class GerenciarEtiquetaAC {

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
        if($fws_['codInscrito']==''){
            throw new Exception("Fecha a aba e clique no link novamente.");
        }
        $objInscrito = new Inscrito();
        $objInscrito->setCodInscrito($fws_['codInscrito']);
        $objInscrito->getInscritoById();

        /*Regras para bloquear emissao de etiqueta creacha*/
//        $ChapasConsultarEventos = "('67374158904'-'31880512807'-'02954642912'-'63503760997'-'072744870612')";
//        if(stristr($ChapasConsultarEventos, "'".$objInscrito->getCpf()."'")){
//            
//        }else{
//            if(!$objInscrito->getCodIsento() and !$objInscrito->getTotalPagamentos() and $objInscrito->getCodPerfil()!='25'){
//                throw new Exception("Emissão de etiqueta bloqueada.<br>Caso necessário, "
//                . "entre em contato com a administração do evento para regularizar sua inscrição.");
//            }
//        }
        
        if($objInscrito->getNomeInscrito()==''){
            throw new Exception("Inscrito não localizado.");
        }
        
        /*Registrar impressão de cracha*/
        $objInscrito->registrarImpressaoCracha();
        
        $fws_['nome_inscrito'] = maiusculo($objInscrito->getNomeInscrito());
        $fws_['cod_inscrito']  = $objInscrito->getCodInscrito();
        
        if($objInscrito->getCidade()!='' and $objInscrito->getEstado()!='' and $objInscrito->getEstado()!='-1'){
            $fws_['cidade-estado'] = $objInscrito->getCidade()."-".$objInscrito->getEstado();
        }else{  $fws_['cidade-estado'] = ""; }

        $fws_['tipoEtiqueta'] = 'cracha';
        
        switch ($fws_['tipoEtiqueta']) {
            case 'cracha':
                $fws_['nomeArquivo'] = 'chacha-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['texto_etiqueta'] = ""
                . "<h4>{$fws_['nome_inscrito']}</h4>"
                . "<p style='margin-top:5px;'><h4>{$fws_['cidade-estado']}</h4></p>";
                $fws_['modelo_template'] = "modelo-etiqueta.php";
                break;
            default:
                throw new Exception("Tipo de etiqueta não informado.");
                break;
        }
        
        return $fws_;
    }
    public function gerarEtiqueta($fws_, $debug = false) {
        /*carregando texto*/
//        $fws_['codInscrito'] = 3733;
        $this->gerarEtiquetaFpdf($fws_, $debug = false);
//        $this->gerarEtiquetaHtml2Pdf($fws_, $debug = false);
    }
    public function gerarEtiquetaHtml2Pdf($fws_, $debug = false) {
        /*carregando texto*/
        try {
            $fws_ = $this->carregarTexto($fws_);
        } catch (Exception $exc) {
            echo $exc->getMessage();
            exit();
        }

        /*mesclando com o modelo*/
        ob_start();
        header('Content-Type: text/html; charset=ISO-8859-1');
//        header("Content-Type: application/pdf");
//        header("Content-Disposition: inline; filename=".$fws_['nomeArquivoCartaAceite']);
        include(dirname(__FILE__) . '/../view/recepcao/etiquetas/modelos/'.$fws_['modelo_template']);
        $content = ob_get_clean();
        echo $content;
        /*gerando pdf*/
        require_once(dirname(__FILE__) . '/../lib/html2pdf/html2pdf.class.php');
        $html2pdf = new HTML2PDF('P', 'A4', 'pt');
//        $html2pdf = new HTML2PDF('P','mm',array(90,30));
//        $html2pdf = new HTML2PDF('P','cm',array(8,5));
        $html2pdf->setTestTdInOnePage(false);
        $html2pdf->WriteHTML($content, isset($_GET['vuehtml']));
//        $html2pdf->WriteHTML($content, 1);

//        if (!file_exists(WB_PATH_LOG_CARTAS_ACEITE)) {
//            mkdir(WB_PATH_LOG_CARTAS_ACEITE, 0755);
//        }
//        $html2pdf->Output(WB_PATH_LOG_CARTAS_ACEITE. '/' . $fws_['nomeArquivoCartaAceite'], 'F'); // grava o arquivo em disco na primeira vez que ? visualizado
        $html2pdf->Output($fws_['nomeArquivo']); // grava o arquivo em disco na primeira vez que ? visualizado
        exit();
    }  
    
    public function gerarEtiquetaFpdf($fws_, $debug = false) {
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
        $pdf=new FPDF('P','mm',array(100,35)); // Cria um arquivo novo tipo carta, na vertical.
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

