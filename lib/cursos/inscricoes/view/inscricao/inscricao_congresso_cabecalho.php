<?php
if (($_SESSION['_logado']!='1')and($_SESSION['_inscrito']!='1')) {#Testa se o sessao logado
    $codRetorno = "efetuarLogin";
    if($_SESSION['_administrativo']=='1'){
        $url = WB_URL_VIEW."?content=view/admin/default.php";
        throw new Exception("Você não tem acesso a área do inscrito!");
    }else{    
        $url = WB_URL_VIEW."?content=view/inscricao/inscricao_01.php";
        throw new Exception("Para acessar esta area é necessário estar logado!");
    }
}

if((addslashesopctec($_GET['acao'])=='logarComo') and ($_SESSION['_administrativo']=='1')){
    @$SIS_RECEPCAO_PARTICIPANTES = 11;
    @$cod_msg = false;
    @$redirect = false;
    If (FWA_Autenticacao($_SESSION['_cod_usuario'], @$SIS_RECEPCAO_PARTICIPANTES, 5, $cod_msg, $redirect)) {
        GerenciarLoginAC::efetuarLoginComo($fws_, $debug);
    }else{
        echo "Para logar como, é necessário perfil: Recepção de participantes (".$_SESSION['_administrativo']."-".$_SESSION['_cod_usuario'].")";
        exit();
    }
}
if($_SESSION['_logadoComo'] == 'inscrito'){
    $whereEvento = " cod_evento=".$_SESSION['_cod_evento'];
    $whereCarregarInscrito = " and (i.cod_inscrito = '" . $_SESSION['_cod_inscrito'] . "')";
}else{
    $whereEvento = " cod_evento=".COD_EVENTO;
    $whereCarregarInscrito = " and (i.cod_inscrito = '" . $_SESSION['_cod_inscrito'] . "')";
    $whereCarregarInscrito .= " and (i.e_mail = '" . $_SESSION['_login'] . "')";
}
$var_sql = "SELECT i.nome_inscrito
                ,i.cod_inscrito
                ,i.rg
                ,i.cpf
                ,i.e_mail
                ,i.status
                ,i.endereco
                ,i.numero
                ,i.bairro
                ,i.cep
                ,i.cidade
                ,i.estado
                ,i.data_inscricao
                ,i.desconto
                ,(SELECT Count(t.cod_trabalho) FROM tab_trabalhos as t, tab_opcoes_areas_tematicas as oat, tab_inscritos ti
                 WHERE
                     (t.cod_evento        = i.cod_evento)
                 and (t.cod_inscrito      = i.cod_inscrito)
                 and (t.status           = 'ATIVADA')
                 and (t.cod_area_tematica = oat.cod_area_tematica)) as total
                ,i.nome_apelido
                ,i.cod_evento
                ,i.id_publico
           FROM tab_inscritos as i
           WHERE
           ".$whereEvento ."
           ".$whereCarregarInscrito."
           and (i.status      = 'ATIVADA')
           ORDER BY i.cod_inscrito DESC limit 1
          ";
#Fazendo conexão com o banco
$RS = $Conexao_db->Execute($var_sql);
//$Conexao_db->cacheSecs = 0;
//if($_SERVER['REMOTE_ADDR'] == '200.203.142.25'){
//    echo $var_sql;
//}
//$RS = $Conexao_db->cacheExecute($var_sql);

#Verificando se houve retorno de algum registro
if ($RS->EOF) {#Se cpf Esta inscrito
    $codRetorno = "verificarEmailInscrito";
    $url = "". WB_URL_VIEW."?content=view/inscricao/inscricao_01.php";
    throw new Exception("Login não encontrado.");
}

