<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-cabecalho.php");
#require_once(dirname(__FILE__)."/../../view/admin/consulta-dados-congresso-busca.php");
echo "<div class=\"panel-body\">";
$Conexao_db = FWA_Conexao("OpcTec");

#Definindo comando SQL
$var_sql = "
                 SELECT Count(tii.cod_inscrito) as Total_Inscritos
                 , (SELECT Count(cod_inscrito)
                   FROM tab_inscritos
                   WHERE (cod_evento = tii.cod_evento)and
                         (status     = 'ATIVADA') ) as Total_Inscritos_Ativados
                 , (SELECT Count(cod_inscrito)
                   FROM tab_inscritos
                   WHERE (cod_evento = tii.cod_evento)and
                         (status     = 'DESATIVADA') ) as Total_Inscritos_Desativados
                 , (SELECT Count(cod_inscrito)
                   FROM tab_inscritos
                   WHERE (cod_evento = tii.cod_evento)and
                         (status     = 'EXCLUÍDA') ) as Total_Inscritos_Excluidos
                 , (SELECT Count(tt.cod_trabalho)
                   FROM tab_trabalhos tt, tab_inscritos ti
                   WHERE (tt.cod_evento   = tii.cod_evento)and
                         (tt.cod_inscrito = ti.cod_inscrito)and
                         (tt.status       = 'ATIVADA') and
                         (ti.status       = 'ATIVADA') ) as Total_Trabalhos_Enviados
                 , (SELECT Count(tm.cod_monografia)
                   FROM tab_monografias tm, tab_inscritos ti
                   WHERE (tm.cod_evento   = tii.cod_evento)and
                         (tm.cod_inscrito = ti.cod_inscrito)and
                         (tm.status       = 'ATIVADA')and
                         (ti.status       = 'ATIVADA') ) as Total_Monografias_Enviadas
                 , (SELECT Count(tlio.cod_oficina)
                   FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti, tab_opcoes_oficinas toa
                   WHERE (tlio.cod_evento   = tii.cod_evento)and
                         (tlio.cod_oficina  = toa.cod_oficina)and
                         (tlio.cod_inscrito = ti.cod_inscrito)and
                         (ti.status         = 'ATIVADA')) as Total_Inscritos_Oficinas
                 , (SELECT Count(cod_inscrito)
                   FROM tab_inscritos
                   WHERE (cod_evento = tii.cod_evento)and
                         (desconto   = 'SIM')and
                         (status     = 'ATIVADA') ) as Total_Inscritos_Com_Desconto
                 , (SELECT Count(cod_inscrito)
                   FROM tab_inscritos
                   WHERE (cod_evento = tii.cod_evento)and
                         (desconto   = 'NÃO')and
                         (status     = 'ATIVADA') ) as Total_Inscritos_Sem_Desconto
                 , (SELECT Count(distinct(tp.cod_inscrito))
                   FROM tab_inscritos ti
                   INNER JOIN tab_pagamentos tp ON ti.cod_inscrito=tp.cod_inscrito and ti.cod_evento=tp.cod_evento
                   WHERE ti.cod_evento = tii.cod_evento and
                         ti.status     = 'ATIVADA')  as Total_Inscritos_Efetuaram_Pagamento
                 , (SELECT Count(tp.cod_inscrito)
                   FROM tab_inscritos ti, tab_pagamentos tp
                   WHERE (ti.cod_evento = tii.cod_evento)and
                         (ti.status     = 'ATIVADA')and
                         (ti.cod_inscrito=tp.cod_inscrito))  as Total_PG_Registrados
                 , (SELECT Sum(tp.valor)
                   FROM tab_inscritos ti, tab_pagamentos tp
                   WHERE (ti.cod_evento = tii.cod_evento)and
                         (ti.status     = 'ATIVADA')and
                         (ti.cod_inscrito=tp.cod_inscrito) and
                         (tp.forma_pagamento<>'BOLETO BANCÁRIO UNIFICADO'))  as Total_Valor_Arrecadado
                 ,(SELECT SUM(valor_pago) FROM tab_boletos WHERE cod_evento= tii.cod_evento and flg_pagamento=1 GROUP BY cod_evento) as Total_Valor_Arrecadado_Boleto
                 ,(SELECT Count(distinct(tb.cod_inscrito))
                   FROM tab_inscritos ti, tab_boletos tb
                   WHERE ti.cod_evento = tii.cod_evento and
                         ti.status     = 'ATIVADA' and
                         ti.cod_inscrito=tb.cod_inscrito)  as Total_Inscritos_Geraram_Boletos
                 ,(SELECT Count(tb.cod_inscrito)
                   FROM tab_inscritos ti, tab_boletos tb
                   WHERE ti.cod_evento = tii.cod_evento and
                         ti.status     = 'ATIVADA' and
                         ti.cod_inscrito=tb.cod_inscrito)  as Total_Boletos_Gerados
                 ,(SELECT Count(tb.cod_inscrito)
                   FROM tab_inscritos ti, tab_boletos tb
                   WHERE ti.cod_evento = tii.cod_evento and
                         ti.status     = 'ATIVADA'and
                         ti.cod_inscrito=tb.cod_inscrito and (tb.status IS NULL OR tb.status='ATIVADA')
                         and (tb.flg_pagamento is null or tb.flg_pagamento<>1)
                         AND (tb.flg_remessa<>1 OR tb.flg_remessa IS NULL))  as Total_Boletos_Aguardando_Remessa
                 ,(SELECT COUNT(DISTINCT(ti.cod_inscrito)) 
                   FROM tab_inscritos ti
                   WHERE  ti.cod_evento = tii.cod_evento AND
                          ti.status     = 'ATIVADA' AND
                         (DATE_FORMAT(ti.dat_incl, '%Y-%m-%d') >= '2018-06-10' ))as Total_Inscritos_Apos_Data_Para
                ,(SELECT COUNT(DISTINCT(tis.cpf)) 
                   FROM tab_isentos tis
                   WHERE (tis.cod_evento = tii.cod_evento)AND
                         (tis.status     = 'ATIVADA')) as Total_Isentos_Cadastrados
                , (SELECT Count(ti.cod_inscrito) 
                   FROM tab_inscritos ti
                   LEFT JOIN tab_isentos tis ON tis.cpf = ti.cpf AND tis.cod_evento = ti.cod_evento
                   LEFT JOIN tab_isentos_codigo tic ON tic.cod_inscrito = ti.cod_inscrito
                   WHERE ti.cod_evento = tii.cod_evento and
                         ti.status     = 'ATIVADA' AND (tis.cpf IS NOT NULL OR tic.cod_inscrito IS NOT NULL)) as Total_Inscritos_Isentos
                ,(SELECT COUNT(DISTINCT(vc.cod_voucher)) 
                   FROM tab_vouchers vc
                   WHERE (vc.cod_evento = tii.cod_evento)AND
                         (vc.status     = 'ATIVADA' or vc.status is null)) as Total_Vouchers_Gerados
                , (SELECT Count(ti.cod_inscrito) 
                   FROM tab_inscritos ti
                   INNER JOIN tab_lig_inscritos_vouchers livc on livc.cod_inscrito=ti.cod_inscrito and livc.cod_evento=ti.cod_evento
                   WHERE (ti.cod_evento = tii.cod_evento)and
                         (ti.status     = 'ATIVADA') ) as Total_Inscritos_Vouchers
                , ( SELECT count(ti.cod_inscrito) from tab_inscritos ti
                    where ti.status='ATIVADA' and (ti.cod_inscrito IN (SELECT tlip.cod_inscrito FROM tab_lig_inscritos_perfis tlip WHERE cod_perfil IN (select cod_perfil from tab_opcoes_perfis where perfil_isento=1 and cod_evento=tii.cod_evento))
                    or ti.cod_perfil in (select cod_perfil from tab_opcoes_perfis where perfil_isento=1 and cod_evento=tii.cod_evento))) Total_Inscritos_Isentos_Perfil   
                ,(SELECT Count(ti.cod_inscrito) 
                   FROM tab_inscritos ti where ti.cod_evento = tii.cod_evento AND ti.status='ATIVADA'
                    AND ti.cpf IN (SELECT REPLACE(REPLACE(cpf,'.',''),'-','') FROM tab_descontos WHERE cod_evento=tii.cod_evento AND porcentagem_desconto=100)) Total_Inscritos_Isentos_Desconto_100                    
                 FROM tab_inscritos tii
                 WHERE (tii.cod_evento = " . COD_EVENTO . ")
               "; # and (status     = 'ATIVADA')
//                  ,(Select Count(ti.cod_inscrito) 
//                    FROM tab_inscritos ti
//                    WHERE
//                    ( ti.cod_inscrito in (Select distinct(tp.cod_inscrito) from tab_pagamentos tp))
//                    AND(ti.cod_evento=1)
//                    AND (ti.status='ATIVADA')
//                    )
//    echo $var_sql;
#Fim da definição do comando SQL
//    $Conexao_db->debug=true;
#Executando a consulta no banco SQL
$RS = $Conexao_db->Execute($var_sql);

#Verificando se houve retorno de algum registro
If (!$RS->EOF) {
    #Definindo cabeçalho da tabela dos resultados da consula
    #Efetuando loop para o visualização dos registros retornados pela consulta SQL
    While (!$RS->EOF) {

        #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $Total_Inscritos = $RS->fields[0];
        $Total_Inscritos_Ativados = $RS->fields[1];
        $Total_Inscritos_Desativados = $RS->fields[2];
        $Total_Inscritos_Excluidos = $RS->fields[3];
        $Total_Trabalhos_Enviados = $RS->fields[4];
        $Total_Monografias_Enviadas = $RS->fields[5];
        $Total_Inscritos_Oficinas = $RS->fields[6];
        $Total_Inscritos_Com_Desconto = $RS->fields[7];
        $Total_Inscritos_Sem_Desconto = $RS->fields[8];
        $Total_Inscritos_Efetuaram_Pagamento = $RS->fields[9];
        $Total_PG_Registrados = $RS->fields[10];
        $Total_Valor_Arrecadado = $RS->fields[11];
        $Total_Valor_Arrecadado_Boleto = $RS->fields[12];
        $Total_Inscritos_Geraram_Boletos = $RS->fields[13];
        $Total_Boletos_Gerados = $RS->fields[14];
        $Total_Boletos_Aguardando_Remessa = $RS->fields[15];
        $Total_Inscritos_Apos_Data_Param = $RS->fields[16];
        $Total_Isentos_Cadastrados = $RS->fields[17];
        $Total_Inscritos_Isentos = $RS->fields[18];
        $Total_Vouchers_Gerados = $RS->fields[19];
        $Total_Inscritos_Vouchers = $RS->fields[20];
        $Total_Inscritos_Isentos_Perfil = $RS->fields[21];
        $Total_Inscritos_Isentos_Desconto_100 = $RS->fields[22];
        $Total_Inscritos_Isentos_Cadastrados = $Total_Inscritos_Isentos; // antes de somar desconto 100%

        $Total_Inscritos_Isentos = $Total_Inscritos_Isentos+$Total_Inscritos_Isentos_Desconto_100;

        #Definindo tabela com resultado
        #Movendo para o proximo registro
        $RS->MoveNext();
    }
}
// $Total_Inscritos_NaoIsento_NaoPagamento = $Total_Inscritos-$Total_Inscritos_Isentos-$Total_Inscritos_Geraram_Boletos-$Total_Inscritos_Vouchers;
/**removendo os inscritos com vouchers dessa conta, pois nem todos tem desconto de 100% */
// $Total_Inscritos_NaoIsento_NaoPagamento = 0;
// if($Total_Inscritos_Isentos>0){
    $Total_Inscritos_NaoIsento_NaoPagamento = $Total_Inscritos_Ativados-$Total_Inscritos_Efetuaram_Pagamento-$Total_Inscritos_Isentos-$Total_Inscritos_Isentos_Perfil-$Total_Inscritos_Vouchers;
// }
$Total_Inscritos_NaoPagaram_Boleto = $Total_Inscritos_Geraram_Boletos-$Total_Inscritos_Efetuaram_Pagamento;
$Total_Inscritos_Pagantes_Geral = $Total_Inscritos_Efetuaram_Pagamento+$Total_Inscritos_Isentos+$Total_Inscritos_Vouchers;
$Total_Isentos_Cadastrados_NaoInscrito = $Total_Isentos_Cadastrados-$Total_Inscritos_Isentos;

