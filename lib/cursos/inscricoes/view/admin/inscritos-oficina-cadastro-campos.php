<?php
require_once(dirname(__FILE__)."/../../include/config.php");
?>
            
      <TABLE WIDTH='100%' BORDER='0' CELLSPACING='0' CELLPADDING='4'>
        <TR>
          <TD VALIGN='top'>

            Nome do Inscrito:<br>
            <?php echo $cod_inscrito." - ".$nome_inscrito; ?> 
            <input type='hidden' name='cod_inscrito' value='<?php echo $cod_inscrito ?>'>
            <input type='hidden' name='cod_evento' value='<?php echo $cod_evento ?>'>
            <br><br>
            Titulo das Oficinas:<br>
            <?php echo FWA_Select_Oficina($cod_oficina,$titulo_oficina,$cod_evento) ?>
            <input type="hidden" name="cod_oficina_anterior" value="<?php echo $cod_oficina; ?>">
            <br><br>
            <label for="tipo_relacao">Tipo de relação com a ativadade:</label>
            <select id="tipo_relacao" name="tipo_relacao">
                <option value="">Selecione um tipo de relação</option>
                <?php echo CarregarOptionsAC::tipoRelacao($tipo_relacao, $debug=false)?>
            </select>
            <br><br>
            <label for="flg_presenca">Presença:</label>
            <select id="flg_presenca" name="flg_presenca">
                <option value=""></option>
                <?php echo CarregarOptionsAC::presenca($flg_presenca, $debug=false)?>
            </select>
            <br><br>
            <label for="frequencia">Frequência:</label>
            <INPUT CLASS='form_campos' TYPE='text' NAME='frequencia' value='<?php echo $frequencia ?>' 
                SIZE='15' maxlength='50' onBlur='trim(this);' 
                onkeypress='javascript: return isNum(window.event.keyCode);' require='require'> ex: (98) não informe porcentagem
            <br><br>
            <label for="carga_horaria">Carga Horária:</label>
            <INPUT CLASS='form_campos' TYPE='text' NAME='carga_horaria' value='<?php echo $carga_horaria ?>' 
                SIZE='15' maxlength='50' onBlur='trim(this);' require='require'> ex: (16h) 
            <br><br>
            <label for="carga_horaria_pratica">Carga Horária Prática:</label>
            <INPUT CLASS='form_campos' TYPE='text' NAME='carga_horaria_pratica' value='<?php echo $carga_horaria_pratica ?>' 
                SIZE='15' maxlength='50' onBlur='trim(this);' require='require'> ex: (16h) 
            <br><br>
            <label for="carga_horaria_teorica">Carga Horária Teórica:</label>
            <INPUT CLASS='form_campos' TYPE='text' NAME='carga_horaria_teorica' value='<?php echo $carga_horaria_teorica ?>' 
                SIZE='15' maxlength='50' onBlur='trim(this);' require='require'> ex: (16h) 
          </TD>
        </TR>
       </TABLE>
       <br>&nbsp;


