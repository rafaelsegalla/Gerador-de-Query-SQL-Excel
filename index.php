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
        <input type="file" name="importTraducao" id="importTraducao" class="upload-box" placeholder="Fazer Upload"
               accept=".csv, .xls, .xlsx" title="Fazer Upload">
    </div>
</header>
<section>
    <textarea style="height: 400px; width: 100%;" id="resultado_em_json"></textarea>
</section>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="SheetJS/xlsx.full.min.js"></script>
<script>
    var banco = 'basilar.en_internacionalizacao';
    var coluna = 'es_es';
    $('#importTraducao').click(function () {
        this.value = '';
    });

    function montaPreview(json) {
        var result = '';
        for (let i = 0; i < json.length; i++) {
            linha = 'update ' + banco + ' set ' + coluna + " = '" + json[i]['es_es'] + "' where token = '" + json[i]['Token'] + "'; &#13;&#10;";
            result += linha;
        }
        $("#resultado_em_json").empty();
        $("#resultado_em_json").append(result);
    }

    $('#importTraducao').change(function (e) {
        var files = e.target.files, f = files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            tokensJSON = convertePlanilhaEmJSON(e);
            montaPreview(tokensJSON);
        };
        reader.readAsArrayBuffer(f);
    });
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