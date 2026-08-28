<?php
class EventoConfiguracaoDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    public function getById(EventoConfiguracao $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_eventos_configuracao WHERE cod_evento=\'%s\' ', $obj->getCodEvento());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $rows['populado']=1;
                }
                $obj->setAll($rows);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getLastId($condicao=false, $debug=false) {
        try {
            $sql = "SELECT ifnull(MAX(cod_evento),0)+1 FROM tab_eventos_configuracao";

            $rs = $this->conex->Execute($sql);
            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                foreach ($rs as $rows) {

                }
                return $rows['0'];
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getByIdExiste(EventoConfiguracao $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_evento FROM tab_eventos_configuracao WHERE cod_evento=\'%s\' ', $obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if ((!$rs) or ($rs->EOF)) {
                return false;
            } else {
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    /**@todo verificar onde esta sendo usado */
    public function carregarListaEventosConfiguracaoByGestor(EventoConfiguracao $obj, $debug=false) {
        try{
            $sql = sprintf('SELECT * FROM tab_eventos_configuracao WHERE gestor=\'HUTEC\'order by cod_evento desc, titulo');
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{

                foreach($rs as $rows){
                    $rows['populado']=1;
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaEventosConfiguracao($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function getAll(EventoConfiguracao $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_eventos_configuracao order by cod_evento desc');

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $rows['populado']=1;
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaEventosConfiguracao($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaEventossByIdEvento(EventoConfiguracao $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_eventos_configuracao WHERE cod_evento=\'%s\' order by cod_evento desc',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $rows['populado']=1;
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaEventosConfiguracao($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function insert(EventoConfiguracao $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
                        
            $sql = "SELECT * FROM tab_eventos_configuracao WHERE cod_evento=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_eventos_configuracao");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodEventos($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(EventoConfiguracao $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_eventos_configuracao WHERE cod_evento = ".$obj->getCodEvento(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_eventos_configuracao");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $updateSQL = $this->conex->GetUpdateSQL($rs,$fws_, true, false, true);            
            
            $sql = sprintf($updateSQL);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
            
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    /**
     * UPDATE direcionado APENAS nas colunas de certificado.
     *
     * Por que separado do update() geral: o update() usa GetUpdateSQL sobre a
     * tabela INTEIRA (~40 colunas) a partir de um POST parcial via setAll().
     * Isso regrava colunas nao enviadas e quebra em MySQL estrito (local). Aqui
     * so mexemos nas ~18 colunas de certificado, montadas uma a uma e escapadas
     * com qstr, sem tocar em mais nada.
     *
     * Espelho dos campos de certificado da tela de configuracao. Se um novo
     * campo de certificado for criado na config do evento, incluir aqui.
     */
    public function updateConfiguracaoCertificado(EventoConfiguracao $obj, $debug=false) {
        try {
            $q = array($this->conex, 'qstr'); // escape

            $sets = array(
                'emitir_certificado='                  . call_user_func($q, $obj->getEmitirCertificado()),
                'certificado_dat_ini='                 . call_user_func($q, $obj->getCertificadoDatIni()),
                'certificado_data_emissao='            . call_user_func($q, $obj->getCertificadoDataEmissao()),
                'liberar_avaliar_evento='              . call_user_func($q, $obj->getLiberarAvaliarEvento()),
                'url_avaliar_evento='                  . call_user_func($q, $obj->getUrlAvaliarEvento()),
                'certificado_selo='                    . call_user_func($q, $obj->getCertificadoSelo()),
                'certificado_assinatura_dupla='        . call_user_func($q, $obj->getCertificadoAssinaturaDupla()),
                'certificado_congressista='            . call_user_func($q, $obj->getCertificadoCongressista()),
                'certificado_congressista_qualidade='  . call_user_func($q, $obj->getCertificadoCongressistaQualidade()),
                'certificado_congressista_carga_horaria=' . call_user_func($q, $obj->getCertificadoCongressistaCargaHoraria()),
                'certificado_trabalho_nao_apresentado=' . call_user_func($q, $obj->getCertificadoTrabalhoNaoApresentado()),
                'certificado_texto_inicial='           . call_user_func($q, $obj->getCertificadoTextoInicial()),
                'certificado_exibir_qualidade='        . call_user_func($q, $obj->getCertificadoExibirQualidade()),
                'certificado_texto_qualidade='         . call_user_func($q, $obj->getCertificadoTextoQualidade()),
                'certificado_texto_inicial_trabalho='  . call_user_func($q, $obj->getCertificadoTextoInicialTrabalho()),
                'certificado_texto_no='                . call_user_func($q, $obj->getCertificadoTextoNo()),
                'certificado_texto_do='                . call_user_func($q, $obj->getCertificadoTextoDo()),
                'certificado_texto_reallizado='        . call_user_func($q, $obj->getCertificadoTextoReallizado()),
                'certificado_promovido_por='           . call_user_func($q, $obj->getCertificadoPromovidoPor()),
            );

            $sql = 'UPDATE tab_eventos_configuracao SET ' . implode(', ', $sets)
                . ' WHERE cod_evento=' . (int) $obj->getCodEvento();
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs !== false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * UPDATE direcionado de UMA coluna: o modelo da frente do certificado.
     * NULL = evento volta ao layout antigo (modelos hardcoded).
     * Isolado de proposito -- definir o padrao do evento nao pode arrastar
     * junto as outras ~19 colunas de certificado.
     */
    public function updateModeloCertificado(EventoConfiguracao $obj, $debug=false) {
        try {
            $cod = (int) $obj->getModeloCertificado();
            $sql = 'UPDATE tab_eventos_configuracao SET modelo_certificado='
                . ($cod > 0 ? $cod : 'NULL')
                . ' WHERE cod_evento=' . (int) $obj->getCodEvento();
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs !== false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Grava so o dia padrao de vencimento das mensalidades.
     *
     * UPDATE de uma coluna de proposito: o update() geral reescreve a linha inteira
     * (GetUpdateSQL com force) e engasga em colunas de data invalidas.
     */
    public function updateDiaVencimentoMensalidade(EventoConfiguracao $obj, $debug=false) {
        try {
            $dia = (int) $obj->getMensDiaVenc();
            if ($dia < 1 || $dia > 31) {
                return false;
            }
            $sql = 'UPDATE tab_eventos_configuracao SET mens_dia_venc=' . $dia
                 . ' WHERE cod_evento=' . (int) $obj->getCodEvento();
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs !== false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(EventoConfiguracao $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_eventos_configuracao WHERE cod_evento=\'%s\' ', $obj->getCodEvento());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function importarConfDireitoGrupoSistema(EventoConfiguracao $obj, $debug=false) {
        try {
            $sql = sprintf('INSERT INTO `sis_direitos_grupos_sistemas` (cod_grupo, cod_sistema, cod_direito,cod_evento)
            SELECT cod_grupo, cod_sistema, cod_direito, '.$obj->getCodEventoDestino().' FROM `sis_direitos_grupos_sistemas`'
                    . ' WHERE cod_evento='.$obj->getCodEventoOrigem());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }   
    
    /**
     * Copia a configuracao de OUTRO projeto por cima da configuracao deste.
     *
     * Por que UPDATE de tabela contra ela mesma, e nao getById()+salvar(): o
     * salvar() geral monta GetUpdateSQL com as ~273 colunas vindas do VO, e
     * qualquer coluna que nao chegue preenchida quebra em MySQL estrito. Aqui
     * quem copia e o banco, coluna a coluna, sem os valores passarem pelo PHP —
     * nao ha como um campo se perder no caminho.
     *
     * A lista de colunas vem do proprio banco (MetaColumnNames) em vez de uma
     * lista fixa: coluna nova de configuracao passa a ser importada sozinha, sem
     * ninguem lembrar de voltar aqui.
     */
    public function importarDeOutroEvento(EventoConfiguracao $obj, $debug=false) {
        try {
            $destino = (int) $obj->getCodEventoDestino();
            $origem  = (int) $obj->getCodEventoOrigem();

            /* Fora da copia: a chave — copia-la faria o registro virar o outro
             * projeto — e o rastro de auditoria, que e desta gravacao e nao do
             * projeto de origem.
             *
             * realizacao_ini/fim tambem ficam de fora, e o motivo e diferente: a
             * data de realizacao existe DUAS vezes no sistema — aqui e em
             * tab_eventos.data_ini/data_fim, editada na tela "Editar Evento".
             * O importar so alcanca esta tabela, entao trazer a data da origem
             * deixaria as duas brigando, com o tab_eventos (que costuma estar
             * certo, porque ninguem o importou) apontando outra coisa. Deixando
             * de fora, o projeto de destino fica com a data dele nas duas. */
            $naoCopia = array('cod_evento', 'dat_incl', 'res_incl', 'dat_alter',
                              'res_alter', 'ip', 'http_user_agent', 'versao',
                              'realizacao_ini', 'realizacao_fim');

            $colunas = $this->conex->MetaColumnNames('tab_eventos_configuracao');
            $existe = array();
            foreach ($colunas as $coluna) {
                $existe[strtolower($coluna)] = $coluna;
            }

            /* Projeto recem-criado ainda NAO tem linha de configuracao — e o
             * caso mais util do importar, porque nao ha nada a perder. Ali o
             * caminho e INSERT, nao UPDATE: nao existe registro para atualizar. */
            if (!$this->configuracaoExiste($destino)) {
                return $this->inserirConfiguracaoDeOutroEvento(
                    $destino, $origem, $existe, $naoCopia, $debug);
            }

            $sets = array();
            foreach ($existe as $chave => $coluna) {
                if (in_array($chave, $naoCopia)) {
                    continue;
                }
                $sets[] = 'd.`'.$coluna.'` = o.`'.$coluna.'`';
            }
            if (!$sets) {
                return false;
            }

            /* Auditoria da propria importacao — so as colunas que a tabela tem
             * de fato. tab_eventos_configuracao nao segue o mesmo conjunto de
             * colunas de auditoria das outras tabelas (nao tem dat_alter, por
             * exemplo), e citar coluna inexistente derruba o UPDATE inteiro. */
            $auditoria = array(
                'dat_alter'       => $this->conex->qstr(date('Y-m-d H:i:s')),
                'res_alter'       => $this->conex->qstr($_SESSION['_cod_usuario']),
                'ip'              => $this->conex->qstr($_SERVER['REMOTE_ADDR']),
                'http_user_agent' => $this->conex->qstr($_SERVER['HTTP_USER_AGENT']),
                'versao'          => 'ifnull(d.`versao`,0)+1'
            );
            foreach ($auditoria as $chave => $valor) {
                if (isset($existe[$chave])) {
                    $sets[] = 'd.`'.$existe[$chave].'` = '.$valor;
                }
            }

            $sql = 'UPDATE tab_eventos_configuracao d, tab_eventos_configuracao o'
                 . ' SET '.implode(', ', $sets)
                 . ' WHERE d.cod_evento = '.$destino
                 . '   AND o.cod_evento = '.$origem;

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs ? true : false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /** Este projeto ja tem linha em tab_eventos_configuracao? */
    private function configuracaoExiste($codEvento) {
        $sql = 'SELECT cod_evento FROM tab_eventos_configuracao WHERE cod_evento = '.((int) $codEvento);
        $rs = $this->conex->Execute($sql);
        return ($rs and !$rs->EOF);
    }

    /**
     * Cria a configuracao do projeto como copia da de outro projeto.
     *
     * Vale para projeto novo, que ainda nao tem configuracao nenhuma. Mesma
     * lista de colunas do UPDATE, com duas diferencas:
     *
     *   - cod_evento entra, com o codigo do DESTINO (e o que faz a linha nova
     *     pertencer a este projeto e nao ao outro);
     *   - realizacao_ini/fim, que a copia nunca traz, nascem com a data do
     *     PROPRIO projeto, lida de tab_eventos. Deixa-las nulas obrigaria o
     *     usuario a redigitar uma data que ele ja preencheu em "Editar Evento" —
     *     e e justamente a data que vive duplicada nas duas tabelas.
     */
    private function inserirConfiguracaoDeOutroEvento($destino, $origem, $existe, $naoCopia, $debug=false) {
        $colunas = array($existe['cod_evento']);
        $valores = array((int) $destino);

        foreach ($existe as $chave => $coluna) {
            if (in_array($chave, $naoCopia)) {
                continue;
            }
            $colunas[] = $coluna;
            $valores[] = 'o.`'.$coluna.'`';
        }

        foreach (array('realizacao_ini' => 'data_ini', 'realizacao_fim' => 'data_fim') as $conf => $evento) {
            if (isset($existe[$conf])) {
                $colunas[] = $existe[$conf];
                $valores[] = '(SELECT e.`'.$evento.'` FROM tab_eventos e WHERE e.cod_evento = '.((int) $destino).')';
            }
        }

        $auditoria = array(
            'dat_incl'        => $this->conex->qstr(date('Y-m-d H:i:s')),
            'res_incl'        => $this->conex->qstr($_SESSION['_cod_usuario']),
            'ip'              => $this->conex->qstr($_SERVER['REMOTE_ADDR']),
            'http_user_agent' => $this->conex->qstr($_SERVER['HTTP_USER_AGENT']),
            'versao'          => '1'
        );
        foreach ($auditoria as $chave => $valor) {
            if (isset($existe[$chave])) {
                $colunas[] = $existe[$chave];
                $valores[] = $valor;
            }
        }

        $sql = 'INSERT INTO tab_eventos_configuracao (`'.implode('`, `', $colunas).'`)'
             . ' SELECT '.implode(', ', $valores)
             . ' FROM tab_eventos_configuracao o WHERE o.cod_evento = '.((int) $origem);

        $this->conex->debug = $debug;
        $rs = $this->conex->Execute($sql);
        return $rs ? true : false;
    }

    public function carregarListaAdminGeral(EventoConfiguracao $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT * FROM sis_usuarios WHERE '
            . 'cod_usuario IN (SELECT cod_usuario FROM `sis_ligacao_usuarios_grupos` WHERE cod_grupo=999');
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    
}
