<?php

class GerenciarCertificadoAC {

    /**
     * Modelo da frente injetado de fora (preview do editor).
     * Quando preenchido, vence a resolucao perfil->evento: e assim que o
     * "Visualizar" mostra posicoes que ainda nao foram salvas.
     * Fora do preview permanece null e nada muda na emissao normal.
     */
    public $modeloCertificadoForcado = null;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $objConf = new GerenciarConfiguracaoEventoAC();
        $objConf->carregarConfiguracao($this);
    }

    function montarAssinatura($fws_, $debug=false){

        // Caminho da assinatura base (ex: assinatura-coord-rregina-celia.png)
        $assinatura = 'assinatura-coord-rregina-celia.png';

        // Nome e cargo (dinâmicos)
        $nome  = "Dra Regina Celia B. Rezende Machado";
        $cargo = "Coordenadora Geral";

        // Carrega a imagem da assinatura
        $img = imagecreatefrompng($assinatura);
        $largura = imagesx($img);
        $altura  = imagesy($img);

        // Cria uma nova imagem com espaço extra abaixo do texto
        $nova_altura = $altura + 120;
        $nova = imagecreatetruecolor($largura, $nova_altura);

        // Fundo branco
        $branco = imagecolorallocate($nova, 255, 255, 255);
        imagefill($nova, 0, 0, $branco);

        // Copia a assinatura para a nova imagem
        imagecopy($nova, $img, 0, 0, 0, 0, $largura, $altura);

        // Define a cor do texto (preto)
        $preto = imagecolorallocate($nova, 0, 0, 0);

        // Caminho da fonte TTF (use uma fonte limpa, ex: Arial ou DejaVuSans)
        $fonte = __DIR__ . '/DejaVuSans.ttf';

        // Escreve nome e cargo centralizados
        $fontSize = 16;
        $yNome  = $altura + 40;
        $yCargo = $altura + 80;

        // Centraliza o texto
        $bboxNome  = imagettfbbox($fontSize, 0, $fonte, $nome);
        $bboxCargo = imagettfbbox($fontSize, 0, $fonte, $cargo);
        $xNome  = ($largura - ($bboxNome[2] - $bboxNome[0])) / 2;
        $xCargo = ($largura - ($bboxCargo[2] - $bboxCargo[0])) / 2;

        imagettftext($nova, $fontSize, 0, $xNome,  $yNome,  $preto, $fonte, $nome);
        imagettftext($nova, $fontSize, 0, $xCargo, $yCargo, $preto, $fonte, $cargo);

        // Salva a imagem final
        imagepng($nova, 'assinatura_gerada.png', 9);

        // Libera memória
        imagedestroy($img);
        imagedestroy($nova);

        echo "Imagem gerada com sucesso: assinatura_gerada.png";

    }

    function avaliarEvento($fws_, $debug = false) {
        
        try {
            if (!$_SESSION['_cod_inscrito']) {
                throw new Exception("Sessão do usuário encerrada, retorno ao link anterior.");
            }   
            /*destino padrao*/
            $url = WB_URL_SITE."/index.php?content=view/inscricao/inscricao_congresso_ok.php";
            
            $objInscrito = new Inscrito();
            $objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
            $objInscrito->getInscritoById();
            $objInscrito->setAvaliouEvento('1');
            $objInscrito->salvar();

        } catch (OKException $exc) {
            $objInscrito->carregarObjEvento($debug);
            $objEv = $objInscrito->getObjEvento();
            $objConf = $objEv->getEventoConfiguracao();

            $objPopularConf = new GerenciarConfiguracaoEventoAC();
            $objPopularConf->carregarConfiguracaoByObjEvento($this, $objEv);             
//            if($this->urlBaseFormularioAvaliacao!=''){
            if($objConf->getLiberarAvaliarEvento()==1){
                $url = $objConf->getUrlAvaliarEvento();
            }
        } catch (Exception $exc) {
            $_SESSION['MSG']="Não foi possível gravar avaliação.";
            $url = WB_URL_SITE."/index.php?content=view/inscricao/inscricao_congresso_ok.php";
        }

        @header("Location: $url");
        echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
        exit();
    }

    /**
     * Substitui URLs remotas de imagens (servidas por scripts PHP) por caminhos
     * locais no disco, para que o mPDF leia direto sem fazer requests HTTP
     * (evitando deadlock de sessão e problemas de rede/SSL).
     */
    private function resolverImagensParaLocal($htmlContent, $fws_) {
        /* 1. Background: certificado-frente-unica.php → .jpg local */
        $evArrayBg = array(1,2,3,4,5,6,7,21,61,57,70,98);
        if (in_array($this->codEvento, $evArrayBg)) {
            $bgPath = WB_PATH_LOG_CERTIFICADOS . "../midia/";
        } else {
            $bgPath = WB_ROOT . "../logn/eventos/000/midia/";
        }
        $bgFile = $bgPath . "certificado-frente-unica.jpg";
        if (file_exists($bgFile)) {
            $htmlContent = preg_replace(
                '#https?://[^\'"]+/certificado-frente-unica\.php(\?[^\'"]*)?#',
                str_replace('\\', '/', $bgFile),
                $htmlContent
            );
        }

        /* 2. Assinatura presidente: ass-presidente.php → .jpg local */
        $assPresFile = WB_PATH_LOG_CERTIFICADOS . "../assinaturas/assinatura-presidente.jpg";
        if (!file_exists($assPresFile)) {
            $assPresFile = WB_ROOT . "../logn/eventos/000/assinaturas/assinatura-presidente.jpg";
        }
        if (file_exists($assPresFile)) {
            $htmlContent = preg_replace(
                '#https?://[^\'"]+/ass-presidente\.php(\?[^\'"]*)?#',
                str_replace('\\', '/', $assPresFile),
                $htmlContent
            );
        }

        /* 3. Assinatura coordenador: ass-coordenador.php?id=X → .jpg local */
        if ($fws_['cod_oficina'] != '') {
            $assCoordFile = WB_PATH_LOG_CERTIFICADOS . "../assinaturas/coordenador-" . numberPad($fws_['cod_oficina'], 7) . ".jpg";
        } else {
            $assCoordFile = '';
        }
        if ($assCoordFile != '' && file_exists($assCoordFile)) {
            $htmlContent = preg_replace(
                '#https?://[^\'"]+/ass-coordenador\.php(\?[^\'"]*)?#',
                str_replace('\\', '/', $assCoordFile),
                $htmlContent
            );
        }

        /* 4. QR Code: gerar localmente via biblioteca */
        $qrId = $fws_['nomeArquivoValidacao'];
        if ($qrId != '') {
            $qrTempFile = sys_get_temp_dir() . '/qr-cert-' . md5($qrId) . '.png';
            if (!file_exists($qrTempFile)) {
                require_once(dirname(__FILE__) . '/../lib/Image_QRCode/Image_QRCode-0.1.3/Image/QRCode.php');
                $options = array(
                    "image_type" => "png",
                    "module_size" => 5,
                    "error_correct" => "M",
                    "version" => 5,
                    "output_type" => "return"
                );
                $stringData = "Validação de conteúdo: https://www.opctec.net.br/opctec/atividades/certificados/validar/?id=" . $qrId;
                $qr = new \Image_QRCode();
                $gd = $qr->makeCode($stringData, $options);
                imagepng($gd, $qrTempFile);
                imagedestroy($gd);
            }
            $htmlContent = preg_replace(
                '#https?://[^\'"]+/qr-code\.php(\?[^\'"]*)?#',
                str_replace('\\', '/', $qrTempFile),
                $htmlContent
            );
        }

        return $htmlContent;
    }

    /**
     * Gera certificado em lote (frente + verso) sem redirect/exit.
     * Salva o PDF final em disco e retorna true/false.
     */
    public function gerarCertificadoEmLote($fws_, $debug = false) {
        /* carregando texto */
        $fws_ = $this->carregarTextoCertificado($fws_, $debug);

        /* mesclando com o modelo */
        ob_start();
        $evArray = array(1,2,3,4,5,6,7,10,21,61,57);
        $evArrayFrenteUnica = array(70,98);

        if (in_array($this->codEvento, $evArray)) {
            $modelo_template = dirname(__FILE__) . '/../view/certificados/' . $this->codEvento . '/modelos/' . $fws_['modelo_template'];
        } else if (in_array($this->codEvento, $evArrayFrenteUnica)) {
            $modelo_template = dirname(__FILE__) . '/../view/certificados/modelos/modelo-certificado-frente-unica.php';
        } else {
            $modelo_template = dirname(__FILE__) . '/../view/certificados/modelos/' . $fws_['modelo_template'];
        }
        include($modelo_template);
        $content = ob_get_clean();

        if (!file_exists(WB_PATH_LOG_CERTIFICADOS)) {
            mkdir(WB_PATH_LOG_CERTIFICADOS, 0755);
        }
        require_once(dirname(__FILE__) . '/../lib/vendor/autoload.php');

        $htmlContent = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        $htmlContent = $this->resolverImagensParaLocal($htmlContent, $fws_);

        $mpdf = new \Mpdf\Mpdf(['format' => 'A4','orientation' => 'L','unit'=>'mm',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0]
        );
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->WriteHTML($htmlContent);
        $mpdf->Output(WB_PATH_LOG_CERTIFICADOS . '/' . $fws_['nomeArquivoCertificado'], 'F');
        unset($mpdf);

        /* Anexar verso inline via FPDI (sem redirect) */
        $frenteFile = WB_PATH_LOG_CERTIFICADOS . $fws_['nomeArquivoCertificado'];
        if (in_array($this->codEvento, [21, 61, 70])) {
            $programaFile = WB_PATH_LOG_CERTIFICADOS . '/verso/programa.pdf';
        } else if ($fws_['cod_oficina'] != '' and ($this->codEvento >= 10)) {
            $programaFile = WB_PATH_LOG_CERTIFICADOS . '/verso/programa-' . numberPad($fws_['cod_oficina'], 7) . '.pdf';
        } else {
            $programaFile = WB_PATH_LOG_CERTIFICADOS . '/verso/programa.pdf';
        }

        require_once(dirname(__FILE__) . '/../lib/vendor/setasign/fpdf/Fpdf.php');

        if (file_exists($frenteFile) && file_exists($programaFile)) {
            $fpdi = new setasign\Fpdi\Fpdi();
            $fpdi->setSourceFile($frenteFile);
            $template = $fpdi->importPage(1);
            $fpdi->AddPage('L', 'A4');
            $fpdi->useTemplate($template);

            $fpdi->setSourceFile($programaFile);
            $template = $fpdi->importPage(1);
            $fpdi->AddPage('L', 'A4');
            $fpdi->useTemplate($template);

            $fpdi->Output($frenteFile, 'F');
            unset($fpdi);
        }

        return file_exists($frenteFile) ? $frenteFile : false;
    }

    /**
     * Carga horaria para certificados de Ministrante e Monitor, seguindo a
     * hierarquia pedida:
     *   1) carga horaria pratica/teorica separadas, quando informadas na ligacao
     *      (apenas para eventos a partir de cod_evento >= 125);
     *   2) carga horaria da relacao (ligacao inscrito<->oficina) -> prioridade;
     *   3) carga horaria da atividade (oficina);
     *   4) carga horaria do evento (so entra se nao houver nenhuma das anteriores).
     * Retorna a string ja formatada para entrar em "com carga horaria total de ...".
     */
    /**
     * A pessoa esta com o certificado GERAL de participacao bloqueado? (opt-out)
     * Le tab_inscritos.flg_bloquear_certificado_geral. NULL/0 = recebe (false).
     * Na duvida (erro/inscrito nao encontrado) retorna false = comportamento antigo.
     */
    private function pessoaBloqueadaCertificadoGeral($codInscrito) {
        try {
            if ((int) $codInscrito <= 0) {
                return false;
            }
            $obj = new Inscrito();
            $obj->setCodEvento($this->codEvento);
            $obj->setCodInscrito((int) $codInscrito);
            $obj->getInscritoById();
            return $obj->getFlgBloquearCertificadoGeral() ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }

    private function cargaHorariaMinistranteMonitor($objOf) {
        // 1) Pratica/teorica separadas (somente eventos >= 125)
        if ((int) $objOf->getCodEvento() >= 125) {
            $teorica = trim($objOf->getCargaHorariaTeorica());
            $pratica = trim($objOf->getCargaHorariaPratica());
            $partes = array();
            if ($teorica != '') { $partes[] = formatarCargaHoraria($teorica) . ' teóricas'; }
            if ($pratica != '') { $partes[] = formatarCargaHoraria($pratica) . ' práticas'; }
            if (!empty($partes)) {
                return implode(' e ', $partes);
            }
        }
        // 2) Carga horaria da relacao/ligacao (inscrito<->oficina) -> PRIORIDADE
        if (trim($objOf->getCargaHorariaInscrito()) != '') {
            return formatarCargaHoraria($objOf->getCargaHorariaInscrito());
        }
        // 3) Carga horaria da atividade (oficina)
        if (trim($objOf->getCargaHorariaOficina()) != '') {
            return formatarCargaHoraria($objOf->getCargaHorariaOficina());
        }
        // 4) Carga horaria do evento (ultimo fallback; so entra se nao houver as anteriores)
        return formatarCargaHoraria($objOf->getCargaHoraria());
    }

    public function gerarCertificado($fws_, $debug = false) {
        if($_SERVER['REMOTE_ADDR']=='177.82.0.288'){
            // error_reporting(E_ALL ^ E_NOTICE);
            // ini_set("display_errors", "On");
            $debug=false;
            // dd($fws_);
            // exit();
        }
        /* carregando texto */
        try {
            /*
            if(($_SERVER['REMOTE_ADDR']!='177.82.0288') and ($_SERVER['HTTP_HOST'] != 'eventos.test')){
            //if($this->codEvento!=70){
                throw new Exception("Emissão de certificados em manutenção<br>Previsão de retorno 30/01/2025 23h59.<br>Deseja ser avisado quando o emissão de certificado retornar? <a href='https://api.whatsapp.com/send?phone=5543996967790' target='_blank' title='Deseja ser avisado quando o sistema retornar?'>Clique aqui para ser avisado.</a>");
            }
            */
            $fws_ = $this->carregarTextoCertificado($fws_,$debug);
        } catch (Exception $exc) {
            $this->handleException($exc);
            return;
        }
        if($_SERVER['REMOTE_ADDR']=='177.82.0.288'){
            $debug = true;
        }
        /* mesclando com o modelo */
        ob_start();
//        header('Content-Type: text/html; charset=ISO-8859-1');
//        header("Content-Type: application/pdf");
//        header("Content-Disposition: inline; filename=" . $fws_['nomeArquivoCertificado']);
//        $evArray = array(1,2,3,4,5,6,7,10,18,19);
        $evArray = array(1,2,3,4,5,6,7,10,21,61,57);
        $evArrayFrenteUnica = array(70,98);
        
        if (in_array($this->codEvento, $evArray)) {
            $modelo_template = dirname(__FILE__) . '/../view/certificados/' . $this->codEvento . '/modelos/' . $fws_['modelo_template'];
        }else if (in_array($this->codEvento, $evArrayFrenteUnica)) {
            $modelo_template = dirname(__FILE__) . '/../view/certificados/modelos/modelo-certificado-frente-unica.php';
        }else{
            $modelo_template = dirname(__FILE__) . '/../view/certificados/modelos/' . $fws_['modelo_template'];
        }
        executarLog($modelo_template,"emissaoCertificado",true);
        include($modelo_template);
        $content = ob_get_clean();

        if (!file_exists(WB_PATH_LOG_CERTIFICADOS)) {
            mkdir(WB_PATH_LOG_CERTIFICADOS, 0755);
        }
        require_once(dirname(__FILE__) . '/../lib/vendor/autoload.php');
        // Configurar o mPDF para usar UTF-8

        $htmlContent = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        $htmlContent = $this->resolverImagensParaLocal($htmlContent, $fws_);
        if($_SERVER['REMOTE_ADDR']=='177.82.0.288'){
            dd($fws_);
            echo $htmlContent;
            exit();
        }
        $mpdf = new \Mpdf\Mpdf(['format' => 'A4','orientation' => 'L','unit'=>'mm',
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

        // $mpdf->Output(WB_PATH_LOG_CERTIFICADOS . '/' . $fws_['nomeArquivoCertificado'], 'I');
        // exit();
        $mpdf->Output(WB_PATH_LOG_CERTIFICADOS . '/' . $fws_['nomeArquivoCertificado'], 'F');
        unset($mpdf);

        //Redirecionando para anexar verso
        $fws_['c'] = 'GerenciarCertificado';
        $fws_['m'] = 'carregarVerso';
        postExternalUrl(WB_URL_COMMAND,$fws_,$debug);
        exit();
    }
    public function carregarVerso($fws_, $debug = false) {
        require_once(dirname(__FILE__) . '/../lib/vendor/autoload.php');
        $frenteFile = WB_PATH_LOG_CERTIFICADOS . $fws_['nomeArquivoCertificado'];
        if (in_array($this->codEvento, [21, 61, 70])) {
            $programaFile    = WB_PATH_LOG_CERTIFICADOS . '/verso/programa.pdf';
        }else if($fws_['cod_oficina']!='' and ($this->codEvento>=10)){
            $programaFile    = WB_PATH_LOG_CERTIFICADOS . '/verso/programa-'. numberPad($fws_['cod_oficina'], 7).'.pdf';
        }else{
            $programaFile    = WB_PATH_LOG_CERTIFICADOS . '/verso/programa.pdf';
        }
        $arquivoPDFFinal = WB_PATH_LOG_CERTIFICADOS . '/frente';
        $arquivoPDFFinal = $frenteFile;

        // echo $frenteFile.'<br>';
        // echo $programaFile.'<br>';
        // echo $arquivoPDFFinal.'<br>';
        // Carregar o arquivo da classe Fpdf

        require_once(dirname(__FILE__) . '/../lib/vendor/setasign/fpdf/Fpdf.php');
        
        if (!file_exists($frenteFile)) {
            throw new Exception("Arquivo da frente do certificado não encontrado.");
        }
        // if (!file_exists($programaFile)) {
        //     throw new Exception("Arquivo do programa não encontrado.");
        // }
        
        $mpdf = new setasign\Fpdi\Fpdi();
        $mpdf->setSourceFile($frenteFile);
        $template = $mpdf->importPage(1);
        $mpdf->AddPage('L', 'A4');
        $mpdf->useTemplate($template);
        if (file_exists($programaFile)) {
            $mpdf->setSourceFile($programaFile);
            $template = $mpdf->importPage(1);
            $mpdf->AddPage('L', 'A4');
            $mpdf->useTemplate($template);
            $mpdf->Output($arquivoPDFFinal,'F');
        }
        //Abre o arquivo gerado
        if (file_exists($arquivoPDFFinal)) {
            $nomeArquivo = pathinfo($arquivoPDFFinal, PATHINFO_BASENAME);
            header("Content-Type: application/pdf");
    //        header("Content-Disposition: attachment; filename=".$nomeArquivo);
            header("Content-Disposition: inline; filename=".$nomeArquivo);
            header("Expires: 0");
            header("Cache-Control: private");
            readfile($arquivoPDFFinal);
            //Remover o arquivo temporário
            if(!unlink($arquivoPDF)) {
                throw new Exception("Não foi possível remover o arquivo temporário");
            }
        } else {
            throw new Exception("Não foi localizado o arquivo PDF");
        } 
    }
    public function gerarCertificadoArquivo($fws_, $debug = false) {

        if($_SERVER['REMOTE_ADDR']=='177.82.0.288'){
            $debug=true;
            // error_reporting(E_ALL ^ E_NOTICE);
            // ini_set("display_errors", "On");
        }
        $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'].'-'.$fws_['cod_inscrito'].'-'.$fws_['cod_oficina'].'-01.pdf';
        $fileFinal = WB_PATH_LOG_CERTIFICADOS . $fws_['nomeArquivoCertificado'];
//        echo $fileFinal.'<br>';
//        if(file_exists($fileFinal)){
//            echo "<font color=green>Certificado Gerado</font>";
//            return true;
//        }
//        echo "<font color=red>Certificado NÃO Gerado</font>";
        
        $fws_['nomeArquivoCertificado'] = '';
        $_SESSION['_cod_inscrito'] = $fws_['cod_inscrito'];
        /* carregando texto */
        try {
            $fws_ = $this->carregarTextoCertificado($fws_,$debug);
        } catch (Exception $exc) {
            echo $exc->getMessage();
            exit();
        }
        $_SESSION['_cod_inscrito'] = NULL;
        // $this->codEvento=21;
        /* mesclando com o modelo */
        ob_start();
//        header('Content-Type: text/html; charset=ISO-8859-1');
//        header("Content-Type: application/pdf");
//        header("Content-Disposition: inline; filename=" . $fws_['nomeArquivoCertificado']);
        include(dirname(__FILE__) . '/../view/certificados/' . $this->codEvento . '/modelos/' . $fws_['modelo_template']);
        $content = ob_get_clean();

//        require_once(dirname(__FILE__).'/../lib/PDFMerger/PDFMerger.php');
        /* gerando pdf */
//        require_once(dirname(__FILE__).'/../lib/html2pdf/html2pdf.class.php');
        require_once(dirname(__FILE__)."/../lib/vendor/spipu/html2pdf/src/Html2Pdf.php");

//        $html2pdf = new HTML2PDF('L', 'A4', 'pt');
        $html2pdf = new Spipu\Html2Pdf\Html2Pdf();
        $html2pdf->pdf->SetDisplayMode("fullpage");
        $html2pdf->setTestTdInOnePage(false);
//        $html2pdf->WriteHTML($content, isset($_GET['vuehtml']));
        $html2pdf->WriteHTML($content, 0);

        if (!file_exists(WB_PATH_LOG_CERTIFICADOS)) {
            mkdir(WB_PATH_LOG_CERTIFICADOS, 0755);
        }
        /*
        I : envoyer en inline au navigateur. Le plug-in est utilis� s'il est install�. Le nom indiqu� dans name est utilis� lorsque l'on s�lectionne "Enregistrer sous" sur le lien g�n�rant le PDF.
        * D : envoyer au navigateur en for�ant le t�l�chargement, avec le nom indiqu� dans name.
        * F : sauver dans un fichier local, avec le nom indiqu� dans name (peut inclure un r�pertoire).
        * S 
         * 
         */
//        $html2pdf->Output($fws_['nomeArquivoCertificado']); // grava o arquivo em disco na primeira vez que � visualizado
        $html2pdf->Output(WB_PATH_LOG_CERTIFICADOS . '/' . $fws_['nomeArquivoCertificado'], 'F'); // grava o arquivo em disco na primeira vez que � visualizado       
        unset($html2pdf);
        // $objVerso = new GerenciarCertificadoAC();
        // $objVerso->carregarVersoArquivo($fws_, $debug);
        exit();
    }
    public function carregarVersoArquivo($fws_, $debug = false) {

        $frenteFile = WB_PATH_LOG_CERTIFICADOS . $fws_['nomeArquivoCertificado'];
        if($fws_['cod_oficina']!='' and $this->codEvento==10){
            $programaFile    = WB_PATH_LOG_CERTIFICADOS . '/verso/programa-'. numberPad($fws_['cod_oficina'], 7).'.pdf';
        }else{
            $programaFile    = WB_PATH_LOG_CERTIFICADOS . '/verso/programa.pdf';
        }
        $arquivoPDFFinal = WB_PATH_LOG_CERTIFICADOS . '/frente';
        $arquivoPDFFinal = $frenteFile;
//        echo __LINE__.'-';
        unset($html2pdf);
        unset($pdf);
        
//        require_once(dirname(__FILE__).'/../lib/PDFMerger/PDFMerger.php');
//        $pdf = new PDFMerger();
//        if (!file_exists($frenteFile)) {
//            echo "frente não exite";
//        }else{
//            $pdf->addPDF($frenteFile);
//        }
//        if (!file_exists($programaFile)) {
////            echo "verso não exite";
//        }else{
//            $pdf->addPDF($programaFile);
//        }
//        $pdf->merge("file", $arquivoPDFFinal);
        
        // and now we can use library
        require_once(dirname(__FILE__)."/../lib/vendor/jurosh/pdf-merge/src/Jurosh/PDFMerge/PDFMerger.php");
        $pdf = new Jurosh\PDFMerge\PDFMerger;
        if (!file_exists($frenteFile)) {
            echo "frente não exite";
        }else{
            $pdf->addPDF($frenteFile);
        }
        if (!file_exists($programaFile)) {
//            echo "verso não exite";
        }else{
            $pdf->addPDF($programaFile);
        }
        $pdf->merge("file", $arquivoPDFFinal);
        
        
//        unset($pdf);
        //Abre o arquivo gerado
        if (file_exists($arquivoPDFFinal)) {
            echo "Arquivo gerado com sucesso.";
            //Remover o arquivo temporário
//            if(!unlink($arquivoPDF)) {
//                echo "Não foi possível remover o arquivo temporário";
//            }
        } else {
            echo "Não foi localizado o arquivo PDF";
        } 
    }
    /**
     * @param type $fws_
     * @param type $debug
     * @return string
     * @throws Exception
     */
    function carregarTextoCertificado($fws_, $debug = false) {
        $gerarDefault = false;
        if($_SERVER['REMOTE_ADDR']=='177.82.0.288'){
            $debug = true;
        }

        $objInscrito = new Inscrito();
        if (FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_ASSOCIAR_PERFIS, 5, false, false)){
            $objInscrito->setCodInscrito($fws_['cod_inscrito']);
        }else{
            if (!$_SESSION['_cod_inscrito']) {
                throw new Exception("Sessão do usuário encerrada, retorno ao link anterior.");
            }else{
                $objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
            }
        }
        if($fws_['cod_inscrito']!='' and $fws_['cod_evento']!=''){
            $objInscrito->setCodInscrito($fws_['cod_inscrito']);
            $objInscrito->setCodEvento($fws_['cod_evento']);
        }
        $objInscrito->getInscritoById($debug);
        $objInscrito->carregarObjEvento($debug);
        $objEv = $objInscrito->getObjEvento();
        $objConf = $objEv->getEventoConfiguracao();
        
        $objPopularConf = new GerenciarConfiguracaoEventoAC();
        $objPopularConf->carregarConfiguracaoByObjEvento($this, $objEv);      
        
        $fws_['assinatura_duplicada'] = '';
        $fws_['logo_suplementar']     = '';
        /**@todo essa configuração de assinatura duplicada talvez tenha que ser movida para local mais especifico, talvez no upload do arquivo */
        if($objConf->getCertificadoAssinaturaDupla()){
            $fws_['assinatura_duplicada'] = 1;
        }
        /**@todo precisa criar a opção para upload do cabeçalho e rodapé de cada certificado ou gestor */
        if($objConf->getCertificadoSelo()){
            $fws_['logo_suplementar'] = 'view/certificados/modelos/img/gestor/logo-0030-certificado.jpg';
        }
        
        $fws_['cod_evento'] = $objInscrito->getCodEvento();
        if (!$objInscrito->getCodVoucher() and !$objInscrito->getCodIsento() and !$objInscrito->getTotalPagamentos() and !$objInscrito->getTotalPerfisIsentos()) {
            throw new Exception("Emissão de certificado bloqueada.<br>Caso necessário, entre em contato com a administração do evento para regularizar sua inscrição.");
        }

        if ($objInscrito->getNomeInscrito() == '') {
            throw new Exception("Inscrito não localizado.");
        }
                
        $fws_['nome_inscrito'] = maiusculo($objInscrito->getNomeInscrito());
        $fws_['cod_inscrito'] = $objInscrito->getCodInscrito();
        $fws_['descTipoCertificado'] = $fws_['tipoCertificado'];
        $fws_['tipoCertificado'] = removerEspacos(removerAcentos(minusculo($fws_['tipoCertificado'])));
        
        if ((substr($fws_['tipoCertificado'],-1)=='o') or (substr($fws_['tipoCertificado'],-1)=='e')){
            $this->noOuNa          = "no";
            $this->doOuDa          = "do";
            $this->relalizadoOurelalizado = "realizado";
        }else{
            $this->noOuNa          = "na";
            $this->doOuDa          = "da";
            $this->relalizadoOurelalizado = "realizada";
        }
        
        if($objConf->getCertificadoTextoNo()!=''){
            $this->noOuNa = $objConf->getCertificadoTextoNo();
        } 
        if($objConf->getCertificadoTextoDo()!=''){
            $this->doOuDa = $objConf->getCertificadoTextoDo();
        } 
        if($objConf->getCertificadoTextoReallizado()!=''){
            $this->relalizadoOurelalizado = $objConf->getCertificadoTextoReallizado();
        } 

        $this->cidadeEvento = $this->cidadeEvento? " na cidade de ".$this->cidadeEvento."," : "";

        $this->txtQualidade = 'na qualidade de';
        if($objConf->getCertificadoTextoQualidade()!=''){
            $this->txtQualidade = $objConf->getCertificadoTextoQualidade();
        }
        $this->txtConferidoA = 'CONFERIDO A';
        if($objConf->getCertificadoTextoInicial()!=''){
            $this->txtConferidoA = $objConf->getCertificadoTextoInicial();
        }
        
//        if (mb_detect_encoding($fws_['nome_inscrito'] . 'x', 'UTF-8, ISO-8859-1') == 'UTF-8') {
//            $fws_['nome_inscrito'] = utf8_decode($fws_['nome_inscrito']);
//        }
        /**
         * @todo carregar o tipo do certificado para trazer detalhes como carga horária e etç.
         */
        if($debug){
            dd($fws_);
            echo $fws_['tipoCertificado'];
            exit();
        }
        switch ($fws_['tipoCertificado']) {
            case 'apresentador_trabalho':
                $objTrabalho = new InscritoTrabalho();
                $objTrabalho->setCodTrabalho($fws_['cod_trabalho']);
                $objTrabalho->setCodInscrito($objInscrito->getCodInscrito());
                $objTrabalho->carregarTrabalhoById();

                $fws_['txt_premiacao'] = $objConf->getCertificadoTextoInicialTrabalho()? "<span style='font-weight:bold;'>".$objConf->getCertificadoTextoInicialTrabalho()."</span>" : "CONFERIDO ao TRABALHO";

                if ($objTrabalho->getTxtPremiacao()!=''){
                    $fws_['txt_premiacao']   = "<font size=3><b>". maiusculo($objTrabalho->getTxtPremiacao())."</b> Conferido ao Trabalho</font>";
                    $fws_['nivel_premiacao'] = substr($objTrabalho->getTxtPremiacao(),0,1);
                }
                                 
//                if(!$objTrabalho->getFlgAprovado()){
//                    throw new Exception("Trabalho não aprovado");
//                }
                if (!($objInscrito->getFlgPresencaInscrito() == 1)) {
                    /**@todo liberando certificado somente de trabalho */
                    if ($objInscrito->getCodEvento()!=61){
                        throw new Exception("Presenção não confirmada durante o evento.");
                    }
                }
                $textoPublicacao = false;
                /** @todo liberando certificado de publicação em anais para aprovados mas não apresentados */
                if($objConf->getCertificadoTrabalhoNaoApresentado()==1){
                    if (!( $objTrabalho->getFlgPresenca() == 1)) {
                        $textoPublicacao = true;
                        // throw new Exception("Apresentação do trabalho não confirmada.");
                    }
                }else{
                    if (!( $objTrabalho->getFlgPresenca() == 1)) {
                        throw new Exception("Apresentação do trabalho não confirmada.");
                    }
                }                
                if ($objTrabalho->getTituloTrabalho()) {
                    $fws_['titulo_trabalho'] = $objTrabalho->getTituloTrabalho();
                }
                if (!$fws_['titulo_trabalho']) {
                    throw new Exception("Inscrito não esta associado a trabalho, ou trabalho não encontrado");
                }
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '-' . $fws_['cod_trabalho'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '-' . $fws_['cod_trabalho'];

//                $fws_['coAutores'] .= $objTrabalho->getNomeCoAutor1()? " e co-autor(es)" : "";
//                if($_SERVER['REMOTE_ADDR']=='189.90.70.56'){
//                    echo minusculo($objInscrito->getNomeInscrito())."<->".minusculo($objTrabalho->getNomeCoAutor1())."<br>";
//                }

                /* para ignorar nome do inscrito e manter a ordem de cadastro de co-autores */
                $coAutores = "";
                if(COD_EVENTO==1){
                    if ($objInscrito->getCodInscrito() == '1379') {
                        $coAutores = $objTrabalho->getNomeCoAutor1();
                    } else {
                        $coAutores = $fws_['nome_inscrito'];
                    }

                    if (!@stristr(minusculo($coAutores), minusculo($objTrabalho->getNomeCoAutor1())))
                        $coAutores .= $objTrabalho->getNomeCoAutor1() ? "; " . $objTrabalho->getNomeCoAutor1() : "";
                    
                }else{
                    
                    
                    $coAutores = "<u> ".trim($fws_['nome_inscrito'])."</u>";
                    if (!@stristr(minusculo($coAutores), minusculo($objTrabalho->getAutorApresentadorNome()))){
                        $coAutores = "".trim($fws_['nome_inscrito'])."";
                        $coAutores .= $objTrabalho->getAutorApresentadorNome() ? ", <u> " . trim($objTrabalho->getAutorApresentadorNome())."</u>" : "";
                    }
                    
                    if (!@stristr(minusculo($coAutores), minusculo($objTrabalho->getNomeCoAutor1())))
                        $coAutores .= $objTrabalho->getNomeCoAutor1() ? ", " . trim($objTrabalho->getNomeCoAutor1()) : "";
                    
                }

                if (!@stristr(minusculo($coAutores), minusculo($objTrabalho->getNomeCoAutor2())))
                    $coAutores .= $objTrabalho->getNomeCoAutor2() ? ", " . trim($objTrabalho->getNomeCoAutor2()) : "";

                if (!@stristr(minusculo($coAutores), minusculo($objTrabalho->getNomeCoAutor3())))
                    $coAutores .= $objTrabalho->getNomeCoAutor3() ? ", " . trim($objTrabalho->getNomeCoAutor3()) : "";

                if (!@stristr(minusculo($coAutores), minusculo($objTrabalho->getNomeCoAutor4())))
                    $coAutores .= $objTrabalho->getNomeCoAutor4() ? ", " . trim($objTrabalho->getNomeCoAutor4()) : "";

                if (!@stristr(minusculo($coAutores), minusculo($objTrabalho->getNomeCoAutor5())))
                    $coAutores .= $objTrabalho->getNomeCoAutor5() ? ", " . trim($objTrabalho->getNomeCoAutor5()) : "";

                if (!@stristr(minusculo($coAutores), minusculo($objTrabalho->getNomeCoAutor6())))
                    $coAutores .= $objTrabalho->getNomeCoAutor6() ? ", " . trim($objTrabalho->getNomeCoAutor6()) : "";

                $fws_['coAutores'] = maiusculo($coAutores);
                $fws_['titulo_trabalho'] = str_replace("“","\"",$fws_['titulo_trabalho']);
                $fws_['titulo_trabalho'] = str_replace("”","\"",$fws_['titulo_trabalho']);
                $fws_['titulo_trabalho'] = maiusculo($fws_['titulo_trabalho']);
  
                if($textoPublicacao == true){
                    $textoApresentacaoTrabalho = "por sua publicação nos anais";
                }else{
                    $textoApresentacaoTrabalho = "por sua apresentação";
                }

                $fws_['texto_certificado'] = ""
                        . $fws_['txt_premiacao']
                        . "<br><b>'{$fws_['titulo_trabalho']}'</b>, "
                        . "<br> de autoria de <b>{$fws_['coAutores']}</b>"
                        . "<br> ".$textoApresentacaoTrabalho." ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.""
                        . "."
                        . "";

                $fws_['modelo_template'] = "modelo-certificado-apresentador-trabalho.php";
                break;
            case 'curso':           
            case 'oficina':
            case 'minicurso':
            case 'participou_atividade':
                $objInscrito->carregarListaOficinas();
                if ($objInscrito->getListaOficinas()) {
                    foreach ($objInscrito->getListaOficinas() as $objOf) {
                        if ($objOf->getCodOficina() == $fws_['cod_oficina'] and $objOf->getStatus() == 'ATIVADA') {
                            $fws_['minicurso'] = $objOf->getTituloCertificado();
                            if($objOf->getTipoRelacao()==''){

                                // $fws_['cargaHoraria'] = str_replace('HORAS','',$objOf->getCargaHoraria())." horas";
                                $fws_['cargaHoraria'] = formatarCargaHoraria($objOf->getCargaHoraria());
                                if($objOf->getCargaHorariaExtenso()!=''){
                                    $fws_['cargaHoraria'] = $objOf->getCargaHorariaExtenso();
                                }
                                $this->qualidadeDe= '';
                            }else if($objOf->getTipoRelacao()=='Ministrante'){
                                // Hierarquia: pratica/teorica (>=125) -> relacao -> evento
                                $fws_['cargaHoraria'] = $this->cargaHorariaMinistranteMonitor($objOf);
                                // qualidadeDe no padrao ", na qualidade de X" (sem virgula no fim)
                                // para nao gerar virgula dupla antes de ", com carga horaria".
                                $this->qualidadeDe= ', '.$this->txtQualidade.' <b>'. maiusculo($objOf->getTipoRelacao()).'</b>';
                            }else if($objOf->getCodPerfil()!='' and ($objOf->getTipoRelacao()=='Ministrante')){
                                // $fws_['cargaHoraria'] = str_replace('HORAS','',$objOf->getCargaHorariaPerfil())." horas";
                                $fws_['cargaHoraria'] = formatarCargaHoraria($objOf->getCargaHorariaPerfil());
                                if($objOf->getTituloPerfil()=='Comissão Organizadora'){
                                    $this->qualidadeDe= ' '.$this->txtQualidade.' mebro da <b>'. maiusculo($objOf->getTituloPerfil()).'</b>, ';
                                }else{
                                    $this->qualidadeDe= ' '.$this->txtQualidade.' <b>'. maiusculo($objOf->getTituloPerfil()).'</b>, ';
                                }
                            }else if(($objOf->getTipoRelacao()=='Coordenador(a)')or($objOf->getTipoRelacao()=='Vice-coordenador(a)')){
                                // Cascata de carga horaria: ligacao (inscrito<->perfil/atividade)
                                // -> perfil -> evento/atividade. Usa a primeira informada;
                                // se vazia, cai para a proxima. Coordenador/vice geralmente
                                // nao tem carga horaria individual na ligacao.
                                if (trim($objOf->getCargaHorariaInscrito()) != '') {
                                    $fws_['cargaHoraria'] = formatarCargaHoraria($objOf->getCargaHorariaInscrito());
                                } elseif (trim($objOf->getCargaHorariaPerfil()) != '') {
                                    $fws_['cargaHoraria'] = formatarCargaHoraria($objOf->getCargaHorariaPerfil());
                                } else {
                                    // Evento/atividade: getCargaHorariaOficina (alias da query principal)
                                    // ou getCargaHoraria (queries sem alias, ex: evento 49).
                                    $chEvento = trim($objOf->getCargaHorariaOficina()) != '' ? $objOf->getCargaHorariaOficina() : $objOf->getCargaHoraria();
                                    $fws_['cargaHoraria'] = formatarCargaHoraria($chEvento);
                                }
                                if($objOf->getTipoRelacao()=='Comissão Organizadora'){
                                    $this->qualidadeDe= ' '.$this->txtQualidade.' mebro da <b>'. maiusculo($objOf->getTituloPerfil()).'</b>, ';
                                }else{
                                    $this->qualidadeDe= ' '.$this->txtQualidade.' <b>'. maiusculo($objOf->getTipoRelacao()).'</b>, ';
                                }
                            }else{
                                /*
                                ob_start();
                                var_dump($objOf);
                                $saida = ob_get_clean();
                                executarLog("Certificado Texto: Tipo de relação não identificado: ".$objOf->getTipoRelacao()." - Cod Oficina: ".$objOf->getCodOficina()." - Cod Evento: ".$objOf->getCodEvento().$saida,'CertifficadoAC',true);
                                */
                                // $fws_['cargaHoraria'] = str_replace('HORAS','',$objOf->getCargaHoraria())." horas";
                                if($_SERVER['REMOTE_ADDR']=='177.82.0.288'){
                                    dd($objOf);
                                    exit();
                                }
                                if (trim($objOf->getTipoRelacao()) == 'Monitor' && (int) $objOf->getCodEvento() >= 125) {
                                    // Monitor (eventos >= 125): mesma hierarquia do ministrante
                                    // (pratica/teorica -> relacao -> evento). Tem prioridade sobre o
                                    // extenso (que pode trazer a carga da oficina e mascarar a do relacionamento).
                                    $fws_['cargaHoraria'] = $this->cargaHorariaMinistranteMonitor($objOf);
                                } elseif (trim($objOf->getCargaHorariaExtenso()) != '') {
                                    $fws_['cargaHoraria'] = $objOf->getCargaHorariaExtenso();
                                } elseif (trim($objOf->getCargaHorariaInscrito()) != '') {
                                    $fws_['cargaHoraria'] = formatarCargaHoraria($objOf->getCargaHorariaInscrito());
                                } else {
                                    $fws_['cargaHoraria'] = formatarCargaHoraria($objOf->getCargaHorariaOficina());
                                }

                                $this->qualidadeDe= '';
                                
                                if ((in_array($objOf->getCodEvento(), [21, 61]))or $objConf->getCertificadoExibirQualidade()==1) {
                                    $this->qualidadeDe = ', '.$this->txtQualidade.' <b>'. maiusculo($objOf->getTipoRelacao()).'</b>';
                                }
                                $fws_['frequencia'] = $objOf->getFrequencia()? ', obtendo '.$objOf->getFrequencia().'% de frequência' : '';
                            }
                            
                            $this->qualificacaoCertificado = $objOf->getQualificacaoCertificado()? $objOf->getQualificacaoCertificado() : $this->qualificacaoCertificado;
                            $this->periodoEvento  = $objOf->getPeriodoCertificado()? $objOf->getPeriodoCertificado() : $this->periodoEvento;
                            $this->dataEmissao    = $objOf->getDataEmissaoCertificado()? $objOf->getDataEmissaoCertificado() : $this->dataEmissao;

                            // Local da atividade PREVALECE sobre a cidade do evento no
                            // texto do certificado (mesmo criterio da data de emissao,
                            // linha ~875). Antes disto o valor ia para $this->realizadoEm,
                            // variavel que nunca era lida em lugar nenhum do template --
                            // so $this->cidadeEvento (formatada acima, "na cidade de X,")
                            // entra nas frases "realizado(a)$cidadeEvento no periodo de...".
                            if ($objOf->getLocal() != '') {
                                $this->cidadeEvento = ' na cidade de ' . $objOf->getLocal() . ',';
                            }


                            if (!$fws_['minicurso']) {
                                throw new Exception("Inscrito não esta associado a " . DESC_ATIVIDADE . ".");
                            }
            
                            if ($fws_['cod_oficina']) {
                                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '-' . $fws_['cod_oficina'] . '.pdf';
                                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '-' . $fws_['cod_oficina'];
                            } else {
                                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                            }
            
                            $fws_['durante'] = "<br> durante a ".$this->nomeEvento.",";
                            $fws_['durante'] = "";
                            if ($objConf->getCodEvento()==70){
                                $fws_['durante'] = " durante o ".$this->nomeEvento.", ";
                            }
                                    
            //                $this->periodoEvento = " 19 de Setembro a 20 de Outubro de 2022";
            //                $this->dataEmissao = "Londrina (PR), 20 de Outubro de 2022.";
                            if(($objOf->getTipoRelacao()=='Coordenador(a)')or($objOf->getTipoRelacao()=='Vice-coordenador(a)')){
                                $fws_['tipo_atividade'] = $objOf->getTipo();
                                $tipo_atividade = strtolower($objOf->getTipo());
                                $minicursoString = strtolower($fws_['minicurso']);
                                if (strpos($minicursoString, $tipo_atividade) !== false) {
                                    $fws_['tipo_atividade'] = '';
                                }
            
                                $fws_['texto_certificado'] = $this->txtConferidoA
                                        . "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>"
                                        . "<br>o Certificado de  <b>". maiusculo($objOf->getTipoRelacao())."</b>, ".$this->doOuDa." ". strtolower($fws_['tipo_atividade'])." "
                                        . "<br><b>'{$fws_['minicurso']}'</b>,<br>"
                                        . $fws_['durante']
                                        . "".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.""
                                        . ", com carga horária total de ".$fws_['cargaHoraria']
                                        . $fws_['frequencia']
                                        .".";
                            }else{
                                if ($this->qualificacaoCertificado!=''){
                                    $fws_['texto_certificado'] = $this->txtConferidoA
                                        . "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>"
                                        . "<br>O Certificado  ".$this->qualificacaoCertificado." "
                                        . "<br><b>'{$fws_['minicurso']}'</b>,<br>"
                                        . $fws_['durante']
                                        . "".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.""
                                        . $this->qualidadeDe
                                        . ", com carga horária total de ".$fws_['cargaHoraria']
                                        . $fws_['frequencia']
                                        ."."
                                        . "";
                                }else if(($objOf->getTipoRelacao()!='')){
                                    $fws_['tipo_atividade'] = $objOf->getTipo();
                                    $tipo_atividade = strtolower($objOf->getTipo());
                                    $minicursoString = strtolower($fws_['minicurso']);
                                    if (strpos($minicursoString, $tipo_atividade) !== false) {
                                        $fws_['tipo_atividade'] = '';
                                    }
            
                                    $fws_['texto_certificado'] = $this->txtConferidoA
                                        . "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>"
                                        . "<br>O Certificado de  <b>". maiusculo($objOf->getTipoRelacao())."</b>, ".$this->doOuDa." ". strtolower($fws_['tipo_atividade'])." "
                                        . "<br><b>'{$fws_['minicurso']}'</b>,<br>"
                                        . $fws_['durante']
                                        . "".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.""
                                        . ", com carga horária total de ".$fws_['cargaHoraria']
                                        . $fws_['frequencia']
                                        .".";
                                }else {
                                    $fws_['tipo_atividade'] = $objOf->getTipo();
                                    $tipo_atividade = strtolower($objOf->getTipo());
                                    $minicursoString = strtolower($fws_['minicurso']);
                                    if (strpos($minicursoString, $tipo_atividade) !== false) {
                                        $fws_['tipo_atividade'] = '';
                                    }
            
                                    $fws_['texto_certificado'] = $this->txtConferidoA
                                        . "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>"
                                        . "<br>O Certificado de  <b>PARTICIPANTE</b>, ".$this->doOuDa." ". strtolower($fws_['tipo_atividade'])." "
                                        . "<br><b>'{$fws_['minicurso']}'</b>,<br>"
                                        . $fws_['durante']
                                        . "".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.""
                                        . ", com carga horária total de ".$fws_['cargaHoraria']
                                        . $fws_['frequencia']
                                        .".";
                                }
                            }
            
                            $fws_['modelo_template'] = "modelo-certificado-minicurso.php";                            
                        }
                    }
                }
                break;
            case 'congressista':
            case 'participante':
                // Bloqueio POR PESSOA do certificado GERAL de participacao (opt-out).
                // Gate AUTORITATIVO: barra a geracao direta (via URL) mesmo quando o
                // link nao aparece na area do inscrito. NULL/0 = recebe (comportamento antigo).
                if ($this->pessoaBloqueadaCertificadoGeral($fws_['cod_inscrito'])) {
                    throw new Exception("Certificado de participação não disponível para esta inscrição.");
                }
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . " ".$this->txtQualidade." ";
          
                        if($objConf->getCertificadoCongressistaQualidade()!=''){
                            $fws_['texto_certificado'] .= "<br>".$objConf->getCertificadoCongressistaQualidade();
                        }else{
                            $fws_['texto_certificado'] .= "<br>MEMBRO EFETIVO ";
                        }
                        if($objConf->getCertificadoCongressistaCargaHoraria()!=''){
                            $fws_['texto_certificado'] .= "<br>".$objConf->getCertificadoCongressistaCargaHoraria();                      
                        }else{
                            $fws_['texto_certificado'] .= "<br>Carga Horária: 15 horas";
                        }
                        

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'equipedeapoio':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . "<br> como membro da <b>Equipe de Apoio</b>.";

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'monitor':
            case 'monitor(a)':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . " na condição de "
                        . "<br>Monitor(a) "
                        . "<br>Carga Horária: 30 horas.";

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'diretoria':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . "<br> como membro da <b>Diretoria </b>.";

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'membrodacomissaoorganizadora':
                if($objEv->getCodEvento()!=98){
                    $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                    $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                    $fws_['texto_certificado'] = "";
                            $fws_['texto_certificado'] = $this->txtConferidoA;
                            $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                            . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                            . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                            . "<br> como membro da <b>Comissão Organizadora</b>";
                            if($this->codEvento==70){
                                
                            }else{
                                $fws_['texto_certificado'] .= "<br>Carga Horária: 40 horas.";
                            }

                    $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                    break;
                } else {
                    $gerarDefault = true;
                    break;
                }
            case 'moderador':
            case 'moderador(a)':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . "<br> ".$this->txtQualidade." <b>Moderador(a) </b>.";

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'comitecientifico':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . "<br> como membro do <b>Comitê Científico</b>.";

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'avaliador':
            case 'avaliadordetrabalhocientifico':
            case 'avaliador(a)detrabalhocientifico':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . " na condição de "
                        . "<br><b>Avaliador(a) de Trabalho Científico</b> ";
                        if($this->codEvento==70){
                            
                        }else{
                            $fws_['texto_certificado'] .= "<br>Carga Horária: 20 horas.";
                        }

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'coordenadores':
            case 'coordenador':
            case 'coordenador(a)':
            case 'Coordenador do Comitê Científico':
                
                $tipoQualidade = textoMaiusculoMinusculo($fws_['tipoCertificado']);
                
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . "<br> ".$this->txtQualidade." <b>".$tipoQualidade."</b>.";

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'coordenadorageral':
            case 'coordenadorgeral':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . "<br> ".$this->txtQualidade." <b>Coordenador(a) Geral</b>.";

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
//            case 'coordenadora':
//                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
//                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
//                $fws_['texto_certificado'] = ""
//                        . "CONFERIDO A"
//                        . "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
//                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
//                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
//                        . "<br> ".$this->txtQualidade."  <b>COORDENADORA</b>.";
//
//                $fws_['modelo_template'] = "modelo-certificado-minicurso.php";
//                break;
            case 'coordenador(a)desessao':
            case 'coordenadordesessao':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","                                
                        . "<br> ".$this->txtQualidade." <b>Coordenador(a) de Sessão</b>.";
                        if($this->codEvento==70){
                            
                        }else{
                            $fws_['texto_certificado'] .= "<br>Carga Horária: 12 horas.";
                        }

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'conferencista':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . "<br> ".$this->txtQualidade." <b>Conferencista</b>.";
                        if($this->codEvento==70){
                            
                        }else{
                            $fws_['texto_certificado'] .= "<br>Carga Horária: 12 horas.";
                        }

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'ministrante(a)deminicurso':
            case 'ministrantedeminicurso':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . "<br> ".$this->txtQualidade." <b>Ministrante(a) de Minicurso</b>.";
                        if($this->codEvento==70){
                            
                        }else{
                            $fws_['texto_certificado'] .= "<br>Carga Horária: 12 horas.";
                        }

                $fws_['modelo_template'] = "modelo-certificado-minicurso.php";
                break;
            case 'coordenador(a)deminicurso':
                $objInscrito->setCodPerfil('60');
                $txtAtividade = "";
                try {
                    $objInscrito->carregarListaMinistrantesOficinas();
                    if ($objInscrito->getListaMinistrantesOficinas()) {
                        foreach ($objInscrito->getListaMinistrantesOficinas() as $objOf) {
//                            if ($objOf->getCodOficina() == $fws_['cod_oficina'] and $objOf->getStatus() == 'ATIVADA') {
                                $txtAtividade = "<br> do ".minusculo($objOf->getTipo())." \"". $objOf->getTitulo()."\"";
//                            }
                        }
                    }
                } catch (Exception $exc) { 
//                    echo $exc->getMessage();
                }
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . "<br> ".$this->txtQualidade." <b>Coordenador(a) de Minicurso</b>"
                        . $txtAtividade;        
                        if($this->codEvento==70){
                            
                        }else{
                            $fws_['texto_certificado'] .= "<br>Carga Horária: 12 horas.";
                        }

                $fws_['modelo_template'] = "modelo-certificado-minicurso.php";
                break;
            case 'palestrante':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . "<br> ".$this->txtQualidade." <b>Palestrante</b>.";
                        if($this->codEvento==70){
                            
                        }else{
                            $fws_['texto_certificado'] .= "<br>Carga Horária: 12 horas.";
                        }

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'painelista':
//                $objInscrito->carregarListaOficinas();
//                if ($objInscrito->getListaOficinas()) {
//                    foreach ($objInscrito->getListaOficinas() as $objOf) {
//                        if ($objOf->getCodOficina() == $fws_['cod_oficina'] and $objOf->getStatus() == 'ATIVADA') {
//                            $fws_['minicurso'] = $objOf->getTitulo();
//                        }
//                    }
//                }
                if(in_array($fws_['cod_inscrito'], array(5430))){
                    $titulo_painel = "\"Cuidados Paliativos, a Atenção Primária à Saúde e a Pandemia: experiências e aprendizados\"";
                }
                
                if($titulo_painel!=''){
                    $txtPainel = "<br> no painel ".$titulo_painel;
                }
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . "<br> ".$this->txtQualidade." <b>Painelista</b>"
                        . $txtPainel;
                        if($this->codEvento==70){
                            
                        }else{
                            $fws_['texto_certificado'] .= "<br>Carga Horária: 12 horas.";
                        }

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'coordenadordepainel':
            case 'coordenador(a)depainel':
//                $objInscrito->carregarListaOficinas();
//                if ($objInscrito->getListaOficinas()) {
//                    foreach ($objInscrito->getListaOficinas() as $objOf) {
//                        if ($objOf->getCodOficina() == $fws_['cod_oficina'] and $objOf->getStatus() == 'ATIVADA') {
//                            $fws_['minicurso'] = $objOf->getTitulo();
//                        }
//                    }
//                }
                if(in_array($fws_['cod_inscrito'], array(5430))){
                    $titulo_painel = "\"Cuidados Paliativos, a Atenção Primária à Saúde e a Pandemia: experiências e aprendizados\"";
                }

                if($titulo_painel!=''){
                    $txtPainel = "<br> no painel ".$titulo_painel;
                }
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.","
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . "<br> ".$this->txtQualidade." <b>Coordenador(a) de Painel</b> "
                        . $txtPainel;
                        if($this->codEvento==70){
                            
                        }else{
                            $fws_['texto_certificado'] .= "<br>Carga Horária: 12 horas.";
                        }

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'comissaoorganizadora':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.""
                        . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . "<br> como membro da <b>Comissão Organizadora</b>.";

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
            case 'expositordestand':
                $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
                $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
                $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;
                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                        . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.""
                         . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                        . "<br> como <b>Expositor de Stand</b>.";

                $fws_['modelo_template'] = "modelo-certificado-congressista.php";
                break;
//            case 'comite cientifico':
//                
//                $objInscrito->carregarListaPerfil($condicoes = false, $debug);
//                if($objInscrito->getListaPerfil()){
//                    foreach ($objInscrito->getListaPerfil() as $objPerfil) {
//                        if(strtolower($objPerfil->getTitulo())=='comite cientifico'){
//
//                        $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] .'.pdf';
//                        $fws_['nomeArquivoValidacao']   = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
//                        $fws_['texto_certificado'] = ""
//                        . "CONFERIDO A"
//                        . "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
//                        . "<br> participou como Membro do Comite Científico no 9º Congresso Brasileiro de Melhoramento de Plantas (CBMP),"
//                        . "<br> com carga horária total de 4 horas.";
//
//                        $fws_['modelo_template'] = "modelo-certificado-congressista.php";
//                            
//                        }
//                    }
//                }
//
//                break;
            default:
                $fws_ = $this->gerarCertificadoDefault($fws_, $objInscrito, $debug);
                break;
        }
        if($_SERVER['REMOTE_ADDR']=='177.82.0.288'){
            dd($fws_);
            exit();
        }
        if($gerarDefault == true) {
            $fws_ = $this->gerarCertificadoDefault($fws_, $objInscrito, $debug);
        }
        /**@todo trazer data de emissão da configuração do certificado */
        // Quando o certificado e DA ATIVIDADE (case oficina/minicurso, ~linha 875)
        // e a atividade tem data_emissao_certificado proprio, ele PREVALECE sobre
        // o padrao do evento -- os overrides abaixo (config + por-evento fixo) so
        // se aplicam quando a atividade nao trouxe uma data.
        $dataEmissaoDaAtividadePrevalece = trim((string) $this->dataEmissao) !== '';
        if (!$dataEmissaoDaAtividadePrevalece) {
            $data = $objConf->getCertificadoDataEmissao(); // YYYY-MM-DD
            if (!empty($data)) {
                // formatDateViewPorExtenso() ja monta "DD de <mes-pt> de YYYY" via
                // getMonthName() (independente de locale). O 2o parametro false evita o
                // " as HH horas e MM minutos" quando a data vem com hora.
                $this->dataEmissao = 'Londrina (PR), ' . formatDateViewPorExtenso($data, false);
            }
            if($objEv->getCodEvento()==61){
                $this->dataEmissao = "Londrina (PR), 02 de Agosto de 2024";
            }
            if($objEv->getCodEvento()==95){
                $this->dataEmissao = "Londrina (PR), 20 de Outubro de 2025";
            }
            if (in_array($objEv->getCodEvento(), [92])){
                $this->dataEmissao = "Londrina (PR), 22 de Agosto de 2025";
            }
            if (in_array($objEv->getCodEvento(), [103])){
                $this->dataEmissao = "Londrina (PR), 31 de Outubro de 2025";
            }
            if (in_array($objEv->getCodEvento(), [109])){
                $this->dataEmissao = "Londrina (PR), 06 de Novembro de 2025";
            }
        }
        $fws_['data_emissao'] = $this->dataEmissao;
        $fws_['link_validacao'] = WB_URL_VALIDAR_CERTIFICADO . '/?id=' . $fws_['nomeArquivoValidacao'];
        $fws_['assinatura'] = PRESIDENTE . '<br>Presidente do ' . DESC_EVENTO;

        /* Promovido por (config do certificado): aparece so quando preenchido; vale para todos os tipos */
        if (isset($fws_['texto_certificado']) and trim($objConf->getCertificadoPromovidoPor()) != '') {
            $fws_['texto_certificado'] .= "<br>Promovido por: " . $objConf->getCertificadoPromovidoPor();
        }
        /* ------------------------------------------------------------------
         * Modelo CONFIGURAVEL da frente (tab_certificado_modelo).
         *
         * Unico ponto de override: as ~24 atribuicoes de modelo_template acima
         * continuam valendo como padrao. So troca de modelo quando o evento (ou
         * o perfil) apontou um. Sem modelo configurado, nada muda -- e por isso
         * que nenhum evento que ja emite certificado e afetado.
         * ------------------------------------------------------------------ */
        try {
            if ($this->modeloCertificadoForcado) {
                // PREVIEW do editor: usa o modelo montado a partir do formulario
                // (valores ainda NAO salvos) e grava com nome reservado, para
                // nunca sobrescrever o certificado real de ninguem.
                $fws_['objModeloCertificado']   = $this->modeloCertificadoForcado;
                $fws_['modelo_template']        = 'modelo-certificado-configuravel.php';
                $fws_['nomeArquivoCertificado'] = 'preview-modelo-0000000.pdf';
            } else {
                $objModelo = new CertificadoModelo();
                $objModelo->setCodEvento($fws_['cod_evento']);
                $codModelo = $objModelo->resolverModelo($objInscrito->getCodPerfil(), $debug);
                if ($codModelo) {
                    $objModelo->setCodCertificadoModelo($codModelo);
                    if ($objModelo->carregarPorCod($debug)) {
                        $fws_['objModeloCertificado'] = $objModelo;
                        $fws_['modelo_template']      = 'modelo-certificado-configuravel.php';
                    }
                }
            }
        } catch (Exception $e) {
            // Falha ao resolver modelo nao pode impedir a emissao: o certificado
            // sai no layout de sempre.
        }

        if ($_SERVER['REMOTE_ADDR']=='177.82.0.288') {
            echo '->'.$fws_['texto_certificado'].'<-';
            dd($fws_);
            exit();
        }
        return $fws_;
    }

    public function gerarCertificadoDefault($fws_, $objInscrito, $debug) {
        if ($_SERVER['REMOTE_ADDR']=='177.82.0288') {
            $debug = true;
        }
        if($debug){ 
            echo __LINE__ . " - Tipo de certificado (".$fws_['tipoCertificado'].") não localizado. Para gerar este documento ";
        }
        /*Default para todos tipos de certificado de comissão*/
        $tipoCertificado = $fws_['tipoCertificado'];
        $padrao = "/comissao/";
        // if ((preg_match($padrao, $tipoCertificado))
        //         or($fws_['tipoCertificado']=='coordenador(a)deeixotematico')
        //         or($fws_['tipoCertificado']=='mediador(a)demesaredonda')
        //     ) {
            echo $debug? __LINE__.'-' : '';
            $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'] . '.pdf';
            $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito'];
            if (in_array($objInscrito->getCodEvento(), [21, 61])) {
                $this->noOuNa= "no";
            }
            echo $debug? __LINE__.'-' : '';
            $objInscrito->carregarListaPerfil($condicoes = false, $debug);
            echo $debug? __LINE__.'-' : '';
            if($objInscrito->getListaPerfil()){
                echo $debug? __LINE__.'-' : '';
                foreach ($objInscrito->getListaPerfil() as $objPerfil) {
                    echo $debug? __LINE__.'-' : '';
                    if ($debug){
                        echo "<br>Perfil: fws_['cod_perfil'] ->" . $fws_['cod_perfil'].'<-';
                        echo "<br>Perfil: objPerfil->getStatus() ->" . $objPerfil->getStatus().'<-';
                        echo "<br>Perfil: objPerfil->getLiberarCertificado() ->" . $objPerfil->getLiberarCertificado().'<-';
                        //dd($objPerfil);
                    }
                    if ($objPerfil->getCodPerfil() == $fws_['cod_perfil'] and trim($objPerfil->getStatus()) == 'ATIVADA' and $objPerfil->getLiberarCertificado() == true) {   
                        /** @todo Gerar Certificado, acrescentar um campo no perfil para titulo para Certificado igual temos no cadastro de oficinas */
                        if($objPerfil->getTitulo()!=''){
                            $fws_['descTipoCertificado'] = $objPerfil->getTitulo();
                        }
                        echo $debug? __LINE__.'-' : '';               
                        $fws_['nomeArquivoCertificado'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito']. '-' . $fws_['cod_perfil'] . '.pdf';
                        $fws_['nomeArquivoValidacao'] = $fws_['tipoCertificado'] . '-' . $fws_['cod_inscrito']. '-' . $fws_['cod_perfil'];

                        $fws_['texto_certificado'] = "";
                        $fws_['texto_certificado'] = $this->txtConferidoA;                               

                        $fws_['texto_certificado'] .= "<br><span style='font-size:20px;font-weight:bold;'>{$fws_['nome_inscrito']}</span>,"
                            . "<br> por sua participação ".$this->noOuNa." ".$this->nomeEvento.""
                                . "<br> ".$this->relalizadoOurelalizado."".$this->cidadeEvento." no período de ".$this->periodoEvento.","
                            . "<br> ".$this->txtQualidade." <b>".$fws_['descTipoCertificado']."</b>";
                            /* Carga horaria da PESSOA neste perfil (colunas NOVAS de
                             * tab_lig_inscritos_perfis, que chegam via SELECT lp.*).
                             *
                             * SEGURANCA CONTRA REGRESSAO: essas colunas nasceram NULL em
                             * TODOS os registros existentes. Enquanto a pessoa nao tiver
                             * carga propria na ligacao, o codigo cai no ELSE abaixo, que e
                             * uma copia LITERAL do comportamento antigo (padrao do perfil,
                             * carga_horaria_certificado). Ou seja: nenhum certificado que
                             * ja era emitido muda de texto.
                             *
                             * O caminho NOVO so roda para quem foi cadastrado com carga na
                             * ligacao do perfil (ex.: pelo modal de "Adicionar inscrito").
                             * Ai teorica/pratica entram, no mesmo padrao do ministrante por
                             * oficina (cargaHorariaMinistranteMonitor). */
                            $chTeorica  = trim((string) $objPerfil->getCargaHorariaTeorica());
                            $chPratica  = trim((string) $objPerfil->getCargaHorariaPratica());
                            $chTotalLig = trim((string) $objPerfil->getCargaHoraria());

                            if ($chTeorica != '' || $chPratica != '' || $chTotalLig != '') {
                                // A ligacao (tab_lig_inscritos_perfis) tem carga propria ->
                                // SUBSTITUI COMPLETAMENTE a carga do perfil. Aqui NAO se usa
                                // getCargaHorariaCertificado() de jeito nenhum: se a pessoa
                                // fez so teorica, sai so teorica; se so o total, so o total.
                                // formatarCargaHoraria resolve a unidade ("24h"->"24h",
                                // "24"->"24 horas"), por isso sem " horas" fixo.
                                $partesCH = array();
                                if ($chTeorica != '') { $partesCH[] = formatarCargaHoraria($chTeorica) . " teóricas"; }
                                if ($chPratica != '') { $partesCH[] = formatarCargaHoraria($chPratica) . " práticas"; }

                                if ($chTotalLig != '' && !empty($partesCH)) {
                                    // total + detalhe teorica/pratica
                                    $fws_['texto_certificado'] .= "<br> com carga horária total de "
                                        .formatarCargaHoraria($chTotalLig)." (".implode(' e ', $partesCH).").";
                                } elseif ($chTotalLig != '') {
                                    // so o total da ligacao
                                    $fws_['texto_certificado'] .= "<br> com carga horária total de ".formatarCargaHoraria($chTotalLig).".";
                                } else {
                                    // so teorica e/ou pratica (sem total) -- nunca cai no perfil
                                    $fws_['texto_certificado'] .= "<br> com carga horária de ".implode(' e ', $partesCH).".";
                                }
                            } else {
                                // Ligacao VAZIA -> carga do perfil. Copia LITERAL do original
                                // (regressao zero: e o que todo certificado ja emitido usa).
                                if($objPerfil->getCargaHorariaCertificado()!=''){
                                    $fws_['texto_certificado'] .= "<br> com carga horária total de ".$objPerfil->getCargaHorariaCertificado()." horas.";
                                }
                            }

                        $fws_['modelo_template'] = "modelo-certificado-congressista.php";

                    }
                }
            }
        // }else{
        //     throw new Exception("Tipo de certificado (".$fws_['tipoCertificado'].") não localizado. Para gerar este documento novamente"
        //         . " é necessário voltar ao sistema. "
        //         . " <a href='".WB_URL_CERTIFICADO."'>Clique aqui para continuar</a>.");
        // }
        return $fws_;
    }

    public static function linkOld() {
        ?>
        <a href="../certificados/certificado.php" target='_blank'>
            <img style="margin-right:2px;" src="../imgs/icones/certificado-2.jpg" border="0" width="40px" align="left"/>
            <font size="3">
            <b>Certificado</b><br>Clique aqui para visualizar e imprimir o certificado.
            <br>
            </font></a>
        <?php
    }

    public static function link() {
        ?>
        <p style="text-align: justify; ">Para acessar, entre no link</p>
        <p style="text-align: justify; ">Além do número do CPF, será solicitado o número de inscrição no EVENTO,
            o qual poderá ser recuperado com a opção&nbsp;</p>
        <p style="text-align: justify; ">Em caso de dúvidas, favor entrar em contato</p>
        <?php
    }

    public function validarCertificado($fws_, $debug = false) {

        // if($_SERVER['REMOTE_ADDR']=='177.82.0.28'){
            $textoLog = "Entrou em Validar Certificado";
            $idArquivo = removerEspacos(removerAcentos(minusculo($fws_['idArquivo'])));
            // 1) Extrair números do idArquivo
            $tokens = explode('-', $idArquivo);
            $nums   = array_values(array_filter($tokens, static function($t){ return ctype_digit($t); }));
            $cod_inscrito = null;
            $cod_evento   = null;
            if (count($nums) >= 1) {
                // penúltimo = cod_inscrito (se existir), senão o único número
                $cod_inscrito = (int)($nums[count($nums) >= 2 ? count($nums)-2 : 0]);
            }
            try {
                // echo $cod_inscrito.'-<br>';
                $objInscrito = new Inscrito();
                $objInscrito->setCodInscrito($cod_inscrito);
                $objInscrito->getInscritoByIdSemEvento();
                $cod_evento = $objInscrito->getCodEvento();
            } catch (Exception $exc) {
                if ($debug) {
                    echo $exc->getMessage();
                    $textoLog .= " Erro - ". $exc->getMessage();
                }
            }
            // echo $cod_evento.'-<br>';
            // exit();
            // $filepath = WB_PATH_LOG_CERTIFICADOS;
            $filepath = WB_ROOT . '../logn/eventos/'.str_pad($cod_evento, '3', "0", STR_PAD_LEFT).'/certificados/';
            $arquivo = $idArquivo . ".pdf";
            
            //    echo $filepath.$arquivo."<br>";
            //    exit();

            executarLog($textoLog,"validarCertificado",true);
    //     }else{   
    //         echo "Certificado Validado";   
    //         exit();   
    //         $idArquivo = removerEspacos(removerAcentos(minusculo($fws_['idArquivo'])));
    //         $filepath = WB_PATH_LOG_CERTIFICADOS;
    //         $arquivo = $idArquivo . ".pdf";
            
    //         $textoLogo = "Entrou em Validar Certificado - ".$arquivo;
    //         executarLog($textoLogo,"validarCertificado",true);
    // //        echo $filepath.$arquivo."<br>";
    // //        exit();
    //     }
        if (file_exists($filepath . $arquivo)) {
            $textoLogo = "Econtrou para Validar Certificado - ".$arquivo;
            executarLog($textoLogo,"validarCertificado",true);            

            header("Content-Description: File Transfer");
            header("Cache-Control: private"); // work-around for IE
            header("Content-Type: application/pdf");
//            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=\"" . $arquivo . "\"");
            header("Content-Length: " . @filesize($filepath . $arquivo));
            header("Pragma: public");
            header("Expires: 0");
            readfile($filepath . $arquivo) or die();
        } else 
        if (file_exists(WB_ROOT . '../logn/eventos/010/certificados/' . $arquivo)) {
            $filepath = WB_ROOT . '../logn/eventos/010/certificados/';
            $textoLogo = "Econtrou para Validar Certificado - ".$arquivo;
            executarLog($textoLogo,"validarCertificado",true);            

            header("Content-Description: File Transfer");
            header("Cache-Control: private"); // work-around for IE
            header("Content-Type: application/pdf");
//            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=\"" . $arquivo . "\"");
            header("Content-Length: " . @filesize($filepath . $arquivo));
            header("Pragma: public");
            header("Expires: 0");
            readfile($filepath . $arquivo) or die();
        } else {
            $textoLogo = "NÃO Econtrou para Validar Certificado - ".$arquivo;
            executarLog($textoLogo,"validarCertificado",true);            

            // echo "<script language=\"Javascript\"> alert('Arquivo não existe!'); window.location='" . WB_URL_VIEW . "';</script>";
        }
    }

    /**
     * 
     * @param Trabalho $obj
     * @param type $retorno BOOLEAN, MSG
     * @param type $debug
     */
    public static function comunicadoCertificadoLiberado($obj, $retorno = 'BOOLEAN', $debug = false) {

        $msgTo = '';
        /** Atribui o assunto */
        $msgSubject = ABREV_EVENTO . " - Comunicado de Liberação de Certificados";
        $msgBody = '';
        /** Monta o corpo da mensagem */
        $msgBody = "";
        $msgBody .= $msgSubject . "\r";
        $msgBody .= "Ola: " . $obj->getNomeInscrito() . "\r\r";
        $msgBody .= "Os certificados do evento já estão liberados.\r";
        $msgBody .= "Para emissão de certificados acesse sua área de inscrito.\r\r";
        $msgBody .= "Login: {$obj->getEmailInscrito()}\r";
        $msgBody .= "Senha: {$obj->getCodInscrito()}\r";
        $msgBody .= WB_URL_CERTIFICADO."\r\r";
        $msgBody .= "Esta é uma mensagem automática, favor não responder.\r";
        $msgBody .= "att. Comissão Organizadora do " . ABREV_EVENTO . "\r\n";
        $msgBody .= "Dúvidas: " . EMAIL_CONTATO . "\r\n";
        $msgBody .= "";

        if ($debug) {
            //echo $msgBody . "<hr>";
        }

        /** Envia o e-mail */
        $msgTo = $obj->getEmailInscrito();
        //$msgTo = "contato@opctec.com.br";
//        $enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);
        GerenciarEmailAC::enviarEmail($msgTo, $subject, $message);
        //$enviou = $enviarEmail();
        //$enviou = false;
        if (!$enviou) {
            if ($retorno == 'BOOLEAN') {
                return false;
            } else {
                echo "<font color='red'><b>E-mail não enviado</b></font><br>";
            }
        } else {
            if ($retorno == 'BOOLEAN') {
                return true;
            } else {
                echo "E-mail enviado - {$obj->getEmailInscrito()}<br>";
            }
        }
    }

    /**
     * 
     * @param Trabalho $obj
     * @param type $retorno BOOLEAN, MSG
     * @param type $debug
     */
    public static function comunicadoIsentos($obj, $retorno = 'BOOLEAN', $debug = false) {

        $msgTo = '';
        /** Atribui o assunto */
        $msgSubject = ABREV_EVENTO . " - Comunicado de Cadastramento de Isentos";
        $msgBody = '';
        /** Monta o corpo da mensagem */
        $msgBody = "";
        $msgBody .= $msgSubject . "\r";
        $msgBody .= ".\r\r";
        $msgBody .= "Ola: " . $obj->getNomeInscrito() . "\r\r";
        $msgBody .= "Sua inscrição para o " . ABREV_EVENTO . ", foi efetivada automáticamente após confirmação de sua isenção.\r";
        $msgBody .= "Login: {$obj->getEmailInscrito()}\r";
        $msgBody .= "Senha: {$obj->getCodInscrito()}\r";
        $msgBody .= URL_OFICIAL."/\r\r";
        $msgBody .= "Esta é uma mensagem automática, favor não responder.\r";
        $msgBody .= "att. Comissão Organizadora do " . ABREV_EVENTO . "\r\n";
        $msgBody .= "Dúvidas: " . EMAIL_CONTATO . "\r\n";
        $msgBody .= "";

        if ($debug) {
            //echo $msgBody . "<hr>";
        }

        /** Envia o e-mail */
        $msgTo = $obj->getEmailInscrito();
        //$msgTo = "contato@opctec.com.br";
//        $enviou = mail($msgTo, $msgSubject, $msgBody, HEADER_EMAIL);

        $enviou = GerenciarEmailAC::enviarEmail($msgTo, $msgSubject, $msgBody);
        //$enviou = $enviarEmail();
        //$enviou = false;
        if (!$enviou) {
            if ($retorno == 'BOOLEAN') {
                return false;
            } else {
                echo "<font color='red'><b>E-mail não enviado</b></font><br>";
            }
        } else {
            if ($retorno == 'BOOLEAN') {
                return true;
            } else {
                echo "E-mail enviado - {$obj->getEmailInscrito()}<br>";
            }
        }
    }

    private function handleException($exc) {
        echo $exc->getMessage();
        exit();
    }

    public function downloadZipCertificados($fws_, $debug = false) {
        global $fwsGet_;
        $nomeArquivo = $fwsGet_['arquivo'] != '' ? $fwsGet_['arquivo'] : $fws_['arquivo'];
        $arquivo = WB_PATH_LOG_CERTIFICADOS . basename($nomeArquivo);
        if (!file_exists($arquivo) || pathinfo($arquivo, PATHINFO_EXTENSION) !== 'zip') {
            echo "Arquivo não encontrado.";
            return;
        }
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($arquivo) . '"');
        header('Content-Length: ' . filesize($arquivo));
        readfile($arquivo);
        exit();
    }
}
