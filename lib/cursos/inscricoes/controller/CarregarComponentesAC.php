<?php
class CarregarComponentesAC {
    public static function campoSituacaoInscricao ($fws_) { ?>
        <label for="ordenar">Situação Inscrito: </label>
        &nbsp;<input type='radio' name='situacao' value='ATIVADA'     <?php echo verifItemChecked($fws_['situacao'], 'ATIVADA') ?> > Ativada
        &nbsp;<input type='radio' name='situacao' value='DESATIVADA'  <?php echo verifItemChecked($fws_['situacao'], 'DESATIVADA') ?> > Desativada
        <!--&nbsp;<input type='radio' name='situacao' value='CANCELADA'   <?php echo verifItemChecked($fws_['situacao'], 'CANCELADA') ?> > Cancelada-->
        &nbsp;<input type='radio' name='situacao' value='EXCLUIDA'    <?php echo verifItemChecked($fws_['situacao'], 'EXCLUIDA') ?> > Excluída
        &nbsp;&nbsp;&nbsp;
         <label for="ordenar">Situação da Presença: </label>
        &nbsp;<input type='radio' name='presenca_inscrito' value=''    <?php echo verifItemChecked($fws_['presenca_inscrito'], '') ?> > Todos
        &nbsp;<input type='radio' name='presenca_inscrito' value='SIM'    <?php echo verifItemChecked($fws_['presenca_inscrito'], 'SIM') ?> > Presença Confirmada
        &nbsp;<input type='radio' name='presenca_inscrito' value='NAO'    <?php echo verifItemChecked($fws_['presenca_inscrito'], 'NAO') ?> > Presença Não Confirmada
        <br>
        <label for="ordenar">Situação no Processo: </label>
        &nbsp;<input type='radio' name='status_processo' value=''     <?php echo verifItemChecked($fws_['status_processo'], '') ?> > Todos
        &nbsp;<input type='radio' name='status_processo' value='INSCRITO'  <?php echo verifItemChecked($fws_['status_processo'], 'INSCRITO') ?> > Inscrito
        <!--&nbsp;<input type='radio' name='status_processo' value='APROVADO'   <?php echo verifItemChecked($fws_['status_processo'], 'APROVADO') ?> > Aprovado-->
        &nbsp;<input type='radio' name='status_processo' value='CONVOCADO'    <?php echo verifItemChecked($fws_['status_processo'], 'CONVOCADO') ?> > Convocado
        &nbsp;<input type='radio' name='status_processo' value='PARTICIPANDO'    <?php echo verifItemChecked($fws_['status_processo'], 'PARTICIPANDO') ?> > Participando
        &nbsp;<input type='radio' name='status_processo' value='DESISTENTE'    <?php echo verifItemChecked($fws_['status_processo'], 'DESISTENTE') ?> > Desistente
        &nbsp;<input type='radio' name='status_processo' value='CANCELADO'    <?php echo verifItemChecked($fws_['status_processo'], 'CANCELADO') ?> > Cancelado
        &nbsp;<input type='radio' name='status_processo' value='SUSPENSO'    <?php echo verifItemChecked($fws_['status_processo'], 'SUSPENSO') ?> > Suspenso
        <br>
        <?php if (COD_EVENTO==115) { ?>
        <?php if (in_array($_SERVER['REMOTE_ADDR'], ['177.82.0.28','127.0.0.1','::1'])) {?>
        &nbsp;&nbsp;&nbsp;
        <label for="intervalo_registros">Intervalo de registros: </label>
        &nbsp;<input type="radio" name="intervalo_registros" value="" 
            <?php echo verifItemChecked($fws_['intervalo_registros'], '') ?> > Todos
        &nbsp;<input type="radio" name="intervalo_registros" value="0-30" 
            <?php echo verifItemChecked($fws_['intervalo_registros'], '0-30') ?> > do 1º ao 30º
        &nbsp;<input type="radio" name="intervalo_registros" value="31-60" 
            <?php echo verifItemChecked($fws_['intervalo_registros'], '31-60') ?> >  do 31º ao 60º
        &nbsp;<input type="radio" name="intervalo_registros" value="61-30" 
            <?php echo verifItemChecked($fws_['intervalo_registros'], '61-30') ?> > do 61º ao 90º
        <br>
         <label for="limite_registros">Quantidade de registros: </label>
        &nbsp;<input type="number" name="limite_registros" value="<?php echo $fws_['limite_registros'] ?>" >
        <br>
        <?php } ?>
        <?php } ?>
        &nbsp;&nbsp;&nbsp;
        <label for="ordenar">Outras: </label>
            &nbsp;<input type='radio' name='outros_filtros_inscrito' value=''    <?php echo verifItemChecked($fws_['outros_filtros_inscrito'], '') ?> > Todos
            &nbsp;<input type='radio' name='outros_filtros_inscrito' value='avaliou_evento'    <?php echo verifItemChecked($fws_['outros_filtros_inscrito'], 'avaliou_evento') ?> > Avaliou Evento
            &nbsp;<input type='radio' name='outros_filtros_inscrito' value='ultimo_login_com_falha'    <?php echo verifItemChecked($fws_['outros_filtros_inscrito'], 'ultimo_login_com_falha') ?> > Cujo Ultimo Login Falhou
            &nbsp;<input type='radio' name='outros_filtros_inscrito' value='nunca_logou'    <?php echo verifItemChecked($fws_['outros_filtros_inscrito'], 'nunca_logou') ?> > Nunca Tentou Logar

        <br>
        <?php /**@todo criar area de configuração para evento vinculado, Tabela de Propósitos de Vínculo */ ?>
        <?php if (COD_EVENTO==80 or COD_EVENTO==88) { ?>
            <?php $fws_['cod_evento_vinculado']=82; ?>
            <input type="hidden" name="cod_evento_vinculado" value="<?php echo $fws_['cod_evento_vinculado']; ?>">
        <label for="cod_evento_vinculado">Verificar situação no projeto vinculado CONTRATOS - RECIBOS: </label> 
        &nbsp;<input type='radio' name='status_processo_vinculado' value=''<?php echo verifItemChecked($fws_['status_processo_vinculado'], '') ?> > Todos
        &nbsp;<input type='radio' name='status_processo_vinculado' value='VINCULADO'  <?php echo verifItemChecked($fws_['status_processo_vinculado'], 'VINCULADO') ?> > Vinculados
        &nbsp;<input type='radio' name='status_processo_vinculado' value='CANCELADO'    <?php echo verifItemChecked($fws_['status_processo_vinculado'], 'CANCELADO') ?> > Cancelados
        &nbsp;<input type='radio' name='status_processo_vinculado' value='NAO_VINCULADO'    <?php echo verifItemChecked($fws_['status_processo_vinculado'], 'NAO_VINCULADO') ?> > Não Vinculados (Sem Retorno)
        <br><br>
        <?php } ?>

    <?php }

