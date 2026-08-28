<?php

class MsgAviso extends MsgAvisoVO
{

    private $objDAO;


    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new MsgAvisoDAO();
    }

    public function MsgAvisosExiste($debug = false)
    {
        if (!$this->getCodMsgAviso()) {
            throw new Exception('Código do Aviso inválido ou não informado: ' . $this->getCodMsgAviso());
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function getTotalMsgAvisosForAreaInscrito($debug = false)
    {
        return $this->objDAO->carregarTotalMsgAvisosForAreaInscrito($this, $debug);
    }

    public function carregarListaMsgAvisosByIdEvento($debug = false)
    {
        $this->objDAO->carregarListaMsgAvisosByIdEvento($this, $debug);
        if ($this->getListaMsgAvisos()) {
            return true;
        } else {
            throw new Exception("Evento não possui Avisos associados.");
        }
    }

    public function carregarListaMsgAvisosForAreaInscrito($debug = false)
    {
        $this->objDAO->carregarListaMsgAvisosForAreaInscrito($this, $debug);
        if ($this->getListaMsgAvisos()) {
            return true;
        } else {
            throw new Exception("Nenhum aviso encontrado.");
        }
    }

    public function salvar($debug = false)
    {
        try {
            $this->validatorObj();

            if (is_null($this->getCodMsgAviso()) or $this->getCodMsgAviso() == "") {
                if ($this->objDAO->insert($this, $debug)) {
                    throw new OKException("Aviso gravado com sucesso.");
                } else {
                    throw new Exception("O aviso não pode ser gravado.");
                }
            } else {
                $this->MsgAvisosExiste($debug);

                if ($this->objDAO->update($this, $debug)) {
                    throw new OKException("Aviso atualizado com sucesso.");
                } else {
                    throw new Exception("Avsiso não foi atualizado.");
                }
            }
        } catch (Exception $e) {
            throw $e; // Lança a exceção novamente após tratamento
        }
    }

    public function insertLigacaoInscritoAviso($debug = false)
    {
        try {
            // $this->validatorObjInsert();
            $this->objDAO->insertLigacaoInscritoAviso($this, $debug);
        } catch (Exception $e) {
        }
    }

    public function excluir($debug = false)
    {
        $this->validatorExcluir();
        $this->MsgAvisosExiste($debug);
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("MsgAvisos cód. " . $this->getCodMsgAviso() . " foi excluído com sucesso");
        } else {
            throw new Exception("MsgAvisos cód. " . $this->getCodMsgAviso() . " NÃO foi excluído");
        }
    }

    public function getMsgAvisoById($debug = false)
    {
        $this->objDAO->getById($this, $debug);
    }

    public function getEvento()
    {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

}