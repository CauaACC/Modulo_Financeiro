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

    public function getDesStatus(){
        if(is_integer($this->getStatus())){
            return ($this->getStatus()==1 ? "Ativo" : "Inativo");
        }else{
            return $this->status;
        }
    }

    public function __call($metodo, $parametros)
    {
        $prefixo  = substr($metodo, 0, 3);
        $preVar = strtolower(substr($metodo, 3,1));
        $variavel = substr($metodo, 4);
        $variavel = $preVar.$variavel;

        if( $prefixo == 'set' ) {
            $this->$variavel = $parametros[0];
        }
        elseif( $prefixo  == 'get' ) {
            if (isset($this->$variavel)) {
                return $this->$variavel;
            }else{
                return NULL;
            }
        }
        else {
            throw new Exception('O método ' . $metodo . ' não existe!');
        }
    }

    public function setAll($rows,$camelCase=false,$debug=false){
        foreach ($rows as $campo=>$value) {

            $atributo = "";
            $setMetodo = "";
            $getMetodo = "";

            if(is_string($campo)){
                if($camelCase)
                {
                    $setMetodo = "set".ucwords($campo);
                }
                else
                {
                    $arrayVar1 = explode('_',$campo);
                    foreach ($arrayVar1 as $key => $value1)
                    {
                        $value1 = strtolower($value1);
                        if($key!=0){
                            $atributo = $atributo.ucwords($value1);
                        }else{
                            $atributo = $value1;
                        }
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

    public function getAllForArray($arrayCampo,$debug=false){
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
                    if($key!=0){
                        $atributo = $atributo.ucwords($value1);
                    }else{
                        $atributo = $value1;
                    }
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
                if($key!=0){
                    $atributo = $atributo.ucwords($value1);
                }else{
                    $atributo = $value1;
                }
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
