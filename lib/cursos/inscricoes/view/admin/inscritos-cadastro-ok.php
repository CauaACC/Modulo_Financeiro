<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-cabecalho.php");
require_once(dirname(__FILE__)."/../../view/admin/inscritos-cadastro-localiza.php");
// CSS do cartao das abas (uma vez por pagina). Os helpers de montagem ficam em
// include/functions/functions.php, que o config.php ja carrega.
echo "<link href='resources/css/cartao-listagem.css' rel='stylesheet'>";

$MSG   = $_SESSION['MSG'];
$debug = false;

//$cod_evento    = trim(addslashesopctec($_GET['cod_evento']));
$cod_inscrito  = $_GET['cod_inscrito']? trim(addslashesopctec($_GET['cod_inscrito'])) : null;
$CPF           = $_GET['cpf']? trim(addslashesopctec($_GET['cpf'])) : null;
$NOME_INSCRITO = $_GET['nome_inscrito']? trim(addslashesopctec($_GET['nome_inscrito'])) : null;
$E_MAIL = $_GET['e_mail']? trim(addslashesopctec($_GET['e_mail'])) : null;
$FONE_CONTATO = $_GET['fone_contato']? trim(addslashesopctec($_GET['fone_contato'])) : null;

$objI = new Inscrito();
if($cod_inscrito){
    $objI->setCodInscrito($cod_inscrito);
    $objI->getInscritoByIdSemEvento();
}
if($objI->getCodEvento()!=''){
    $objI->carregarObjEvento();
    $objEv = $objI->getObjEvento();
    $cod_evento = $objI->getCodEvento(); 
    echo "<h3>".$objEv->getCodEvento()." - ".$objEv->getTitulo()."</h3>";
}else{
    $cod_evento = COD_EVENTO; 
}