    public static function campoRegistroAtividade ($fws_) { ?>
        <label for="registro_atividade">Verificar situação do registro de atividades: </label> <br>
        &nbsp;<input type='radio' name='registro_atividade_status' value='' <?php echo verifItemChecked($fws_['registro_atividade_status'], '') ?> > Todos
        &nbsp;<input type='radio' name='registro_atividade_status' value='NAO_REGISTROU' <?php echo verifItemChecked($fws_['registro_atividade_status'], 'NAO_REGISTROU') ?> > Não Registrou        
        &nbsp;<input type='radio' name='registro_atividade_status' value='REGISTROU' <?php echo verifItemChecked($fws_['registro_atividade_status'], 'REGISTROU') ?> > Registrou
        &nbsp;<input type='radio' name='registro_atividade_status' value='NAO_AVALIADO'    <?php echo verifItemChecked($fws_['registro_atividade_status'], 'NAO_AVALIADO') ?> > Não Avaliados
        &nbsp;<input type='radio' name='registro_atividade_status' value='REGISTRO_VALIDADO' <?php echo verifItemChecked($fws_['registro_atividade_status'], 'REGISTRO_VALIDADO') ?> > Registro Validado
        &nbsp;<input type='radio' name='registro_atividade_status' value='REGISTRO_NAO_VALIDADO' <?php echo verifItemChecked($fws_['registro_atividade_status'], 'REGISTRO_NAO_VALIDADO') ?> > Registro Não Validado
        <!-- &nbsp;<input type='radio' name='registro_atividade_status' value='VALIDADA'  <?php echo verifItemChecked($fws_['registro_atividade_status'], 'VALIDADA') ?> > Validadas
        &nbsp;<input type='radio' name='registro_atividade_status' value='NAO_VALIDADAS'    <?php echo verifItemChecked($fws_['registro_atividade_status'], 'NAO_VALIDADAS') ?> > Não validadas -->
        <?php //if ($_SERVER['REMOTE_ADDR']=='177.82.0.28') { ?>
        <br><label for="periodo_apuracao_reg_ativ">Período de apuração de registro: </label>
                &nbsp;&nbsp;
                <input type="date"  id="periodo_apuracao_reg_ativ_ini" name="periodo_apuracao_reg_ativ_ini" 
                       value="<?php echo $fws_['periodo_apuracao_reg_ativ_ini']?>">
                &nbsp;&nbsp; à  &nbsp;&nbsp;
                <input type="date"  id="periodo_apuracao_reg_ativ_fim" name="periodo_apuracao_reg_ativ_fim"
                       value="<?php echo $fws_['periodo_apuracao_reg_ativ_fim']?>">
        <?php //} ?>
    <?php }

    public static function campoQuantidadePedidos($fws_, $debug) { ?>
        <label for="pedidos">Qtd.Pedidos: </label>
        <select id="pedidos" name="pedidos">
            <option class='form_campos' value="" <?php echo verifItemSelected($fws_['pedidos'], '') ?> > Todos</option>
            <?php 
            CarregarOptionsAC::qtdPedidos($fws_, $debug);
            ?>
        </select>
        <label for="pedidos">Qtd.Pedidos Até: </label>
        <select id="pedidos" name="pedidosQtdAte">
            <option class='form_campos' value="" <?php echo verifItemSelected($fws_['pedidosQtdAte'], '') ?> > Todos</option>
            <?php 
            CarregarOptionsAC::pedidosQtdAte($fws_, $debug);
            ?>
        </select>
    <?php }

