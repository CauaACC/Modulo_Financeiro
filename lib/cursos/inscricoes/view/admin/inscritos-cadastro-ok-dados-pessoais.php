<?php
#---- Mostra Dados Pessoais -----------------------------
/*
 * Historico do layout desta aba:
 *
 * 1) <blockquote> com ~50 linhas "<br>Rotulo: <b>valor</b>" numa coluna so — em
 *    tela larga o conteudo ficava espremido a esquerda com metade da tela vazia.
 * 2) Grade de cartao (.card-grid): rotulo EM CIMA, valor embaixo.
 * 3) Agora: o mesmo desenho da PREVIA DA NFS-e (.nfse-previa) — rotulo a
 *    ESQUERDA em cinza, valor a direita, filete fino separando as linhas. A
 *    diferenca para a previa e que la sao 6 linhas dentro de um modal e aqui sao
 *    60+ campos, entao os pares entram numa grade que se ajusta a largura em vez
 *    de uma coluna unica.
 *
 * Campo sem conteudo continua visivel como "—": quem confere um cadastro precisa
 * distinguir "nao tem" de "nao existe esse campo aqui".
 *
 * CSS: .dp-* em resources/css/cartao-listagem.css (carregado por
 * inscritos-cadastro-ok.php). O mesmo desenho vale para a ficha de alteracao,
 * em inscritos-cadastro-campos.php.
 */

/* Helpers locais: proposito identico ao montarSecao/montarCampo do
   functions.php, mas no desenho da previa. Ficam aqui, e nao no functions,
   porque as outras abas (Trabalhos, Oficinas, Premiacao) continuam no desenho
   de cartao — mexer no global mudaria todas de uma vez. */
if (!function_exists('dpSecao')) {
    function dpSecao($titulo) {
        return "<div class='dp-secao'><span>"
             . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8')
             . "</span></div>";
    }

    /**
     * Uma linha rotulo/valor da ficha.
     *
     * @param bool $html true quando o valor ja e HTML pronto (link, badge).
     * @param bool $full true ocupa a linha inteira da grade (texto longo).
     */
    function dpCampo($rotulo, $valor, $html = false, $full = false) {
        $v = trim((string) $valor);
        // "0" e valor legitimo (parcelas, pontos); so string vazia e vazio.
        $vazio = ($v === '' || $v === '-' || $v === '/' || $v === '00/00/0000');

        $conteudo = $vazio ? '&mdash;' : ($html ? $v : htmlspecialchars($v, ENT_QUOTES, 'UTF-8'));

        return "<div class='dp-linha" . ($full ? ' dp-linha-full' : '') . "'>"
             . "<span class='dp-rot'>" . htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') . "</span>"
             . "<span class='dp-val" . ($vazio ? ' vazio' : '') . "'>" . $conteudo . "</span>"
             . "</div>";
    }

    /** Texto aberto: aparece INTEIRO, sem "ver mais". */
    function dpTexto($rotulo, $texto) {
        $t = trim((string) $texto);
        if ($t === '') {
            return '';
        }
        return "<div class='dp-linha dp-linha-full'>"
             . "<span class='dp-rot'>" . htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') . "</span>"
             . "<span class='dp-val dp-texto'>" . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . "</span>"
             . "</div>";
    }
}

if ($objInscrito->getOrigemCadastro() && $objInscrito->getOrigemCadastro() != 'INSCRICAO') {
    echo "<div class='alert alert-warning' style='margin:10px 0;'>
        <strong>Origem do cadastro: " . $objInscrito->getOrigemCadastro() . "</strong> &mdash; ";
    if ($objInscrito->getOrigemCadastro() == 'CERTIFICADO') {
        echo "Cadastro simplificado para emiss&atilde;o de certificado. CPF e e-mail n&atilde;o obrigat&oacute;rios. Sem acesso ao login de inscrito.";
    } elseif ($objInscrito->getOrigemCadastro() == 'VINCULO_LOGIN') {
        echo "Cadastro vinculado a login de usu&aacute;rio administrativo.";
    }
    echo "</div>";
}

echo "<fieldset><legend> Dados Pessoais </legend>";

// Cabecalho no mesmo desenho do topo da previa: titulo a esquerda, acoes a
// direita, separados por um filete.
//
// Botoes iguais aos da ficha de alteracao: btn-sm, mesmo jogo em cima e
// embaixo. A ficha tem 60+ campos — quem termina de conferir esta no fim da
// pagina, e ter de voltar ao topo so para clicar em "Alterar cadastro" era o
// unico jeito ate aqui.
$BOTOES_FICHA = "<a class='btn btn-primary btn-sm' href='index.php?content=view/admin/inscritos-cadastro-alterar.php"
              . "&cod_inscrito=" . $cod_inscrito . "&cod_evento=" . $cod_evento . "'>"
              . "<i class='glyphicon glyphicon-pencil'></i> Alterar cadastro</a>"
              . "<a class='btn btn-default btn-sm' href='index.php?content=view/admin/inscritos-cadastro-incluir.php"
              . "&cod_evento=" . $cod_evento . "' title='Incluir uma nova inscri&ccedil;&atilde;o'>"
              . "<i class='glyphicon glyphicon-plus'></i> Incluir inscrição</a>";

