<?php

class EmailTemplate extends EmailTemplateVO
{

    private $objDAO;

    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new EmailTemplateDAO();
    }

    public function EmailTemplateExiste($debug = false)
    {
        if (!$this->getCodEmailTemplate()) {
            throw new Exception('Código do EmailTemplate inválido ou não informado: ' . $this->getCodEmailTemplate());
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function carregarListaEmailTemplateByIdEvento($debug = false)
    {
        $this->objDAO->carregarListaEmailTemplateByIdEvento($this, $debug);
        if ($this->getListaEmailTemplates()) {
            return true;
        } else {
            throw new Exception("Evento não possui EmailTemplate associados.");
        }
    }

    public function salvar($debug = false)
    {
        try {
            $this->validatorObj();

            if (is_null($this->getCodEmailTemplate()) or $this->getCodEmailTemplate() == "") {
                if ($this->objDAO->insert($this, $debug)) {
                    throw new OKException("EmailTemplate gravado com sucesso.");
                } else {
                    throw new Exception("EmailTemplate não foi gravado com sucesso.");
                }
            } else {
                $this->EmailTemplateExiste($debug);

                if ($this->objDAO->update($this, $debug)) {
                    throw new OKException("EmailTemplate atualizado com sucesso.");
                } else {
                    throw new Exception("EmailTemplate não foi atualizado.");
                }
            }
        } catch (Exception $e) {
            throw $e; // Lança a exceção novamente após tratamento
        }
    }


    public function excluir($debug = false)
    {
        $this->validatorExcluir();
        $this->EmailTemplateExiste($debug);
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("EmailTemplate cód. " . $this->getCodEmailTemplate() . " foi excluído com sucesso");
        } else {
            throw new Exception("EmailTemplate cód. " . $this->getCodEmailTemplate() . " NÃO foi excluído");
        }
    }

    public function getEmailTemplateById($debug = false)
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
    
    public function carregarAssuntosOutrosEventos($debug = false)
    {
        return $this->objDAO->carregarAssuntosOutrosEventos($this, $debug);
    }

    public function importarEmailTemplatesPorIds($ids, $debug = false)
    {
        if (empty($ids)) {
            throw new Exception("Nenhum template selecionado para importação.");
        }
        if ($this->objDAO->importarEmailTemplatesPorIds($this, $ids, $debug)) {
            throw new OKException(count($ids) . " template(s) importado(s) com sucesso.");
        } else {
            throw new Exception("Erro ao importar templates.");
        }
    }

    public function importarEmailTemplates($debug = false) {
        if ($this->objDAO->importarEmailTemplates($this, $debug)) {
            throw new OKException("Template cód: ".$this->getCodEventoAnt()." importados para ". $this->getCodEvento() . " com sucesso.");
        } else {
            throw new Exception("Template cód:" . $this->getCodEventoAnt() . " não foi importados.");
        }
    }

}