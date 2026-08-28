<fieldset><legend> Log de Acessos</legend>
    <?php
    /*
     * Sinal para o suporte: a pessoa ja DEFINIU senha propria (bcrypt), ou ainda
     * so entra pelo metodo legado (numero da inscricao)? Nao indica que ela
     * JA USOU essa senha para logar -- so que a senha existe (o log de acesso
     * abaixo nao separa por qual metodo o sucesso veio).
     */
    $temSenhaPropria = SenhaHelper::isBcrypt($objI->getSenhaCriptografada());
    echo "<div class='card-total' style='margin-bottom:10px;'>";
    echo   $temSenhaPropria
         ? "<span><span class='badge-tipo'><span class='dot dot-green'></span>senha nova</span></span>"
         : "<span><span class='badge-tipo'><span class='dot dot-orange'></span>legado</span></span>";
    echo "</div>";
    /*
     * Tabela no padrao tbl-fin do projeto, ordenavel pelo cabecalho (o handler
     * delegado vive no default.js). Sem LIMIT: a listagem mostra tudo, como nas
     * demais telas — o que interessa aqui e justamente o historico completo.
     */
    $var_sql = "SELECT cod_evento, login, origem, sucesso, descricao, data_inclusao
    FROM sis_log_acesso
    WHERE login='".$objI->getEMail()."' order by cod_log_acesso desc";
    // echo $var_sql;
        If ($rsLog=FWA_Executa_SQL($debug,$Conexao_db,$var_sql)) {
        If (!$rsLog->EOF) {
            $linhas    = '';
            $totLinhas = 0;
            $totOk     = 0;
            While (!$rsLog->EOF) {
                $totLinhas++;
                if ($rsLog->fields['sucesso'] == 1) {
                    $totOk++;
                    $descSucesso = "<span class='badge-tipo'><span class='dot dot-green'></span>sucesso</span>";
                } else {
                    $descSucesso = "<span class='badge-tipo'><span class='dot dot-red'></span>falhou</span>";
                }
                #Adicionando nas variaveis, os valores dos campos retirados dos registros
                $linhas .= "<tr>";
                $linhas .= "<td class='col-codigo'>".$rsLog->fields['cod_evento']."</td>";
                $linhas .= "<td>".$rsLog->fields['origem']."</td>";
                $linhas .= "<td class='col-badge'>".$descSucesso."</td>";
                $linhas .= "<td>".$rsLog->fields['descricao']."</td>";
                $linhas .= "<td class='col-data'>".formatDateView($rsLog->fields['data_inclusao'])."</td>";
                $linhas .= "</tr>";
                $rsLog->MoveNext();
            }

            echo "<div class='card-total' style='margin-top:0;margin-bottom:10px;'>";
            echo   "<span>Registros <b>".$totLinhas."</b></span>";
            echo   "<span>Com sucesso <b>".$totOk."</b></span>";
            echo   "<span>Falhas <b>".($totLinhas - $totOk)."</b></span>";
            echo   "<span style='color:#999;'>Login consultado: ".htmlspecialchars($objI->getEMail(), ENT_QUOTES, 'UTF-8')."</span>";
            echo "</div>";

            echo "<table id='tblLogAcessos' class='tbl-fin'>";
            echo "<thead><tr>"
               . "<th class='col-codigo' data-col='0' style='cursor:pointer;'>Projeto <span class='sort-arrow'>&#8593;&#8595;</span></th>"
               . "<th data-col='1' style='cursor:pointer;'>Módulo <span class='sort-arrow'>&#8593;&#8595;</span></th>"
               . "<th class='col-badge' data-col='2' style='cursor:pointer;'>Status login <span class='sort-arrow'>&#8593;&#8595;</span></th>"
               . "<th data-col='3' style='cursor:pointer;'>Aviso <span class='sort-arrow'>&#8593;&#8595;</span></th>"
               . "<th class='col-data' data-col='4' style='cursor:pointer;'>Data <span class='sort-arrow'>&#8593;&#8595;</span></th>"
               . "</tr></thead>";
            echo "<tbody>".$linhas."</tbody>";
            echo "</table>";
        }else{
            echo montarVazio('Nenhuma ocorrência encontrada.');
        }
    }
    ?>
    </fieldset>
