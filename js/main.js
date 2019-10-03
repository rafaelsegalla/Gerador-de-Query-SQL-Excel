$('#importTraducao').click(function () {
    this.value = '';
});

function getColumnsNameJson(json) {
    if (json.length > 0){
        var columnsIn = json[0];
        columns = [];
        for(var key in columnsIn){
            columns.push(key);
        }
        return columns;
    }
    return null;
}

function criaConsultas(json, colunas) {
    var result = '';
    var banco = $('#db').val();
    var tabela = $('#table').val();

    if(banco != "") {
        tabela = "." + $('#table').val();
    }

    if(tabela == "") {
        $("#resultado_em_json").empty();
        $("#resultado_em_json").append("Informe a tabela!");
        return;
    }
    var operacao = $('#operacao').val();

    for (let i = 0; i < json.length; i++) {
        var params = "";
        for(let ii = 0; ii < colunas.length; ii++) {
            if($('#' + colunas[ii]).is(":checked")){
                coluna = colunas[ii];
                $('#aspas' + colunas[ii]).is(":checked") ?
                params += coluna + " = '" + json[i][coluna] + "'"
                 :
                params += coluna + " =  " + json[i][coluna];
                
                params += ', '
            }
        }
        var pos = params.lastIndexOf(',');
        params = params.substring(0, pos);

        if(operacao === "Update") {
            pk = $('#pk').val();
            prefixo = $("#prefixo").val();

            linha = 'UPDATE' + banco.toLowerCase() + tabela.toLowerCase() + ' SET ' + params + ' WHERE ' + pk + " = '" + prefixo + json[i][pk] + "'; &#13;&#10;";
        } else {
            linha = 'INSERT INTO ' + banco.toLowerCase() + tabela.toLowerCase() + ' SET ' + params + "; &#13;&#10;";
        }
        result += linha;
    }

    $("#resultado_em_json").empty();
    $("#resultado_em_json").append(result);

    $("#btn-copy").html("Copiar");
    $("#btn-copy").css("padding", "0 20px");
}

var colunasT;
var jsonS;
var ev = false;
$('#importTraducao').change(function (e) {
    var files = e.target.files, f = files[0];
    var reader = new FileReader();
    reader.onload = function (e) {
        jsonS = convertePlanilhaEmJSON(e);
        colunasT = getColumnsNameJson(jsonS);
        exibeCamposColunas(colunasT, "columns");
        criaConsultas(jsonS, colunasT);
    };
    reader.readAsArrayBuffer(f);
    if(ev === false){

        $('#db').keyup(function() {
            criaConsultas(jsonS, colunasT)
        });
        $('#table').keyup(function() {
            criaConsultas(jsonS, colunasT)
        });
        $('#operacao').change(function() {
            if($('#operacao').val() == "Insert"){
                $('#pk-div').addClass("disabled");
                criaConsultas(jsonS, colunasT);
                return;
            }
            $('#pk-div').removeClass("disabled");
            criaConsultas(jsonS, colunasT);
        });
        ev = true;
    }
});

function exibeCamposColunas(arrayColunas, divId) {
    if(arrayColunas == null) {
        $('#' + divId).empty();
        $('#' + divId).append("<p style='text-align:'>Não foi possivel encontrar colunas no excel selecionado.</p>");
        return;
    }
    totalCheckbox = "<div class='mb-3'><h6 class='tip'>Selecione as colunas que serão utilizadas * </h6>";
    totalCheckboxAspas = "<div class='mb-3'><h6 class='tip'>Selecione as colunas que não necessitam de aspas * </h6>";
    // language=HTML
    selectPk = '<div class="input-group mb-3 col-sm-12 col-xs-12 col-12 col-md-6 col-xl-6" id="pk-div">' +
        '<div class="input-group-prepend">' +
        '<label class="input-group-text bc-yellow" id="basic-addon4" style="height: 33px;" for="pk"><i class="fas fa-key"></i></label>' +
        "</div><select class='browser-default custom-select form-control' id='pk' " +
        "onchange='criaConsultas(jsonS, colunasT)'" +
        "aria-label='PK' aria-describedby='basic-addon4'>" +
        '</div>';

    for(let i=0; i < arrayColunas.length; i++) {
        if(arrayColunas[i] != "__EMPTY"){
            totalCheckbox += "<div class='custom-control custom-checkbox custom-control-inline'>" +
                "<input type='checkbox' class='custom-control-input' checked id='" + arrayColunas[i] + "' value='" + arrayColunas[i] +
                "' onchange='criaConsultas(jsonS, colunasT)'> <label class='custom-control-label' for='" + arrayColunas[i] + "'>"
                + arrayColunas[i].toLowerCase() + "</label></div>";

            totalCheckboxAspas += "<div class='custom-control custom-checkbox custom-control-inline'>" +
                "<input type='checkbox' class='custom-control-input' checked id='aspas" + arrayColunas[i] + "' value='" + arrayColunas[i] +
                "' onchange='criaConsultas(jsonS, colunasT)'> <label class='custom-control-label' for='aspas" + arrayColunas[i] + "'>"
                + arrayColunas[i].toLowerCase() + "</label></div>";


            selectPk += "<option value='" + arrayColunas[i] + "'>" + arrayColunas[i] + "</option>";
        }
    }
    totalCheckboxAspas += "</div>";
    selectPk += "</select>" +
        '<div class="input-group mb-3 col-sm-12 col-xs-12 col-12 col-md-6 col-xl-6 pad-0 mt-15">' +
        '<div class="input-group-prepend">' +
        '<label class="input-group-text bc-grey" id="basic-addon3" for="prefixo"><i class="fab fa-autoprefixer"></i></label>' +
        "</div><input type='text' id='prefixo' onkeyup='criaConsultas(jsonS, colunasT)' " +
        'class="form-control" placeholder="Prefixo" aria-label="Prefixo" aria-describedby="basic-addon3">' +
        '</div>' +
        '</div>' +
        '<div class="col-sm-12 col-xs-12 col-4-5 "></div><div class="row justify-content-en"><div class="input-group mb-3" >' +
       '</div></div>';
    //    '<button type="button" id="btn-copy" class="btn btn-dark adj-btn" onclick=' + "'" + 'copyToClipboard("#resultado_em_json")' + "'" +'>Copiar</button>

    $('#' + divId).empty();
    $('#' + divId).append(selectPk + "<div class='col-12 mb-3'>" + totalCheckbox + "</div>" + totalCheckboxAspas);
}

function convertePlanilhaEmJSON(e) {
    var data = new Uint8Array(e.target.result);

    var workbook = XLSX.read(data, { type: "array" });

    var first_sheet_name = workbook.SheetNames[0];

    var worksheet = workbook.Sheets[first_sheet_name];

    return XLSX.utils.sheet_to_json(worksheet, { raw: true });

}
function copyToClipboard() {
    var $temp = $("<textarea>");
    $("body").append($temp);
    $temp.val($('#resultado_em_json').text()).select();
    document.execCommand("copy");
    $temp.remove();
    $("#btn-copy").html("Copiado");
    $("#btn-copy").css("padding", "0 17px");
}