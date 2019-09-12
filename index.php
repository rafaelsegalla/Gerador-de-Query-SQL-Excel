<!doctype html>
<html lang="pt_br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Gerador de Consultas</title>
</head>
<body>
<header>
    <div>
        <label>Importar EXCEL</label>
    </div>
    <div>
        <input type="file" name="importTraducao" id="importTraducao" class="upload-box" placeholder="Fazer Upload"
               accept=".csv, .xls, .xlsx" title="Fazer Upload">
    </div>
    <div>
        <label>Database: </label>
        <input type="text" name="db" id="db" placeholder="Database: ">
    </div>
    <div>
        <label>Table: </label>
        <input type="text" name="table" id="table" placeholder="Table: ">
    </div>
    <div>
        <label>Operação: </label>
        <select id="operacao">
            <option value="Update">Update</option>
        </select>
    </div>
    <div id="columns"></div>
</header>
<section>
    <textarea style="height: 400px; width: 100%;" id="resultado_em_json"></textarea>
</section>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="SheetJS/xlsx.full.min.js"></script>
<script>
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
    function getTotalCheck(colunas) {
        var totalChecado = 0;
        for(let ii = 0; ii < colunas.length; ii++) {
            if($('#' + colunas[ii]).is(":checked")){
                totalChecado++;
            }
        }
        return totalChecado;
    }
    function criaConsultas(json, colunas) {
        var result = '';
        var banco = $('#db').val();
        var tabela = $('#table').val();

        if(banco != "") {
            tabela = "." + $('#table').val();
        }

        if(banco == "" && tabela == "") {
            $("#resultado_em_json").empty();
            $("#resultado_em_json").append("Informe a tabela!");
            return;
        }
        var params = "";
        var operacao = $('#operacao').val();
        var totalChecado = getTotalCheck(colunas);

        for (let i = 0; i < json.length; i++) {
            for(let ii = 0; ii < colunas.length; ii++) {
                if($('#' + colunas[ii]).is(":checked")){
                    coluna = colunas[ii];
                    params += coluna + " = '" + json[i][coluna] + "'";
                    if(ii < (totalChecado - 1)) {
                        params += ', '
                    }
                }
            }
            if(operacao === "Update") {
                pk = $('#pk').val();
                linha = 'update ' + banco + tabela + ' set ' + params + ' where ' + pk + " = '" + json[i][pk] + "'; &#13;&#10;";
            }
            result += linha;
        }
        $("#resultado_em_json").empty();
        $("#resultado_em_json").append(result);
    }

    var colunasT;
    var jsonS;
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
    });

    function exibeCamposColunas(arrayColunas, divId) {
        if(arrayColunas == null) {
            $('#' + divId).empty();
            $('#' + divId).append("<p>Não foi possivel encontrar colunas no excel selecionado.</p>");
            return;
        }
        totalCheckbox = "";
        selectPk = "Primary Key: <select id='pk' onchange='criaConsultas(jsonS, colunasT)'>";
        for(let i=0; i < arrayColunas.length; i++) {
            if(arrayColunas[i] != "__EMPTY"){
                totalCheckbox += "<input type='checkbox' checked id='" + arrayColunas[i] + "' value='" + arrayColunas[i] + "' onchange='criaConsultas(jsonS, colunasT)'> " + arrayColunas[i];
                selectPk += "<option value='" + arrayColunas[i] + "'>" + arrayColunas[i] + "</option>"
            }
        }
        selectPk += "</select>";
        $('#' + divId).empty();
        $('#' + divId).append(selectPk + "<div>" + totalCheckbox + "</div>");
    }

    function convertePlanilhaEmJSON(e) {
        var data = new Uint8Array(e.target.result);

        var workbook = XLSX.read(data, { type: "array" });

        var first_sheet_name = workbook.SheetNames[0];

        var worksheet = workbook.Sheets[first_sheet_name];

        return XLSX.utils.sheet_to_json(worksheet, { raw: true });

    }
</script>
</body>
</html>