<?php
use Twilio\Rest\Client; 
    
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
ini_set("display_errors", "On");

class GerenciarWhatsAC{

    public function carregarInscrito($fws_, $debug = false) {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $obj = new Inscrito();
        $obj->setCodInscrito($fws_['codInscrito']);
        $obj->getInscritoById($debug);
        return $obj;
    }

    public function carregarObjTwilio() {
        // Configuração do Twilio
        $sid    = "ACe35071f1f1b43e4bea566da1e3b0f269";
        $token  = "edc724e0574265ed1ceaecde0fe12fab";
        require_once(dirname(__FILE__) . '/../lib/vendor/autoload.php');  
        
        $this->twilioFone = "whatsapp:+554396967790";
        $this->objTwilio = new Client($sid, $token);
        return $this->objTwilio;
    }

    public function carregarObjInscrito($fws_, $debug = false) {
        $obj = new Inscrito();
        $obj->setCodInscrito($fws_['codInscrito']);
        $obj->getInsgetInscritoByEmail($debug);
        return $obj;
    }

    public function verificarControleEnvio($objInscrito, $debug = false) {
        // Inicia a sessão, se ainda não estiver iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $message = "Sistema em manutenção.";
        $ok = false;
        // echo '{"message":"' . tratarJson($message) . '","statusMessage":"' . $ok . '"}';
        // exit();        
        throw new Exception("Sistema em manutenção.");  
        // return;

        // Controle de envio em curto espaço de tempo para evitar possíveis ataques
        if (!isset($_SESSION['whatsapp_envios_tempo'])) {
            $_SESSION['whatsapp_envios_tempo'] = [];
        }

        $tempoAtual = time();
        $limiteEnvios = 5; // Limite de envios permitidos
        $intervaloTempo = 60; // Intervalo de tempo em segundos

        // Remove registros antigos fora do intervalo de tempo
        $_SESSION['whatsapp_envios_tempo'] = array_filter(
            $_SESSION['whatsapp_envios_tempo'],
            function ($tempoEnvio) use ($tempoAtual, $intervaloTempo) {
            return ($tempoAtual - $tempoEnvio) <= $intervaloTempo;
            }
        );

        // Verifica se o limite de envios foi atingido
        if (count($_SESSION['whatsapp_envios_tempo']) >= $limiteEnvios) {
            if ($debug) {
            echo "Limite de envios atingido. Tente novamente mais tarde.";
            }
            return;
        }

        // Registra o envio atual
        $_SESSION['whatsapp_envios_tempo'][] = $tempoAtual;

        // Controle de mensagens enviadas para o inscrito na sessão atual
        if (!isset($_SESSION['whatsapp_enviados'])) {
            $_SESSION['whatsapp_enviados'] = [];
        }

        if (in_array($objInscrito->getCodInscrito(), $_SESSION['whatsapp_enviados'])) {
            if ($debug) {
            echo "Mensagem já enviada anteriormente para este inscrito na sessão atual.";
            }
            return;
        }

        // Marca como enviado na sessão
        $_SESSION['whatsapp_enviados'][] = $objInscrito->getCodInscrito();
    }

    private function validarFone($fone) {
        if (!isValidFoneNumber($fone)) {
            throw new Exception('Número inválido:'. $fone);
        }
        if($fone == ''){
            throw new Exception('Fone do inscrito não informado.');
        }
    }