while (!$RS->EOF) {

    #Adicionando nas variaveis, os valores dos campos retirados dos registros
    $nome_inscrito = $RS->fields[0];
    $cod_inscrito = $RS->fields[1];
    $rg = $RS->fields[2];
    $cpf = $RS->fields[3];
    $e_mail = mb_strtolower($RS->fields[4]);
    $status = $RS->fields[5];
    $endereco = $RS->fields[6];
    $numero = $RS->fields[7];
    $bairro = $RS->fields[8];
    $cep = $RS->fields[9];
    $cidade = $RS->fields[10];
    $estado = $RS->fields[11];
    $data_inscricao = $RS->fields[12];
    $desconto = $RS->fields[13];
    $total = $RS->fields[14];
    $nome_apelido = $RS->fields[15];
    $cod_evento = $RS->fields[16];
    $id_publico = $RS->fields[17];
    if(!$nome_apelido){
        $nome_apelido= $nome_inscrito;
    }
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <FORM NAME='exit' METHOD='POST' action="<?php echo WB_URL_COMMAND?>">
            <input type="hidden" name="c" value="GerenciarLogin">
            <h4>Ola <?php echo $nome_apelido?>.
            &nbsp;&nbsp;&nbsp;
            <INPUT TYPE='hidden' VALUE='Voltar' NAME='acao'>
            <?php if($_SESSION['_logadoComo']=='inscrito'){?>
                <input type="hidden" name="m" value="efetuarLogoutLogarComo">
                <button class="btn btn-xs btn-danger" type="submit"><i class="fa fa-sign-out"></i> Sair de Logar Como</button>
            <?php }else{?>
                <input type="hidden" name="m" value="efetuarLogout">
                <button class="btn btn-xs btn-primary" type="submit">Sair</button>
            <?php }?>

            </h4>
        </FORM>
    </div>
    <div class="panel-body">
    <?php
    if(COD_EVENTO==9){
        $nome_inscrito = textoMaiusculoMinusculo($nome_inscrito);
        echo "Nome do Pagador: {$nome_inscrito}";
        echo "
        <br>Número de Inscrição: {$cod_inscrito}
        <br>Identificador Público: {$id_publico}
        <br>E-mail:              {$e_mail} 
        <br><br>";
        $OP_EDICAO_DADOS_PESSOAIS = false;
    }else if ($content!='view/inscricao/inscricao_congresso_hall_virtual.php' 
    and $content!='view/inscricao/inscricao_congresso_apresentacao_virtual.php'
    and $content!='view/inscricao/inscricao_congresso_apresentacao_abertura.php'
    and $content!='view/inscricao/inscricao_congresso_apresentacao_conferencia_1.php'
    and $content!='view/inscricao/inscricao_congresso_apresentacao_conferencia_2.php'
    and $content!='view/inscricao/inscricao_congresso_apresentacao_conferencia_abertura.php') {
        $nome_inscrito = textoMaiusculoMinusculo($nome_inscrito);
        if($objEv->getCodEvento()==37 or $objEv->getCodEvento()==38){
            echo "Nome do Candidato: ";
        }else{
            echo $objEvConf->getDadosPessoaisNome()!=''? $objEvConf->getDadosPessoaisNome() : "Nome para o Certificado: ";
        }
        echo " {$nome_inscrito}";
        echo "
        <br>Número de Inscrição: {$cod_inscrito}
        <br>Identificador Público: {$id_publico}
        <br>E-mail:              {$e_mail} 
        <br><br>";
    }else{
        $OP_EDICAO_DADOS_PESSOAIS = false;
    }
    ?>
    
    <?php
    /*Permitir a edição de minicurso durante o logar como*/
//    @$SIS_RECEPCAO_PARTICIPANTES = 11;
//    @$cod_msg = false;
//    @$redirect = false;
//    If (FWA_Autenticacao($_SESSION['_cod_usuario'], @$SIS_RECEPCAO_PARTICIPANTES, 5, $cod_msg, $redirect)) {
//        $OP_EDICAO_DADOS_PESSOAIS = true;
//    }
    ?>
    <?php if($OP_EDICAO_DADOS_PESSOAIS){?>
    <a href='index.php?content=view/inscricao/inscricao_congresso_cadastro_dados_pessoais.php'>
        <button class="btn btn-primary" role='button'>Editar Dados de sua Inscrição</button></a>
    <?php }?>
    <?php if ($OP_TIPO_FLUXO_INSCRICAO == 'AGENDA') {
        // Força recriar com o cod_inscrito atual da sessão (logarComo pode ter trocado)
        $objInscrito = new Inscrito();
        $objInscrito->setCodInscrito($_SESSION['_cod_inscrito']);
        $objInscrito->setCodEvento($cod_evento);
        $objInscrito->getInscritoById($debug);
        require_once 'inscricao_consulta_minhas_agendas.php';
    } ?>
    <br>
    <?php if($OP_EDICAO_DEPENDENTES){?>
    <!-- <a href='index.php?content=view/admin/GerenciarInscritoDependentes.php'>
        <button class="btn btn-xs btn-primary" role='button'>Cadastro de dependente</button></a> -->
    <?php }?>
    </div>
</div>
<?php
    #Movendo para o proximo registro
    $RS->MoveNext();
}
?>
<?php // Heartbeat de PRESENCA para o monitor de logados. Este cabecalho so roda
      // para inscrito logado, entao o ping so dispara logado. m=heartbeat NAO
      // renova a sessao. Aba oculta nao pinga; jitter evita rajada sincronizada. ?>
<script type="text/javascript">
(function(){
    var HB_URL = '<?php echo WB_URL_COMMAND ?>&c=PingSessao&m=heartbeat';
    var hbHandle = null;
    function baterPresenca(){
        if (document.hidden) return;            // aba em segundo plano nao pinga
        try {
            var x = new XMLHttpRequest();
            x.open('POST', HB_URL + '&t=' + Date.now(), true);
            x.onreadystatechange = function(){
                if (x.readyState !== 4) return;
                var d = null; try { d = JSON.parse(x.responseText); } catch (e) {}
                // sessao morreu com a aba aberta -> aviso PROATIVO, reusando o
                // overlay do sessao-expirada.js (cobre a tela + vai ao login).
                if (d && d.online === false) {
                    if (hbHandle) { clearInterval(hbHandle); hbHandle = null; }
                    if (typeof window.sessaoExpiradaMostrar === 'function') {
                        window.sessaoExpiradaMostrar('inscrito');
                    }
                }
            };
            x.send();
        } catch (e) {}
    }
    baterPresenca();                            // primeiro imediato
    hbHandle = setInterval(baterPresenca, 60000 + Math.floor(Math.random() * 15000));
})();
</script>