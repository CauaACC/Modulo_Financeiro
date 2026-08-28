<?php
class soma_data {

    public $data;
    public $add;
    public $datas = array();
    public $arr = array();
    
    function __construct($data, $add,$debug=false){
        $this->soma_data($data, $add, $debug);
    }
    
    function soma_data($data, $add,$debug=false)
    {
        $add  = trim($add);
        $data = trim($data);
        $data = preg_replace("#(agora|hoje|atual)#i", date("Y-m-d H:i:s"), $data);

        echo ($debug? "<hr>data informada antes do converte->".$data."<hr>" : "");

        $data = $this->converter_data($data);

        echo ($debug? "<hr>soma_data: data informada após converte->".$data."<hr>" : "");

        echo ($debug? "<hr>soma_data: add informada->".$add."<hr>" : "");
        
        if($this->valida_data($data)) {
            $add = preg_replace("#semana[s]?#i", "week", $add);
            $add = preg_replace("#m(e|[ê])s(es)?#i", "month", $add);
            $add = preg_replace("#ano[s]?#i", "year", $add);
            $add = preg_replace("#dia[s]?#i", "days", $add);
            $add = preg_replace("#hora[s]?#i", "hours", $add);
            $add = preg_replace("#minuto[s]?#i", "minutes", $add);
            $add = preg_replace("#segundo[s]?#i", "seconds", $add);

            echo ($debug? "<hr>getDate: ".getdate(strtotime("$data +$add"))."<hr>" : "");

            setlocale(LC_TIME,"portuguese");

            //$data = strftime("%A %D %B %Y %c %j", strtotime("$data +$add"));
            $data = strftime("%A %B %Y %D %T %j %c", strtotime("$data +$add"));

            echo ($debug? "<hr>soma_data: data tratada->".$data."<hr>" : "");
//Array ( [0] => Quarta [1] => Setembro [2] => 2011 [3] => Qua [4] => 21 [5] => Set [6] => 2011 [7] => 18:05:50 [8] => BRT [9] => 264 )
            $arr = explode(" ",$data);

            echo ($debug? "<hr>arr: ".print_r($arr)."<hr>" : "");

            $this->datas['semana'] = $arr[0];

            $arr2 = explode("/", $arr[3]); // mm/dd/yy
            echo ($debug? "<hr>arr2: ".print_r($ar2)."<hr>" : "");
            
            $mes = $arr2[0];
            $dia = $arr2[1];

            $this->datas['dia']     = sprintf("%02d", $dia);
            $this->datas['mes'][1]  = $arr[1];
            $this->datas['mes'][2]  = sprintf("%02d", $mes);
            $this->datas['ano']     = $arr[2];
            $arr3             = explode(":", $arr[4]);
            $this->datas['hora']    = $arr3[0];
            $this->datas['minuto']  = $arr3[1];
            $this->datas['segundo'] = $arr3[2];
            $this->datas['dia_ano'] = $arr[5];
            $this->datas['data_completa'] = $data;
            $this->datas['data_hora'] = $arr[6].' '.$arr[7];
            $this->datas['data'] = $this->datas['dia'].'/'.$this->datas['mes'][2].'/'.$this->datas['ano'];

            if($debug){
                echo "<br>datas['semana]: ".$this->datas['semana'];
                echo "<br>datas['dia']: ".$this->datas['dia'];
                echo "<br>datas['mes'][1]: ".$this->datas['mes'][1];
                echo "<br>datas['mes'][2]: ".$this->datas['mes'][2];
                echo "<br>datas['ano']: ".$this->datas['ano'];

                echo "<br>datas['hora']: ".$this->datas['hora'];
                echo "<br>datas['minuto']: ".$this->datas['minuto'];
                echo "<br>datas['segundo']: ".$this->datas['segundo'];
                echo "<br>datas['dia_ano']: ".$this->datas['dia_ano'];
                echo "<br>datas['data']: ".$this->datas['data'];
                echo "<br>datas['data_hora']: ".$this->datas['data_hora'];
                echo "<br>datas['data_completa']: ".$this->datas['data_completa'];
            }

            return $this->datas;

        } else {
            echo "Data invalida";
            return false;
        }
    }
    function diminuir_data($data, $add,$debug=false)
    {
        $add  = trim($add);
        $data = trim($data);
        $data = preg_replace("#(agora|hoje|atual)#i", date("Y-m-d H:i:s"), $data);

        echo ($debug? "<hr>data informada antes do converte->".$data."<hr>" : "");

        $data = $this->converter_data($data);

        echo ($debug? "<hr>soma_data: data informada após converte->".$data."<hr>" : "");

        echo ($debug? "<hr>soma_data: add informada->".$add."<hr>" : "");
        
        if($this->valida_data($data)) {
            $add = preg_replace("#semana[s]?#i", "week", $add);
            $add = preg_replace("#m(e|[ê])s(es)?#i", "month", $add);
            $add = preg_replace("#ano[s]?#i", "year", $add);
            $add = preg_replace("#dia[s]?#i", "days", $add);
            $add = preg_replace("#hora[s]?#i", "hours", $add);
            $add = preg_replace("#minuto[s]?#i", "minutes", $add);
            $add = preg_replace("#segundo[s]?#i", "seconds", $add);

            echo ($debug? "<hr>getDate: ".getdate(strtotime("$data - $add"))."<hr>" : "");

            setlocale(LC_TIME,"portuguese");

            //$data = strftime("%A %D %B %Y %c %j", strtotime("$data +$add"));
            $data = strftime("%A %B %Y %D %T %j %c", strtotime("$data -$add"));

            echo ($debug? "<hr>soma_data: data tratada->".$data."<hr>" : "");
//Array ( [0] => Quarta [1] => Setembro [2] => 2011 [3] => Qua [4] => 21 [5] => Set [6] => 2011 [7] => 18:05:50 [8] => BRT [9] => 264 )
            $arr = explode(" ",$data);

            echo ($debug? "<hr>arr: ".print_r($arr)."<hr>" : "");

            $this->datas['semana'] = $arr[0];

            $arr2 = explode("/", $arr[3]); // mm/dd/yy
            echo ($debug? "<hr>arr2: ".print_r($ar2)."<hr>" : "");
            
            $mes = $arr2[0];
            $dia = $arr2[1];

            $this->datas['dia']     = sprintf("%02d", $dia);
            $this->datas['mes'][1]  = $arr[1];
            $this->datas['mes'][2]  = sprintf("%02d", $mes);
            $this->datas['ano']     = $arr[2];
            $arr3             = explode(":", $arr[4]);
            $this->datas['hora']    = $arr3[0];
            $this->datas['minuto']  = $arr3[1];
            $this->datas['segundo'] = $arr3[2];
            $this->datas['dia_ano'] = $arr[5];

            if($debug){
                echo "<br>datas['semana]: ".$this->datas['semana'];
                echo "<br>datas['dia']: ".$this->datas['dia'];
                echo "<br>datas['mes'][1]: ".$this->datas['mes'][1];
                echo "<br>datas['mes'][2]: ".$this->datas['mes'][2];
                echo "<br>datas['ano']: ".$this->datas['ano'];

                echo "<br>datas['hora']: ".$this->datas['hora'];
                echo "<br>datas['minuto']: ".$this->datas['minuto'];
                echo "<br>datas['segundo']: ".$this->datas['segundo'];
                echo "<br>datas['dia_ano']: ".$this->datas['dia_ano'];
            }

            return $datas;

        } else {
            echo "Data invalida";
            return false;
        }
    }

