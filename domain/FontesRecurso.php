<?php

class FontesRecurso extends FontesRecursoVO {

    private $objDAO;

    function __construct() {
        $this->codEvento = COD_EVENTO;
        $this->objDAO = new FontesRecursoDAO();
    }

    public function getEvento() {
        $obj = new Evento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

    public function FontesRecursoExiste() {
        if (!$this->getCodFontesRecurso()) {
            throw new Exception('Código do Centro de Custo inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhum Centro de Custo foi encontrada para o código informado: " . $this->getCodFontesRecurso() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function salvar($debug = false) {
        
        $this->validatorObj();
        if (is_null($this->getCodFontesRecurso()) or $this->getCodFontesRecurso() == "") {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Centro de Custo salva com sucesso.");
            } else {
                throw new Exception("Novo Centro de Custo não foi salva.");
            }
        } else {
            $this->FontesRecursoExiste($debug);

            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Centro de Custo foi atualizado com sucesso.");
            } else {
                throw new Exception("Centro de Custo atual não foi atualizado.");
            }
        }
    }

    public function excluir($debug = false) {
        if($this->validatorExcluir()){
            $excluidoFontesRecurso          = $this->objDAO->delete($this, $debug);
            if ($excluidoFontesRecurso) {
                throw new OKException("Centro de Custo cód:" . $this->getCodFontesRecurso() . " foi excluída com sucesso.");
            } else {
                throw new Exception("Centro de Custo cód:" . $this->getCodFontesRecurso() . " não foi excluída.");
            }
        }
    }

    public function getFontesRecursoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function carregarListaFontesRecurso($condicoes=false, $debug = false) {
        $this->objDAO->carregarListaFontesRecursoByIdEvento($this, $condicoes, $debug);
        if ($this->getListaFontesRecurso()) {
        } else {
            throw new Exception("Evento não possui Centro de Custo cadastrado.");
        }
    }
}
