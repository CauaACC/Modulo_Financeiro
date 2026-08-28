<?php require_once 'inscricao_congresso_ok_menu_principal_style.php'; ?>

<body>
    <div id="tela-inicial-container">
        <div id="home" class="section home">
            <div class="">
                <div class="menu-option" onclick="showSection('avisos')">
                    <i class="glyphicon glyphicon-bell"></i>
                    <div class="menu-text">
                        <span>Avisos 
                            <?php 
                            $objMsgAviso = new MsgAviso();
                            $objMsgAviso->setCodEvento($objEv->getCodEvento());
                            $objMsgAviso->setCodInscrito($objInscrito->getCodInscrito());
                            $objMsgAviso->setCodPerfil($objInscrito->getCodPerfil());
                            echo '(' . $objMsgAviso->getTotalMsgAvisosForAreaInscrito() . ')';
                            ?>
                        </span>
                        <p>Consulte os avisos e novidades importantes para o seu processo.</p>
                    </div>
                </div>  
                <div class="menu-option" onclick="showSection('inscricao')">
                    <i class="glyphicon glyphicon-pencil"></i>
                    <div class="menu-text">
                        <span>Contratos e Documentos</span>
                        <p>Assine ou consulte seu contrato, informe não aceitação da vaga ou solicite desligamento caso já tenha assinado.</p>
                    </div>
                </div>
                <?php /**@todo acrescentar configuração para liberar registro de atividade por perfil */?>
                <?php if (!in_array($objInscrito->getCodPerfil(), ['469','476','477'])) {?>            
                <div class="menu-option" onclick="showSection('registroAtividades')">
                    <i class="glyphicon glyphicon-book"></i>
                    <div class="menu-text">
                        <span>Registro de Atividades</span>
                        <p>Registre as ações realizadas durante o projeto. O registro diário das atividades é necessário para a liberação dos benefícios.</p>
                    </div>
                </div>
                <div class="menu-option" onclick="showSection('demonstrativosPagamento')">
                    <i class="glyphicon glyphicon-usd"></i>
                    <div class="menu-text">
                        <span>Pagamentos</span>
                        <p>Consulte seus comprovantes de benefícios ou Recibos de Pagamento Autônomo (RPA).</p>
                    </div>
                </div>
                <?php } ?>
                <?php /**@todo acrescentar configuração para liberar avaliação de registro de atividade por perfil */?>
                <?php if (in_array($objInscrito->getCodPerfil(), ['469','477'])) {?>              
                    <div class="menu-option" onclick="showSection('registroAtividadesAvaliacao')">
                    <i class="glyphicon glyphicon-book"></i>
                    <div class="menu-text">
                        <span>Validação de Atividades</span>
                        <p>Valide as ações realizadas durante o projeto. A Validação das atividades deve ser executada semanalmente e é necessária para a liberação dos benefícios ao prestadores de serviço.</p>
                    </div>
                </div>
                <?php } ?>                
                <div class="menu-option" onclick="showSection('materiaisCurso')">
                    <i class="glyphicon glyphicon-education"></i>
                    <div class="menu-text">
                        <span>Materiais de Apoio</span>
                        <p>Acesse os materiais didáticos e conteúdos dos cursos disponíveis.</p>
                    </div>
                </div>                
            </div>
        </div>


        <!-- Seção de Avisos -->
        <div id="avisos" class="section">
            <h2><a onclick="showSection('home')">
            <i class="glyphicon glyphicon-arrow-left"></i></a>Avisos
             <?php echo '(' . $objMsgAviso->getTotalMsgAvisosForAreaInscrito() . ')'; ?></h2>
            <?php require_once('inscricao_congresso_avisos.php'); ?>
            <a class="back-btn" onclick="showSection('home')">Voltar para Tela Inicial</a>
        </div>

        <!-- Seção de Contratos e Documentos -->
            <div id="inscricao" class="section">
            <h2><a onclick="showSection('home')"><i class="glyphicon glyphicon-arrow-left"></i></a>Contratos e Documentos</h2>
            <b>Solicitação de desligamento e outras informações administrativas:</b>
            <br>E-mail: projetoformadores25e26@hutec.com.br
            <br><a href="https://wa.me/5543991220724" target="_blank">WhatsApp via Chat (43) 99122-0724</a>
            <?php
                if(in_array($objEv->getCodEvento(), [80])){
                    /** removido durante inscrição */
                    require_once('inscricao_trabalhos_consulta_trabalhos_submetidos_edital_recurso.php');
                
                }else if(in_array($objEv->getCodEvento(), [37, 38, 46, 69])){
                    /** removido durante inscrição */
                    require_once('inscricao_trabalhos_consulta_trabalhos_submetidos_edital.php');
                    if(trim($objInscrito->getStatusProcesso())=='CONVOCADO'){
                        require_once('inscricao_consulta_aptos.php');
                    }                    
                }else{
                    require_once('inscricao_trabalhos_consulta_trabalhos_submetidos.php');
                }
                ?>    
                <!-- -->
                <?php if (in_array($objEv->getCodEvento(), [37, 38, 46, 69])) {?>
                <?php
                    // if($_SERVER['SERVER_ADDR']=='189.90.70.56'){
                    //     echo __LINE__;
                    //     require_once('inscricao_consulta_aptos.php');
                    // }
                ?>
            <!-- -->
            <?php } ?>
            <a class="back-btn" onclick="showSection('home')">Voltar para Tela Inicial</a>
            </div>
            <?php /**@todo acrescentar configuração para liberar registro de atividade por perfil */?>
            <?php if (!in_array($objInscrito->getCodPerfil(), ['469','476','477'])) {?>
            <!-- Seção de Registro de Atividades -->
            <div id="registroAtividades" class="section">
                <h2><a onclick="showSection('home')"><i class="glyphicon glyphicon-arrow-left"></i></a>Registro de Atividades</h2>
                <!-- Acompanhe suas atividades registradas. -->
                <?php require_once(dirname(__FILE__).'/../../view/admin/GerenciarRegistroAtividade.php'); ?>
                <a class="back-btn" onclick="showSection('home')">Voltar para Tela Inicial</a>
            </div>

            <!-- Seção de Demonstrativos de Pagamento -->
            <div id="demonstrativosPagamento" class="section">
                <h2><a onclick="showSection('home')"><i class="glyphicon glyphicon-arrow-left"></i></a>Demonstrativos de Pagamento</h2>
                <p>Acesso aos comprovantes de pagamento.</p>
                <ul>
                    <!-- <li><a href="path/to/material1.pdf" download>Baixe o material do curso X</a></li> -->
                    <li>Nenhum comprovante disponível no momento</li>
                </ul>                
                <?php
                    if(in_array($objEv->getCodEvento(), [80])){
                        /** removido durante inscrição */
                        require_once('inscricao_trabalhos_consulta_trabalhos_submetidos_edital_recurso.php');
                    
                    }else if(in_array($objEv->getCodEvento(), [37, 38, 46, 69])){
                        /** removido durante inscrição */
                        require_once('inscricao_trabalhos_consulta_trabalhos_submetidos_edital.php');
                        if(trim($objInscrito->getStatusProcesso())=='CONVOCADO'){
                            require_once('inscricao_consulta_aptos.php');
                        }                    
                    }else{
                        require_once('inscricao_trabalhos_consulta_trabalhos_submetidos.php');
                    }
                    ?>    
                    <!-- -->
                    <?php if (in_array($objEv->getCodEvento(), [37, 38, 46, 69])) {?>
                    <?php
                        // if($_SERVER['SERVER_ADDR']=='189.90.70.56'){
                        //     echo __LINE__;
                        //     require_once('inscricao_consulta_aptos.php');
                        // }
                    ?>
                <!-- -->
                <?php } ?>
                <a class="back-btn" onclick="showSection('home')">Voltar para Tela Inicial</a>
            </div>
            <?php } ?>
            <!-- Seção de Materiais de Curso -->
            <div id="materiaisCurso" class="section">
                <h2><a onclick="showSection('home')"><i class="glyphicon glyphicon-arrow-left"></i></a>Materiais de Apoio</h2>
                <p>Acesse os materiais didáticos e conteúdos de cursos disponíveis para seu aprendizado.</p>
                <ul>
                    <!-- <li><a href="path/to/material1.pdf" download>Baixe o material do curso X</a></li> -->
                    <li>Nenhum material disponível no momento</li>
                </ul>
                <a class="back-btn" onclick="showSection('home')">Voltar para Tela Inicial</a>
            </div>


        <?php if ($objInscrito->getCodPerfil()=='469') {?>
        <!-- Seção de Avaliação de Atividades -->
        <div id="registroAtividadesAvaliacao" class="section">
            <h2><a onclick="showSection('home')"><i class="glyphicon glyphicon-arrow-left"></i></a>Validação de Atividades</h2>
            Avalidor do Núcleo
            <?php require_once(dirname(__FILE__).'/../../view/admin/GerenciarRegistroAtividadeAvaliacao.php'); ?>
            <a class="back-btn" onclick="showSection('home')">Voltar para Tela Inicial</a>
        </div>
        <?php } ?>
    </div>

</body>
</html>
