<?php
class OficinaDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    /**
     * Lista oficinas inscritíveis do evento, no formato usado pelo fluxo de seleção (agenda).
     * SQL e filtros espelham inscricao_congresso_oficina_seleciona.php (linhas 142-191) para que
     * o fluxo AGENDA mostre exatamente as mesmas oficinas que o fluxo PADRAO legado.
     * Inclui cálculo de VAGAS_DISPONIVEIS (numero_vagas - total inscritos com CPF distinto).
     */
    public function carregarOficinasInscritiveisAgenda(Oficina $obj, $debug = false) {
        try {
            $codEvento = (int) $obj->getCodEvento();
            $sql = "
                SELECT too.ordem, too.status, too.titulo, too.nome_ministrante, too.e_mail_ministrante,
                       too.fone_ministrante, too.local, too.carga_horaria, too.numero_vagas,
                       too.horario_ini, too.horario_fim, too.data_ini, too.data_fim,
                       too.palavras_chave, too.publico_alvo, too.objetivo, too.pre_requisito,
                       too.ementa, too.descricao, too.observacao, too.cod_oficina, too.abrev_tipo,
                       too.numero_oficial, too.status_inscricao, too.inscrito_apto, too.investimento,
                       too.data_inicio, too.data_fim_inscricao,
                       too.numero_vagas - COALESCE(
                           (SELECT COUNT(DISTINCT ti.cpf)
                            FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
                            WHERE tlio.cod_evento = $codEvento
                              AND tlio.cod_oficina = too.cod_oficina
                              AND tlio.cod_inscrito = ti.cod_inscrito
                              AND ti.status = 'ATIVADA'), 0) AS vagas_disponiveis,
                       too.vagas_universal_01, too.vagas_universal_02
                FROM tab_opcoes_oficinas too
                WHERE too.cod_evento = $codEvento
                  AND too.status = 'ATIVADA'
                  AND too.receber_inscricao = 1
                  AND too.status_inscricao = 'ATIVADA'
                ORDER BY too.data_inicio, too.horario_ini, too.ordem, too.cod_oficina
            ";
            $this->conex->debug = $debug;
            $rs = $this->conex->CacheExecute(60, $sql);
            if ((!$rs) || ($rs->EOF)) { return array(); }
            $return = array();
            foreach ($rs as $rows) {
                $obj->setAll($rows);
                $return[] = clone $obj;
            }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function verificandoInscritoApto(Oficina $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_inscritos_aptos WHERE cod_evento=\'%s\' '
                    . 'and cod_oficina=\'%s\' and cpf=\'%s\' and status=\'ATIVADO\''
                    , $obj->getCodEvento(),$obj->getCodOficina(),$obj->getCpf());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                }
                $obj->setAll($rows);
                if($obj->getStatus()=='ATIVADO'){
                    return true;
                }
                return false;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getInscritoAptoCount(Oficina $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT count(cod_oficina) as total FROM tab_inscritos_aptos WHERE cod_evento=\'%s\' and cod_oficina= \'%s\' and cpf= \'%s\''
                    , $obj->getCodEvento(),$obj->getCpf());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return 0;
            }else{
                foreach($rs as $rows){
                }
                $obj->setAll($rows);
                return $obj->getTotal();
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getById(Oficina $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_opcoes_oficinas WHERE cod_evento=\'%s\' and cod_oficina= \'%s\'', $obj->getCodEvento(),$obj->getCodOficina());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
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
            $sql = "SELECT ifnull(MAX(cod_oficina),0)+1 FROM tab_opcoes_oficinas";

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
    public function getByIdExiste(Oficina $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_oficina FROM tab_opcoes_oficinas WHERE cod_evento=\'%s\' and cod_oficina= \'%s\'', $obj->getCodEvento(),$obj->getCodOficina());

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
    public function getAll(Oficina $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT * FROM tab_opcoes_oficinas order by cod_evento desc');

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaEventos($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaOficinaByIdEvento(Oficina $obj, $orderBy = false,$debug=false) {
        try{
            if($orderBy==false){
                $orderBy = 'order by ordem';
                $orderBy = 'order by o.cod_oficina desc ';
            }else{
                $orderBy = 'order by '.$orderBy;
            }
            
//            $sql = sprintf('SELECT * FROM tab_opcoes_oficinas '
//                    . 'WHERE cod_evento=\'%s\' '
//                    . $oderBy  ,$obj->getCodEvento());
            $sql = 'SELECT o.*, IFNULL(io.total_inscritos,0) AS total_inscritos FROM tab_opcoes_oficinas o
                    LEFT JOIN
                        (SELECT COUNT(lio.cod_oficina) total_inscritos, lio.cod_oficina 
                        FROM tab_lig_inscritos_oficinas lio, tab_inscritos ti
                        WHERE lio.cod_evento='.$obj->getCodEvento().' AND 
                        lio.STATUS=\'ATIVADA\' and ti.cod_inscrito=lio.cod_inscrito and ti.status=\'ATIVADA\'
                        GROUP BY lio.cod_oficina) io ON io.cod_oficina=o.cod_oficina 
                    WHERE o.cod_evento='.$obj->getCodEvento().' AND o.status != \'EXCLUIDA\' '. $orderBy;
            if($_SERVER['REMOTE_ADDR']=='189.90.70.229'){
                $debug = true;
            }
            $this->conex->debug = $debug;
            // Sem CacheExecute (removido): o cache de 1h fazia telas que
            // editam e recarregam na sequencia (ex.: certificado por
            // atividade) nao verem a propria alteracao por ate 1h.
            $rs = $this->conex->Execute($sql);
            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaOficina($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaOficinaSemCacheByIdEvento(Oficina $obj, $orderBy = false,$debug=false) {
        try{
            if($orderBy==false){
                $orderBy = 'order by ordem';
                $orderBy = 'order by o.cod_oficina desc ';
            }else{
                $orderBy = 'order by '.$orderBy;
            }
            
//            $sql = sprintf('SELECT * FROM tab_opcoes_oficinas '
//                    . 'WHERE cod_evento=\'%s\' '
//                    . $oderBy  ,$obj->getCodEvento());
            $sql = 'SELECT o.*, IFNULL(io.total_inscritos,0) AS total_inscritos FROM tab_opcoes_oficinas o
                    LEFT JOIN
                        (SELECT COUNT(lio.cod_oficina) total_inscritos, lio.cod_oficina 
                        FROM tab_lig_inscritos_oficinas lio, tab_inscritos ti
                        WHERE lio.cod_evento='.$obj->getCodEvento().' AND 
                        lio.STATUS=\'ATIVADA\' and ti.cod_inscrito=lio.cod_inscrito and ti.status=\'ATIVADA\'
                        GROUP BY lio.cod_oficina) io ON io.cod_oficina=o.cod_oficina 
                    WHERE o.cod_evento='.$obj->getCodEvento().' AND o.status != \'EXCLUIDA\' '. $orderBy;
            if($_SERVER['REMOTE_ADDR']=='189.90.70.229'){
                $debug = true;
            }
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaOficina($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function toggleCampo(Oficina $obj, $campo, $debug=false) {
        try {
            $camposPermitidos = array(
                'status', 'receber_inscricao', 'liberar_certificado'
            );
            if (!in_array($campo, $camposPermitidos)) {
                return false;
            }

            if ($campo == 'status') {
                $sql = sprintf("UPDATE tab_opcoes_oficinas SET status = IF(status='ATIVADA','DESATIVADA','ATIVADA') WHERE cod_evento=%s AND cod_oficina=%s",
                    $obj->getCodEvento(), $obj->getCodOficina());
            } else {
                $sql = sprintf("UPDATE tab_opcoes_oficinas SET %s = IF(%s=1,0,1) WHERE cod_evento=%s AND cod_oficina=%s",
                    $campo, $campo, $obj->getCodEvento(), $obj->getCodOficina());
            }

            $this->conex->debug = $debug;
            $this->conex->Execute($sql);

            $this->getById($obj, $debug);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    /**
     * UPDATE direcionado APENAS nos campos de certificado da atividade.
     * Igual a filosofia do EventoConfiguracao: nao usa o update() geral (que
     * regrava a linha inteira via GetUpdateSQL e quebra em MySQL estrito), so
     * mexe nas colunas de certificado, escapadas com qstr.
     */
    public function updateCertificado(Oficina $obj, $debug=false) {
        try {
            $q = array($this->conex, 'qstr');
            $sets = array(
                'liberar_certificado='      . (int) $obj->getLiberarCertificado(),
                'titulo_certificado='       . call_user_func($q, $obj->getTituloCertificado()),
                'qualificacao_certificado=' . call_user_func($q, $obj->getQualificacaoCertificado()),
                'carga_horaria='            . call_user_func($q, $obj->getCargaHoraria()),
                'carga_horaria_extenso='    . call_user_func($q, $obj->getCargaHorariaExtenso()),
                'periodo_certificado='      . call_user_func($q, $obj->getPeriodoCertificado()),
                'data_emissao_certificado=' . call_user_func($q, $obj->getDataEmissaoCertificado()),
                // 'local' sai no certificado (GerenciarCertificadoAC usa getLocal).
                'local='                    . call_user_func($q, $obj->getLocal()),
            );
            $sql = 'UPDATE tab_opcoes_oficinas SET ' . implode(', ', $sets)
                . ' WHERE cod_evento=' . (int) $obj->getCodEvento()
                . ' AND cod_oficina=' . (int) $obj->getCodOficina();
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs !== false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(Oficina $obj, $debug=false) {
        try {           
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            
                        
            $sql = "SELECT * FROM tab_opcoes_oficinas WHERE cod_evento=".$obj->getCodEvento()." and cod_oficina=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_opcoes_oficinas");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodOficina($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(Oficina $obj, $debug=false) {
        try {           
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIp($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            
            $sql = "SELECT * FROM tab_opcoes_oficinas WHERE cod_evento = ".$obj->getCodEvento()." and cod_oficina = ".$obj->getCodOficina(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_opcoes_oficinas");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            $updateSQL = $this->conex->GetUpdateSQL($rs,$fws_, true, false, true);            
            echo $debug==1? $updateSQL : '';
            // $sql = sprintf($updateSQL);
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($updateSQL);
            return $rs;

        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Vincula um curso EAD a um conjunto de oficinas (grava cod_curso em tab_opcoes_oficinas).
     * UPDATE cirurgico: NAO passa pelo salvar()/force-update da oficina (nao mexe nas demais
     * colunas nem no fluxo de duplicar). Reconcilia: desvincula quem saiu e vincula quem entrou.
     * Relacao: N oficinas -> 1 curso.
     *
     * @param int   $codEvento
     * @param int   $codCurso
     * @param array $codOficinas cod_oficina selecionadas (vazio = desvincula todas do curso)
     */
    public function vincularCursoNasOficinas($codEvento, $codCurso, $codOficinas, $debug=false) {
        try {
            $codEvento = (int) $codEvento;
            $codCurso  = (int) $codCurso;
            if (!$codEvento || !$codCurso) { return false; }

            $ids = array();
            if (is_array($codOficinas)) {
                foreach ($codOficinas as $c) {
                    $c = (int) $c;
                    if ($c > 0) { $ids[$c] = $c; }
                }
            }
            $this->conex->debug = $debug;

            // 1) Desvincula as oficinas que estavam nesse curso e sairam da selecao
            $sqlLimpa = "UPDATE tab_opcoes_oficinas SET cod_curso = NULL "
                      . "WHERE cod_evento = $codEvento AND cod_curso = $codCurso";
            if (!empty($ids)) {
                $sqlLimpa .= " AND cod_oficina NOT IN (" . implode(',', $ids) . ")";
            }
            $this->conex->Execute($sqlLimpa);

            // 2) Vincula as oficinas selecionadas a esse curso
            if (!empty($ids)) {
                $sqlSeta = "UPDATE tab_opcoes_oficinas SET cod_curso = $codCurso "
                         . "WHERE cod_evento = $codEvento AND cod_oficina IN (" . implode(',', $ids) . ")";
                $this->conex->Execute($sqlSeta);
            }
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Retorna array de cod_oficina (int) vinculadas a um curso EAD.
     */
    public function getCodOficinasByCurso($codEvento, $codCurso, $debug=false) {
        $return = array();
        try {
            $codEvento = (int) $codEvento;
            $codCurso  = (int) $codCurso;
            if (!$codEvento || !$codCurso) { return $return; }
            $sql = "SELECT cod_oficina FROM tab_opcoes_oficinas "
                 . "WHERE cod_evento = $codEvento AND cod_curso = $codCurso ORDER BY cod_oficina";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) || ($rs->EOF)) { return $return; }
            foreach ($rs as $rows) { $return[] = (int) $rows['cod_oficina']; }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return $return;
        }
    }

    /**
     * Mapa cod_curso => array(cod_oficina) de TODOS os cursos EAD do evento que
     * tem oficina vinculada. Uma consulta so, no lugar de um
     * getCodOficinasByCurso por curso dentro do laco.
     */
    public function getCursosComOficina($codEvento, $debug=false) {
        $return = array();
        try {
            $codEvento = (int) $codEvento;
            if (!$codEvento) { return $return; }
            $sql = "SELECT cod_curso, cod_oficina FROM tab_opcoes_oficinas "
                 . "WHERE cod_evento = $codEvento AND cod_curso IS NOT NULL AND cod_curso > 0 "
                 . "ORDER BY cod_curso, cod_oficina";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) || ($rs->EOF)) { return $return; }
            foreach ($rs as $rows) { $return[(int) $rows['cod_curso']][] = (int) $rows['cod_oficina']; }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return $return;
        }
    }

    /**
     * Retorna array de cod_oficina (int) em que o inscrito esta matriculado e ATIVADA.
     */
    public function getCodOficinasByInscrito($codEvento, $codInscrito, $debug=false) {
        $return = array();
        try {
            $codEvento   = (int) $codEvento;
            $codInscrito = (int) $codInscrito;
            if (!$codEvento || !$codInscrito) { return $return; }
            $sql = "SELECT cod_oficina FROM tab_lig_inscritos_oficinas "
                 . "WHERE cod_evento = $codEvento AND cod_inscrito = $codInscrito AND status = 'ATIVADA'";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ((!$rs) || ($rs->EOF)) { return $return; }
            foreach ($rs as $rows) { $return[] = (int) $rows['cod_oficina']; }
            return $return;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return $return;
        }
    }

    public function delete(Oficina $obj, $debug=false) {
        try {
            $var_sql = "
                            INSERT INTO tab_opcoes_oficinas_excluidos
                            SELECT *
                            FROM tab_opcoes_oficinas
                            WHERE (cod_oficina = ".$obj->getCodOficina().")
                        ";
            $this->conex->Execute($var_sql);

            $sql = sprintf('DELETE FROM tab_opcoes_oficinas WHERE cod_evento=\'%s\' and cod_oficina= \'%s\'', $obj->getCodEvento(),$obj->getCodOficina());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
    public function duplicar(Oficina $obj, $debug=false) {
        try{
            
            $sql = sprintf('INSERT INTO tab_opcoes_oficinas (cod_evento,numero_oficial,ordem,receber_inscricao,status_inscricao,liberar_certificado,status,abrev_tipo,tipo
                ,titulo,titulo_anterior,titulo_certificado,qualificacao_certificado,nome_ministrante,e_mail_ministrante,fone_ministrante,link_aula
                ,link_aula_publicar,local,carga_horaria,numero_vagas,valor_atividade,investimento,horario_ini,horario_fim,data_ini,data_fim
                ,palavras_chave,publico_alvo,objetivo,pre_requisito,ementa,descricao,observacao,periodo_inscricao_data_ini,periodo_inscricao_data_fim
                ,http_user_agent,data_inclusao,data_alteracao,ip_cliente,session_id,material_necessario,numero_minimo_vagas,data_inicio_inscricao
                ,data_fim_inscricao,_endereco,_certificado,post_facebook,produto_id,data_inicio,email_administrador,layout_conteudo_verso,nome_site
                ,data_curso,nome_instituicao,conteudo_verso,data_certificado,carga_horaria_extenso,crm_obrigatorio,_status_curso,mais_informacao,ativo
                ,banner,liberar_pagamento,periodo_certificado,data_emissao_certificado
                )
                SELECT cod_evento,numero_oficial,ordem,receber_inscricao,status_inscricao,liberar_certificado,status,abrev_tipo,tipo
                ,concat("Cópia de ",titulo),titulo_anterior,concat("Cópia de ",titulo_certificado),qualificacao_certificado,nome_ministrante,e_mail_ministrante,fone_ministrante,link_aula
                ,link_aula_publicar,local,carga_horaria,numero_vagas,valor_atividade,investimento,horario_ini,horario_fim,data_ini,data_fim
                ,palavras_chave,publico_alvo,objetivo,pre_requisito,ementa,descricao,observacao,periodo_inscricao_data_ini,periodo_inscricao_data_fim
                ,http_user_agent,data_inclusao,data_alteracao,ip_cliente,session_id,material_necessario,numero_minimo_vagas,data_inicio_inscricao
                ,data_fim_inscricao,_endereco,_certificado,post_facebook,produto_id,data_inicio,email_administrador,layout_conteudo_verso,nome_site
                ,data_curso,nome_instituicao,conteudo_verso,data_certificado,carga_horaria_extenso,crm_obrigatorio,_status_curso,mais_informacao,ativo
                ,banner,liberar_pagamento,periodo_certificado,data_emissao_certificado
                FROM tab_opcoes_oficinas WHERE cod_evento=\'%s\' and cod_oficina= \'%s\'', $obj->getCodEvento(),$obj->getCodOficina());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodOficina($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function importarOficinas(Oficina $obj, $debug=false) {
        try{
            
            $sql = sprintf('INSERT INTO tab_opcoes_oficinas (cod_evento,numero_oficial,ordem,receber_inscricao,status_inscricao,liberar_certificado,status,abrev_tipo,tipo
                ,titulo,titulo_anterior,titulo_certificado,qualificacao_certificado,nome_ministrante,e_mail_ministrante,fone_ministrante,link_aula
                ,link_aula_publicar,local,carga_horaria,numero_vagas,valor_atividade,investimento,horario_ini,horario_fim,data_ini,data_fim
                ,palavras_chave,publico_alvo,objetivo,pre_requisito,ementa,descricao,observacao,periodo_inscricao_data_ini,periodo_inscricao_data_fim
                ,http_user_agent,data_inclusao,data_alteracao,ip_cliente,session_id,material_necessario,numero_minimo_vagas,data_inicio_inscricao
                ,data_fim_inscricao,_endereco,_certificado,post_facebook,produto_id,data_inicio,email_administrador,layout_conteudo_verso,nome_site
                ,data_curso,nome_instituicao,conteudo_verso,data_certificado,carga_horaria_extenso,crm_obrigatorio,_status_curso,mais_informacao,ativo
                ,banner,liberar_pagamento,periodo_certificado,data_emissao_certificado
                )
                SELECT '.$obj->getCodEvento().' as cod_evento,numero_oficial,ordem,receber_inscricao,status_inscricao
                ,'.$obj->getLiberarCertificado().',status,abrev_tipo,tipo
                ,concat("Cópia de ",titulo),titulo_anterior,concat("Cópia de ",titulo_certificado),qualificacao_certificado,nome_ministrante,e_mail_ministrante,fone_ministrante,link_aula
                ,link_aula_publicar,local,carga_horaria,numero_vagas,valor_atividade,investimento,horario_ini,horario_fim,data_ini,data_fim
                ,palavras_chave,publico_alvo,objetivo,pre_requisito,ementa,descricao,observacao,periodo_inscricao_data_ini,periodo_inscricao_data_fim
                ,http_user_agent,data_inclusao,data_alteracao,ip_cliente,session_id,material_necessario,numero_minimo_vagas,data_inicio_inscricao
                ,data_fim_inscricao,_endereco,_certificado,post_facebook,produto_id,data_inicio,email_administrador,layout_conteudo_verso,nome_site
                ,data_curso,nome_instituicao,conteudo_verso,data_certificado,carga_horaria_extenso,crm_obrigatorio,_status_curso,mais_informacao,ativo
                ,banner,liberar_pagamento,periodo_certificado,data_emissao_certificado
                FROM tab_opcoes_oficinas WHERE cod_evento=\'%s\' ', $obj->getCodEventoAnt());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function atualizarDataFim(Oficina $obj, $debug=false) {
        try{
            /** @todo definir o numero de dias a fechar inscrição a partir da data de inicio
             * ou da data de realização, opção usada para o museu 
             * $obj->getDiasSubtrair() = 7 */

            $sql = sprintf('UPDATE tab_opcoes_oficinas
            SET data_fim_inscricao = DATE_SUB(data_inicio, INTERVAL '.$obj->getDiasSubtrair().' DAY)
            WHERE cod_evento=\'%s\' AND data_fim_inscricao IS NULL', $obj->getCodEventoAnt());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
}
