<?php
#---- Busca Trabalhos ---------------------------------------------------------
if ($objEvConf->getTrabSubmissao() == 1) {

    $debug = false;

    $var_sql = "
        SELECT
         toa.titulo
        ,ti.cod_inscrito
        ,ti.nome_inscrito
        ,tt.cod_trabalho
        ,tt.autor_apresentador_nome
        ,tt.autor_apresentador_e_mail
        ,tt.nome_co_autor_1
        ,tt.nome_co_autor_2
        ,tt.nome_co_autor_3
        ,tt.nome_co_autor_4
        ,tt.nome_co_autor_5
        ,tt.nome_co_autor_6
        ,tt.instituicao_trabalho
        ,tt.titulo_trabalho
        ,tt.palavras_chave
        ,tt.resumo_trabalho
        ,tt.arquivo_upload
        ,tt.arquivo_gerado
        ,tt.flg_enviado_avaliacao
        ,tt.premiacao
        ,tt.status
        ,tt.pontos_total
        ,tt.pontos_total_avaliado
        ,tt.pontos_total_confirmado
        ,tt.versao
        ,tt.tipo_documento
        ,tt.data_inclusao
        ,tt.data_alteracao
        ,tt.flg_assinado
        ,tt.flg_permitir_alterar
        ,tt.flg_permitir_alterar_data_fim
        ,tt.periodo_referencia
        FROM tab_trabalhos tt
        INNER JOIN tab_inscritos ti ON tt.cod_inscrito = ti.cod_inscrito
        LEFT JOIN tab_opcoes_areas_tematicas toa ON tt.cod_area_tematica = toa.cod_area_tematica
        WHERE tt.cod_evento   = {$cod_evento}
          AND tt.cod_inscrito = {$cod_inscrito}
        ORDER BY toa.titulo
    ";

    echo "<fieldset style='background:#eae9e9; border:1px solid #ccc;'>
          <legend>{$objEvConf->getTrabAnexoTipo()}</legend>";

    if ($RS_T = FWA_Executa_SQL($debug, $Conexao_db, $var_sql)) {

        if (!$RS_T->EOF) {

            echo "
            <table class='table table-bordered table-striped table-condensed'>
            <thead>
                <tr>
                    <th>Cód.</th>
                    <th>Tipo Documento</th>
                    <!--th>Título</th-->
                    <!--th>Instituição</th-->
                    <!--th>Ref.</th-->
                    <!--th>Status</th-->
                    <!--th>Assinado</th-->
                    <!--th>Versão</th-->
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            ";

            while (!$RS_T->EOF) {

                $cod_trabalho       = $RS_T->fields[3];
                $instituicao        = $RS_T->fields[12];
                $titulo_trabalho    = $RS_T->fields[13];
                $arquivo_gerado     = $RS_T->fields[17];
                $statusT            = $RS_T->fields[20];
                $versao             = $RS_T->fields[24];
                $tipo_documento     = $RS_T->fields[25];
                $data_inclusao      = $RS_T->fields['data_inclusao'];
                $data_alteracao     = $RS_T->fields['data_alteracao'];
                $flg_assinado       = $RS_T->fields['flg_assinado'];
                $periodo_referencia = $RS_T->fields['periodo_referencia'];

                echo "<tr>";

                echo "<td>{$cod_trabalho}</td>";
                echo "<td>{$tipo_documento}</td>";
                // echo "<td>{$titulo_trabalho}</td>";
                // echo "<td>{$instituicao}</td>";
                // echo "<td>" . ($periodo_referencia ?: '—') . "</td>";
                // echo "<td>{$statusT}</td>";
                // echo "<td>" . ($flg_assinado == 1 ? 'Sim' : 'Não') . "</td>";
                //echo "<td>{$versao}</td>";

                /* ===== BLOCO CRÍTICO – NÃO ALTERADO ===== */
                echo "<td>";
                if ($arquivo_gerado != '') {

                    $ultimo_arquivo = $arquivo_gerado;

                    preg_match(
                        '/_(\d+)\.(pdf|txt|doc|docx|xls|xlsx|jpg|jpeg|png)$/',
                        $ultimo_arquivo,
                        $matches
                    );

                    $ultima_versao = isset($matches[1]) ? intval($matches[1]) : 0;
                    $extensao = isset($matches[2]) ? "." . $matches[2] : "";
                    $nome_arquivo_base = preg_replace(
                        '/_\d+\.(pdf|txt|doc|docx|xls|xlsx|jpg|jpeg|png)$/',
                        '',
                        $ultimo_arquivo
                    );

                    for ($i = $ultima_versao; $i >= 1; $i--) {
                        $arquivo_gerado = $nome_arquivo_base . "_" . $i . $extensao;
                        // retornarIconeExtensao($arquivo_gerado) . "

                        echo "<button type='button'
                                class='btn btn-xs btn-primary'
                                onclick=\"visualiza('{$arquivo_gerado}')\">
                                Visualizar </button><br>";


                        echo $data_inclusao
                            ? " <small>Incl.: " . formatDateView($data_inclusao) . "</small>"
                            : "";

                        echo $data_alteracao
                            ? " <small>Alt.: " . formatDateView($data_alteracao) . "</small>"
                            : "";
                        echo " - v{$i}";
                        
                        echo "<br>";
                    }
                }

                // echo "
                //     <a href='index.php?content=view/admin/inscritos-trabalho-cadastro-alterar.php
                //         &cod_trabalho={$cod_trabalho}
                //         &cod_evento={$cod_evento}'>
                //         <img src='resources/image/txt-alterar.gif'>
                //     </a>
                // ";

                // if (FWA_Autenticacao($_SESSION['_cod_usuario'], 999, 5, false, false)) {
                //     echo "
                //         <a href='view/admin/inscritos-trabalho-cadastro-excluir-grava.php
                //             ?cod_inscrito={$cod_inscrito}
                //             &cod_trabalho={$cod_trabalho}
                //             &cod_evento={$cod_evento}'>
                //             <img src='resources/image/txt-excluir.gif'>
                //         </a>
                //     ";
                // }

                echo "</td>";
                echo "</tr>";

                $RS_T->MoveNext();
            }

            echo "</tbody></table>";

        } else {

            echo "
                <a href='index.php?content=view/admin/inscritos-trabalho-cadastro-incluir.php
                    &cod_inscrito={$cod_inscrito}
                    &cod_evento={$cod_evento}'>
                    <img src='resources/image/txt-incluir.gif'>
                </a>
            ";
        }
    }

    echo "</fieldset>";
}
