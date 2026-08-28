      <fieldset>
          <legend> Mensagens via WhatsApp</legend>
          <?php
            $var_sql = "SELECT cod_evento, login, origem, sucesso, descricao, data_inclusao 
            FROM sis_log_acesso 
            WHERE login='" . $objI->getEMail() . "' and login='zaka' order by cod_log_acesso desc";
            // echo $var_sql;
            if ($rsLog = FWA_Executa_SQL($debug, $Conexao_db, $var_sql)) {
                if (!$rsLog->EOF) {
                    echo "<table border=0 class='table'>";
                    // echo "<tr><td colspan=4>login: ".$rsLog->fields['login']."</td></tr>";
                    echo "<tr><th>Projeto</th><th>Módulo</th><th>Status login</th><th>Mensagem</th><th>Data</th></tr>";
                    while (!$rsLog->EOF) {
                        $descSucesso = "<span class='text-danger'>falhou</span>";
                        if ($rsLog->fields['sucesso'] == 1) {
                            $descSucesso = "<span class='text-success'>sucesso</span>";
                        }
                        #Adicionando nas variaveis, os valores dos campos retirados dos registros
                        echo "<tr>";
                        echo "<td>" . $rsLog->fields['cod_evento'] . "</td>";
                        echo "<td>" . $rsLog->fields['origem'] . "</td>";
                        echo "<td>" . $descSucesso . "</td>";
                        echo "<td>" . $rsLog->fields['descricao'] . "</td>";
                        echo "<td>" . formatDateView($rsLog->fields['data_inclusao']) . "</td>";
                        echo "</tr>";
                        $rsLog->MoveNext();
                    }
                    echo "</table>";
                } else {
                    echo "Nenhuma ocorrência encontrada.";
                }
            }
            ?>
      </fieldset>