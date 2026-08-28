<SCRIPT LANGUAGE="JavaScript1.2" src="resources/js/area-tematica-cadastro.js"></SCRIPT>

<?php
#require_once(dirname(__FILE__)."/../../include/config.php");
$FWA_Option_Dia    = FWA_Option_Dia();
$FWA_Option_Mes    = FWA_Option_Mes();
$FWA_Option_Ano    = FWA_Option_Ano(3);
$FWA_Option_Hora   = FWA_Option_Hora();
$FWA_Option_Minuto = FWA_Option_Minuto();


#$STATUS="DESATIVADO";

If(isset($STATUS)){
  $OPTION_STATUS = "<option selected value='{$STATUS}'>{$STATUS}</option>";
}Else{
  $OPTION_STATUS = "<option selected value='-1'>Alterar Status</option>";
}

echo @<<<corpo
<input type='hidden' name='COD_EVENTO' value='{$COD_EVENTO}'>

<table border='0' width='570'>

<tr><td>&nbsp; Status:           </td>
                                 <td>
                                   <select name='STATUS'>
                                     {$OPTION_STATUS}
                                     <option value='ATIVADA '>ATIVADA </option>
                                     <option value='DESATIVA'>DESATIVA</option>
                                   </select>
                                 </td></tr>

<tr><td>&nbsp; Titulo da Area Tematica:  </td><td> <input type='text' name='TITULO'             value='{$TITULO            }' size='60' maxlength='255' onBlur="trim(this);" ></td></tr>
<tr><td>&nbsp; Nome Ministrante:   </td><td> <input type='text' name='NOME_MINISTRANTE'   value='{$NOME_MINISTRANTE  }' size='60' maxlength='255' onBlur="trim(this);" onkeypress="javascript: return isAlfa(window.event.keyCode);"></td></tr>
<tr><td>&nbsp; E-mail Ministrante: </td><td> <input type='text' name='E_MAIL_MINISTRANTE' value='{$E_MAIL_MINISTRANTE}' size='60' maxlength='255' onBlur="trim(this);" </td></tr>
<tr><td>&nbsp; Fone Ministrante:   </td><td> <input type='text' name='FONE_MINISTRANTE'   value='{$FONE_MINISTRANTE  }' size='60' maxlength='255' onBlur="trim(this);" onkeypress="javascript: return isNumHifens(window.event.keyCode);"></td></tr>

<tr><td>&nbsp; Local:    </td><td> <input type='text' name='LOCAL'              value='{$LOCAL        }'      size='60' maxlength='255' onBlur="trim(this);" </td></tr>
<tr><td>&nbsp; Carga Horária:      </td><td> <input type='text' name='CARGA_HORARIA'      value='{$CARGA_HORARIA}'      size='10' maxlength='10'  onBlur="trim(this);" onkeypress="javascript: return isNum(window.event.keyCode);"> horas</td></tr>
<tr><td>&nbsp; Número Vagas:       </td><td> <input type='text' name='NUMERO_VAGAS'       value='{$NUMERO_VAGAS }'      size='10' maxlength='10'  onBlur="trim(this);" onkeypress="javascript: return isNum(window.event.keyCode);"> </td></tr>

<tr><td>&nbsp; Horário Inicio:     </td><td>
<!--
<input type='text' name='HORARIO_INI_HORA'   value='{$HORARIO_INI_HORA}'   size='5' maxlength='5' onBlur="trim(this);" onkeypress="javascript: return isNum(window.event.keyCode);"> h
<input type='text' name='HORARIO_INI_MINUTO' value='{$HORARIO_INI_MINUTO}' size='5' maxlength='5' onBlur="trim(this);" onkeypress="javascript: return isNum(window.event.keyCode);"> min.
-->
        <SELECT name='HORARIO_INI_HORA'>
          <OPTION VALUE='{$HORARIO_INI_HORA}'>{$HORARIO_INI_HORA}</OPTION>
          {$FWA_Option_Hora}
        </SELECT> &nbsp; Horas e &nbsp;

        <SELECT name='HORARIO_INI_MINUTO'>
          <OPTION VALUE='{$HORARIO_INI_MINUTO}'>{$HORARIO_INI_MINUTO}</OPTION>
          {$FWA_Option_Minuto}
        </SELECT> &nbsp; Minutos &nbsp;

                                   </td></tr>

<tr><td>&nbsp; Horário Fim:        </td><td>
<!--
<input type='text' name='HORARIO_FIM_HORA'   value='{$HORARIO_FIM_HORA}'   size='5' maxlength='5' onBlur="trim(this);" onkeypress="javascript: return isNum(window.event.keyCode);"> h
<input type='text' name='HORARIO_FIM_MINUTO' value='{$HORARIO_FIM_MINUTO}' size='5' maxlength='5' onBlur="trim(this);" onkeypress="javascript: return isNum(window.event.keyCode);"> min.
-->

        <SELECT name='HORARIO_FIM_HORA'>
          <OPTION VALUE='{$HORARIO_FIM_HORA}'>{$HORARIO_FIM_HORA}</OPTION>
          {$FWA_Option_Hora}
        </SELECT> &nbsp; Horas e &nbsp;

        <SELECT name='HORARIO_FIM_MINUTO'>
          <OPTION VALUE='{$HORARIO_FIM_MINUTO}'>{$HORARIO_FIM_MINUTO}</OPTION>
          {$FWA_Option_Minuto}
        </SELECT> &nbsp; Minutos &nbsp;



                                   </td></tr>