// Fornecedor e cadastrado como inscrito (perfil de categoria FORNECEDOR), mas
// nao e participante do evento -- entao nao entra na contagem de cadastrados.
// So o fornecedor sai da conta: comissao, ministrante e coordenacao continuam
// contando como sempre contaram, para nao mudar numero de evento nenhum.
$Total_Cadastros_Fornecedores = 0;
try {
    ConexaoDB::getInstance();
    $_conexPainel = ConexaoDB::getConexaoDB();
    $_sqlForn = sprintf("SELECT COUNT(*) AS n
                           FROM tab_inscritos i
                           INNER JOIN tab_opcoes_perfis p
                                   ON p.cod_perfil = i.cod_perfil AND p.cod_evento = i.cod_evento
                          WHERE i.cod_evento = '%s' AND i.status = 'ATIVADA'
                            AND p.categoria_perfil = 'FORNECEDOR'", COD_EVENTO);
    $_rsForn = $_conexPainel->Execute($_sqlForn);
    if ($_rsForn && !$_rsForn->EOF) {
        $Total_Cadastros_Fornecedores = intval($_rsForn->fields['n']);
    }
} catch (Exception $e) {
    $Total_Cadastros_Fornecedores = 0;
}
$Total_Cadastros_SemFornecedor = $Total_Inscritos_Ativados - $Total_Cadastros_Fornecedores;
// o fornecedor nao "deixou de pagar" -- ele nao se inscreve
$Total_Inscritos_NaoIsento_NaoPagamento = $Total_Inscritos_NaoIsento_NaoPagamento - $Total_Cadastros_Fornecedores;
echo "<div style='display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:8px; margin-bottom:8px;'>";
echo "<a href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&status=ATIVADA' style='text-decoration:none;'>"
   . "<div style='border:1px solid #e5e7eb; border-radius:10px; padding:10px 14px; background:#fff;'>"
   . "<div style='font-size:12px; color:#666; font-weight:600;'>Cadastrados (Ativados)</div>"
   . "<div style='font-size:22px; font-weight:700; color:#111;'>{$Total_Cadastros_SemFornecedor}</div>"
   . ($Total_Cadastros_Fornecedores > 0
        ? "<div style='font-size:11px; color:#888; margin-top:2px;'>+{$Total_Cadastros_Fornecedores} fornecedor(es), fora da contagem</div>"
        : "")
   . "</div></a>";

    if($objEvConf->getRecebePagamento()){
        $totalConfirmados = $Total_Cadastros_SemFornecedor - $Total_Inscritos_NaoIsento_NaoPagamento;
        $porcentagemPendentes = $Total_Cadastros_SemFornecedor > 0 ? number_format(($Total_Inscritos_NaoIsento_NaoPagamento*100)/$Total_Cadastros_SemFornecedor, 2) : '0.00';
        $corPendentes = ($Total_Inscritos_Efetuaram_Pagamento * 0.1 <= $Total_Inscritos_NaoIsento_NaoPagamento) ? '#dc3545' : '#111';

        echo "<a href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&pagamentos=99' style='text-decoration:none;'>"
           . "<div style='border:1px solid #e5e7eb; border-radius:10px; padding:10px 14px; background:#fff;'>"
           . "<div style='font-size:12px; color:#666; font-weight:600;'>Confirmados</div>"
           . "<div style='font-size:22px; font-weight:700; color:#27ae60;'>{$totalConfirmados}</div>"
           . "<div id='detalheConfirmados' style='font-size:11px; color:#888; margin-top:2px;'>";
        $detalhes = array();
        if ($Total_Inscritos_Efetuaram_Pagamento > 0) $detalhes[] = "Pagos: {$Total_Inscritos_Efetuaram_Pagamento}";
        if ($Total_Inscritos_Vouchers > 0) $detalhes[] = "Vouchers: {$Total_Inscritos_Vouchers}";
        if ($Total_Inscritos_Isentos_Cadastrados > 0) $detalhes[] = "Isentos: {$Total_Inscritos_Isentos_Cadastrados}";
        if ($Total_Inscritos_Isentos_Desconto_100 > 0) $detalhes[] = "Desc.100%: {$Total_Inscritos_Isentos_Desconto_100}";
        if ($Total_Inscritos_Isentos_Perfil > 0) $detalhes[] = "Perfil isento: {$Total_Inscritos_Isentos_Perfil}";
        echo implode(' | ', $detalhes);
        echo "<span id='slotEmpenho'></span>"
           . "</div>"
           . "</div></a>";

        echo "<span id='slotNaoPagos'></span>";
    }
    echo "</div>";
    #------------------------------------------------------
/* ================================
   TOTAIS DE ISENÇÃO (2 números)
   ================================ */

/* 1) Inscritos ATIVADOS isentos (todas as origens) */
$sql_total_isentos_inscritos = "
SELECT COUNT(DISTINCT z.cod_inscrito) AS total_isentos_inscritos
FROM (
  /* Voucher 100% vinculado */
  SELECT liv.cod_inscrito
  FROM tab_lig_inscritos_vouchers liv
  JOIN tab_vouchers v   ON v.cod_voucher = liv.cod_voucher AND v.porcentagem = 100
  JOIN tab_inscritos ti ON ti.cod_inscrito = liv.cod_inscrito
                       AND ti.cod_evento   = ".COD_EVENTO."
                       AND ti.status       = 'ATIVADA'
  WHERE liv.cod_evento = ".COD_EVENTO."

  UNION
  /* Isento por código/indicação (vinculado) */
  SELECT tis.cod_inscrito
  FROM tab_isentos_codigo tis
  JOIN tab_inscritos ti ON ti.cod_inscrito = tis.cod_inscrito
                       AND ti.cod_evento   = ".COD_EVENTO."
                       AND ti.status       = 'ATIVADA'
  WHERE tis.cod_evento = ".COD_EVENTO."

  UNION
  /* Perfil isento direto */
  SELECT ti.cod_inscrito
  FROM tab_inscritos ti
  WHERE ti.cod_evento = ".COD_EVENTO."
    AND ti.status     = 'ATIVADA'
    AND ti.cod_perfil IN (SELECT cod_perfil FROM tab_opcoes_perfis WHERE perfil_isento=1)

  UNION
  /* Perfil isento via ligação */
  SELECT tlip.cod_inscrito
  FROM tab_lig_inscritos_perfis tlip
  JOIN tab_inscritos ti ON ti.cod_inscrito = tlip.cod_inscrito
                       AND ti.cod_evento   = ".COD_EVENTO."
                       AND ti.status       = 'ATIVADA'
  WHERE tlip.cod_perfil IN (SELECT cod_perfil FROM tab_opcoes_perfis WHERE perfil_isento=1)

  UNION
  /* Desconto 100% (CPF) aplicados a inscritos ATIVADOS */
  SELECT ti.cod_inscrito
  FROM tab_inscritos ti
  WHERE ti.cod_evento = ".COD_EVENTO."
    AND ti.status     = 'ATIVADA'
    AND ti.cpf IN (
      SELECT REPLACE(REPLACE(cpf,'.',''),'-','')
      FROM tab_descontos
      WHERE cod_evento = ".COD_EVENTO." AND porcentagem_desconto = 100
    )

  UNION
  /* Flag de isento no próprio inscrito */
  SELECT ti.cod_inscrito
  FROM tab_inscritos ti
  WHERE ti.cod_evento = ".COD_EVENTO."
    AND ti.status     = 'ATIVADA'
    AND ti.cod_isento IS NOT NULL AND ti.cod_isento <> ''
) z
";

/* 2) Direito à isenção (mesmo sem cadastro):
      - chave INSCRITO:<cod_inscrito> (origens acima)
      - chave CPF:<cpf> da tab_descontos 100%
      - OPCIONAL: chave VOUCHER:<codigo> 100% não vinculado (se seu schema permitir)
*/
$sql_total_direito_isencao = "
SELECT COUNT(DISTINCT e.chave) AS total_direito_isencao
FROM (
  /* 2.1) Todos os inscritos ATIVADOS isentos (mesmo conjunto da query 1) */
  SELECT CONCAT('INSCRITO:', z.cod_inscrito) AS chave
  FROM (
    SELECT liv.cod_inscrito
    FROM tab_lig_inscritos_vouchers liv
    JOIN tab_vouchers v   ON v.cod_voucher = liv.cod_voucher AND v.porcentagem = 100
    JOIN tab_inscritos ti ON ti.cod_inscrito = liv.cod_inscrito
                         AND ti.cod_evento   = ".COD_EVENTO."
                         AND ti.status       = 'ATIVADA'
    WHERE liv.cod_evento = ".COD_EVENTO."
    UNION
    SELECT tis.cod_inscrito
    FROM tab_isentos_codigo tis
    JOIN tab_inscritos ti ON ti.cod_inscrito = tis.cod_inscrito
                         AND ti.cod_evento   = ".COD_EVENTO."
                         AND ti.status       = 'ATIVADA'
    WHERE tis.cod_evento = ".COD_EVENTO."
    UNION
    SELECT ti.cod_inscrito
    FROM tab_inscritos ti
    WHERE ti.cod_evento = ".COD_EVENTO."
      AND ti.status     = 'ATIVADA'
      AND ti.cod_perfil IN (SELECT cod_perfil FROM tab_opcoes_perfis WHERE perfil_isento=1)
    UNION
    SELECT tlip.cod_inscrito
    FROM tab_lig_inscritos_perfis tlip
    JOIN tab_inscritos ti ON ti.cod_inscrito = tlip.cod_inscrito
                         AND ti.cod_evento   = ".COD_EVENTO."
                         AND ti.status       = 'ATIVADA'
    WHERE tlip.cod_perfil IN (SELECT cod_perfil FROM tab_opcoes_perfis WHERE perfil_isento=1)
    UNION
    SELECT ti.cod_inscrito
    FROM tab_inscritos ti
    WHERE ti.cod_evento = ".COD_EVENTO."
      AND ti.status     = 'ATIVADA'
      AND ti.cpf IN (
        SELECT REPLACE(REPLACE(cpf,'.',''),'-','')
        FROM tab_descontos
        WHERE cod_evento = ".COD_EVENTO." AND porcentagem_desconto = 100
      )
    UNION
    SELECT ti.cod_inscrito
    FROM tab_inscritos ti
    WHERE ti.cod_evento = ".COD_EVENTO."
      AND ti.status     = 'ATIVADA'
      AND ti.cod_isento IS NOT NULL AND ti.cod_isento <> ''
  ) z

  UNION ALL

  /* 2.2) CPFs com desconto 100% (pode não existir inscrição ainda) */
  SELECT CONCAT('CPF:', REPLACE(REPLACE(d.cpf,'.',''),'-','')) AS chave
  FROM tab_descontos d
  WHERE d.cod_evento = ".COD_EVENTO."
    AND d.porcentagem_desconto = 100

  /* 2.3) OPCIONAL — vouchers 100% não vinculados
         Descomente/adapte se existir tabela/colunas de códigos e controle de uso:
  UNION ALL
  SELECT CONCAT('VOUCHER:', vc.codigo) AS chave
  FROM tab_voucher_codigos vc
  JOIN tab_vouchers v ON v.cod_voucher = vc.cod_voucher AND v.porcentagem = 100
  WHERE vc.cod_evento = ".COD_EVENTO."
    AND vc.usado = 0
  */
) e
";

/* ===== Executa e lê os totais ===== */
// echo $sql_total_isentos_inscritos;
// $RS_isentos = $Conexao_db->Execute($sql_total_isentos_inscritos);
// $RS_dir     = $Conexao_db->Execute($sql_total_direito_isencao);

// $total_isentos_inscritos = ($RS_isentos && !$RS_isentos->EOF) ? (int)$RS_isentos->fields[0] : 0;
// $total_direito_isencao   = ($RS_dir && !$RS_dir->EOF)         ? (int)$RS_dir->fields[0]     : 0;

/* ===== Exemplo de uso ===== */
// echo "<div class='cards'>";
// echo "  <div class='card'><h3>Isentos inscritos (ATIVADOS)</h3><div class='v'>".number_format($total_isentos_inscritos,0,',','.')."</div></div>";
// echo "  <div class='card'><h3>Direito à isenção (cpfs/vouchers+inscritos)</h3><div class='v'>".number_format($total_direito_isencao,0,',','.')."</div></div>";
// echo "</div>";

    #------------------------------------------------------
    if ($objEvConf->getTrabSubmissao()) {
        //  echo " <tr><td><b>Total de Membros na Comissão Organizadora:</b> 0</td></tr>";
        //  echo "<tr><td><b>Total de Membros na Comissão Ciêntifica:  </b> 0</td></tr>";
        //  echo "<tr><td><b>Total ".$objEvConf->getTrabAnexoTipo()."/Documentos Enviados:</b>  {$Total_Trabalhos_Enviados} </td></tr>";
         echo "<tr><td><a class='link' href='index.php?content=view/admin/consulta-dados-congresso-graficos-trabalhos.php'>Detalhes dos ".$objEvConf->getTrabAnexoTipo()."  enviados</a></td></tr>";
     }
     
     echo " 
    <!--        
    <tr><td>
    Obs: 
    <span style='font-size:11px;'>
    <ul>    
    <li>Alguns inscritos podem ser inseridos associados a algun beneficio após gerar boleto ou pago sua inscrição por boleto;</li>
    <li>Instituições podem se inscrever e receber boleto para pagamento de inscrições em lote,
        quando isso ocorrer sugere-se que se emitam vouchers para que essas empressas distribuam esse vouchers a seus beneficiarios
        ou solicitar nome,cpf e email para cadastro no sistema de isenção;</li>
    </td></tr>
-->
</table>
";
#------------------------------------------------------
if ($objEvConf->getTrabSubmissao()) {
    $var_sql = "SELECT e.cod_evento, total_apresentacao, total_videos FROM `tab_eventos` e
    LEFT JOIN (SELECT COUNT(v.cod_arquivo_video)         AS total_videos       , v.cod_evento FROM tab_arquivo_video v         WHERE v.status='ATIVADA'  GROUP BY v.cod_evento)  AS v  ON v.cod_evento=e.cod_evento
    LEFT JOIN (SELECT COUNT(ap.cod_arquivo_apresentacao) AS total_apresentacao , ap.cod_evento FROM tab_arquivo_apresentacao ap WHERE ap.status='ATIVADA' GROUP BY ap.cod_evento) AS ap ON ap.cod_evento=e.cod_evento
    LEFT JOIN (SELECT COUNT(t.cod_trabalho) qtd_trabalhos_presentes, t.cod_evento FROM tab_trabalhos t WHERE t.flg_presenca IS NOT NULL) AS t ON t.cod_evento=e.cod_evento
    WHERE e.cod_evento=".COD_EVENTO;
    $RS = $Conexao_db->Execute($var_sql);
    If (!$RS->EOF) {
        While (!$RS->EOF) {
            $qtdArquivosApresentacaoEnviados = $RS->fields['total_apresentacao'];
            $qtdArquivosVideosEnviados       = $RS->fields['total_videos'];
            $qtdTrabalhosPresentes       = $RS->fields['qtd_trabalhos_presentes'];
            $RS->MoveNext();
        }
        if($objEvConf->getApresentacaoAnexar()){
            echo "<br><b>Quantidade de Posters enviados: </b>";
            echo $qtdArquivosApresentacaoEnviados;
        }
        if($objEvConf->getApresentacaoAnexarVideo()){
            echo "<br><b>Quantidade de Vídeos enviados: </b>";
            echo $qtdArquivosVideosEnviados;
        }
        if($qtdTrabalhosPresentes>0){
            echo "<br><b>Quantidade de Presença registrada: </b>";
            echo $qtdTrabalhosPresentes."<br>";
        }
    }
}
#-------------------------------------------------------------------------
$eventoRecebePagamento = $objEvConf->getRecebePagamento();
$temPermissaoFinanceiro = (@FWA_Autenticacao($_SESSION['_cod_usuario'], enumSistema::SIS_CONTROLE_FINANCEIRO, 5, $cod_msg, false) == TRUE);
// Painel "Por perfil": aparece para TODO MUNDO que já chegou no Painel de Bordo.
// A quebra por perfil (quantos inscritos, quantos pagaram, quantos não) é
// informação operacional — o coordenador precisa dela para tocar o evento, e ela
// faz sentido inclusive em evento gratuito.
// O que exige permissão financeira é o DINHEIRO (valor arrecadado), controlado
// separadamente por $mostrarValoresFinanceiros logo abaixo.
$mostrarPainelPerfil = true;
$mostrarValoresFinanceiros = $temPermissaoFinanceiro;
if($mostrarPainelPerfil){

    // Vouchers por origem - carrega dados para exibir abaixo dos gráficos
    $listaVouchersOrigem = array();
    try {
        $objVoucher = new Voucher();
        $objVoucher->carregarListaPatrocinadoresVoucherByIdEvento($debug);
        if ($objVoucher->getListaPatrocinadoresVoucherByIdEvento()!=null) {
            $listaVouchersOrigem = $objVoucher->getListaPatrocinadoresVoucherByIdEvento();
        }
    } catch (Exception $exc) {
    }
    
    #------------------------------------------------------
    ## Consulta Inscritos por Perfil##
    if($naoMostrar){ 
        $var_sql = "
        SELECT cod_evento, cod_perfil, titulo_perfil,cod_taxa, titulo_taxa
        , COUNT(cod_taxa) AS total_inscritos 
        , SUM(tx_unica_a_vista_valor) AS valor_total
        , tx_unica_a_vista_valor as valor_unitario 
        FROM vw_pagamentos_inscricao WHERE cod_evento   = " . COD_EVENTO . "
        GROUP BY cod_taxa ORDER BY cod_perfil, titulo_taxa, cod_taxa, tx_unica_a_vista_valor
            ";

        $RS = $Conexao_db->Execute($var_sql);
        If (!$RS->EOF) {
            echo "<br><table cellspacing=4 cellpadding=4 border=0>
                    <tr><td colspan='5'><b>Totais arrecadados somente com a taxa de inscrição Perfil/Período:</b><br></td></tr>
                    ";

            #Efetuando loop para o visualização dos registros retornados pela consulta SQL
            While (!$RS->EOF) {

                #Adicionando nas variaveis, os valores dos campos retirados dos registros
                $cod_evento    = $RS->fields[0];
                $cod_perfil    = $RS->fields[1];
                $titulo_perfil = $RS->fields[2];
                $cod_taxa      = $RS->fields[3];
                $titulo_taxa   = $RS->fields[4];
                $inscritos_perfil = $RS->fields[5];
                $valor_total_perfil  = $RS->fields[6];
                $valor_unitario = $RS->fields[7];
                if(COD_EVENTO==2){
                    $valor_total_perfil  = $inscritos_perfil*200.00;
                    $valor_unitario = 200.00;           
                }

                if ($titulo_taxa != $titulo_taxa_ant) {
                    if ($valor_perfis != '') {
                    echo "<tr valign='top'><td colspan=5 style='color:green;'>Núm.pagantes: " . $inscritos_perfis . "
                        , valor arrecadado: R$ " . formatMoneyView($valor_perfis. "00")."<br>&nbsp;
                        </td></tr>";    
                    $valor_perfis = 0;  
                    $inscritos_perfis = 0;
                    }
                    echo "<tr><td colspan='5'><b>Período de arrecadação " . $titulo_taxa . "</b></td></tr>";
                    echo "<tr valign='top'>
                        <td width='100px' align='right'>Insc.Pagos</td>
                        <td width='100px' align='right'>Vlr. Unitário  </td>
                        <td width='100px' align='right'>Vlr. Arrecadado </td>
                        <td width='10px' align='right'>&nbsp</td>
                        <td width='300px' align='left'>Nome do Perfil  </td>
                    </tr>
                    ";
                }
                $titulo_taxa_ant = $titulo_taxa;

                echo "<tr valign='top'>
                    <td align='right'> " . $inscritos_perfil . "</td>
                    <td align='right'> " . formatMoneyView($valor_unitario.'00') . "</td>
                    <td align='right'> " . formatMoneyView($valor_total_perfil.'00')  . "</td>
                    <td align='right'>&nbsp</td>
                    <td align='left'> " . $titulo_perfil . "</td>
                    </tr>
                    ";

                $inscritos_perfis = $inscritos_perfis + $inscritos_perfil;
                $valor_perfis     = $valor_perfis + $valor_total_perfil;
            
                $valor_geral = $valor_geral+$valor_total_perfil;  
                $inscritos_geral = $inscritos_geral+$inscritos_perfil;

                #Movendo para o proximo registro
                $RS->MoveNext();
            }
            echo "<tr valign='top'>
                    <td colspan=5 style='color:green;'>Núm.pagantes: " . $inscritos_perfis . "
                    , valor arrecadado: R$ " . formatMoneyView($valor_perfis. "00")."<br>
                    <span style='font-size:11px;color:red'>obs: O valor acima é aproximado, não leva em consideração, descontos e isentos</span>
                    </td>
                </tr>";    
        //    echo "<tr valign='top'>
        //            <td colspan=5>
        //            Total Geral núm.pagantes.: " . $inscritos_geral . "
        //            , valor arrecadado: " . $valor_geral . "
        //            </td>
        //          </tr>
        //         ";
            echo "</table><hr>";
        }#naoMostrar
    }
    #------------------------------------------------------
    ## Consulta Inscritos por Perfil##
    if($mostrarPainelPerfil){

/* -------- 1) SQL: KPIs por perfil (base para cards) -------- */
// $sql_perfil = "
// SELECT
//   ti.cod_perfil,
//   top.titulo,
//   COUNT(DISTINCT ti.cod_inscrito) AS qt_total,
//   COUNT(DISTINCT CASE
//     WHEN (pg.status='ATIVADA' OR pg.status IS NULL)
//      AND ( UPPER(TRIM(pg.forma_pagamento))='EMPENHO' OR pg.data_pagamento IS NOT NULL )
//     THEN ti.cod_inscrito END) AS qt_pagos,
//   (COUNT(DISTINCT ti.cod_inscrito) -
//    COUNT(DISTINCT CASE
//     WHEN (pg.status='ATIVADA' OR pg.status IS NULL)
//      AND ( UPPER(TRIM(pg.forma_pagamento))='EMPENHO' OR pg.data_pagamento IS NOT NULL )
//     THEN ti.cod_inscrito END)) AS qt_nao_pagos,
//   SUM(CASE
//     WHEN (pg.status='ATIVADA' OR pg.status IS NULL)
//      AND ( UPPER(TRIM(pg.forma_pagamento))='EMPENHO' OR pg.data_pagamento IS NOT NULL )
//     THEN IFNULL(pg.valor,0) ELSE 0 END) AS valor_arrecadado
//     -- aqui entra os isentos e vouchers 100% e isentos por perfil e 
// FROM tab_inscritos ti
// JOIN tab_opcoes_perfis top ON top.cod_perfil = ti.cod_perfil
// LEFT JOIN tab_pagamentos pg ON pg.cod_inscrito = ti.cod_inscrito
// WHERE ti.cod_evento = ".COD_EVENTO."
//   AND ti.status = 'ATIVADA'
// GROUP BY ti.cod_perfil, top.titulo
// ORDER BY top.titulo
// ";
 $sql_perfil = "SELECT
  ti.cod_perfil,
  top.titulo,
  COUNT(DISTINCT ti.cod_inscrito) AS qt_total,
  COUNT(DISTINCT CASE
    WHEN (pg.status='ATIVADA' OR pg.status IS NULL)
      AND ( UPPER(TRIM(pg.forma_pagamento))='EMPENHO' OR pg.data_pagamento IS NOT NULL )
    THEN ti.cod_inscrito END) AS qt_pagos,
  (COUNT(DISTINCT ti.cod_inscrito) -
    COUNT(DISTINCT CASE
    WHEN (pg.status='ATIVADA' OR pg.status IS NULL)
      AND ( UPPER(TRIM(pg.forma_pagamento))='EMPENHO' OR pg.data_pagamento IS NOT NULL )
    THEN ti.cod_inscrito END)) AS qt_nao_pagos,
  SUM(CASE
    WHEN (pg.status='ATIVADA' OR pg.status IS NULL)
      AND ( UPPER(TRIM(pg.forma_pagamento))='EMPENHO' OR pg.data_pagamento IS NOT NULL )
    THEN IFNULL(pg.valor,0) ELSE 0 END) AS valor_arrecadado,
  /*-- Nova coluna com a contagem de isentos*/
  COUNT(DISTINCT i.cod_inscrito) AS qt_isentos
FROM tab_inscritos ti
JOIN tab_opcoes_perfis top ON top.cod_perfil = ti.cod_perfil
LEFT JOIN tab_pagamentos pg ON pg.cod_inscrito = ti.cod_inscrito
/* LEFT JOIN com a Subconsulta (funciona no MySQL 5.7)*/
LEFT JOIN (
    /* Lógica de Isenção Consolidada */
    SELECT DISTINCT liv.cod_inscrito
    FROM tab_lig_inscritos_vouchers liv
    INNER JOIN tab_vouchers v ON v.cod_voucher=liv.cod_voucher AND v.porcentagem=100
    INNER JOIN tab_inscritos ti_v ON ti_v.cod_inscrito = liv.cod_inscrito
           AND ti_v.cod_evento   = ".COD_EVENTO."
           AND ti_v.status       = 'ATIVADA'
    WHERE liv.cod_evento = ".COD_EVENTO."

    UNION

    SELECT DISTINCT tis.cod_inscrito
    FROM tab_isentos_codigo tis
    INNER JOIN tab_inscritos ti_isc ON ti_isc.cod_inscrito = tis.cod_inscrito
           AND ti_isc.cod_evento   = ".COD_EVENTO."
           AND ti_isc.status       = 'ATIVADA'
    WHERE tis.cod_evento = ".COD_EVENTO."

    UNION

    SELECT DISTINCT ti_perfil.cod_inscrito
    FROM tab_inscritos ti_perfil
    WHERE ti_perfil.cod_evento = ".COD_EVENTO." AND ti_perfil.status='ATIVADA'
      AND ti_perfil.cod_perfil IN (SELECT cod_perfil FROM tab_opcoes_perfis WHERE perfil_isento=1)

    UNION

    SELECT DISTINCT tlip.cod_inscrito
    FROM tab_lig_inscritos_perfis tlip
    INNER JOIN tab_inscritos ti_lip ON ti_lip.cod_inscrito = tlip.cod_inscrito
           AND ti_lip.cod_evento   = ".COD_EVENTO."
           AND ti_lip.status       = 'ATIVADA'
    WHERE tlip.cod_evento = ".COD_EVENTO." AND tlip.cod_perfil IN (SELECT cod_perfil FROM tab_opcoes_perfis WHERE perfil_isento=1)

    UNION

    SELECT DISTINCT ti_cpf.cod_inscrito
    FROM tab_inscritos ti_cpf
    WHERE ti_cpf.cod_evento = ".COD_EVENTO." AND ti_cpf.status='ATIVADA'
      AND ti_cpf.cpf IN (
        SELECT REPLACE(REPLACE(cpf,'.',''),'-','')
        FROM tab_descontos
        WHERE cod_evento = ".COD_EVENTO." AND porcentagem_desconto = 100
      )
) AS i ON i.cod_inscrito = ti.cod_inscrito
WHERE ti.cod_evento = ".COD_EVENTO."
  AND ti.status = 'ATIVADA'
GROUP BY ti.cod_perfil, top.titulo
ORDER BY top.titulo";
// echo $sql_perfil;
/* -------- 2) SQL: por forma de pagamento (bucketizado) -------- */
/* -------- Por forma de pagamento (inclui ISENTO) -------- */
$sql_forma = "
SELECT
  t.forma_bucket,
  SUM(t.qtd_pagamentos) AS qtd_pagamentos,
  SUM(t.valor_total)    AS valor_total
FROM (
  /* Pagamentos 'normais' (mantido) */
  SELECT
    CASE
      WHEN UPPER(TRIM(pg.forma_pagamento)) LIKE 'BOLETO%' THEN 'BOLETO'
      WHEN UPPER(TRIM(pg.forma_pagamento)) IN ('EMPENHO') THEN 'EMPENHO'
      WHEN UPPER(TRIM(pg.forma_pagamento)) LIKE 'DEPOSITO%' OR UPPER(TRIM(pg.forma_pagamento)) LIKE 'DEPÓSITO%' THEN 'DEPOSITO'
      WHEN UPPER(TRIM(pg.forma_pagamento)) IN ('PIX') THEN 'PIX'
      WHEN UPPER(TRIM(pg.forma_pagamento)) LIKE 'CART%' THEN 'CARTAO'
      WHEN UPPER(TRIM(pg.forma_pagamento)) IN ('OUTRAS','OUTROS') THEN 'OUTRAS'
      WHEN UPPER(TRIM(pg.forma_pagamento)) IN ('ISENTO','ISENCAO','ISENÇÃO') THEN 'ISENTO'
      ELSE 'OUTRO'
    END AS forma_bucket,
    COUNT(*) AS qtd_pagamentos,
    SUM(IFNULL(pg.valor,0)) AS valor_total
  FROM tab_pagamentos pg
  JOIN tab_inscritos ti
    ON ti.cod_inscrito = pg.cod_inscrito
   AND ti.cod_evento   = ".COD_EVENTO."
   AND ti.status       = 'ATIVADA'
  WHERE ti.cod_evento = ".COD_EVENTO." 
    AND (pg.status='ATIVADA' OR pg.status IS NULL)
    AND (UPPER(TRIM(pg.forma_pagamento))='EMPENHO' OR pg.data_pagamento IS NOT NULL)
  GROUP BY forma_bucket

  UNION ALL

  /* ISENTOS: contam como 1, valor=0 (todas as origens de isenção) */
  SELECT
    'ISENTO' AS forma_bucket,
    COUNT(*) AS qtd_pagamentos,
    0.0      AS valor_total
  FROM (
      /* Voucher 100% */
      SELECT DISTINCT liv.cod_inscrito
      FROM tab_lig_inscritos_vouchers liv
      INNER JOIN tab_vouchers v ON v.cod_voucher=liv.cod_voucher AND v.porcentagem=100
      INNER JOIN tab_inscritos ti
              ON ti.cod_inscrito = liv.cod_inscrito
             AND ti.cod_evento   = ".COD_EVENTO."
             AND ti.status       = 'ATIVADA'
      WHERE liv.cod_evento = ".COD_EVENTO."

      UNION
      /* Indicação/código de isento */
      SELECT DISTINCT tis.cod_inscrito
      FROM tab_isentos_codigo tis
      INNER JOIN tab_inscritos ti
              ON ti.cod_inscrito = tis.cod_inscrito
             AND ti.cod_evento   = ".COD_EVENTO."
             AND ti.status       = 'ATIVADA'      
      WHERE tis.cod_evento = ".COD_EVENTO."

      UNION
      /* Perfil isento cadastrado no próprio perfil */
      SELECT DISTINCT ti.cod_inscrito
      FROM tab_inscritos ti
      WHERE ti.cod_evento = ".COD_EVENTO." AND ti.status='ATIVADA'
        AND ti.cod_perfil IN (SELECT cod_perfil FROM tab_opcoes_perfis WHERE perfil_isento=1)

      UNION
      /* Perfil isento via ligação de perfis */
      SELECT DISTINCT tlip.cod_inscrito
      FROM tab_lig_inscritos_perfis tlip
      WHERE tlip.cod_evento = ".COD_EVENTO." AND tlip.cod_perfil IN (SELECT cod_perfil FROM tab_opcoes_perfis WHERE perfil_isento=1)

      UNION
      /* Descontos 100% por campanha (CPF) */
      SELECT DISTINCT ti.cod_inscrito
      FROM tab_inscritos ti
      WHERE ti.cod_evento = ".COD_EVENTO." AND ti.status='ATIVADA'
        AND ti.cpf IN (
          SELECT REPLACE(REPLACE(cpf,'.',''),'-','')
          FROM tab_descontos
          WHERE cod_evento = ".COD_EVENTO." AND porcentagem_desconto = 100
        )
  ) iz
) t
GROUP BY t.forma_bucket
ORDER BY valor_total DESC
";


/* -------- 3) SQL: PAGAMENTOS por dia (usa data_pagamento) -------- */
$sql_diario = "
SELECT
  DATE(pg.data_pagamento) AS dia,
  COUNT(*) AS qtd,
  SUM(IFNULL(pg.valor,0)) AS valor
FROM tab_pagamentos pg
JOIN tab_inscritos ti ON ti.cod_inscrito = pg.cod_inscrito
WHERE ti.cod_evento = ".COD_EVENTO."
  AND (pg.status='ATIVADA' OR pg.status IS NULL)
  AND pg.data_pagamento IS NOT NULL
GROUP BY DATE(pg.data_pagamento)
ORDER BY DATE(pg.data_pagamento)
";

/* -------- 4) SQL: INSCRITOS por dia (para comparar no mesmo gráfico) -------- */
$sql_inscritos_dia = "
SELECT
  dia,
  total_inscritos
FROM vw_inscritos_por_dia_evento
WHERE cod_evento = ".COD_EVENTO."
ORDER BY dia
";

/* ===== Execuções ===== */
/* $sql_perfil sempre roda (alimenta a tabela "Por perfil", útil a qualquer evento).
   Demais queries só interessam aos gráficos/tabelas de pagamento — evita queries
   pesadas (e views só de produção) em eventos sem inscrição paga. */
$RS1 = $Conexao_db->Execute($sql_perfil);
$RS2 = $eventoRecebePagamento ? $Conexao_db->Execute($sql_forma)         : null;
$RS3 = $eventoRecebePagamento ? $Conexao_db->Execute($sql_diario)        : null;
$RS4 = $eventoRecebePagamento ? $Conexao_db->Execute($sql_inscritos_dia) : null;

/* ===== Monta estruturas p/ o front ===== */
$cards = [
  'total_inscritos'  => 0,
  'total_pagos'      => 0,
  'total_isentos'    => 0,
  'total_nao_pagos'  => 0,
  'valor_arrecadado' => 0.0
];
$tbl_perfil = [];
// dd($RS1);
if ($RS1 && !$RS1->EOF) {
  while (!$RS1->EOF) {
    $cod_perfil   = (int)$RS1->fields[0];
    $titulo       = $RS1->fields[1];
    $qt_total     = (int)$RS1->fields[2];
    $qt_pagos     = (int)$RS1->fields[3];
    $qt_nao_pagos = (int)$RS1->fields[4];
    $valor        = (float)$RS1->fields[5];
    $qt_isentos        = (int)$RS1->fields[6];
    $qt_nao_pagos = $qt_nao_pagos-$qt_isentos;

    $cards['total_inscritos']  += $qt_total;
    $cards['total_pagos']      += $qt_pagos;
    $cards['total_isentos']    += $qt_isentos;
    $cards['total_nao_pagos']  += $qt_nao_pagos;
    $cards['valor_arrecadado'] += $valor;

    $tbl_perfil[] = [
      'cod_perfil'  => $cod_perfil,
      'titulo'      => $titulo,
      'qt_total'    => $qt_total,
      'qt_pagos'    => $qt_pagos,
      'qt_isentos'    => $qt_isentos,
      'qt_nao'      => $qt_nao_pagos,
      'valor'       => $valor
    ];
    $RS1->MoveNext();
  }
}

/* Por forma de pagamento */
$formas_labels = []; $formas_qtd = []; $formas_valor = [];
if ($RS2 && !$RS2->EOF) {
  while (!$RS2->EOF) {
    $label = $RS2->fields[0];
    $qtd   = (int)$RS2->fields[1];
    $val   = (float)$RS2->fields[2];

    $formas_labels[] = $label;
    $formas_qtd[]    = $qtd;
    $formas_valor[]  = $val;

    $RS2->MoveNext();
  }
}

/* Pagamentos por dia */
$dias_qtd_map = []; $dias_valor_map = [];
if ($RS3 && !$RS3->EOF) {
  while (!$RS3->EOF) {
    $dia = (string)$RS3->fields[0]; // yyyy-mm-dd
    $qtd = (int)$RS3->fields[1];
    $val = (float)$RS3->fields[2];

    $dias_qtd_map[$dia]   = $qtd;
    $dias_valor_map[$dia] = $val;

    $RS3->MoveNext();
  }
}

/* Inscritos por dia (para comparação) */
$ins_qtd_map = [];
if ($RS4 && !$RS4->EOF) {
  while (!$RS4->EOF) {
    $dia = (string)$RS4->fields[0]; // yyyy-mm-dd
    // $dia = formatDateView($dia);
    $qt  = (int)$RS4->fields[1];
    $ins_qtd_map[$dia] = $qt;
    $RS4->MoveNext();
  }
}

/* Unificar labels (datas) e alinhar séries */
$all_labels = array_keys($dias_qtd_map + $ins_qtd_map); // união por chave
sort($all_labels); // yyyy-mm-dd ordena lexicograficamente correto

$dias_labels = $all_labels;
$dias_qtd    = [];
$dias_valor  = [];
$inscritos_qtd = [];

foreach ($all_labels as $d) {
  $dias_qtd[]      = isset($dias_qtd_map[$d])   ? (int)$dias_qtd_map[$d]   : 0;
  $dias_valor[]    = isset($dias_valor_map[$d]) ? (float)$dias_valor_map[$d] : 0.0;
  $inscritos_qtd[] = isset($ins_qtd_map[$d])    ? (int)$ins_qtd_map[$d]    : 0;
}

/* ===== Render HTML + Charts ===== */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Dashboard Financeiro por Perfil</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body{font-family:Arial, Helvetica, sans-serif; margin:16px;}
  .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-bottom:8px}
  .card{border:1px solid #e5e7eb;border-radius:12px;padding:14px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04)}
  .card h3{margin:0 0 8px;font-size:14px;color:#374151;font-weight:600}
  .card .v{font-size:22px;color:#111827;font-weight:700}
  table{width:100%;border-collapse:collapse;margin-top:12px}
  th,td{padding:8px;border-bottom:1px solid #eee}
  th{text-align:left;background:#f9fafb;font-weight:600}
  td.num{text-align:right}
  .grid{display:grid;grid-template-columns:1fr;gap:16px}
  @media(min-width:980px){.grid{grid-template-columns:1fr 1fr}}
  .panel{border:1px solid #e5e7eb;border-radius:12px;padding:12px;background:#fff;margin-top: 8px;}
  .panel h2{margin:0 0 8px;font-size:16px}
  canvas{max-height:420px}
  .panel-group .panel + .panel { margin-top: 6px; }
  .panel { margin-top: 6px; }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php if($eventoRecebePagamento){ ?>
<div class="cards">
  <!-- <a href="index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&status=ATIVADA" style="text-decoration:none; color:inherit;"><div class="card"><h3>Total de inscritos</h3><div class="v"><?php echo (int)$cards['total_inscritos']; ?></div></div></a> -->
  <a href="index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&pagamentos=99" style="text-decoration:none; color:inherit;"><div class="card"><h3>Pagamentos confirmados</h3><div class="v"><?php echo (int)$cards['total_pagos']; ?></div></div></a>
  <?php $pctNaoPagos = $cards['total_inscritos'] > 0 ? number_format(($cards['total_nao_pagos']*100)/$cards['total_inscritos'], 1) : '0'; ?>
  <a id="cardNaoPagos" href="index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&pagamentos=-1" style="text-decoration:none; color:inherit;"><div class="card" style="border-color:#f5c6cb;"><h3 style="color:#dc3545;">N&atilde;o pagos (<?php echo $pctNaoPagos; ?>%)</h3><div class="v" style="color:#dc3545;"><?php echo (int)$cards['total_nao_pagos']; ?></div></div></a>
  <script>document.getElementById('slotNaoPagos').replaceWith(document.getElementById('cardNaoPagos'));</script>
  <?php /* dinheiro so para quem tem permissao financeira; as contagens acima
           ficam visiveis para quem opera o evento */
  if ($mostrarValoresFinanceiros) { ?>
  <a href="index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&pagamentos=99" style="text-decoration:none; color:inherit;"><div class="card"><h3>Valor arrecadado</h3><div class="v"><?php echo number_format($cards['valor_arrecadado'], 2, ',', '.'); ?></div></div></a>
  <?php } ?>
</div>
<?php } else { ?>
<div class="cards">
  <a href="index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&status=ATIVADA" style="text-decoration:none; color:inherit;"><div class="card"><h3>Total de inscritos</h3><div class="v"><?php echo (int)$cards['total_inscritos']; ?></div></div></a>
  <div class="card"><h3>Isentos</h3><div class="v"><?php echo (int)$cards['total_isentos']; ?></div></div>
</div>
<?php } ?>

<?php if($eventoRecebePagamento){ ?>
<!-- Graficos lado a lado -->
<div class="grid">
  <div class="panel">
    <h2>Por forma de pagamento</h2>
    <canvas id="chartFormas"></canvas>
  </div>
  <div class="panel">
    <h2>Pagamentos por dia (com Inscritos/dia)</h2>
    <canvas id="chartDiario"></canvas>
  </div>
</div>
<?php } ?>

<?php if($eventoRecebePagamento){ ?>
<!-- Tabelas lado a lado -->
<div class="grid" style="margin-top:4px;">
  <div class="panel">
    <h2>Por forma de pagamento</h2>
    <table>
      <thead><tr><th>Forma</th><th class="num" style="text-align:right;">Qtd</th><?php if ($mostrarValoresFinanceiros) { ?><th class="num" style="text-align:right;">Valor</th><?php } ?></tr></thead>
      <tbody>
        <?php
        $totalEmpenho = 0;
        for($i=0;$i<count($formas_labels);$i++){
          if (strtoupper($formas_labels[$i]) == 'EMPENHO') { $totalEmpenho = $formas_qtd[$i]; }
          $formaParam = urlencode($formas_labels[$i]);
          $colValor = $mostrarValoresFinanceiros
              ? "<td class='num'>".number_format($formas_valor[$i], 2, ',', '.')."</td>"
              : "";
          echo "<tr><td><a href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&forma_pagamento={$formaParam}' style='color:#2d6a4f; text-decoration:none;'>".htmlspecialchars($formas_labels[$i])."</a></td><td class='num'>".$formas_qtd[$i]."</td>".$colValor."</tr>";
        }
        if ($totalEmpenho > 0) {
          echo "<script>document.getElementById('slotEmpenho').innerHTML = ' | Empenho: {$totalEmpenho}';</script>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <?php if (!empty($listaVouchersOrigem)) { ?>
  <div class="panel">
    <h2>Vouchers por Origem</h2>
    <table>
      <thead><tr><th>Origem</th><th class="num" style="text-align:right;">Gerados</th><th class="num" style="text-align:right;">Utilizados</th></tr></thead>
      <tbody>
        <?php
        $totalGerados = 0; $totalUtilizadosGeral = 0;
        foreach ($listaVouchersOrigem as $objLP) {
            $utilizados = $objLP->getTotalUtilizado() + $objLP->getUsadosOff();
            if ($objLP->getPatrocinador()=='ROCHE') { $utilizados = 10; }
            $totalGerados += $objLP->getTotalGerado();
            $totalUtilizadosGeral += $utilizados;
            $voucherParam = urlencode($objLP->getPatrocinador());
            echo "<tr><td><a href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&patrocinador_voucher={$voucherParam}' style='color:#2d6a4f; text-decoration:none;'>" . htmlspecialchars($objLP->getPatrocinador()) . "</a></td>"
               . "<td class='num'>" . $objLP->getTotalGerado() . "</td>"
               . "<td class='num'>" . $utilizados . "</td></tr>";
        }
        ?>
      </tbody>
      <tfoot><tr style="font-weight:700; border-top:2px solid #ddd;"><td>Total</td><td class="num"><?php echo $totalGerados; ?></td><td class="num"><?php echo $totalUtilizadosGeral; ?></td></tr></tfoot>
    </table>
  </div>
  <?php } ?>
</div>
<?php } ?>

<div class="panel" style="margin-top:16px">
  <h2>Por perfil</h2>
<table>
    <thead>
      <tr>
        <th>Perfil</th>
        <th class="num" style="text-align: right;">Total</th>
        <?php if($eventoRecebePagamento){ ?><th class="num" style="text-align: right;">Pagos</th><?php } ?>
        <th class="num" style="text-align: right;">Isentos</th>
        <?php if($eventoRecebePagamento){ ?><th class="num" style="text-align: right;">Não pagos</th><th class="num" style="text-align: right;">Arrecadado</th><?php } ?>
      </tr>
    </thead>
    <tbody>
      <?php
      foreach($tbl_perfil as $r){
        $colPagos  = $eventoRecebePagamento ? "<td class='num'>".$r['qt_pagos']."</td>" : "";
        $colNaoArr = $eventoRecebePagamento ? "<td class='num'>".$r['qt_nao']."</td><td class='num'>".$r['valor']."</td>" : "";
        echo "<tr>
          <td><a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_perfil=".$r['cod_perfil']."&cod_evento=".COD_EVENTO."'>".htmlspecialchars($r['titulo'])."</a></td>
          <td class='num'>".$r['qt_total']."</td>
          ".$colPagos."
          <td class='num'>".$r['qt_isentos']."</td>
          ".$colNaoArr."
        </tr>";
      }
      ?>
      <tr style="font-weight:bold;border-top:2px solid #ddd">
        <td style="text-align:right">Totais:</td>
        <td class="num"><?php echo array_sum(array_column($tbl_perfil,'qt_total')); ?></td>
        <?php if($eventoRecebePagamento){ ?><td class="num"><?php echo array_sum(array_column($tbl_perfil,'qt_pagos')); ?></td><?php } ?>
        <td class="num"><?php echo array_sum(array_column($tbl_perfil,'qt_isentos')); ?></td>
        <?php if($eventoRecebePagamento){ ?><td class="num"><?php echo array_sum(array_column($tbl_perfil,'qt_nao')); ?></td><td class="num"><?php echo array_sum(array_column($tbl_perfil,'valor')); ?></td><?php } ?>
      </tr>
    </tbody>
  </table>
</div>

<?php if($eventoRecebePagamento){ ?>
<div class="panel" style="margin-top:16px">
  <h2>Ultimos 10 Pagamentos:</h2>
    <?php
    try {
        $objPagamento = new Pagamento();
        $objPagamento->carregarListaPagamentos10UltimosByIdEvento($debug);
        if ($objPagamento->getListaPagamentos()!=null) {
            // echo "<br><b></b><br>";
            echo "<table>";
            echo "<tr style='font-size:12px;'><th>Nome</th><th>Valor</th><th>Forma</th><th>Data Transação</th></tr>";
            foreach ($objPagamento->getListaPagamentos() as $objLP) {
                echo "<tr style='font-size:12px;'>";
                echo '<td>- '.textoMaiusculoMinusculo($objLP->getNomeInscrito()).'</td>';
                echo '<td>'.formatMoneyView($objLP->getValor()).'</td>';
                echo '<td>'.$objLP->getFormaPagamento().'</td>';
                echo '<td>'.formatDateView($objLP->getDataPagamento()).'</td>';
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $exc) {
    //     $exc->getMessage()."<br>";
    }    
    ?>
</div>  
<script>
const formasLabels = <?php echo json_encode($formas_labels, JSON_UNESCAPED_UNICODE); ?>;
const formasQtd    = <?php echo json_encode($formas_qtd); ?>;
const formasValor  = <?php echo json_encode($formas_valor); ?>;

const diasLabels      = <?php echo json_encode($dias_labels); ?>;
const diasQtd         = <?php echo json_encode($dias_qtd); ?>;          // pagamentos/dia
const diasValor       = <?php echo json_encode($dias_valor); ?>;        // valor/dia
const inscritosPorDia = <?php echo json_encode($inscritos_qtd); ?>;     // inscritos/dia

/* ---- Charts ---- */
new Chart(document.getElementById('chartFormas'), {
  type: 'bar',
  data: {
    labels: formasLabels,
    datasets: [
      { label: 'Quantidade', data: formasQtd },
      { label: 'Valor', data: formasValor, yAxisID: 'y2' }
    ]
  },
  options: {
    responsive: true,
    interaction: { mode: 'index', intersect: false },
    scales: {
      y:  { beginAtZero: true, title: { display: true, text: 'Qtd' } },
      y2: { beginAtZero: true, position: 'right', title: { display: true, text: 'Valor' } }
    }
  }
});

new Chart(document.getElementById('chartDiario'), {
  type: 'line',
  data: {
    labels: diasLabels,
    datasets: [
      { label: 'Qtd/dia (pagamentos)', data: diasQtd, tension: 0.25, borderWidth: 2, pointRadius: 2 },
      { label: 'Inscritos/dia', data: inscritosPorDia, tension: 0.25, borderWidth: 2, pointRadius: 2 },
      { label: 'Valor/dia', data: diasValor, tension: 0.25, yAxisID: 'y2', borderWidth: 2, pointRadius: 2 }
    ]
  },
  options: {
    responsive: true,
    interaction: { mode: 'index', intersect: false },
    scales: {
      y:  { beginAtZero: true, title: { display: true, text: 'Qtd' } },
      y2: { beginAtZero: true, position: 'right', title: { display: true, text: 'Valor' } }
    }
  }
});
</script>
<?php } ?>
</body>
</html>

<?php

    if($naoMostrar){
        $var_sql = "
            SELECT ti.cod_perfil, top.titulo, COUNT(ti.cod_inscrito) AS total_inscricoes_por_perfil
            , tp.total_inscricoes_pagas_por_perfil, tp.valor_inscricoes_pagas_por_perfil, tp.tx_unica_a_vista_valor
            , of.total_inscricoes_pagas_por_oficina
            , emp.total_empenho_pagas_por_perfil, emp.valor_empenho_pagas_por_perfil, emp.valor_empenho
            , outras.total_outras_por_perfil, outras.valor_outras_por_perfil, outras.valor_outras
            , dcc.total_depositos_cc_por_perfil, dcc.valor_depositos_cc_por_perfil, dcc.valor_depositos_cc
            FROM tab_inscritos ti
            INNER JOIN tab_opcoes_perfis top ON top.cod_perfil=ti.cod_perfil
            left JOIN (SELECT COUNT(ti.cod_inscrito) AS total_inscricoes_pagas_por_perfil, ti.cod_perfil, top.titulo
            , SUM(tot.tx_unica_a_vista_valor) AS valor_inscricoes_pagas_por_perfil
                        ,tot.tx_unica_a_vista_valor
                        FROM tab_inscritos ti
                        INNER JOIN tab_opcoes_perfis top ON top.cod_perfil=ti.cod_perfil
                        INNER JOIN tab_pagamentos tp ON ti.cod_inscrito=tp.cod_inscrito and 
                        (tp.forma_pagamento='BOLETO BANCÁRIO' AND tp.forma_pagamento<>'BOLETO BANCÁRIO UNIFICADO')
                        INNER JOIN tab_opcoes_taxas tot ON tot.cod_perfil=top.cod_perfil AND (tp.data_pagamento BETWEEN tot.periodo_inicial AND tot.periodo_final) AND tot.status='ATIVADA'
                        WHERE (ti.cod_evento   = " . COD_EVENTO . ")
                            AND (ti.status         = 'ATIVADA')
                        GROUP BY  ti.cod_perfil) AS tp ON tp.cod_perfil = ti.cod_perfil
            LEFT JOIN (SELECT COUNT(ti.cod_perfil) AS total_inscricoes_pagas_por_oficina, tlio.cod_evento, ti.cod_perfil
                            FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
                            WHERE
                                (ti.cod_evento   = " . COD_EVENTO . ")
                            AND (tlio.cod_evento   = ti.cod_evento)
                            AND (tlio.cod_inscrito = ti.cod_inscrito)
                            AND (ti.status         = 'ATIVADA')
                            AND (ti.cod_inscrito IN (SELECT DISTINCT(tp.cod_inscrito) FROM tab_pagamentos tp 
                                                        WHERE tp.status = 'ATIVADA' OR tp.status IS NULL)
                                )
                            GROUP BY  ti.cod_perfil, tlio.cod_evento) AS of ON of.cod_perfil = ti.cod_perfil
            LEFT JOIN (SELECT COUNT(ti.cod_inscrito) AS total_empenho_pagas_por_perfil, ti.cod_perfil, top.titulo
            , SUM(tp.valor) AS valor_empenho_pagas_por_perfil,tp.valor AS valor_empenho
                        FROM tab_inscritos ti
                        INNER JOIN tab_opcoes_perfis top ON top.cod_perfil=ti.cod_perfil
                        INNER JOIN tab_pagamentos tp ON ti.cod_inscrito=tp.cod_inscrito AND tp.forma_pagamento='EMPENHO'
                        WHERE (ti.cod_evento   = " . COD_EVENTO . ")
                            AND (ti.status         = 'ATIVADA')
                        GROUP BY  ti.cod_perfil) AS emp ON emp.cod_perfil = ti.cod_perfil
            LEFT JOIN (SELECT COUNT(ti.cod_inscrito) AS total_outras_por_perfil, ti.cod_perfil, top.titulo
            , SUM(tp.valor) AS valor_outras_por_perfil,tp.valor AS valor_outras
                        FROM tab_inscritos ti
                        INNER JOIN tab_opcoes_perfis top ON top.cod_perfil=ti.cod_perfil
                        INNER JOIN tab_pagamentos tp ON ti.cod_inscrito=tp.cod_inscrito AND tp.forma_pagamento='Outras'
                        WHERE (ti.cod_evento   = " . COD_EVENTO . ")
                            AND (ti.status         = 'ATIVADA')
                        GROUP BY  ti.cod_perfil) AS outras ON outras.cod_perfil = ti.cod_perfil
            LEFT JOIN (SELECT COUNT(ti.cod_inscrito) AS total_depositos_cc_por_perfil, ti.cod_perfil, top.titulo
            , SUM(tp.valor) AS valor_depositos_cc_por_perfil,tp.valor AS valor_depositos_cc
                        FROM tab_inscritos ti
                        INNER JOIN tab_opcoes_perfis top ON top.cod_perfil=ti.cod_perfil
                        INNER JOIN tab_pagamentos tp ON ti.cod_inscrito=tp.cod_inscrito AND tp.forma_pagamento='DEPOSITO CONTA CORRENTE'
                        WHERE (ti.cod_evento   = " . COD_EVENTO . ")
                            AND (ti.status         = 'ATIVADA')
                        GROUP BY  ti.cod_perfil) AS dcc ON dcc.cod_perfil = ti.cod_perfil
            WHERE (ti.cod_evento   = " . COD_EVENTO . ")AND (ti.status         = 'ATIVADA')
            GROUP BY  ti.cod_perfil            
        ";

        $RS = $Conexao_db->Execute($var_sql);

        If (!$RS->EOF) {
            /*
            echo "<br><table cellspacing=4 cellpadding=4 border=0>
                    <tr><td colspan='3'><b>Totais arrecadados por perfis: (sendo revisado)</b></td></tr>
                    ";
            */
            $naoMostrar = true;
                
            echo "<tr valign='top'>
                        <td width='100px' align='right'>Total Inscritos     &nbsp;</td>
                        <td width='100px' align='right'>Num. Empenho    &nbsp;</td>
                        <td width='100px' align='right'>Valor Empenho    &nbsp;</td>
                        <td width='100px' align='right'>Num. Outros    &nbsp;</td>
                        <td width='100px' align='right'>Valor Outros   &nbsp;</td>
                        <td width='100px' align='right'>Num. Dep.CC    &nbsp;</td>
                        <td width='100px' align='right'>Valor Dep.CC    &nbsp;</td>
                        <td width='100px' align='right'>Num. Boletos  &nbsp;</td>
                        <!--<td width='100px' align='right'>Valor Unitário    &nbsp;</td>-->
                        <td width='130px' align='right'>Valor Boletos &nbsp;</td>  
                        <td width='125px' align='right'>Total em Ofic. Pagas&nbsp;</td>
                        <td width='15px'  align='right'>&nbsp;</td>
                        <td width='700px'>Nome do Perfil           &nbsp;</td>
                    </tr>
                    ";
            }#naoMostrar
            #Efetuando loop para o visualização dos registros retornados pela consulta SQL
            While (!$RS->EOF) {

                #Adicionando nas variaveis, os valores dos campos retirados dos registros
                $cod_perfil = $RS->fields[0];
                $titulo = $RS->fields[1];
                $total_inscritos_por_perfil = $RS->fields[2];
                $total_inscritos_pagos_por_perfil = $RS->fields[3];
                $valor_inscritos_pagos_por_perfil = $RS->fields[4];
                if(COD_EVENTO==2){
                    $valor_inscritos_pagos_por_perfil = $total_inscritos_pagos_por_perfil*'200,00';
                }
                $valor_unitario = $RS->fields[5];
                $total_oficinas_pagas = $RS->fields[6];
                $total_empenho_pagas_por_perfil = $RS->fields[7];
                $valor_empenho_pagas_por_perfil = $RS->fields[8];
                $valor_empenho = $RS->fields[9];
                
                $total_outras_por_perfil = $RS->fields[10];
                $valor_outras_por_perfil = $RS->fields[11];
                $valor_outras = $RS->fields[12];
                
                $total_deposito_cc_por_perfil = $RS->fields[13];
                $valor_deposito_cc_por_perfil = $RS->fields[14];
                $valor_deposito_cc_ = $RS->fields[15];

                $total_inscritos_em_perfis = $total_inscritos_por_perfil + $total_inscritos_em_perfis;
                
                $total_empenho_pagas_em_perfil = $total_empenho_pagas_por_perfil + $total_empenho_pagas_em_perfil;
                $valor_empenho_pagas_em_perfil = $valor_empenho_pagas_por_perfil + $valor_empenho_pagas_em_perfil;

                $total_outras_em_perfil = $total_outras_por_perfil + $total_outras_em_perfil;
                $valor_outras_em_perfil = $valor_outras_por_perfil + $valor_outras_em_perfil;

                $total_deposito_cc_em_perfil = $total_deposito_cc_por_perfil + $total_deposito_cc_em_perfil;
                $valor_deposito_cc_em_perfil = $valor_deposito_cc_por_perfil + $valor_deposito_cc_em_perfil;
                
                $total_inscritos_pagos_em_perfis = $total_inscritos_pagos_por_perfil + $total_inscritos_pagos_em_perfis;
                $valor_inscritos_pagos_em_perfis = $valor_inscritos_pagos_por_perfil + $valor_inscritos_pagos_em_perfis;
                $total_oficinas_pagas_por_perfil = $total_oficinas_pagas + $total_oficinas_pagas_por_perfil;

                if(COD_EVENTO==2){
                    $valor_inscritos_pagos_por_perfil = formatMoneyView($valor_inscritos_pagos_por_perfil.'00');
                }

                if($naoMostrar){ 
                echo "<tr valign='top'>
                    <td align='right'>" . $total_inscritos_por_perfil . "</td>

                    <td align='right'>" . $total_empenho_pagas_por_perfil . "</td>
                    <td align='right'>" . formatMoneyView($valor_empenho_pagas_por_perfil) . "</td>

                    <td align='right'>" . $total_outras_por_perfil . "</td>
                    <td align='right'>" . formatMoneyView($valor_outras_por_perfil) . "</td>

                    <td align='right'>" . $total_deposito_cc_por_perfil . "</td>
                    <td align='right'>" . formatMoneyView($valor_deposito_cc_por_perfil) . "</td>
                    
                    <td align='right'>" . $total_inscritos_pagos_por_perfil . "</td>
                    <!--<td align='right'>" . $valor_unitario . "</td>-->
                    <td align='right'>"
                . $valor_inscritos_pagos_por_perfil
                . "</td>
                    <td align='right'>" . $total_oficinas_pagas . "</td>
                    <td >&nbsp;</td>
                    <td > <a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_perfil=" . $cod_perfil . "'>" . $titulo . "</a> </td>
                    </tr>
                    ";

                }#naomostrar
                #Movendo para o proximo registro
                $RS->MoveNext();
            }
            $total_valor_oficinas_pagas_por_perfil = $total_oficinas_pagas_por_perfil * $OP_VALOR_OFICINA;
            if($naoMostrar){ 
            echo "<tr valign='top'>
                        <td width='100px' align='right' style='color:green;'>Total: " . $total_inscritos_em_perfis . "</td>
                        <td width='100px' align='right' style='color:green;'>Qtd.: " . $total_empenho_pagas_em_perfil . "</td>
                        <td width='100px' align='right' style='color:green;'>Vlr.Total: " . $valor_empenho_pagas_em_perfil . "</td>
                        <td width='100px' align='right' style='color:green;'>Qtd.: " . $total_outras_em_perfil . "</td>
                        <td width='100px' align='right' style='color:green;'>Vlr.Total: " . $valor_outras_em_perfil . "</td>
                        <td width='100px' align='right' style='color:green;'>Qtd.: " . $total_deposito_cc_em_perfil . "</td>
                        <td width='100px' align='right' style='color:green;'>Vlr.Total: " . $valor_deposito_cc_em_perfil . "</td>
                        <td width='100px' align='right' style='color:green;'>Qtd.: " . $total_inscritos_pagos_em_perfis . "</td>
                        <!--<td width='100px' align='right'></td>-->
                        <td width='100px' align='right' style='color:green;'>
                        Vlr.Total:<br>  R$ ". $valor_inscritos_pagos_em_perfis . "</td>
                        <td width='100px' align='right' style='color:green;'>Vlr.Total: " . $total_oficinas_pagas_por_perfil . "</td>
                        <td width='15px'  align='right'>&nbsp;</td>
                        <td width='700px'></td>
                    </tr>
                    ";
            }#naoMostrar
            echo "</table><br>";
            
            $valor_inscritos_pagos_em_perfis = $valor_inscritos_pagos_em_perfis.'00';
            $total_valor_oficinas_pagas_por_perfil = $total_valor_oficinas_pagas_por_perfil.'00';
            $Total_Valor_Refeicoes = $Total_Valor_Arrecadado-($valor_inscritos_pagos_em_perfis + $total_valor_oficinas_pagas_por_perfil);
            
            
            echo "<table cellspacing=4 cellpadding=4 border=0>"
            . "<tr><td colspan=2><b>Resumo da Arrecadação</b></td></tr>"
            //. "<tr style='color:green;'><td>Valor referente a refeições:  </td><td>R$ ".formatMoneyView($Total_Valor_Refeicoes)."</td></tr>"
            // . "<tr style='color:green;'><td>Valor referente a atividades:   </td><td>R$ ".formatMoneyView($total_valor_oficinas_pagas_por_perfil)."</td></tr>"
            // . "<tr style='color:green;'><td>Valor referente a inscrições: </td><td>R$ ".formatMoneyView($valor_inscritos_pagos_em_perfis)."</td></tr>"
            . "<tr style='color:green;'><td>Valor referente a insc. por empenho: </td><td>R$ ".$valor_empenho_pagas_em_perfil."</td></tr>"
            . "<tr style='color:green;'><td>Valor referente a insc. por outras: </td><td>R$ ".$valor_outras_em_perfil."</td></tr>"
            . "<tr style='color:green;'><td>Valor referente a insc. por deposito cc: </td><td>R$ ".$valor_deposito_cc_em_perfil."</td></tr>"
            . "<!--<tr style='color:green;font-weight: bolder'><td>Saldo Esperado em C.C.:       </td><td>R$ ".$Total_Valor_Arrecadado_Boleto."</td></tr>-->"
            . "<tr style='color:green;font-weight: bolder'><td>Valor total (inscrições e outros):       </td><td>R$ ".formatMoneyView($Total_Valor_Arrecadado)."</td></tr>"
            . "</table><br>";
        }
    }
}
## Consulta Inscritos por Estado ##
#Definindo comando SQL tp.cod_inscrito, tp.valor,
$var_sql = "
                SELECT  Count(ti.cod_inscrito) as Total_Pagos_Inscritos
                 , (SELECT Count(tt.cod_trabalho) as Total_Pagos_Trabalhos_Enviados
                   FROM tab_trabalhos tt, tab_inscritos ti 
                   WHERE (tt.cod_evento   = " . COD_EVENTO . ")and
                         (tt.cod_inscrito = ti.cod_inscrito)and
                         (ti.status       = 'ATIVADA')and
                         (tt.status       = 'ATIVADA')and
                      (ti.cod_inscrito in (SELECT tp.cod_inscrito FROM tab_pagamentos tp GROUP BY tp.cod_inscrito)))
                 , (SELECT Count(tm.cod_monografia) as Total_Pagos_Monografias_Enviadas
                   FROM tab_monografias tm, tab_inscritos ti 
                   WHERE (tm.cod_evento   = " . COD_EVENTO . ")and
                         (tm.cod_inscrito = ti.cod_inscrito)and
                         (tm.status       = 'ATIVADA')and
                         (ti.status       = 'ATIVADA')and
                      (ti.cod_inscrito in (SELECT tp.cod_inscrito FROM tab_pagamentos tp GROUP BY tp.cod_inscrito)))
                 , (SELECT Count(tlio.cod_oficina) as Total_Pagos_Inscritos_Oficinas
                   FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti, tab_opcoes_oficinas toa
                   WHERE (tlio.cod_evento   = " . COD_EVENTO . ")and
                         (tlio.cod_oficina  = toa.cod_oficina)and
                         (tlio.cod_inscrito = ti.cod_inscrito)and
                         (ti.status         = 'ATIVADA')and
                      (ti.cod_inscrito in (SELECT tp.cod_inscrito FROM tab_pagamentos tp GROUP BY tp.cod_inscrito)))
                 , (SELECT Count(ti.cod_inscrito) as Total_Pagos_Inscritos_Com_Desconto
                   FROM tab_inscritos ti
                   WHERE (ti.cod_evento = " . COD_EVENTO . ")and
                         (ti.desconto   = 'SIM')and
                         (ti.status     = 'ATIVADA')and
                      (ti.cod_inscrito in (SELECT tp.cod_inscrito FROM tab_pagamentos tp GROUP BY tp.cod_inscrito)))
                 , (SELECT Count(ti.cod_inscrito) as Total_Pagos_Inscritos_Sem_Desconto
                   FROM tab_inscritos ti
                   WHERE (ti.cod_evento = " . COD_EVENTO . ")and
                         (ti.desconto   = 'NAO')and
                         (ti.status       = 'ATIVADA')and
                      (ti.cod_inscrito in (SELECT tp.cod_inscrito FROM tab_pagamentos tp GROUP BY tp.cod_inscrito)))
                 , (SELECT COUNT(ttpe.cod_evento) AS Total_Inscritos_Trabalhos_Pagos_Enviados
                   FROM (SELECT ti.cod_inscrito,ti.cod_evento
                            FROM tab_trabalhos tt, tab_inscritos ti 
                            WHERE (tt.cod_evento   = " . COD_EVENTO . ")AND
                                (tt.cod_inscrito = ti.cod_inscrito)AND
                                (ti.status       = 'ATIVADA')AND
                                (tt.status       = 'ATIVADA')AND
                                (ti.cod_inscrito IN (SELECT tp.cod_inscrito FROM tab_pagamentos tp GROUP BY tp.cod_inscrito))
                           GROUP BY ti.cod_evento,ti.cod_inscrito) AS ttpe
                    )
                FROM tab_inscritos ti
                WHERE (ti.cod_evento = " . COD_EVENTO . ")and
                      (ti.status       = 'ATIVADA')and
                      (ti.cod_inscrito in (SELECT tp.cod_inscrito FROM tab_pagamentos tp GROUP BY tp.cod_inscrito))
               ";


#echo $var_sql;
#Fim da definição do comando SQL
#Executando a consulta no banco SQL
$RS = $Conexao_db->Execute($var_sql);

#Verificando se houve retorno de algum registro
If (!$RS->EOF) {
    #Definindo cabeçalho da tabela dos resultados da consula
    //echo "<table><tr><td><br><b>Pagamentos:</b></td></tr>";
    #Efetuando loop para o visualização dos registros retornados pela consulta SQL

    $ii == 0;
    While (!$RS->EOF) {

        #Adicionando nas variaveis, os valores dos campos retirados dos registros
        #$cod_inscrito                       = $RS->fields[0];
        $Total_Pagos_Inscritos = $RS->fields[0];
        $Total_Pagos_Trabalhos_Enviados = $RS->fields[1];
        $Total_Pagos_Monografias_Enviadas = $RS->fields[2];
        $Total_Pagos_Inscritos_Oficinas = $RS->fields[3];
        $Total_Pagos_Inscritos_Com_Desconto = $RS->fields[4];
        $Total_Pagos_Inscritos_Sem_Desconto = $RS->fields[5];
        $Total_Inscritos_Trabalhos_Pagos_Enviados = $RS->fields[6];
        #Definindo tabela com resultado

//        echo "
//        <tr><td><b><a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&pagamentos=99'>
//        Inscritos que efetuaram algum pagamento:</b> {$Total_Inscritos_Efetuaram_Pagamento}</a></td></tr>
//        <tr><td><b><a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&pagamentos=99'>
//        Quantidade de pagamentos registrados:</b> {$Total_PG_Registrados}</a> <!--(alguns inscritos podem ter efetuado mais de 1 pagamento)--></td></tr>
//            ";
        if ($objEvConf->getTrabSubmissao()) {
            if ($objEvConf->getReceberPagamento()) {
                echo "      
                <tr><td><b><a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-trabalhos.php&pagamentos=99'>
                Trabalhos enviados e pagos: </b> {$Total_Pagos_Trabalhos_Enviados}</a> <!--(alguns inscritos podem ter enviado mais de 1 trabalho)--></td></tr>";
                
                echo "<tr><td><b><a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&pagamentos=99'>
                Inscritos que enviaram trabalho e efetuaram o pagamento: </b> {$Total_Inscritos_Trabalhos_Pagos_Enviados}</td></tr>
                <!--<tr><td><b>Inscritos que efetuaram o pagamento e optaram pela premiações:</b> {$Total_Pagos_Monografias_Enviadas}</td></tr>-->
                <!--<tr><td><b>Inscritos que efetuaram o pagamento e optaram por atividade:  </b> {$Total_Pagos_Inscritos_Oficinas}</td></tr>-->
                <!--<tr><td><b>Inscritos efetuaram o pagamento com desconto: </b> {$Total_Pagos_Inscritos_Com_Desconto}</td></tr>-->
                <!--tr><td><b>Inscritos efetuaram o pagamento sem desconto: </b> {$Total_Pagos_Inscritos_Sem_Desconto}</td></tr-->
                ";
            }
        }

        #Movendo para o proximo registro
        $RS->MoveNext();
    }
    echo "</table><br>";
}
#------------------------------------------------------
if ($objEvConf->getPossuiEventoParalelo()) {
    try {
        ## Consulta Inscritos por Evento Paralelo ##
        echo "<br><b>Detalhes de inscritos por Evento Paralelos:</b><br>";
        $objG = new Grafico();
        $objG->carregarListaTotalInscritosPorEventoParalelo($debug);

        #Definindo cabeçalho da tabela dos resultados da consula
        echo "<table cellspacing=4 cellpadding=4>
                <tr><td colspan='7'><b>Totais de inscritos por Evento Paralelo:</b></td></tr>
                ";
        echo "<tr valign='top'>
                    <td width='50px' align='right'></td>
                    <td width='50px' align='right'>Vagas</td>
                    <td width='70px' align='right'>Inscritos</td>
                    <td width='50px' align='right'>Insc. Pagos</td>
                    <td width='50px' align='right'>Insc. Isentos</td>
                    <td width='10px'></td>
                    <!--<td width='50px' align='right'>Aguard. Pag.</td>-->
                    <td width='10px'></td>
                    <td width='50px' align='right'>Vagas Disponíveis</td>
                    <td width='10px'></td>
                    <td width='700px'>Titulo</td>
                  </tr>
                 ";
        foreach ($objG->getListaTotalInscritosPorEventoParalelo() as $objL) {
            #Adicionando nas variaveis, os valores dos campos retirados dos registros
            $cod_oficina = $objL->getCodOficina();
            $Titulo = $objL->getTitulo();
            $numero_vagas = $objL->getNumeroVagas();
            $status = $objL->getStatus();
            $Total_Inscritos_Oficinas = $objL->getTotalInscricoes();
            $Total_Inscritos_Pagos = $objL->getTotalInscricoesPagas();
            $Total_Inscritos_Isentos = $objL->getTotalInscricoesIsentos();
            $Total_Inscritos_Of_por_CPF = $objL->getTotalInscricoesPorCpf();
            $total_inscricoes_boleto_aguardando_pag_por_oficina = $objL->getTotalInscricoesBoletoAguardandoPagPorOficina();
            $ordem = $objL->getOrdem();
            $numero_oficial = $objL->getNumeroOficial();

            if(($cod_oficina!=98)and($cod_oficina!=99)){
                $totalAtividades++;
            @$Total_Vagas_Disponiveis = $numero_vagas - $Total_Inscritos_Pagos - $Total_Inscritos_Isentos;
            #Definindo tabela com resultado

            @$total_inscritos_em_oficinas = $total_inscritos_em_oficinas + $Total_Inscritos_Oficinas;
            @$total_inscritos_em_oficinas_pagos = $total_inscritos_em_oficinas_pagos + $Total_Inscritos_Pagos;
            @$total_inscritos_em_oficinas_isentos = $total_inscritos_em_oficinas_isentos + $Total_Inscritos_Isentos;
            @$total_inscritos_em_oficinas_vagas = $total_inscritos_em_oficinas_vagas + $numero_vagas;
            @$total_inscritos_em_oficinas_vagas_disp = $total_inscritos_em_oficinas_vagas_disp + $Total_Vagas_Disponiveis;
            @$total_geral_inscricoes_boleto_aguardando_pag_por_oficina = $total_geral_inscricoes_boleto_aguardando_pag_por_oficina + $total_inscricoes_boleto_aguardando_pag_por_oficina;
            #$Total_Vagas_Disponiveis = $RS->fields[1]-$RS->fields[2];


            echo "<tr valign='top'>
                  <td align='right'></td>
                  <td align='right'>" . $numero_vagas . "              </td>
                  <td align='right'>" . $Total_Inscritos_Of_por_CPF . "</td>
                  <td align='right'>" . $Total_Inscritos_Pagos . "</td>
                  <td align='right'>" . $Total_Inscritos_Isentos . "</td>
                  <td width='10px'></td>
                  <!--<td align='right'>" . $total_inscricoes_boleto_aguardando_pag_por_oficina . "</td>-->
                  <td width='10px'></td>
                  <td align='right'>" . $Total_Vagas_Disponiveis . "   </td>
                  <td width='10px'></td>
                  <td ><a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_oficina=" . $cod_oficina . "'>" . $Titulo . "</a> </td>
                  </tr>
                 ";


                #Movendo para o proximo registro
            }
        }
        echo "<tr valign='top'>
                  <td align='right'><b>Totais:</b></td>
                  <td align='right'><b>" . $total_inscritos_em_oficinas_vagas . "</b></td>
                  <td align='right'><b>" . $total_inscritos_em_oficinas . "</b></td>
                  <td align='right'><b>" . $total_inscritos_em_oficinas_pagos . "</b></td>
                  <td align='right'><b>" . $total_inscritos_em_oficinas_isentos . "</b></td>
                    <td width='10px'></td>
                  <!--<td align='right'><b>" . $total_geral_inscricoes_boleto_aguardando_pag_por_oficina . "</b></td>-->
                  <td width='10px'></td>
                  <td align='right'><b>" . $total_inscritos_em_oficinas_vagas_disp . "</b></td>
                  <td ></td>
                  <td ></td>
                  </tr>
                 ";
        echo "<tr><td colspan=7>Total de Evento Paralelo listados: ".$totalAtividades."</td></tr>";
        echo "</table><br>";
    } catch (Exception $exc) {
        $exc->getMessage()."<hr>";
    }
}
#------------------------------------------------------
if ($objEvConf->getPossuiEventoParaleloSimposio()) {
    try {
        ## Consulta Inscritos por Simpósio ##
        echo "<br><b>Detalhes de inscritos por Simpósios:</b><br>";
        $objG = new Grafico();
        $objG->carregarListaTotalInscritosPorSimposio($debug);

        #Definindo cabeçalho da tabela dos resultados da consula
        echo "<table cellspacing=4 cellpadding=4>
                <tr><td colspan='7'><b>Totais de inscritos por Simpósio:</b></td></tr>
                ";
        echo "<tr valign='top'>
                    <td width='50px' align='right'></td>
                    <td width='50px' align='right'>Vagas</td>
                    <td width='70px' align='right'>Inscritos</td>
                    <td width='50px' align='right'>Insc. Pagos</td>
                    <td width='50px' align='right'>Insc. Isentos</td>
                    <td width='10px'></td>
                    <!--<td width='50px' align='right'>Aguard. Pag.</td>-->
                    <td width='10px'></td>
                    <td width='50px' align='right'>Vagas Disponíveis</td>
                    <td width='10px'></td>
                    <td width='10px'>Número</td>
                    <td width='700px'>Titulo</td>
                  </tr>
                 ";
        foreach ($objG->getListaTotalInscritosPorSimposio() as $objL) {
            #Adicionando nas variaveis, os valores dos campos retirados dos registros
            $cod_oficina = $objL->getCodOficina();
            $Titulo = $objL->getTitulo();
            $numero_vagas = $objL->getNumeroVagas();
            $status = $objL->getStatus();
            $Total_Inscritos_Oficinas = $objL->getTotalInscricoes();
            $Total_Inscritos_Pagos = $objL->getTotalInscricoesPagas();
            $Total_Inscritos_Isentos = $objL->getTotalInscricoesIsentos();
            $Total_Inscritos_Of_por_CPF = $objL->getTotalInscricoesPorCpf();
            $total_inscricoes_boleto_aguardando_pag_por_oficina = $objL->getTotalInscricoesBoletoAguardandoPagPorOficina();
            $ordem = $objL->getOrdem();
            $numero_oficial = $objL->getNumeroOficial();

            if(($cod_oficina!=98)and($cod_oficina!=99)){
                $totalAtividades++;
            @$Total_Vagas_Disponiveis = $numero_vagas - $Total_Inscritos_Pagos - $Total_Inscritos_Isentos;
            #Definindo tabela com resultado

            @$total_inscritos_em_oficinas = $total_inscritos_em_oficinas + $Total_Inscritos_Oficinas;
            @$total_inscritos_em_oficinas_pagos = $total_inscritos_em_oficinas_pagos + $Total_Inscritos_Pagos;
            @$total_inscritos_em_oficinas_isentos = $total_inscritos_em_oficinas_isentos + $Total_Inscritos_Isentos;
            @$total_inscritos_em_oficinas_vagas = $total_inscritos_em_oficinas_vagas + $numero_vagas;
            @$total_inscritos_em_oficinas_vagas_disp = $total_inscritos_em_oficinas_vagas_disp + $Total_Vagas_Disponiveis;
            @$total_geral_inscricoes_boleto_aguardando_pag_por_oficina = $total_geral_inscricoes_boleto_aguardando_pag_por_oficina + $total_inscricoes_boleto_aguardando_pag_por_oficina;
            #$Total_Vagas_Disponiveis = $RS->fields[1]-$RS->fields[2];


            echo "<tr valign='top'>
                  <td align='right'></td>
                  <td align='right'>" . $numero_vagas . "              </td>
                  <td align='right'>" . $Total_Inscritos_Of_por_CPF . "</td>
                  <td align='right'>" . $Total_Inscritos_Pagos . "</td>
                  <td align='right'>" . $Total_Inscritos_Isentos . "</td>
                  <td width='10px'></td>
                  <!--<td align='right'>" . $total_inscricoes_boleto_aguardando_pag_por_oficina . "</td>-->
                  <td width='10px'></td>
                  <td align='right'>" . $Total_Vagas_Disponiveis . "   </td>
                  <td width='10px'></td>
                  <td width='10px'>".$objL->getAbrevTipo()."</td>
                  <td ><a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_oficina=" . $cod_oficina . "'>" . $Titulo . "</a> </td>
                  </tr>
                 ";


                #Movendo para o proximo registro
            }
        }
        echo "<tr valign='top'>
                  <td align='right'><b>Totais:</b></td>
                  <td align='right'><b>" . $total_inscritos_em_oficinas_vagas . "</b></td>
                  <td align='right'><b>" . $total_inscritos_em_oficinas . "</b></td>
                  <td align='right'><b>" . $total_inscritos_em_oficinas_pagos . "</b></td>
                  <td align='right'><b>" . $total_inscritos_em_oficinas_isentos . "</b></td>
                    <td width='10px'></td>
                  <!--<td align='right'><b>" . $total_geral_inscricoes_boleto_aguardando_pag_por_oficina . "</b></td>-->
                  <td width='10px'></td>
                  <td align='right'><b>" . $total_inscritos_em_oficinas_vagas_disp . "</b></td>
                  <td ></td>
                  <td ></td>
                  </tr>
                 ";
        echo "<tr><td colspan=7>Total de Simpósio listados: ".$totalAtividades."</td></tr>";
        echo "</table><br>";
    } catch (Exception $exc) {
        $exc->getMessage()."<hr>";
    }
}
#------------------------------------------------------
/** @todo Avaliação de Inscrito */
if (in_array(COD_EVENTO, array(80,88))) {

    $codEventoInscricao = COD_EVENTO;
    $codEventoContrato = 82;
    if(COD_EVENTO==80){
        $codPerfilVagasUniversal_01 = 446;
        $codPerfilVagasUniversal_02 = 445;
    }
    if(COD_EVENTO==88){
        $codPerfilVagasUniversal_01 = 492;
        $codPerfilVagasUniversal_02 = 491;
    }

        echo "<style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 16px;
            font-family: Arial, sans-serif;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        table th {
            background-color: #00A86B;
            color: white;
            font-weight: bold;
        }
        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        table tr:hover {
            background-color: #ddd;
        }
        table caption {
            font-size: 20px;
            margin-bottom: 10px;
            font-weight: bold;
        }
    </style>";

    /** para contagem de classificacao final */
    $contagem_status = " ,SUM(CASE WHEN tai.classificacao_status_final = 'H' THEN 1 ELSE 0 END) AS habilitados
    ,SUM(CASE WHEN tai.classificacao_status_final = 'I' THEN 1 ELSE 0 END) AS inabilitados
    ,SUM(CASE WHEN tai.classificacao_status_final NOT IN ('H', 'I') THEN 1 ELSE 0 END) AS indefinido
    ,SUM(CASE WHEN tai.classificacao_status_final = 'H' THEN 1 ELSE 0 END)
    +SUM(CASE WHEN tai.classificacao_status_final = 'I' THEN 1 ELSE 0 END)
    AS total_avaliados ";

    /** para contagem de classificacao */
    $contagem_status = " ,SUM(CASE WHEN tai.classificacao_status = 'H' THEN 1 ELSE 0 END) AS habilitados
    ,SUM(CASE WHEN tai.classificacao_status = 'I' THEN 1 ELSE 0 END) AS inabilitados
    ,SUM(CASE WHEN tai.classificacao_status NOT IN ('H', 'I') THEN 1 ELSE 0 END) AS indefinido
    ,SUM(CASE WHEN tai.classificacao_status = 'H' THEN 1 ELSE 0 END)
    +SUM(CASE WHEN tai.classificacao_status = 'I' THEN 1 ELSE 0 END)
    AS total_avaliados ";
  
    $var_sql = "SELECT 
    o.ordem
    ,p.cod_perfil
    ,p.titulo AS cargo
    ,o.titulo AS nucleo
    ,o.cod_oficina AS cod_nucleo
    ,CASE 
        WHEN p.cod_perfil = ".$codPerfilVagasUniversal_01." THEN o.vagas_universal_01 
        WHEN p.cod_perfil = ".$codPerfilVagasUniversal_02." THEN o.vagas_universal_02 
        ELSE NULL
    END AS vagas
    ,COUNT(ti.cod_inscrito) AS total_inscritos
    ".$contagem_status."
    ,COUNT(DISTINCT ti.cod_inscrito) - COUNT(DISTINCT tai.cod_inscrito) AS total_nao_avaliados
    ,SUM(CASE WHEN ti.status_processo = 'CONVOCADO' THEN 1 ELSE 0 END)+ SUM(CASE WHEN ti.status_processo = 'DESISTENTE' THEN 1 ELSE 0 END) AS convocados
,COUNT(CASE WHEN ti.cpf IN (SELECT cpf FROM tab_inscritos WHERE cod_evento = ".$codEventoContrato." AND STATUS = 'ATIVADA' and status_processo='PARTICIPANDO') THEN 1 END) AS participando
    ,SUM(CASE WHEN ti.status_processo= 'DESISTENTE' THEN 1 ELSE 0 END) AS desistentes
    ,SUM(CASE WHEN ti.status_processo = 'SEM_RETORNO' THEN 1 ELSE 0 END) AS sem_retorno
,COUNT(CASE WHEN (status_processo = 'CONVOCADO') AND ti.cpf NOT IN (SELECT cpf FROM tab_inscritos WHERE cod_evento = ".$codEventoContrato." AND STATUS = 'ATIVADA') THEN 1 END) AS nao_se_apresentou    
FROM tab_inscritos ti
LEFT  JOIN  tab_trabalhos_avaliacao_inscricao tai ON tai.cod_inscrito = ti.cod_inscrito
INNER JOIN  tab_opcoes_perfis p ON ti.cod_perfil = p.cod_perfil
LEFT JOIN  tab_lig_inscritos_oficinas lio ON ti.cod_inscrito = lio.cod_inscrito and ti.status = 'ATIVADA'
INNER JOIN  tab_opcoes_oficinas o ON lio.cod_oficina = o.cod_oficina
WHERE  ti.cod_evento=".$codEventoInscricao."
GROUP BY  ti.cod_perfil, o.cod_oficina
ORDER BY  p.cod_perfil, o.ordem";
    // Executando a consulta no banco de dados
    $RS = $Conexao_db->Execute($var_sql);

    // Verificando se houve retorno de algum registro
    if (!$RS->EOF) {
        ob_start();
        // Loop para percorrer os registros retornados pela consulta
        while (!$RS->EOF) {
            // Obtendo os dados de cada linha
            $cod_perfil = $RS->fields['cod_perfil'];
            $cargo = $RS->fields['cargo'];
            $ordem = $RS->fields['ordem'];
            $nucleo = $RS->fields['nucleo'];
            $cod_nucleo = $RS->fields['cod_nucleo'];
            $vagas = $RS->fields['vagas'];
            $total_inscritos = $RS->fields['total_inscritos'];
            $habilitados = $RS->fields['habilitados'];
            $convocados = $RS->fields['convocados'];
            $participando = $RS->fields['participando'];
            $desistentes = $RS->fields['desistentes'];
            $nao_se_apresentou = $RS->fields['nao_se_apresentou'];
            $inabilitados = $RS->fields['inabilitados'];
            $indefinido = $RS->fields['indefinido'];
            $total_avaliados = $RS->fields['total_avaliados'];
            $total_nao_avaliados = $RS->fields['total_nao_avaliados'];

            $convocar = $vagas - $participando;
            $aptos_convocar = $habilitados - $convocados;
            $aptos_convocar_style = '';
            if($convocar > $aptos_convocar){
                $aptos_convocar_style = 'style=color:red;';
            }

            $styleNenhumConvocado = '';
            if ($total_inscritos == $inabilitados){
                $styleNenhumConvocado = 'style=color:red;';
            }
            if ($vagas > $habilitados){
                $styleNenhumConvocado = 'style=color:red;';
            }
            if ($total_inscritos == 0){
                $styleNenhumConvocado = 'style=color:orange;';
            }

            // Definindo o cabeçalho da tabela dos resultados da consulta
            if ($cod_perfil != $cod_perfil_ant) {
                if ($cod_perfil_ant != null) {
                    echo "<tr>
                            <td colspan='4'><b>Totais:</b></td>
                            <td align='center'><b>{$total_inscritos_sum}</b></td>
                            <td align='center'><b>{$habilitados_sum}</b></td>
                            <td align='center'><b>{$inabilitados_sum}</b></td>
                            <td align='center'><b>{$indefinido_sum}</b></td>
                            <td align='center'><b>{$total_avaliados_sum}</b></td>
                            <td align='center'><b>{$total_nao_avaliados_sum}</b></td>
                            <td align='center'><b>{$convocados_sum}</b></td>
                            <td align='center'><b>{$participando_sum}</b></td>
                            <td align='center'><b>{$desistentes_sum}</b></td>
                            <td align='center'><b>{$nao_se_apresentou_sum}</b></td>
                            <td align='center'><b>{$convocar_sum}</b></td>
                            <td align='center'><b>{$aptos_convocar_sum}</b></td>
                          </tr>";
                    echo "</tbody></table>";
                    
                    // Reset totals for the next profile
                    $total_inscritos_sum = 0;
                    $habilitados_sum = 0;
                    $convocados_sum = 0;
                    $participando_sum = 0;
                    $desistentes_sum = 0;
                    $nao_se_apresentou_sum = 0;
                    $inabilitados_sum = 0;
                    $indefinido_sum = 0;
                    $total_avaliados_sum = 0;
                    $total_nao_avaliados_sum = 0;

                    $convocar_sum = 0;
                    $aptos_convocar_sum = 0;
                }
               
                echo "<h4>Relatório de Inscritos " . $cargo . "</h4>";
                ?>
                <table>
                    <thead>
                        <tr>
                            <th>Ordem</th>
                            <th>Cód.Núcleo</th>
                            <th>Núcleo</th>
                            <th>Vagas</th>
                            <th>Candidatos</th>
                            <!-- <th>Classificados 1ºFase</th>-->
                            <th>Habilitados</th>
                            <th>Não Habilitados</th>
                            <th>Avaliados</th>
                            <th>Indefinido/Iniciado</th>
                            <th>Não Avaliados</th>
                            <th>Convocados</th>
                            <th>participando</th>
                            <th>Desistentes</th>
                            <th>Não se Apresentou</th>
                            <th>Convocar</th>
                            <th>Aptos a Convocar</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                <?php
            }
            $cod_perfil_ant = $cod_perfil;            
            // Calculating totals
            $total_inscritos_sum += $total_inscritos;
            $habilitados_sum += $habilitados;
            $convocados_sum += $convocados;
            $participando_sum += $participando;
            $desistentes_sum += $desistentes;
            $nao_se_apresentou_sum += $nao_se_apresentou;
            $inabilitados_sum += $inabilitados;
            $indefinido_sum += $indefinido;
            $total_avaliados_sum += $total_avaliados;
            $total_nao_avaliados_sum += $total_nao_avaliados;

            $convocar_sum += $convocar;
            $aptos_convocar_sum += $aptos_convocar;

            // Exibindo os dados na tabela
            echo "<tr ".$styleNenhumConvocado.">
                    <td align='center'>{$ordem}</td>
                    <td align='center'>{$cod_nucleo}</td>
                    <td align='center' {$aptos_convocar_style}>{$nucleo}</td>
                    <td align='center'>{$vagas}</td>
                    <td align='center'>{$total_inscritos}</td>
                    <td ".$styleNenhumConvocado." align='center'>{$habilitados}</td>
                    <td align='center'>{$inabilitados}</td>
                    <td align='center'>{$total_avaliados}</td>
                    <td align='center'>{$indefinido}</td>
                    <td align='center'>{$total_nao_avaliados}</td>
                    <td align='center'>{$convocados}</td>
                    <td align='center'>{$participando}</td>
                    <td align='center'>{$desistentes}</td>
                    <td align='center'>{$nao_se_apresentou}</td>
                    <td align='center'>{$convocar}</td>
                    <td align='center' {$aptos_convocar_style}>{$aptos_convocar}</td>
                </tr>";

            // Avançando para o próximo registro
            $RS->MoveNext();
        }
        echo "<tr>
        <td colspan='4'><b>Totais:</b></td>
        <td align='center'><b>{$total_inscritos_sum}</b></td>
        <td align='center'><b>{$habilitados_sum}</b></td>
        <td align='center'><b>{$inabilitados_sum}</b></td>
        <td align='center'><b>{$total_avaliados_sum}</b></td>
        <td align='center'><b>{$indefinido_sum}</b></td>
        <td align='center'><b>{$total_nao_avaliados_sum}</b></td>
        <td align='center'><b>{$convocados_sum}</b></td>
        <td align='center'><b>{$participando_sum}</b></td>
        <td align='center'><b>{$desistentes_sum}</b></td>
        <td align='center'><b>{$nao_se_apresentou_sum}</b></td>
        <td align='center'><b>{$convocar_sum}</b></td>
        <td align='center' {$aptos_convocar_style}><b>{$aptos_convocar_sum}</b></td>
        </tr>";
        echo "</tbody></table>";        
        // Fechando a tabela
        echo "</table>";
        $htmlContent = ob_get_clean();
        $filePath = WB_PATH_LOG_AVULSO.'output'.$cod_perfil.'.html';
        // Write the HTML content to a file
        file_put_contents($filePath, $htmlContent);
        // Print the HTML content to the screen
        echo $htmlContent;
    } else {
        // Se não houver registros, exibe uma mensagem
        echo "<p>Nenhum registro encontrado.</p>";
    }
    #------------------------------------------------------
    ### Consulta AvaliadosPorAvaliadorInscrito ##
    // echo "<br><b>Detalhes de AvaliadosPorAvaliadorInscrito:</b><br>";
    // echo "De acordo Instituição informada no trabalho<br>";
    // try {
    //     $objG = new Grafico();
    //     $objG->carregarListaTotalAvaliadosPorAvaliadorInscrito($debug);
    //     $varSomaTotal = "";
    //     foreach ($objG->getListaTotalAvaliadosPorAvaliadorInscrito() as $objL) {

    //         if($objL->getClassificacaoStatus() == 'H'){
    //             $objL->setTotalAprovado($objL->getTotalStatus());
    //         }
    //         if($objL->getClassificacaoStatus() == 'I'){
    //             $objL->setTotalReprovado($objL->getTotalStatus());
    //         }
    //         if($objL->getClassificacaoStatus() == ''){
    //             $objL->setTotalIndefinido($objL->getTotalIndefinido());
    //         }
    //         $varGraficoAvaliadosPorAvaliadorInscrito .= "['" . $objL->getIdInclusao() . "'," . $objL->getTotalAprovado(). "," . $objL->getTotalReprovado(). "," . $objL->getTotalIndefinido(). "],";
    //         @$varSomaTotal = $varSomaTotal + $objL->getTotalStatus();
    //     }
    // } catch (Exception $exc) {
    //     $exc->getMessage()."<br>";
    // }
    ?>
    <!-- <html>
        <head>
            <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
            <script type="text/javascript">
                google.charts.load("current", {packages: ["corechart"]});
                google.charts.setOnLoadCallback(drawChart);
                function drawChart() {
                    var data = google.visualization.arrayToDataTable([
                        ['id_inclusao', 'Aprovados', 'Reprovados', 'INDEFINIDOS'],
                        <?php echo  $varGraficoAvaliadosPorAvaliadorInscrito ?>
                    ]);

                    // var options = {
                    //     title: 'Avaliados por Avaliador Inscrito',
                    //     pieHole: 0.4,
                    // };
                    var options = {
                        title: 'Avaliados por Avaliador Inscrito',
                        hAxis: { title: 'id_inclusao' },
                        vAxis: { title: 'Total Status' },
                        bars: 'grouped', // Barras agrupadas
                        colors: ['#4caf50', '#f44336'], // Verde para aprovados, vermelho para reprovados
                    };                    

                    var chart = new google.visualization.ColumnChart(document.getElementById('graficoAvaliadosPorAvaliadorInscrito'));
                    chart.draw(data, options);
                }
            </script>
        </head>
        <body>
            <div id="graficoAvaliadosPorAvaliadorInscrito" style="width:450px; height:240px;"></div>
        </body>
    </html> -->
    <?php
    #------------------------------------------------------    
}
if(COD_EVENTO!=80){
    if ($objEvConf->getAtividade()){ 
        #------------------------------------------------------
        ## Consulta Inscritos por Oficina##
        #Definindo comando SQL

        $var_sql = "
        SELECT
        toa.cod_oficina
        ,toa.titulo
        ,toa.numero_vagas
        ,toa.status
        ,tti.total_inscricoes
        ,ttp.total_inscricoes_pagas
        ,ttis.total_inscricoes_isentos
        ,(
        Select Count(*) from (SELECT ti.cpf, tlio.cod_oficina
        FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
        WHERE ((tlio.cod_evento   = ti.cod_evento)
            and (tlio.cod_inscrito = ti.cod_inscrito)
            and (ti.status         = 'ATIVADA'))
        GROUP BY  ti.cpf,tlio.cod_oficina) as too
            where toa.cod_oficina= too.cod_oficina
        ) as total_inscricoes_por_cpf
        ,aguardpag.total_inscricoes_boleto_aguardando_pag_por_oficina
        ,toa.ordem
        ,toa.numero_oficial
        FROM tab_opcoes_oficinas toa
        LEFT JOIN (
        SELECT COUNT(tlio.cod_oficina) AS total_inscricoes, tlio.cod_evento, tlio.cod_oficina
        FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
        WHERE (tlio.cod_evento   = ti.cod_evento)
            AND (tlio.cod_inscrito = ti.cod_inscrito)
            AND (ti.status         = 'ATIVADA')
        GROUP BY  tlio.cod_oficina, tlio.cod_evento
        ) AS tti ON tti.cod_evento = toa.cod_evento AND tti.cod_oficina=toa.cod_oficina
        LEFT JOIN (
        SELECT COUNT(tlio.cod_oficina) AS total_inscricoes_pagas, tlio.cod_evento, tlio.cod_oficina
        FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
        WHERE (tlio.cod_evento   = ti.cod_evento)
            AND (tlio.cod_inscrito = ti.cod_inscrito)
            AND (ti.status         = 'ATIVADA')
            AND (ti.cod_inscrito in (SELECT tp.cod_inscrito FROM tab_pagamentos tp 
                                        WHERE tp.status = 'ATIVADA' OR tp.status IS NULL 
                                        GROUP BY tp.cod_inscrito))
        GROUP BY  tlio.cod_oficina, tlio.cod_evento
        ) AS ttp ON ttp.cod_evento = toa.cod_evento AND ttp.cod_oficina=toa.cod_oficina
        LEFT JOIN (
        SELECT COUNT(tlio.cod_oficina) AS total_inscricoes_isentos, tlio.cod_evento, tlio.cod_oficina
        FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
        WHERE (tlio.cod_evento   = ti.cod_evento)
            AND (tlio.cod_inscrito = ti.cod_inscrito)
            AND (ti.status         = 'ATIVADA')
            AND (ti.cpf in (SELECT tis.cpf FROM tab_isentos tis 
                                        WHERE (tis.status = 'ATIVADA' OR tis.status IS NULL) and tis.cod_evento=" . COD_EVENTO . "
                                        GROUP BY tis.cod_isento))
        GROUP BY  tlio.cod_oficina, tlio.cod_evento
        ) AS ttis ON ttis.cod_evento = toa.cod_evento AND ttis.cod_oficina=toa.cod_oficina
        LEFT JOIN (SELECT COUNT(tlio.cod_oficina) AS total_inscricoes_boleto_aguardando_pag_por_oficina, tlio.cod_evento,  tlio.cod_oficina
            FROM tab_lig_inscritos_oficinas tlio, tab_inscritos ti
                WHERE
                    (tlio.cod_evento   = ti.cod_evento)
                AND (tlio.cod_inscrito = ti.cod_inscrito)
                AND (ti.status         = 'ATIVADA')
                AND (ti.cod_inscrito IN (SELECT cod_inscrito FROM (SELECT cod_evento, cod_inscrito, cod_boleto, valor_documento, data_vencimento
                , DATE_FORMAT(STR_TO_DATE(data_vencimento,\"%d/%m/%Y\"),\"%Y%m%d\") AS data_vencimento_comparar
                , DATE_FORMAT(STR_TO_DATE(DATE_SUB(NOW(), INTERVAL 4 DAY),\"%Y-%m-%d\"),\"%Y%m%d\") AS data_referencia_pagamento
                FROM tab_boletos WHERE flg_pagamento<>1 OR flg_pagamento IS NULL ) AS bolt WHERE bolt.data_referencia_pagamento<=bolt.data_vencimento_comparar)
                    )  GROUP BY  tlio.cod_oficina, tlio.cod_evento
        ) AS aguardpag ON aguardpag.cod_evento = toa.cod_evento AND aguardpag.cod_oficina=toa.cod_oficina 
        WHERE
        toa.cod_evento = " . COD_EVENTO . " and toa.status='ATIVADA' 
        ORDER BY toa.ordem, toa.status,toa.titulo
        ";
        // (toa.tipo='Minicurso') AND 
        #echo $var_sql;
        #Fim da definição do comando SQL
        #Executando a consulta no banco SQL
        $RS = $Conexao_db->Execute($var_sql);

        #Verificando se houve retorno de algum registro
        If (!$RS->EOF) {
            #Definindo cabeçalho da tabela dos resultados da consula
            /* Tabela reescrita: usa o mesmo estilo das demais do painel (.panel +
               table + .num) em vez de cor de fundo por celula e colunas
               espacadoras. A ultima coluna numerica mostra
               $Total_Vagas_Disponiveis -- antes estava rotulada "Não Pagos",
               que e outra coisa. */
            echo "<div class='panel' style='margin-top:10px;'>
                    <h2>Totais de inscritos por atividade</h2>
                    <table class='tbl-atividades'>
                    <thead>
                      <tr>
                        <th style='text-align:left;'>Atividade</th>
                        <th class='num'>Vagas</th>
                        <th class='num'>Inscritos</th>
                        <th class='num'>Pagos</th>
                        <th class='num'>Isentos</th>
                        <th class='num'>Vagas disp.</th>
                      </tr>
                    </thead>
                    <tbody>
                    ";

            #Efetuando loop para o visualização dos registros retornados pela consulta SQL
            While (!$RS->EOF) {
                
                
                #Adicionando nas variaveis, os valores dos campos retirados dos registros
                $cod_oficina = $RS->fields[0];
                $Titulo = $RS->fields[1];
                $numero_vagas = $RS->fields[2];
                $status = $RS->fields[3];
                $Total_Inscritos_Oficinas = $RS->fields[4];
                $Total_Inscritos_Pagos = $RS->fields[5];
                $Total_Inscritos_Isentos = $RS->fields[6];
                $Total_Inscritos_Of_por_CPF = $RS->fields[7];
                $total_inscricoes_boleto_aguardando_pag_por_oficina = $RS->fields[8];
                $ordem = $RS->fields[9];
                $numero_oficial = $RS->fields[10];

                if(($cod_oficina!=98)and($cod_oficina!=99)){
                    $totalAtividades++;
                $Total_Vagas_Disponiveis = $numero_vagas - $Total_Inscritos_Pagos -$Total_Inscritos_Isentos;
                #Definindo tabela com resultado

                $total_inscritos_em_oficinas = $total_inscritos_em_oficinas + $Total_Inscritos_Oficinas;
                $total_inscritos_em_oficinas_pagos = $total_inscritos_em_oficinas_pagos + $Total_Inscritos_Pagos;
                $total_inscritos_em_oficinas_isentos = $total_inscritos_em_oficinas_isentos + $Total_Inscritos_Isentos;
                $total_inscritos_em_oficinas_vagas = $total_inscritos_em_oficinas_vagas + $numero_vagas;
                $total_inscritos_em_oficinas_vagas_disp = $total_inscritos_em_oficinas_vagas_disp + $Total_Vagas_Disponiveis;
                $total_geral_inscricoes_boleto_aguardando_pag_por_oficina = $total_geral_inscricoes_boleto_aguardando_pag_por_oficina + $total_inscricoes_boleto_aguardando_pag_por_oficina;
                #$Total_Vagas_Disponiveis = $RS->fields[1]-$RS->fields[2];


                $esgotada = ($Total_Vagas_Disponiveis <= 0);
                echo "<tr>
                    <td>
                      <a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cod_oficina=" . $cod_oficina . "'>" . $Titulo . "</a>
                      <small style='color:#9ca3af;'> M-" . $numero_oficial . "</small>
                    </td>
                    <td class='num'>" . $numero_vagas . "</td>
                    <td class='num'>" . $Total_Inscritos_Of_por_CPF . "</td>
                    <td class='num'>" . $Total_Inscritos_Pagos . "</td>
                    <td class='num'>" . $Total_Inscritos_Isentos . "</td>
                    <td class='num'" . ($esgotada ? " style='color:#dc3545;font-weight:600;'" : "") . ">" . $Total_Vagas_Disponiveis . "</td>
                    </tr>
                    ";


                    #Movendo para o proximo registro
                }
                    $RS->MoveNext();

            }
            echo "</tbody>
                  <tfoot>
                    <tr>
                      <td><b>Totais</b> <small style='color:#9ca3af;'>(" . $totalAtividades . " atividade" . ($totalAtividades == 1 ? "" : "s") . ")</small></td>
                      <td class='num'><b>" . $total_inscritos_em_oficinas_vagas . "</b></td>
                      <td class='num'><b>" . $total_inscritos_em_oficinas . "</b></td>
                      <td class='num'><b>" . $total_inscritos_em_oficinas_pagos . "</b></td>
                      <td class='num'><b>" . $total_inscritos_em_oficinas_isentos . "</b></td>
                      <td class='num'><b>" . $total_inscritos_em_oficinas_vagas_disp . "</b></td>
                    </tr>
                  </tfoot>
                  </table>
                  </div>
                    ";
        }
    }
}
#------------------------------------------------------
echo '<div class="grid">';
echo '<div class="panel">';
## Consulta Pessoas Registrando Por Dia ##
if ($objEvConf->getRegistroDiario()){
    $var_sql = "SELECT dia, COUNT(DISTINCT cod_inscrito) AS total_pessoas
    FROM tab_registro_atividade
    WHERE cod_inscrito IS NOT NULL
    AND STATUS = 'ATIVADA' and cod_evento =" . COD_EVENTO."
    GROUP BY dia ORDER BY dia";
 
    $RS = $Conexao_db->Execute($var_sql);
    If (!$RS->EOF) {
        echo "<table><tr><td colspan='10'><b>Pessoas Registrando Por Dia:</b></td></tr>";
        $varSomaTotal = "";
        While (!$RS->EOF) {
            $total_Registro_dia = $RS->fields[1];
            $dia = formatDateView($RS->fields[0]);
            #Definindo tabela com resultado
            $varSomaTotalDia = $varSomaTotalDia + $total_Registro_dia;
            $varGraficoRegistroPorDia .= "['" . $dia. "'," . $total_Registro_dia . "],";

            #Movendo para o proximo registro
            $RS->MoveNext();
        }
        echo "</table>";
    }
    //echo $varGraficoRegistroPorDia;
    ?>
    <html>
        <head>
            <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
            <script type="text/javascript">
                google.charts.load("current", {packages: ["corechart"]});
                google.charts.setOnLoadCallback(drawChart);
                function drawChart() {
                    var data = google.visualization.arrayToDataTable([
                        ['Task', ''],
                        <?php echo  $varGraficoRegistroPorDia ?>
                    ]);

                    var options = {
                        title: 'Pessoas Registrando Por Dia',
                        pieHole: 0.4,
                    };

                    var chart = new google.visualization.ColumnChart(document.getElementById('graficoRegistroPorDia'));
                    chart.draw(data, options);
                }
            </script>
        </head>
        <body>
            <div id="graficoRegistroPorDia" style="width:450px; height:240px;"></div>
        </body>
    </html>
    <?php
}
#------------------------------------------------------
## Consulta Inscritos por Dia ##
$var_sql = "SELECT dia, total_inscritos FROM `vw_inscritos_por_dia_evento` WHERE cod_evento =" . COD_EVENTO." order by dia";
$RS = $Conexao_db->Execute($var_sql);
If (!$RS->EOF) {
    // echo "<table><tr><td colspan='10'><b>Inscritos por dia:</b></td></tr>";
    $varSomaTotal = "";
    While (!$RS->EOF) {
        $total_inscritos_dia = $RS->fields[1];
        $dia = formatDateView($RS->fields[0]);
        #Definindo tabela com resultado
        $varSomaTotalDia = $varSomaTotalDia + $total_inscritos_dia;
        $varGraficoInscritosPorDia .= "['" . $dia. "'," . $total_inscritos_dia . "],";

        #Movendo para o proximo registro
        $RS->MoveNext();
    }
    // echo "</table>";
}
//echo $varGraficoInscritosPorDia;
?>
<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load("current", {packages: ["corechart"]});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Task', '<?php echo $varSomaTotalDia ?>'],
                    <?php echo  $varGraficoInscritosPorDia ?>
                ]);

                var options = {
                    title: 'Inscritos por Dia',
                    pieHole: 0.4,
                };

                var chart = new google.visualization.ColumnChart(document.getElementById('graficoPorDia'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id="graficoPorDia" style="width:450px; height:240px;"></div>
    </body>
</html>
</div>
<div class="panel">
<?php
#------------------------------------------------------
## Login por Dia ##
$var_sql = "SELECT DATE(data_inclusao) AS DIA, COUNT(*) as total_logins FROM sis_log_acesso
WHERE sucesso = 1 AND  cod_evento =" . COD_EVENTO."
GROUP BY  DATE(data_inclusao) ORDER BY DATE(data_inclusao)";
$RS = $Conexao_db->Execute($var_sql);
If (!$RS->EOF) {
    // echo "<table><tr><td colspan='10'><b>Login por dia:</b></td></tr>";
    $varSomaTotal = "";
    While (!$RS->EOF) {
        $total_Login_dia = $RS->fields[1];
        $dia = formatDateView($RS->fields[0]);
        #Definindo tabela com resultado
        $varSomaTotalDia = $varSomaTotalDia + $total_Login_dia;
        $varGraficoLoginPorDia .= "['" . $dia. "'," . $total_Login_dia . "],";

        #Movendo para o proximo registro
        $RS->MoveNext();
    }
    // echo "</table><br>";
}
//echo $varGraficoLoginPorDia;
?>
<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load("current", {packages: ["corechart"]});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Task', '<?php echo $varSomaTotalDia ?>'],
                    <?php echo  $varGraficoLoginPorDia ?>
                ]);

                var options = {
                    title: 'Login por Dia.',
                    pieHole: 0.4,
                };

                var chart = new google.visualization.ColumnChart(document.getElementById('graficoLoginPorDia'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id="graficoLoginPorDia" style="width:450px; height:240px;"></div>
    </body>
</html>
</div>
</div>
<?php
echo '<div class="grid">';
echo '<div class="panel">';
#------------------------------------------------------
## Consulta Login por Estado ##
#Definindo comando SQL
$var_sql = "
                 SELECT COALESCE(Count(ti.cod_inscrito),0) as Total_Inscritos_Estado, ti.estado
                   FROM tab_inscritos ti
                   WHERE (ti.cod_evento  =" . COD_EVENTO . ")and
                         (ti.status='ATIVADA')
                   Group by ti.estado order by Count(ti.cod_inscrito) desc, ti.estado
               ";

#echo $var_sql;
#Fim da definição do comando SQL
#Executando a consulta no banco SQL
$RS = $Conexao_db->Execute($var_sql);

#Verificando se houve retorno de algum registro
If (!$RS->EOF) {
    #Definindo cabeçalho da tabela dos resultados da consula
    // echo "<table><tr><td colspan='10'><b>Inscritos por estado:</b></td></tr>";
    #Efetuando loop para o visualização dos registros retornados pela consulta SQL

    $ii == 0;
    $varSomaTotal = 0;
    While (!$RS->EOF) {

        #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $Total_Inscritos_Estado = $RS->fields[0];
        $Estado = $RS->fields[1];
        #Definindo tabela com resultado
        @$varSomaTotal = $varSomaTotal + $Total_Inscritos_Estado;
        if($Estado=='-1' or $Estado==''){
            $Estado = "Não informou";
        }
        if ($ii == 10)
            $ii = 0;

        // if ($ii == 0)
        //     echo "<tr valign='top'>";

        // echo "<td>
        //       | <a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&estado=" . $Estado . "'>
        //         " . $Total_Inscritos_Estado . "
        //         ->
        //         " . $Estado . "</a>
        //       </td>
        //      ";
        $varGraficoInscritosPorEstado .= "['" . $Estado . "'," . $Total_Inscritos_Estado . "],";

        $ii = $ii + 1;
        // if ($ii == 10)
        //     echo "</tr>";



        #Movendo para o proximo registro
        $RS->MoveNext();
    }
    // echo "</table><br>";
}
?>
<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load("current", {packages: ["corechart"]});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Task', '<?php echo $varSomaTotal ?>'],
                    <?php echo  $varGraficoInscritosPorEstado ?>
                ]);

                var options = {
                    title: 'Inscritos por Estado',
                    pieHole: 0.4,
                };

                var chart = new google.visualization.ColumnChart(document.getElementById('graficoPorEstado'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id="graficoPorEstado" style="width:450px; height:240px;"></div>
    </body>
</html>
</div>
<div class="panel">
<?php
#------------------------------------------------------
## Consulta Inscritos por Estado ##
#Definindo comando SQL
$var_sql = ""
        . "SELECT total, cidade,estado "
        . "FROM vw_qtd_inscritos_cidade "
        . "WHERE estado='PR' and cod_evento=" . COD_EVENTO . " order by total desc, cidade";

#echo $var_sql;
#Fim da definição do comando SQL
#Executando a consulta no banco SQL
$RS = $Conexao_db->Execute($var_sql);

#Verificando se houve retorno de algum registro
If (!$RS->EOF) {
    #Definindo cabeçalho da tabela dos resultados da consula
    // echo "<table><tr><td colspan='10'><b>Inscritos por cidade do Paraná:</b></td></tr>";
    #Efetuando loop para o visualização dos registros retornados pela consulta SQL

    $ii == 0;
    $varSomaTotal = 0;
    While (!$RS->EOF) {

        #Adicionando nas variaveis, os valores dos campos retirados dos registros
        $total = $RS->fields[0];
        $cidade = $RS->fields[1];
        #Definindo tabela com resultado
        @$varSomaTotal = $varSomaTotal + $total;
        if($cidade=='-1' or $cidade==''){
            $cidade = "Não informou";
        }
        if ($ii == 10)
            $ii = 0;

        // if ($ii == 0)
        //     echo "<tr valign='top'>";

        // echo "<td>
        //       | <a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cidade=" . $cidade . "'>
        //         " . $total . "
        //         ->" . $cidade . "</a>
        //       </td>
        //      ";
        $varGraficoInscritosPorCidade .= "['" . $cidade . "'," . $total. "],";

        $totalCidades++;
        $ii = $ii + 1;
        // if ($ii == 10)
        //     echo "</tr>";



        #Movendo para o proximo registro
        $RS->MoveNext();
    }
    // echo "</table>";
    // echo "<br>Total de cidades do Paraná:.".$totalCidades."<br>";
}
?>
<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load("current", {packages: ["corechart"]});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Task', '<?php echo $varSomaTotal ?>'],
                    <?php echo  $varGraficoInscritosPorCidade ?>
                ]);

                var options = {
                    title: 'Inscritos por Cidade do Paraná',
                    pieHole: 0.4,
                };

                var chart = new google.visualization.ColumnChart(document.getElementById('graficoCidadePr'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id="graficoCidadePr" style="width:450px; height:240px;"></div>
    </body>
</html>
</div>
</div>
<div class="grid">
<div class="panel">
<?php
#------------------------------------------------------
## Consulta Inscritos por Regional ##
// echo "<br><b>Detalhes de inscritos por Reginal do Paraná:</b><br>";
// echo "De acordo com a cidade selecionada pelo inscrito<br>";
try {
    $objG = new Grafico();
    $objG->carregarListaTotalInscritosPorRegionalPR($debug);
    foreach ($objG->getListaTotalInscritosPorRegionalPR() as $objL) {
        $varGraficoInscritosPorRegionalPR .= "['". $objL->getNome() . "'," . $objL->getTotal(). "],";
        $totalInscritosPR = $totalInscritosPR+$objL->getTotal();
    }
    // echo "Total inscrito no Paraná: ".$totalInscritosPR."<br>";
} catch (Exception $exc) {
    $exc->getMessage()."<br>";
}
?>
<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load("current", {packages: ["corechart"]});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Task', '<?php echo $totalInscritosPR ?>'],
                    <?php echo  $varGraficoInscritosPorRegionalPR ?>
                ]);

                var options = {
                    title: 'Inscritos por Regional do Paraná',
                    pieHole: 0.4,
                };

                var chart = new google.visualization.ColumnChart(document.getElementById('graficoInscritosPorRegionalPR'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id="graficoInscritosPorRegionalPR" style="width: 400px; height: 240px;"></div>
    </body>
</html>
</div>
<div class="panel">
<?php
#------------------------------------------------------
## Consulta Inscritos por Regional ##
// echo "<br><b>Detalhes de inscritos por Macrorregiões do Paraná:</b><br>";
// echo "De acordo com a cidade selecionada pelo inscrito<br>";
try {
    $objG = new Grafico();
    $objG->carregarListaTotalInscritosPorMacrorregiaoPR($debug);
    $varSomaTotal = 0;
    foreach ($objG->getListaTotalInscritosPorMacrorregiaoPR() as $objL) {
        $varGraficoInscritosPorMacrorregiaoPR .= "['" . $objL->getMacrorregiao() . "'," . $objL->getTotal(). "],";
        @$varSomaTotal = $varSomaTotal + $objL->getTotal();
        /**
         * Listagem para filtro
        echo "<td>
              | <a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cidade=" . $cidade . "'>
                " . $total . "
                ->" . $cidade . "</a>
              </td>
             ";   
        */
    }
} catch (Exception $exc) {
    $exc->getMessage()."<br>";
}
?>
<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load("current", {packages: ["corechart"]});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Task', '<?php echo $varSomaTotal ?>'],
                    <?php echo  $varGraficoInscritosPorMacrorregiaoPR ?>
                ]);

                var options = {
                    title: 'Inscritos por Macrorregiões do Paraná',
                    pieHole: 0.4,
                };

                var chart = new google.visualization.ColumnChart(document.getElementById('graficoInscritosPorMacrorregiaoPR'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id="graficoInscritosPorMacrorregiaoPR" style="width:450px; height:240px;"></div>
    </body>
</html>
</div>
</div>
<?php if ($naoMostrar){ ?>
<div class="grid">
<div class="panel">
<?php
#------------------------------------------------------
## Consulta Inscritos por Instituição ##
// echo "<br><b>Detalhes de inscritos por Tipo de Instituição:</b><br>";
// echo "De acordo instituição informada pelo inscrito<br>";
try {
    $objG = new Grafico();
    $objG->carregarListaTotalInscritosPorTipoInstituicao($debug);
    $varSomaTotal = 0;
    foreach ($objG->getListaTotalInscritosPorTipoInstituicao() as $objL) {
        $varGraficoInscritosPorTipoInstituicao .= "['" . $objL->getTipoInstituicao() . "'," . $objL->getTotal(). "],";
        @$varSomaTotal = $varSomaTotal + $objL->getTotal();
        /**
         * Listagem para filtro
        echo "<td>
              | <a class='link' href='index.php?content=view/admin/consulta-dados-congresso-listagem-inscritos.php&cidade=" . $cidade . "'>
                " . $total . "
                ->" . $cidade . "</a>
              </td>
             ";   
        */
    }
} catch (Exception $exc) {
    $exc->getMessage()."<br>";
}
?>
<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load("current", {packages: ["corechart"]});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Task', '<?php echo $varSomaTotal ?>'],
                    <?php echo  $varGraficoInscritosPorTipoInstituicao ?>
                ]);

                var options = {
                    title: 'Inscritos por Tipo Instituição',
                    pieHole: 0.4,
                };

                var chart = new google.visualization.ColumnChart(document.getElementById('graficoInscritosPorTipoInstituicao'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id="graficoInscritosPorTipoInstituicao" style="width:450px; height:240px;"></div>
    </body>
</html>
</div>
<div class="panel">
<?php
#------------------------------------------------------
## Consulta Inscritos por Profissão de Atuação ##
// echo "<br><b>Detalhes de inscritos por Tipo de Profissão de Atuação:</b><br>";
// echo "De acordo instituição informada pelo inscrito<br>";
try {
    $objG = new Grafico();
    $objG->carregarListaTotalInscritosPorProfissaoAtuacao($debug);
    $varSomaTotal = 0;
    foreach ($objG->getListaTotalInscritosPorProfissaoAtuacao() as $objL) {
        $varGraficoInscritosPorProfissaoAtuacao .= "['" . $objL->getProfissaoAtuacao() . "'," . $objL->getTotal(). "],";
        @$varSomaTotal = $varSomaTotal + $objL->getTotal();
    }
} catch (Exception $exc) {
    $exc->getMessage()."<br>";
}
?>
<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load("current", {packages: ["corechart"]});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Task', '<?php echo $varSomaTotal ?>'],
                    <?php echo  $varGraficoInscritosPorProfissaoAtuacao ?>
                ]);

                var options = {
                    title: 'Inscritos por Tipo Profissão de Atuação',
                    pieHole: 0.4,
                };

                var chart = new google.visualization.ColumnChart(document.getElementById('graficoInscritosPorProfissaoAtuacao'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id="graficoInscritosPorProfissaoAtuacao" style="width:450px; height:240px;"></div>
    </body>
</html>
</div>
</div>
<?php } ?>

<?php if ($naoMostrar){ ?>
<div class="grid">
<div class="panel">
<?php
#------------------------------------------------------
### Consulta Inscritos por Tipo/Categoria de Inscrito ##
try {
    $objG = new Grafico();
    $objG->carregarListaTotalInscritosPorTipoInscricao($debug);
    $varSomaTotal = 0;
    foreach ($objG->getListaTotalInscritosPorTipoInscricao() as $objL) {
        $varGraficoInscritosPorTipoInscricao .= "['" . $objL->getTipoInscricao() . "'," . $objL->getTotal(). "],";
        @$varSomaTotal = $varSomaTotal + $objL->getTotal();
    }
} catch (Exception $exc) {
    $exc->getMessage()."<br>";
}
?>
<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load("current", {packages: ["corechart"]});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Task', '<?php echo $varSomaTotal ?>'],
                    <?php echo  $varGraficoInscritosPorTipoInscricao ?>
                ]);
                var options = { title: 'Inscritos por Tipo Inscrito', pieHole: 0.4 };
                var chart = new google.visualization.ColumnChart(document.getElementById('graficoInscritosPorTipoInscricao'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id="graficoInscritosPorTipoInscricao" style="width:450px; height:240px;"></div>
    </body>
</html>
</div>
</div>
<?php } ?>

<?php if ($objEvConf->getTrabSubmissao()) { ?>
<div class="grid">

<?php if ($objEvConf->getTrabEixoTematico()) { ?>
<div class="panel">
<?php
    try {
        $objG = new Grafico();
        $objG->carregarListaTotalTrabalhosPorAreaTematica($debug);
        $varSomaTotal = "";
        foreach ($objG->getListaTotalTrabalhosPorAreaTematica() as $objL) {
            $varGraficoTrabalhosPorAreaTematica .= "['" . $objL->getTitulo() . "'," . $objL->getTotal(). "],";
            @$varSomaTotal = $varSomaTotal + $objL->getTotal();
        }
    } catch (Exception $exc) {
        $exc->getMessage()."<br>";
    }
?>
<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load("current", {packages: ["corechart"]});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Task', '<?php echo $varSomaTotal ?>'],
                    <?php echo  $varGraficoTrabalhosPorAreaTematica ?>
                ]);
                var options = { title: 'Trabalhos por Área Tematica', pieHole: 0.4 };
                var chart = new google.visualization.ColumnChart(document.getElementById('graficoTrabalhosPorAreaTematica'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id="graficoTrabalhosPorAreaTematica" style="width:450px; height:240px;"></div>
    </body>
</html>
</div>
<?php } ?>

<?php if ($objEvConf->getTrabFormatoTexto()) { ?>
<div class="panel">
<?php
    try {
        $objG = new Grafico();
        $objG->carregarListaTotalTrabalhosPorFormatoTexto($debug);
        $varSomaTotal = "";
        foreach ($objG->getListaTotalTrabalhosPorFormatoTexto() as $objL) {
            $varGraficoTrabalhosPorFormatoTexto .= "['" . $objL->getFormatoTexto() . "'," . $objL->getTotal(). "],";
            @$varSomaTotal = $varSomaTotal + $objL->getTotal();
        }
    } catch (Exception $exc) {
        $exc->getMessage()."<br>";
    }
?>
<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load("current", {packages: ["corechart"]});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Task', '<?php echo $varSomaTotal ?>'],
                    <?php echo  $varGraficoTrabalhosPorFormatoTexto ?>
                ]);
                var options = { title: 'Trabalhos por Formato de Texto', pieHole: 0.4 };
                var chart = new google.visualization.ColumnChart(document.getElementById('graficoTrabalhosPorFormatoTexto'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id="graficoTrabalhosPorFormatoTexto" style="width:450px; height:240px;"></div>
    </body>
</html>
</div>
<?php } ?>

<?php if ($objEvConf->getTrabPremiacao()) { ?>
<div class="panel">
<?php
    echo "<br><b>Detalhes de Trabalhos por Premiação:</b><br>";
    echo "De acordo Premiação informada no trabalho<br>";
    try {
        $objG = new Grafico();
        $objG->carregarListaTotalTrabalhosPorPremiacao($debug);
        $varSomaTotal = "";
        foreach ($objG->getListaTotalTrabalhosPorPremiacao() as $objL) {
            $varGraficoTrabalhosPorPremiacao .= "['" . $objL->getPremiacao() . "'," . $objL->getTotal(). "],";
            @$varSomaTotal = $varSomaTotal + $objL->getTotal();
        }
    } catch (Exception $exc) {
        $exc->getMessage()."<br>";
    }
?>
<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load("current", {packages: ["corechart"]});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Task', '<?php echo $varSomaTotal ?>'],
                    <?php echo  $varGraficoTrabalhosPorPremiacao ?>
                ]);
                var options = { title: 'Trabalhos por Premiação', pieHole: 0.4 };
                var chart = new google.visualization.ColumnChart(document.getElementById('graficoTrabalhosPorPremiacao'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id="graficoTrabalhosPorPremiacao" style="width:450px; height:240px;"></div>
    </body>
</html>
</div>
<?php } ?>

<?php if ($objEvConf->getTrabInstituicao()) { ?>
<div class="panel">
<?php
    echo "<br><b>Detalhes de Trabalhos por Instituição:</b><br>";
    echo "De acordo Instituição informada no trabalho<br>";
    try {
        $objG = new Grafico();
        $objG->carregarListaTotalTrabalhosPorInstituicao($debug);
        $varSomaTotal = "";
        foreach ($objG->getListaTotalTrabalhosPorInstituicao() as $objL) {
            $varGraficoTrabalhosPorInstituicao .= "['" . $objL->getInstituicaoTrabalho() . "'," . $objL->getTotal(). "],";
            @$varSomaTotal = $varSomaTotal + $objL->getTotal();
        }
    } catch (Exception $exc) {
        $exc->getMessage()."<br>";
    }
?>
<html>
    <head>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            google.charts.load("current", {packages: ["corechart"]});
            google.charts.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Task', '<?php echo $varSomaTotal ?>'],
                    <?php echo  $varGraficoTrabalhosPorInstituicao ?>
                ]);
                var options = { title: 'Trabalhos por Instituição', pieHole: 0.4 };
                var chart = new google.visualization.ColumnChart(document.getElementById('graficoTrabalhosPorInstituicao'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <div id="graficoTrabalhosPorInstituicao" style="width:450px; height:240px;"></div>
    </body>
</html>
</div>
<?php } ?>

</div><!-- grid trabalhos -->
<?php } ?>

</DIV>
</div></div></div>
