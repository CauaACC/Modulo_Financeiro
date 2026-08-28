<?php

class PessoaDAO extends DAO implements DAOInterface {

    public function PessoaDAO() {
        $this->setNomeTabela("pessoa");
        parent::__construct();
    }

    public function carregandoWhere(EntityInterface $obj) {
        $WHERE = " cod_pessoa = " . $obj->getCodPessoa();
        $this->setWhere($WHERE);
    }

    public function getAll(EntityInterface $obj, $debug = false) {
        if ($obj->getCodInstituicao()) {
            $WHERE = "WHERE cod_instituicao = " . $obj->getCodInstituicao();
        }
        $sql = sprintf('SELECT * FROM ' . $this->getNomeTabela() . ' 
             ' . $WHERE . '
            ORDER BY nome');
        $lista = $this->carregarLista($obj, $sql, $debug);
        $obj->setListaPessoa($lista);
        return true;
    }

    public function getLastId(EntityInterface $obj, $debug = false) {
        $sql = 'SELECT COALESCE(MAX(cod_Pessoa),0)+1 FROM ' . $this->getNomeTabela();
        return $this->carregarLastId($sql, $debug);
    }

    public function getById(EntityInterface $obj, $debug = false) {
        $this->carregandoWhere($obj);
        return $this->carregarObjeto($obj, $debug);
    }

    public function getByCpf(EntityInterface $obj, $debug = false) {
        $WHERE = " cpf = " . $obj->getCpf();
        $this->setWhere($WHERE);
        return $this->carregarObjeto($obj, $debug);
    }

    public function getByEmail(EntityInterface $obj, $debug = false) {
        $WHERE = " email = " . $obj->getEmail();
        $this->setWhere($WHERE);
        return $this->carregarObjeto($obj, $debug);
    }

    public function getExisteById(EntityInterface $obj, $debug = false) {
        $this->carregandoWhere($obj);
        return $this->existeRegistro($debug);
    }

    public function insert(EntityInterface $obj, $debug = false) {
        $obj->setCodPessoa(0);
        $this->carregandoWhere($obj);
        $obj->setCodPessoa($this->getLastId($obj, $debug));
        $obj->setDatIni(Date('Y-m-d'));
        return $this->executarInsert($obj, $debug);
    }

    public function update(EntityInterface $obj, $debug = false) {
        $this->carregandoWhere($obj);
        $obj->setDatAlter(Date('Y-m-d'));
        return $this->executarUpdate($obj, $debug);
    }

    public function delete(EntityInterface $obj, $debug = false) {
        $this->carregandoWhere($obj);
        return $this->executarDelete($obj);
    }

    public function desativar(EntityInterface $obj, $debug = false) {
        $this->carregandoWhere($obj);
        return $this->executarDesativacao($obj);
    }

    public function carregarListaPessoaNomeEmailCpf(EntityInterface $obj, $debug = false) {

        $maiusculo = mb_strtoupper($obj->getNome());

        $whereSql = '';
        if ($obj->getCodPessoa() != '') {
            $whereSql = ' p.cod_pessoa= ' . $obj->getCodPessoa() . ' ';
        } else {
            $whereSql = " upper(nome) like '%" . $maiusculo . "%' and p.cpf like '%" . $obj->getCpf() . "%' 
                and email like '%" . $obj->getEmail() . "%' ";
        }

        $sql = "SELECT p.*, ap.cod_participante, ou.cod_participante as cod_ouvinte, 
            pf.cod_professor, tec.cod_tecnico, proj.cpf as cpf_pessoa_projeto from pessoa p
            left join professor pf on pf.cod_professor = p.cod_pessoa
            left join apresentador ap on ap.cod_participante = p.cod_pessoa
            left join ouvinte ou on ou.cod_participante = p.cod_pessoa
            left join tecnico tec on tec.cod_tecnico = p.cod_pessoa
            left join pessoa_projeto proj on proj.cpf = p.cpf
            WHERE 
                $whereSql
                order by nome;";

        $lista = $this->carregarLista($obj, $sql, $debug);
        $obj->setListaPessoa($lista);
        return true;
    }

    public function carregarListaPessoaRefeicaoFiltro(EntityInterface $obj, $fws_, $debug = false) {
        $sql = "SELECT p.cod_pessoa, p.nome, p.email, p.cpf, pf.cod_refeicao, inst.sigla, ap.pago, 
            CASE
            WHEN cod_refeicao='J-01-10-14' THEN '1'
            WHEN cod_refeicao='A-02-10-14' THEN '2'
            WHEN cod_refeicao='J-02-10-14' THEN '3'
            WHEN cod_refeicao='A-03-10-14' THEN '4'
            END as ordem
            from pessoa p
            inner join pessoa_refeicao pf on pf.cod_pessoa = p.cod_pessoa
            inner join participante_evento pe on pe.cod_participante = p.cod_pessoa
            inner join instituicao inst on inst.cod_instituicao = pe.cod_instituicao 
            inner join apresentador ap on ap.cod_participante = p.cod_pessoa
            where p.cpf like '%" . $fws_['cpf'] . "%'";
        if ($fws_['cod_refeicao'] != "0") {
            $sql.=" and pf.cod_refeicao = '" . $fws_['cod_refeicao'] . "'";
        }
        if ($fws_['instituicao'] != "0") {
            $sql.=" and inst.cod_instituicao = '" . $fws_['instituicao'] . "'";
        }
        if ($fws_['pago'] != "0") {
            $sql.=" and ap.pago = '" . $fws_['pago'] . "'";
        }
        $sql .= " order by ordem, p.nome;";
        $lista = $this->carregarLista($obj, $sql, $debug);
        $obj->setListaPessoa($lista);
        return true;
    }

    public function carregarListaPessoaRefeicaoGroupBy(EntityInterface $obj, $fws_, $debug = false) {
        $sql = "SELECT p.nome, p.cod_pessoa, p.email, p.cpf, inst.sigla, COUNT(pf.cod_refeicao) as quantidade, ap.pago, ap.valor_boleto            
            from pessoa p
            inner join pessoa_refeicao pf on pf.cod_pessoa = p.cod_pessoa
            inner join participante_evento pe on pe.cod_participante = p.cod_pessoa
            inner join instituicao inst on inst.cod_instituicao = pe.cod_instituicao 
            inner join apresentador ap on ap.cod_participante = p.cod_pessoa
            where p.cpf like '%" . $fws_['cpf'] . "%'";
        if ($fws_['instituicao'] != "0") {
            $sql.=" and inst.cod_instituicao = '" . $fws_['instituicao'] . "'";
        }
        if ($fws_['pago'] != "0") {
            $sql.=" and ap.pago = '" . $fws_['pago'] . "'";
        }
        $sql .= "group by p.nome, p.cod_pessoa, p.email, p.cpf, inst.sigla, ap.pago, ap.valor_boleto
            order by p.nome";
        $lista = $this->carregarLista($obj, $sql, $debug);
        $obj->setListaPessoa($lista);
        return true;
    }

    public function carregarListaEstatistica(EntityInterface $obj, $debug = false) {
        $sql = "select cod_refeicao, count(*) as total,
            CASE
            WHEN cod_refeicao='J-01-10-14' THEN '1'
            WHEN cod_refeicao='A-02-10-14' THEN '2'
            WHEN cod_refeicao='J-02-10-14' THEN '3'
            WHEN cod_refeicao='A-03-10-14' THEN '4'
            END as ordem
            from pessoa_refeicao group by cod_refeicao
            order by ordem;";
        $lista = $this->carregarLista($obj, $sql, $debug);
        $obj->setListaPessoa($lista);
        return true;
    }

    public function carregarListaFuncao(EntityInterface $obj, $debug = false) {
        $sql = "select c.cod_funcao, f.nome as nome_funcao from certificado c
            inner join funcao f on f.cod_funcao=c.cod_funcao
            where c.cod_pessoa = " . $obj->getCodPessoa() . " 
            order by cod_funcao ;";
        $lista = $this->carregarLista($obj, $sql, $debug);
        $obj->setListaFuncao($lista);
        return true;
    }

    public function carregarListaEnsalamentoVisualizarImpressao(EntityInterface $obj, $fws_, $debug = false) {
        $sql = "SELECT sessao.ordem, sessao.cod_sessao,artigo.cod_artigo, upper(artigo.titulo) as titulo, p.nome as pessoa,p.cod_pessoa,
                sessao.data as data_sessao, sessao.hora_inicio, sessao.hora_fim, bloco.nome as bloco, sala.cod_sala, sala.nome as nome_sala, 
		instituicao.nome as instituicao, instituicao.sigla, projeto.nome as projeto, o.nome as orientador, c.nome as coordenador_sessao,
                artigo.cod_area_cnpq, ac.des_area_cnpq, artigo.cod_sub_area, sub_area.nome as subarea, sub_area.cod_area, area.nome as area 
                , (select count(cod_artigo) from artigo_sessao where cod_sessao=sessao.cod_sessao group by cod_sessao) as total_artigos_sessao
            FROM artigo 
            INNER JOIN artigo_sessao ON artigo.cod_artigo = artigo_sessao.cod_artigo
            INNER JOIN sessao        ON artigo_sessao.cod_sessao = sessao.cod_sessao
            INNER JOIN sala          ON sessao.cod_sala = sala.cod_sala
            INNER JOIN pessoa p      ON artigo.cod_participante = p.cod_pessoa
            INNER JOIN apresentador apre ON artigo.cod_participante = apre.cod_participante AND apre.cod_forma in (1)
            LEFT JOIN pessoa o on o.cod_pessoa = apre.cod_professor
            INNER JOIN projeto       ON apre.cod_projeto = projeto.cod_projeto
            INNER JOIN participante_evento ON artigo.cod_participante = participante_evento.cod_participante
            INNER JOIN instituicao   ON participante_evento.cod_instituicao = instituicao.cod_instituicao
            INNER JOIN sub_area      ON artigo.cod_sub_area = sub_area.cod_sub_area
            INNER JOIN area          ON sub_area.cod_area = area.cod_area
            INNER JOIN bloco on sala.cod_bloco=bloco.cod_bloco
            LEFT JOIN pessoa c on c.cod_pessoa = sessao.coord_sessao
            left JOIN area_cnpq ac on artigo.cod_area_cnpq = ac.cod_area_cnpq
            WHERE true ";
            
        //$sql.=" and sala.nome like '%%'";
        if ($fws_['instituicao'] != "0" and $fws_['instituicao'] != null) {
            $sql.=" and instituicao.cod_instituicao = " . $fws_['instituicao'];
        }
        if ($fws_['cod_area'] != "0" and $fws_['cod_area'] != null) {
            $sql.=" and area.cod_area = " . $fws_['cod_area'];
        }
        if ($fws_['subarea'] != "0" and $fws_['subarea'] != null) {
            $sql.=" and sub_area.cod_sub_area = " . $fws_['subarea'];
        }
        if ($fws_['sala'] != "0" and $fws_['sala'] != null) {
            $sql.=" and sala.cod_sala = " . $fws_['sala'];
        }
        if ($fws_['cod_bloco'] != "0" and $fws_['cod_bloco'] != null) {
            $sql.=" and sala.cod_bloco = " . $fws_['cod_bloco'];
        }
        if ($fws_['data_sessao'] != "0" and $fws_['data_sessao'] != null) {
            $sql.=" and sessao.data = '" . $fws_['data_sessao'] . "'";
        }
        //$sql.=" ORDER BY sessao.data, sessao.cod_sessao, sessao.hora_inicio, bloco.nome, sala.nome, area.nome, artigo.cod_area_cnpq, sub_area.nome, UPPER(p.nome)";
        $sql.=" ORDER BY bloco.nome, sessao.ordem, sessao.data, sessao.hora_inicio, sala.nome, area.nome, artigo.cod_area_cnpq, sub_area.nome, UPPER(p.nome)";

//        if($fws_['ocultarArtigos'] ==4){
//            $sql.=" ORDER BY instituicao.cod_instituicao, bloco.nome, sessao.ordem, sessao.data, sessao.hora_inicio, sala.nome, area.nome, artigo.cod_area_cnpq, sub_area.nome, UPPER(p.nome)";
//        }else{
//            $sql.=" ORDER BY bloco.nome, sessao.ordem, sessao.data, sessao.hora_inicio, sala.nome, area.nome, artigo.cod_area_cnpq, sub_area.nome, UPPER(p.nome)";
//        }
        //echo $sql."<hr>";
        $lista = $this->carregarLista($obj, $sql, $debug);
        $obj->setListaPessoa($lista);
        return true;
    }

    public function carregarListaAlunosAprovados(EntityInterface $obj, $fws_, $debug = false) {

//            INNER JOIN artigo_sessao ON artigo.cod_artigo = artigo_sessao.cod_artigo
//            INNER JOIN sessao        ON artigo_sessao.cod_sessao = sessao.cod_sessao
//            INNER JOIN sala          ON sessao.cod_sala = sala.cod_sala
//            INNER JOIN bloco on sala.cod_bloco=bloco.cod_bloco
//            LEFT JOIN pessoa c on c.cod_pessoa = sessao.coord_sessao
//                sessao.cod_sessao,artigo.cod_artigo,sessao.data as data_sessao, sessao.hora_inicio, sessao.hora_fim, 
//                sala.cod_sala, sala.nome as nome_sala, bloco.nome as bloco, o.nome as orientador, c.nome as coordenador_sessao,
//                , (select count(cod_artigo) from artigo_sessao where cod_sessao=sessao.cod_sessao group by cod_sessao) as total_artigos_sessao
//                    
        $sql = "SELECT upper(artigo.titulo) as titulo, p.nome as pessoa,p.cod_pessoa,
		instituicao.nome as instituicao, instituicao.sigla, projeto.nome as projeto,
                artigo.cod_area_cnpq, ac.des_area_cnpq, artigo.cod_sub_area, sub_area.nome as subarea, sub_area.cod_area, area.nome as area 
            FROM artigo 
            INNER JOIN pessoa p      ON artigo.cod_participante = p.cod_pessoa
            INNER JOIN apresentador apre ON artigo.cod_participante = apre.cod_participante
            LEFT JOIN pessoa o on o.cod_pessoa = apre.cod_professor
            INNER JOIN projeto       ON apre.cod_projeto = projeto.cod_projeto
            INNER JOIN participante_evento ON artigo.cod_participante = participante_evento.cod_participante
            INNER JOIN instituicao   ON participante_evento.cod_instituicao = instituicao.cod_instituicao
            INNER JOIN sub_area      ON artigo.cod_sub_area = sub_area.cod_sub_area
            INNER JOIN area          ON sub_area.cod_area = area.cod_area
            left JOIN area_cnpq ac on artigo.cod_area_cnpq = ac.cod_area_cnpq
            inner join historico_artigo ha on ha.cod_artigo=artigo.cod_artigo and ha.cod_status=7 
                and ha.cod_historico in (select max(cod_historico) from historico_artigo group by cod_artigo)
            WHERE p.nome <>'' ";

        if ($fws_['cod_forma'] != "0" and $fws_['cod_forma'] != null) {
            $sql.=" AND apre.cod_forma in (" . $fws_['cod_forma'] . ")";
        }
        if ($fws_['instituicao'] != "0" and $fws_['instituicao'] != null) {
            $sql.=" and instituicao.cod_instituicao = " . $fws_['instituicao'];
        }
        if ($fws_['cod_area'] != "0" and $fws_['cod_area'] != null) {
            $sql.=" and area.cod_area = " . $fws_['cod_area'];
        }
        if ($fws_['subarea'] != "0" and $fws_['subarea'] != null) {
            $sql.=" and sub_area.cod_sub_area = " . $fws_['subarea'];
        }
        if ($fws_['sala'] != "0" and $fws_['sala'] != null) {
            $sql.=" and sala.cod_sala = " . $fws_['sala'];
        }
        if ($fws_['data_sessao'] != "0" and $fws_['data_sessao'] != null) {
            $sql.=" and sessao.data = '" . $fws_['data_sessao'] . "'";
        }
        $sql.=" ORDER BY instituicao.sigla, p.nome ";

        //echo $sql."<hr>";
        $lista = $this->carregarLista($obj, $sql, $debug);
        $obj->setListaPessoa($lista);
        return true;
    }

}

?>