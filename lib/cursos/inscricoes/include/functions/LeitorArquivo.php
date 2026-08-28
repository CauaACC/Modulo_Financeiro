<?php
/**
 * @see esta classes possui metodos para executar a manipula��o de um arquivo do tipo csv
 * possu� os m�todos carregarArquivo, setNmArquivo, imprimirTituloColunas, imprimirLinhas, 
 */
class LeitorArquivo {
    private $nmArquivo;
    private $totalLinhas;
    private $listaLinhas;
    /**
     * faz a leitura do arquivo informado em setNmArquivo, � necess�rio informar 
     * campinho completo para o arquivo
     * @param type $debug 
     */
    public function carregarArquivoCsv($arquivo, $debug=false){
        
        if(substr($arquivo, -3)=='csv'){
            $delimitador = ';';
            $cerca = '"';
            // Abrir arquivo para leitura
            $f = fopen($arquivo, 'r');
            
            if ($f) {
                // Enquanto nao terminar o arquivo
                $totalLinhas = 0;
                while (!feof($f)) {
                    // Ler uma linha do arquivo
                    $linha = fgetcsv($f, 0, $delimitador, $cerca);
                    if (!$linha) {
                        continue;
                    }
                    // Montar registro com valores indexados pelo cabecalho
                    //$registro = array_combine($cabecalho, $linha);
                    // Obtendo o nome
                    $colunas = '';
                    for ($i=0; $i < count($linha); $i++) {
                        $colunas .=limparString($linha[$i]) . ";";
                    }
                    $listaLinhas[] = $colunas . PHP_EOL;
                    $totalLinhas ++;
                }
//                echo $listaLinhas;
//                echo "<hr>".$totalLinhas."<br>";
                $this->setListaLinhas($listaLinhas);
                $this->setTotalLinhas($totalLinhas);
                fclose($f);
//                exit();
            }
        }else{
            $listaLinhas = file($arquivo);
            $this->setListaLinhas($listaLinhas);
            $totalLinhas = count($listaLinhas);
            $this->setTotalLinhas($totalLinhas);
        }
        echo ($debug? "<br> Contador.: " . $this->getTotalLinhas() . "<br>" : "");
    }
    public function carregarArquivo($arquivo, $debug=false){
        
        if(substr($arquivo, -3)=='csv'){
            $this->carregarArquivoCsv($arquivo,$debug);
        }else{
            $listaLinhas = file($arquivo);
            $this->setListaLinhas($listaLinhas);
            $totalLinhas = count($listaLinhas);
            $this->setTotalLinhas($totalLinhas);
        }
        echo ($debug? "<br> Contador.: " . $this->getTotalLinhas() . "<br>" : "");
    }

