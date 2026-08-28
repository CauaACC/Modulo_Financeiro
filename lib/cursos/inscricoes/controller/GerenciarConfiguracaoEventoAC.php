<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GerenciarConfiguracaoEventoAC
 *
 * @author home
 */
class GerenciarConfiguracaoEventoAC {
    public function carregarConfiguracaoByObjEvento($obj, $objEv){
        
        $obj->noOuNa          = "no";
        $obj->doOuDa          = "do";
        $obj->relalizadoOurelalizado = "realizada";
        // $obj->nomeEvento      = $objEv->getAbrevEvento();
        $obj->nomeEvento      = $objEv->getTitulo();
        $obj->nomeEventoCompleto = $objEv->getTitulo();
        $obj->cidadeEvento    = $objEv->getCidadeUf();
        $obj->periodoEvento   = $objEv->getPeriodo();
        $obj->nomeOrganizador = $objEv->getNomeOrganizador();
        $obj->cidadeOrganizador = $objEv->getCidadeOrganizador();
        $obj->cnpjOrganizador   = $objEv->getCnpjOrganizador();
        $obj->enderecoOrganizador   = $objEv->getEnderecoOrganizador();
        $obj->telefoneOrganizador   = $objEv->getFoneOrganizador();
        $obj->assinaturaOrganizador = "Administração: ".$obj->nomeEvento;
        $obj->dataEmissao = $obj->cidadeEvento.", ".Date('d')." de ".getMonthName(Date('m'))." de ".Date('Y').".";
        
        #@todo liberar certificado sem avaliacao
        $obj->urlBaseFormularioAvaliacao = $objEv->getUrlAvaliacaoEvento();
        $obj->liberarFormularioAvaliacao = $objEv->getUrlAvaliacaoEvento()? 1 : 0;

        $obj->DESC_EVENTO  = $objEv->getDescEvento();
        $obj->NOME_EVENTO  = $objEv->getTitulo();
        $obj->URL_OFICIAL  = $objEv->getUrlOficial();
        $obj->ABREV_EVENTO = $objEv->getAbrevEvento();

        $obj->COD_EVENTO   = $objEv->getCodEvento();
        $obj->ANO_EVENTO   = $objEv->getAnoEvento();
        $obj->anoEvento = $objEv->getAnoEvento();
        $obj->MAX_OFICINAS = '0';

        $obj->ABREV_EVENTO_EMAIL = $objEv->getAbrevEvento();
        $obj->EMAIL_CONTATO   = $objEv->getEmailContato();
        $obj->EMAIL_REPLAY_TO = $objEv->getEmailReplayTo();
        $obj->EMAIL_BCC       = $objEv->getEmailBcc();
        $obj->EMAIL_CONTATO_ATT = $objEv->getEmailContatoAtt();

        $obj->LINK_CONTATO      = $objEv->getLinkContato();

        $obj->COORDENADOR_GERAL = '';
        $obj->PRESIDENTE        = $objEv->getPresidente();
        $obj->PRESIDENTE_TITULO = $objEv->getPresidenteTitulo();
        return $obj;
    }
    public function carregarConfiguracaoByObjInscrito($obj, $objInscrito){
        
        $objInscrito->carregarObjEvento();
        $objEv = $objInscrito->getObjEvento();
        $objConf = $objEv->getEventoConfiguracao();
        
        $obj->noOuNa          = "no";
        $obj->doOuDa          = "do";
        $obj->relalizadoOurelalizado = "realizada";
        $obj->nomeEvento      = $objEv->getAbrevEvento();
        $obj->nomeEventoCompleto = $objEv->getTitulo();
        $obj->cidadeEvento    = $objEv->getCidadeUf();
        $obj->periodoEvento   = $objEv->getPeriodo();
        $obj->nomeOrganizador = $objEv->getNomeOrganizador();
        $obj->cidadeOrganizador = $objEv->getCidadeOrganizador();
        $obj->cnpjOrganizador   = $objEv->getCnpjOrganizador();
        $obj->enderecoOrganizador   = $objEv->getEnderecoOrganizador();
        $obj->telefoneOrganizador   = $objEv->getFoneOrganizador();
        $obj->assinaturaOrganizador = "Administração: ".$obj->nomeEvento;
        $obj->dataEmissao = $obj->cidadeEvento.", ".Date('d')." de ".getMonthName(Date('m'))." de ".Date('Y').".";
//        $obj->urlBase = UrlInscView($objInscrito);
        #@todo liberar certificado sem avaliacao
        $obj->urlBaseFormularioAvaliacao = $objEv->getUrlAvaliacaoEvento();
        $obj->liberarFormularioAvaliacao = $objEv->getUrlAvaliacaoEvento()? 1 : 0;

        $obj->DESC_EVENTO  = $objEv->getDescEvento();
        $obj->NOME_EVENTO  = $objEv->getTitulo();
        $obj->URL_OFICIAL  = $objEv->getUrlOficial();
        $obj->ABREV_EVENTO = $objEv->getAbrevEvento();

        $obj->COD_EVENTO   = $obj->codEvento;
        $obj->ANO_EVENTO   = $obj->anoEvento;
        $obj->MAX_OFICINAS = '0';

        $obj->ABREV_EVENTO_EMAIL = $objEv->getAbrevEvento();
        $obj->EMAIL_CONTATO   = $objEv->getEmailContato();
        $obj->EMAIL_REPLAY_TO = $objEv->getEmailReplayTo();
        $obj->EMAIL_BCC       = $objEv->getEmailBcc();
        $obj->EMAIL_CONTATO_ATT = $objEv->getEmailContatoAtt();

        $obj->LINK_CONTATO      = $objEv->getLinkContato();

        $obj->COORDENADOR_GERAL = '';
        $obj->PRESIDENTE        = $objEv->getPresidente();
        $obj->PRESIDENTE_TITULO = $objEv->getPresidenteTitulo();
    }
    public function carregarConfiguracao($obj){
        $obj->noOuNa          = "no";
        $obj->doOuDa          = "do";
        $obj->relalizadoOurelalizado = "realizada";
        $obj->nomeEvento      = "HUTEC";
        $obj->nomeEventoCompleto = "HUTEC";
        $obj->cidadeEvento    = "Londrina - PR";
        $obj->periodoEvento   = "";
        $obj->nomeOrganizador = " HUTEC";
        $obj->cidadeOrganizador = "Londrina - PR";
        $obj->cnpjOrganizador   = "";
        $obj->enderecoOrganizador    = "";
        $obj->telefoneOrganizador    = "";
        $obj->assinaturaOrganizador  = "Administração ".$obj->nomeEvento;
        $obj->dataEmissao = "Londrina, ".Date('d')." de ".Date('m')." de ".Date('Y').".";
        $obj->urlBase     = "https://www.opctec.net.br/hutec/".WB_PROJETO;
        #@todo liberar certificado sem avaliacao
        $obj->liberarFormularioAvaliacao = false; #true/false
        $obj->urlBaseFormularioAvaliacao = "https://docs.google.com/forms/d/e/1FAIpQLSd_UntmTuq_pCi6_nU_89rv4vTZnmvd2LCP98S4913vTrp5sg/viewform";

        $obj->DESC_EVENTO  = 'Cursos Hutec';
        $obj->NOME_EVENTO  = 'Cursos Hutec';
        $obj->URL_OFICIAL  = 'https://hutec.org.br';
        $obj->ABREV_EVENTO = 'HUTEC';

        $obj->COD_EVENTO   =  $obj->codEvento;
        $obj->ANO_EVENTO   =  $obj->anoEvento;
        $obj->MAX_OFICINAS = '0';

        $obj->ABREV_EVENTO_EMAIL = 'HUTEC';
        $obj->EMAIL_CONTATO   = 'contato.opctec@gmail.com';
        $obj->EMAIL_REPLAY_TO = '';
        $obj->EMAIL_BCC       = '';
        $obj->EMAIL_CONTATO_ATT = 'Administração HUTEC';

        $obj->LINK_CONTATO      = 'https://www.opctec.net.br/hutec/'.WB_PROJETO.'/#contact';

        $obj->COORDENADOR_GERAL =   '';
        $obj->PRESIDENTE        =   $obj->COORDENADOR_GERAL;
        $obj->PRESIDENTE_TITULO = 'Administração Geral da Hutec';

       if($obj->codEvento==2){
           $obj->nomeEvento = "4º Congresso Paranaense de Saúde Pública/Coletiva";
           $obj->cidadeEvento = "Curitiba-PR";
           $obj->periodoEvento = "18 a 20 de julho de 2018";
           $obj->nomeOrganizador = " INSTITUTO DE ESTUDOS EM SAUDE COLETIVA";
           $obj->cidadeOrganizador = "Londrina-PR";
           $obj->cnpjOrganizador  = "CNPJ: 80.511.173/0001-03 ";
           $obj->enderecoOrganizador  = "Av. Rio de Janeiro, 1500 Sl.11 - cep:86010-150";
           $obj->telefoneOrganizador  = "(43) 3024 6221";
           $obj->assinaturaOrganizador  = "Prof.Dr. João José Batista de Campos<br>Presidente do ".$obj->nomeEvento;
           $obj->dataEmissao = "Londrina, 18 de julho de 2018.";
           $obj->urlBase = "http://www.opctec.net.br/inesco/4congresso";
           $obj->urlBaseFormularioAvaliacao = "https://docs.google.com/forms/d/e/1FAIpQLSfsRwbGNClsOZbuHQ-t29zEzXmhQKyNNXz42line_kPPlIiew/viewform";
       }
       if($obj->codEvento==3){
           $obj->noOuNa          = "na";
           $obj->relalizadoOurelalizado = "realizada";
           $obj->nomeEvento      = "5º Mostra Paranaense de Pesquisas em Saúde";
           $obj->cidadeEvento    = "Londrina-PR";
           $obj->periodoEvento   = "11 e 12 de julho de 2019";
           $obj->nomeOrganizador = " INSTITUTO DE ESTUDOS EM SAUDE COLETIVA";
           $obj->cidadeOrganizador = "Londrina-PR";
           $obj->cnpjOrganizador   = "CNPJ: 80.511.173/0001-03 ";
           $obj->enderecoOrganizador    = "Av. Rio de Janeiro, 1500 Sl.11 - cep:86010-150";
           $obj->telefoneOrganizador    = "(43) 3024 6221";
           $obj->assinaturaOrganizador  = "Prof.Dr. João José Batista de Campos<br>Presidente do ".$obj->nomeEvento;
           $obj->dataEmissao = "Londrina, 12 de julho de 2019.";
           $obj->urlBase     = "http://www.opctec.net.br/inesco/5mostraparanaensesaude";
           $obj->urlBaseFormularioAvaliacao = "https://docs.google.com/forms/d/e/1FAIpQLSfZgx7dB3Eui5P_A_ArzZ6DezeZMbVxr5vFxm6LOJx8dYh9ew/viewform";
           
            $obj->DESC_EVENTO  = '5º Mostra Paranaense de Pesquisas em Saúde';
            $obj->NOME_EVENTO  = 'Mostra Paranaense de Pesquisas em Saúde';
            $obj->URL_OFICIAL  = 'http://mostraparanaensesaude.org.br';
            $obj->ABREV_EVENTO = '5º MPS';

            $obj->COD_EVENTO   =  '3';
            $obj->ANO_EVENTO   =  '2019';
            $obj->MAX_OFICINAS = '0';
            
            $obj->ABREV_EVENTO_EMAIL = '5.MPS';
            $obj->EMAIL_CONTATO   = 'inesco@inesco.org.br';
            $obj->EMAIL_REPLAY_TO = '';
            $obj->EMAIL_BCC       = '';
            $obj->EMAIL_CONTATO_ATT = 'Comissão Organizadora do 5ºMPS - Inesco';
            
            $obj->LINK_CONTATO      = 'http://mostraparanaensesaude.org.br/#contact';
            
            $obj->PRESIDENTE        =   'Prof.Dr. João José Batista de Campos';
            $obj->PRESIDENTE_TITULO = 'Presidente da 5º Mostra Paranaense de Pesquisas em Saúde';
//            return $obj;
       }  
       if($obj->codEvento==4){
           $obj->noOuNa          = "na";
           $obj->relalizadoOurelalizado = "realizada";
           $obj->nomeEvento      = "5º Congresso Paranaense de Saúde Pública/Coletiva";
           $obj->cidadeEvento    = "Londrina-PR";
           $obj->periodoEvento   = "15 e 16 de julho de 2020";
           $obj->nomeOrganizador = " INSTITUTO DE ESTUDOS EM SAUDE COLETIVA";
           $obj->cidadeOrganizador = "Londrina-PR";
           $obj->cnpjOrganizador   = "CNPJ: 80.511.173/0001-03 ";
           $obj->enderecoOrganizador    = "Av. Rio de Janeiro, 1500 Sl.11 - cep:86010-150";
           $obj->telefoneOrganizador    = "(43) 3024 6221";
           $obj->assinaturaOrganizador  = "Prof.Dr. João José Batista de Campos<br>Presidente do ".$obj->nomeEvento;
           $obj->dataEmissao = "Londrina, 17 de julho de 2020.";
           $obj->urlBase     = "http://www.opctec.net.br/inesco/5congresso";
           $obj->urlBaseFormularioAvaliacao = "https://docs.google.com/forms/d/e/1FAIpQLSdj6Fl-_5Of7hQowLLtWSwMC0rHqnHMy3zUw5K_9LvQMswkoA/viewform";
           
            $obj->DESC_EVENTO  = '5º Congresso Paranaense de Saúde Pública/Coletiva';
            $obj->NOME_EVENTO  = 'Congresso Paranaense de Saúde Pública/Coletiva';
            $obj->URL_OFICIAL  = 'http://congressosaudepublica.org.br';
            $obj->ABREV_EVENTO = '5º CPSPC';

            $obj->COD_EVENTO   =  '4';
            $obj->ANO_EVENTO   =  '2020';
            $obj->MAX_OFICINAS = '0';
            
            $obj->ABREV_EVENTO_EMAIL = '5.CPSPC';
            $obj->EMAIL_CONTATO   = 'inesco@inesco.org.br';
            $obj->EMAIL_REPLAY_TO = '';
            $obj->EMAIL_BCC       = '';
            $obj->EMAIL_CONTATO_ATT = 'Comissão Organizadora do 5ºCPSPC - Inesco';
            
            $obj->LINK_CONTATO      = 'http://congressosaudepublica.org.br/#contact';
            
            $obj->PRESIDENTE        =   'Prof.Dr. João José Batista de Campos';
            $obj->PRESIDENTE_TITULO = 'Presidente do 5º Congresso Paranaense de Saúde Pública/Coletiva';
//            return $obj;
       }
       if($obj->codEvento==5){
           $obj->noOuNa          = "para a";
           $obj->relalizadoOurelalizado = "realizada em plataforma Online";
           $obj->nomeEvento      = "Plenária Estadual do CRESS-PR";
           $obj->cidadeEvento    = "";
           $obj->periodoEvento   = "09 e 10 de novembro de 2020";
           $obj->nomeOrganizador = " Conselho Regional de Serviço Social - CRESS/PR 11ª Região";
           $obj->cidadeOrganizador = "Londrina-PR";
           $obj->cnpjOrganizador   = "";
           $obj->enderecoOrganizador    = "R. Monsenhor Celso, 154 - 13º andar - CEP 80.010-150 - Centro - Curitiba - PR";
           $obj->telefoneOrganizador    = "(41) 99104-2875";
           $obj->assinaturaOrganizador  = " Andrea Luiza Curralinho Braga<br>Presidenta do CRESS-PR";
           $obj->dataEmissao = "Curitiba, 11 de novembro de 2020.";
           $obj->urlBase     = "http://www.opctec.net.br/cresspr/plenaria";
           $obj->urlBaseFormularioAvaliacao = "";
           
            $obj->DESC_EVENTO  = 'Plenária Participativa do CRESS-PR';
            $obj->NOME_EVENTO  = 'Plenária Participativa do CRESS-PR';
            $obj->URL_OFICIAL  = 'http://www.cresspr.org.br';
            $obj->ABREV_EVENTO = 'CRESS-PR';

            $obj->COD_EVENTO   = '5';
            $obj->ANO_EVENTO   = '2020';
            $obj->MAX_OFICINAS = '0';
            
            $obj->ABREV_EVENTO_EMAIL = 'CRESS-PR';
            $obj->EMAIL_CONTATO   = 'cresspr@gmail.com';
            $obj->EMAIL_REPLAY_TO = '';
            $obj->EMAIL_BCC       = '';
            $obj->EMAIL_CONTATO_ATT = 'Comissão Organizadora da Plenária Participativa do CRESS-PR';
            
            $obj->LINK_CONTATO      = 'http://www.cresspr.org.br/site/contato/';
            
            $obj->PRESIDENTE        =   'Andrea Luiza Curralinho Braga';
            $obj->PRESIDENTE_TITULO = 'Presidenta do CRESS-PR';
//            return $obj;
       }
       if($obj->codEvento==6){
           $obj->noOuNa          = "na";
           $obj->relalizadoOurelalizado = "realizada";
           $obj->nomeEvento      = "7º Mostra Paranaense de Pesquisas e de Relatos de Experiências em Saúde";
           $obj->cidadeEvento    = "Londrina-PR";
           $obj->periodoEvento   = "09 e 10 de julho de 2021";
           $obj->nomeOrganizador = " INSTITUTO DE ESTUDOS EM SAUDE COLETIVA";
           $obj->cidadeOrganizador = "Londrina-PR";
           $obj->cnpjOrganizador   = "CNPJ: 80.511.173/0001-03 ";
           $obj->enderecoOrganizador    = "Av. Rio de Janeiro, 1500 Sl.11 - cep:86010-150";
           $obj->telefoneOrganizador    = "(43) 3024 6221";
           $obj->assinaturaOrganizador  = "Prof.Dr. Márcio José de Almeida<br>Presidente do ".$obj->nomeEvento;
           $obj->dataEmissao = "Londrina, 11 de julho de 2021.";
           $obj->urlBase     = "http://www.opctec.net.br/inesco/7mostraparanaensesaude";
           $obj->urlBaseFormularioAvaliacao = "https://forms.gle/d2nobnDq3UKxMRQQ8";
           
            $obj->DESC_EVENTO  = '7º  Mostra Paranaense de Pesquisas e de Relatos de Experiências em Saúde';
            $obj->NOME_EVENTO  = ' Mostra Paranaense de Pesquisas e de Relatos de Experiências em Saúde';
            $obj->URL_OFICIAL  = 'http://mostraparanaensesaude.org.br';
            $obj->ABREV_EVENTO = '7º MPSPC';

            $obj->COD_EVENTO   =  '6';
            $obj->ANO_EVENTO   =  '2021';
            $obj->MAX_OFICINAS = '2';
            
            $obj->ABREV_EVENTO_EMAIL = '7.MPSPC';
            $obj->EMAIL_CONTATO   = 'inesco@inesco.org.br';
            $obj->EMAIL_REPLAY_TO = '';
            $obj->EMAIL_BCC       = '';
            $obj->EMAIL_CONTATO_ATT = 'Comissão Organizadora do 7ºMPSPC - Inesco';
            
            $obj->LINK_CONTATO      = 'http://mostraparanaensesaude.org.br/#contact';
            
            $obj->COORDENADOR_GERAL =   'Prof.Dr. Márcio José de Almeida';
            $obj->PRESIDENTE        =   $obj->COORDENADOR_GERAL;
            $obj->PRESIDENTE_TITULO = 'Presidente do 7º Mostra Paranaense de Pesquisas e de Relatos de Experiências em Saúde';
//            return $obj;
       }
       if($obj->codEvento==7){
           $obj->noOuNa          = "no";
           $obj->doOuDa          = "do";
           $obj->relalizadoOurelalizado = "realizada";
           $obj->nomeEvento      = "6º Congresso Paranaense de Saúde Púbica/Coletiva";
           $obj->nomeEventoCompleto = "6º Congresso Paranaense de Saúde Púbica/Coletiva, 8º Mostra Paranaense de Pesquisas e de Relatos de Experiências em Saúde e 7º Prêmio Inova Saúde Paraná";
           $obj->cidadeEvento    = "Londrina-PR";
           $obj->periodoEvento   = "14 e 16 de julho de 2022";
           $obj->nomeOrganizador = " INSTITUTO DE ESTUDOS EM SAUDE COLETIVA";
           $obj->cidadeOrganizador = "Londrina-PR";
           $obj->cnpjOrganizador   = "CNPJ: 80.511.173/0001-03 ";
           $obj->enderecoOrganizador    = "Av. Rio de Janeiro, 1500 Sl.11 - cep:86010-150";
           $obj->telefoneOrganizador    = "(43) 3024 6221";
           $obj->assinaturaOrganizador  = "Prof.Dr. Márcio José de Almeida<br>Presidente do ".$obj->nomeEvento;
           $obj->dataEmissao = "Londrina, 16 de julho de 2022.";
           $obj->urlBase     = "http://www.opctec.net.br/inesco/6congresso";
           #@todo liberar certificado sem avaliacao
           $obj->liberarFormularioAvaliacao = true; #true/false
           $obj->urlBaseFormularioAvaliacao = "https://docs.google.com/forms/d/e/1FAIpQLSd_UntmTuq_pCi6_nU_89rv4vTZnmvd2LCP98S4913vTrp5sg/viewform";
           
            $obj->DESC_EVENTO  = '6º Congresso Paranaense de Saúde Púbica/Coletiva';
            $obj->NOME_EVENTO  = ' Congresso Paranaense de Saúde Púbica/Coletiva';
            $obj->URL_OFICIAL  = 'http://congressosaudepublica.org.br';
            $obj->ABREV_EVENTO = '6º CPSPC';

            $obj->COD_EVENTO   =  '7';
            $obj->ANO_EVENTO   =  '2022';
            $obj->MAX_OFICINAS = '2';
            
            $obj->ABREV_EVENTO_EMAIL = '6.CPSPC';
            $obj->EMAIL_CONTATO   = 'inesco@inesco.org.br';
            $obj->EMAIL_REPLAY_TO = '';
            $obj->EMAIL_BCC       = '';
            $obj->EMAIL_CONTATO_ATT = 'Comissão Organizadora do 6ºCPSPC - Inesco';
            
            $obj->LINK_CONTATO      = 'http://congressosaudepuglica.org.br/#contact';
            
            $obj->COORDENADOR_GERAL =   'Prof.Dr. Márcio José de Almeida';
            $obj->PRESIDENTE        =   $obj->COORDENADOR_GERAL;
            $obj->PRESIDENTE_TITULO = 'Presidente do 6º Congresso Paranaense de Saúde Púbica/Coletiva';
//            return $obj;
       }
       if($obj->codEvento==8){
           $obj->noOuNa          = "no";
           $obj->doOuDa          = "do";
           $obj->relalizadoOurelalizado = "realizada";
           $obj->nomeEvento      = "CLÍNICA DE FUTEBOL: ATUAÇÃO PROFISSIONAL EM CATEGORIAS DE BASE";
           $obj->nomeEventoCompleto = "CLÍNICA DE FUTEBOL: ATUAÇÃO PROFISSIONAL EM CATEGORIAS DE BASE";
           $obj->cidadeEvento    = "Londrina-PR";
           $obj->periodoEvento   = "02 A 05 de novembro de 2022";
           $obj->nomeOrganizador = " PSTS/FAUEL";
           $obj->cidadeOrganizador = "Londrina-PR";
           $obj->cnpjOrganizador   = "";
           $obj->enderecoOrganizador    = "";
           $obj->telefoneOrganizador    = "";
           $obj->assinaturaOrganizador  = "Coordenação da ".$obj->nomeEvento;
           $obj->dataEmissao = "Londrina, 16 de julho de 2022.";
           $obj->urlBase     = "https://www.opctec.net.br/pstc/pacbase";
           #@todo liberar certificado sem avaliacao
           $obj->liberarFormularioAvaliacao = false; #true/false
           $obj->urlBaseFormularioAvaliacao = "https://docs.google.com/forms/d/e/1FAIpQLSd_UntmTuq_pCi6_nU_89rv4vTZnmvd2LCP98S4913vTrp5sg/viewform";
           
            $obj->DESC_EVENTO  = 'CLÍNICA DE FUTEBOL: ATUAÇÃO PROFISSIONAL EM CATEGORIAS DE BASE';
            $obj->NOME_EVENTO  = 'CLÍNICA DE FUTEBOL: ATUAÇÃO PROFISSIONAL EM CATEGORIAS DE BASE';
            $obj->URL_OFICIAL  = 'http://congressosaudepublica.org.br';
            $obj->ABREV_EVENTO = 'PACBASE';

            $obj->COD_EVENTO   =  '8';
            $obj->ANO_EVENTO   =  '2022';
            $obj->MAX_OFICINAS = '0';
            
            $obj->ABREV_EVENTO_EMAIL = 'PACBASE';
            $obj->EMAIL_CONTATO   = 'contato.opctec@gmail.com';
            $obj->EMAIL_REPLAY_TO = '';
            $obj->EMAIL_BCC       = '';
            $obj->EMAIL_CONTATO_ATT = 'Comissão Organizadora do PACBASE - PSTEC/UEL';
            
            $obj->LINK_CONTATO      = 'https://www.opctec.net.br/pstc/pacbase/#contact';
            
            $obj->COORDENADOR_GERAL =   'Prof.Dr. Leandro Ricardo Altimari';
            $obj->PRESIDENTE        =   $obj->COORDENADOR_GERAL;
            $obj->PRESIDENTE_TITULO = 'Coordenador do Curso de Futebol: Atuação Profissional em Categorias de Base';
//            return $obj;
       }
       if($obj->codEvento==9){
           $obj->noOuNa          = "no";
           $obj->doOuDa          = "do";
           $obj->relalizadoOurelalizado = "realizada";
           $obj->nomeEvento      = "AREMAC";
           $obj->nomeEventoCompleto = "AREMAC";
           $obj->cidadeEvento    = "Arraial do Cabo - RJ";
           $obj->periodoEvento   = "";
           $obj->nomeOrganizador = " AREMAC";
           $obj->cidadeOrganizador = "Arraial do Cabo - RJ";
           $obj->cnpjOrganizador   = "";
           $obj->enderecoOrganizador    = "";
           $obj->telefoneOrganizador    = "";
           $obj->assinaturaOrganizador  = "Administração da ".$obj->nomeEvento;
           $obj->dataEmissao = "Londrina, ".Date('d')." de ".Date('m')." de ".Date('Y').".";
           $obj->urlBase     = "https://www.opctec.net.br/aremac/ingresso";
           #@todo liberar certificado sem avaliacao
           $obj->liberarFormularioAvaliacao = false; #true/false
           $obj->urlBaseFormularioAvaliacao = "https://docs.google.com/forms/d/e/1FAIpQLSd_UntmTuq_pCi6_nU_89rv4vTZnmvd2LCP98S4913vTrp5sg/viewform";
           
            $obj->DESC_EVENTO  = 'Ingressos Arraial do Cabo';
            $obj->NOME_EVENTO  = 'Ingressos Arraial do Cabo';
            $obj->URL_OFICIAL  = 'http://congressosaudepublica.org.br';
            $obj->ABREV_EVENTO = 'PACBASE';

            $obj->COD_EVENTO   =  '9';
            $obj->ANO_EVENTO   =  '2022';
            $obj->MAX_OFICINAS = '0';
            
            $obj->ABREV_EVENTO_EMAIL = 'AREMAC';
            $obj->EMAIL_CONTATO   = 'contato.opctec@gmail.com';
            $obj->EMAIL_REPLAY_TO = '';
            $obj->EMAIL_BCC       = '';
            $obj->EMAIL_CONTATO_ATT = 'Administração AREMAC';
            
            $obj->LINK_CONTATO      = 'https://www.opctec.net.br/aremac/ingresso/#contact';
            
            $obj->COORDENADOR_GERAL =   '';
            $obj->PRESIDENTE        =   $obj->COORDENADOR_GERAL;
            $obj->PRESIDENTE_TITULO = 'Administração Geral da Aremac';
//            return $obj;
       }
       if($obj->codEvento==10){
           $obj->noOuNa          = "no";
           $obj->doOuDa          = "do";
           $obj->relalizadoOurelalizado = "realizada";
           $obj->nomeEvento      = "HUTEC";
           $obj->nomeEventoCompleto = "HUTEC";
           $obj->cidadeEvento    = "Londrina - PR";
           $obj->periodoEvento   = "";
           $obj->nomeOrganizador = " HUTEC";
           $obj->cidadeOrganizador = "Londrina - PR";
           $obj->cnpjOrganizador   = "";
           $obj->enderecoOrganizador    = "";
           $obj->telefoneOrganizador    = "";
           $obj->assinaturaOrganizador  = "Administração da ".$obj->nomeEvento;
           $obj->dataEmissao = "Londrina, ".Date('d')." de ".Date('m')." de ".Date('Y').".";
           $obj->urlBase     = "https://www.opctec.net.br/hutec/cursos";
           #@todo liberar certificado sem avaliacao
           $obj->liberarFormularioAvaliacao = false; #true/false
           $obj->urlBaseFormularioAvaliacao = "https://docs.google.com/forms/d/e/1FAIpQLSd_UntmTuq_pCi6_nU_89rv4vTZnmvd2LCP98S4913vTrp5sg/viewform";
           
            $obj->DESC_EVENTO  = 'Cursos Hutec';
            $obj->NOME_EVENTO  = 'Cursos Hutec';
            $obj->URL_OFICIAL  = 'http://hutec.com.br';
            $obj->ABREV_EVENTO = 'HUTEC';

            $obj->COD_EVENTO   =  '10';
            $obj->ANO_EVENTO   =  '2022';
            $obj->MAX_OFICINAS = '0';
            
            $obj->ABREV_EVENTO_EMAIL = 'HUTEC';
            $obj->EMAIL_CONTATO   = 'contato.opctec@gmail.com';
            $obj->EMAIL_REPLAY_TO = '';
            $obj->EMAIL_BCC       = '';
            $obj->EMAIL_CONTATO_ATT = 'Administração HUTEC';
            
            $obj->LINK_CONTATO      = 'https://www.opctec.net.br/hutec/cursos/#contact';
            
            $obj->COORDENADOR_GERAL =   '';
            $obj->PRESIDENTE        =   $obj->COORDENADOR_GERAL;
            $obj->PRESIDENTE_TITULO = 'Administração Geral da Hutec';
//            return $obj;
       }
       if($obj->codEvento==18){
           $obj->noOuNa          = "no";
           $obj->doOuDa          = "do";
           $obj->relalizadoOurelalizado = "realizada";
           $obj->nomeEvento      = "HUTEC";
           $obj->nomeEventoCompleto = "HUTEC";
           $obj->cidadeEvento    = "Londrina - PR";
           $obj->periodoEvento   = "";
           $obj->nomeOrganizador = " HUTEC";
           $obj->cidadeOrganizador = "Londrina - PR";
           $obj->cnpjOrganizador   = "";
           $obj->enderecoOrganizador    = "";
           $obj->telefoneOrganizador    = "";
           $obj->assinaturaOrganizador  = "Administração da ".$obj->nomeEvento;
           $obj->dataEmissao = "Londrina, ".Date('d')." de ".Date('m')." de ".Date('Y').".";
           $obj->urlBase     = "https://www.opctec.net.br/hutec/18";
           #@todo liberar certificado sem avaliacao
           $obj->liberarFormularioAvaliacao = false; #true/false
           $obj->urlBaseFormularioAvaliacao = "https://docs.google.com/forms/d/e/1FAIpQLSd_UntmTuq_pCi6_nU_89rv4vTZnmvd2LCP98S4913vTrp5sg/viewform";
           
            $obj->DESC_EVENTO  = 'Cursos Hutec';
            $obj->NOME_EVENTO  = 'Cursos Hutec';
            $obj->URL_OFICIAL  = 'http://hutec.com.br';
            $obj->ABREV_EVENTO = 'HUTEC';

            $obj->COD_EVENTO   =  '18';
            $obj->ANO_EVENTO   =  '2023';
            $obj->MAX_OFICINAS = '0';
            
            $obj->ABREV_EVENTO_EMAIL = 'HUTEC';
            $obj->EMAIL_CONTATO   = 'contato.opctec@gmail.com';
            $obj->EMAIL_REPLAY_TO = '';
            $obj->EMAIL_BCC       = '';
            $obj->EMAIL_CONTATO_ATT = 'Administração HUTEC';
            
            $obj->LINK_CONTATO      = 'https://www.opctec.net.br/hutec/18/#contact';
            
            $obj->COORDENADOR_GERAL =   '';
            $obj->PRESIDENTE        =   $obj->COORDENADOR_GERAL;
            $obj->PRESIDENTE_TITULO = 'Administração Geral da Hutec';
//            return $obj;
       }

    }
}
