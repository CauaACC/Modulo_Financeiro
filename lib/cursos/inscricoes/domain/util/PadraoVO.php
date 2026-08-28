<?php

/**
 * Description of PadraoVO
 *
 * @author zaka
 */
abstract class PadraoVO {
            
    function __construct() {
        $this->setCodEvento(COD_EVENTO);
        $this->setDatIncl(date('Y-m-d H:i:s'));
        $this->setResIncl($_SESSION['_cod_usuario']);
        $this->setDatAlter(date('Y-m-d H:i:s'));
        $this->setResAlter($_SESSION['_cod_usuario']);
    }

    public function isNullOrEmpty($variable)
    {
        return is_null($variable) || $variable === "";
    }

    function getObjEventoConfiguracao($codEvento = false, $debug = false) {
        if(!$codEvento){
            $codEvento = $this->getCodEvento();
        }
        $objEvConf = EventoConfiguracao::getInstance($codEvento);
        return $objEvConf;
    }
    
    public function getDesStatus(){
        if(is_integer($this->getStatus())){
            return ($this->getStatus()==1 ? "Ativo" : "Inativo");
        }else{
            return $this->status;
        }
    }
    
    public function __call($metodo, $parametros)
    {
        // Selecionando os 3 primeiros caracteres do método chamado
        $prefixo  = substr($metodo, 0, 3);
        // Definido nome de variavel, padrão primeira palavra inicia com letra minuscula
        $preVar = strtolower(substr($metodo, 3,1));
        $variavel = substr($metodo, 4);
        $variavel = $preVar.$variavel;

        // se for set*, "seta" um valor para a propriedade
        if( $prefixo == 'set' ) {
            $this->$variavel = $parametros[0];
            //echo $variavel."<br>";
        }
        // se for get*, retorna o valor da propriedade
        elseif( $prefixo  == 'get' ) {
            // echo "variável:".$variavel." - ";
            // echo $this->$variavel."<br>";
            if (isset($this->$variavel)) {
                return $this->$variavel;
            }else{
                return NULL;
            }
        }
        // Retorna exception dizendo que não existe o método chamada
        else {
            throw new Exception('O método ' . $metodo . ' não existe!');
        }
    }

    public function setAll($rows,$camelCase=false,$debug=false){
//        if($_SERVER["REMOTE_ADDR"]=='189.90.70.56'){
//            $debug=true;
//        }
        /*
         * Primeiro percorrer o array $rows a procura de seus indices
         * Depois localizado seu indices, formar o metodos set
         * Depois de formar os métodos é necessário executalos
         * E a forma de se fazer isso é executando os seguinte comando
         *$this->$metodo($fws_);
         * onde: $this     reflete ao objeto atual, $metodo
         *       $metodos  reflete ao método contruido a partir do indice do array
         *       $fws_     reflete ao valor a ser setador pelo método
         */
        foreach ($rows as $campo=>$value) {
            
            $atributo = "";
            $setMetodo = "";
            $getMetodo = "";  
            //removi no dia 16/12/2011 porque naõ estava salvando, os campos ja 
            //vem do formulario com as letras maisculas definidas corretamente
            //$campo = strtolower($campo);
//            echo "set->".$campo." - ".$value."<br>";
                
            //echo "set->".$campo."-".$value."<br>";
           
            if(is_string($campo)){
                /*
                 * Caso nomeie os campos em formulário com a
                 * primeira letra minúscula em caso de palavras compostas 
                 * a primeira letra de cada palavra em maiúscula.
                 * Se utilizar underline em nomes de campos em formulário defina isForm com false.
                 */
                if($camelCase)
                {
                    $setMetodo = "set".ucwords($campo);
                }
                else
                {                
                    /* Utilizado quando os índices do array $rows não estiver no formato camelCase e utilizando _(underline) para separação de nome de campo */
                    $arrayVar1 = explode('_',$campo);
                    foreach ($arrayVar1 as $key => $value1)
                    {
                        //17/12/2011
                        //Esta linha foi acrescentada em virtude no nome do campo
                        //retornado pelo Oracle estar em maiuscula
                        $value1 = strtolower($value1);
                        //Definindo padrão de variaveis
                        if($key!=0){
                            $atributo = $atributo.ucwords($value1);
                        }else{
                            $atributo = $value1;
                        }
                        //Definindo padrão para metodos sets
                        if($key!=0){
                            $setMetodo = $setMetodo.ucwords($value1);
                        }else{
                            $setMetodo = "set".ucwords($value1);
                        }            
                    }
                }
                if($campo=="_submit_check"){
                    break;
                } 
                /*
                 * Quando estiver trabalhando com um array, é melhor gravar no banco em formato json
                 *  utilizando a função json_encode()
                 * Caso contrario sera gravado no banco apenas a string "Array"
                 * Para reverter o processo e voltar o objeto json para seu formato
                 *  em array utilize json_decode() na visualização
                 */
                if(is_array($value)){
                    $value=array_map('htmlentities',$value);
                    $value = json_encode($value);
                }
                $value = $value!=''?  stripslashes($value) : NULL;
                $this->$setMetodo($value);
                echo ($debug)? $setMetodo." - ".$value."<br>" : "";
            }
        }
        echo ($debug)? "<hr>" : "";

    }    