If($cod_inscrito!='' or $CPF!='' or $NOME_INSCRITO!='' or $E_MAIL!=''){

    $whereNomeInscrito = "";
    if($NOME_INSCRITO!=''){
        $whereNomeInscrito = " AND (ti.nome_inscrito like '%".$NOME_INSCRITO."%')";
    }
    $whereCpfInscrito = "";
    if($CPF!=''){
        $whereCpfInscrito = " AND (ti.cpf = '".$CPF."')";
    }
    $whereCodInscrito = "";
    if($cod_inscrito!=''){
        $whereCodInscrito = " AND(ti.cod_inscrito = ".$cod_inscrito.")";
    }       
    $whereEMail = "";
    if($E_MAIL!=''){
        $whereEMail = " AND(ti.e_mail = '".$E_MAIL."')";
    }       
    $whereFoneContato = "";
    if($FONE_CONTATO!=''){
        $whereFoneContato = " AND(ti.fonte_contato like '%".$FONE_CONTATO."%')";
    }       
  $Conexao_db = FWA_Conexao("OpcTec");

  $var_Sql = "SELECT
                ti.cod_inscrito,
                ti.nome_inscrito,
                ti.cpf,
                ti.rg,
                ti.e_mail,
                ti.data_nascimento,
                ti.sexo,
                ti.profissao_atuacao,
                ti.grau_escolaridade,
                ti.instituicao,
                ti.endereco,
                ti.numero,
                ti.complemento,
                ti.bairro,
                ti.cep,
                ti.cidade,
                ti.estado,
                ti.uf,
                ti.fone_residencial,
                ti.fone_comercial,
                ti.fone_celular,
                ti.desconto,
                ti.data_inscricao,
                ti.data_alteracao,
                ti.http_user_agent,
                ti.ip_cliente,
                ti.status,
                ti.session_id,
                ti.cod_perfil,
                top.titulo as titulo_perfil
,ti.nome_recibo       
,ti.institucional     
,ti.endereco_recibo   
,ti.numero_recibo     
,ti.complemento_recibo
,ti.bairro_recibo     
,ti.cep_recibo        
,ti.cidade_recibo     
,ti.estado_recibo        
,ti.dat_incl
,ti.info_almoco
,ti.cpf_recibo  
,tis.cod_isento
,ti.impressao_cracha,
                ti.nome_apelido,
                ti.tipo_instituicao,
                ti.tipo_inscricao
                ,ti.e_mail_2
                FROM tab_inscritos ti
                left join tab_isentos tis on tis.cpf=ti.cpf
                inner join tab_opcoes_perfis top on ti.cod_perfil=top.cod_perfil
                WHERE
                (ti.cod_evento   = ".$cod_evento.")
                ".$whereCodInscrito."
                ".$whereCpfInscrito."
                ".$whereNomeInscrito."
                ".$whereEMail."
                ".$whereFoneContato."
                
               ";

  If($RS=FWA_Executa_SQL($debug,$Conexao_db,$var_Sql)){
    If (!$RS->EOF) {      
        $iii = 0;
      While (!$RS->EOF) {
          $iii++;
          $RS->MoveNext();
      }
      echo "<h4>Total de Registros Localizados: ".$iii."</h4>";
      $RS->MoveFirst();
    }
    If (!$RS->EOF) {
      While (!$RS->EOF) {
          $ii++;
      #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $cod_inscrito          = $RS->fields[0];
        $nome_inscrito         = $RS->fields[1];
        $cpf                   = $RS->fields[2];
        $rg                    = $RS->fields[3];
        $e_mail                = $RS->fields[4];
        $data_nascimento       = formatDateView($RS->fields[5]);
        $sexo                  = $RS->fields[6];
        $profissao_atuacao     = $RS->fields[7];
        $grau_escolaridade     = $RS->fields[8];
        $instituicao           = $RS->fields[9];;
        $endereco              = $RS->fields[10];
        $numero                = $RS->fields[11];
        $complemento           = $RS->fields[12];
        $bairro                = $RS->fields[13];
        $cep                   = $RS->fields[14];
        $cidade                = $RS->fields[15];
        $estado                = $RS->fields[16];
        $uf                    = $RS->fields[17];
        $fone_residencial      = $RS->fields[18];
        $fone_comercial        = $RS->fields[19];
        $fone_celular          = $RS->fields[20];
        $desconto              = $RS->fields[21];
        $data_inscricao        = $RS->fields[22];
        $data_alteracao        = $RS->fields[23];
        $http_user_agent       = $RS->fields[24];
        $ip_cliente            = $RS->fields[25];
        $status                = $RS->fields[26];
        $session_id            = $RS->fields[27];
        $cod_perfil            = $RS->fields[28];
        $titulo_perfil         = $RS->fields[29];
$nome_recibo       = $RS->fields[30];
$institucional     = $RS->fields[31];
$endereco_recibo   = $RS->fields[32];
$numero_recibo     = $RS->fields[33];
$complemento_recibo= $RS->fields[34];
$bairro_recibo     = $RS->fields[35];
$cep_recibo        = $RS->fields[36];
$cidade_recibo     = $RS->fields[37];
$estado_recibo     = $RS->fields[38];
$dat_incli         = $RS->fields[39];
$info_almoco       = $RS->fields[40];
$cpf_recibo        = formatCpfCnpjView($RS->fields[41]);
$cod_isento        = $RS->fields[42];
$impressao_cracha        = $RS->fields[43];
$nome_apelido = $RS->fields[44];
$tipo_instituicao = $RS->fields[45];
$tipo_inscricao = $RS->fields[46];
$e_mail_2 = $RS->fields[47];      
        
$objInscrito = new Inscrito();
$objInscrito->setCodInscrito($cod_inscrito);
$objInscrito->setCodEvento($cod_evento);
$objInscrito->getInscritoById();
//dd($objInscrito);

// echo "Gerado em: ".Date('Y-m-d H:i:s');
        #Definindo tabela com resultado
        if ($DATA_INSCRICAO != ''){
            $DATA_INSCRICAO = substr($data_inscricao,6,2)."/".substr($data_inscricao,4,2)."/".substr($data_inscricao,0,4)." - ".substr($data_inscricao,8,2)."h".substr($data_inscricao,10,2);
            $DATA_INSCRICAO = formatDateView($dat_incli);
        }

        $statusIsento = $objInscrito->getDesIsento();
        $statusCracha = $objInscrito->getDesImpressaoCracha();
        $flg_presenca_inscrito = $objInscrito->getDesFlgPresencaInscrito();

echo "
      - <a href='index.php?content=view/admin/GerenciarIsento.php'>
      Listar Isentos</a>
      <br>
      
      <hr size='1' width='100%'>
      <h4>Nome: {$nome_inscrito} </h4>
      {$cod_inscrito} - Status: <b>{$status}</b> - Presença: <b>{$flg_presenca_inscrito}</b>  -  ";
      echo "Data Inscrição: ".formatDateView($objInscrito->getDatIncl())."<br>";
        if (in_array($objEvConf->getGatewayPagamento(), ['UNIPRIME', 'SISPRIME']) or $objEvConf->getCodEvento()==45) {
        echo "<a title='Listar Boletos' target='_blank'
        href='index.php?content=view/admin/GerenciarBoletoAvulso.php&codInscrito={$cod_inscrito}&cod_evento=".$cod_evento."'>
        <img src='resources/image/boleto-1.png' width='30px' height='25' border='0'></a>";
        }
      ?>
    <?php if ($objInscrito->getStatus()=='ATIVADA' and $objInscrito->getQrCode()!=''){ ?>
    <a title='Listar Boletos' target="_blank" href='<?php echo "https://".FWA_Dominio()."/".$objInscrito->getUrlPrefixo()?>/inscricoes/<?php echo  $objInscrito->getQrCode() ?>'>
        <img src='<?php echo  WB_URL_IMG ?>/boleto-1.png'  border='0' height="25" width='30px'></a>
    <?php } ?>       
<?php
echo " 
      <a href='index.php?content=view/admin/inscritos-cadastro-incluir.php&cod_evento=".$cod_evento."'>
      <img src='resources/image/fig-incluir.gif' border='0' width='20px'></a>
              
      <a href='index.php?content=view/admin/inscritos-cadastro-ok.php&acao=Visualizar&cod_inscrito={$cod_inscrito}&cod_evento=".$cod_evento."' title='Editar Inscrição'>
      <img src='resources/image/fig-alterar.gif' border='0'></a>

      <a href='view/admin/inscritos-cadastro-excluir-grava.php?acao=Excluir&cpf={$CPF}&cod_inscrito={$cod_inscrito}&cod_evento=".$cod_evento."'>
      <img src='resources/image/fig-excluir.gif' title='Excluir Inscrição' border='0'></a>
      
      <a href='view/admin/inscritos-cadastro-ativar-grava.php?acao=Ativar&cpf={$CPF}&cod_inscrito={$cod_inscrito}&cod_evento=".$cod_evento."'>
      <img src='resources/image/fig-ativar.gif' title='Ativar Inscrição' border='0'></a>

      <a href='view/admin/inscritos-cadastro-desativar-grava.php?acao=Desativar&cpf={$CPF}&cod_inscrito={$cod_inscrito}&cod_evento=".$cod_evento."'>
      <img src='resources/image/fig-desativar.gif' title='Desativar Inscrição' border='0'></a>
      
      <a target='_blank' href='index.php?content=view/inscricao/inscricao_congresso_ok.php&acao=logarComo&cpf={$CPF}&cod_inscrito={$cod_inscrito}&cod_evento=".$cod_evento."'>
      <img src='resources/image/icone_preview.gif' title='Logar Como' border='0'></a>
        
        &nbsp;|&nbsp; Desconto:       <b> {$desconto}      </b>
        &nbsp;|&nbsp; Isento:         <b> {$statusIsento}    </b>
        &nbsp;|&nbsp; Crachá:         <b> {$statusCracha}    </b>
        &nbsp;|&nbsp; Data Inscrição: <b> {$DATA_INSCRICAO}</b>
      <br>&nbsp;
     ";
?>

<br>
<?php if ($objInscrito->getCodIsento()) { ?>
    <a class="show" id="btnExcluirIsento-<?php echo $objInscrito->getCodInscrito() ?>" title="Remover cadastrado da lista de isentos" href='javascript:void(0);' onclick='javascript:excluirIsento(<?php echo  $objInscrito->getCodInscrito() ?>);'>
        <img src='<?php echo  WB_URL_IMG ?>/fig-excluir.gif' alt='Excluir isenção' border='0' width="15px"> Excluir Isenção</a>     
    <a class="hide" id="btnAdicionarIsento-<?php echo $objInscrito->getCodInscrito() ?>" title="Adicionar cadastrado na lista de isentos" href="javascript:void(0);" onclick="javascript:adicionarIsento(<?php echo  $objInscrito->getCodInscrito() ?>,'<?php echo  $objInscrito->getNomeInscrito() ?>','<?php echo  $objInscrito->getCpf() ?>','<?php echo  $objInscrito->getEMail() ?>');">
        <img src='<?php echo  WB_URL_IMG ?>/abre.gif' alt='Adicionar cadastrado na lista de isentos' border='0' width="15px">Adicionar Isenção</a>  
<?php }else{ ?> 
    <a class="hide" id="btnExcluirIsento-<?php echo $objInscrito->getCodInscrito() ?>" title="Remover cadastrado da lista de isentos" href='javascript:void(0);' onclick='javascript:excluirIsento(<?php echo  $objInscrito->getCodInscrito() ?>);'>
        <img src='<?php echo  WB_URL_IMG ?>/fig-excluir.gif' alt='Excluir isenção' border='0' width="15px"> Excluir Isenção</a>     
    <a class="show" id="btnAdicionarIsento-<?php echo $objInscrito->getCodInscrito() ?>" title="Adicionar cadastrado na lista de isentos" href="javascript:void(0);" onclick="javascript:adicionarIsento(<?php echo  $objInscrito->getCodInscrito() ?>,'<?php echo  $objInscrito->getNomeInscrito() ?>','<?php echo  $objInscrito->getCpf() ?>','<?php echo  $objInscrito->getEMail() ?>');">
        <img src='<?php echo  WB_URL_IMG ?>/abre.gif' alt='Adicionar cadastrado na lista de isentos' border='0' width="15px">Adicionar Isenção</a>  
<?php } ?>  
<input type="hidden" value="<?php echo  $objInscrito->getCodIsento() ?>" id="codInscritoIsento-<?php echo $objInscrito->getCodInscrito()?>">
<div id="txtInscritoIsento-<?php echo $objInscrito->getCodInscrito() ?>">
    <?php
    if($objInscrito->getCodIsento()){
        echo "Cód.Isento: ".$objInscrito->getCodIsento()."<br>";
    }
    ?>   
</div>

<?php if ($objInscrito->getCodVoucher()) { ?>
<a class="show" id="btnExcluirInscritoVoucher-<?php echo $objInscrito->getCodInscrito() ?>" title="Remover voucher cadastrado" href='javascript:void(0);' onclick="javascript:excluirInscritoVoucher(<?php echo  $objInscrito->getCodInscrito() ?>);">                         
    <img src='<?php echo  WB_URL_IMG ?>/fig-excluir.gif' alt='Excluir voucher' border='0' width="15px"> Excluir Voucher</a>     
<a class="hide" id="btnAdicionarInscritoVoucher-<?php echo $objInscrito->getCodInscrito() ?>" title="Adicionar voucher para o inscrito" href="javascript:void(0);" onclick="javascript:adicionarInscritoVoucher('<?php echo  $objInscrito->getCodInscrito() ?>');">
    <img src='<?php echo  WB_URL_IMG ?>/abre.gif' alt='Adicionar cadastrado na lista de isentos' border='0' width="15px">Adicionar Voucher</a>  
<?php }else{ ?> 
<a class="hide" id="btnExcluirInscritoVoucher-<?php echo $objInscrito->getCodInscrito() ?>" title="Remover voucher cadastrado" href='javascript:void(0);' onclick="javascript:excluirInscritoVoucher(<?php echo  $objInscrito->getCodInscrito() ?>);">
    <img src='<?php echo  WB_URL_IMG ?>/fig-excluir.gif' alt='Excluir voucher' border='0' width="15px"> Excluir Voucher</a>     
<a class="show" id="btnAdicionarInscritoVoucher-<?php echo $objInscrito->getCodInscrito() ?>" title="Adicionar voucher para o inscrito" href="javascript:void(0);" onclick="javascript:adicionarInscritoVoucher('<?php echo  $objInscrito->getCodInscrito() ?>');">
    <img src='<?php echo  WB_URL_IMG ?>/abre.gif' alt='Adicionar cadastrado na lista de isentos' border='0' width="15px">Adicionar Voucher</a>  
<?php } ?> 
<input type="hidden" value="<?php echo  $objInscrito->getCodVoucher() ?>" id="codInscritoVoucher-<?php echo $objInscrito->getCodInscrito()?>">
<div id="txtInscritoVoucher-<?php echo $objInscrito->getCodInscrito() ?>">
    <?php
    if($objInscrito->getCodVoucher()){
        echo "Cód.Voucher: ".$objInscrito->getCodVoucher()."<br>";
    }
    ?>   
</div>


<div id="msgEnviarSenha"></div>
<button class="btn btn-xs btn-primary" type="button" onclick="enviarSenhaAdmin()">Enviar senha para</button>
<?php echo mb_strtolower($e_mail) ?>
<script type="text/javascript">
function enviarSenhaAdmin() {
    $.ajax({
        type: "POST",
        url: '<?php echo WB_URL_COMMAND?>',
        data: {'c': 'GerenciarLogin', 'm': 'recuperarSenhaAdmin', 'e_mail': '<?php echo mb_strtolower($e_mail) ?>', '_submit_check': '1'},
        dataType: "json",
        cache: false,
        beforeSend: function() { startLoading(); },
        success: function(data) {
            if (data.statusMessage == '1') {
                $('#msgEnviarSenha').html('<div class="alert alert-success alert-dismissible" style="margin-top:5px;"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fa fa-check"></i> ' + data.message + '</div>');
            } else {
                $('#msgEnviarSenha').html('<div class="alert alert-danger alert-dismissible" style="margin-top:5px;"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fa fa-times"></i> ' + (data.message || 'Erro ao enviar.') + '</div>');
            }
        },
        error: function() {
            $('#msgEnviarSenha').html('<div class="alert alert-danger" style="margin-top:5px;">Erro de comunica&ccedil;&atilde;o.</div>');
        },
        complete: function() { stopLoading(); }
    });
}
</script>

<a target='_blank' class='btn btn-xs' href='<?php
                                echo "https://api.whatsapp.com/send?phone=".limpaFoneWhats($objInscrito->getFoneContatoDdd().$objInscrito->getFoneContato())
                                . "&text=Olá ".$objInscrito->getPreNome()
                                . ". Segue seus dados para acesso: "
                                . " https://".$_SERVER["SERVER_NAME"]."/".$objInscrito->getUrlPrefixo()."/inscricoes"
                                . " \rlogin: ". $objInscrito->getEMail()
                                . " \rsenha: ". $objInscrito->getCodInscrito()
                                . " ";
                                ?>
                               '>Enviar login via whats</a>
<br>
<?php
try {
    $objInscrito->carregarListaPerfil();
    if ($objInscrito->getListaPerfil()) {
        echo "<hr>Lista de Perfis associados ao Inscrito:<br>";
        foreach ($objInscrito->getListaPerfil() as $objLP) {
            echo $objLP->getCodPerfil() . " - " . $objLP->getTitulo() . "<br>";
        }
        echo "<hr>";
    }
} catch (Exception $exc) {
//    echo $exc->getMessage();
}
                
If($MSG){
   echo FWA_Mostrar_Mensagem($MSG);
}
#--------------------------------------------------------
#echo  "<br><br>";
?>
    <!-- Abas de navegação -->
    <div id="tabs">
        <ul>
            <li class="txtAba"><a href="#dadosPessoais">Dados Pessoais</a></li>
            <?php if($objEvConf->getAtividade()==1){ ?>
            <li class="txtAba"><a href="#atividades">Atividades</a></li>
            <?php } ?>
            <li class="txtAba"><a href="#pedidos">Pedidos</a></li>
            <li class="txtAba"><a href="#pagamentos">Pagamentos</a></li>
            <li class="txtAba"><a href="#documentos">Documentos</a></li>
            <li class="txtAba"><a href="#logAcessos">Log Acessos</a></li>
            <li class="txtAba"><a href="#mensagens">Comunicação via WhatsApp</a></li>
        </ul>

        <!-- Conteúdo das abas -->
        <div id="dadosPessoais" class="tab-content">
            <?php require_once('inscritos-cadastro-ok-dados-pessoais.php') ?>

</div>
<div id="pedidos" class="tab-content">
<?php
#--------------------------------------------------------
#echo  "<br><br>";
#---- Busca Pedidos ------------------------------------      
      $debug = false;
      ?>
        <form name="abrirReciboPedido" id="abrirReciboPedido" method="post" action="<?php echo WB_URL_ACTION?>?content=view/recibos/recibo.php" target="_blank">
            <input type="hidden" value="" name="cod_pedido" id="cod_pedido">
        </form>
        <script language="JavaScript" type="text/javascript">
//            function gerarReciboPedido(codPedido){
//                $('#cod_pedido').val(codPedido);
//                $('#abrirReciboPedido').submit();
//            }
        </script> 
        <?php
         
        $objPedido = new Pedido();
        $objPedido->setInscritoId($cod_inscrito);
        $objPedido->setEventoId($cod_evento);

        // Helper: transforma a string da transacao em link para a consulta PagSeguro
        if (!function_exists('FWA_linkConsultaTransacao')) {
            function FWA_linkConsultaTransacao($transacao_id, $texto = null) {
                $tid = trim((string)$transacao_id);
                if ($tid === '') return '';
                $label = ($texto !== null) ? $texto : htmlspecialchars($tid);
                $url = 'index.php?content=view/admin/GerenciarConsultaPagSeguro.php&transacao_auto=' . urlencode($tid);
                return "<a href='{$url}' target='_blank' title='Consultar esta transação no PagSeguro'>{$label}</a>";
            }
        }

        // A lista e carregada ANTES de desenhar o fieldset: o contador da
        // legenda e a barra de acoes precisam saber quantos pedidos existem.
        // O carregamento lanca excecao quando o inscrito nao tem pedido — isso
        // e ausencia de dado, nao erro, e vira lista vazia.
        $listaPedidos = array();
        $erroPedidos  = '';
        try {
            $objPedido->carregarListaPedidosByInscritoId();
            $listaPedidos = $objPedido->getListaPedidos();
        } catch (Exception $exc) {
            $erroPedidos = $exc->getMessage();
        }
        $qtdPedidos = count($listaPedidos);

        // A NUMERACAO e cronologica e fica presa ao pedido: 1 e sempre o mais
        // antigo do inscrito, independente da ordem em que a lista aparece.
        // Se ela fosse "posicao na tela", inverter a ordem renomearia todos os
        // pedidos e "o pedido 3" deixaria de querer dizer a mesma coisa.
        $ordemCronologica = array();
        $idsOrdenados = array();
        foreach ($listaPedidos as $objP) {
            $idsOrdenados[] = (int) $objP->getId();
        }
        sort($idsOrdenados);
        foreach ($idsOrdenados as $pos => $idPedido) {
            $ordemCronologica[$idPedido] = $pos + 1;
        }

        // EXIBICAO do mais novo para o mais velho. O id do pedido e a ordem de
        // criacao, entao serve de criterio sem depender de data preenchida.
        if (is_array($listaPedidos)) {
            usort($listaPedidos, function ($a, $b) {
                return (int) $b->getId() - (int) $a->getId();
            });
        }

        echo "<fieldset><legend> Pedidos (" . $qtdPedidos . ")</legend>";
        ?>
        <style>
            /* Cartao do pedido: a informacao que decide (numero, valor,
               situacao) vem primeiro; a descricao, que costuma ter varias
               linhas, fica truncada. */
            .ped-card { border:1px solid #e3e3e3; border-left:3px solid #bdbdbd; border-radius:6px;
                        padding:10px 14px; margin-bottom:10px; background:#fff; }
            .ped-card.ped-pago    { border-left-color:#43a047; }
            .ped-card.ped-vencido { border-left-color:#e53935; }
            /* Uma linha so por pedido: identificacao, valor, metadados e, no
               fim dela, a situacao. A situacao vai encostada na direita porque
               e a conclusao da linha — no meio, virava só mais um detalhe. */
            .ped-cab { display:flex; align-items:baseline; gap:10px; flex-wrap:wrap; }
            .ped-cab .ped-badge { margin-left:auto; }
            /* Ordem na lista: discreta de proposito — orienta a leitura sem
               competir com o numero do pedido, que e o dado de verdade. */
            .ped-ord { font-size:12px; font-weight:600; color:#9e9e9e; min-width:16px; }
            .ped-cab .ped-ord + .ped-num { margin-left:-6px; }
            .ped-num { font-weight:700; font-size:14px; color:#333; }
            .ped-valor { font-size:16px; font-weight:700; color:#1b5e20; }
            .ped-badge { font-size:11px; font-weight:600; padding:2px 8px; border-radius:10px; }
            .ped-badge-ok     { background:#e8f5e9; color:#1b5e20; }
            /* Aguardando dentro do prazo: cinza neutro. So o pago (verde) e o
               vencido (vermelho) usam cor — sao os dois estados que pedem
               leitura imediata. */
            .ped-badge-aberto { background:#f0f1f3; color:#555; }
            .ped-badge-venc   { background:#fdecea; color:#b71c1c; }
            /* rotulo em cinza, valor em preto; so o vencimento estourado em
               vermelho — se todo valor fosse vermelho, nenhum se destacaria */
            .ped-meta { font-size:12px; color:#888; }
            .ped-meta b { color:#333; font-weight:600; }
            .ped-meta b.venc { color:#b71c1c; }

            /* Descricao escondida por padrao; o botao "Detalhes" a revela
               abaixo da barra de acoes, dentro do proprio cartao. */
            .ped-desc { display:none; font-size:12px; color:#555; margin-top:10px;
                        padding:8px 10px; background:#fafafa; border:1px solid #eee;
                        border-radius:4px; }
            .ped-card.ped-detalhado .ped-desc { display:block; }

            .ped-acoes { display:flex; gap:6px; flex-wrap:wrap; align-items:center; margin-top:10px; }
            /* Mesma altura do "Enviar credencial" do topo da tela (btn-xs).
               A regra vale para .btn e nao para btn-sm/btn-xs especificos
               porque parte destes botoes (os da NFS-e) e injetada por JS —
               fixar a classe la e aqui daria duas fontes para a mesma medida. */
            .ped-acoes .btn { font-size:12px; padding:1px 6px; line-height:1.5; border-radius:3px; }
            .ped-acoes .ped-excluir { color:#b71c1c; margin-left:auto; }

            /* Barra do topo: incluir pedido nao pertence a nenhum pedido da
               lista, entao fica UMA vez aqui em cima e nao repetido em cada
               cartao. O que e destrutivo/restrito vai para a direita. */
            .ped-barra { display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-bottom:12px; }
            .ped-barra .ped-direita { margin-left:auto; display:flex; gap:6px; align-items:center; }

            /* Botao colorido e <a>, e o CSS global do admin pinta link em todos
               os estados — no hover/visited a letra escurecia e sumia no fundo.
               Aqui ela e branca sempre. O "Excluir" fica de fora: e btn-link
               vermelho de propósito. */
            .ped-barra .btn-primary, .ped-barra .btn-primary:link,
            .ped-barra .btn-primary:visited, .ped-barra .btn-primary:hover,
            .ped-barra .btn-primary:focus, .ped-barra .btn-primary:active,
            .ped-acoes .btn-primary, .ped-acoes .btn-primary:link,
            .ped-acoes .btn-primary:visited, .ped-acoes .btn-primary:hover,
            .ped-acoes .btn-primary:focus, .ped-acoes .btn-primary:active,
            .ped-acoes .btn-warning, .ped-acoes .btn-warning:link,
            .ped-acoes .btn-warning:visited, .ped-acoes .btn-warning:hover,
            .ped-acoes .btn-warning:focus, .ped-acoes .btn-warning:active,
            .ped-acoes .btn-success, .ped-acoes .btn-success:link,
            .ped-acoes .btn-success:visited, .ped-acoes .btn-success:hover,
            .ped-acoes .btn-success:focus, .ped-acoes .btn-success:active,
            .ped-acoes .btn-danger, .ped-acoes .btn-danger:link,
            .ped-acoes .btn-danger:visited, .ped-acoes .btn-danger:hover,
            .ped-acoes .btn-danger:focus, .ped-acoes .btn-danger:active {
                color:#fff;
            }

            /* Botao de inverter: acao de visualizacao, nao de dado — fica
               neutro para nao disputar com o "Incluir pedido". */
            .ped-inverter { font-size:12px; }
        </style>
        <script type="text/javascript">
            $(document).ready(function() {
                // "Detalhes" abre a descricao do proprio cartao.
                $(document).on('click', '.ped-detalhes', function(e) {
                    e.preventDefault();
                    var $card = $(this).closest('.ped-card');
                    var aberto = $card.hasClass('ped-detalhado');
                    $card.toggleClass('ped-detalhado', !aberto);
                    $(this).text(aberto ? 'Detalhes' : 'Ocultar detalhes');
                });
                // Inverter a ordem dos cartoes. A numeracao NAO e recalculada:
                // ela e cronologica e pertence ao pedido, entao o mesmo pedido
                // continua sendo "o 3" nas duas ordens.
                $(document).on('click', '.ped-inverter', function(e) {
                    e.preventDefault();
                    var $botao = $(this);
                    var $lista = $('.ped-lista');
                    $lista.append($lista.children('.ped-card').get().reverse());

                    var eraDesc = $botao.attr('data-ordem') === 'desc';
                    $botao.attr('data-ordem', eraDesc ? 'asc' : 'desc');
                    $botao.html(eraDesc
                        ? '<i class="glyphicon glyphicon-arrow-up"></i> Mais antigos primeiro'
                        : '<i class="glyphicon glyphicon-arrow-down"></i> Mais novos primeiro');
                });

            });
        </script>
        <?php

        // --- Barra do topo: contador, incluir e (restrito) excluidos ---
        echo "<div class='ped-barra'>";

        echo   "<a class='btn btn-sm btn-primary' href='index.php?content=view/admin/inscritos-pedido-cadastro-incluir.php"
             . "&cod_evento=" . $cod_evento . "&cod_inscrito=" . $cod_inscrito . "'"
             . " title='Incluir um novo pedido para este inscrito'>"
             . "<i class='glyphicon glyphicon-plus'></i> Incluir pedido</a>";

        // O contador fica so na legenda do fieldset ("Pedidos (3)") — repetir
        // aqui do lado do botao seria a mesma informacao duas vezes na mesma
        // altura da tela.

        // Inverter e so ordem de EXIBICAO: resolve no cliente, sem recarregar a
        // pagina e sem perder a aba aberta.
        if ($qtdPedidos > 1) {
            echo "<a href='#' class='btn btn-default btn-sm ped-inverter' data-ordem='desc'"
               . " title='Inverter a ordem da lista'>"
               . "<i class='glyphicon glyphicon-arrow-down'></i> Mais novos primeiro</a>";
        }

        echo   "<span class='ped-direita'>";
        // Link p/ listar pedidos excluidos (super admin 668; so aparece se houver o que listar)
        if (isset($_SESSION['_cod_usuario']) && (int)$_SESSION['_cod_usuario'] === 668) {
            $qtdPedExcluido = (new PedidoDAO())->contarExcluidos($cod_evento, $cod_inscrito);
            if ($qtdPedExcluido > 0) {
                $urlRecuperar = 'index.php?content=view/admin/GerenciarPedidoExcluido.php&cod_inscrito='
                              . (int)$cod_inscrito . '&cod_evento=' . (int)$cod_evento;
                echo "<a href='".$urlRecuperar."' class='btn btn-default btn-xs' title='Listar pedidos excluidos deste inscrito (permite restaurar; acesso restrito)'>"
                   . "<i class='glyphicon glyphicon-trash'></i> Listar excluídos (".(int)$qtdPedExcluido.")"
                   . "</a>";
            }
        }
        echo   "</span>";
        echo "</div>";

        if ($erroPedidos != '' || $qtdPedidos == 0) {
            echo "<div class='ped-meta' style='margin-bottom:10px;'>"
               . "Este inscrito ainda não tem pedidos."
               . "</div>";
        }

        // Container proprio: inverter a ordem mexe nos filhos DELE, e nao em
        // tudo que estiver solto dentro do fieldset.
        echo "<div class='ped-lista'>";

        foreach ($listaPedidos as $objP) {
                $ordemPedido = isset($ordemCronologica[(int) $objP->getId()])
                             ? $ordemCronologica[(int) $objP->getId()]
                             : 0;

                // Um CARTAO por pedido. Antes era um paredao de texto onde a
                // descricao (longa, em negrito) engolia o que importa —
                // valor, vencimento e situacao. Aqui a identificacao do pedido
                // vem primeiro, a descricao fica truncada e todas as acoes se
                // concentram numa barra unica no rodape do cartao.
                $pagoOuAcerto = ($objP->getCodPagamento() != '' || $objP->getStatus() == 'Pago por acerto');
                $vencido      = (!$pagoOuAcerto && testeDataVencimento($objP->getDatVencimento()));

                $descricao = $objP->getDescricaoCompleta() != ''
                           ? $objP->getDescricaoCompleta()
                           : $objP->getDescricao();

                // O valor ja costuma vir formatado (com virgula) do banco;
                // so formata quando vier numerico puro, para nao dobrar pontos.
                $valorTxt = (string) $objP->getValorTotal();
                if ($valorTxt !== '' && strpos($valorTxt, ',') === false && is_numeric($valorTxt)) {
                    $valorTxt = number_format((float) $valorTxt, 2, ',', '.');
                }

                // A borda esquerda acompanha a situacao: verde pago, vermelho
                // vencido, neutro enquanto esta no prazo.
                echo "<div class='ped-card" . ($pagoOuAcerto ? " ped-pago" : ($vencido ? " ped-vencido" : "")) . "'>";

                // --- cabecalho: identificacao, valor e situacao ---
                echo "<div class='ped-cab'>";
                // Ordem na lista (1, 2, 3...). E so posicao na tela — serve
                // para conversar ("o terceiro pedido") sem decorar o numero
                // interno, que nao e sequencial nem começa em 1.
                echo   "<span class='ped-ord'>" . $ordemPedido . " -</span>";
                echo   "<span class='ped-num'>Pedido " . (int) $objP->getId() . "</span>";
                echo   "<span class='ped-valor'>R$ " . htmlspecialchars($valorTxt, ENT_QUOTES, 'UTF-8') . "</span>";

                // Metadados na MESMA linha, na ordem em que a pergunta aparece:
                // o que e (tipo/referencia) -> quando vence -> em quantas vezes
                // -> dados do pagamento. A situacao vai por ultimo, encostada na
                // direita, porque e a conclusao da linha e nao mais um detalhe.
                $meta = array();
                if ($objP->getMaxQtdParcela() != '') {
                    $meta[] = "Parcelas <b>" . (int) $objP->getMaxQtdParcela() . "</b>";
                }
                if ($objP->getReferencia() != '') {
                    $meta[] = "Ref. <b>" . htmlspecialchars((string) $objP->getReferencia(), ENT_QUOTES, 'UTF-8') . "</b>";
                }
                $rotuloVenc = ($vencido && !$pagoOuAcerto) ? "Venceu" : "Venc.";
                $meta[] = $rotuloVenc . " " . ($vencido && !$pagoOuAcerto ? "<b class='venc'>" : "<b>")
                        . formatDateView($objP->getDatVencimento()) . "</b>";
                if ($objP->getTipo() != '') {
                    $meta[] = "Tipo <b>" . htmlspecialchars((string) $objP->getTipo(), ENT_QUOTES, 'UTF-8') . "</b>";
                }
                // O status do REGISTRO (ATIVADA/DESATIVADA) saiu daqui: a linha
                // responde sobre o pagamento, e "Situação ATIVADA" ao lado de
                // "PAGO em ..." era duas situacoes diferentes lado a lado.
                // Pedido fora do normal continua avisando — no proprio selo.
                if ($objP->getTransacaoId() != '') {
                    $meta[] = "T-id " . FWA_linkConsultaTransacao($objP->getTransacaoId());
                }
                echo   "<span class='ped-meta'>" . implode(" &middot; ", $meta) . "</span>";

                // O selo responde UMA pergunta: como esta o pagamento. O status
                // do registro (ATIVADA) nao entra — e o normal de todo pedido e
                // so faria ruido ao lado de "PAGO". A excecao e o pedido fora do
                // normal (cancelado/desativado): ai o status E a informacao, e
                // aparece no lugar do resto.
                $statusPedido = trim((string) $objP->getStatus());
                $foraDoNormal = ($statusPedido != '' && strtoupper($statusPedido) != 'ATIVADA'
                                 && !$pagoOuAcerto);

                if ($foraDoNormal) {
                    echo "<span class='ped-badge ped-badge-venc'>"
                       . htmlspecialchars(strtoupper($statusPedido), ENT_QUOTES, 'UTF-8') . "</span>";
                } elseif ($pagoOuAcerto) {
                    // Mantem a hora do pagamento (existia na lista antiga), mas
                    // omite "00:00", que so aparece quando a hora nao foi
                    // registrada e nao informa nada.
                    $dtPago = trim((string) formatDateView($objP->getDataPagamento()));
                    $dtPago = str_replace(' 00:00', '', $dtPago);
                    echo "<span class='ped-badge ped-badge-ok'>PAGO"
                       . ($dtPago != '' ? " em " . $dtPago : "") . "</span>";
                } elseif ($vencido) {
                    // Vermelho e reservado ao que ja venceu — o que exige acao.
                    echo "<span class='ped-badge ped-badge-venc'>VENCIDO</span>";
                } else {
                    echo "<span class='ped-badge ped-badge-aberto'>AGUARDANDO</span>";
                }
                echo "</div>";

                // Inconsistencia entre o que o pedido diz e o que o PagSeguro diz
                // da mesma transacao — o caso tipico e o pedido marcado como PAGO
                // com a transacao ainda "aguardando pagamento" la. Sem isso, so
                // olhando a tela de consulta de transacoes da para perceber.
                if ($objP->getTransacaoId() != '') {
                    echo PagamentoDivergencia::alertaHtml(
                        PagamentoDivergencia::doPedido(
                            $objP->getTransacaoId(),
                            $pagoOuAcerto,
                            $objP->getValorTotal()
                        ),
                        true
                    );
                }

                // --- acoes: todas juntas, mesmo tamanho, na mesma linha ---
                echo "<div class='ped-acoes'>";
                // "Detalhes" primeiro: e o que abre a descricao, que agora nasce
                // escondida. So aparece quando ha descricao para mostrar.
                if ($descricao != '') {
                    echo "<a href='#' class='btn btn-sm btn-default ped-detalhes'"
                       . " title='Mostrar a descricao completa do pedido'>Detalhes</a>";
                }
                echo   "<a class='btn btn-sm btn-primary' href='index.php?content=view/admin/inscritos-pedido-cadastro-alterar.php"
                     . "&cod_evento=" . $cod_evento . "&cod_pedido=" . (int) $objP->getId()
                     . "&cod_inscrito=" . $cod_inscrito . "'>Alterar</a>";
                // "Incluir" saiu daqui para a barra do topo: a tela de inclusao
                // monta o pedido a partir do inscrito/perfil/taxa e IGNORA o
                // cod_pedido que este link mandava — era o mesmo botao repetido
                // em cada cartao, prometendo copiar um pedido que nunca copiou.
                if (!$pagoOuAcerto) {
                    echo "<a class='btn btn-sm btn-warning' href='index.php?content=view/admin/inscritos-pagamento-cadastro-incluir.php"
                       . "&cod_evento=" . $cod_evento . "&cod_pedido=" . (int) $objP->getId()
                       . "&cod_inscrito=" . $cod_inscrito . "'>Cadastrar Pagamento</a>";
                }

                // Emissao de NFS-e do pedido (Focus NFe). A nota e por pedido e
                // pelo valor do pedido — emitir antes de receber e caso normal.
                $__codPedidoNfse = $objP->getId();
                $__nfseParte = 'barra';
                require('inscritos-cadastro-ok-nfse-item.php');

                echo   "<a class='btn btn-sm btn-primary' target='_blank' href='https://api.whatsapp.com/send?phone="
                     . limpaFoneWhats($objInscrito->getFoneContatoDdd() . $objInscrito->getFoneContato())
                     . "&text=Ola " . $objInscrito->getPreNome()
                     . ", você se inscreveu para o " . $objEv->getTitulo() . "."
                     . " Caso esteja com dificuldades para efetuar o pagamento de sua inscrição. "
                     . " Tente utilizar o link a seguir, qualquer dúvida entre em contato'>Whats</a>";

                // Excluir por ultimo e separado: acao destrutiva nao fica
                // encostada nas acoes do dia a dia.
                echo   "<a href='#' class='btn btn-sm btn-link ped-excluir btn-excluir-pedido'"
                     . " data-cod-pedido='" . (int) $objP->getId() . "'"
                     . " data-cod-inscrito='" . (int) $cod_inscrito . "'"
                     . " data-descricao='" . htmlspecialchars($objP->getDescricao(), ENT_QUOTES, 'UTF-8') . "'"
                     . " data-valor='" . htmlspecialchars($objP->getValorTotal(), ENT_QUOTES, 'UTF-8') . "'"
                     . " title='Excluir pedido'>Excluir</a>";
                echo "</div>";

                // --- descricao: nasce escondida, abre EMBAIXO da linha ---
                // Ela e o texto mais longo do cartao e quase nunca e o que se
                // procura; ficava truncada ocupando espaco em todos os pedidos.
                if ($descricao != '') {
                    echo "<div class='ped-desc'>" . $descricao . "</div>";
                }

                // Notas emitidas deste pedido (preenchido por JS)
                $__nfseParte = 'lista';
                require('inscritos-cadastro-ok-nfse-item.php');

                echo "</div>";
             #Movendo para o proximo registro
        }

        echo "</div>"; // .ped-lista

echo "</fieldset>";

// JS e modal da NFS-e: uma vez por pagina, depois do laco dos pedidos.
require('inscritos-cadastro-ok-nfse-scripts.php');
#--------------------------------------------------------
?>

<!-- Modal de confirmacao de exclusao de pedido -->
<div class="modal fade" id="modalExcluirPedido" tabindex="-1" role="dialog" aria-labelledby="modalExcluirPedidoLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formExcluirPedido" method="post" action="<?php echo WB_URL_COMMAND; ?>&c=GerenciarPedido&m=excluir">
                <div class="modal-header" style="background-color:#d9534f;color:#fff;">
                    <h4 class="modal-title" id="modalExcluirPedidoLabel">
                        <i class="glyphicon glyphicon-warning-sign"></i> Confirmar exclusão de pedido
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar" style="color:#fff;opacity:.9;"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Atenção:</strong> esta ação remove o pedido da listagem do inscrito.
                        O registro fica preservado no log de auditoria (<code>pedidos_atualizados</code>) e pode ser recuperado posteriormente.
                    </div>
                    <p>
                        <strong>Pedido:</strong> <span id="modalExcluirPedidoDescricao"></span><br>
                        <strong>Valor:</strong> R$ <span id="modalExcluirPedidoValor"></span><br>
                        <strong>Cód. pedido:</strong> <span id="modalExcluirPedidoId"></span>
                    </p>
                    <div class="form-group">
                        <label for="modalExcluirPedidoMotivo"><strong>Motivo da exclusão</strong> <span style="color:#d9534f;">*</span></label>
                        <textarea id="modalExcluirPedidoMotivo" name="motivo_exclusao" class="form-control" rows="3" maxlength="500" placeholder="Descreva o motivo (mín. 5 caracteres)" required></textarea>
                        <small class="text-muted">Este motivo fica registrado no log de auditoria.</small>
                    </div>
                    <div id="modalExcluirPedidoErro" class="alert alert-danger" style="display:none;"></div>
                    <input type="hidden" id="modalExcluirPedidoCodPedido"   name="cod_pedido"   value="">
                    <input type="hidden" id="modalExcluirPedidoCodInscrito" name="cod_inscrito" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="btnConfirmarExcluirPedido">
                        <i class="glyphicon glyphicon-trash"></i> Confirmar exclusão
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
function abrirModalExcluirPedido(codPedido, codInscrito, descricao, valor) {
    $('#modalExcluirPedidoCodPedido').val(codPedido);
    $('#modalExcluirPedidoCodInscrito').val(codInscrito);
    $('#modalExcluirPedidoId').text(codPedido);
    $('#modalExcluirPedidoDescricao').text(descricao || '(sem descrição)');
    $('#modalExcluirPedidoValor').text(valor || '0,00');
    $('#modalExcluirPedidoMotivo').val('');
    $('#modalExcluirPedidoErro').hide().text('');
    $('#modalExcluirPedido').modal('show');
}

$(document).ready(function() {
    $(document).on('click', '.btn-excluir-pedido', function(e) {
        e.preventDefault();
        abrirModalExcluirPedido(
            $(this).data('cod-pedido'),
            $(this).data('cod-inscrito'),
            $(this).data('descricao'),
            $(this).data('valor')
        );
    });

    $('#formExcluirPedido').on('submit', function(e) {
        var motivo = $.trim($('#modalExcluirPedidoMotivo').val());
        if (motivo.length < 5) {
            e.preventDefault();
            $('#modalExcluirPedidoErro').text('Informe um motivo com pelo menos 5 caracteres.').show();
            $('#modalExcluirPedidoMotivo').focus();
            return false;
        }
        $('#btnConfirmarExcluirPedido').prop('disabled', true).html('Excluindo...');
        return true;
    });
});
</script>

</div>
<div id="pagamentos" class="tab-content">
<?php
#echo  "<br><br>";
#---- Busca Pagamentos ------------------------------------      
      $debug = false;
      ?>
        <form name="abrirReciboPagamento" id="abrirReciboPagamento" method="post" action="<?php echo WB_URL_ACTION?>?content=view/recibos/recibo.php" target="_blank">
            <input type="hidden" value="" name="cod_pagamento" id="cod_pagamento">
        </form>
        <script language="JavaScript" type="text/javascript">
            function gerarReciboPagamento(codPagamento){
                $('#cod_pagamento').val(codPagamento);
                $('#abrirReciboPagamento').submit();
            }
        </script> 
         <?php
      $var_sql = "SELECT tp.cod_evento, tp.cod_pagamento, tp.cod_inscrito, tp.valor, tp.forma_pagamento"
              . ", tp.campo_7, tp.data_inclusao, tp.data_alteracao, tp.observacao, tp.data_pagamento, tp.numero_empenho, tp.data_emissao_recibo, tp.status, tp.pedido_id, tp.transacao_id, ped.descricao as pedido_titulo "
              . "FROM tab_pagamentos tp "
              . " LEFT JOIN pedidos ped ON tp.pedido_id=ped.id "
              . "WHERE (tp.cod_inscrito=".$cod_inscrito.") and (tp.cod_evento=".$cod_evento.")";

      echo "<fieldset><legend> Pagamentos Efetuados</legend>";

      // Link p/ listar pagamentos excluidos (financeiro; so aparece se houver o que listar)
      @$cod_msg_fin = false;
      $podeFinanceiro = FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, $cod_msg_fin, false);
      if ($podeFinanceiro) {
          $qtdPagExcluido = (new PagamentoDAO())->contarExcluidos($cod_evento, $cod_inscrito);
          if ($qtdPagExcluido > 0) {
              $urlPagExcluido = 'index.php?content=view/admin/GerenciarPagamentoExcluido.php&cod_inscrito='
                              . (int)$cod_inscrito . '&cod_evento=' . (int)$cod_evento;
              echo "<div style='text-align:right;margin-bottom:8px;'>"
                 . "<a href='".$urlPagExcluido."' class='btn btn-default btn-xs' title='Listar pagamentos excluidos deste inscrito (acesso restrito ao financeiro)'>"
                 . "<i class='glyphicon glyphicon-trash'></i> Listar excluídos (".(int)$qtdPagExcluido.")"
                 . "</a></div>";
          }
      }

      // Acao do bloco: incluir sai do laco e aparece uma vez, nao por item.
      echo "<div class='card-barra-topo'>";
      echo   "<a class='btn btn-sm btn-primary' href='index.php?content=view/admin/inscritos-pagamento-cadastro-incluir.php"
           . "&cod_evento=".$cod_evento."&cod_inscrito=".$cod_inscrito."'>"
           . "<i class='glyphicon glyphicon-plus'></i> Incluir pagamento</a>";
      echo "</div>";

      If ($RS_P=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)) {
        If (!$RS_P->EOF) {
          $i = 0;
          // String com decimais: formatMoneyView(0) devolve vazio (0 e falsy),
          // e o total agora pode chegar a zero quando tudo esta estornado.
          $valorTotal = '0.00';
          bcscale(2);
          While (!$RS_P->EOF) {
              $i = $i +1;
             #Adicionando nas variaveis, os valores dos campos retirados dos registros
             $cod_evento    = $RS_P->fields[0];
             $cod_pagamento = $RS_P->fields[1];
//             $cod_inscrito  = $RS_P->fields[2];
             $valor         = $RS_P->fields[3];
             $forma_pagamento = $RS_P->fields[4];
             $numero_boleto   = $RS_P->fields[5];
             $data_inclusao_pagamento  = $RS_P->fields[6];
             $data_alteracao_pagamento = $RS_P->fields[7];
             $observacao = $RS_P->fields[8];
             $data_pagamento = $RS_P->fields[9];
             $numero_empenho = $RS_P->fields[10];
             $data_emissao_recibo = $RS_P->fields[11];
             $status = $RS_P->fields[12];
             $pedido_id = $RS_P->fields[13];
             $transacao_id = $RS_P->fields[14];
             $pedido_titulo = $RS_P->fields[15];
             
             // Pagamento estornado/cancelado/devolvido: fica visivel (vermelho),
             // mas fora do total e sem Recibo/Whats — financeiramente nao e receita.
             $pagEstornado = in_array(trim((string)$status), array('ESTORNADO','CANCELADO','DEVOLVIDO'));

             If($valor==""){
               $valor = "O Pagamento ainda não consta no sistema.";
             }else if(!$pagEstornado){
                $valorTotal = bcadd($valorTotal,$valor);
             }
             
//             $data_inclusao_pagamento = substr($data_inclusao_pagamento,6,2)."/".substr($data_inclusao_pagamento,4,2)."/".substr($data_inclusao_pagamento,0,4)." - ".substr($data_inclusao_pagamento,8,2)."h".substr($data_inclusao_pagamento,10,2);
//             If($data_alteracao_pagamento!=""){
//               $data_alteracao_pagamento = substr($data_alteracao_pagamento,6,2)."/".substr($data_alteracao_pagamento,4,2)."/".substr($data_alteracao_pagamento,0,4)." - ".substr($data_alteracao_pagamento,8,2)."h".substr($data_alteracao_pagamento,10,2);
//             }
             
                // Um CARTAO por pagamento — mesmo padrao da aba Pedidos.
                // Antes: blockquote de 13 linhas, com os tres .gif repetidos no
                // topo de cada item. Nenhum campo saiu daqui; o que mudou e a
                // hierarquia — valor e situacao primeiro, resto com rotulo.
                $valorNumerico = is_numeric($valor);
                $pagoEfetivado = (formatData($data_pagamento) != '');

                // Mesma lista de formas que sempre gerou recibo.
                // Pagamento estornado nao oferece Recibo/Whats.
                $temRecibo = !$pagEstornado && in_array($forma_pagamento, array(
                    'BOLETO BANCÁRIO', 'INEXIGIBILIDADE', 'EMPENHO', 'TESOURARIA', 'CARTAO',
                    'DOACAO', 'APOIO', 'BOLETO A COMPENSAR', 'PIX', 'TRANSFERENCIA'));

                echo "<div class='card-item" . ($pagEstornado ? " card-alerta" : ($pagoEfetivado ? " card-ok" : (!$valorNumerico ? " card-alerta" : ""))) . "'>";

                // --- cabecalho: identificacao, valor e situacao ---
                echo "<div class='card-cab'>";
                echo   "<span class='card-titulo'>Pagamento " . $cod_pagamento . "</span>";
                if ($valorNumerico) {
                    echo "<span class='card-valor'>R$ " . formatMoneyView($valor) . "</span>";
                } else {
                    // "O Pagamento ainda nao consta no sistema." — mensagem que
                    // o codigo antigo punha no lugar do valor.
                    echo "<span class='card-badge card-badge-alerta'>"
                       . htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8') . "</span>";
                }
                if ($forma_pagamento != '') {
                    echo "<span class='card-badge card-badge-neutro'>"
                       . htmlspecialchars(textoMaiusculoMinusculo($forma_pagamento), ENT_QUOTES, 'UTF-8') . "</span>";
                }
                if ($pagoEfetivado) {
                    echo "<span class='card-badge card-badge-ok'>PAGO em " . formatData($data_pagamento) . "</span>";
                } else {
                    echo "<span class='card-badge card-badge-espera'>SEM DATA DE PAGAMENTO</span>";
                }
                // Situacao entra sempre: a badge acima fala da data, nao do status.
                // Estornado/cancelado/devolvido em vermelho.
                if ($status != '') {
                    echo "<span class='card-badge " . ($pagEstornado ? "card-badge-alerta" : "card-badge-info") . "'>"
                       . htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') . "</span>";
                }
                echo "</div>";

                // Inconsistencia entre o pagamento registrado aqui e a transacao
                // no PagSeguro (status, valor ou data). Mesma regra da consulta de
                // transacoes e da aba Pedidos — ver PagamentoDivergencia.
                if (trim((string) $transacao_id) != '') {
                    echo PagamentoDivergencia::alertaHtml(
                        PagamentoDivergencia::doPagamento(
                            $transacao_id,
                            $status,
                            $valor,
                            $data_pagamento
                        ),
                        true
                    );
                }

                // --- metadados: os mesmos campos de antes, agora com rotulo ---
                echo montarMeta(array(
                    'Forma'          => $forma_pagamento,
                    'Cód. pagamento' => $cod_pagamento,
                    'Pedido'         => $pedido_id,
                    'Nº boleto'      => $numero_boleto,
                    'Nº empenho'     => $numero_empenho,
                ));
                echo montarMeta(array(
                    'Pago em'        => formatData($data_pagamento),
                    'Incluído em'    => formatData($data_inclusao_pagamento),
                    'Alterado em'    => formatData($data_alteracao_pagamento),
                    'Emissão recibo' => formatData($data_emissao_recibo),
                    'T-id'           => (trim($transacao_id) != '' ? FWA_linkConsultaTransacao($transacao_id) : ''),
                ), array('T-id'));

                // --- pedido de origem (o numero ja esta na meta acima) ---
                echo "<div class='card-grid'>";
                if ($pedido_titulo != '') {
                    echo montarCampo('Descrição do pedido', $pedido_titulo, false, true);
                } else {
                    echo "<div class='card-campo card-campo-full'>"
                       . "<span class='card-rot'>Descrição do pedido</span>"
                       . "<span class='card-val alerta'>Pedido não encontrado</span></div>";
                }
                echo "</div>";

                // --- observacao: aparece INTEIRA, sem "ver mais" ---
                echo montarTexto('Observação', $observacao);

                // --- acoes: todas juntas, mesmo tamanho, na mesma linha ---
                echo "<div class='card-acoes'>";
                echo   "<a class='btn btn-sm btn-primary' href='index.php?content=view/admin/inscritos-pagamento-cadastro-alterar.php"
                     . "&cod_evento=".$cod_evento."&cod_pagamento={$cod_pagamento}&cod_inscrito={$cod_inscrito}'>Alterar</a>";
                echo   "<a class='btn btn-sm btn-primary' href='index.php?content=view/admin/inscritos-pagamento-cadastro-incluir.php"
                     . "&cod_evento=".$cod_evento."&cod_pagamento={$cod_pagamento}&cod_inscrito={$cod_inscrito}'"
                     . " title='Incluir novo pagamento a partir deste'>Incluir</a>";
                if ($temRecibo) {
                    echo "<a class='btn btn-sm btn-primary' href='javascript:void(0);' onclick=\"gerarReciboPagamento('".$cod_pagamento."');\""
                       . " title='Recibo cód. ".$cod_pagamento." - ".textoMaiusculoMinusculo($forma_pagamento)." - R$ ".formatMoneyView($valor)."'>"
                       . "Recibo</a>";
                    echo "<a class='btn btn-sm btn-primary' target='_blank' href='https://api.whatsapp.com/send?phone="
                       . limpaFoneWhats($objInscrito->getFoneContatoDdd().$objInscrito->getFoneContato())
                       . "&text=Ola ".$objInscrito->getPreNome().", o status de seu pagamento já foi atualizado'>Whats</a>";
                }
                // Estornar: so aparece quando a API do PagBank esta configurada
                // no env.php (WB_PAGSEGURO_TOKEN). Sem credencial nao ha como
                // devolver o dinheiro, e um botao que so muda status confunde
                // quem precisa do estorno de verdade.
                if ($podeFinanceiro && !$pagEstornado && $valorNumerico
                        && PagSeguroClient::estornoHabilitado()) {
                    $temTransacao = (trim((string)$transacao_id) != '');
                    $avisoDevolucao = $temTransacao
                        ? "A DEVOLUÇÃO DO DINHEIRO é solicitada ao PagBank agora."
                        : "Este pagamento NÃO tem transação vinculada: fica só o registro"
                          . " (devolução manual no gateway/banco).";
                    echo "<a class='btn btn-sm btn-warning' href='view/admin/inscritos-pagamento-cadastro-estornar-grava.php"
                       . "?cod_evento=".$cod_evento."&cod_pagamento={$cod_pagamento}&cod_inscrito={$cod_inscrito}'"
                       . " onclick=\"return confirm('ESTORNAR o pagamento ".$cod_pagamento." de R$ ".formatMoneyView($valor)."?\\n\\n"
                       . $avisoDevolucao . "\\n\\n"
                       . "O status vira ESTORNADO e o valor sai do total do inscrito.');\""
                       . " title='" . ($temTransacao
                            ? 'Estornar no PagBank e registrar como ESTORNADO'
                            : 'Registrar como ESTORNADO (sem transação vinculada)') . "'>Estornar</a>";
                }
                echo   "<a class='btn btn-sm btn-link card-excluir' href='view/admin/inscritos-pagamento-cadastro-excluir-grava.php"
                     . "?cod_evento=".$cod_evento."&cod_pagamento={$cod_pagamento}&cod_inscrito={$cod_inscrito}'"
                     . " title='Excluir pagamento'>Excluir</a>";
                echo "</div>";

                // Nota fiscal deste pagamento fica ABAIXO das acoes: e
                // informacao acessoria, nao acao do dia a dia.
                require('inscritos-cadastro-ok-nota-fiscal-item.php');

                echo "</div>";  // .card-item
             #Movendo para o proximo registro
             $RS_P->MoveNext();
          }
          echo "<div class='card-total'>";
          echo   "<span>Total de pagamentos <b>".$i."</b></span>";
          echo   "<span>Valor total <b>R$ ".formatMoneyView($valorTotal)."</b></span>";
          echo "</div>";
        }Else{
          echo montarVazio('Não há pagamentos registrados para este inscrito.');
        }
      }Else{
         echo "Problema na consulta Pagamentos!";
      }

echo "</fieldset>";
// Modais de Anexar/Substituir NF (1x por pagina)
require_once('inscritos-cadastro-ok-nota-fiscal-modais.php');

#--------------------------------------------------------
#echo  "<br><br>";
#---- Busca Boletos ------------------------------------    
if (in_array($objEvConf->getGatewayPagamento(), ['UNIPRIME', 'SISPRIME'])) {
    $debug = false;
    echo "<fieldset><legend> Boletos Gerados</legend>";
    try {
        $objBoleto = new Boleto();
        $objBoleto->setCodInscrito($cod_inscrito);
        if(!$objBoleto->carregarListaBoletosByCodInscrito()){
            throw new Exception("Nenhum boleto encontrado.");
        }
        foreach ($objBoleto->getListaBoletos() as $objL) {
            if ($objL->getStatus()=='DESATIVADA') {
            echo "<hr size='1' width='100%'>";
            if (FWA_Autenticacao($_SESSION['_cod_usuario'], 999, 5, false, false)) {
            echo " 
            &nbsp;&nbsp;&nbsp;&nbsp;
            <a href='index.php?content=view/admin/GerenciarBoletoAvulso.php&codInscrito={$cod_inscrito}'><img src='resources/image/txt-incluir.gif' border='0'></a>
    <!--        &nbsp;&nbsp;&nbsp;&nbsp;
                <a href='index.php?content=view/admin/inscritos-boleto-cadastro-incluir.php&cod_evento=".$cod_evento."&cod_boleto={$objL->getCodBoleto()}&cod_inscrito={$cod_inscrito}'><img src='resources/image/txt-incluir.gif' border='0'></a>
    -->                
            &nbsp;&nbsp;&nbsp;&nbsp;
            <a href='index.php?content=view/admin/inscritos-boleto-cadastro-alterar.php&cod_evento=".$cod_evento."&cod_boleto={$objL->getCodBoleto()}&cod_inscrito={$cod_inscrito}'><img src='resources/image/txt-alterar.gif' border='0'></a>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <a href='view/admin/inscritos-boleto-cadastro-excluir-grava.php?cod_evento=".$cod_evento."&cod_boleto={$objL->getCodBoleto()}&cod_inscrito={$cod_inscrito}'><img src='resources/image/txt-excluir.gif' border='0'></a>
            ";
            }
            echo " 
            <blockquote>
            Nosso número:   <b> {$objL->getNossoNumero()} </b>
            <br>Cód.boleto: <b> {$objL->getCodBoleto()}  </b>
            <br>Nº do Documentto:  <b> {$objL->getNumeroDocumento()}  </b>";

    //        echo "<br>Data Inclusão: <b> ".formatDateView($objL->getDatIncl())."</b>";
    //        echo "<br>Data Alteracao: <b> ".formatDateView($objL->getDatAlter())."</b>";

            echo "
            <br>Valor Documento: <b> ".$objL->getValorDocumento()."</b>
            <br>Data Vencimento: <b> ".formatDateView($objL->getDataVencimento())."</b>
            - Data Emissão: <b> ".formatDateView($objL->getDataEmissao())."</b>
            - Data Pagamento: <b> ".formatDateView($objL->getDataPagamento())."</b>
            <br>
            <br>Pagamento:<b> ".$objL->getDescFlgPagamento()."</b>
            <br>Remessa:  <b> ".$objL->getDescFlgRemessa()."</b>
            </blockquote>
            ";
            }
        }
    } catch (Exception $exc) {
        echo "
        &nbsp;&nbsp;&nbsp;&nbsp;".$exc->getMessage()."<br><br>
        <a href='index.php?content=view/admin/inscritos-boleto-cadastro-incluir.php&cod_evento=".$cod_evento."&cod_inscrito={$cod_inscrito}'><img src='resources/image/txt-incluir.gif' border='0'></a>
        &nbsp;&nbsp;&nbsp;&nbsp;<br>&nbsp;
        ";
    }

    echo "</fieldset>";
}
#--------------------------------------------------------
?>
</div>
<div id="atividades" class="tab-content">
<?php
#echo  "<br><br>";
require_once('inscritos-cadastro-ok-premiacao.php');
#--------------------------------------------------------
#echo  "<br><br>";
#---- Busca Oficinas ------------------------------------
require_once('inscritos-cadastro-ok-oficinas.php');
#--------------------------------------------------------
#echo  "<br><br>";
?>
</div>
<div id="documentos" class="tab-content">
    <?php
    if ($objEvConf->getCodEvento()==115){
        require_once('inscritos-cadastro-ok-trabalhos-115.php');
    }Else{
        require_once('inscritos-cadastro-ok-trabalhos.php');
    }    
    ?>
</div>
<div id="logAcessos" class="tab-content">
      <?php require_once('inscritos-cadastro-ok-log-acessos.php'); ?>
</div>
<div id="mensagens" class="tab-content">
    <?php require_once('inscritos-cadastro-ok-mensagens.php'); ?>
</div>

<?php
#--------------------------------------------------------
        #Movendo para o proximo registro
        $RS->MoveNext();
      }
    //   echo "<h4>Total de Registros Localizados: ".$ii."</h4>";
    }Else{
      echo "
      <hr size=1 width=100%>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <a href='index.php?content=view/admin/inscritos-cadastro-incluir.php&cod_evento=".$cod_evento."'><img src='resources/image/txt-incluir.gif' border='0'></a>
      &nbsp;&nbsp;&nbsp;&nbsp;

      <hr size=1 width=100%>
        <table border=0 width=570>
          <tr><td>
            &nbsp;&nbsp;&nbsp;&nbsp;
            O Inscrito Número: ".$cod_inscrito.", não Existe ou foi Excluído!
          </td></tr>
        </table>
      <br>
      ";
    }
  }Else{
    echo "
    <hr size=1 width=100%>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <a href='index.php?content=view/admin/inscritos-cadastro-incluir.php&cod_evento=".$cod_evento."'><img src='resources/image/txt-incluir.gif' border='0'></a>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <hr size=1 width=100%>
      <table border=0 width=570>
        <tr><td>
          &nbsp;&nbsp;&nbsp;&nbsp;
          <!--<br>ERRO SQL - Tente novamente ou entre em contado com adminstrador do sistema.-->
          <br>Para incluir uma inscrição clique no botão Incluir!
        </td></tr>
      </table>
    <br>
    ";
  }
}Else{
  echo "
    <hr size=1 width=100%>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <a href='index.php?content=view/admin/inscritos-cadastro-incluir.php&cod_evento=".$cod_evento."'><img src='resources/image/txt-incluir.gif' border='0'></a>
    &nbsp;&nbsp;&nbsp;&nbsp;
  ";
  echo "<h4>Para localizar algum cadastro informe ao menos um dos campos acima.</h4><h5>Fique atento, buscas por nome podem retornar mais de um registro.</h5>";
}
?>
<form name="getdoc" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarUpload">
    <input type="hidden" name="m" value="abrirWord">
    <input type="hidden" value="" name="cod">
</form>
<form name="visualizarDoc" method="post" action="<?php echo WB_URL_COMMAND?>" target="_blank">
    <input type="hidden" name="c" value="GerenciarUpload">
    <input type="hidden" name="m" value="visualizarDoc">
    <input type="hidden" value="" name="cod">
</form>
<script language="JavaScript" type="text/javascript">
    function getdoc(cod){
        document.getdoc.cod.value=cod;
        document.getdoc.submit();
    }
    function visualiza(cod){
        document.visualizarDoc.cod.value=cod;
        document.visualizarDoc.submit();
    }
function excluirIsento(codInscrito){
    codIsento = $('#codInscritoIsento-'+codInscrito).val();
    if(confirm('Deseja excluir inscrito: '+codInscrito+' da lista de isentos?')){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'codInscrito':codInscrito,'codIsento':codIsento, 'c':'GerenciarIsento', 'm':'excluirIsento'}),
            dataType: "json",
            cache: false,
            success: function(data){
                elementMsg = '#msgInscrito-'+codInscrito
                processJsonElement(data,elementMsg);                
                if(data.statusMessage==1){
                    $('#txtInscritoIsento-'+codInscrito).html('');
                    $('#btnExcluirIsento-'+codInscrito).removeClass('show').addClass('hide');
                    $('#codInscritoIsento-'+codInscrito).val('');
                    $('#btnAdicionarIsento-'+codInscrito).removeClass('hide').addClass('show');
                }else{
                    $('#btnExcluirIsento-'+codInscrito).removeClass('hide').addClass('show')
                    $('#btnAdicionarIsento-'+codInscrito).removeClass('show').addClass('hide');
                }
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando-'+codInscrito,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando-'+codInscrito,'hidden');
            }
        });
    }
}
function adicionarIsento(codInscrito,nomeInscrito, cpf, eMail){
    if(confirm('Deseja adicionar inscrito: '+nomeInscrito+' à lista de isentos?')){
        var departamento = prompt("Informe departamento que esta abonando a inscrição.");
        var organizacao = prompt("Informe organização que esta abonando a inscrição.");
        var cpf_cnpj_abonador = prompt("Informe o cpf ou cnpj do órgão que esta abonando a inscrição.");
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'nomeIsento':nomeInscrito,'codInscrito':codInscrito,'departamento':departamento,'organizacao':organizacao,'cpfCnpjAbonador':cpf_cnpj_abonador,'cpf':cpf,'email':eMail, 'c':'GerenciarIsento', 'm':'salvarIsento'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
                elementMsg = '#msgInscrito-'+codInscrito
                processJsonElement(data,elementMsg);
                if(data.statusMessage==1){
                    $('#btnExcluirIsento-'+codInscrito).removeClass('hide').addClass('show');
                    $('#txtInscritoIsento-'+codInscrito).html('Cód.Isento: '+data.codIsento+'');
                    $('#codInscritoIsento-'+codInscrito).val(data.codIsento);
                    $('#btnAdicionarIsento-'+codInscrito).removeClass('show').addClass('hide');
                }else{
                    $('#btnExcluirIsento-'+codInscrito).removeClass('show').addClass('hide');
                    $('#btnAdicionarIsento-'+codInscrito).removeClass('hide').addClass('show');
                }              
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando-'+codInscrito,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando-'+codInscrito,'hidden');
            }
        });
    }
}
function excluirInscritoVoucher(codInscrito){
    var codVoucher=$('#codInscritoVoucher-'+codInscrito).val();
    if(confirm('Deseja excluir voucher '+codVoucher+' do inscrito: '+codInscrito+'?')){
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'codInscrito':codInscrito,'codVoucher':codVoucher, 'c':'GerenciarInscritoVoucher', 'm':'excluirInscritoVoucher'}),
            dataType: "json",
            cache: false,
            success: function(data){
                elementMsg = '#msgInscrito-'+codInscrito
                processJsonElement(data,elementMsg);                
                if(data.statusMessage==1){
                    $('#txtInscritoVoucher-'+codInscrito).html('');
                    $('#btnExcluirInscritoVoucher-'+codInscrito).removeClass('show').addClass('hide');
                    $('#codInscritoVoucher-'+codInscrito).val('');
                    $('#btnAdicionarInscritoVoucher-'+codInscrito).removeClass('hide').addClass('show');
                }else{
                    $('#btnExcluirInscritoVoucher-'+codInscrito).removeClass('hide').addClass('show')
                    $('#btnAdicionarInscritoVoucher-'+codInscrito).removeClass('show').addClass('hide');
                }
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando-'+codInscrito,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando-'+codInscrito,'hidden');
            }
        });
    }
}
function adicionarInscritoVoucher(codInscrito){
    var codVoucher = prompt("Informe o código do voucher.");
    if(codVoucher!=null){   
//        if(confirm('Voucher digitado: ' + codVoucher)){ }else{ return false } 
        $.ajax({
            type: "POST",
            url: '<?php echo  WB_URL_COMMAND?>&t='+Math.random(),
            data: ({'codInscrito':codInscrito,'codVoucher':codVoucher, 'c':'GerenciarInscritoVoucher', 'm':'adicionarInscritoVoucher'}),
            dataType: "json",
            async: false,
            cache: false,
            success: function(data){
                elementMsg = '#msgInscrito-'+codInscrito
                processJsonElement(data,elementMsg);
                if(data.statusMessage==1){
                    $('#btnExcluirInscritoVoucher-'+codInscrito).removeClass('hide').addClass('show');
                    $('#txtInscritoVoucher-'+codInscrito).html('Cód.Voucher: '+codVoucher+'');
                    $('#codInscritoVoucher-'+codInscrito).val(codVoucher);
                    $('#btnAdicionarInscritoVoucher-'+codInscrito).removeClass('show').addClass('hide');
                }else{
                    $('#btnExcluirInscritoVoucher-'+codInscrito).removeClass('show').addClass('hide');
                    $('#btnAdicionarInscritoVoucher-'+codInscrito).removeClass('hide').addClass('show');
                }              
            }
            ,timeout: 4000
            ,beforeSend: function() {
                loading('div#carregando-'+codInscrito,'show');
            }
            ,error: function(ajax) {
                //showResponse(ajax);
            }
            ,complete: function() {        
                loading('div#carregando-'+codInscrito,'hidden');
            }
        });
    }
}
</script>
</div></div></div></div></div></div></div></div>
<style>
legend {
    font-weight: bold;
    font-size: 19px;
}
/* Estilos CSS para as abas */
.tab-content {
    display: none;
}