echo "<div class='dp-cab'>";
echo   "<span>Ficha do inscrito</span>";
echo   "<span class='dp-cab-acoes'>" . $BOTOES_FICHA . "</span>";
echo "</div>";

/* Sem .card-item em volta: na previa da NFS-e as linhas ficam soltas sob o
   cabecalho, sem moldura. A caixa com borda arredondada e filete lateral era o
   ultimo resto do desenho de cartao nesta aba. */
echo "<div class='dp-corpo'>";

/* Daqui para baixo a ordem e a divisao das secoes acompanham, campo a campo, a
   ficha de alteracao (inscritos-cadastro-campos.php): conferir e alterar tem de
   ser a MESMA leitura, com cada coisa no mesmo lugar nas duas telas. Ao mexer em
   uma, mexer na outra. */

/* ---- Identificacao ---------------------------------------------------- */
echo dpSecao('Identificação');
echo "<div class='dp-ficha'>";
echo dpCampo('Nome',                 $nome_inscrito);
echo dpCampo('Nome p/ crachá',       $nome_apelido);
echo dpCampo('Perfil de inscrição',  $titulo_perfil);
echo dpCampo('Sexo',                 $sexo);
echo dpCampo('Data de nascimento',   $data_nascimento);
echo dpCampo('Cidade de nascimento', $objInscrito->getCidadeNascimento());
echo dpCampo('Estado de nascimento', $objInscrito->getEstadoNascimento());
echo dpCampo('Raça',                 $objInscrito->getRaca());
echo dpCampo('Estado civil',         $objInscrito->getEstadoCivil());
echo dpCampo('Nome da mãe',          $objInscrito->getNomeMae());
echo dpCampo('Nome do pai',          $objInscrito->getNomePai());
echo "</div>";

/* ---- Situacao da inscricao -------------------------------------------- */
/* Secao que faltava inteira na leitura: status, origem do cadastro e presenca
   so existiam na tela de alterar. Origem aparecia aqui apenas como o alerta
   amarelo do topo, e mesmo assim so quando != INSCRICAO. */
echo dpSecao('Situação da inscrição');
echo "<div class='dp-ficha'>";
echo dpCampo('Status',          $objInscrito->getStatus());
echo dpCampo('Origem cadastro', $objInscrito->getOrigemCadastro());
echo dpCampo('Status processo', $objInscrito->getStatusProcesso());
// Mesma leitura do select do formulario: 1 = Presente, 0 ou vazio = Ausente.
echo dpCampo('Presença', $objInscrito->getFlgPresencaInscrito() == '1' ? 'Presente' : 'Ausente');
echo "</div>";

/* ---- Documentos ------------------------------------------------------- */
echo dpSecao('Documentos');
echo "<div class='dp-ficha'>";
echo dpCampo('CPF',                 $cpf);
echo dpCampo('RG',                  $objInscrito->getRg());
echo dpCampo('RG — data de emissão',$objInscrito->getDataEmissaoView());
echo dpCampo('RG — órgão expedidor',$objInscrito->getOrgaoExpedidor());
echo dpCampo('RG — cidade emissão', $objInscrito->getRgCidadeEmissao());
echo dpCampo('RG — estado emissão', $objInscrito->getRgEstadoEmissao());
echo dpCampo('Número do PIS',       $objInscrito->getPis());
echo dpCampo('Número do NIS',       $objInscrito->getNis());
echo "</div>";

/* ---- Contato ---------------------------------------------------------- */
echo dpSecao('Contato');
echo "<div class='dp-ficha'>";
echo dpCampo('E-mail',   $e_mail);
echo dpCampo('E-mail 2', $objInscrito->getEMail2());
$foneContato = trim($objInscrito->getFoneContatoDdd() . '-' . $objInscrito->getFoneContato(), '- ');
echo dpCampo('Fone de contato',     $foneContato);
echo dpCampo('Telefone residencial',$fone_residencial);
echo dpCampo('Telefone celular',    $fone_celular);
echo dpCampo('Telefone comercial',  $fone_comercial);
echo "</div>";

/* ---- Endereco --------------------------------------------------------- */
/* A data de nascimento e a cidade/estado de nascimento estavam AQUI dentro no
   layout antigo; foram para Identificacao, que e onde pertencem.
   A ordem e a mesma do formulario: CEP primeiro, porque e ele que preenche o
   resto pelo ViaCEP; Estado antes de Cidade, porque um depende do outro. */
echo dpSecao('Endereço');
echo "<div class='dp-ficha'>";
echo dpCampo('CEP',         $cep);
echo dpCampo('Rua',         $endereco);
echo dpCampo('Número',      $numero);
echo dpCampo('Complemento', $complemento);
echo dpCampo('Bairro',      $bairro);
echo dpCampo('Estado',      $estado);
echo dpCampo('Cidade',      $cidade);
echo "</div>";