    /*
     * Utilizado em DAOs antes de gerar o Sql de insert ou update automáticamente
     * Para criar uma array a partir dos campos de uma tabela
     * Fornece um array contendo os campos da tabela
     * Esse array é utilizado em DAO's em funções de Insert e Update para gerar SQL's automaticos
     * @access public
     * @param array[] $arrayCampo Array com campos de uma tabela de BD.
     * @return array $fws_['nomeDoCampoDaTabela']
     */

    public function getAllForArray($arrayCampo,$debug=false){
//        if($_SERVER["REMOTE_ADDR"]=='189.90.70.56'){
//            $debug=true;
//        }
        
        foreach($arrayCampo as $campo) {
            echo ($debug)? "get->".$campo."<br>" : "";
            
            $atributo = "";
            $setMetodo = "";
            $getMetodo = "";  
            $campo = strtolower($campo);
            $arrayVar1 = explode('_',$campo);
            if(is_string($campo)){

                foreach ($arrayVar1 as $key => $value1)
                {
                    //Definindo padrão de variaveis
                    if($key!=0){
                        $atributo = $atributo.ucwords($value1);
                    }else{
                        $atributo = $value1;
                    }
                    //Definindo padrão para metodos sets
                    if($key!=0){
                        $getMetodo = $getMetodo.ucwords($value1);
                    }else{
                        $getMetodo = "get".ucwords($value1);
                    }            
                }
                $fws_[$campo] = addslashesopctec($this->$getMetodo());
            }
        }
        echo ($debug)? "<hr>" : "";
        return $fws_;
    }   
    //converteLatinHTML
    public function tratarJson($texto){
        $texto = addslashesopctec($texto);
        $texto = str_replace("\r", '\r', $texto);
        $texto = str_replace("\n", '\n', $texto);
        $texto = str_replace("\t", '\t', $texto);
        return $texto;
    }
    public function retirarBarras($texto){
        $texto = str_replace("\\", "", $texto);
        $texto = str_replace("/", "", $texto);
        return $texto;
    }

    public function getPadrao($campo){
        echo ($debug)? "get->".$campo."<br>" : "";
        $atributo = "";
        $setMetodo = "";
        $getMetodo = "";  
        $campo = strtolower($campo);
        $arrayVar1 = explode('_',$campo);
        if(is_string($campo)){

            foreach ($arrayVar1 as $key => $value1)
            {
                //Definindo padrão de variaveis
                if($key!=0){
                    $atributo = $atributo.ucwords($value1);
                }else{
                    $atributo = $value1;
                }
                //Definindo padrão para metodos sets
                if($key!=0){
                    $getMetodo = $getMetodo.ucwords($value1);
                }else{
                    $getMetodo = "get".ucwords($value1);
                }            
            }
            return addslashesopctec($this->$getMetodo());
        }
    }
}