    public static function campoListaPagamentos($fws_, $debug) { ?>
                        <b>Selecione as opções referente a pagamentos: </b>
                &nbsp;<input type='radio' name='pagamentos' value=''    <?php echo verifItemChecked($fws_['pagamentos'], '') ?> > Todos 
                &nbsp;<input type='radio' name='pagamentos' value='-1'    <?php echo verifItemChecked($fws_['pagamentos'], '-1') ?> > Não pagos 
                &nbsp;<input type='radio' name='pagamentos' value='102'    <?php echo verifItemChecked($fws_['pagamentos'], '102') ?> > Pagos menos Isentos 
                &nbsp;<input type='radio' name='pagamentos' value='99'    <?php echo verifItemChecked($fws_['pagamentos'], '99') ?> > Pagos 
                
                <br>&nbsp;&nbsp;&nbsp;
                <b>Quanto a quantidade de pagamentos registrados: </b>
                <!-- <br>&nbsp;&nbsp;&nbsp; -->
                &nbsp;<input type='radio' name='pagamentos' value='1'    <?php echo verifItemChecked($fws_['pagamentos'], '1') ?> > 1
                &nbsp;<input type='radio' name='pagamentos' value='2'    <?php echo verifItemChecked($fws_['pagamentos'], '2') ?> > 2
                <?php //if ((FWA_Autenticacao($_SESSION['_cod_usuario'], 999, 5, false, false))) {?>
                &nbsp;<input type='radio' name='pagamentos' value='3'    <?php echo verifItemChecked($fws_['pagamentos'], '3') ?> > 3
                &nbsp;<input type='radio' name='pagamentos' value='4'    <?php echo verifItemChecked($fws_['pagamentos'], '4') ?> > 4
                &nbsp;<input type='radio' name='pagamentos' value='5'    <?php echo verifItemChecked($fws_['pagamentos'], '5') ?> > 5
                &nbsp;<input type='radio' name='pagamentos' value='6'    <?php echo verifItemChecked($fws_['pagamentos'], '6') ?> > 6
                &nbsp;<input type='radio' name='pagamentos' value='7'    <?php echo verifItemChecked($fws_['pagamentos'], '7') ?> > 7
                &nbsp;<input type='radio' name='pagamentos' value='8'    <?php echo verifItemChecked($fws_['pagamentos'], '8') ?> > 8
                &nbsp;<input type='radio' name='pagamentos' value='9'    <?php echo verifItemChecked($fws_['pagamentos'], '9') ?> > 9
                &nbsp;<input type='radio' name='pagamentos' value='10'    <?php echo verifItemChecked($fws_['pagamentos'], '10') ?> > 10                
                &nbsp;<input type='radio' name='pagamentos' value='11'    <?php echo verifItemChecked($fws_['pagamentos'], '11') ?> > 11                
                &nbsp;<input type='radio' name='pagamentos' value='12'    <?php echo verifItemChecked($fws_['pagamentos'], '12') ?> > 12                
                &nbsp;<input type='radio' name='pagamentos' value='13'    <?php echo verifItemChecked($fws_['pagamentos'], '13') ?> > 13                
                <br>&nbsp;&nbsp;&nbsp; 
                <label for="pagamentosQtdMaior">Qtd.Pagamentos Maior que: </label>
                <select id="pagamentosQtdMaior" name="pagamentosQtdMaior">
                    <option class='form_campos' value="" <?php echo verifItemSelected($fws_['pagamentosQtdMaior'], '') ?> > Todos</option>
                    <?php 
                    CarregarOptionsAC::pagamentosQtdMaior($fws_, $debug);
                    ?>
                </select>
                <label for="pagamentosQtdMenor">Qtd.Pagamentos Menor que: </label>
                <select id="pagamentosQtdMenor" name="pagamentosQtdMenor">
                    <option class='form_campos' value="" <?php echo verifItemSelected($fws_['pagamentosQtdMenor'], '') ?> > Todos</option>
                    <?php 
                    CarregarOptionsAC::pagamentosQtdMenor($fws_, $debug);
                    ?>
                </select>
                <?php //} ?>
                <br>
                <br> <label for="xml_pagamentos">Listar pagamentos separadamente: </label>
                &nbsp;<input type='radio' name='xml_pagamentos' value=''    <?php echo verifItemChecked($fws_['xml_pagamentos'], '') ?> > Não separar
                &nbsp;<input type='radio' name='xml_pagamentos' value='104' <?php echo verifItemChecked($fws_['xml_pagamentos'], '104') ?> > Separar Pagamentos para XML
                <br>
                <label for="xml_pagamentos_carregados">Listar pagamentos NÃO carregado: </label>
                &nbsp;<input type='radio' name='xml_pagamentos_carregados' value=''    <?php echo verifItemChecked($fws_['xml_pagamentos_carregados'], '') ?> > Todos
                &nbsp;<input type='radio' name='xml_pagamentos_carregados' value='nao-carregados' <?php echo verifItemChecked($fws_['xml_pagamentos_carregados'], 'nao-carregados') ?> > Não carregados
                <br><br>
                &nbsp;&nbsp;&nbsp;
                <label for="tipo_pedido">Pagamentos: </label>
                &nbsp;<input type='radio' name='tipo_pedido' value=''            <?php echo verifItemChecked($fws_['tipo_pedido'], '') ?> > Todos 
                &nbsp;<input type='radio' name='tipo_pedido' value='mensalidade' <?php echo verifItemChecked($fws_['tipo_pedido'], 'mensalidade') ?> > de/com Mensalidade

                <br>
                &nbsp;&nbsp;&nbsp;
                <label for="pagamentos">Por tipo de Isenção: </label>
                &nbsp;<input type='radio' name='pagamentos' value='98'    <?php echo verifItemChecked($fws_['pagamentos'], '98') ?> > Isentos Indicados
                &nbsp;<input type='radio' name='pagamentos' value='ISENTO-PERFIL'    <?php echo verifItemChecked($fws_['pagamentos'], 'ISENTO-PERFIL') ?> > Isentos por Perfil
                &nbsp;<input type='radio' name='pagamentos' value='100'    <?php echo verifItemChecked($fws_['pagamentos'], '100') ?> > Isentos por Vouchers
                &nbsp;<input type='radio' name='pagamentos' value='103'    <?php echo verifItemChecked($fws_['pagamentos'], '103') ?> > Usou Voucher
                &nbsp;<input type='radio' name='pagamentos' value='101'    <?php echo verifItemChecked($fws_['pagamentos'], '101') ?> > Desconto por Campanha

                <br>
                &nbsp;&nbsp;&nbsp;
                <label for="pagamentos">Forma Pagamento: </label>
                &nbsp;<input type='radio' name='forma_pagamento' value=''        <?php echo verifItemChecked($fws_['forma_pagamento'], '') ?> > Todos
                &nbsp;<input type='radio' name='forma_pagamento' value='EMPENHO' <?php echo verifItemChecked($fws_['forma_pagamento'], 'EMPENHO') ?> > Empenho
                &nbsp;<input type='radio' name='forma_pagamento' value='BOLETO'  <?php echo verifItemChecked($fws_['forma_pagamento'], 'BOLETO') ?> > Boleto
                &nbsp;<input type='radio' name='forma_pagamento' value='CARTAO'  <?php echo verifItemChecked($fws_['forma_pagamento'], 'CARTAO') ?> > Cartão
                &nbsp;<input type='radio' name='forma_pagamento' value='TESOURARIA'  <?php echo verifItemChecked($fws_['forma_pagamento'], 'TESOURARIA') ?> > Dinheiro
                &nbsp;<input type='radio' name='forma_pagamento' value='PIX'  <?php echo verifItemChecked($fws_['forma_pagamento'], 'PIX') ?> > Pix

                &nbsp;<input type='radio' name='forma_pagamento' value='DEPOSITO-CC'  <?php echo verifItemChecked($fws_['forma_pagamento'], 'DEPOSITO-CC') ?> > Deposito CC
                &nbsp;<input type='radio' name='forma_pagamento' value='OUTROS'  <?php echo verifItemChecked($fws_['forma_pagamento'], 'OUTROS') ?> > Outros
                <br>
                &nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' name='forma_pagamento' value='PAG.TERCEIRO'  <?php echo verifItemChecked($fws_['forma_pagamento'], 'PAG.TERCEIRO') ?> > 
                Optou por emitir Boleto/Recibo em nome de sua Instituição/Empresa?
                <br>
    <?php }