    function valida_data($data)
    {
        $data = trim($data);
        if(preg_match("@^\d{2}/\d{2}/\d{4}$@", $data)) {
            $arr = explode("/", $data);
            return !checkdate($arr[1], $arr[0], $arr[2]) ? false : true;
        }
        if(preg_match("@^\d{4}-\d{2}-\d{2}$@", $data)) {
            $arr = explode("-", $data);
            return !checkdate($arr[1], $arr[2], $arr[1]) ? false : true;
        }
        if(preg_match("@^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$@", $data)) {
            $arr = explode("-", $data);
            $dia = substr($arr[2], 0, 2);
            return !checkdate($arr[1], $dia, $arr[0]) ? false : true;
        }
        return false;
    }

    function converter_data($data)
    {
        $data = trim($data);
        if(preg_match("@^\d{4}-\d{2}-\d{2}$@", $data)) {
            return $data;
        }
        if(preg_match("@^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$@", $data)) {
            return $data;
        }
        if(preg_match("@^\d{2}/\d{2}/\d{4}$@", $data)) {
            return implode("-", array_reverse(explode("/", $data)));
        }else {
            echo "data invalida";
            return false;
        }
    }

    function testDiaUtil($fws, $debug=false){
        /*
         * @fws['Data']=dd/mm/YYYY
         * $fws['dataTeste']
         * data[0]=dia
         * data[1]=mes
         * data[2]=ano
         */

         if($this->valida_data($fws['dataTeste'])){
            $data = explode("/", $fws['dataTeste']);

            echo ($debug? "->".$fws['dataTeste']." data após explode-> ".$data[0]."/".$data[1]."/".$data[2]."<br>" : "");

            setlocale(LC_TIME,"portuguese");
            $diaSemana = date("l", mktime(0, 0, 0, $data[1], $data[0], $data[2]));

            echo ($debug? "->dia da semana: ".$diaSemana."<br>" : "");

            if(
                ($diaSemana=="Sábado")or($diaSemana=="Saturday")or
                ($diaSemana=="Domingo")or($diaSemana=="Sunday")or
                (FWF_Feriado($data[2]."-".$data[1]."-".$data[0], $fws['datasNaoEntregar']))
            ){
                $testDiaUtil=false;
            }else{
                $testDiaUtil=true;
            }

            return $testDiaUtil;
        }else{
            echo "<br>Data inválida: ".$fws['dataTeste']."<br>";
            exit();
        }
    }
}
#Array ( [semana] => domingo [dia] => 01 [mes] => Array ( [1] => outubro [2] => 10 ) [ano] => 2006 [hora] => 00 [minuto] => 00 [segundo] => 00 [dia_ano] => 274 )
#print_r(soma_data("2006-02-15 18:28:14", "5 minutos"));

/* Exemplo
$data_atual = Date("Y-m-d");
$dias_somar = 3;

$a = new soma_data($data_atual, $dias_somar." dias");

echo $a->datas['semana']."<br>";

while(($a->datas['semana']=="sábado")or($a->datas['semana']=="domingo")){
  $dias_somar = $dias_somar+1;
  $a = new soma_data($data_atual, $dias_somar." dias");
  echo $a->datas['semana']."<br>";
}

echo $a->datas[semana]."- [semana]<br>";
echo $a->datas[dia]."- [dia]<br>";
echo $a->datas[mes][1]."- [mes][1]<br>";
echo $a->datas[mes][2]."- [mes][2]<br>";
echo $a->datas[ano]."- [ano]<br>";
echo $a->datas[hora]."- [hora]<br>";
echo $a->datas[minuto]."- [minuto]<br>";
echo $a->datas[segundo]."- [segundo]<br>"
echo $a->datas[dia_ano]."- [dia_ano]<br>"

*/