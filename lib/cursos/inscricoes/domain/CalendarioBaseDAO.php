<?php

class CalendarioBaseDAO extends DAO
{
    public function __construct()
    {
        $this->tableName = "tab_calendario_base";
        parent::__construct();
    }

    public function carregarAnosDisponiveis($debug = false)
    {
        try {
            $sql = "SELECT DISTINCT ano FROM {$this->tableName} WHERE ano IS NOT NULL ORDER BY ano DESC";
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $anos = [];
            if ($rs && !$rs->EOF) {
                foreach ($rs as $row) { $anos[] = (int) $row['ano']; }
            }
            return $anos;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return [];
        }
    }

    public function getById(CalendarioBase $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT * FROM ' . $this->tableName . ' WHERE ano=\'%s\' AND id = \'%s\'',
                $obj->getAno(), $obj->getId()
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) {
                return false;
            } else {
                foreach ($rs as $rows) { }
                $obj->setAll($rows);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function getByIdExiste(CalendarioBase $obj, $debug = false)
    {
        try {
            $sql = sprintf('SELECT id FROM ' . $this->tableName . ' WHERE ano=\'%s\' AND id = \'%s\'',
                $obj->getAno(), $obj->getId()
            );
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return (!$rs || $rs->EOF) ? false : true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaCalendarioBaseByIdEvento(CalendarioBase $obj, $debug = false)
    {
        try {
            $whereAno = $obj->getAno() ? sprintf(" WHERE ano='%s' ", intval($obj->getAno())) : "";
            $sql = 'SELECT * FROM ' . $this->tableName . $whereAno . ' ORDER BY dia_calendario';
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) {
                return false;
            } else {
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaCalendarioBases($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function carregarListaCalendarioBaseByIdEventoRegAtividade(CalendarioBase $obj, $debug = false)
    {
        try {
            // 1. Pega os feriados do evento
            // 2. Pega os dias da base, exceto os que já existem no feriado
            $sql = sprintf('SELECT fe.dia_calendario, fe.tipo_dia, YEAR(fe.dia_calendario) AS ano, MONTH(fe.dia_calendario) AS mes
             FROM tab_calendario_feriados_evento fe WHERE fe.evento_id = %s UNION ALL
                SELECT cb.dia_calendario, cb.tipo_dia, cb.ano, cb.mes FROM tab_calendario_base cb
                WHERE   cb.mes = \'%s\' AND cb.ano = \'%s\' AND NOT EXISTS (
                    SELECT 1 FROM tab_calendario_feriados_evento fe
                    WHERE fe.evento_id = \'%s\' AND fe.dia_calendario = cb.dia_calendario
                ) ORDER BY dia_calendario', $obj->getCodEvento(), $obj->getMes(), $obj->getAno(), $obj->getCodEvento());
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if (!$rs || $rs->EOF) {
                return false;
            } else {
                foreach ($rs as $rows) {
                    $obj->setAll($rows);
                    $return[] = clone $obj;
                }
                $obj->setListaCalendarioBases($return);
                return true;
            }
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function insert(CalendarioBase $obj, $debug = false)
    {
        try {
            $obj->setDatIncl(date('Y-m-d H:i:s'));
            $obj->setResIncl($_SESSION['_cod_usuario']);
            $obj->setIpIncl($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentIncl($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao(1);

            $sql = "SELECT * FROM $this->tableName WHERE ano=" . $obj->getAno() . " AND id=0";
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $insertSQL = $this->conex->GetInsertSQL($rs, $fws_, true, false);
            $sql = sprintf($insertSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            $obj->setId($this->conex->Insert_ID());
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function update(CalendarioBase $obj, $debug = false)
    {
        try {
            $obj->setDatAlter(date('Y-m-d H:i:s'));
            $obj->setResAlter($_SESSION['_cod_usuario']);
            $obj->setIpAlter($_SERVER['REMOTE_ADDR']);
            $obj->setHttpUserAgentAlter($_SERVER['HTTP_USER_AGENT']);
            $obj->setVersao($obj->getVersao() + 1);

            $sql = "SELECT * FROM " . $this->tableName . " WHERE ano=" . $obj->getAno() . " AND id=" . $obj->getId();
            $rs = $this->conex->Execute($sql);
            $arrayColunas = $this->conex->MetaColumnNames($this->tableName);
            $fws_ = $obj->getAllForArray($arrayColunas, false);
            $updateSQL = $this->conex->GetUpdateSQL($rs, $fws_, true, false, true);
            $sql = sprintf($updateSQL);

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return $rs;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function delete(CalendarioBase $obj, $debug = false)
    {
        try {
            $sql = sprintf('DELETE FROM ' . $this->tableName . ' WHERE ano=\'%s\' AND id= \'%s\'',
                $obj->getAno(), $obj->getId()
            );
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql) !== false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }
    public function excluirAno($ano, $debug = false) {
        try {
            $sql = sprintf("DELETE FROM %s WHERE ano='%s'", $this->tableName, intval($ano));
            $this->conex->debug = $debug;
            return $this->conex->Execute($sql) !== false;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    public function contarAno($ano, $debug = false) {
        try {
            $sql = sprintf("SELECT COUNT(*) AS total FROM %s WHERE ano='%s'", $this->tableName, intval($ano));
            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            return ($rs && !$rs->EOF) ? intval($rs->fields['total']) : 0;
        } catch (ADODB_Exception $e) {
            return 0;
        }
    }

    public function gerarCalendarioSQL($ano, $debug = false) {
        try {
            $ano = intval($ano);
            $dataInicio = "$ano-01-01";
            $dataFim = "$ano-12-31";

            $this->excluirAno($ano, $debug);

            $sql = "
                INSERT INTO {$this->tableName} (dia_calendario, dia_semana, mes, ano, tipo_dia, eh_fim_semana, dat_incl, res_incl)
                SELECT
                    DATE_ADD('$dataInicio', INTERVAL seq DAY) AS dia_calendario,
                    ELT(DAYOFWEEK(DATE_ADD('$dataInicio', INTERVAL seq DAY)),
                        'Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado') AS dia_semana,
                    MONTH(DATE_ADD('$dataInicio', INTERVAL seq DAY)) AS mes,
                    $ano AS ano,
                    CASE
                        WHEN DAYOFWEEK(DATE_ADD('$dataInicio', INTERVAL seq DAY)) IN (1,7) THEN 'Fim de Semana'
                        ELSE 'Util'
                    END AS tipo_dia,
                    CASE
                        WHEN DAYOFWEEK(DATE_ADD('$dataInicio', INTERVAL seq DAY)) IN (1,7) THEN 1
                        ELSE 0
                    END AS eh_fim_semana,
                    NOW() AS dat_incl,
                    '" . $_SESSION['_cod_usuario'] . "' AS res_incl
                FROM (
                    SELECT @row := @row + 1 AS seq
                    FROM (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL
                                SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t1,
                         (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL
                                SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t2,
                         (SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL
                                SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t3,
                         (SELECT @row := -1) t4
                ) dias
                WHERE DATE_ADD('$dataInicio', INTERVAL seq DAY) <= '$dataFim'
            ";

            $this->conex->debug = $debug;
            $rs = $this->conex->Execute($sql);
            if ($rs === false) return false;

            // Marca feriados nacionais oficiais (datas fixas + Sexta-feira Santa movel).
            $feriados = $this->calcularFeriadosNacionais($ano);
            foreach ($feriados as $data => $nome) {
                $sqlFer = sprintf(
                    "UPDATE %s SET tipo_dia='Feriado Nacional', eh_feriado=1, descricao=%s WHERE dia_calendario='%s'",
                    $this->tableName,
                    $this->conex->qstr($nome),
                    $data
                );
                $this->conex->Execute($sqlFer);
            }
            return true;
        } catch (ADODB_Exception $e) {
            $this->enviarMensagem($e);
            return false;
        }
    }

    /**
     * Retorna [data => nome] dos feriados nacionais oficiais do ano.
     * Datas fixas + Sexta-feira Santa (movel, calculada via algoritmo de Meeus/Jones/Butcher).
     * Carnaval, Corpus Christi e Quinta-feira Santa NAO sao feriados nacionais (sao pontos facultativos).
     */
    private function calcularFeriadosNacionais($ano)
    {
        $ano = intval($ano);
        $feriados = [
            sprintf('%04d-01-01', $ano) => 'Confraternização Universal',
            sprintf('%04d-04-21', $ano) => 'Tiradentes',
            sprintf('%04d-05-01', $ano) => 'Dia do Trabalho',
            sprintf('%04d-09-07', $ano) => 'Independência do Brasil',
            sprintf('%04d-10-12', $ano) => 'N. Sra. Aparecida',
            sprintf('%04d-11-02', $ano) => 'Finados',
            sprintf('%04d-11-15', $ano) => 'Proclamação da República',
            sprintf('%04d-11-20', $ano) => 'Consciência Negra',
            sprintf('%04d-12-25', $ano) => 'Natal',
        ];
        // Sexta-feira Santa = Pascoa - 2 dias
        $pascoa = $this->calcularPascoa($ano);
        $sextaSanta = date('Y-m-d', strtotime($pascoa . ' -2 days'));
        $feriados[$sextaSanta] = 'Sexta-feira Santa';
        return $feriados;
    }

    /**
     * Algoritmo de Meeus/Jones/Butcher para calcular o domingo de Pascoa.
     * Independe da extensao calendar do PHP (que pode nao estar instalada).
     */
    private function calcularPascoa($ano)
    {
        $a = $ano % 19;
        $b = intdiv($ano, 100);
        $c = $ano % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $mes = intdiv($h + $l - 7 * $m + 114, 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;
        return sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
    }
}
