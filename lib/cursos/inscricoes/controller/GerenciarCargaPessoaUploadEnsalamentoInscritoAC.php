<?php
/**
 * Carga de ENSALAMENTO por INSCRITO.
 * Recebe uma linha ja mapeada por cabecalho (assoc) e grava/atualiza a sala do inscrito
 * em tab_ensalamento_inscrito. Match do inscrito em cascata: cod_inscrito -> CPF -> e-mail.
 * A leitura do arquivo (xlsx/xls/csv) e o loop ficam em GerenciarCargaPessoaUploadAC.
 */
class GerenciarCargaPessoaUploadEnsalamentoInscritoAC {

    function __construct() {
        FWA_exigirPermissao(enumSistema::SIS_GERENCIAR_ADM_GERAL);
    }

    /**
     * @param array $linha  assoc com chaves canonicas: cod_inscrito, cpf, email, sala, bloco, dia, hora, obs
     * @param object $objAr Arquivo em processamento (traz cod_evento)
     */
    public static function carregarLinha($linha, $objAr){

        $codInscritoInformado = isset($linha['cod_inscrito']) ? trim($linha['cod_inscrito']) : '';
        $cpf   = isset($linha['cpf'])   ? trim($linha['cpf'])   : '';
        $email = isset($linha['email']) ? trim($linha['email']) : '';

        $sala  = isset($linha['sala'])  ? trim($linha['sala'])  : '';
        $bloco = isset($linha['bloco']) ? trim($linha['bloco']) : '';
        $dia   = isset($linha['dia'])   ? trim($linha['dia'])   : '';
        $hora  = isset($linha['hora'])  ? trim($linha['hora'])  : '';
        $obs   = isset($linha['obs'])   ? trim($linha['obs'])   : '';

        // Linha totalmente vazia (ex.: linha em branco no fim da planilha) -> ignora sem erro
        if($codInscritoInformado==='' && $cpf==='' && $email==='' && $sala==='' && $bloco==='' && $dia==='' && $hora==='' && $obs===''){
            throw new OKException("");
        }

        if($sala==='' && $bloco==='' && $dia==='' && $hora==='' && $obs===''){
            throw new Exception("Nenhum dado de ensalamento informado (sala/bloco/dia/hora/obs)");
        }

        // Resolve o cod_inscrito
        $codInscrito = self::resolverCodInscrito($codInscritoInformado, $cpf, $email, $objAr->getCodEvento());

        $objE = new EnsalamentoInscrito();
        $objE->setCodEvento($objAr->getCodEvento());
        $objE->setCodInscrito($codInscrito);
        $objE->setSala($sala);
        $objE->setBloco($bloco);
        $objE->setDia($dia);
        $objE->setHora($hora);
        $objE->setObs($obs);
        $objE->salvar();
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

        // 2) CPF (getByCpfExiste faz SELECT * puro -> getCodInscrito e seguro)
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
