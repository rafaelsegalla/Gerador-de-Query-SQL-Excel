<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SheetJS</title>
    <script src="js/jquery.min.js"></script>
    <script src="js/xlsx.full.min.js"></script>
</head>
<body>
    <div id="wrapper">
        <input type="file" id="inputUploadExcel">
    </div>
</body>
<script>
    $('#inputUploadExcel').change(function (e) {
        var files = e.target.files, f = files[0];
        var reader = new FileReader();

        reader.onload = function (e) {
        var data = new Uint8Array(e.target.result);

        var workbook = XLSX.read(data, {type:"array"});

        var first_sheet_name = workbook.SheetNames[0];

        var worksheet = workbook.Sheets[first_sheet_name];
        console.log(XLSX.utils.sheet_to_json(worksheet,{raw:true}));


        // var htmlstr = XLSX.write(wb,{sheet:"", type:'binary',bookType:'json'});
        // $('#wrapper')[0].innerHTML += htmlstr;
    };

    reader.readAsArrayBuffer(f);
    });
    function doit(type, fn, dl) {
        var elt = document.getElementById('data-table');
        var wb = XLSX.utils.table_to_book(elt, {sheet:"Sheet JS"});
        return dl ?
            XLSX.write(wb, {bookType:type, bookSST:true, type: 'base64'}) :
            XLSX.writeFile(wb, fn || ('test.' + (type || 'xlsx')));
    }
</script>
</html>