    /**
     * Leitor nativo do arquivo .xls do HUTEC (SpreadsheetML / Excel 2003 XML gerado pelo FastReport).
     * Diferente do CSV, este formato tem celulas mescladas e posicoes fixas por ss:Index.
     * Cada linha de detalhe (com data dd/mm/aaaa na coluna 1) e convertida para o mesmo formato
     * "24 colunas separadas por ;" que o restante do fluxo (carregarRetornoHutec) ja espera,
     * de forma que os indices [0][2][4][6][7][8][15][16][21][22] continuam validos sem mudar nada.
     * Linhas de cabecalho, secoes e subtotais (RECEITAS, DESPESAS, Saldo...) sao ignoradas.
     */
    public function carregarArquivoXls($arquivo, $debug = false) {
        $conteudo = file_get_contents($arquivo);

        // Roteia por formato (o botao "Exportar" do relatorio HUTEC gera dois tipos com a mesma extensao .xls):
        //  - OLE2/BIFF (xls binario "classico" do Excel, assinatura D0CF11E0) ou XLSX (ZIP, assinatura PK) -> PhpSpreadsheet
        //  - SpreadsheetML (XML do Excel 2003 / FastReport) -> parser nativo por regex (codigo abaixo)
        $ole2 = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
        if (substr($conteudo, 0, 2) === 'PK' || strncmp($conteudo, $ole2, 8) === 0) {
            $this->carregarArquivoXlsBinario($arquivo, $debug);
            return;
        }

        $listaLinhas = array();

        // Mesma regra do leitor binario: so entram detalhes do centro de custo das ENTRADAS de
        // pagamento (CURSOS E EVENTOS / 0106); taxas/despesas de outros centros ficam de fora.
        $ALVO_COD  = '0106';
        $ALVO_NOME = 'CURSOS E EVENTOS';
        $centroCod  = '';
        $centroNome = '';
        $temCentroCusto = false; // o balancete tem centros de custo; o extrato bancario nao tem

        if (preg_match_all('/<Row\b[^>]*>(.*?)<\/Row>/s', $conteudo, $rows)) {
            foreach ($rows[1] as $rowXml) {
                $colunas = $this->extrairColunasRowXls($rowXml);
                $c1 = isset($colunas[1]) ? trim($colunas[1]) : '';

                // Cabecalho de centro de custo: codigo de 4 digitos na col 1, nome na col 4 (ex.: "0106" | "CURSOS E EVENTOS")
                if (preg_match('/^\d{4}$/', $c1)) {
                    $centroCod  = $c1;
                    $centroNome = isset($colunas[4]) ? trim($colunas[4]) : '';
                    $temCentroCusto = true;
                    continue;
                }

                // Linha de detalhe: coluna 1 (ss:Index 1) e uma data no formato dd/mm/aaaa
                if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $c1)) {
                    continue;
                }

                // Balancete (tem centro de custo): so entram os pagamentos do centro CURSOS E EVENTOS / 0106.
                // Extrato bancario (sem centro de custo): nao filtra por centro.
                if ($temCentroCusto) {
                    $ehCentroPagamento = ($centroCod === $ALVO_COD) || (mb_stripos($centroNome, $ALVO_NOME) !== false);
                    if (!$ehCentroPagamento) {
                        continue;
                    }
                }

                // Monta 24 campos (ss:Index 1..24 => posicoes 0..23) separados por ';'
                $campos = array();
                for ($i = 1; $i <= 24; $i++) {
                    $valor = isset($colunas[$i]) ? $colunas[$i] : '';
                    // remove quebras de linha e ';' internos para nao quebrar o formato da linha
                    $valor = str_replace(array("\r", "\n", ";"), array('', ' ', ' '), $valor);
                    $campos[] = trim($valor);
                }
                $listaLinhas[] = implode(';', $campos) . PHP_EOL;
            }
        }

        $this->setListaLinhas($listaLinhas);
        $this->setTotalLinhas(count($listaLinhas));
        echo ($debug ? "<br> Contador XLS.: " . $this->getTotalLinhas() . "<br>" : "");
    }

    /**
     * Leitor do .xls BINARIO (OLE2/BIFF) - segundo modo de exportacao do relatorio HUTEC.
     * Usa o PhpSpreadsheet (ja instalado em lib/spreadsheet/) e reproduz EXATAMENTE o mesmo
     * formato de saida do leitor SpreadsheetML: cada linha de detalhe (coluna 1 = data dd/mm/aaaa)
     * vira "24 campos separados por ;", com as colunas 1..24 nas posicoes 0..23. Assim o restante
     * do fluxo (carregarRetornoHutec) le os indices [0][2][4][6][7][8][15][16][21][22] sem mudar nada.
     */
    public function carregarArquivoXlsBinario($arquivo, $debug = false) {
        require_once dirname(__FILE__) . '/../../lib/spreadsheet/vendor/autoload.php';

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(
            \PhpOffice\PhpSpreadsheet\IOFactory::identify($arquivo)
        );
        $reader->setReadDataOnly(true);
        $sheet = $reader->load($arquivo)->getActiveSheet();

        // O balancete e dividido em secoes por CENTRO DE CUSTO. So o centro das ENTRADAS de
        // pagamento por evento (CURSOS E EVENTOS / 0106) entra na conciliacao; os demais centros
        // (TAXA HUTEC, TAXA DIR., DESPESAS...) sao deducoes/despesas e NAO sao pagamentos.
        $ALVO_COD  = '0106';
        $ALVO_NOME = 'CURSOS E EVENTOS';
        $centroCod  = '';
        $centroNome = '';
        $temCentroCusto = false; // o balancete tem centros de custo; o extrato bancario nao tem

        $listaLinhas = array();
        foreach ($sheet->getRowIterator() as $row) {
            $r = $row->getRowIndex();
            $c1 = trim((string) $sheet->getCell('A' . $r)->getValue());

            // Cabecalho de centro de custo: codigo de 4 digitos na col 1, nome na col 4 (ex.: "0106" | "CURSOS E EVENTOS")
            if (preg_match('/^\d{4}$/', $c1)) {
                $centroCod  = $c1;
                $centroNome = trim((string) $sheet->getCell('D' . $r)->getValue());
                $temCentroCusto = true;
                continue;
            }

            // Linha de detalhe: coluna 1 (A) e uma data dd/mm/aaaa (mesma regra do leitor XML)
            if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $c1)) {
                continue;
            }

            // Balancete (tem centro de custo): so entram os pagamentos do centro CURSOS E EVENTOS / 0106.
            // Extrato bancario (sem centro de custo): nao filtra por centro.
            if ($temCentroCusto) {
                $ehCentroPagamento = ($centroCod === $ALVO_COD) || (mb_stripos($centroNome, $ALVO_NOME) !== false);
                if (!$ehCentroPagamento) {
                    continue;
                }
            }

            // Colunas 1..24 => posicoes 0..23
            $campos = array();
            for ($c = 1; $c <= 24; $c++) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                $valor = (string) $sheet->getCell($col . $r)->getValue();
                // remove quebras de linha e ';' internos para nao quebrar o formato da linha
                $valor = str_replace(array("\r", "\n", ";"), array('', ' ', ' '), $valor);
                $campos[] = trim($valor);
            }
            $listaLinhas[] = implode(';', $campos) . PHP_EOL;
        }

        $this->setListaLinhas($listaLinhas);
        $this->setTotalLinhas(count($listaLinhas));
        echo ($debug ? "<br> Contador XLS binario.: " . $this->getTotalLinhas() . "<br>" : "");
    }

    /**
     * Extrai as celulas de um bloco <Row> do SpreadsheetML respeitando ss:Index e ss:MergeAcross.
     * Retorna array associativo indice(1-based) => valor de texto da celula.
     */
    private function extrairColunasRowXls($rowXml) {
        $colunas = array();
        $cur = 0;
        if (preg_match_all('/<Cell\b([^>]*)>(.*?)<\/Cell>/s', $rowXml, $cells, PREG_SET_ORDER)) {
            foreach ($cells as $cell) {
                $attrs = $cell[1];
                $inner = $cell[2];

                if (preg_match('/ss:Index="(\d+)"/', $attrs, $m)) {
                    $cur = (int) $m[1];
                } else {
                    $cur++;
                }

                $valor = '';
                if (preg_match('/<Data\b[^>]*>(.*?)<\/Data>/s', $inner, $d)) {
                    $valor = html_entity_decode(strip_tags($d[1]), ENT_QUOTES, 'UTF-8');
                }
                $colunas[$cur] = $valor;

                // avanca pelo merge horizontal para posicionar a proxima celula sem ss:Index
                if (preg_match('/ss:MergeAcross="(\d+)"/', $attrs, $ma)) {
                    $cur += (int) $ma[1];
                }
            }
        }
        return $colunas;
    }

    /**
     * Imprime a primeira linha do arquivo csv informado, normalmente utilizada para
     * identificar a informa��o das colunas, � importante para saber em que ordem as informa��es das 
     * colunas est�o retornando
     */
    public function imprimirTituloColunas() {
        $listaLinhas = $this->getListaLinhas();
        $totalLinhas = $this->getTotalLinhas();
        
        $linha = $listaLinhas[0];
        $arrayColunas = explode(';', $linha);

        //Loop em cima das colunas retornadas das linhas do arquivo
        foreach ($arrayColunas as $key => $value) {
            echo $value."-";
        }
        echo "<hr>";
    }
    /**
     * Imprime todas as linhas de um arquivo carregado, pode ser utilizado como exemplo
     * para utilizar o retorno do getLinha e inserir em uma tabela do banco ou coisa parecida
     */
    public function imprimirLinhas($debug=false){
        $listaLinhas = $this->getListaLinhas();
        $totalLinhas = $this->getTotalLinhas();
        //Loop em cima das linhas retornadas do arquivo
        $contadorInverso = 1;
        $i=1;
        while($totalLinhas > 0){
            $linha = $listaLinhas[$contadorInverso];
            $arrayColunas = explode(';', $linha);

            //Loop em cima das colunas retornadas das linhas do arquivo
            echo $i++;
            foreach ($arrayColunas as $key => $value) {
                echo "-".$value;
            }
            echo "<hr>";
            $totalLinhas--;
            $contadorInverso++;
        }
    }
    public function transformarArquivo(){
        // Abrir o arquivo CSV original para leitura
        $inputFile = fopen('seu_arquivo.csv', 'r');
        
        // Criar um novo arquivo CSV para salvar sem aspas e com ponto e vírgula
        $outputFile = fopen('novo_arquivo.csv', 'w');
        
        // Loop para ler cada linha do arquivo original
        while (($data = fgetcsv($inputFile, 1000, ",")) !== FALSE) {
            // Escrever a linha no novo arquivo, sem aspas e com separador ponto e vírgula
            fputcsv($outputFile, $data, ";", '"');
        }
        
        // Fechar os arquivos
        fclose($inputFile);
        fclose($outputFile);
        
        echo "Arquivo convertido com sucesso!";
    }
    
    public function getListaLinhas() {
        return $this->listaLinhas;
    }

    public function setListaLinhas($listaLinhas) {
        $this->listaLinhas = $listaLinhas;
    }

    public function getNmArquivo() {
        return $this->nmArquivo;
    }

    public function setNmArquivo($nmArquivo) {
        $this->nmArquivo = $nmArquivo;
    }

    public function getTotalLinhas() {
        return $this->totalLinhas;
    }

    public function setTotalLinhas($totalLinhas) {
        $this->totalLinhas = $totalLinhas;
    }
}
?>