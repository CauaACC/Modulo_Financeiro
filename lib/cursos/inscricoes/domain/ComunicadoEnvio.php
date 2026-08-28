<?php

class ComunicadoEnvio extends ComunicadoEnvioVO {

    private $objDAO;

    function agendarEnvioComunicadoAprovacao($objL = false, $debug = false){

        /*carregarModelo*/
        $objComunicadoModelo = new ComunicadoModelo();
        $objComunicadoModelo->setTipo($this->getTipo());
        $objComunicadoModelo->carregarModeloByTipo();
        
        /*mesclarDadosModelo*/
        $objComunicadoModelo->getAssunto();
        $objComunicadoModelo->getCorpo();
        //Substituir as tags do texto por seus registros
        $tagsRecibo = array(
            "nome_bolsista" => $aluno->getObjCaeAluno()->getNomAlunoc()
            , "titulo_projeto_iniciacao_cientifica" => mb_strtoupper($aluno->getEspTituloProjic())
            , "nome_programa" => $edital->getObjPrograma()->getDesPrograma()
            , "nome_docente" => $orientador->getObjCrhFuncionario()->getNmFuncionario()
            , "data_inicio_bolsista" => "01/" . substr(formatDateView($dataInicioBolsista, false), -7)
            , "vigencia_edital_final" => formatDateView($edital->getDatVigenciaFinal(), false)
            , "nome_bolsa" => ($aluno->getObjBolsa() == null ? '' : $aluno->getObjBolsa()->getDesBolsa())
            , "data_atual_extenso" => date('d') . " de " . getMonthName(date('m')) . " de " . date("Y")
        );
        foreach ($tagsRecibo as $key => $value) {
            $declaracao = str_replace("[" . mb_strtolower($key) . "]", $value, $declaracao);
        }
        
        $objComunicado = new Comunicado();
        $objComunicado->setAssunto($objComunicadoModelo->getAssunto());
        $objComunicado->setCorpo($objComunicadoModelo->getCorpo());
        $objComunicado->setTipo($objComunicadoModelo->getTipo());
        $objComunicado->salvar();
        
        $objComunicadoEnvio->setCodComunicado($objComunicado->getCodComunicado());
        $objComunicadoEnvio->setDestinatarioEmail($objL->getEmail());
        $objComunicadoEnvio->setForma('EMAIL');
        $objComunicadoEnvio->setTipo('COMUNICADO-APROVACAO');
        $objComunicadoEnvio->agendarEnvioComunicadoAprovado($objL);        
    }
    /**
     * @tipo
     * @destinatarioEmail
     * @envioForma EMAIL
     * @envioData 2019-06-10 04:47:48
     * @envioSituacao PENDENTE | ENVIADO | CANCELADO
     */
    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new ComunicadoEnvioDAO();
    }

    public function ComunicadoEnvioExiste() {
        if (!$this->getCodComunicadoEnvio()) {
            throw new Exception('Código do ComunicadoEnvio inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma ComunicadoEnvio foi encontrada para o código informado: " . $this->getCodComunicadoEnvio() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function getEmailEnviado($debug = false){
        return $this->objDAO->getEmailEnviado($this, $debug);
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodComunicadoEnvio()) or $this->getCodComunicadoEnvio() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Comunicado de Envio salvo com sucesso.");
            } else {
                throw new Exception("Novo Comunicado de Envio não foi salva.");
            }
        } else {
            $this->ComunicadoEnvioExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Comunicado de Envio foi atualizado com sucesso.");
            } else {
                throw new Exception("Comunicado de Envio atual não foi atualizada.");
            }
        }
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Comunicado de Envio cód:" . $this->getCodComunicadoEnvio() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Comunicado de Envio cód:" . $this->getCodComunicadoEnvio() . " não foi excluída.");
        }
    }

    public function getComunicadoEnvioById($debug = false) {
        return $this->objDAO->getById($this, $debug);
    }

    public function carregarListaComunicadoEnvios($debug = false,$limit=false) {
        if ($this->objDAO->getAll($this, $debug,$limit)) {
            return true;
        } else {
            throw new Exception("Evento não possui Comunicado de Envio cadastrado.");
        }
    }
    public function carregarListaComunicadoEnviosByTipoComunicadoEnvio($debug = false,$limit=false) {
        if ($this->objDAO->getListaComunicadoEnvioByByTipoComunicadoEnvio($this, $debug)) {
            return true;
        } else {
            throw new Exception("Evento não possui Comunicado de Envio cadastrado.");
        }
    }

    public function getEvento() {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}

?>