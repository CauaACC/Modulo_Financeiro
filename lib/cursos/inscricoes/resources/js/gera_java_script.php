<?
#-------------------------------------------------------------------
  $dir_classes="../../../../scripts/classes";
  include_once($dir_classes."/functions/conexao_banco/FWA_Conexao.php");
  #include_once("/scripts/include/functions/functions/conexao_banco/FWA_Conexao.php");

  If (!$Conexao_db = FWA_Conexao("")) { //Conectando-se ao banco de dados
      $_SESSION['MSG'] = "Erro-de-Conexão";
    $url = "http://www.opctec.com.br";
    echo "Erro de Conexao";
    #echo "<HTML><META HTTP-EQUIV=\"REFRESH\" CONTENT=\"2; URL=".$url."\"></HTML>";
    exit;
  }
#-------------------------------------------------------------------
?>

  <script language="JavaScript">
  <!-- **** SCRIPT GERA ARRAY DAS CIDADES **** -->
  <?
    echo "teamCidade = new Array(";

    #seleciona todos os grupos cadastrados
    $consql="
              SELECT b.cod_estado,  r.nome_estado, Count(b.cod_estado) AS total_cidade
              FROM estado AS r, cidade AS b
              WHERE (r.cod_estado=b.cod_estado)
              GROUP BY b.cod_estado, r.nome_estado
              ORDER BY r.nome_estado
            ";
    $RS = $Conexao_db->Execute($consql);
    $conta=0;
    While (!$RS->EOF){
      $num = 28; # total de estado
      @$conta=$conta+1;
      $cod_estado  = $RS->fields[0];
      $nome_estado = $RS->fields[1];
      $num_sub     = $RS->fields[2];

      echo "new Array(\n";

      $sub_sql="SELECT r.cod_cidade, r.nome_cidade FROM cidade as r WHERE r.cod_estado=".$cod_estado;
      $RS1 = $Conexao_db->Execute($sub_sql);

      If (!$RS1->EOF){
        //se ele achar algum subgrupo para o grupo ele marca a palavra Todas
        echo "new Array(\"Selecione a Cidade\", \"escolha\"),\n";
        While (!$RS1->EOF){

          $codigo_sub = $RS1->fields[0];
          $sub_nome   = $RS1->fields[1];
          @$conta_sub=$conta_sub+1;

          If ($conta_sub==$num_sub){
            echo "new Array(\"$sub_nome\", $codigo_sub)\n";
            $conta_sub="";
          }Else{
            echo "new Array(\"$sub_nome\", $codigo_sub),\n";
          }
          $RS1->MoveNext();
        }
      }Else{
        //Se ele nao achar subgrupo para o grupo selecionado...
        echo "new Array(\"Qualquer\", 0)\n";
      }

      #echo $conta." - ".$num."<br>";

      If ($num>$conta){
        echo "),\n";
      }
      $RS->MoveNext();
    }
    echo ")\n";
    echo ");\n";
  ?>
  <!-- **** SCRIPT GERA ARRAY DOS BAIRROS**** -->
  <?
    echo "teamBairro = new Array(";

    #seleciona todos os grupos cadastrados
    $consql="
              SELECT b.cod_regiao,  r.nome_regiao, Count(b.cod_regiao) AS total_bairro
              FROM regiao AS r, bairro AS b
              WHERE (r.cod_regiao=b.cod_regiao)
              GROUP BY b.cod_regiao, r.nome_regiao
              ORDER BY r.nome_regiao
            ";
    $RS = $Conexao_db->Execute($consql);
    $conta=0;
    While (!$RS->EOF){
      $num = 25; # total de regiões
      @$conta=$conta+1;
      $cod_regiao  = $RS->fields[0];
      $nome_regiao = $RS->fields[1];
      $num_sub     = $RS->fields[2];

      echo "new Array(\n";

      $sub_sql="SELECT r.cod_bairro, r.nome_bairro FROM bairro as r WHERE r.cod_regiao=".$cod_regiao;
      $RS1 = $Conexao_db->Execute($sub_sql);

      If (!$RS1->EOF){
        //se ele achar algum subgrupo para o grupo ele marca a palavra Todas
        echo "new Array(\"Selecione o Bairro\", \"escolha\"),\n";
        While (!$RS1->EOF){

          $codigo_sub = $RS1->fields[0];
          $sub_nome   = $RS1->fields[1];
          @$conta_sub=$conta_sub+1;

          If ($conta_sub==$num_sub){
            echo "new Array(\"$sub_nome\", $codigo_sub)\n";
            $conta_sub="";
          }Else{
            echo "new Array(\"$sub_nome\", $codigo_sub),\n";
          }
          $RS1->MoveNext();
        }
      }Else{
        //Se ele nao achar subgrupo para o grupo selecionado...
        echo "new Array(\"Qualquer\", 0)\n";
      }

      #echo $conta." - ".$num."<br>";

      If ($num>$conta){
        echo "),\n";
      }

      $RS->MoveNext();
    }
    echo ")\n";
    echo ");\n";
  ?>

