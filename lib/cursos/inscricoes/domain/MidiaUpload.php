<?php

class MidiaUpload extends MidiaUploadVO
{

    private $objDAO;
    
    function __construct()
    {
        $this->codEvento = COD_EVENTO;
        $this->setArrayTipoArquivo(array_keys($this->getListaAtributosMidia()));
        $this->objDAO = new MidiaUploadDAO();
    }

    public function MidiaUploadExiste($debug = false)
    {
        if (!$this->getCodArquivo()) {
            throw new Exception('Código do Mídia Upload inválido ou não informado: ' . $this->getCodArquivo());
        }
        return $this->objDAO->getByIdExiste($this, $debug);
    }

    public function carregarListaMidiaUploadsByPedidoId($debug = false)
    {
        $this->objDAO->carregarListaMidiaUploadsByPedidoId($this, $debug);
        if ($this->getListaMidiaUploads()) {
            return true;
        } else {
            throw new Exception("Inscrito não possui ingressos associados.");
        }
    }

    public function salvar($debug = false)
    {
        try {
            $this->validatorObj();

            if (is_null($this->getCodArquivo()) or $this->getCodArquivo() == "") {
                if ($this->objDAO->insert($this, $debug)) {
                    throw new OKException("Mídia Upload gravado com sucesso.");
                } else {
                    throw new Exception("Mídia Upload não foi gravado com sucesso.");
                }
            } else {
                $this->MidiaUploadExiste($debug);

                if ($this->objDAO->update($this, $debug)) {
                    throw new OKException("Mídia Upload atualizado com sucesso.");
                } else {
                    throw new Exception("Mídia Upload não foi atualizado.");
                }
            }
        } catch (Exception $e) {
            throw $e; // Lança a exceção novamente após tratamento
        }
    }


    public function excluir($debug = false)
    {
        $this->validatorExcluir();
        $this->MidiaUploadExiste($debug);
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Mídia Upload cód. " . $this->getCodArquivo() . " foi excluído com sucesso");
        } else {
            throw new Exception("Mídia Upload cód. " . $this->getCodArquivo() . " NÃO foi excluído");
        }
    }

    public function getMidiaUploadById($debug = false)
    {
        $this->objDAO->getById($this, $debug);
    }

    public function getEvento()
    {
        $obj = new InscEvento();
        $obj->setCodEvento($this->getCodEvento());
        $obj->getEventoById();
        return $obj;
    }

    public function getAll($debug = false)
    {
        return $this->objDAO->getAll($this, $debug);
    }

    public function carregarListaMidiasUploadByMidias($debug = false)
    {
        return $this->objDAO->carregarListaMidiasUploadByMidias($this, $debug);
    }

    /**
     * Registra (insert/update) um PDF de PROGRAMA (verso do certificado) ja
     * gerado em disco pelo Editor de Programa. Diferente do upload manual, aqui
     * NAO ha $_FILES: o arquivo ja existe, so persistimos o registro em
     * tab_arquivo para aparecer na lista de midias. Upsert por (evento, oficina).
     */
    public function registrarProgramaGerado($nomeArquivo, $codOficina = '', $codPerfil = '', $debug = false)
    {
        $this->setLocalMidia('PROGRAMA');
        $this->setTipoArquivo('PROGRAMA');
        // Escopo geral grava 0, nunca NULL/vazio: "cod_oficina=0" nao casa com
        // NULL em SQL, entao a busca do upsert falhava e cada "Gerar verso"
        // inseria uma linha nova apontando para o mesmo programa.pdf.
        $this->setCodOficina((int) $codOficina > 0 ? (int) $codOficina : 0);
        $this->setCodPerfil($codPerfil);
        $this->setArquivoUpload($nomeArquivo);
        $this->setArquivoGerado($nomeArquivo);
        // Este objeto nao vem de formulario, entao nao carrega status. Sem
        // definir aqui, o GetUpdateSQL do update grava status VAZIO e o
        // registro some das consultas que filtram por ATIVADA.
        $this->setStatus('ATIVADA');
        return $this->objDAO->upsertProgramaGerado($this, $debug);
    }
}