<?php
//require_once(dirname(__FILE__) . "/../../view/admin/consulta-dados-congresso-seguranca.php");
$objSRV = new GerenciarProjetoAC();
?>
<?php require(dirname(__FILE__)."/../../view/coordenador/cabecalho.php"); ?>
<?php require(dirname(__FILE__)."/../../view/coordenador/area_menu_coordenador.php"); ?>
<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Cadastro de Projetos/Eventos e Cursos</h4>
        </div>
        <div class="panel-body">
            <style>
            /* ===== WIZARD CONTAINER ===== */
            .wizard-container {
                max-width: 1100px;
                margin: 0 auto;
                padding: 10px 0 0;
            }

            /* ===== PROGRESS BAR ===== */
            .wizard-progress {
                background: #e9ecef;
                border-radius: 20px;
                height: 6px;
                margin-bottom: 36px;
                overflow: hidden;
            }
            .wizard-progress-bar {
                height: 100%;
                background: linear-gradient(90deg, #2D937E 0%, #34d399 100%);
                border-radius: 20px;
                transition: width 0.5s cubic-bezier(.4,0,.2,1);
            }

            /* ===== STEPPER ===== */
            .wizard-stepper {
                display: flex;
                justify-content: center;
                align-items: flex-start;
                margin-bottom: 36px;
                position: relative;
                padding: 0 20px;
            }
            .wizard-stepper .step-item {
                text-align: center;
                position: relative;
                flex: 1;
                cursor: pointer;
                z-index: 2;
                -webkit-user-select: none;
                user-select: none;
            }
            .wizard-stepper .step-icon {
                width: 44px;
                height: 44px;
                border-radius: 50%;
                background: #fff;
                color: #adb5bd;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto;
                font-size: 16px;
                transition: all 0.35s cubic-bezier(.4,0,.2,1);
                position: relative;
                border: 3px solid #dee2e6;
                box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            }
            .wizard-stepper .step-item.active .step-icon {
                background: linear-gradient(135deg, #2D937E 0%, #34d399 100%);
                color: #fff;
                border-color: #2D937E;
                box-shadow: 0 6px 20px rgba(45,147,126,0.35);
                transform: scale(1.12);
            }
            .wizard-stepper .step-item.completed .step-icon {
                background: #0d9668;
                color: #fff;
                border-color: #0d9668;
                box-shadow: 0 3px 12px rgba(13,150,104,0.25);
            }
            .wizard-stepper .step-label {
                margin-top: 8px;
                font-size: 11px;
                color: #adb5bd;
                font-weight: 500;
                transition: all 0.3s ease;
                letter-spacing: 0.01em;
            }
            .wizard-stepper .step-item.active .step-label {
                color: #2D937E;
                font-weight: 700;
            }
            .wizard-stepper .step-item.completed .step-label {
                color: #0d9668;
                font-weight: 600;
            }

            /* Linhas conectoras */
            .wizard-stepper .step-line {
                flex: 1;
                height: 3px;
                background: #dee2e6;
                margin: 21px -5px 0;
                position: relative;
                z-index: 1;
                border-radius: 3px;
                transition: background 0.5s cubic-bezier(.4,0,.2,1);
            }
            .wizard-stepper .step-line.completed {
                background: linear-gradient(90deg, #0d9668, #34d399);
            }

            /* ===== WIZARD CARD ===== */
            .wizard-card {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 4px 24px rgba(0,0,0,0.06), 0 1px 4px rgba(0,0,0,0.04);
                padding: 32px;
                margin-bottom: 20px;
                border: 1px solid rgba(0,0,0,0.06);
            }
            .wizard-card::after {
                content: '';
                display: table;
                clear: both;
            }
            .wizard-card-header {
                display: flex;
                align-items: center;
                margin-bottom: 24px;
                padding-bottom: 16px;
                border-bottom: 2px solid #f1f5f9;
            }
            .wizard-card-header .header-icon {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                background: linear-gradient(135deg, rgba(45,147,126,0.1) 0%, rgba(52,211,153,0.1) 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 14px;
                flex-shrink: 0;
            }
            .wizard-card-header .header-icon i {
                font-size: 20px;
                color: #2D937E;
            }
            .wizard-card-header h4 {
                margin: 0;
                color: #1e293b;
                font-weight: 700;
                font-size: 18px;
            }
            .wizard-card-header .step-badge {
                margin-left: auto;
                background: linear-gradient(135deg, #e8f5f1 0%, #d1fae5 100%);
                color: #0d9668;
                padding: 5px 14px;
                border-radius: 20px;
                font-size: 11.5px;
                font-weight: 700;
                white-space: nowrap;
                letter-spacing: 0.03em;
                text-transform: uppercase;
            }

            /* ===== BOTOES ===== */
            .wizard-nav {
                clear: both;
                display: flex;
                justify-content: space-between;
                padding-top: 24px;
                margin-top: 24px;
                border-top: 2px solid #f1f5f9;
            }
            .wizard-nav .btn {
                padding: 11px 28px;
                border-radius: 10px;
                font-weight: 600;
                font-size: 14px;
                transition: all 0.25s cubic-bezier(.4,0,.2,1);
                letter-spacing: 0.01em;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            .btn-wizard-next {
                background: linear-gradient(135deg, #2D937E 0%, #0d9668 100%);
                color: #fff !important;
                border: none;
                box-shadow: 0 2px 8px rgba(45,147,126,0.2);
            }
            .btn-wizard-next:hover {
                background: linear-gradient(135deg, #247a68 0%, #047857 100%);
                color: #fff !important;
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(45,147,126,0.35);
            }
            .btn-wizard-prev {
                background: #f8fafc;
                color: #64748b !important;
                border: 2px solid #e2e8f0;
            }
            .btn-wizard-prev:hover {
                background: #f1f5f9;
                color: #334155 !important;
                border-color: #cbd5e1;
            }
            .btn-wizard-save {
                background: linear-gradient(135deg, #0d9668 0%, #059669 100%);
                color: #fff !important;
                border: none;
                box-shadow: 0 2px 8px rgba(13,150,104,0.2);
            }
            .btn-wizard-save:hover {
                background: linear-gradient(135deg, #047857 0%, #065f46 100%);
                color: #fff !important;
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(13,150,104,0.35);
            }

            /* ===== TAB PANES ===== */
            .wizard-container .wz-step {
                display: none;
            }
            .wizard-container .wz-step.wz-active {
                display: block;
                animation: wzFadeIn 0.35s cubic-bezier(.4,0,.2,1);
            }
            @keyframes wzFadeIn {
                from { opacity: 0; transform: translateY(12px); }
                to { opacity: 1; transform: translateY(0); }
            }

            /* ===== REVISAO ===== */
            .revisao-secao {
                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                border-radius: 12px;
                padding: 22px;
                margin-bottom: 16px;
                border-left: 4px solid #2D937E;
                border: 1px solid #e2e8f0;
                border-left: 4px solid #2D937E;
            }
            .revisao-secao h5 {
                color: #1e293b;
                margin-bottom: 14px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-weight: 700;
                font-size: 15px;
            }
            .revisao-secao .btn-edit {
                font-size: 12px;
                color: #2D937E;
                text-decoration: none;
                border: 1.5px solid #2D937E;
                padding: 3px 12px;
                border-radius: 14px;
                font-weight: 600;
                transition: all 0.2s ease;
                display: inline-flex;
                align-items: center;
                gap: 4px;
            }
            .revisao-secao .btn-edit:hover {
                background: #2D937E;
                color: #fff;
            }
            .revisao-campo {
                display: flex;
                padding: 8px 0;
                border-bottom: 1px solid #e2e8f0;
            }
            .revisao-campo:last-child {
                border-bottom: none;
            }
            .revisao-campo .label-rev {
                width: 200px;
                font-weight: 600;
                color: #64748b;
                flex-shrink: 0;
                font-size: 13px;
            }
            .revisao-campo .valor-rev {
                color: #1e293b;
                font-size: 13px;
            }

            /* ===== TERMOS ===== */
            .termos-box {
                background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
                border: 1.5px solid #f59e0b;
                border-radius: 12px;
                padding: 16px 22px;
                margin: 24px 0;
            }
            .termos-box label {
                margin: 0;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 10px;
                font-weight: 500;
                color: #92400e;
                font-size: 14px;
            }

            /* ===== FORM STYLING ===== */
            .wizard-card .form-control {
                border-radius: 8px;
                border: 1.5px solid #e2e8f0;
                padding: 8px 12px;
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
            }
            .wizard-card .form-control:focus {
                border-color: #2D937E;
                box-shadow: 0 0 0 3px rgba(45,147,126,0.12);
            }

            /* ===== CAMPOS DO WIZARD ===== */
            .wz-field {
                margin-bottom: 18px;
            }
            .wz-field:first-child {
                margin-top: 4px;
            }
            .wz-field-label {
                display: block;
                font-size: 13.5px;
                font-weight: 600;
                color: #1e293b;
                margin-bottom: 5px;
                letter-spacing: 0.01em;
            }
            .wz-field-label .wz-required {
                color: #ef4444;
                margin-left: 2px;
                font-weight: 700;
            }
            .wz-field-hint {
                display: block;
                font-size: 11.5px;
                color: #94a3b8;
                margin-bottom: 8px;
                line-height: 1.4;
                font-style: italic;
            }

            /* Grid de campos lado a lado */
            .wz-row {
                display: flex;
                gap: 16px;
                margin-bottom: 18px;
            }
            .wz-row > .wz-field {
                margin-bottom: 0;
                flex: 1;
                min-width: 0;
            }
            .wz-row > .wz-field.wz-col-8 { flex: 2; }
            .wz-row > .wz-field.wz-col-4 { flex: 1; }

            /* Fieldsets (radio/checkbox) */
            .wz-fieldset {
                border: 1.5px solid #e2e8f0;
                border-radius: 10px;
                padding: 16px 18px 12px;
                margin: 0;
                background: #fafbfc;
            }
            .wz-fieldset legend {
                font-size: 13.5px;
                font-weight: 600;
                color: #1e293b;
                padding: 0 8px;
                margin-bottom: 8px;
                width: auto;
                border: none;
            }
            .wz-fieldset legend .wz-required {
                color: #ef4444;
                margin-left: 2px;
                font-weight: 700;
            }
            .wz-fieldset .wz-option {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 5px 0;
                cursor: pointer;
            }
            .wz-fieldset .wz-option label {
                margin: 0;
                font-size: 13px;
                color: #334155;
                font-weight: 400;
                cursor: pointer;
            }
            .wz-fieldset .wz-option input[type="radio"],
            .wz-fieldset .wz-option input[type="checkbox"] {
                accent-color: #2D937E;
                width: 16px;
                height: 16px;
                cursor: pointer;
            }

            /* Secao-titulo dentro dos steps */
            .wz-section-title {
                font-size: 12px;
                font-weight: 700;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                margin: 20px 0 10px;
                padding-bottom: 6px;
                border-bottom: 1.5px solid #f1f5f9;
            }
            .wz-section-title:first-child {
                margin-top: 4px;
            }

            /* ===== ALERT INLINE ===== */
            .wizard-alert {
                background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
                border: 1.5px solid #fca5a5;
                border-radius: 10px;
                padding: 12px 18px;
                margin-bottom: 16px;
                color: #991b1b;
                font-weight: 500;
                font-size: 14px;
                display: flex;
                align-items: center;
                gap: 8px;
                animation: wzFadeIn 0.3s ease;
            }
            </style>

            <?php
            try {
                ?>

                <div class="wizard-container">
                    <!-- Progress bar -->
                    <div class="wizard-progress">
                        <div class="wizard-progress-bar" id="wizardProgressBar" style="width:11.11%"></div>
                    </div>

                    <!-- Stepper com icones -->
                    <div class="wizard-stepper">
                        <div class="step-item active" data-step="1" onclick="irParaStep(1);">
                            <div class="step-icon"><i class="fa fa-clipboard"></i></div>
                            <div class="step-label">Dados B&aacute;sicos</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" data-step="2" onclick="irParaStep(2);">
                            <div class="step-icon"><i class="fa fa-calendar"></i></div>
                            <div class="step-label">Per&iacute;odos</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" data-step="3" onclick="irParaStep(3);">
                            <div class="step-icon"><i class="fa fa-cogs"></i></div>
                            <div class="step-label">Formato</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" data-step="4" onclick="irParaStep(4);">
                            <div class="step-icon"><i class="fa fa-file-alt"></i></div>
                            <div class="step-label">Justificativa</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" data-step="5" onclick="irParaStep(5);">
                            <div class="step-icon"><i class="fa fa-users"></i></div>
                            <div class="step-label">Equipe</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" data-step="6" onclick="irParaStep(6);">
                            <div class="step-icon"><i class="fa fa-coins"></i></div>
                            <div class="step-label">Or&ccedil;amento</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" data-step="7" onclick="irParaStep(7);">
                            <div class="step-icon"><i class="fa fa-list-ol"></i></div>
                            <div class="step-label">Programa</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" data-step="8" onclick="irParaStep(8);">
                            <div class="step-icon"><i class="fa fa-calendar-check"></i></div>
                            <div class="step-label">Cronograma</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" data-step="9" onclick="irParaStep(9);">
                            <div class="step-icon"><i class="fa fa-check-circle"></i></div>
                            <div class="step-label">Revis&atilde;o</div>
                        </div>
                    </div>

                    <form id="formProjeto" name="formProjeto" action="<?php echo WB_URL_COMMAND ?>&dummy="
                          method="post">

                        <div id="wizardSteps">
                            <!-- Step 1: Dados Basicos (Campo01) -->
                            <div class="wz-step wz-active" id="step1">
                                <div class="wizard-card">
                                    <div class="wizard-card-header">
                                        <div class="header-icon"><i class="fa fa-clipboard"></i></div>
                                        <h4>Dados B&aacute;sicos do Projeto</h4>
                                        <span class="step-badge">Etapa 1 de 9</span>
                                    </div>
                                    <?php
                                    require_once(dirname(__FILE__) . "/GerenciarProjetoCampo01.php");
                                    ?>
                                    <div class="wizard-nav" style="justify-content:flex-end;">
                                        <button type="button" class="btn btn-wizard-next" onclick="proximoAba();">
                                            Pr&oacute;ximo <i class="fa fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Periodos e Detalhes (Campo02) -->
                            <div class="wz-step" id="step2">
                                <div class="wizard-card">
                                    <div class="wizard-card-header">
                                        <div class="header-icon"><i class="fa fa-calendar"></i></div>
                                        <h4>Per&iacute;odos e Detalhes</h4>
                                        <span class="step-badge">Etapa 2 de 9</span>
                                    </div>
                                    <?php
                                    require_once(dirname(__FILE__) . "/GerenciarProjetoCampo02.php");
                                    ?>
                                    <div class="wizard-nav">
                                        <button type="button" class="btn btn-wizard-prev" onclick="voltarAba();">
                                            <i class="fa fa-arrow-left"></i> Anterior
                                        </button>
                                        <button type="button" class="btn btn-wizard-next" onclick="proximoAba();">
                                            Pr&oacute;ximo <i class="fa fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Formato e Servicos (Campo03) -->
                            <div class="wz-step" id="step3">
                                <div class="wizard-card">
                                    <div class="wizard-card-header">
                                        <div class="header-icon"><i class="fa fa-cogs"></i></div>
                                        <h4>Formato e Servi&ccedil;os</h4>
                                        <span class="step-badge">Etapa 3 de 9</span>
                                    </div>
                                    <?php
                                    require_once(dirname(__FILE__) . "/GerenciarProjetoCampo03.php");
                                    ?>
                                    <div class="wizard-nav">
                                        <button type="button" class="btn btn-wizard-prev" onclick="voltarAba();">
                                            <i class="fa fa-arrow-left"></i> Anterior
                                        </button>
                                        <button type="button" class="btn btn-wizard-next" onclick="proximoAba();">
                                            Pr&oacute;ximo <i class="fa fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 4: Justificativa (Campo07) -->
                            <div class="wz-step" id="step4">
                                <div class="wizard-card">
                                    <div class="wizard-card-header">
                                        <div class="header-icon"><i class="fa fa-file-alt"></i></div>
                                        <h4>Justificativa</h4>
                                        <span class="step-badge">Etapa 4 de 9</span>
                                    </div>
                                    <?php
                                    require_once(dirname(__FILE__) . "/GerenciarProjetoCampo07.php");
                                    ?>
                                    <div class="wizard-nav">
                                        <button type="button" class="btn btn-wizard-prev" onclick="voltarAba();">
                                            <i class="fa fa-arrow-left"></i> Anterior
                                        </button>
                                        <button type="button" class="btn btn-wizard-next" onclick="proximoAba();">
                                            Pr&oacute;ximo <i class="fa fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 5: Equipe e Parcerias (Campo08) -->
                            <div class="wz-step" id="step5">
                                <div class="wizard-card">
                                    <div class="wizard-card-header">
                                        <div class="header-icon"><i class="fa fa-users"></i></div>
                                        <h4>Equipe e Parcerias</h4>
                                        <span class="step-badge">Etapa 5 de 9</span>
                                    </div>
                                    <?php
                                    require_once(dirname(__FILE__) . "/GerenciarProjetoCampo08.php");
                                    ?>
                                    <div class="wizard-nav">
                                        <button type="button" class="btn btn-wizard-prev" onclick="voltarAba();">
                                            <i class="fa fa-arrow-left"></i> Anterior
                                        </button>
                                        <button type="button" class="btn btn-wizard-next" onclick="proximoAba();">
                                            Pr&oacute;ximo <i class="fa fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 6: Previsao Orcamentaria (Campo05) -->
                            <div class="wz-step" id="step6">
                                <div class="wizard-card">
                                    <div class="wizard-card-header">
                                        <div class="header-icon"><i class="fa fa-coins"></i></div>
                                        <h4>Previs&atilde;o Or&ccedil;ament&aacute;ria</h4>
                                        <span class="step-badge">Etapa 6 de 9</span>
                                    </div>
                                    <?php
                                    require_once(dirname(__FILE__) . "/GerenciarProjetoCampo05.php");
                                    ?>
                                    <div class="wizard-nav">
                                        <button type="button" class="btn btn-wizard-prev" onclick="voltarAba();">
                                            <i class="fa fa-arrow-left"></i> Anterior
                                        </button>
                                        <button type="button" class="btn btn-wizard-next" onclick="proximoAba();">
                                            Pr&oacute;ximo <i class="fa fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 7: Programa (Campo06) -->
                            <div class="wz-step" id="step7">
                                <div class="wizard-card">
                                    <div class="wizard-card-header">
                                        <div class="header-icon"><i class="fa fa-list-ol"></i></div>
                                        <h4>Programa</h4>
                                        <span class="step-badge">Etapa 7 de 9</span>
                                    </div>
                                    <?php
                                    require_once(dirname(__FILE__) . "/GerenciarProjetoCampo06.php");
                                    ?>
                                    <div class="wizard-nav">
                                        <button type="button" class="btn btn-wizard-prev" onclick="voltarAba();">
                                            <i class="fa fa-arrow-left"></i> Anterior
                                        </button>
                                        <button type="button" class="btn btn-wizard-next" onclick="proximoAba();">
                                            Pr&oacute;ximo <i class="fa fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 8: Cronograma do Projeto (Campo09) -->
                            <div class="wz-step" id="step8">
                                <div class="wizard-card">
                                    <div class="wizard-card-header">
                                        <div class="header-icon"><i class="fa fa-calendar-check"></i></div>
                                        <h4>Cronograma do Projeto</h4>
                                        <span class="step-badge">Etapa 8 de 9</span>
                                    </div>
                                    <?php
                                    require_once(dirname(__FILE__) . "/GerenciarProjetoCampo09.php");
                                    ?>
                                    <div class="wizard-nav">
                                        <button type="button" class="btn btn-wizard-prev" onclick="voltarAba();">
                                            <i class="fa fa-arrow-left"></i> Anterior
                                        </button>
                                        <button type="button" class="btn btn-wizard-next" onclick="proximoAba();">
                                            Pr&oacute;ximo <i class="fa fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 9: Revisao -->
                            <div class="wz-step" id="step9">
                                <div class="wizard-card">
                                    <div class="wizard-card-header">
                                        <div class="header-icon"><i class="fa fa-check-circle"></i></div>
                                        <h4>Revis&atilde;o do Projeto</h4>
                                        <span class="step-badge">Etapa 9 de 9</span>
                                    </div>
                                    <div id="revisao-conteudo"></div>

                                    <div class="termos-box">
                                        <label>
                                            <input type="checkbox" name="aceita_termos" id="aceita_termos" value="Ciente dos Termos de Uso e Pol&iacute;ticas da OPCTEC">
                                            Estou ciente dos Termos de Uso e Pol&iacute;ticas da OPCTEC
                                        </label>
                                    </div>

                                    <div class="wizard-nav">
                                        <button type="button" class="btn btn-wizard-prev" onclick="voltarAba();">
                                            <i class="fa fa-arrow-left"></i> Anterior
                                        </button>
                                        <button type="button" class="btn btn-wizard-save" onclick="salvarProjeto();">
                                            <i class="fa fa-check"></i> Salvar Projeto
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-sm-12" style="padding-left:0;">
                            <div id="carregando" class="shown hidden">
                                <div class="imagem">
                                    <div class="frase">Carregando...</div>
                                    <img src="<?php echo WB_PATH ?>/resources/image/loading-9.gif" border="0">
                                </div>
                            </div>
                            <div id="mensagem"></div>
                            <div id="sucesso" class="hidden"></div>
                            <div id="erro" class="hidden"></div>
                            <input type="hidden" value="1" name="_submit_check">
                            <input type="hidden" value="GerenciarProjeto" name="c">
                            <input type="hidden" value="salvarProjeto" name="m">
                            <input type="hidden" name="update" id="update" value="0"/>
                            <input type="hidden" name="codProjeto" id="codProjeto" value=""/>
                        </div>
                    </form>
                </div>
            <?php } catch (Exception $exc) { ?>
                <div class="form-group col-sm-12" style="padding-left:0;"> <?php echo $exc->getMessage(); ?></div>
            <?php } ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    // === Wizard state ===
    var stepAtual = 1;
    var totalSteps = 9;
    var STORAGE_KEY = 'wizard_projeto_<?php echo isset($COD_EVENTO) ? $COD_EVENTO : "0" ?>';

    // === Config TinyMCE reutilizavel ===
    var tmceConfig = {
        plugins: 'code textcolor link table lists',
        width: '100%',
        height: 400,
        menubar: false,
        statusbar: true,
        toolbar: 'code | undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | forecolor backcolor fontsizeselect | link | table',
        force_br_newlines: true,
        force_p_newlines: false,
        forced_root_block: ''
    };

    function initTinyIfNeeded(id) {
        if (typeof tinymce !== 'undefined' && !tinymce.get(id)) {
            tinymce.init($.extend({selector: '#' + id}, tmceConfig));
        }
    }

    // === Wizard navigation (vanilla JS — sem dependencia de jQuery) ===

    function proximoAba() {
        if (stepAtual >= totalSteps) return;
        try { salvarLocalStorage(); } catch(e) {}
        stepAtual++;
        mostrarStep(stepAtual);
        if (stepAtual === 9) { try { gerarRevisao(); } catch(e) {} }
    }

    function voltarAba() {
        if (stepAtual <= 1) return;
        try { salvarLocalStorage(); } catch(e) {}
        stepAtual--;
        mostrarStep(stepAtual);
    }

    function irParaStep(step) {
        if (step < 1 || step > totalSteps || step === stepAtual) return;
        try { salvarLocalStorage(); } catch(e) {}
        stepAtual = step;
        mostrarStep(step);
        if (step === 9) { try { gerarRevisao(); } catch(e) {} }
    }

    function mostrarStep(step) {
        // Vanilla JS — funciona mesmo sem jQuery
        var steps = document.querySelectorAll('.wizard-container .wz-step');
        for (var i = 0; i < steps.length; i++) {
            steps[i].classList.remove('wz-active');
        }
        var target = document.getElementById('step' + step);
        if (target) target.classList.add('wz-active');
        atualizarStepper(step);
        window.scrollTo({top: 0, behavior: 'smooth'});

        // TinyMCE lazy init por step
        if (step === 4) { initTinyIfNeeded('justificativa'); }
        if (step === 5) {
            initTinyIfNeeded('partesEnvolvidas');
            initTinyIfNeeded('ministrantes');
            initTinyIfNeeded('pessoalTecnico');
        }
        if (step === 6) {
            initTinyIfNeeded('PrevisaoOrcamentaria');
            initTinyIfNeeded('investimento');
        }
        if (step === 7) { initTinyIfNeeded('programacao'); }
        if (step === 8) { initTinyIfNeeded('cronogramaEvento'); }
    }

    function atualizarStepper(step) {
        // Progress bar
        var bar = document.getElementById('wizardProgressBar');
        if (bar) bar.style.width = ((step / totalSteps) * 100) + '%';

        // Step items
        var items = document.querySelectorAll('.wizard-stepper .step-item');
        for (var i = 0; i < items.length; i++) {
            var s = parseInt(items[i].getAttribute('data-step'));
            items[i].classList.remove('active', 'completed');
            if (s < step) items[i].classList.add('completed');
            else if (s === step) items[i].classList.add('active');
        }

        // Step lines
        var lines = document.querySelectorAll('.wizard-stepper .step-line');
        for (var j = 0; j < lines.length; j++) {
            if (j < step - 1) lines[j].classList.add('completed');
            else lines[j].classList.remove('completed');
        }
    }

    // === Init com jQuery (quando disponivel) ===
    (function initWizard() {
        function onReady() {
            try {
                $('#itens tr:odd').css('background', '#F4F4F4');
                $('#itens tr:even').css('background', '#E9E9E9');
                $('#btnSalvar').click(function () { salvarProjeto(); });
                $('#btnSalvarComoNovo').click(function () { $('#update').val(0); $('#salvarProjeto').val('Adicionar'); salvarProjeto(); });
                $('#btnLimpar').click(function () { limparCampos(); });
                $('.editar').click(function () { editarProjeto($(this).parents("tr").attr("id")); });
                $('.excluir').click(function () { excluirProjeto($(this).parents("tr").attr("id")); });
                $('#cpf').on('paste', function (e) {
                    var pastedText = (e.originalEvent || e).clipboardData.getData('text');
                    $(this).val(pastedText.replace(/[\s.-]+/g, '').trim());
                    e.preventDefault();
                });
                $("#dataInicioEvento, #dataFimEvento, #dataInicioInscricoes, #dataFimInscricoes, #dataInicioEnvioTrabalhos, #dataFimEnvioTrabalhos").datetimepicker({});
                restaurarLocalStorage();
            } catch(e) { console.warn('Wizard init jQuery:', e); }
        }
        if (typeof $ !== 'undefined' && $.fn) {
            $(document).ready(onReady);
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof $ !== 'undefined') onReady();
            });
        }
    })();

    // === localStorage ===

    function salvarLocalStorage() {
        if (typeof $ === 'undefined') return;
        if (typeof tinymce !== 'undefined') tinymce.triggerSave();
        var dados = $('#formProjeto').serializeArray();
        localStorage.setItem(STORAGE_KEY, JSON.stringify(dados));
    }

    function restaurarLocalStorage() {
        var dados = localStorage.getItem(STORAGE_KEY);
        if (!dados) return;
        try { dados = JSON.parse(dados); } catch(e) { return; }
        if (typeof $ === 'undefined') return;
        dados.forEach(function(item) {
            var el = $('[name="' + item.name + '"]');
            if (el.is(':checkbox') || el.is(':radio')) {
                el.filter('[value="' + item.value + '"]').prop('checked', true);
            } else {
                el.val(item.value);
            }
        });
    }

    function limparLocalStorage() {
        localStorage.removeItem(STORAGE_KEY);
    }

    // === Review (step 9) ===

    function getTinyText(id) {
        var val = (typeof tinymce !== 'undefined' && tinymce.get(id) ? tinymce.get(id).getContent({format:'text'}) : $('#' + id).val()) || '-';
        if (val.length > 200) val = val.substring(0, 200) + '...';
        return $('<span>').text(val).html();
    }

    function gerarRevisao() {
        if (typeof tinymce !== 'undefined') tinymce.triggerSave();
        var html = '';

        // Secao 1 - Dados Basicos → step 1
        html += '<div class="revisao-secao">';
        html += '<h5>Dados B\u00e1sicos <a href="#" class="btn-edit" onclick="irParaStep(1);return false;"><i class="fa fa-pencil"></i> Editar</a></h5>';
        html += montarLinhaRevisao('T\u00edtulo', '#titulo');
        html += montarLinhaRevisao('Coordenador', '#nomeOrganizador');
        html += montarLinhaRevisao('E-mail', '#emailContato');
        html += montarLinhaRevisao('Telefone', '#foneOrganizador');
        html += montarLinhaRevisao('Descri\u00e7\u00e3o', '#descricaoProjeto');
        html += montarLinhaRevisao('P\u00fablico-Alvo', '#publicoAlvo');
        html += montarLinhaRevisao('Objetivo', '#objetivo');
        html += '</div>';

        // Secao 2 - Periodos e Detalhes → step 2
        html += '<div class="revisao-secao">';
        html += '<h5>Per\u00edodos e Detalhes <a href="#" class="btn-edit" onclick="irParaStep(2);return false;"><i class="fa fa-pencil"></i> Editar</a></h5>';
        html += montarLinhaRevisao('In\u00edcio do Evento', '#dataInicioEvento');
        html += montarLinhaRevisao('Fim do Evento', '#dataFimEvento');
        html += montarLinhaRevisao('In\u00edcio Inscri\u00e7\u00f5es', '#dataInicioInscricoes');
        html += montarLinhaRevisao('Fim Inscri\u00e7\u00f5es', '#dataFimInscricoes');
        html += montarLinhaRevisao('In\u00edcio Envio Trabalhos', '#dataInicioEnvioTrabalhos');
        html += montarLinhaRevisao('Fim Envio Trabalhos', '#dataFimEnvioTrabalhos');
        html += montarLinhaRevisao('Categoria', '#Categoria');
        html += montarLinhaRevisao('P\u00fablico Esperado', '#expectativa');
        html += montarLinhaRevisao('Sigla', '#sigla');
        html += montarLinhaRevisao('Carga Hor\u00e1ria', '#cargaHoraria');
        html += '</div>';

        // Secao 3 - Formato e Servicos → step 3
        html += '<div class="revisao-secao">';
        html += '<h5>Formato e Servi\u00e7os <a href="#" class="btn-edit" onclick="irParaStep(3);return false;"><i class="fa fa-pencil"></i> Editar</a></h5>';
        var formatoVal = $('input[name="tipoProjeto"]:checked').val() || '-';
        html += '<div class="revisao-campo"><span class="label-rev">Formato</span><span class="valor-rev">' + $('<span>').text(formatoVal).html() + '</span></div>';
        var servicos = [];
        if ($('#trabEnvioTrabalhos').is(':checked')) servicos.push('Envio de Trabalhos');
        if ($('#trabEscolherAtividades').is(':checked')) servicos.push('Escolher Atividade');
        if ($('#trabEmissaoCertificados').is(':checked')) servicos.push('Emiss\u00e3o de Certificados');
        if ($('#trabReceberPagamentos').is(':checked')) servicos.push('Receber Pagamentos');
        html += '<div class="revisao-campo"><span class="label-rev">Servi\u00e7os</span><span class="valor-rev">' + (servicos.length ? $('<span>').text(servicos.join(', ')).html() : '-') + '</span></div>';
        html += montarLinhaRevisao('Local de Realiza\u00e7\u00e3o', '#descLocalRealizacao');
        html += montarLinhaRevisao('Valores de Inscri\u00e7\u00e3o', '#descricaoValoresInscricao');
        html += montarLinhaRevisao('Trabalhos/Artigos', '#descricaoTrabalhosArtigos');
        html += '</div>';

        // Secao 4 - Justificativa → step 4
        html += '<div class="revisao-secao">';
        html += '<h5>Justificativa <a href="#" class="btn-edit" onclick="irParaStep(4);return false;"><i class="fa fa-pencil"></i> Editar</a></h5>';
        html += '<div class="revisao-campo"><span class="label-rev">Justificativa</span><span class="valor-rev">' + getTinyText('justificativa') + '</span></div>';
        html += '</div>';

        // Secao 5 - Equipe e Parcerias → step 5
        html += '<div class="revisao-secao">';
        html += '<h5>Equipe e Parcerias <a href="#" class="btn-edit" onclick="irParaStep(5);return false;"><i class="fa fa-pencil"></i> Editar</a></h5>';
        html += '<div class="revisao-campo"><span class="label-rev">Partes Envolvidas</span><span class="valor-rev">' + getTinyText('partesEnvolvidas') + '</span></div>';
        html += '<div class="revisao-campo"><span class="label-rev">Ministrantes</span><span class="valor-rev">' + getTinyText('ministrantes') + '</span></div>';
        html += '<div class="revisao-campo"><span class="label-rev">Pessoal T\u00e9cnico</span><span class="valor-rev">' + getTinyText('pessoalTecnico') + '</span></div>';
        html += '</div>';

        // Secao 6 - Previsao Orcamentaria → step 6
        html += '<div class="revisao-secao">';
        html += '<h5>Previs\u00e3o Or\u00e7ament\u00e1ria <a href="#" class="btn-edit" onclick="irParaStep(6);return false;"><i class="fa fa-pencil"></i> Editar</a></h5>';
        html += '<div class="revisao-campo"><span class="label-rev">Previs\u00e3o Or\u00e7ament\u00e1ria</span><span class="valor-rev">' + getTinyText('PrevisaoOrcamentaria') + '</span></div>';
        html += '<div class="revisao-campo"><span class="label-rev">Modelo de cobran\u00e7a</span><span class="valor-rev">' + getTinyText('investimento') + '</span></div>';
        html += '</div>';

        // Secao 7 - Programa → step 7
        html += '<div class="revisao-secao">';
        html += '<h5>Programa <a href="#" class="btn-edit" onclick="irParaStep(7);return false;"><i class="fa fa-pencil"></i> Editar</a></h5>';
        html += '<div class="revisao-campo"><span class="label-rev">Programa</span><span class="valor-rev">' + getTinyText('programacao') + '</span></div>';
        html += '</div>';

        // Secao 8 - Cronograma do Projeto → step 8
        html += '<div class="revisao-secao">';
        html += '<h5>Cronograma do Projeto <a href="#" class="btn-edit" onclick="irParaStep(8);return false;"><i class="fa fa-pencil"></i> Editar</a></h5>';
        html += '<div class="revisao-campo"><span class="label-rev">Cronograma</span><span class="valor-rev">' + getTinyText('cronogramaEvento') + '</span></div>';
        html += '</div>';

        $('#revisao-conteudo').html(html);
    }

    function montarLinhaRevisao(label, seletor) {
        var val = $(seletor).val() || '-';
        if (val.length > 200) val = val.substring(0, 200) + '...';
        return '<div class="revisao-campo"><span class="label-rev">' + label + '</span><span class="valor-rev">' + $('<span>').text(val).html() + '</span></div>';
    }

    // === Save ===

    function salvarProjeto() {
        if (typeof $ !== 'undefined' && !$('#aceita_termos').is(':checked')) {
            mostrarAlertaWizard(9, 'Voc\u00ea precisa aceitar os termos para salvar o projeto.');
            return;
        }
        if (typeof tinymce !== 'undefined') tinymce.triggerSave();
        salvarProjetoPorAbas();
        limparLocalStorage();
    }

    function mostrarAlertaWizard(step, texto) {
        var old = document.getElementById('wizardAlert');
        if (old) old.remove();
        var div = document.createElement('div');
        div.id = 'wizardAlert';
        div.className = 'wizard-alert';
        div.innerHTML = '<i class="fa fa-exclamation-triangle"></i> ' + texto;
        var header = document.querySelector('#step' + step + ' .wizard-card-header');
        if (header && header.parentNode) {
            header.parentNode.insertBefore(div, header.nextSibling);
            setTimeout(function() { div.style.opacity = '0'; setTimeout(function() { if (div.parentNode) div.remove(); }, 300); }, 4000);
        }
    }

    // === Existing functions ===

    function carregarTabelaProjetos() {
        $.post('<?php echo WB_URL_COMMAND ?>&t=' + Math.random()
            , {'c': 'GerenciarProjeto', 'm': 'carregarTabelaProjetos', '_submit_check': '1'}
            , function (html) {
                $("table tbody.inputs tr").remove();
                $("table").trigger("update");
                $("table tbody.inputs").append(html);
                $("table").trigger("update");
                $('.editar').click(function () {
                    var id = $(this).parents("tr").attr("id");
                    editarProjeto(id);
                });
                $('.excluir').click(function () {
                    var id = $(this).parents("tr").attr("id");
                    excluirProjeto(id);
                });
            }
        );
    }

    function editarProjeto(codProjeto) {
        if (codProjeto) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codProjeto': codProjeto,
                    'c': 'GerenciarProjeto',
                    'm': 'editarProjeto',
                    '_submit_check': '1'
                }),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function () {
                    startLoading();
                },
                success: function (data) {
                    $('#codEvento').val(data.codEvento);
                    $('#codProjeto').val(data.codProjeto);
                    $('#emailCoordenador').val(data.emailCoordenador);
                    $('#nomeCoordenador').val(data.nomeCoordenador);
                    $('#tituloEvento').val(data.tituloEvento);
                    $('#sigla').val(data.sigla);
                    $('#dataInicioEvento').val(data.dataInicioEvento);
                    $('#dataFimEvento').val(data.dataFimEvento);
                    $('#dataInicioInscricoes').val(data.dataInicioInscricoes);
                    $('#dataFimInscricoes').val(data.dataFimInscricoes);
                    $('#tipoProjeto').val(data.tipoProjeto);
                    $('#trabEnvioTrabalhos').val(data.trabEnvioTrabalhos);
                    $('#trabEscolherAtividades').val(data.trabEscolherAtividades);
                    $('#trabEmissaoCertificados').val(data.trabEmissaoCertificados);
                    $('#trabReceberPagamentos').val(data.trabReceberPagamentos);
                    $('#descricaoProjeto').val(data.descricaoProjeto);
                    $('#descricaoValoresInscricao').val(data.descricaoValoresInscricao);
                    $('#listaAtividades').val(data.listaAtividades);
                    $('#descricaoAtividades').val(data.descricaoAtividades);
                    $('#infoTrabEnvioArtigo').val(data.infoTrabEnvioArtigo);
                    $('#descricaoTrabalhosArtigos').val(data.descricaoTrabalhosArtigos);
                    $('#publicoAlvo').val(data.publicoAlvo);
                    $('#quantidadeVagas').val(data.quantidadeVagas);
                    $('#cargaHoraria').val(data.cargaHoraria);
                    $('#investimento').val(data.investimento);
                    $('#programacao').val(data.programacao);
                    $('#outrasInformacoes').val(data.outrasInformacoes);
                    $('#linkInscricoes').val(data.linkInscricoes);
                    $('#pais').val(data.pais);
                    $('#expectativa').val(data.expectativa);
                    $('#tipo').val(data.tipo);
                    $('#categoria').val(data.categoria);
                    $('#telefone').val(data.telefone);
                    $('#linkInstagram').val(data.linkInstagram);
                    $('#linkFacebook').val(data.linkFacebook);
                    $('#aceitaTermos').val(data.aceitaTermos);

                    $('#update').val(1);
                    $('#salvarProjeto').val('Salvar Altera\u00e7\u00f5es');
                    // Voltar para step 1 na edicao
                    stepAtual = 1;
                    mostrarStep(1);
                    $('html,body').scrollTop(0);
                }
                , complete: function () {
                    stopLoading();
                }
            });
        } else {
            alert('Projeto n\u00e3o foi alterado!');
        }
    }

    function excluirProjeto(codProjeto) {
        if (confirm('Deseja realmente excluir Novo Projeto?')) {
            $.ajax({
                type: "POST",
                url: '<?php echo WB_URL_COMMAND ?>&t=' + Math.random(),
                data: ({
                    'codProjeto': codProjeto,
                    'c': 'GerenciarProjeto',
                    'm': 'excluirProjeto',
                    '_submit_check': '1'
                }),
                dataType: "json",
                async: false,
                cache: false,
                beforeSend: function () {
                    startLoading();
                },
                success: function (data) {
                    processJson(data);
                    if (data.statusMessage) {
                        carregarTabelaProjetos();
                    }
                    limparCampos();
                }
                , complete: function () {
                    stopLoading();
                }
            });
        }
    }

    function limparCampos() {
        $('#codEvento').val('');
        $('#codProjeto').val('');
        $('#emailCoordenador').val('');
        $('#nomeCoordenador').val('');
        $('#tituloEvento').val('');
        $('#sigla').val('');
        $('#dataInicioEvento').val('');
        $('#dataFimEvento').val('');
        $('#dataInicioInscricoes').val('');
        $('#dataFimInscricoes').val('');
        $('#dataInicioEnvioTrabalhos').val('');
        $('#dataFimEnvioTrabalhos').val('');
        $('#Categoria').val('');
        $('#expectativa').val('');
        $('#cargaHoraria').val('');
        $('#quantidadeVagasMinima').val('');
        $('#quantidadeVagas').val('');
        $('#descricao_categoria').val('');
        $('input[name="tipoProjeto"]').prop('checked', false);
        $('input[name="trabEnvioTrabalhos"]').prop('checked', false);
        $('input[name="trabEscolherAtividades"]').prop('checked', false);
        $('input[name="trabEmissaoCertificados"]').prop('checked', false);
        $('input[name="trabReceberPagamentos"]').prop('checked', false);
        $('#descricaoProjeto').val('');
        $('#descricaoValoresInscricao').val('');
        $('#listaAtividades').val('');
        $('#descricaoAtividades').val('');
        $('#infoTrabEnvioArtigo').val('');
        $('#descricaoTrabalhosArtigos').val('');
        $('#publicoAlvo').val('');
        $('#quantidadeVagas').val('');
        $('#cargaHoraria').val('');
        // Limpar todos os campos TinyMCE
        var tinyFields = ['justificativa','partesEnvolvidas','ministrantes','pessoalTecnico','PrevisaoOrcamentaria','investimento','programacao','cronogramaEvento'];
        tinyFields.forEach(function(id) {
            $('#' + id).val('');
            if (typeof tinymce !== 'undefined' && tinymce.get(id)) {
                tinymce.get(id).setContent('');
            }
        });
        $('#outrasInformacoes').val('');
        $('#linkInscricoes').val('');
        $('#pais').val('');
        $('#expectativa').val('');
        $('#tipo').val('');
        $('#categoria').val('');
        $('#telefone').val('');
        $('#linkInstagram').val('');
        $('#linkFacebook').val('');
        $('#aceita_termos').prop('checked', false);
        $('#update').val(0);
        $('#salvarProjeto').val('Adicionar');
        limparLocalStorage();
        stepAtual = 1;
        mostrarStep(1);
    }
</script>
<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"> -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link type="text/css" href="resources/js/jquery-ui-1.7.3.custom/css/smoothness/jquery-ui-1.7.3.custom.css" rel="stylesheet"/>
<link rel="stylesheet" href="/resources/demos/style.css">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script type="text/javascript" src="resources/js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/development-bundle/ui/ui.dialog.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.price_format.1.7.min.js"></script>
<script type="text/javascript" src="resources/js/jquery/jquery.form.js"></script>
<script type="text/javascript" src="resources/js/tinymce_4.3.0/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="resources/js/tinymce-charcount.js"></script>
<script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js"></script>
<script type="text/javascript" src="resources/js/jquery-ui-1.7.3.custom/development-bundle/ui/i18n/ui.datepicker-pt-BR.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        tinymce.init({
            selector: "textarea.editor_padrao",
            plugins: "code textcolor link table",
            width: 500,
            height: 500,
            max_height: 1200,
            resize: "both",
            menubar: false,
            statusbar: true,
            block_formats: 'Header 1=h1;Header 2=h2;Header 3=h3',
            force_br_newlines: true,
            force_p_newlines: false,
            forced_root_block: '',
            apply_source_formatting: true,
            convert_newlines_to_brs: true,
            remove_linebreaks: false,
            toolbar: "code | undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | forecolor backcolor fontsizeselect | link | table ",
            setup: function (editor) { tinyMceCharCount.attach(editor, 5000); },
            init_instance_callback: function (editor) {
                $('.mce-tinymce').show('fast');
                $(editor.getContainer()).find(".mce-path").css("display", "none");
            },
            textcolor_map: [
                "FF0000", "Red",
                "008B45", "Green",
                "0000FF", "Blue"
            ],
            fontsize_formats: "10pt 12pt 14pt 18pt 24pt 36pt",
        });
    });

    function atualizarDDD() {
        var paisSelecionado = document.getElementById("pais").value;
        var ddd;

        switch (paisSelecionado) {
            case "\u00c1frica do Sul": ddd = "+27"; break;
            case "Angola": ddd = "+244"; break;
            case "Argentina": ddd = "+54"; break;
            case "Bol\u00edvia": ddd = "+591"; break;
            case "Brasil": ddd = "+55"; break;
            case "Chile": ddd = "+56"; break;
            case "Col\u00f4mbia": ddd = "+57"; break;
            case "Equador": ddd = "+593"; break;
            case "Estados Unidos": ddd = "+1"; break;
            case "M\u00e9xico": ddd = "+52"; break;
            case "Mo\u00e7ambique": ddd = "+258"; break;
            case "Paraguai": ddd = "+595"; break;
            case "Peru": ddd = "+51"; break;
            case "Portugal": ddd = "+351"; break;
            case "Uruguai": ddd = "+598"; break;
        }
        document.getElementById("telefone").value = ddd;
    }

    function habilitarCampos() {
        habilitarTelefone();
    }

    function habilitarTelefone() {
        var paisSelect = document.getElementById("pais");
        var telefoneInput = document.getElementById("telefone");
        telefoneInput.disabled = !paisSelect.value;
        atualizarDDD();
    }

    function salvarProjetoPorAbas() {
        if (typeof tinymce !== 'undefined') tinymce.triggerSave();
        if (window.tinyMceCharCount && !tinyMceCharCount.podeSalvar()) return;
        var update = $('#update').val();
        $('#formProjeto').ajaxSubmit({
            target: '#mensagem',
            dataType: 'json',
            async: false,
            cache: false,
            beforeSend: function () {
                startLoading();
            },
            timeout: 4000,
            error: function (ajax) {
                showResponse(ajax);
            },
            success: function (data) {
                processJson(data);
                if (data.statusMessage) {
                    $('#codProjeto').val(data.codProjeto);
                    $('#update').val(1);
                }
            },
        });
    }

</script>