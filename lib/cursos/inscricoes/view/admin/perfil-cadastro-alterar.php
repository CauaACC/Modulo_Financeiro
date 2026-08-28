<?php
require_once(dirname(__FILE__)."/../../include/config.php");
require_once(dirname(__FILE__)."/../../view/admin/perfil-cabecalho.php");
require_once(dirname(__FILE__)."/../../view/admin/perfil-cadastro-busca.php");
#Fazendo conexão com o banco
$cod_perfil = $_GET['cod_perfil'];
#echo $cod_perfil  ."<br>";

try {
    
    if (!isset($cod_perfil)){
        throw new Exception("Código do Perfil não informado.");
    }
    
    $objPerfil = new Perfil();
    $objPerfil->setCodPerfil($cod_perfil);
    $objPerfil->carregarPerfilById();
    
    if($objPerfil->getTitulo() == ""){
        throw new Exception("Peril não encontrado.");
    }
    
        echo "
        <hr size='1' width='100%'>
        <FONT SIZE='2' FACE='VERDANA'> <B> Ficha de Cadastro de Perfis</B></FONT><br>
        <FONT SIZE='1' FACE='VERDANA'> <B> * Campos Obrigatórios</B></FONT>
        <FONT SIZE='1' FACE='VERDANA'>";

        FWA_Mostrar_Mensagem($_SESSION['MSG']);

        echo "<FORM method='POST' name='form' action='view/admin/perfil-cadastro-alterar-grava.php' onsubmit=\"return criticar()\">
        <input type='hidden' id='codPerfil' name='cod_perfil' value='{$cod_perfil}'>
        <input type='hidden' id='codEvento' name='COD_EVENTO'  value='{$COD_EVENTO}'>";
        
        echo "<font size='2'><b>Cógido da Perfil: {$cod_perfil}</b></font><br><br>";
        echo "<table border='0' width='565'>
        <tr><td colspan='2' align='center'>
            <input type='submit' value='Salvar' style='font-size:14px' >
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='reset' value='Limpar' style='font-size:14px' >
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <button class='button' style='font-size:14px' id='btnDuplicar' onkeypress='javascript: event.preventDefault();'>Duplicar</button>
        </td></tr>
        </table>";
    
        echo "<fieldset width='570'><legend> Dados da Perfil </legend>";
        include_once("perfil-cadastro-campos.php");
        echo "</fieldset>";
        echo "<table border='0' width='565'>
        <tr><td colspan='2' align='center'>
            <br><br>
            <input type='submit' value='Salvar' style='font-size:14px'>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='reset' value='Limpar' style='font-size:14px'>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <button class='button' style='font-size:14px' id='btnDuplicar' onkeypress='javascript: event.preventDefault();'>Duplicar</button>
        </td></tr>
        </table>";
      echo "</FORM>";
      
} catch (Exception $exc) {
    echo $exc->getMessage();
}
?>
</div></div></div></div>
</body>
</html>
<script type="text/javascript">
    $(document).ready(function() {
        $('#btnDuplicar').click(function(event) {
            event.preventDefault();
//            $('#novoCodPerfil').val(0);
//            $('#salvarOficinaPerfil').val('Duplicar');
//            document.location.href='/index.php';
            $('#c').val('GerenciarPerfil');
            $('#m').val('duplicar');
            duplicarPerfil();
        });
    });
    function duplicarPerfil(event) {
        $.ajax({
            type: "POST"
            ,url: '<?php echo  WB_URL_COMMAND ?>&t=' + Math.random()
            ,data: ({'codPerfil': $('#codPerfil').val(), 'c': 'GerenciarPerfil', 'm': 'duplicar', '_submit_check': '1'})
            ,dataType: "json"
            ,async: false
            ,cache: false
            , beforeSend: function() {
                startLoading();
            }
            , timeout: 4000
            , error: function(ajax) {
                showResponse(ajax);
            }
            , success: function(data) {
                processJson(data);
                if (data.statusMessage) {
                    document.location.href='<?php echo WB_URL_VIEW ?>?content=view/admin/perfil-cadastro-alterar.php&cod_perfil='+data.codPerfil
                    //location.reload(); 
                }
            }
            , complete: function() {
                stopLoading();
            }
        });
    }
</script>