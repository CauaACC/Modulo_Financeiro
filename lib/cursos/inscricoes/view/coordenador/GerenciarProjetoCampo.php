<title>Cadastro de Projetos</title>

    <!-- Bootstrap 3 CSS -->
    <!--link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"-->

<style>
    .hidden {
        display: none;
    }

    .step {
        margin-top: 20px;
    }

    /* Estilizando as Bolinhas */
    .progress-tracker {
        display: flex;
        justify-content: space-between;
        margin: 20px 0;
        list-style: none;
        padding: 0;
    }

    .progress-tracker li {
        width: 25%;
        text-align: center;
        position: relative;
    }

    .progress-tracker li::before {
        content: "";
        width: 100%;
        height: 4px;
        background: #ddd;
        position: absolute;
        top: 15px;
        left: 50%;
        z-index: -1;
    }

    .progress-tracker li:first-child::before {
        width: 50%;
        left: 50%;
    }

    .progress-tracker li:last-child::before {
        width: 50%;
        left: 0;
    }

    .progress-tracker li .circle {
        width: 30px;
        height: 30px;
        line-height: 30px;
        border-radius: 50%;
        background: #ddd;
        display: inline-block;
        color: white;
        font-weight: bold;
    }

    .progress-tracker li.active .circle {
        background: #007bff;
    }

    .progress-tracker li.active::before {
        background: #007bff;
    }
</style>

<div class="container" style="margin-bottom: 100px">
    <h2 class="text-center">Cadastro de Projetos</h2>

    <ul class="progress-tracker">
        <li class="active"><span class="circle">1</span><br>Dados Pessoais</li>
        <li><span class="circle">2</span><br>Endereço</li>
        <li><span class="circle">3</span><br>Confirmação</li>
        <li><span class="circle">4</span><br>Finalização</li>
    </ul>

    <form id="multiStepForm">
        <div class="step" id="step1">
            <?php require_once("GerenciarProjetoCampo01.php"); ?>
            <button style="margin-right: 15px" type="button" class="btn btn-primary next pull-right">Próximo</button>

        </div>
        <div class="step hidden" id="step2">
            <?php require_once("GerenciarProjetoCampo02.php"); ?>
            <button type="button" class="btn btn-default prev pull-left">Voltar</button>
            <button style="margin-right: 15px" type="button" class="btn btn-primary next pull-right">Próximo</button>
        </div>
        <div class="step hidden" id="step3">
            <?php require_once("GerenciarProjetoCampo03.php"); ?>
            <button type="button" class="btn btn-default prev pull-left">Voltar</button>
            <button style="margin-right: 15px" type="button" class="btn btn-primary next pull-right">Próximo</button>
        </div>

        <div class="step hidden" id="step4">
            <?php require_once("GerenciarProjetoCampo04.php"); ?>
            <button type="button" class="btn btn-default prev pull-left">Voltar</button>
            <button style="margin-right: 15px" type="button" class="btn btn-primary next pull-right">Finalizar</button>
        </div>
    </form>
</div>


<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script> -->

<script>
    $(document).ready(function() {
        var currentStep = 1;
        var totalSteps = 4;

        function updateProgressTracker() {
            $(".progress-tracker li").removeClass("active");
            $(".progress-tracker li").slice(0, currentStep).addClass("active");
        }

        $(".next").click(function() {
            if (currentStep < totalSteps) {
                $("#step" + currentStep).addClass("hidden");
                currentStep++;
                $("#step" + currentStep).removeClass("hidden");
                updateProgressTracker();
            }
        });

        $(".prev").click(function() {
            if (currentStep > 1) {
                $("#step" + currentStep).addClass("hidden");
                currentStep--;
                $("#step" + currentStep).removeClass("hidden");
                updateProgressTracker();
            }
        });

        $("#multiStepForm").submit(function(event) {
            event.preventDefault();
            alert("Cadastro finalizado com sucesso!");
        });

        updateProgressTracker();
    });
</script>