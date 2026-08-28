<style>
body{
    font-family: sans-serif;  
}

.dependentes{
    padding: 1rem;
    border: 1px solid #ccc;
}

.dependentes button{
    border: none;
    background-color: #5eb2e6;
    color: white;
    border-radius: 3px;
    text-transform: uppercase;
    padding: .75rem 1rem;
}
.dependentes button.green{
    background-color: #005c54;
    margin-top: 2rem;
}

.dependentes .container{
    margin-top: 1rem;
    display: grid;
    grid-gap: .5rem;
}

.dependentes .container input{
    padding: .5rem .75rem;
    border: 1px solid #ccc;
}

.dependentes .container input:focus{
    border: 1px solid black;
    outline: none;
}

.dependentes .container .dependente{
    display: grid;
    grid-gap: .5rem;
    grid-template-columns: 33% 33% 33%;
}

.dependentes .container .dependente .action a{
    text-transform: uppercase;
    text-decoration: none;
    padding: .5rem .75rem;
    display: inline-block;
}

.dependentes .container .dependente .action a.salvar{
    border-radius: 2px;
    background-color: #efefef;
    color: #005c54;
}

.disabled{
    pointer-events: none;
    opacity: .5;
    filter: grayscale(100%);
}
</style>
    
<div class="form-group col-sm-12" style="padding-left: 0px;">
    <label for="participou">Já esteve em ARRAIAL DO CABO?</label>
    &nbsp;&nbsp;&nbsp;            
    <input type='radio' name='ja_participou_outro_evento' value='SIM' <?php echo verifItemChecked($objInscrito->getJaParticipouOutroEvento(), 'SIM') ?>  required autofocus>
    Sim
    &nbsp;&nbsp;&nbsp;
    <input type='radio' name='ja_participou_outro_evento' value='NAO' <?php echo verifItemChecked($objInscrito->getJaParticipouOutroEvento(), 'NAO') ?>  required autofocus>
    Não 
</div>
<div class="form-group col-sm-12" style="padding-left: 0px;">
    <label for="pagamentos">Período de Estadia: </label>
    &nbsp;&nbsp;
    <input type="date"  id="data_estadia_ini" name="data_estadia_ini" size="10"
       value="<?php echo $fws_['data_estadia_ini']?>">
    &nbsp;&nbsp; à  &nbsp;&nbsp;
    <input type="date"  id="data_estadia_fim" name="data_estadia_fim" size="10"
       value="<?php echo $fws_['data_estadia_fim']?>">
</div>

<div class="form-group col-sm-12" style="padding-left: 0px;">

<div class="dependentes">
    <button id="btnAdicionarDependentes" type="button">
        📝Adicionar Pessoas/Ingressos:
    </button>
    <input type="hidden" id="ingressos" name="ingressos" value="">
    <div class="container" id="dependentesContainer">
</div>
<!--  <button class="green" id="btnCapturarDados">
  ✅ Capturar Dados
  </button>-->
</div>
<pre id="containerDados">
  
</pre>

</div>

<script language="JavaScript" type="text/javascript">
$(document).ready(function () {
    var dependentes = [];

//    $('#data_estadia_ini').datepicker();
//    $('#data_estadia_fim').datepicker();
//init
    document.querySelector("#btnAdicionarDependentes").addEventListener("click", adicionarDependentes);
//    carregarDependentes();
//capturarDados
//    document.querySelector("#btnCapturarDados").addEventListener("click", () => {
//        document.querySelector("#containerDados").innerHTML = JSON.stringify(dependentes, undefined, 4);
//    });
    
function ingressosInit(){
    var dependentes = [{
        identificador: 13,
        nome: $('#nome_inscrito').val(),
        rg: $('#rg').val()
    }];
}

function carregarDependentes(){
    
    let dependentes_container = document.querySelector("#dependentesContainer");
    dependentes_container.innerHTML = "";
    dependentes.forEach((el)=>{
        let identificador = el.identificador;
        let nome = el.nome;
        let rg = el.rg;
        let dependente_container = `<div class="dependente" data-id="${identificador}">
				<input class="nome" placeholder="Digite o nome" type="text" value="${nome}"/>
                                <input class="rg" placeholder="Digite o Rg" type="number" value="${rg}"/>
                                <div class="action">
                                <a href="#" class="salvar">adicinar 💾</a>
                                <a href="#" class="remover">❌</a>
				</div>
				</div>`;
  	dependentes_container.innerHTML += dependente_container;
  });
  salvarDependentes();
  removerDependentes();
  travarOutros(false);
}

function removerDependentes(){
    document.querySelectorAll("#dependentesContainer .remover").forEach((el, i)=>{
  	el.addEventListener("click", ()=>{
            dependentes.splice(i, 1);  	
            carregarDependentes();
        });
    });
}

function adicionarDependentes(){
    dependentes.push({ identificador: "", nome: "", rg: "" });
    carregarDependentes();
    travarOutros(document.querySelector("#dependentesContainer > div:last-child"));
}

function salvarDependentes(){
    document.querySelectorAll("#dependentesContainer .salvar").forEach((el, i) => {
        el.addEventListener("click", () => {
            let identificador = el.parentElement.parentElement.getAttribute("data-id");
            let nome = el.parentElement.parentElement.querySelector(".nome").value;
            let rg = el.parentElement.parentElement.querySelector(".rg").value;
            if (!nome.length || !rg.length){
                alert("Nome e Rg precisam ser preenchidos para salvar.");
                return false;
            }
            dependentes.splice(i, 1, {identificador: identificador, nome: nome, rg: rg});
            carregarDependentes();
            travarOutros(false);
            $("#ingressos").val(JSON.stringify(dependentes, undefined, 4));
        });
    });
}

function travarOutros(element){
    if (element == false){
        document.querySelectorAll(".dependentes button, .dependentes .container > div").forEach((el) => {
            el.classList.remove("disabled");
        });
        document.querySelector("#containerDados").innerHTML = "";
        return false;
    }
    document.querySelectorAll(".dependentes button, .dependentes .container > div").forEach((el) => {
        if (el != element){
            el.classList.add("disabled");
        }
    });
}
});
</script>