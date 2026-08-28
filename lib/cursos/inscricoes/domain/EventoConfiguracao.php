<?php

class EventoConfiguracao extends EventoConfiguracaoVO {
    private static $instance = null;
    private $objDAO;

    function __construct($codEvento = null) {
        if ($codEvento !== null) {
            $this->setCodEvento($codEvento);
        }
        $this->objDAO = new EventoConfiguracaoDAO();
    }

    public function isPopulado() {
        // Verifica se os atributos principais estão preenchidos
        /*
        evento_online                                                             
        realizacao_ini                                                            
        realizacao_fim                                                            
        data_inscricao_ini                                                        
        data_inscricao_fim                                                        
        insc_login                                                                
        insc_situacao        
        populado
        // return !empty($this->codEvento) && !empty($this->nomeEvento);
        */
        return !empty($this->getPopulado());
    }

    public static function getInstance($codEvento = null) {
        if (!isset(self::$instance)) {
            if ($codEvento === null) {
                throw new Exception("O código do evento é necessário para criar a instância.");
            }
            self::$instance = new EventoConfiguracao($codEvento);
        }

        return self::$instance;
    }

    public static function getInstancePopulado($codEvento = null) {
        if (!isset(self::$instance)) {
            if ($codEvento === null) {
                throw new Exception("O código do evento é necessário para criar a instância.");
            }
            self::$instance = new EventoConfiguracao($codEvento);
        }
        if(!self::$instance->isPopulado()){
            self::$instance->getEventoConfiguracaoById();
        }
        return self::$instance;
    }
    
    public function EventoConfiguracaoExiste() {
        if (!$this->getCodEvento()) {
            throw new Exception('Código da Evento é inválido ou não informado.');
        }
        if (!$this->objDAO->getByIdExiste($this)) {
            throw new Exception("Nenhuma Evento foi encontrada para o código informado: " . $this->getCodEvento() . ".");
            //throw new Exception("Id informado para deleção não existe");
        }
    }

    public function novo($debug = false) {
        
    }

    public function salvar($debug = false) {
        $this->validatorObj();
        if (!$this->objDAO->getByIdExiste($this)) {
            if ($this->objDAO->insert($this, $debug)) {
                throw new OKException("Configuração do Evento salvo com sucesso.");
            } else {
                throw new Exception("Configuração do Evento não foi salvo.");
            }
        } else {
            if ($this->objDAO->update($this, $debug)) {
                throw new OKException("Configuração do Evento foi atualizada com sucesso.");
            } else {
                throw new Exception("Configuração do Evento atual não foi atualizada.");
            }
        }
    }

    /**
     * Grava SOMENTE os campos de certificado (UPDATE direcionado), sem passar
     * pelo salvar()/update() geral que regrava a tabela inteira e quebra local.
     * O registro de config precisa ja existir (a config do evento sempre existe).
     */
    public function gravarConfiguracaoCertificado($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Código do evento não informado.");
        }
        if ($this->objDAO->updateConfiguracaoCertificado($this, $debug)) {
            throw new OKException("Configuração do certificado salva com sucesso.");
        }
        throw new Exception("Não foi possível salvar a configuração do certificado.");
    }

    /** Define/limpa o modelo da frente do certificado (padrao do evento). */
    public function salvarModeloCertificado($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Código do evento não informado.");
        }
        if ($this->objDAO->updateModeloCertificado($this, $debug)) {
            return true;
        }
        throw new Exception("Não foi possível definir o modelo do certificado.");
    }

    /** Grava o dia padrao de vencimento das mensalidades (o dia combinado no contrato). */
    public function salvarDiaVencimentoMensalidade($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Código do evento não informado.");
        }
        return $this->objDAO->updateDiaVencimentoMensalidade($this, $debug);
    }

    /**
     * Traz para ESTE projeto a configuracao de outro projeto ja existente.
     *
     * Diferenca para o duplicar(): o duplicar CRIA um projeto do zero, entao
     * pode copiar junto perfis, atividades, taxas e templates de e-mail. Aqui o
     * projeto de destino ja existe e ja pode ter esses registros — copiar de
     * novo geraria duplicata silenciosa. Por isso importar mexe SOMENTE em
     * tab_eventos_configuracao.
     *
     * Espera setCodEventoOrigem() e setCodEventoDestino().
     */
    public function importarDeOutroEvento($debug = false) {
        $origem  = $this->getCodEventoOrigem();
        $destino = $this->getCodEventoDestino();

        if (!$origem) {
            throw new Exception('Escolha o projeto de origem.');
        }
        if (!$destino) {
            throw new Exception('Código do projeto de destino não informado.');
        }
        if ((int) $origem === (int) $destino) {
            throw new Exception('O projeto de origem é o próprio projeto atual.');
        }

        /* Origem precisa TER configuracao: sem linha de origem o comando roda,
         * nao copia nada, e o usuario le "importado" sem que nada tenha mudado.
         *
         * O DESTINO, ao contrario, NAO precisa ter: projeto recem-criado ainda
         * nao tem configuracao, e e exatamente ali que importar mais serve. O
         * DAO cria a linha nesse caso. */
        $objOrigem = new EventoConfiguracao($origem);
        $objOrigem->EventoConfiguracaoExiste();

        if ($this->objDAO->importarDeOutroEvento($this, $debug)) {
            throw new OKException('Configuração importada do projeto ' . $origem . '.');
        }
        throw new Exception('Não foi possível importar a configuração.');
    }

    public function excluir($debug = false) {
        $this->validatorEcluir();
        if ($this->objDAO->delete($this, $debug)) {
            throw new OKException("Evento cód:" . $this->getCodEvento() . " foi excluída com sucesso.");
        } else {
            throw new Exception("Evento cód:" . $this->getCodEvento() . " não foi excluída.");
        }
    }

    public function getEventoConfiguracaoById($debug = false) {
        $this->objDAO->getById($this, $debug);
    }

    public function getListaEventosConfiguracaoByIdEvento($debug = false) {
        if (!$this->getCodEvento()) {
            throw new Exception("Id do evento não informado.");
        }
        $this->objDAO->carregarListaEventosConfiguracaoByIdEvento($this, $debug);
        if ($this->getListaEventosConfiguracao()) {
            return $this->getListaEventosConfiguracao();
        } else {
            throw new Exception("Lista de configuracao de eventos não cadastrada.");
        }
    }

    public function carregarListaEventosConfiguracaoByGestor($debug = false) {
        $this->objDAO->carregarListaEventosConfiguracaoByGestor($this, $debug);
        if ($this->getListaEventosConfiguracao()) {
            return $this->getListaEventosConfiguracao();
        } else {
            throw new Exception("Lista de eventos configuracao não econtrada.");
        }
    }
    
    // Previne o clone da instância
    private function __clone() { }

    // Previne a deserialização da instância
    public function __wakeup() { }
}