    public function enviarCredenciaisByWhats($fws_, $objInscrito, $debug = false){
        try {
            
            $this->verificarControleEnvio($objInscrito, $debug);
            $objTwilio = $this->carregarObjTwilio();
            $twilioFone = "whatsapp:+554396967790";
            $foneTo = limpaFoneWhats($objInscrito->getFoneContatoDdd().$objInscrito->getFoneContato());
            $templateSid = "HX9138378855ae85501c598d8b3dac7dd7";

            $this->validarFone($foneTo);

            // Valores que irão substituir os placeholders do template
            $contentVariables = [
                '1' => $objInscrito->getPreNome(),
                '2' => 'https://'.$_SERVER["SERVER_NAME"].'/'.$objInscrito->getUrlPrefixo().'/inscricoes',
                '3' => $objInscrito->getEMail(),
                '4' => $objInscrito->getCodInscrito()
            ]; 
            // echo json_encode($contentVariables);
            // Enviando mensagem usando o template
            $message = $objTwilio->messages->create(
                'whatsapp:+'.$foneTo, // Número do destinatário
                [
                    'from'       => 'whatsapp:+554396967790',
                    'contentSid' => $templateSid,
                    'contentVariables' => json_encode($contentVariables)
                ]
            );
            // echo "Mensagem enviada com sucesso!";
            // echo "Mensagem enviada com sucesso! SID: " . $message->sid;
            return true;
        } catch (Exception $e) {
            return false;
            // echo "Erro ao enviar mensagem: " . $e->getMessage();
        }
    }