<!--
<tr><td>&nbsp; Data Inicio:        </td><td> <input type='text' name='DATA_INI_DIA'       value='{$DATA_INI_DIA   }'    size='5' maxlength='5' onBlur="trim(this);" onkeypress="javascript: return isNum(window.event.keyCode);"> /
                                             <input type='text' name='DATA_INI_MES'       value='{$DATA_INI_MES   }'    size='5' maxlength='5' onBlur="trim(this);" onkeypress="javascript: return isNum(window.event.keyCode);"> /
                                             <input type='text' name='DATA_INI_ANO'       value='{$DATA_INI_ANO   }'    size='5' maxlength='5' onBlur="trim(this);" onkeypress="javascript: return isNum(window.event.keyCode);"> . (ex. dd/m/ANO )
                                   </td></tr>

<tr><td>&nbsp; Data Fim:           </td><td> <input type='text' name='DATA_FIM_DIA'       value='{$DATA_FIM_DIA   }'    size='5' maxlength='5' onBlur="trim(this);" onkeypress="javascript: return isNum(window.event.keyCode);"> /
                                             <input type='text' name='DATA_FIM_MES'       value='{$DATA_FIM_MES   }'    size='5' maxlength='5' onBlur="trim(this);" onkeypress="javascript: return isNum(window.event.keyCode);"> /
                                             <input type='text' name='DATA_FIM_ANO'       value='{$DATA_FIM_ANO   }'    size='5' maxlength='5' onBlur="trim(this);" onkeypress="javascript: return isNum(window.event.keyCode);"> . (ex. dd/m/ANO )
                                   </td></tr>
-->
<tr><td>&nbsp; Data Inicio:           </td><td>
        &nbsp; Dia &nbsp;
        <SELECT name='DATA_INI_DIA'>
          <OPTION VALUE='{$DATA_INI_DIA}'>{$DATA_INI_DIA}</OPTION>
          {$FWA_Option_Dia}
        </SELECT>

        &nbsp; Mês &nbsp;
        <SELECT name='DATA_INI_MES'>
          <OPTION VALUE='{$DATA_INI_MES}'>{$DATA_INI_MES}</OPTION>
          {$FWA_Option_Mes}
        </SELECT>

        &nbsp; Ano &nbsp;
        <SELECT name='DATA_INI_ANO'>
          <OPTION VALUE='{$DATA_INI_ANO}'>{$DATA_INI_ANO}</OPTION>
          {$FWA_Option_Ano}
        </SELECT>

</td></tr>


<tr><td>
        &nbsp; Data Fim:           </td><td>
        &nbsp; Dia &nbsp;
        <SELECT name='DATA_FIM_DIA'>
          <OPTION VALUE='{$DATA_FIM_DIA}'>{$DATA_FIM_DIA}</OPTION>
          {$FWA_Option_Dia}
        </SELECT>

        &nbsp; Mês &nbsp;
        <SELECT name='DATA_FIM_MES'>
          <OPTION VALUE='{$DATA_FIM_MES}'>{$DATA_FIM_MES}</OPTION>
          {$FWA_Option_Mes}
        </SELECT>

        &nbsp; Ano &nbsp;
        <SELECT name='DATA_FIM_ANO'>
          <OPTION VALUE='{$DATA_FIM_ANO}'>{$DATA_FIM_ANO}</OPTION>
          {$FWA_Option_Ano}
        </SELECT>

</td></tr>

<tr><td colspan='2'>&nbsp; Número Máximo de Trabalhos por Avaliador: <input type='text' name='N_MAX_TRAB_POR_AVALIADOR'       value='{$N_MAX_TRAB_POR_AVALIADOR}'      size='10' maxlength='10'  onBlur='trim(this);' onkeypress='javascript: return isNum(window.event.keyCode);'> </td></tr>

<tr><td colspan=2>&nbsp; Palavras-Chave: </td></tr>
<tr><td colspan=2>&nbsp; <textarea rows=5 cols=70               name='PALAVRAS_CHAVE'     size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob'>{$PALAVRAS_CHAVE}</textarea></td></tr>

<tr><td colspan=2>&nbsp; Publico Alvo: </td></tr>
<tr><td colspan=2>&nbsp; <textarea rows=5 cols=70               name='PUBLICO_ALVO'       size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob'>{$PUBLICO_ALVO  }</textarea></td></tr>

<tr><td colspan=2>&nbsp; Objetivo: </td></tr>
<tr><td colspan=2>&nbsp; <textarea rows=5 cols=70               name='OBJETIVO'           size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob'>{$OBJETIVO      }</textarea></td></tr>

<tr><td colspan=2>&nbsp; Pré-Requisito: </td></tr>
<tr><td colspan=2>&nbsp; <textarea rows=5 cols=70               name='PRE_REQUISITO'      size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob'>{$PRE_REQUISITO }</textarea></td></tr>

<tr><td colspan=2>&nbsp; Ementa: </td></tr>
<tr><td colspan=2>&nbsp; <textarea rows=5 cols=70               name='EMENTA'             size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob'>{$EMENTA        }</textarea></td></tr>

<tr><td colspan=2>&nbsp; Descrição: </td></tr>
<tr><td colspan=2>&nbsp; <textarea rows=5 cols=70               name='DESCRICAO'          size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob'>{$DESCRICAO     }</textarea></td></tr>

<tr><td colspan=2>&nbsp; Observação: </td></tr>
<tr><td colspan=2>&nbsp; <textarea rows=5 cols=70               name='OBSERVACAO'         size='100' onBlur='trim(this);' onkeypress='javascript: return isAlfa(window.event.keyCode);' class='textob'>{$OBSERVACAO    }</textarea></td></tr>

</table>
corpo;
?>