<?php
#Executa Existe Oficina
if (($_SESSION['content'] == "inscricao_congresso_ok.php")) {
    $var_sql_consulta = "
    SELECT oo.cod_oficina, oo.titulo
    FROM tab_lig_inscritos_oficinas as lio, tab_opcoes_oficinas as oo, tab_inscritos as ti
    WHERE (lio.cod_evento   = " . COD_EVENTO . ")
       and(lio.cod_inscrito = ti.cod_inscrito)
       and(ti.cod_inscrito  = '" . $cod_inscrito . "')
       and(ti.status        = 'ATIVADA')
       and(lio.cod_oficina  = oo.cod_oficina)
       and(lio.cod_evento   = oo.cod_evento)
   ";

    $RS_1 = $Conexao_db->Execute($var_sql_consulta);
    If (!$RS_1->EOF) {
        $cod_oficina = $RS_1->fields[0];
        $titulo = $RS_1->fields[1];
        $_SESSION['cod_oficina'] = $cod_oficina;
    }
#Fim Executa Existe
}
@$cod_oficina = $_SESSION['cod_oficina'];
If (@$cod_oficina != "") {
    #-----------------------------------------------------------------------------------------
    $var_sql = "
              SELECT cod_oficina, titulo, numero_vagas
              FROM tab_opcoes_oficinas
              WHERE (status      = 'ATIVADA')
                AND (cod_oficina = $cod_oficina)
                AND (cod_evento  = COD_EVENTO)
             ";

    #echo $var_sql;
    #Executando a consulta no banco SQL
    $RS = $Conexao_db->Execute($var_sql);

    #Verificando se houve retorno de algum registro
    If (!$RS->EOF) {

        While (!$RS->EOF) {

            $cod_oficina = $RS->fields[0];
            $titulo = $RS->fields[1];
            $numero_vagas = $RS->fields[2];

            $vagas_net_disponiveis = $numero_vagas;

            #echo $cod_oficina."<br>";
            #echo $titulo."<br>";
            #echo $numero_vagas."<br>";
            #echo $vagas_net_disponiveis."<br>";
            #Definindo tabela com resultado
            echo "
            <table cellspacing='0' cellpadding='0' width='100%' border=0>
            <tr>
                <td>";

            If ($vagas_net_disponiveis <= 0) {
                $vagas_net_disponiveis = "<b>Inscrições Encerradas</b>";
            } Else {
                echo "<INPUT TYPE='hidden'  NAME='cod_oficina' VALUE='" . $cod_oficina . "'>";
                echo "<INPUT TYPE='hidden'  NAME='titulo' VALUE='" . $titulo . "'>";
            }

            echo "
                </td>
                <td width='100%'>
                  Oficina Selecionada: <br><b>" . $titulo . "</b><br>
                </td>
            </tr>
            </table><br>
            ";

            #Movendo para o proximo registro
            $RS->MoveNext();
        }
        if (($_SESSION['content'] != "inscricao_congresso_confirmar.php") and ( $_SESSION['content'] != "inscricao_trabalhos_inicio.php")) {
            ?>
            <a href="index.php?content=view/inscricao/inscricao_congresso_seleciona_oficina.php&cod_oficina="
               class="btn btn-xs btn-primary" role="button">Alterar Oficina</a>
               <?php
           }
       } Else {
           #Se não comando SQL não retornou nenhum registro imprime mensagens de aviso
           #Mensagem de aviso q não comando SQL não retornou nenhum registro.
           echo "A busca não retornou registro, Tente novamente.";
       }
   } Else {
       echo "Nenhuma Oficina foi selecionada.";

       if (($_SESSION['content'] != "inscricao_congresso_confirmar.php") and ( $_SESSION['content'] != "inscricao_trabalhos_inicio.php")) {
           ?><br><br>
        <a href="index.php?content=view/inscricao/inscricao_congresso_seleciona_oficina.php"
           class="btn btn-xs btn-primary" role="button">Selecionar Oficina</a>
        <?php
    }
}
?>




