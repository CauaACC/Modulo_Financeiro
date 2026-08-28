<?php
require_once(dirname(__FILE__)."/../../include/config.php");
#require_once(dirname(__FILE__)."/../../include/inscricao_request_variaveis.php");
#Fazendo conexão com o banco
$Conexao_db = FWA_Conexao("OpcTec");

$FWA_Debug = false;
?>
<h3>Área do Inscrito</h3>  
<div class="panel-group">
<?php
//if (!$_SESSION['_logado']){
//    $_SESSION['MSG']="Não_é_necessário_digitar_login_e_senha!";  
//    $url = "". WB_URL_VIEW."?content=view/admin/login.php";
//    FWA_Mostrar_Mensagem($MSG);
//    echo "<a href='" . $url . "'>" . $url . "->codigo do usuario:" . $_SESSION['_cod_usuario'] . "</a>";
//    if(!$FWA_Debug){
//        header("Location: $url");
//        echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
//    }
//    exit();
//}
If ($_SESSION['_inscrito']!='' and $_SESSION['_logado']!='') {#Testa se o sessao logado

    #Definindo comando SQL
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
                         (t.cod_evento        = " . COD_EVENTO . ")
                     and (t.cod_inscrito      = ti.cod_inscrito)
                     and (ti.cpf              = '" . $_SESSION['_cpf'] . "')
                     and (ti.status           = 'ATIVADA')
                     and (t.cod_area_tematica = oat.cod_area_tematica)) as total
               FROM tab_inscritos as i
               WHERE
                   (cod_evento    = " . COD_EVENTO . ")
               and (i.cpf         = '" . $_SESSION['_cpf'] . "')
               and (i.status      = 'ATIVADA')
               ORDER BY i.cod_inscrito DESC
              ";
    $Conexao_db->debug=$FWA_Debug;
    $RS = $Conexao_db->Execute($var_sql);

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {#Se cpf Esta inscrito
        While (!$RS->EOF) {

            #Adicionando nas variaveis, os valores dos campos retirados dos registros
            $nome_inscrito = $RS->fields[0];
            $cod_inscrito = $RS->fields[1];
            $rg = $RS->fields[2];
            $cpf = $RS->fields[3];
            $e_mail = $RS->fields[4];
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
        ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <FORM NAME='exit' METHOD='POST' ACTION='view/inscricao/inscricao_logout_acao.php'>
                    <h4>Ola <?php echo $nome_inscrito?>.
                    &nbsp;&nbsp;&nbsp;
                    <INPUT TYPE='hidden' VALUE='Voltar' NAME='acao'>
                    <button class="btn btn-xs btn-primary" type="submit">Sair</button>
                    </h4>
                </FORM>
            </div>
            <div class="panel-body">
            <?php
            echo "Nome para o Certificado: {$nome_certificado}";
            echo "
            <br>Número de Inscrição: {$cod_inscrito}
            <br>Cpf: {$cpf}
            <br>E-mail:              {$e_mail} 
            ";
            ?>    
            </div>
        </div>
        <?php
            #Movendo para o proximo registro
            $RS->MoveNext();
        }

        ?>
        <div class="panel panel-default">
            <div class="panel-heading">Submissão de Trabalho</div>
            <div class="panel-body">
            <?php
            $_SESSION['cpf'] = $cpf;
            $_SESSION['cod_inscrito'] = $cod_inscrito;
            if($objEv->getCodEvento()==69){
                require_once('inscricao_trabalhos_consulta_trabalhos_submetidos_edital.php');
            }else{
                require_once('inscricao_trabalhos_consulta_trabalhos_submetidos.php');
            }
            ?>    
            </div>
        </div>
        <?php

        ?>
        <div class="panel panel-default">
            <div class="panel-heading">Oficina Selecionda</div>
            <div class="panel-body">
            <?php
            require_once('inscricao_congresso_consulta_oficina.php');   
            ?>    
            </div>
        </div>
        <?php
        
        #echo "<fieldset><legend> Monografia </legend><blockquote>";
        #require_once('inscricao_monografia_consulta_monografia_submetida.php');
        #echo "</blockquote></fieldset>";
        #echo "<fieldset><legend> Desconto </legend>";
        #require_once('inscricao_texto_desconto.php');
        #echo "</fieldset>";

        ?>
        <div class="panel panel-default">
            <div class="panel-heading">Forma de Pagamento</div>
            <div class="panel-body">
            <?php
            require_once('inscricao_botao_boleto.php');    
            ?>    
            </div>
        </div>
        <?php
    } Else {#Se cpf Esta inscrito
        $_SESSION['MSG']="Cpf_ou_número_de_inscrição_não_encontrados!";
        $url = "". WB_URL_VIEW."?content=view/inscricao/inscricao_login.php";
        echo "<a href='" . $url . "'>" . FWA_Mostrar_Mensagem($MSG) . "</a>";
        if(!$FWA_Debug){
            header("Location: $url");
            echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
        }
    }
} Else {#Testa se sessão logado
    $_SESSION['MSG']="Para_acessar_esta_area_é_necessário_estar_logado!";
    $url = "". WB_URL_VIEW."?content=view/inscricao/inscricao_login.php";
    echo "<a href='" . $url . "'>" . FWA_Mostrar_Mensagem($MSG) . "</a>";
    if(!$FWA_Debug){
        header("Location: $url");
        echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"0; URL=" . $url . "\"></HTML>";
    }
}
?>
</div>