.dataTable tbody tr:nth-child(even) {
    background-color: #FFF;
}
.dataTable tbody tr:nth-child(odd) {
    background-color: #CCC;
}
.dataTable tbody td{
    padding: 5px;
    font-family: Helvetica,sans-serif;
    font-size: 13px;
}
.txtAba{
    font-family: Helvetica,sans-serif;
    font-size: 13px;
}
.resizable {
    resize: both; /* Isso permite redimensionar horizontal e verticalmente */
    overflow: auto; /* Adicione barras de rolagem se o conteúdo exceder o tamanho da textarea */
}
label {
    margin-left: 15px;
}
.dataTable tr td:first-child {
    width: 20%; /* Defina a largura desejada da primeira célula */
  }
.dataTable  {
    width: 100%; /* Defina a largura desejada da primeira célula */
  }
</style>
<script>
$(function() {
    $("#tabs").tabs();
    // Ao clicar em uma aba, salve o índice no LocalStorage
    $("#tabs").on("tabsactivate", function(event, ui) {
        var tabIndex = ui.newTab.index();
        localStorage.setItem('lastActiveTab', tabIndex);
    });
    // Se veio com #aba na URL (ex.: #pagamentos), ela tem prioridade sobre o LocalStorage
    var hashAba = window.location.hash;
    var idxHash = hashAba ? $('#tabs > ul > li > a[href="' + hashAba + '"]').parent().index() : -1;
    if (idxHash >= 0) {
        $("#tabs").tabs("option", "active", idxHash);
    } else {
        var lastActiveTab = localStorage.getItem('lastActiveTab');
        if(lastActiveTab) {
            $("#tabs").tabs("option", "active", parseInt(lastActiveTab));
        }
    }
});
</script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>