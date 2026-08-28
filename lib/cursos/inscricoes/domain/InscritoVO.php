<?php
class InscritoVO extends PadraoVO
{   
    public function getDeclaracaoEstevePresente(){
        return $this->getFlgPresencaInscrito();
    }

    /**
     * Undocumented function
     *
     * @param [type] $em dias, meses ou anos
     * @return void
     */
    public function getIntervaloEstadia($em){
        $data1 = new DateTime($this->getDataEstadiaIni());
        $data2 = new DateTime($this->getDataEstadiaFim());
        $intervalo = $data1->diff( $data2 );
//            echo "Intervalo é de {$intervalo->y} anos, {$intervalo->m} meses e {$intervalo->d} dias"; 
        switch ($em) {
            case 'dias':
                return $intervalo->d;
                break;
            case 'meses':
                return $intervalo->m;
                break;
            case 'anos':
                return $intervalo->y;
                break;
            
            default:
                return false;
                break;
        }
    }
    
    public function getStatusProcessoAtivo(){
        if (in_array($this->getStatusProcesso(), ['DESISTENTE', 'CANCELADO'])) {
            return false; // Ou 'inativo', dependendo do que você precisa
        } else {
            return true; // Ou 'ativo'
        }
    }

    public function getTotalPagamentos(){
        if($this->codEvento==10){
            return 1;
        }
        return $this->totalPagamentos;
    }
    /** Soma dos pagamentos (valor_pagamentos do SELECT da listagem). Fora
     *  da listagem a propriedade nao vem populada — retorna 0. */
    public function getValorPagamentos(){
        return isset($this->valorPagamentos) && is_numeric($this->valorPagamentos)
            ? floatval($this->valorPagamentos) : 0;
    }
    public function getUrlPrefixo(){
        if($this->urlPrefixo!=''){
            return $this->urlPrefixo;
        }else{
            $urlGestor = $this->getGestor();
            if($urlGestor==''){
                $this->carregarObjEvento();
                $objEv = $this->getObjEvento();
                $urlGestor = $objEv->getGestor();
            }
            // if($objEv->getUrlPrefixo()!=''){
            //     return $objEv->getUrlPrefixo();
            // }
            return strtolower($urlGestor).'/'.$this->getCodEvento();
        }
    }
    
    public function getFoneContato(){
        if($this->foneContato!=''){
            return $this->foneContato;
        }else{
            return $this->foneCelular;
        }
    }
    
    public function getEMail(){
        return strtolower($this->eMail);
    }
    // 
    public function getStatusDataDesativar($diasAContar=''){
//        return $this->getDataDesativar();
//        return formatDateHoraTrans($this->getDataDesativar(),'DD/MM/YYYY','YYYYMMDD');
        if(Date('Ymd') <= formatDateHoraTrans($this->getDataDesativar($diasAContar),'DD/MM/YYYY','YYYYMMDD')){
            return false;
        }
        return true;
    }
    /**
     * return DD/MM/YYYY
     */
    public function getDataDesativar($diasAContar=3){
        $a = new soma_data($this->getDatIncl(), $diasAContar." dias");
        return $a->datas['data'];
    }
    public function getNomeApelido() {
        if($this->nomeApelido=='' or $this->getCodEvento()=='7'){
            $preNome = explode(" ", $this->nomeInscrito);
            return $preNome[0];
        }
        return $this->nomeApelido;
    }
    public function getPreNome() {
        $preNome = explode(" ", $this->nomeInscrito);
        return $preNome[0];
    }
    public function getDescImprimiuCracha(){
        if($this->impressao_cracha == 1){
            return "Retirou Crachá";
        }else{
            return "Não retirou Crachá";
        }
    }   
    public function getDesImpressaoCracha(){
        return $this->getDescImprimiuCracha();
    }