    public static function campoDataCadastro($fws_) { ?>
            <div class="form-group col-sm-12" style="padding-left: 0px;">
            <label for="data_inscricao">Período de cadastro do participante: </label>
                &nbsp;&nbsp;
                <input type="text"  id="data_inscricao_ini" name="data_inscricao_ini" 
                       value="<?php echo $fws_['data_inscricao_ini']?>">
                &nbsp;&nbsp; à  &nbsp;&nbsp;
                <input type="text"  id="data_inscricao_fim" name="data_inscricao_fim" 
                       value="<?php echo $fws_['data_inscricao_fim']?>">
            </div>      
    <?php }

    public static function campoDatasPagamento($fws_) { ?>
                <label for="pagamentos">Filtros por Períodos: </label>
                <div class="form-group col-sm-12" style="padding-left: 0px;">
                &nbsp;&nbsp;&nbsp;
                <label for="pagamentos">Data do pagamento: </label>
                    &nbsp;&nbsp;
                    <input type="date"  id="data_pagamento_ini" name="data_pagamento_ini" 
                           value="<?php echo $fws_['data_pagamento_ini']?>">
                    &nbsp;&nbsp; à  &nbsp;&nbsp;
                    <input type="date"  id="data_pagamento_fim" name="data_pagamento_fim" 
                           value="<?php echo $fws_['data_pagamento_fim']?>">
                </div>
                <br>
                <div class="form-group col-sm-12" style="padding-left: 0px;">
                &nbsp;&nbsp;&nbsp;
                <label for="pagamentos">Data de inclusão do pagamento: </label>
                    &nbsp;&nbsp;
                    <input type="date"  id="data_pagamento_incl_ini" name="data_pagamento_incl_ini" 
                           value="<?php echo $fws_['data_pagamento_incl_ini']?>">
                    &nbsp;&nbsp; à  &nbsp;&nbsp;
                    <input type="date"  id="data_pagamento_incl_fim" name="data_pagamento_incl_fim" 
                           value="<?php echo $fws_['data_pagamento_incl_fim']?>">
                </div>
                <br>
                <div class="form-group col-sm-12" style="padding-left: 0px;">
                &nbsp;&nbsp;&nbsp;
                <label for="data_inscricao">Data de vencimento do pedido não pagos: </label>
                    &nbsp;&nbsp;
                    <input type="date"  id="data_vencimento_ini" name="data_vencimento_ini" 
                           value="<?php echo $fws_['data_vencimento_ini']?>">
                    &nbsp;&nbsp; à  &nbsp;&nbsp;
                    <input type="date"  id="data_vencimento_fim" name="data_vencimento_fim" 
                           value="<?php echo $fws_['data_vencimento_fim']?>">
                </div>
    <?php }
    public static function  campoVisualizacao($fws_, $objEvConf) { ?>
         <label for="perpectiva">Visualização: </label><br>
            <!-- &nbsp;<input type='radio' name='perspectiva' value='impressao' <?php echo verifItemChecked($fws_['perspectiva'], 'impressao') ?> > Impressão -->
            &nbsp;<input type='radio' name='perspectiva' value='simplificada' <?php echo verifItemChecked($fws_['perspectiva'], 'simplificada') ?> > Simplicada
            &nbsp;<input type='radio' name='perspectiva' value='detalhada'    <?php echo verifItemChecked($fws_['perspectiva'], 'detalhada') ?> > Detalhada
            <!-- &nbsp;<input type='radio' name='perspectiva' value='listagem'     <?php echo verifItemChecked($fws_['perspectiva'], 'listagem') ?>  > Listagem -->
            <?php //if($fws_['perspectiva']!='listagemAvaliacao'){ ?>            
            &nbsp;<input type='radio' name='perspectiva' value='listagemRPA'  <?php echo verifItemChecked($fws_['perspectiva'], 'listagemRPA') ?>  > Listagem RPA
            <?php //} ?>
            <?php if ($objEvConf->getRecebePagamento()==1) {?>
            &nbsp;<input type='radio' name='perspectiva' value='listagemPagamento'  <?php echo verifItemChecked($fws_['perspectiva'], 'listagemPagamento') ?>  > com Pagamento
            <?php } ?>
            &nbsp;<input type='radio' name='perspectiva' value='listagemMBA'  <?php echo verifItemChecked($fws_['perspectiva'], 'listagemMBA') ?>  > Com dados acadêmicos
            <!--&nbsp;<input type='radio' name='perspectiva' value='impressao' <?php echo verifItemChecked($fws_['perspectiva'], 'Impressao') ?>  > Impressão-->
            &nbsp;<input type='radio' name='perspectiva' value='listagemComAtividade' <?php echo verifItemChecked($fws_['perspectiva'], 'listagemComAtividade') ?>  > com <?php echo $objEvConf->getAtividadeTipo()!=''? $objEvConf->getAtividadeTipo() : 'Atividade' ?>
            <?php if ($objEvConf->getTrabAvaliacao()) { ?>
                &nbsp;<input type='radio' name='perspectiva' value='listagemAvaliacao' <?php echo verifItemChecked($fws_['perspectiva'], 'listagemAvaliacao') ?>  > com Avaliação
            <?php } ?>
            <?php if (in_array($objEvConf->getCodEvento(), [80, 88, 115])) { ?>
                &nbsp;<input type='radio' name='perspectiva' value='listagemClassificacao' <?php echo verifItemChecked($fws_['perspectiva'], 'listagemClassificacao') ?>  > Classificação
            <?php } ?>
            <?php if ($objEvConf->getCodEvento()==82) { ?>
                &nbsp;<input type='radio' name='perspectiva' value='listagemComRegistroAtividade' <?php echo verifItemChecked($fws_['perspectiva'], 'listagemComRegistroAtividade') ?>  > com Total Resgistro Atividade
                &nbsp;<input type='radio' name='perspectiva' value='listagemComRegistroAtividadeExcel' <?php echo verifItemChecked($fws_['perspectiva'], 'listagemComRegistroAtividadeExcel') ?>  > com Resgistro Atividade para Excel
            <?php } ?>
            <br>

            <label for="ordenar">Ordenar por: </label>
            &nbsp;<input type='radio' name='ordenar' value='cod_inscrito'  <?php echo verifItemChecked($fws_['ordenar'], 'cod_inscrito')  ?> > Cód.Inscrito
            &nbsp;<input type='radio' name='ordenar' value='nome_inscrito' <?php echo verifItemChecked($fws_['ordenar'], 'nome_inscrito') ?> > Nome do Inscrito
            <?php if ($objEvConf->getRecebePagamento()==1) {?>
                &nbsp;<input type='radio' name='ordenar' value='data_pagamento' <?php echo verifItemChecked($fws_['ordenar'], 'data_pagamento') ?> > Data do Pagamento
            <?php } ?>
            &nbsp;<input type='radio' name='ordenar' value='cpf'           <?php echo verifItemChecked($fws_['ordenar'], 'cpf')           ?> > Cpf/Cnpj
            <?php if($objEvConf->getAtividadeTipo()=='Agenda'){ ?>
            &nbsp;<input type='radio' name='ordenar' value='data_agendamento' <?php echo verifItemChecked($fws_['ordenar'], 'data_agendamento')           ?> > Data do Agendamento
            <?php } ?>
            <?php if ($objEvConf->getTrabAvaliacao()) { ?>
            &nbsp;<input type='radio' name='ordenar' value='nota_informada' <?php echo verifItemChecked($fws_['ordenar'], 'nota_informada')           ?> > Nota Informada
            &nbsp;<input type='radio' name='ordenar' value='nota_confirmada' <?php echo verifItemChecked($fws_['ordenar'], 'nota_confirmada')           ?> > Nota Confirmada
            &nbsp;<input type='radio' name='ordenar' value='pontos_total_final' <?php echo verifItemChecked($fws_['ordenar'], 'pontos_total_final')           ?> > Pontos Total Final
            &nbsp;<input type='radio' name='ordenar' value='classificacao_final' <?php echo verifItemChecked($fws_['ordenar'], 'classificacao_final')           ?> > Classificação Final
            <?php } ?>
    <?php }