/* ---- Formacao --------------------------------------------------------- */
echo dpSecao('Formação');
echo "<div class='dp-ficha'>";
// Era getEscolaridade(), que nao existe em lugar nenhum do sistema: a coluna
// gravada pelo formulario e grau_escolaridade, entao este campo aparecia SEMPRE
// vazio na leitura mesmo com valor preenchido no cadastro.
echo dpCampo('Grau de escolaridade', $objInscrito->getGrauEscolaridade());
echo dpCampo('Graduação',           $objInscrito->getGraduacao());
echo dpCampo('Ano da graduação',    $objInscrito->getGraduacaoAno());
echo dpCampo('IES da graduação',    $objInscrito->getGraduacaoIes());
echo dpCampo('Pós-graduação',       $objInscrito->getPosGraduacao());
echo dpCampo('Ano da pós-graduação',$objInscrito->getPosGraduacaoAno());
echo dpCampo('IES da pós-graduação',$objInscrito->getPosGraduacaoIes());
echo "</div>";

/* ---- Profissional ----------------------------------------------------- */
echo dpSecao('Profissional');
echo "<div class='dp-ficha'>";
echo dpCampo('Tipo da instituição',       $tipo_instituicao);
echo dpCampo('Instituição / empresa',     $instituicao);
echo dpCampo('Profissão / atividade',     $profissao_atuacao);
echo dpCampo('Empresa atual',             $objInscrito->getEmpresaAtual());
echo dpCampo('Cargo atual',               $objInscrito->getCargoAtual());
echo dpCampo('Pretensão na carreira',     $objInscrito->getPretensaoCarreira());
echo "</div>";

/* ---- Sobre a inscricao ------------------------------------------------ */
/* Estes tres estavam misturados em "Profissional"; no formulario formam uma
   secao propria. */
echo dpSecao('Sobre a inscrição');
echo "<div class='dp-ficha'>";
echo dpCampo('Como ficou sabendo do curso', $objInscrito->getComoFicouSabendo(), false, true);
// Texto aberto: aparece inteiro, sem corte.
echo dpTexto('Por qual motivo decidiu se inscrever no curso', $objInscrito->getQualMotivoInscreveu());
echo dpCampo('Tipo da inscrição',         $tipo_inscricao, false, true);
echo "</div>";

/* ---- Dados bancarios -------------------------------------------------- */
echo dpSecao('Dados bancários');
echo "<div class='dp-ficha'>";
echo dpCampo('Cód. / nome do banco',   $objInscrito->getNmBanco());
echo dpCampo('Agência - DV',           $objInscrito->getDbAgencia());
echo dpCampo('Conta corrente - DV',    $objInscrito->getDbContaCorrente());
echo dpCampo('Pix',                    $objInscrito->getDbPix());
echo "</div>";

#Almoço
if ($ipLiberado and $cod_evento == 1) {
    echo dpSecao('Almoço');
    echo "<div class='dp-ficha'>";
    $refeicoes = array(
        'Terca'  => 'Terça-feira – 15/08/2017',
        'Quarta' => 'Quarta-feira – 16/08/2017',
        'Quinta' => 'Quinta-feira – 17/08/2017',
    );
    foreach ($refeicoes as $chave => $rotulo) {
        $marcado = (verifItemChecked($chave, json_decode(retirarBarras($info_almoco))) == 'checked');
        echo dpCampo($rotulo, $marcado ? 'Sim' : 'Não');
    }
    echo "</div>";
}

/* ---- Recibo / institucional ------------------------------------------- */
echo dpSecao('Pagamento / recibo em nome de terceiro');

$perguntaInstitucional = in_array($objEv->getCodEvento(), array(80, 88))
    ? 'Deseja inscrever em nome de sua Instituição/Empresa?'
    : 'Deseja emitir Pagamento/Recibo em nome de sua Instituição/Empresa ou terceiro?';

// Mesma ordem do formulario.
echo "<div class='dp-ficha'>";
echo dpCampo($perguntaInstitucional, $institucional ? 'SIM' : 'NÃO', false, true);
echo dpCampo('Nome da instituição',   $nome_recibo);
echo dpCampo('CPF / CNPJ',            $cpf_recibo);
echo dpCampo('Rua',                   $endereco_recibo);
echo dpCampo('Número',                $numero_recibo);
echo dpCampo('Complemento',           $complemento_recibo);
echo dpCampo('CEP',                   $cep_recibo);
echo dpCampo('Bairro',                $bairro_recibo);
echo dpCampo('Estado',                $estado_recibo);
echo dpCampo('Cidade',                $cidade_recibo);
echo "</div>";

echo "</div>";      // .dp-corpo

// Mesma barra do topo, repetida no fim — igual as fichas de alterar/incluir.
echo "<div class='dp-cab' style='border-bottom:0; border-top:1px solid #eee;"
   . " padding-top:10px; margin-top:14px;'>";
echo   "<span></span>";
echo   "<span class='dp-cab-acoes'>" . $BOTOES_FICHA . "</span>";
echo "</div>";

echo "</fieldset>";