    public function enviarCredenciaisBySms($fws_, $objInscrito, $debug = false){
        try {

            // $this->verificarControleEnvio($objInscrito, $debug);

            $objTwilio = $this->carregarObjTwilio();

            // Destino em E.164 (ex: +5543XXXXXXXXX)
            $foneTo = limpaFoneWhats($objInscrito->getFoneContatoDdd().$objInscrito->getFoneContato());
            $this->validarFone($foneTo);
            $toE164 = '+'.$foneTo;

            // ATENÇÃO: precisa ser um número Twilio que envie SMS (ou use MessagingServiceSid)
            $fromSms = '+1SEU_NUMERO_TWILIO_SMS'; // troque aqui

            $url = 'https://'.$_SERVER["SERVER_NAME"].'/'.$objInscrito->getUrlPrefixo().'/inscricoes';

            $body =
                'Olá '.$objInscrito->getPreNome()."! \n".
                "Acesse: ".$url."\n".
                "Login: ".$objInscrito->getEMail()."\n".
                "Código: ".$objInscrito->getCodInscrito();

            $message = $objTwilio->messages->create(
                $toE164,
                [
                    'from' => $fromSms,
                    'body' => $body
                ]
            );

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    public function enviarLinkPagamentoByWhats($fws_, $objInscrito, $debug = false){
        try {
            $this->verificarControleEnvio($objInscrito, $debug);

            $objTwilio = $this->carregarObjTwilio();
            $twilioFone = "whatsapp:+554396967790";
            $foneTo = limpaFoneWhats($objInscrito->getFoneContatoDdd().$objInscrito->getFoneContato());
            $templateSid = "HX64427c363129f5173857c2f682448b7e"; // Link de pagamento

            $this->validarFone($foneTo);

            // Valores que irão substituir os placeholders do template
            $contentVariables = [
                '1' => $objInscrito->getPreNome(),
                '2' => NOME_EVENTO,
                '3' => 'https://'.$_SERVER["SERVER_NAME"].'/'.$objInscrito->getUrlPrefixo().'/inscricoes/'.$objInscrito->getQrCode(),
            ]; 

            // echo json_encode($contentVariables);
            // Enviando mensagem usando o template
            $message = $objTwilio->messages->create(
                'whatsapp:+'.$foneTo, // Número do destinatário
                [
                    'from'       => 'whatsapp:+554396967790',
                    'contentSid' => $templateSid,
                    'contentVariables' => json_encode($contentVariables)
                ]
            );
            return true;
            // echo "Mensagem enviada com sucesso!";
            // echo "Mensagem enviada com sucesso! SID: " . $message->sid;
        } catch (Exception $e) {
            return false;
            // echo "Erro ao enviar mensagem: " . $e->getMessage();
        }
    }

    public function enviarAvisoPagamento($fws_, $debug=false){
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $objInscrito = $this->carregarInscrito($fws_);
        $objInscrito->carregarObjEvento();
        $objEv = $objInscrito->getObjEvento();
        $ok = $this->enviarLinkPagamentoByWhats($fws_, $objInscrito, $debug);

        if ($_SERVER['REMOTE_ADDR'] == '177.82.0.28'){
            $message = "Link de pagamento enviado por SMS: (".$objInscrito->getFoneContatoDdd().$objInscrito->getFoneContato().")";
        } else {
            $message = "Link de pagamento enviado para o WhatsApp: (".$objInscrito->getFoneContatoDdd().$objInscrito->getFoneContato().")";
        }
        if($ok===false){
            $message = "Erro ao enviar mensagem: (".$objInscrito->getFoneContatoDdd().$objInscrito->getFoneContato().")";
        }        
        echo '{"message":"' . tratarJson($message) . '","statusMessage":"' . $ok . '"}';
        exit();
    }

    public function enviarCredencial($fws_, $debug=false){
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
        $objInscrito = $this->carregarInscrito($fws_);
        $objInscrito->carregarObjEvento();
        $objEv = $objInscrito->getObjEvento();
        if ($_SERVER['REMOTE_ADDR'] == '177.82.0.28'){
            $ok = $this->enviarCredenciaisBySms($fws_, $objInscrito, $debug);
        } else {
            $ok = $this->enviarCredenciaisByWhats($fws_, $objInscrito, $debug);
        }
        $message = "Credencial enviada para o WhatsApp: (".$objInscrito->getFoneContatoDdd().$objInscrito->getFoneContato().")";
        if($ok===false){
            $message = "Erro ao enviar mensagem: (".$objInscrito->getFoneContatoDdd().$objInscrito->getFoneContato().")";
        }          
        echo '{"message":"' . tratarJson($message) . '","statusMessage":"' . $ok . '"}';
        exit();
    }

    public function enviarMensagemByEvol($foneTo, $mensagem, $debug = false) {
        $evo = new EvolutionApiAC();
        return $evo->evolutionSendText($foneTo, $mensagem);
    }

    public static function acaoEnviarComunicados($obj, $fws_, $debug = false) {

        $foneTo = limpaFoneWhats($obj->getFoneContatoDdd().$obj->getFoneContato());
        $msgBody = '';
        /** Monta o corpo da mensagem */
        // $msgDefaultFoot = " Esta é uma mensagem automática, favor não responder.<br><br>";
        // $objEvConf = EventoConfiguracao::getInstancePopulado(COD_EVENTO);
        if($obj->getUrlPrefixo()==''){
            $urlPrefixo = WB_GRUPO.'/'.WB_PROJETO;
        }else{
            $urlPrefixo = $obj->getUrlPrefixo();
        }
        
        $LINK_PAGAMENTO = "https://".FWA_Dominio()."/".$urlPrefixo."/inscricoes/".$obj->getQrCode();
        $LINK_AREA_INSCRITO = "https://".FWA_Dominio()."/".$urlPrefixo."/inscricoes/";
        
        $NOME_INSCRITO  = $obj->getNomeInscrito();
        $PRE_NOME       = $obj->getPreNome();
        $LOGIN     = $obj->getEmail();
        if ($obj->getEmail()=='') {
            $LOGIN     = $obj->getEMail();
        }
        $CREDENCIAL     = $obj->getCodInscrito();
        $NOME_PROJETO   = ABREV_EVENTO;

        // Arrays de buscas e substituições
        $search     = array('[LINK_PAGAMENTO]','[LINK_AREA_INSCRITO]', '[NOME_INSCRITO]','[PRE_NOME]','[LOGIN]','[CREDENCIAL]','[NOME_PROJETO]');
        $replace    = array($LINK_PAGAMENTO, $LINK_AREA_INSCRITO, $NOME_INSCRITO,$PRE_NOME,$LOGIN,$CREDENCIAL, $NOME_PROJETO,$NOME_PROJETO);
        // Substituindo os placeholders na string
        $fws_['email_template_body'] = str_replace($search, $replace, $fws_['email_template_body']);

        /** Monta o corpo da mensagem */
        // $msgBody = "<html><body>";
        // $msgBody .= "Assunto: ".$fws_['email_subject']."<br>\n";
        // $msgBody .= $fws_['email_template_body'];
        // $msgBody .= "\n<br>\n";
        // $msgBody .= "------------------------------------------------------\n<br>\n";
        // $msgBody .= $msgDefaultFoot;
        // $msgBody .= "</body></html>";

        $msgBody .= $fws_['email_template_body'];
        if ($fws_['acaoEmail']=='testarEmail') {
            echo "<hr><b>Testando msg whats (".$foneTo."): </b>".$msgBody . "";
        }
        if ($fws_['acaoEmail']=='enviarEmail') {
            // $objWhats = new GerenciarWhatsAC();
            //echo __LINE__.'-';
            // $objWhats->enviarArquivoByEvol($foneTo, $mensagem, $caminhoArquivo);
            // $foneTo = '554399598746'; //teste
            // echo "<hr><b>Enviando msg whats (".$foneTo."):</b> ".$msgBody . "<br>";
            // $objWhats->enviarMensagemByEvol($foneTo, $msgBody);
            echo Date('Y-m-d H:i:s') . ' - Envio de mensagem via API Evo';
            echo "<hr><b>Para whats (".$foneTo."):</b>";
            $evo = new EvolutionApiAC();
            return $evo->evolutionSendText($foneTo, $msgBody);
        }
    }

    // public function enviarMsgWhats($fws_, $objInscrito, $debug = false){
    //     $this->carregarObjTwilio();
    //     // dd($objInscrito);  
    //     $foneTo = limpaFoneWhats($objInscrito->getFoneContatoDdd().$objInscrito->getFoneContato());
    //     $templateSid = "HXce3c739428c92b45abd2a4ac84ab91b1"; //Olá..

    //     try {         
    //         // Enviando mensagem usando o template
    //         $message = $this->objTwilio->messages->create(
    //             'whatsapp: +'.$foneTo, // Número do destinatário
    //             [
    //                 'from'       => 'whatsapp:+554396967790',
    //                 'contentSid' => $templateSid
    //             ]
    //         );
    //                 echo "Mensagem enviada com sucesso! SID: " . $message->sid;
    //     } catch (Exception $e) {
    //         echo "Erro ao enviar mensagem: " . $e->getMessage();
    //     }
        
    // }

// // Valores que irão substituir os placeholders do template
// $contentVariables = [
//     '1' => 'João',
//     '2' => 'https://seusite.com/login',
//     '3' => 'joao123',
//     '4' => 'minha_senha'
// ];

// $message = $client->messages->create(
//     'whatsapp:+5511999999999', // Número de destino
//     [
//         'from' => 'whatsapp:+14155238886', // Número do remetente (Twilio Sandbox ou número aprovado)
//         'contentSid' => 'HXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', // SID do template aprovado
//         'contentVariables' => json_encode($contentVariables)
//     ]
// );

    public function enviarMensagemByEvolHTTP($foneTo, $mensagem, $debug = false) {
        
        // Tenta HTTP primeiro
        $urlBase = "http://inscritec-evolution.wz5mth.easypanel.host";
        $session = "whatsoptec";
        $token   = "429683C4C977415CAAFCCE10F7D57E11"; // ✅ CORRIGIDO

        // Normaliza número
        $foneTo = preg_replace('/\D+/', '', $foneTo);
        if (substr($foneTo, 0, 2) !== '55') $foneTo = '55'.$foneTo;

        $url = $urlBase . "/message/sendText/" . $session;
        $payload = [
            "number"      => $foneTo,
            "text"        => $mensagem,
            "linkPreview" => false
        ];

        $headers = [
            'Content-Type: application/json', 
            'apikey: ' . $token
        ];

        if ($debug) {
            echo "Testando com HTTP: " . $url . "<br>";
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);

        $result = curl_exec($ch);
        $http   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error  = curl_error($ch);
        curl_close($ch);

        if ($debug) {
            echo "HTTP Code: " . $http . "<br>";
            echo "Response: " . $result . "<br>";
            echo "Error: " . $error . "<br>";
        }

        return ['http' => $http, 'result' => $result, 'error' => $error];
    }
}