    public function getCpf(){
        if($this->cpf!=''){
            return $this->cpf;
        }
        return $this->getCpfInscrito();
    }    
    public function getCpfView(){
        return formatCpfCnpjView($this->getCpf());
    }
    public function getCpfReciboView(){
        return formatCpfCnpjView($this->getCpfRecibo());
    }
    public function getTipoDocCpfCnpjInscrito(){
        $formatoDocumento = "cpf";
        if(strlen(limparCpfCnpj($this->getCpf()))>11){
            $formatoDocumento = "cnpj";
        }
        return $formatoDocumento;
    }
    public function getTipoDocCpfCnpjRecibo(){
        $formatoDocumento = "cpf";
        if(strlen(limparCpfCnpj($this->getCpfRecibo()))>11){
            $formatoDocumento = "cnpj";
        }
        return $formatoDocumento;
    }
    public function getDataInscricao(){
        if($this->dataInscricao!='' or $this->dataInscricao!=NULL){
            return $this->dataInscricao;
        }
        return $this->dataInclusao;
    }
    public function getPossuiBoletoGerado(){
        if($this->getTotalBoletos()>0){
            return true;
        }
        return false;
    }
    function getDesIsento(){
        if($this->getCodIsento()){
            return 'Isento';
        }
        if($this->getTotalPerfisIsentos()){
            return 'Isento por Perfil';
        }
        return '';
    }
    function getDesFlgPresencaInscrito(){
        if($this->getFlgPresencaInscrito()==1){
            return 'Presente';
        }
        if($this->getFlgPresencaInscrito()==0){
            return 'Ausente';
        }
        return '';
    }
    public function setDatInscrito($datInscrito) {
        $this->datInscrito = formatDateInsert($datInscrito);
    }
    public function validatorObj(){
        if (!$this->getOrigemCadastro()) {
            $this->setOrigemCadastro('INSCRICAO');
        }
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if($this->getOrigemCadastro() != 'CERTIFICADO'){
            if(is_null($this->getEMail())or$this->getEMail()==""){
                $message .= "E-mail não foi informado.<br>";
                $return = false;
            }
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
    public function validatorObjCarga(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }
    public function validatorEcluir(){
        $message = "";
        $return  = true;
        if(is_null($this->getCodEvento())or$this->getCodEvento()==""){
            $message .= "Código do evento não foi informado.<br>";
            $return = false;
        }
        if(is_null($this->getCodInscrito()) or $this->getCodInscrito()==""){
            $message .= "Código da ocorrência não foi informado.<br>";
            $return = false;
        }
        if($return==false){
            throw new Exception($message);
            return false;
        }
        return true;
    }   
    
    public function getNmBanco(){
        
        $codigo_banco = $this->getDbBanco();
        switch ($codigo_banco) {
            case '001':
                $nome_banco = '001 - Banco do Brasil';
                break;
            case '237':
                $nome_banco = '237 - Bradesco';
                break;
            case '341':
                $nome_banco = '341 - Itaú';
                break;
            case '104':
                $nome_banco = '104 - Caixa Econômica Federal';
                break;
            case '033':
                $nome_banco = '033 - Santander';
                break;
            case '077':
                $nome_banco = '077 - Banco Inter';
                break;
            case '260':
                $nome_banco = '260 - Nubank';
                break;
            case '318':
                $nome_banco = '318 - Banco BMG';
                break;
            case '229':
                $nome_banco = '229 - Banrisul';
                break;
            case '347':
                $nome_banco = '347 - Banco do Estado do Paraná';
                break;
            case '003':
                $nome_banco = '003 - Banco da Amazônia';
                break;
            case '290':
                $nome_banco = '290 - PagSeguro';
                break;
            case '756':
                $nome_banco = '756 - Sicoob';
                break;
            case '748':
                $nome_banco = '748 - Sicred';
                break;
            // Adicione mais casos conforme necessário
            default:
                $nome_banco = 'Desconhecido';
        }
        return $nome_banco;
    }
    
    public function getDataEmissaoView(){
        return formatDateView($this->getRgDataEmissao());
    }
}