<!-- **** SCRIPT MONTA AS CIDADES DE ACORDO COM O ESTADO **** -->

  //inicio da função JS
  function fillSelectFromArrayCidade(selectCtrl, itemArray, goodPrompt, badPrompt, defaultItem) {
    var i, j;
    var prompt;
    // empty existing items
    for (i = selectCtrl.options.length; i >= 0; i--) {
    selectCtrl.options[i] = null;
    }
    prompt = (itemArray != null) ? goodPrompt : badPrompt;
    if (prompt == null) {
    j = 0;
    }
    else {
    selectCtrl.options[0] = new Option(prompt);
    j = 1;
    }
    if (itemArray != null) {
    // add new items
    for (i = 0; i < itemArray.length; i++) {
      //selectCtrl.options[j] = new Option(itemArray[i][1]); //para guardar o codido e não o nome
      selectCtrl.options[j] = new Option(itemArray[i][0]);   //para guardar o nome e não o codigo
      if (itemArray[i][1] != null) {
      selectCtrl.options[j].value = itemArray[i][0];
      }
      j++;
    }
    // select first item (prompt) for sub list
    selectCtrl.options[0].selected = true;
    }
  }
  //fim

<!-- **** SCRIPT MONTA OS BAIRROS DE ACORDO COM O ESTADO **** -->

  //inicio da função JS
  function fillSelectFromArrayBairro(selectCtrl, itemArray, goodPrompt, badPrompt, defaultItem) {
    var i, j;
    var prompt;
    // empty existing items
    for (i = selectCtrl.options.length; i >= 0; i--) {
    selectCtrl.options[i] = null;
    }
    prompt = (itemArray != null) ? goodPrompt : badPrompt;
    if (prompt == null) {
    j = 0;
    }
    else {
    selectCtrl.options[0] = new Option(prompt);
    j = 1;
    }
    if (itemArray != null) {
    // add new items

    for (i = 0; i < itemArray.length; i++) {
      //selectCtrl.options[j] = new Option(itemArray[i][1]); //para guardar o codido e não o nome
      selectCtrl.options[j] = new Option(itemArray[i][0]);   //para guardar o nome e não o codigo
      if (itemArray[i][1] != null) {
      selectCtrl.options[j].value = itemArray[i][0];
      }
      j++;
    }

    // select first item (prompt) for sub list
    selectCtrl.options[0].selected = true;
    }
  }
  //fim

<!-- **** SCRIPT MONTA AS CIDADES_INFRACAO DE ACORDO COM O ESTADO_INFRACAO **** -->

  //inicio da função JS
  function fillSelectFromArrayCidade_Infracao(selectCtrl, itemArray, goodPrompt, badPrompt, defaultItem) {
    var i, j;
    var prompt;
    // empty existing items
    for (i = selectCtrl.options.length; i >= 0; i--) {
    selectCtrl.options[i] = null;
    }
    prompt = (itemArray != null) ? goodPrompt : badPrompt;
    if (prompt == null) {
    j = 0;
    }
    else {
    selectCtrl.options[0] = new Option(prompt);
    j = 1;
    }
    if (itemArray != null) {
    // add new items
    for (i = 0; i < itemArray.length; i++) {
      //selectCtrl.options[j] = new Option(itemArray[i][1]); //para guardar o codido e não o nome
      selectCtrl.options[j] = new Option(itemArray[i][0]);   //para guardar o nome e não o codigo
      if (itemArray[i][1] != null) {
      selectCtrl.options[j].value = itemArray[i][0];
      }
      j++;
    }
    // select first item (prompt) for sub list
    selectCtrl.options[0].selected = true;
    }
  }
  //fim

<!-- **** SCRIPT MONTA OS BAIRROS_INFRACAO DE ACORDO COM O ESTADO_INFRACAO **** -->

  //inicio da função JS
  function fillSelectFromArrayBairro_Infracao(selectCtrl, itemArray, goodPrompt, badPrompt, defaultItem) {
    var i, j;
    var prompt;
    // empty existing items
    for (i = selectCtrl.options.length; i >= 0; i--) {
    selectCtrl.options[i] = null;
    }
    prompt = (itemArray != null) ? goodPrompt : badPrompt;
    if (prompt == null) {
    j = 0;
    }
    else {
    selectCtrl.options[0] = new Option(prompt);
    j = 1;
    }
    if (itemArray != null) {
    // add new items

    for (i = 0; i < itemArray.length; i++) {
      //selectCtrl.options[j] = new Option(itemArray[i][1]); //para guardar o codido e não o nome
      selectCtrl.options[j] = new Option(itemArray[i][0]);   //para guardar o nome e não o codigo
      if (itemArray[i][1] != null) {
      selectCtrl.options[j].value = itemArray[i][0];
      }
      j++;
    }

    // select first item (prompt) for sub list
    selectCtrl.options[0].selected = true;
    }
  }
  //fim

  </script>