    public static function campoAcaoEmLote ($fws_, $objEvConf, $debug) {
            // Quem nao pode executar nao ve a area -- descobrir no erro depois de marcar
            // 200 inscritos e pior do que nao ter a opcao. O gate real esta no
            // construtor do ListarInscritosAcoesAC, que usa a MESMA lista.
            if (!FWA_temPermissao(ListarInscritosAcoesAC::permissoesAcaoEmLote())) {
                return;
            }
            ?>
            <!-- Token CSRF, tambem usado como trava de execucao unica: e sorteado no
                 servidor, fica registrado na sessao e some ao ser usado. Protege de POST
                 forjado de fora E impede que um F5 refaca a acao (pedidos/e-mails/
                 certificados em duplicidade). -->
            <input type="hidden" name="<?php echo ListarInscritosAcoesAC::CAMPO_TOKEN_LOTE ?>"
                   value="<?php echo ListarInscritosAcoesAC::novoTokenAcaoLote() ?>">
            <label for="acaoEmLote">Ação em Lote:</label> (É necessário selecionar um evento no filtro) <br>
            <!-- Select em vez de radios: a lista cresceu e quebrava em duas linhas.
                 A acao escolhida VOLTA selecionada apos o envio, senao o formulario dela
                 some da tela e quem executou fica sem saber o que aconteceu. Isso e seguro
                 porque quem impede a repeticao e o token acima, nao o select em branco. -->
            &nbsp;<select name="acaoEmLote" id="acaoEmLote" style="max-width:340px">
                <option value="" <?php echo verifItemSelected($fws_['acaoEmLote'], '') ?>>Nenhuma ação</option>
                <?php if ($_SERVER['REMOTE_ADDR']=='177.82.0.28') {?>
                <option value="enviarComunicadoWhats" <?php echo verifItemSelected($fws_['acaoEmLote'], 'enviarComunicadoWhats') ?>>Enviar comunicado via WhatsApp</option>
                <?php } ?>
                <option value="enviarComunicados" <?php echo verifItemSelected($fws_['acaoEmLote'], 'enviarComunicados') ?>>Enviar comunicados</option>
                <option value="definirPresenca" <?php echo verifItemSelected($fws_['acaoEmLote'], 'definirPresenca') ?>>Definir presença</option>
                <option value="gerarCertificado" <?php echo verifItemSelected($fws_['acaoEmLote'], 'gerarCertificado') ?>>Gerar Certificados em Lote</option>
                <?php if (in_array($objEvConf->getCodEvento(), [80, 82])) {?>
                <option value="alterarStatusProcesso" <?php echo verifItemSelected($fws_['acaoEmLote'], 'alterarStatusProcesso') ?>>Alterar Status no Processo</option>
                <?php } ?>
                <?php if ($objEvConf->getRecebePagamento()==true) {?>
                <option value="gerarMensalidade" <?php echo verifItemSelected($fws_['acaoEmLote'], 'gerarMensalidade') ?>>Gerar Despesas/Pedidos em Lote</option>
                <option value="alterarVencimento" <?php echo verifItemSelected($fws_['acaoEmLote'], 'alterarVencimento') ?>>Alterar Vencimento em Lote</option>
                <?php } ?>
            </select>
            <br>

            <!-- Titulo da acao escolhida: o verificarAcao() MOVE este bloco para dentro do
                 cartao da acao, como cabecalho dele -- solto acima do cartao ficava orfao.
                 Para as acoes sem formulario proprio (definir presenca, certificados) o
                 destino e o cartao abaixo, que so existe para dar esse retorno na tela. -->
            <div id="tituloAcaoLote" style="display:none;">
                <div style="font-weight:600; color:#2d6a4f; font-size:14px;
                            border-bottom:1px solid #e6e6e6; padding-bottom:8px; margin-bottom:12px;">
                    <i class="fa fa-bolt" style="margin-right:6px;"></i><span id="tituloAcaoLoteTexto"></span>
                </div>
            </div>
            <div id="divAcaoSemFormulario" style="display:none; margin-top:10px; margin-bottom:20px;" class="aba-filtro">
                <p style="margin:0; color:#666; font-size:13px;">
                    Confira os filtros acima e clique em <b>Listar na Tela</b> para executar.
                </p>
            </div>

            <?php if (in_array($objEvConf->getCodEvento(), [80, 82])) {?>
            <div id="divStatusProcesso" style="display: none;margin-top: 10px;margin-bottom: 20px;" class="aba-filtro">
                <label for="statusProcessoNovo">Novo status no processo:</label>
                    <select name="statusProcessoNovo" id="statusProcessoNovo">
                        <option value="" <?php echo verifItemSelected($fws_['statusProcessoNovo'], '') ?>></option>
                        <option value="INSCRITO" <?php echo verifItemSelected($fws_['statusProcessoNovo'], 'INSCRITO') ?>>Inscrito</option>
                        <option value="CONVOCADO" <?php echo verifItemSelected($fws_['statusProcessoNovo'], 'CONVOCADO') ?>>Convocado</option>
                        <option value="PARTICIPANDO" <?php echo verifItemSelected($fws_['statusProcessoNovo'], 'PARTICIPANDO') ?>>Participando</option>
                        <option value="DESISTENTE" <?php echo verifItemSelected($fws_['statusProcessoNovo'], 'DESISTENTE') ?>>Desistente</option>
                    </select>
            </div>
            <?php } ?>
            <?php if ($objEvConf->getRecebePagamento()==true) {?>

            <!-- Div contendo o select, inicialmente oculto -->
            <div id="divSelectTaxas" style="display: none;margin-top: 10px;margin-bottom: 20px;" class="aba-filtro">
                <label>Selecione a taxa que deseja aplicar aos listados:</label>
                <select id="selectTaxas" name="cod_taxa">
                    <option value="-1">Definindo manualmente</option>
                    <?php 
                    CarregarOptionsAC::taxas($fws_, $debug);
                    ?>
                </select><br>
                <label>Tipo da Despesa/Pedido:</label>
                <select id="tipoPedido" name="tipoPedido">
                    <option value="">Inscrição</option>
                    <option value="mensalidade" <?php echo verifItemSelected($fws_['tipoPedido'], 'mensalidade') ?>>Mensalidade</option>
                    <option value="extra" <?php echo verifItemSelected($fws_['tipoPedido'], 'extra') ?>>Extra</option>
                </select><br>
                <?php require dirname(__FILE__).'/../view/admin/acao-lote-mensalidade-dica.php'; ?>
                <div id="camposExtras">
                <label for="referencia">Mês de referência ou texto para agrupar:
                    <div class="tooltip"> ? <span class="tooltiptext">ex: Agosto ou Agosto-2024 ou Refeição</span></div>
                 </label>    
                    <input type="text" id="referencia" value="<?php echo $fws_['referencia']?>" name="referencia" size="60" maxlength="50"><br>
                <label for="prefixoPedido">Acrescente Prefixo para Descrição do Pedido:
                    <div class="tooltip"> ? <span class="tooltiptext">ex: 1º Mensalidade ou Inscrição ou Matrícula</span></div>
                 </label>    
                    <input type="text" id="prefixoPedido" value="<?php echo $fws_['prefixoPedido']?>" name="prefixoPedido" size="60" maxlength="50"><br>

                <label for="datVencimento">Data de Vencimento:</label>
                    <input value="<?php echo $fws_['datVencimento']?>"  type="date" id="datVencimento" name="datVencimento"><br>


                    <label for="valor">Valor (R$):</label>
                    <input type="text" id="valorPedido" name="valorPedido" placeholder="0.00" value="<?php echo $fws_['valorPedido']?>"><br>
                </div>

                <?php require dirname(__FILE__).'/../view/admin/acao-lote-mensalidade-campos.php'; ?>

                <?php // teste x execucao real: tambem volta marcado, senao quem testou nao
                      // enxerga em que modo rodou e repete o envio no escuro ?>
                <input type="radio" id="acaoPedidoGerar" name="acaoPedido" value="gerarPedido" <?php echo verifItemChecked($fws_['acaoPedido'], 'gerarPedido') ?>>
                <label for="acaoPedidoGerar">Gerar Pedido/Taxa</label><br>
                  <input type="radio" id="acaoPedidoTestar" name="acaoPedido" value="testarPedido" <?php echo verifItemChecked($fws_['acaoPedido'], 'testarPedido') ?>>
                <label for="acaoPedidoTestar">Testar e visualizar resultado Pedido/Taxa</label><br>
            </div>

            <!-- Troca so o dia do vencimento das parcelas ja geradas. Mes e ano de cada
                 parcela ficam como estao: o que muda e o dia combinado no contrato. -->
            <div id="divAlterarVencimento" style="display: none;margin-top: 10px;margin-bottom: 20px;" class="aba-filtro">
                <label for="novoDiaVencimento">Novo dia de vencimento:</label>
                <input type="number" id="novoDiaVencimento" name="novoDiaVencimento" min="1" max="31"
                       style="width:70px" value="<?php echo $fws_['novoDiaVencimento']?>">
                <div class="tooltip"> ? <span class="tooltiptext">Mês que não tem o dia (31 em fevereiro) recebe o último dia do mês</span></div>
                <br>
                <span style="font-size:12px;color:#777">
                    Mês e ano de cada parcela não mudam — só o dia.
                    Parcela já paga ou já vencida não é alterada.
                    <?php if ($objEvConf->getMensDiaVenc()) { ?>
                        Dia atual configurado no evento: <b><?php echo $objEvConf->getMensDiaVenc() ?></b>.
                    <?php } ?>
                </span><br>
                <label style="font-weight:normal">
                    <input type="checkbox" name="atualizarDiaVencEvento" value="1"
                           <?php echo $fws_['atualizarDiaVencEvento']? 'checked' : '' ?>>
                    Atualizar também o dia padrão de vencimento na configuração do evento
                </label><br>

                <input type="radio" id="acaoVencimentoGravar" name="acaoVencimento" value="gravar">
                <label for="acaoVencimentoGravar">Alterar vencimentos</label><br>
                <input type="radio" id="acaoVencimentoTestar" name="acaoVencimento" value="testar" checked>
                <label for="acaoVencimentoTestar">Testar e visualizar o resultado</label><br>
            </div>
            <?php } ?>
    <?php }
    public static function campoContratos ($objEvConf, $fws_) { ?>
                <div class="form-group col-sm-12" style="padding-left: 0px;">
            <?php if ($objEvConf->getTrabSubmissao()) { ?>
            <label for="pagamentos">Qtd.Trabalhos/Documentos: </label>
            &nbsp;<input type='radio' name='trabalhos' value=''    <?php echo verifItemChecked($fws_['trabalhos'], '') ?> > Todos 
            &nbsp;<input type='radio' name='trabalhos' value='-1'  <?php echo verifItemChecked($fws_['trabalhos'], '-1') ?> > Nenhum 
            &nbsp;<input type='radio' name='trabalhos' value='1'   <?php echo verifItemChecked($fws_['trabalhos'], '1') ?> > Um
            &nbsp;<input type='radio' name='trabalhos' value='1or' <?php echo verifItemChecked($fws_['trabalhos'], '1or') ?> > Uma ou mais
            &nbsp;<input type='radio' name='trabalhos' value='2'   <?php echo verifItemChecked($fws_['trabalhos'], '2') ?> > Duas ou mais
            <?php } ?>
            <?php if ($objEvConf->getContratoAnexar()) { ?>
            <br>    
            <label for="contratos">Contratos Anexados: </label>
            &nbsp;<input type='radio' name='contrato_anexo' value=''    <?php echo verifItemChecked($fws_['contrato_anexo'], '') ?> > Todos 
            &nbsp;<input type='radio' name='contrato_anexo' value='-1'    <?php echo verifItemChecked($fws_['contrato_anexo'], '-1') ?> > Não anexado
            &nbsp;<input type='radio' name='contrato_anexo' value='1'    <?php echo verifItemChecked($fws_['contrato_anexo'], '1') ?> > Anexado
            <?php } ?>
            <?php if ($objEvConf->getTrabAnexoTipo() == "Contrato de Prestação de Serviço" || $objEvConf->getTrabAnexoTipo() == "Documento" || $objEvConf->getTrabAnexoTipo() == "Documentos" ) { ?>
                    <label for="contratos">Contratos Assinados: </label>
                    &nbsp;<input type='radio' name='flg_assinado' value=''    <?php echo verifItemChecked($fws_['flg_assinado'], '') ?> > Todos 
                    &nbsp;<input type='radio' name='flg_assinado' value='-1'    <?php echo verifItemChecked($fws_['flg_assinado'], '-1') ?> > Não Assinados 
                    &nbsp;<input type='radio' name='flg_assinado' value='1'    <?php echo verifItemChecked($fws_['flg_assinado'], '1') ?> > Assinados
                <?php } ?>
            <?php if ($objEvConf->getAvulsoAnexar()) { ?>
            <br>    
            <label for="contratos">RPA Anexada: </label>
            &nbsp;<input type='radio' name='rpa_anexo' value=''    <?php echo verifItemChecked($fws_['rpa_anexo'], '') ?> > Todos 
            &nbsp;<input type='radio' name='rpa_anexo' value='-1'    <?php echo verifItemChecked($fws_['rpa_anexo'], '-1') ?> > Não anexado
            &nbsp;<input type='radio' name='rpa_anexo' value='1'    <?php echo verifItemChecked($fws_['rpa_anexo'], '1') ?> > Anexado
            <br>    
            <label for="contratos">Comprovante Transferência Anexada: </label>
            &nbsp;<input type='radio' name='comp_transf_anexo' value=''    <?php echo verifItemChecked($fws_['comp_transf_anexo'], '') ?> > Todos 
            &nbsp;<input type='radio' name='comp_transf_anexo' value='-1'    <?php echo verifItemChecked($fws_['comp_transf_anexo'], '-1') ?> > Não anexado
            &nbsp;<input type='radio' name='comp_transf_anexo' value='1'    <?php echo verifItemChecked($fws_['comp_transf_anexo'], '1') ?> > Anexado
            <br>    
            <label for="contratos">Termo de Desistencia: </label>
            &nbsp;<input type='radio' name='termo_desist_anexo' value=''    <?php echo verifItemChecked($fws_['comp_transf_anexo'], '') ?> > Todos 
            &nbsp;<input type='radio' name='termo_desist_anexo' value='-1'    <?php echo verifItemChecked($fws_['termo_desist_anexo'], '-1') ?> > Não anexado
            &nbsp;<input type='radio' name='termo_desist_anexo' value='1'    <?php echo verifItemChecked($fws_['termo_desist_anexo'], '1') ?> > Anexado
            <?php } ?>
            <?php if ($objEvConf->getBoletoAnexar()) { ?>
            <br>    
            <label for="boletos">Boletos Anexados: </label>
            &nbsp;<input type='radio' name='boletos' value=''    <?php echo verifItemChecked($fws_['boletos'], '') ?> > Todos 
            &nbsp;<input type='radio' name='boletos' value='-1'    <?php echo verifItemChecked($fws_['boletos'], '-1') ?> > Não anexado
            &nbsp;<input type='radio' name='boletos' value='1'    <?php echo verifItemChecked($fws_['boletos'], '1') ?> > Anexado
            <?php } ?>
            <!-- &nbsp;<input type='radio' name='contratos' value='2'    <?php echo verifItemChecked($fws_['contratos'], '2') ?> > Duas ou mais -->

            <?php if ($objEvConf->getTrabAvaliacao() and $objEvConf->getTrabAvaliacaoPor()=='ARQUIVO') { ?>
                <br>    
            <label for="avaliacao_trabalhos">Avalição de Trabalhos: </label>
            &nbsp;<input type='radio' name='avaliacao_trabalhos' value=''    <?php echo verifItemChecked($fws_['avaliacao_trabalhos'], '') ?> > Todos 
            &nbsp;<input type='radio' name='avaliacao_trabalhos' value='1'    <?php echo verifItemChecked($fws_['avaliacao_trabalhos'], '1') ?> > 1 Trab.Aprovado
            &nbsp;<input type='radio' name='avaliacao_trabalhos' value='2'    <?php echo verifItemChecked($fws_['avaliacao_trabalhos'], '2') ?> > 2 Trab.Aprovado
            &nbsp;<input type='radio' name='avaliacao_trabalhos' value='3'    <?php echo verifItemChecked($fws_['avaliacao_trabalhos'], '3') ?> > 3 Trab.Aprovado
            
            <br>
            <?php } ?>
            <?php if (in_array($objEvConf->getCodEvento(), [80, 88, 115])) { ?>
            <br>
            <label for="docs_contratacao">Docs Contratação: </label>
            &nbsp;<input type='radio' name='docs_contratacao' value=''          <?php echo verifItemChecked($fws_['docs_contratacao'], '') ?> > Todos
            &nbsp;<input type='radio' name='docs_contratacao' value='pendente'  <?php echo verifItemChecked($fws_['docs_contratacao'], 'pendente') ?> > Pendente
            &nbsp;<input type='radio' name='docs_contratacao' value='completo'  <?php echo verifItemChecked($fws_['docs_contratacao'], 'completo') ?> > Completo
            <br>
            <label>Falta documento específico: </label>
            <?php
            $docsFalta = is_array($fws_['doc_contratacao_falta']) ? $fws_['doc_contratacao_falta'] : [];
            $docsOpcoes = [
                'ctps' => 'CTPS',
                'certidao_nascimento' => 'Certidão Nasc/Casam.',
                'foto_cracha' => 'Foto Crachá',
                'certificado_reservista' => 'Reservista',
                'certidao_antecedentes' => 'Antecedentes',
            ];
            foreach ($docsOpcoes as $slug => $label) {
                $checked = in_array($slug, $docsFalta) ? 'checked' : '';
                echo "&nbsp;<input type='checkbox' name='doc_contratacao_falta[]' value='{$slug}' {$checked}> {$label} ";
            }
            ?>
            <br>
            <?php } ?>
    <?php }
    public static function camposAvaliacao($objEvConf, $fws_) { ?>
            <?php if (in_array($objEvConf->getCodEvento(), [80, 88, 115])) { ?>
            <br><label for="classificacao_ate">Classificação final até posição: </label>
            &nbsp;<input type="number" name="classificacao_ate" value="<?php echo $fws_['classificacao_ate'] ?>" style="width:80px;" min="1" placeholder="ex: 150">
            
            <?php } ?>
            <?php if ($objEvConf->getTrabAvaliacaoPermitirRecurso()) { ?>
                <br>
                    <label for="ordenar">Solicitaram Recurso: </label>
                &nbsp;<input type='radio' name='solicitaram_recurso' value=''    <?php echo verifItemChecked($fws_['solicitaram_recurso'], '') ?> > Todos
                &nbsp;<input type='radio' name='solicitaram_recurso' value='SIM' <?php echo verifItemChecked($fws_['solicitaram_recurso'], 'SIM') ?> > Recurso Solicitado
                &nbsp;<input type='radio' name='solicitaram_recurso' value='NAO' <?php echo verifItemChecked($fws_['solicitaram_recurso'], 'NAO') ?> > Recurso Não Solicitado
            <?php } ?>
            <?php if ($objEvConf->getTrabAvaliacao() and $objEvConf->getTrabAvaliacaoPor()=='CANDIDATO') { ?>
                <br>
                    <label for="ordenar">Avaliação de Inscrito: </label>
                &nbsp;<input type='radio' name='avaliacao_inscrito' value=''    <?php echo verifItemChecked($fws_['avaliacao_inscrito'], '') ?> > Todos
                &nbsp;<input type='radio' name='avaliacao_inscrito' value='AVALIADO' <?php echo verifItemChecked($fws_['avaliacao_inscrito'], 'AVALIADO') ?> > Avaliado
                &nbsp;<input type='radio' name='avaliacao_inscrito' value='NAO_AVALIADO' <?php echo verifItemChecked($fws_['avaliacao_inscrito'], 'NAO_AVALIADO') ?> >Não Avaliado
                &nbsp;<input type='radio' name='avaliacao_inscrito' value='HABILITADO' <?php echo verifItemChecked($fws_['avaliacao_inscrito'], 'HABILITADO') ?> >Habilitado
                &nbsp;<input type='radio' name='avaliacao_inscrito' value='NAO_HABILITADO' <?php echo verifItemChecked($fws_['avaliacao_inscrito'], 'NAO_HABILITADO') ?> >Não Habilitado
                &nbsp;<input type='radio' name='avaliacao_inscrito' value='RECURSO_AVALIADO' <?php echo verifItemChecked($fws_['avaliacao_inscrito'], 'RECURSO_AVALIADO') ?> >Recurso Avaliado
                &nbsp;<input type='radio' name='avaliacao_inscrito' value='RECURSO_NAO_AVALIADO' <?php echo verifItemChecked($fws_['avaliacao_inscrito'], 'RECURSO_NAO_AVALIADO') ?> >Recurso Não Avaliado
                &nbsp;<input type='radio' name='avaliacao_inscrito' value='HABILITADO_FINAL' <?php echo verifItemChecked($fws_['avaliacao_inscrito'], 'HABILITADO_FINAL') ?> >Habilitado Final
            <?php } ?>
        <?php
        }
        public static function camposAvitivades($objEvConf, $fws_) {
        ?>  
            <?php if ($objEvConf->getAtividade()) { ?>
                <br>
            <label for="ordenar">Qtd.<?php echo $objEvConf->getAtividadeTipo()!=''? $objEvConf->getAtividadeTipo() : 'Atividades' ?>: </label>
            &nbsp;<input type='radio' name='oficinas' value=''    <?php echo verifItemChecked($fws_['oficinas'], '') ?> > Todos 
            &nbsp;<input type='radio' name='oficinas' value='-1'    <?php echo verifItemChecked($fws_['oficinas'], '-1') ?> > Nenhum 
            &nbsp;<input type='radio' name='oficinas' value='1'    <?php echo verifItemChecked($fws_['oficinas'], '1') ?> > Uma ou mais 
            &nbsp;<input type='radio' name='oficinas' value='2'    <?php echo verifItemChecked($fws_['oficinas'], '2') ?> > Duas ou mais
            <?php } ?>
        </div>
    <?php }
}