<?php
/**
 * Carga de PRESENCA por INSCRITO.
 * Recebe uma linha ja mapeada por cabecalho (assoc) e grava flg_presenca_inscrito
 * em tab_inscritos. Match do inscrito em cascata: cod_inscrito -> CPF -> e-mail.
 * A leitura do arquivo (xlsx/xls/csv) e o loop ficam em GerenciarCargaPessoaUploadAC.
 *
 * Regra da coluna presenca: vazio/null ou 0 -> ausente (flg=0);
 * qualquer outro valor (nao vazio e diferente de zero) -> presente (flg=1).
 * A planilha e a fonte da verdade: reenviar sobrescreve a presenca do inscrito.
 */
class GerenciarCargaPessoaUploadPresencaInscritoAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
    }

    /**
     * @param array $linha  assoc com chaves canonicas: cod_inscrito, cpf, email, nome, presenca
     * @param object $objAr Arquivo em processamento (traz cod_evento)
     */
    public static function carregarLinha($linha, $objAr){

        $codInscritoInformado = isset($linha['cod_inscrito']) ? trim($linha['cod_inscrito']) : '';
        $cpf     = isset($linha['cpf'])      ? trim($linha['cpf'])      : '';
        $email   = isset($linha['email'])    ? trim($linha['email'])    : '';
        $nome    = isset($linha['nome'])     ? trim($linha['nome'])     : '';
        $presRaw = isset($linha['presenca']) ? trim($linha['presenca']) : '';

        // Linha totalmente vazia (ex.: linha em branco no fim da planilha) -> ignora sem erro
        if($codInscritoInformado==='' && $cpf==='' && $email==='' && $nome==='' && $presRaw===''){
            throw new OKException("");
        }

        $presente = self::interpretarPresenca($presRaw);

        // Resolve o cod_inscrito
        $codInscrito = self::resolverCodInscrito($codInscritoInformado, $cpf, $email, $objAr->getCodEvento());

        $objI = new Inscrito();
        $objI->setCodEvento($objAr->getCodEvento());
        $objI->setCodInscrito($codInscrito);
        $objI->setFlgPresencaInscrito($presente);
        $objI->marcarPresenca();
    }

    /**
     * Vazio/null ou 0 -> ausente (0). Qualquer outro valor -> presente (1).
     * Trata "0", "0.00" e "0,00" como zero.
     */
    private static function interpretarPresenca($valor){
        $v = trim((string)$valor);
        if ($v === '') {
            return 0;
        }
        $num = str_replace(',', '.', $v);
        if (is_numeric($num) && (float)$num == 0) {
            return 0;
        }
        return 1;
    }

    private static function resolverCodInscrito($codInscritoInformado, $cpf, $email, $codEvento){

        // 1) cod_inscrito (mais confiavel) - so confirma existencia, nao re-le o cod (getById corrompe cod_inscrito)
        if($codInscritoInformado!=='' && ctype_digit($codInscritoInformado) && (int)$codInscritoInformado > 0){
            $objI = new Inscrito();
            $objI->setCodEvento($codEvento);
            $objI->setCodInscrito((int)$codInscritoInformado);
            if($objI->getInscritoById()){
                return (int)$codInscritoInformado;
            }
            throw new Exception("cod_inscrito ".$codInscritoInformado." não encontrado neste evento");
        }

        // 2) CPF (verificarCpfEstaInscrito faz SELECT * puro -> getCodInscrito e seguro)
        if($cpf!==''){
            $objI = new Inscrito();
            $objI->setCodEvento($codEvento);
            $objI->setCpf(formatCpfInsert(limparCpf($cpf)));
            if($objI->verificarCpfEstaInscrito()){
                return $objI->getCodInscrito();
            }
            throw new Exception("CPF ".$cpf." não encontrado (inscrito ATIVADA) neste evento");
        }

        // 3) e-mail (getByEmailExistePopular faz SELECT * puro -> getCodInscrito e seguro)
        if($email!==''){
            if(!validarEmail($email)){
                throw new Exception("E-mail inválido: ".$email);
            }
            $objI = new Inscrito();
            $objI->setCodEvento($codEvento);
            $objI->setEMail($email);
            if($objI->getByEmailExistePopular()){
                return $objI->getCodInscrito();
            }
            throw new Exception("E-mail ".$email." não encontrado (inscrito ATIVADA) neste evento");
        }

        throw new Exception("Informe cod_inscrito, CPF ou e-mail para identificar o inscrito");
    }
}
