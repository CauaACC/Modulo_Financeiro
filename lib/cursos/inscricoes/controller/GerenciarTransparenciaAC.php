<?php

class GerenciarTransparenciaAC
{
    public function __construct()
    {
        if (!defined('FWA_API_EXEC')) { FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL); }
        // Inicialização da classe
    }

    public function localizarPagamentos($fws_, $debug = false)
    {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset="utf-8">
          <title>Disparar Formulário com Dados do Chatwoot</title>
          <script>
                function enviarFormulario() {
                document.getElementById("formAuto").submit();
                }
          </script>
        </head>
        <body>
          <p id="info">Carregando integração...</p>
          <button id="btnEnviar" onclick="enviarFormulario()" disabled>🔍 Localizar inscrições</button>
        </body>
        </html>
        <?php
    }
/**
     * Mapeia e sanitiza o array de filtros ($fws_) vindo da view para o array de 
     * condições ($condicoes) usado pelo DAO.
     * * @param array $fws_ Array de filtros passados via GET/POST.
     * @return array Array de condições limpas e mapeadas.
     */
    private function montarCondicoesPagamentos($fws_) {
        $condicoes = [];

        // 1. Campos de Busca e Tipo
        $condicoes['tipo_busca']  = $fws_['tipo_busca'] ?? 'Projeto';
        // Remove espaços desnecessários e HTML antes de passar para o DAO
        $condicoes['termo_busca'] = trim(strip_tags($fws_['termo_busca'] ?? '')); 

        // 2. Filtros de Data de Vencimento (pg.vencimento)
        // Renomeado para data_de_vencimento e data_ate_vencimento para clareza
        $condicoes['data_de_vencimento']  = trim($fws_['data_de_vencimento'] ?? '');
        $condicoes['data_ate_vencimento'] = trim($fws_['data_ate_vencimento'] ?? '');

        // 3. Filtros de Período do Projeto (pr.data_inicio / pr.data_fim)
        $condicoes['data_de_projeto']     = trim($fws_['data_de_projeto'] ?? '');
        $condicoes['data_ate_projeto']    = trim($fws_['data_ate_projeto'] ?? '');

        // 4. Tipo de Pagamento (pg.tipo_rpa)
        $condicoes['tipo_pagamento']      = trim($fws_['tipo_pagamento'] ?? '');
        
        // 5. Ordenação
        $condicoes['ordenar']             = $fws_['ordenar'] ?? 'vencimento_desc';

        // O campo 'listar' é usado apenas na view (localizar_pagamentos.php) para controlar a exibição.
        
        return $condicoes;
    }
    /**
     * Carrega a lista de pagamentos do DAO e gera as linhas da tabela HTML.
     * * @param array $condicoes Array de condições de filtro (fws_).
     * @param bool $debug Indica se o modo debug deve ser ativado no DAO.
     * @return void A função imprime diretamente as linhas HTML para o <tbody>.
     */
    public function carregarListaPagamentos($condicoes, $debug = false) {
        try {
            $condicoes['gestor'] = $condicoes['gestor'] ?? WB_GRUPO;

            $objDAO = new TransparenciaDAO();
            // Chama o DAO para buscar os dados
            $lista_pagamentos = $objDAO->carregarListaPagamentos($condicoes, $debug); 
            
            if (!$lista_pagamentos || !is_array($lista_pagamentos)) {
                // Mensagem quando não há resultados (colspan 7 para as 7 colunas)
                echo "<tr><td colspan=\"7\" class=\"text-center\">Nenhum pagamento encontrado com os filtros aplicados.</td></tr>";
                return;
            }

            // A contagem de registros será exibida antes da tabela
            $total_registros = count($lista_pagamentos);
            
            // Loop para desenhar as linhas da tabela (mantendo a estrutura de 7 colunas)
            foreach ($lista_pagamentos as $pg) {
               
                // 2. Formatação do Valor Líquido (Assume que está em formato brasileiro no DB, ex: "1.234,56")
                $valor_limpo = str_replace(',', '.', str_replace('.', '', $pg['valor_liquido']));
                $valor_formatado = 'R$ ' . number_format((float)$valor_limpo, 2, ',', '.');
                
                // 3. Formatação do Tipo de Pagamento (fornecedor)
                $tipo_pagamento_completo = $pg['tipo_pagamento'] == 'RSP' 
                                            ? 'Recibo de solicitação de pagamento' 
                                            : ($pg['tipo_pagamento'] == 'NF' ? 'Nota Fiscal' : $pg['tipo_pagamento']);
                
                echo '<tr>';
                echo '<td>';
                    echo $pg['numero_projeto'];
                    
                    echo '<br><a href="'.WB_URL_VIEW.'?content=view/transparencia/localizar_projetos.php&numero_projeto='.$pg['numero_projeto'].'">detalhes do projeto</a><br>';

                echo '</td>';
                echo '<td>'.$pg['e_protocolo'].'</td>';
                echo '<td>';
                    echo $pg['proposta'];

                echo '</td>';
                echo '<td>';
                echo $pg['nome_fornecedor'];
                echo $pg['cnpj_fornecedor'] ? ' <br><small style="font-weight: normal;">CNPJ: '.$pg['cnpj_fornecedor'].'</small>' : '';
                echo '</td>'; 
                // echo '<td>'.$pg['cnpj_fornecedor'].'</td>';
                echo '<td>'.$pg['servico'].'</td>';
                echo '<td>'.formatDateView($pg['vencimento']).'</td>';
                echo '<td class="text-end">'.$valor_formatado.'</td>';
                echo '<td>'.$pg['forma_pagamento'].'</td>';
                echo '</tr>';
            }

        } catch (Exception $exc) {
            // Em caso de erro, exibe a mensagem de forma centralizada
            echo "<tr><td colspan=\"7\" class=\"text-center\">Erro ao listar pagamentos: " . $exc->getMessage() . "</td></tr>";
        }
    }
    public function carregarCabecalhoTabelaPagamentos() {
    ?>
        <tr>
        <th scope="col" style="min-width: 50px;">Cód.Projeto</th>
        <th scope="col" style="min-width: 50px;">E-protocolo</th>
        <th scope="col" style="min-width: 200px;">Projeto</th>
        <th scope="col" style="min-width: 200px;">Fornecedor</th>
        <th scope="col">Serviço</th>
        <th scope="col">Valor</th>
        <th scope="col" class="text-end" style="min-width: 120px;">Forma Pagamento</th>
        </tr>
    <?php    
    }
    /**
     * Carrega a lista de pagamentos do DAO e gera as linhas da tabela HTML.
     * * @param array $filtros Array de condições de filtro (fws_).
     * @param bool $debug Indica se o modo debug deve ser ativado no DAO.
     * @return void A função imprime diretamente as linhas HTML para o <tbody>.
     */
    public function carregarListaProjetos($filtros, $debug = false) {
        try {
            $filtros['gestor'] = $filtros['gestor'] ?? WB_GRUPO;

            $objDAO = new TransparenciaDAO();
            // Chama o DAO para buscar os dados
            $lista_projetos = $objDAO->carregarListaProjetos($filtros, $debug);
            
            if (!$lista_projetos || !is_array($lista_projetos)) {
                // Mensagem quando não há resultados (colspan 7 para as 7 colunas)
                echo "<tr><td colspan=\"7\" class=\"text-center\">Nenhum projeto encontrado com os filtros aplicados.</td></tr>";
                return;
            }

            // A contagem de registros será exibida antes da tabela
            $total_registros = count($lista_projetos);
            
            // Loop para desenhar as linhas da tabela (mantendo a estrutura de 7 colunas)
            foreach ($lista_projetos as $pg) {
                if ($pg['numero_projeto']!=''){
                    echo '<tr>';
                    echo '<td>'.$pg['numero_projeto'].'</td>';
                    echo '<td>'.$pg['e_protocolo'].'</td>';
                    echo '<td>';
                    echo $pg['proposta'];

                    // converte 008/2024 → 008-2024
                    $num_id = str_replace('/', '-', trim($pg['numero_projeto']));
                    $linkPagamentos = WB_URL_VIEW."?content=view/transparencia/localizar_pagamentos.php&numero_projeto=" . urlencode($num_id);
                    echo '<br><a href="'.$linkPagamentos.'" style="margin-top:5px;">Ver pagamentos</a>';

                    echo '<br>'.$this->listarArquivosProjeto($pg['numero_projeto']);

                    echo '</td>';
                    echo '<td>'.$pg['coordenador'].'</td>';
                    echo '<td>'.formatDateView($pg['data_inicio']).'</td>';
                    echo '<td>'.formatDateView($pg['data_fim']).'</td>';
                    echo '</tr>';
                }
            }

        } catch (Exception $exc) {
            // Em caso de erro, exibe a mensagem de forma centralizada
            echo "<tr><td colspan=\"7\" class=\"text-center\">Erro ao listar projetos: " . $exc->getMessage() . "</td></tr>";
        }
    }
    public function carregarCabecalhoTabelaProjetos() {
    ?>
        <tr>
        <th scope="col" style="min-width: 50px;">Cód.Projeto</th>
        <th scope="col" style="min-width: 50px;">E-protocolo</th>
        <th scope="col" style="min-width: 200px;">Projeto</th>
        <th scope="col" style="min-width: 200px;">Coordenador</th>
        <th scope="col">Data Inicio</th>
        <th scope="col">Data Fim</th>
        </tr>
    <?php
    }

    function listarArquivosProjeto($numero_projeto) {
        // caminho físico real no servidor
        $basePath = __DIR__.'/../resources/public/hutec/';

        // link público acessível via navegador
        $baseUrl  = WB_URL_SITE."/resources/public/hutec/";
        $baseUrl  = '/hutec/atividades/inscricoes/resources/public/hutec/';

        // converte 008/2024 → 008-2024
        $num_id = str_replace('/', '-', trim($numero_projeto));
        // $linkPagamentos = "/index.php?content=view/transparencia/localizar_pagamentos.php&projeto=" . urlencode($numero_projeto);
        // echo '<br><a href="'.$linkPagamentos.'" style="margin-top:5px;">Ver pagamentos</a>';

        $tipos = [
        'acordo-ass', 'acordo_ass',
        'acordo-sem-ass', 'acordo_sem_ass',
        'plano-ass', 'plano_ass',
        'plano-sem-ass', 'plano_sem_ass',
        'acordo-plano-ass', 'acordo_plano_ass',
        'acordo-plano-sem-ass', 'acordo_plano_sem_ass',
        'plano-acordo-ass', 'plano_acordo_ass',
        'plano-acordo-sem-ass', 'plano_acordo_sem_ass',
        'aditivo-ass', 'aditivo_ass',
        'aditivo-sem-ass', 'aditivo_sem_ass'
        ];

        $links = [];
        
        foreach ($tipos as $tipo) {
            $padroes = [
                "{$num_id}-{$tipo}.pdf",
                "{$num_id}_{$tipo}.pdf"
            ];

            foreach ($padroes as $arquivo) {
                $caminho = $basePath . $num_id .'/'.$arquivo;
                // echo $caminho.'<br>';
                if (file_exists($caminho)) {
                    // converte o nome do tipo, deixando "ASS" minúsculo
                    // $label = strtoupper($tipo);
                    // $label = str_ireplace(['-ASS', '_ASS'], '-ass', $label);
                    // $label = str_ireplace(['-SEM-ASS', '_SEM_ASS'], '-sem-ass', $label);  
                    $label = strtolower($tipo);                  
                    // echo "Arquivo encontrado: {$caminho}<br>";
                    $baseUrlView = $baseUrl.$num_id .'/';
                    $links[] = "<a href='{$baseUrlView}{$arquivo}' target='_blank'> <img src='{$baseUrl}../img/pdf.gif' width='10'> ".$label."</a>";
                }
            }
        }

        return $links ? implode('<br>', $links) : '<em>Sem arquivos</em>';
    }
}