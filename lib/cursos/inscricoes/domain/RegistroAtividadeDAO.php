<?php
class RegistroAtividadeDAO extends DAO{
 
    public function __construct() {        
        parent::__construct();
    }  
    
    public function getListaRegistroAtividadesByCodInscrito(RegistroAtividade $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tiac.* , ti.nome_inscrito FROM tab_registro_atividade tiac
            LEFT JOIN tab_inscritos ti on tiac.cod_inscrito=ti.cod_inscrito AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            WHERE tiac.cod_evento=\'%s\' and tiac.cod_inscrito= \'%s\'', $obj->getCodEvento(),$obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaRegistroAtividades($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function getById(RegistroAtividade $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tiac.* , ti.nome_inscrito FROM tab_registro_atividade tiac
            LEFT JOIN tab_inscritos ti on tiac.cod_inscrito=ti.cod_inscrito AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            WHERE tiac.cod_evento=\'%s\' and tiac.cod_reg_atividade= \'%s\'', $obj->getCodEvento(),$obj->getCodRegAtividade());

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
            $sql = "SELECT ifnull(MAX(cod_inscrito),0)+1 FROM tab_registro_atividade";

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
    public function getByIdExiste(RegistroAtividade $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_inscrito FROM tab_registro_atividade WHERE cod_evento=\'%s\'
            and cod_reg_atividade= \'%s\'', $obj->getCodEvento(),$obj->getCodRegAtividade());

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
    public function getByDiaRegistroExiste(RegistroAtividade $obj, $debug=false) {
        try {
            $sql = sprintf('SELECT cod_inscrito FROM tab_registro_atividade WHERE cod_evento=\'%s\' and cod_inscrito= \'%s\' and dia= \'%s\'', $obj->getCodEvento(),$obj->getCodInscrito(),$obj->getDia());

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
    public function getAll(RegistroAtividade $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tiac.* , ti.nome_inscrito FROM tab_registro_atividade tiac
            LEFT JOIN tab_inscritos ti on tiac.cod_inscrito=ti.cod_inscrito AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            order by cod_evento desc ');

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaRegistroAtividades($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }       
    public function carregarListaRegistroAtividadesByIdEvento(RegistroAtividade $obj, $debug=false) {
        try{
            
            $sql = sprintf('SELECT tiac.* , ti.nome_inscrito FROM tab_registro_atividade tiac
            LEFT JOIN tab_inscritos ti on tiac.cod_inscrito=ti.cod_inscrito AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            WHERE tiac.cod_evento=\'%s\'
            order by dia desc ',$obj->getCodEvento());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaRegistroAtividades($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaRegistroAtividadesByIdCodInscrito(RegistroAtividade $obj,$condicoes=false, $debug=false) {
        try{
            
            $whereRegistroAtividaPeriodoApuracao = "";
            if (!empty($condicoes['periodo_apuracao_reg_ativ_ini']) && !empty($condicoes['periodo_apuracao_reg_ativ_fim'])) {
                $whereRegistroAtividaPeriodoApuracao = sprintf(" AND tiac.dia BETWEEN '%s' AND '%s' ", $condicoes['periodo_apuracao_reg_ativ_ini'], $condicoes['periodo_apuracao_reg_ativ_fim']);
            }

            $sql = sprintf('SELECT tiac.* , ti.nome_inscrito FROM tab_registro_atividade tiac
            LEFT JOIN tab_inscritos ti on tiac.cod_inscrito=ti.cod_inscrito AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            WHERE tiac.cod_evento=\'%s\' AND tiac.cod_inscrito=\'%s\'
            '.$whereRegistroAtividaPeriodoApuracao.'
            order by tiac.dia desc ',$obj->getCodEvento(),$obj->getCodInscrito());

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaRegistroAtividades($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function carregarListaRegistroAtividadesByIdNre(RegistroAtividade $obj, $debug=false) {
        try{
            $listaCodNre = implode("','", $obj->getListaCodNre());
            $sql = sprintf('SELECT tiac.*, ti.nome_inscrito FROM tab_registro_atividade tiac
            LEFT JOIN tab_inscritos ti on tiac.cod_inscrito=ti.cod_inscrito AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento
            LEFT JOIN tab_lig_inscritos_oficinas tilia on tiac.cod_inscrito=tilia.cod_inscrito AND tiac.cod_evento=tilia.cod_evento
            WHERE tiac.cod_evento=\'%s\' AND tilia.cod_oficina IN (\'%s\') and tiac.status=\'ATIVADA\' AND ti.status_processo=\'PARTICIPANDO\'
            order by tiac.dia desc ', $obj->getCodEvento(), $listaCodNre);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            if((!$rs)or($rs->EOF)) {
                return false;
            }else{
                foreach($rs as $rows){
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaRegistroAtividades($return);
                return true;                
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    // Lista todas as atividades do evento com filtros opcionais (gerente, status, ano, mes).
    // Usada pela tela de avaliacao do coordenador pedagogico.
    public function carregarListaByEvento(RegistroAtividade $obj, $filtros, $debug=false) {
        try{
            $where = sprintf("WHERE tiac.cod_evento=%d AND tiac.status='ATIVADA'", intval($obj->getCodEvento()));

            if (!empty($filtros['cod_inscrito_gerente'])) {
                $where .= sprintf(" AND tiac.cod_inscrito=%d", intval($filtros['cod_inscrito_gerente']));
            }
            if (!empty($filtros['avaliacao_status'])) {
                if ($filtros['avaliacao_status'] === 'AGUARDANDO') {
                    $where .= " AND (tiac.avaliacao_status IS NULL OR tiac.avaliacao_status='')";
                } else {
                    $where .= sprintf(" AND tiac.avaliacao_status=%s", $this->conex->qstr($filtros['avaliacao_status']));
                }
            }
            if (!empty($filtros['ano'])) {
                $where .= sprintf(" AND YEAR(tiac.dia)=%d", intval($filtros['ano']));
            }
            if (!empty($filtros['mes'])) {
                $where .= sprintf(" AND MONTH(tiac.dia)=%d", intval($filtros['mes']));
            }

            $sql = "SELECT tiac.*, ti.nome_inscrito
                    FROM tab_registro_atividade tiac
                    LEFT JOIN tab_inscritos ti ON ti.cod_inscrito=tiac.cod_inscrito AND ti.cod_evento=tiac.cod_evento
                    {$where}
                    ORDER BY tiac.dia DESC, tiac.cod_reg_atividade DESC";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $lista[] = clone $obj;
                }
            }
            $obj->setListaRegistroAtividades($lista);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    // Exportacao por periodo. $codInscrito vazio/0 = todos os gerentes (ordenado por nome).
    // Retorna array de linhas planas para o XLSX (sem subitens).
    public function getRegistrosParaExportacao($codEvento, $codInscrito, $dataInicio, $dataFim, $debug=false) {
        try {
            $codEvento = intval($codEvento);
            $codInscrito = intval($codInscrito);
            $dataInicioSql = $this->conex->qstr($dataInicio);
            $dataFimSql = $this->conex->qstr($dataFim);

            $filtroInscrito = $codInscrito > 0
                ? sprintf(" AND tiac.cod_inscrito = %d", $codInscrito)
                : "";

            $sql = sprintf("SELECT tiac.cod_reg_atividade, tiac.dia, tiac.dia_semana,
                    tiac.horas_trabalhadas, tiac.descricao,
                    tiac.avaliacao_status, tiac.avaliacao_responsavel, tiac.avaliacao_data,
                    ti.nome_inscrito
                FROM tab_registro_atividade tiac
                LEFT JOIN tab_inscritos ti ON ti.cod_inscrito = tiac.cod_inscrito AND ti.cod_evento = tiac.cod_evento
                WHERE tiac.cod_evento = %d
                    %s
                    AND tiac.status = 'ATIVADA'
                    AND tiac.dia BETWEEN %s AND %s
                ORDER BY ti.nome_inscrito ASC, tiac.dia ASC, tiac.cod_reg_atividade ASC",
                $codEvento, $filtroInscrito, $dataInicioSql, $dataFimSql);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $linhas = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $linhas[] = $row;
                }
            }
            return $linhas;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    // Lista de gerentes (pedagogico) que tem registro de atividade no evento.
    // Retorna array [{cod_inscrito, nome_inscrito}], ordenado por nome.
    public function carregarGerentesComAtividade($codEvento, $debug=false) {
        try{
            $sql = sprintf("SELECT DISTINCT tiac.cod_inscrito, ti.nome_inscrito
                FROM tab_registro_atividade tiac
                INNER JOIN tab_inscritos ti ON ti.cod_inscrito=tiac.cod_inscrito AND ti.cod_evento=tiac.cod_evento
                WHERE tiac.cod_evento=%d AND tiac.status='ATIVADA'
                ORDER BY ti.nome_inscrito", intval($codEvento));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $lista[] = $row;
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    // Retorna pessoas distintas e minutos somados com registro de atividade VALIDADO, agrupado por perfil e mes (YYYY-MM).
    /**
     * Conta inscritos atualmente com status_processo='PARTICIPANDO' por perfil,
     * para o evento. Snapshot do estado atual (nao depende de mes/ano).
     */
    public function carregarParticipandoPorPerfil($codEvento, $debug=false) {
        try {
            $sql = sprintf("
                SELECT
                    p.cod_perfil,
                    p.titulo AS perfil,
                    COUNT(DISTINCT ti.cod_inscrito) AS pessoas
                FROM tab_inscritos ti
                INNER JOIN tab_opcoes_perfis p
                    ON ti.cod_perfil = p.cod_perfil
                   AND p.cod_evento = ti.cod_evento
                WHERE ti.cod_evento = %d
                  AND ti.status = 'ATIVADA'
                  AND ti.status_processo = 'PARTICIPANDO'
                GROUP BY p.cod_perfil, p.titulo
                ORDER BY p.titulo
            ", intval($codEvento));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $lista[] = $row;
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public function carregarPessoasValidadasPorMesPerfil($codEvento, $debug=false) {
        try {
            // Conta apenas registros cujo dia e Util no calendario base
            // E que nao esta marcado como feriado especifico do evento.
            $sql = sprintf("
                SELECT
                    p.cod_perfil,
                    p.titulo AS perfil,
                    DATE_FORMAT(tiac.dia, '%%Y-%%m') AS ano_mes,
                    COUNT(DISTINCT tiac.cod_inscrito) AS pessoas,
                    COALESCE(SUM(tiac.horas_trabalhadas), 0) AS minutos
                FROM tab_registro_atividade tiac
                INNER JOIN tab_inscritos ti
                    ON tiac.cod_inscrito = ti.cod_inscrito
                   AND ti.status = 'ATIVADA'
                   AND ti.cod_evento = tiac.cod_evento
                INNER JOIN tab_opcoes_perfis p
                    ON ti.cod_perfil = p.cod_perfil
                INNER JOIN tab_calendario_base cb
                    ON cb.dia_calendario = tiac.dia
                   AND cb.tipo_dia = 'Util'
                WHERE tiac.cod_evento = %d
                  AND tiac.status = 'ATIVADA'
                  AND tiac.avaliacao_status = 'Validado'
                  AND tiac.dia IS NOT NULL
                  AND tiac.dia NOT IN (
                      SELECT fe.dia_calendario FROM tab_calendario_feriados_evento fe
                      WHERE fe.evento_id = %d
                  )
                GROUP BY p.cod_perfil, p.titulo, DATE_FORMAT(tiac.dia, '%%Y-%%m')
                ORDER BY p.titulo, ano_mes
            ", intval($codEvento), intval($codEvento));

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);

            $lista = array();
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) {
                    $lista[] = $row;
                }
            }
            return $lista;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return array();
        }
    }

    public static function getConsultaRegistroAtividadeLancadas($condicoes, $obj, $debug=false){
        $selectRegistroAtividade   = "";
        $leftJoinRegistroAtividade = "";
        $whereRegistroAtividade    = "";

        if ((!empty($condicoes['registro_atividade_status']))
        OR (!empty($condicoes['perspectiva']=='listagemComRegistroAtividade'))){
            
            if (!empty($condicoes['periodo_apuracao_reg_ativ_ini']) && !empty($condicoes['periodo_apuracao_reg_ativ_fim'])) {
                $whereRegistroAtividade = sprintf(" AND tiac.dia BETWEEN '%s' AND '%s' ", $condicoes['periodo_apuracao_reg_ativ_ini'], $condicoes['periodo_apuracao_reg_ativ_fim']);
            }
            
            $leftJoinRegistroAtividade = " inner join tab_registro_atividade tiac
             on tiac.cod_inscrito=ti.cod_inscrito AND ti.status=\'ATIVADA\' AND ti.cod_evento=tiac.cod_evento ";

             $selectRegistroAtividade   = " ,tiac.* ";
            
        }
        return [
            'select' => $selectRegistroAtividade,
            'leftJoin' => $leftJoinRegistroAtividade,
            'where' => $whereRegistroAtividade,
            'debug' => false
        ];
    }
    
    public static function getConsultaRegistroAtividade($condicoes, $obj, $debug=false){
        $selectRegistroAtividade   = "";
        $leftJoinRegistroAtividade = "";
        $whereRegistroAtividade    = "";
        if (!empty($condicoes['perspectiva']) && $condicoes['perspectiva']=='listagemComRegistroAtividadeExcel') {
            $selectRegistroAtividade   = " ,tavaliador.nome_inscrito as nome_avaliador, ti.nome_inscrito as nome_colaborador ,tiac.* ";
            $leftJoinRegistroAtividade = " inner join tab_registro_atividade tiac
             on tiac.cod_inscrito=ti.cod_inscrito AND ti.status='ATIVADA' AND ti.cod_evento=tiac.cod_evento 
             inner join tab_inscritos tavaliador on tiac.cod_avaliador=tavaliador.cod_inscrito AND tavaliador.cod_evento=tiac.cod_evento ";

            if (!empty($condicoes['periodo_apuracao_reg_ativ_ini']) && !empty($condicoes['periodo_apuracao_reg_ativ_fim'])) {
                $whereRegistroAtividade = sprintf(" AND tiac.dia BETWEEN '%s' AND '%s' ", $condicoes['periodo_apuracao_reg_ativ_ini'], $condicoes['periodo_apuracao_reg_ativ_fim']);
            }
        }else 
        if ((!empty($condicoes['registro_atividade_status']))
        OR (!empty($condicoes['perspectiva']=='listagemComRegistroAtividade'))){
            $codEventoVinculado = (int) $condicoes['cod_evento_vinculado'];
    
            //$listaCodNre = implode("','", $obj->getListaCodNre());

            $whereRegistroAtividaPeriodoApuracao = "";
            if (!empty($condicoes['periodo_apuracao_reg_ativ_ini']) && !empty($condicoes['periodo_apuracao_reg_ativ_fim'])) {

                /** @todo, aqui temos todos os dias de fim de semana e feriados para não calcular descontos de lançamentos nesses dias */
                $whereRegistroAtividaPeriodoApuracao = sprintf(" AND dia BETWEEN '%s' AND '%s' AND dia NOT IN (
                     '2025-03-01',
                     '2025-03-02', '2025-03-04',
                     '2025-03-08', '2025-03-09',
                     '2025-03-15', '2025-03-16',
                     '2025-03-22', '2025-03-23',
                     '2025-03-29', '2025-03-30',
                     '2025-04-05', '2025-04-06',
                     '2025-04-12', '2025-04-13',
                     '2025-04-17', '2025-04-18',
                     '2025-04-19', '2025-04-20',
                     '2025-04-21',
                     '2025-04-26', '2025-04-27',
                     '2025-05-01', '2025-05-02',
                     '2025-05-03', '2025-05-04',
                     '2025-05-10', '2025-05-11',
                     '2025-05-17', '2025-05-18',
                     '2025-05-24', '2025-05-25',
                     '2025-05-31',
                     '2025-06-01', '2025-06-07', '2025-06-08', '2025-06-14', '2025-06-15', '2025-06-19', '2025-06-21', '2025-06-22', '2025-06-28', '2025-06-29',
                     '2025-07-05','2025-07-06','2025-07-12','2025-07-13','2025-07-19','2025-07-20','2025-07-26','2025-07-27',
                     '2025-08-02', '2025-08-03', '2025-08-09', '2025-08-10', '2025-08-16', '2025-08-17', '2025-08-23', '2025-08-24', '2025-08-30', '2025-08-31',
                    '2025-09-06','2025-09-07','2025-09-13','2025-09-14','2025-09-20','2025-09-21','2025-09-27','2025-09-28',
                    '2025-10-04','2025-10-05','2025-10-11','2025-10-12','2025-10-18','2025-10-19','2025-10-25','2025-10-26',
                    '2025-11-01','2025-11-02','2025-11-08','2025-11-09','2025-11-15','2025-11-16','2025-11-20','2025-11-21','2025-11-22','2025-11-23','2025-11-29','2025-11-30','2025-12-06','2025-12-07',
                    '2025-12-13','2025-12-14','2025-12-20','2025-12-21','2025-12-25','2025-12-27','2025-12-28',
                     '2025-12-25',
                    '2025-12-19','2025-12-20','2025-12-21','2025-12-22','2025-12-23','2025-12-24',
                    '2025-12-25','2025-12-26','2025-12-27','2025-12-28','2025-12-29','2025-12-30','2025-12-31',
                    '2026-01-01','2026-01-02','2026-01-03','2026-01-04',   
                    '2026-01-10','2026-01-11','2026-01-17','2026-01-18','2026-01-24','2026-01-25','2026-01-31',
                    '2026-02-01','2026-02-07','2026-02-08','2026-02-14','2026-02-15','2026-02-18','2026-02-17','2026-02-18','2026-02-21','2026-02-22','2026-02-28'
                    ,'2026-03-01','2026-03-07','2026-03-08','2026-03-14','2026-03-15','2026-03-21','2026-03-22','2026-03-28','2026-03-29',
'2026-04-03','2026-04-04','2026-04-05','2026-04-11','2026-04-12','2026-04-18','2026-04-19','2026-04-21','2026-04-25','2026-04-26',
'2026-05-01','2026-05-02','2026-05-03','2026-05-09','2026-05-10','2026-05-16','2026-05-17','2026-05-23','2026-05-24','2026-05-30','2026-05-31',
'2026-06-04','2026-06-06','2026-06-07','2026-06-13','2026-06-14','2026-06-20','2026-06-21','2026-06-27','2026-06-28',
'2026-07-04','2026-07-05','2026-07-11','2026-07-12','2026-07-18','2026-07-19','2026-07-25','2026-07-26',
'2026-08-01','2026-08-02','2026-08-08','2026-08-09','2026-08-15','2026-08-16','2026-08-22','2026-08-23','2026-08-29','2026-08-30',
'2026-09-05','2026-09-06','2026-09-07','2026-09-12','2026-09-13','2026-09-19','2026-09-20','2026-09-26','2026-09-27',
'2026-10-03','2026-10-04','2026-10-10','2026-10-11','2026-10-12','2026-10-17','2026-10-18','2026-10-24','2026-10-25','2026-10-31',
'2026-11-01','2026-11-02','2026-11-07','2026-11-08','2026-11-14','2026-11-15','2026-11-20','2026-11-21','2026-11-22','2026-11-28','2026-11-29',
'2026-12-05','2026-12-06','2026-12-12','2026-12-13','2026-12-19','2026-12-20','2026-12-25','2026-12-26','2026-12-27'                    
                     )
                     ", $condicoes['periodo_apuracao_reg_ativ_ini'], $condicoes['periodo_apuracao_reg_ativ_fim']);

                //$whereRegistroAtividaPeriodoApuracao = sprintf(" AND dia BETWEEN '%s' AND '%s' AND dia NOT IN (
                //    SELECT DATA FROM tab_calendario_base WHERE tipo_dia <>'Util'
                //    )
                //    ", $condicoes['periodo_apuracao_reg_ativ_ini'], $condicoes['periodo_apuracao_reg_ativ_fim']);                

                // Obtém o mês da data inicial
                $mes = obterMesData($condicoes['periodo_apuracao_reg_ativ_ini']);
                $anoMes = obterAnoMesData($condicoes['periodo_apuracao_reg_ativ_ini']);

                $horas_contratadas_dia = 6; // 6 horas por dia 360 minutos
                $hora_total = 7200;
                $valor_mes = 4750;
                $horas_somar_feriado = 0;  
                $vlr_remuneracao_por_minuto = 0.66; 
                $feriados = 0; # 0 dias
                // Se for fevereiro (mês 02), ajusta os valores
                if ($anoMes == '2025-02') {
                    $hora_total = 3600;
                    $valor_mes = 2375;
                } 
                if ($anoMes == '2025-03') {
                    $horas_somar_feriado = 360; # 1 dia
                }  
                /**@todo, todo mes precisa calcular o total de horas uteis e horas de feriados, para de fato só descontar as horas não trabalhadas em dias uteis */
                if ($anoMes == '2025-04') {
                    $hora_total = 7920;
                    $horas_somar_feriado = 1080; # 2 dias
                }   
                 
                if ($anoMes == '2025-05') {
                    //  $hora_total = 7920;
                     $hora_total = 7200;
                     $feriados = 2; # 0 dias
                     //$horas_somar_feriado = 720; # 2 dias
                }   
                /** @todo colocamos quantidade horas uteis no mes e a quantidade de veriados
                 *  os dias que são feriados não são descontados e nem somados.
                 */
                if ($anoMes == '2025-06') {
                    //  $hora_total = 7920;
                     $hora_total = 6840;
                     $feriados = 2; # 0 dias
                    //  Londrina
                    //  $hora_total = 6480;
                    //  $feriados = 3; # 0 dias
                    //  $horas_somar_feriado = 1080; # 3 dias
                }   
                if ($anoMes == '2025-07') {
                     $hora_total = 8280;
                     $feriados = 0; # 0 dias
                     $vlr_remuneracao_por_minuto = 0.66;
                }   
                if ($anoMes == '2025-08') {
                     $hora_total = 7560;
                     $feriados = 0; # 0 dias
                     $vlr_remuneracao_por_minuto = 0.66;
                }   
                if ($anoMes == '2025-09') {
                     $hora_total = 7920;
                     $feriados = 0; # 0 dias
                     $vlr_remuneracao_por_minuto = 0.66;
                }   
                if ($anoMes == '2025-10') {
                     $hora_total = 8280;
                     $feriados = 0; # 0 dias
                     $vlr_remuneracao_por_minuto = 0.66;
                }   
                if ($anoMes == '2025-11') {
                     $hora_total = 6480;
                     $feriados = 0; # 0 dias
                     $vlr_remuneracao_por_minuto = 0.66;
                }   
                if ($anoMes == '2025-12') {
                     $hora_total = 5040;
                     $feriados = 0; # 0 dias
                     $vlr_remuneracao_por_minuto = 0.66;
                }
                if ($anoMes == '2026-01') {
                     $hora_total = 7200;
                     $feriados = 0; # 0 dias
                     $vlr_remuneracao_por_minuto = 0.66;
                } 
                if ($anoMes == '2026-02') {
                     $hora_total = 6480;
                     $feriados = 0; # 0 dias
                     $vlr_remuneracao_por_minuto = 0.66;
                } 
                if ($anoMes == '2026-03') {
                     $hora_total = 7920;
                     $feriados = 0; # 0 dias
                     $vlr_remuneracao_por_minuto = 0.66;
                } 
                /**gerado por ia */
if ($anoMes == '2026-04') {
     $hora_total = 7200; // 20 dias úteis
     $feriados = 2;
     $vlr_remuneracao_por_minuto = 0.66;
} 
if ($anoMes == '2026-05') {
     $hora_total = 7200; // 20 dias úteis
     $feriados = 1;
     $vlr_remuneracao_por_minuto = 0.66;
} 
if ($anoMes == '2026-06') {
     $hora_total = 6720; // 18 dias úteis
     $feriados = 1;
     $vlr_remuneracao_por_minuto = 0.66;
} 
if ($anoMes == '2026-07') {
     $hora_total = 8280; // 23 dias úteis
     $feriados = 0;
     $vlr_remuneracao_por_minuto = 0.66;
} 
if ($anoMes == '2026-08') {
     $hora_total = 7560; // 21 dias úteis
     $feriados = 0;
     $vlr_remuneracao_por_minuto = 0.66;
} 
if ($anoMes == '2026-09') {
     $hora_total = 7560; // 21 dias úteis
     $feriados = 1;
     $vlr_remuneracao_por_minuto = 0.66;
} 
if ($anoMes == '2026-10') {
     $hora_total = 7560; // 21 dias úteis
     $feriados = 1;
     $vlr_remuneracao_por_minuto = 0.66;
} 
if ($anoMes == '2026-11') {
     $hora_total = 6840; // 19 dias úteis (30 dias - 9 fds - 02/11 - 20/11; 15/11 cai domingo)
     $feriados = 2;
     $vlr_remuneracao_por_minuto = 0.66;
} 
if ($anoMes == '2026-12') {
     $hora_total = 7920; // 22 dias úteis
     $feriados = 1;
     $vlr_remuneracao_por_minuto = 0.66;
}       
                // if ($mes == '12') {
                //      $hora_total = 7920;
                //      $feriados = 0; # 0 dias
                //      $vlr_remuneracao_por_minuto = 0.66;
                // }   
                // $vlr_remuneracao_por_minuto = 0.66;  
                // if ($mes == '06') {
                //     $hora_total = 7920;
                //     $horas_somar_feriado = 1080; # 3 dias
                // }   
                // $vlr_remuneracao_por_minuto = 0.66;  

                // $selectVlrAPagar = "
                // , IFNULL(tra.horas_nao_trabalhadas,0) as horas_nao_trabalhadas
                // , replace(replace(format(IFNULL(tra.valor_a_pagar,0),2),',',''),'.',',') as valor_a_pagar ";
                $selectVlrAPagar = "
                , IFNULL(tra.horas_nao_trabalhadas,0) as horas_nao_trabalhadas
                , replace(replace(format(IFNULL(tra.horas_nao_trabalhadas*".$vlr_remuneracao_por_minuto.",0),2),',',''),'.',',') as  valor_a_descontar
                , replace(replace(format(IFNULL(4750 - tra.horas_nao_trabalhadas*".$vlr_remuneracao_por_minuto.",0),2),',',''),'.',',') as valor_a_pagar";
                
                // $sqlValorAPagar = " , ".$valor_mes." - ((".$hora_total."-SUM(CASE WHEN avaliacao_status = 'Validado' THEN horas_trabalhadas ELSE 0 END)) * ".$vlr_remuneracao_por_minuto.") AS valor_a_pagar 
                //     ,".$hora_total."-SUM(CASE WHEN avaliacao_status = 'Validado' THEN horas_trabalhadas ELSE 0 END) as horas_nao_trabalhadas"; 
                    
                $sqlValorAPagar = " 
                , ".$valor_mes." - (".$hora_total."-(".$horas_somar_feriado."+SUM(CASE WHEN avaliacao_status = 'Validado' THEN horas_trabalhadas ELSE 0 END)) * ".$vlr_remuneracao_por_minuto.") AS valor_a_descontar 
                , ".$hora_total."-(".$horas_somar_feriado."+SUM(CASE WHEN avaliacao_status = 'Validado' THEN horas_trabalhadas ELSE 0 END)) as horas_nao_trabalhadas ";                  
            }

            $selectRegistroAtividade = " 
            , ti.status_processo
            , IFNULL(tra.total_registro_atividades,0) as total_registro_atividades 
            , IFNULL(tra.total_regist_ativ_validados,0) as total_regist_ativ_validados 
            , IFNULL(tra.total_regist_ativ_nao_validados,0) as total_regist_ativ_nao_validados
            , IFNULL(tra.total_carga_horaria_validados,0) as total_carga_horaria_validados
            , IFNULL(tra.total_carga_horaria_nao_validados,0) as total_carga_horaria_nao_validados 
            ".$selectVlrAPagar."
            ";

            $leftJoinRegistroAtividade = "
            LEFT JOIN (SELECT count(cod_reg_atividade) as total_registro_atividades, cod_inscrito, cod_evento
            , SUM(CASE WHEN avaliacao_status = 'Validado' THEN 1 ELSE 0 END) AS total_regist_ativ_validados
            , SUM(CASE WHEN avaliacao_status = 'Não Validado' OR avaliacao_status IS NULL THEN 1 ELSE 0 END) AS total_regist_ativ_nao_validados
            , ".$horas_somar_feriado."+SUM(CASE WHEN avaliacao_status = 'Validado' THEN horas_trabalhadas ELSE 0 END)
             AS total_carga_horaria_validados
            , SUM(CASE WHEN avaliacao_status = 'Não Validado' OR avaliacao_status IS NULL THEN horas_trabalhadas ELSE 0 END) AS total_carga_horaria_nao_validados   
            ". $sqlValorAPagar ."
            FROM tab_registro_atividade WHERE status='ATIVADA'
            ". $whereRegistroAtividaPeriodoApuracao . "
            GROUP BY cod_inscrito, cod_evento) AS tra 
            ON tra.cod_inscrito=ti.cod_inscrito AND tra.cod_evento=ti.cod_evento ";
    

                // $whereRegistroAtividade = $whereRegistroAtividaPeriodoApuracao;
                if ($condicoes['registro_atividade_status'] == 'REGISTROU') {
                    $whereRegistroAtividade .= " AND tra.total_registro_atividades > 0 ";
                } elseif ($condicoes['registro_atividade_status'] == 'NAO_REGISTROU') {
                    $whereRegistroAtividade .= " AND IFNULL(tra.total_registro_atividades,0) = 0 ";
                }elseif ($condicoes['registro_atividade_status'] == 'REGISTRO_VALIDADO') {
                    $whereRegistroAtividade .= " AND tra.total_regist_ativ_validados > 0 ";
                } elseif ($condicoes['registro_atividade_status'] == 'REGISTRO_NAO_VALIDADO') {
                    $whereRegistroAtividade .= " AND AND tra.total_regist_ativ_nao_validados > 0 ";
                } elseif ($condicoes['registro_atividade_status'] == 'VALIDADO') {
                    $whereRegistroAtividade .= " AND tiac.avaliacao_status IS NOT NULL ";
                } elseif ($condicoes['registro_atividade_status'] == 'NAO_VALIDADO') {
                    $whereRegistroAtividade .= " AND tiac.avaliacao_status IS NULL ";
                } elseif ($condicoes['registro_atividade_status'] == 'PAGO') {
                    $whereRegistroAtividade .= " AND tiac.avaliacao_status IS NULL ";
                } elseif ($condicoes['registro_atividade_status'] == 'NAO_AVALIADO') {
                    $whereRegistroAtividade .= " AND tiac.avaliacao_status IS NULL ";
                } elseif ($condicoes['registro_atividade_status'] == 'REGISTRADO') {
                    $whereRegistroAtividade .= " AND (tiac.cod_registro <> '' or tiac.cod_registro is not null)";
                } else {
                    // $whereRegistroAtividade .= " AND (tiac.cod_registro <> '' or tiac.cod_registro is not null) ";
                }
            
        }
    
        return [
            'select' => $selectRegistroAtividade,
            'leftJoin' => $leftJoinRegistroAtividade,
            'where' => $whereRegistroAtividade,
            'debug' => false
        ];
    }

    public function insert(RegistroAtividade $obj, $debug=false) {
        try {           
            $obj->setDataInclusao(date('Y-m-d H:i:s'));
            $obj->setIdInclusao($_SESSION['_cod_usuario']);
            $obj->setSessionIdInclusao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpInclusao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentInclusao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);
            $obj->setStatus('ATIVADA');
            
                        
            $sql = "SELECT * FROM tab_registro_atividade WHERE cod_evento=".$obj->getCodEvento()." and cod_reg_atividade=0"; 
            $rs = $this->conex->Execute($sql);     
            
            $arrayColunas = $this->conex->MetaColumnNames("tab_registro_atividade");
            $fws_ = $obj->getAllForArray($arrayColunas,false);
            
            $insertSQL = $this->conex->GetInsertSQL($rs ,$fws_, true, true);
            $sql = sprintf($insertSQL);
            
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setCodRegAtividade($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function update(RegistroAtividade $obj, $debug=false) {
        try {           
            $obj->setDataAlteracao(date('Y-m-d H:i:s'));
            $obj->setIdAlteracao($_SESSION['_cod_usuario']);
            $obj->setSessionIdAlteracao($_SESSION['userLogado']['_cod_inscrito']);
            $obj->setIpAlteracao($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlteracao($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao()+1);
            $obj->setStatus('ATIVADA');
            
            $sql = "SELECT * FROM tab_registro_atividade WHERE cod_evento = ".$obj->getCodEvento()." and cod_reg_atividade = ".$obj->getCodRegAtividade(); 
            $rs = $this->conex->Execute($sql); 
            $arrayColunas = $this->conex->MetaColumnNames("tab_registro_atividade");
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
    public function delete(RegistroAtividade $obj, $debug=false) {
        try {
            $sql = sprintf('DELETE FROM tab_registro_atividade WHERE cod_evento=\'%s\' and cod_reg_atividade= \'%s\'', $obj->getCodEvento(),$obj->getCodRegAtividade());
            
            $this->conex->debug = $debug;
            
            $rs = $this->conex->Execute($sql);
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }    
}
