<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="css/style.css" rel="stylesheet">
        <link href="{{ asset('css/app.css') }} rel="stylesheet" type="text/css" />
        <title>Importar - APS</title>
    </head>
    <body>
        <div class="container">
            <br/>
            <div class="row">
                <div class="clod-md-4"></div>
                <div class="clod-md-6"></div>
                    <div  class="row">
                        <form action="{{url('import-aps')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="clo-md-6">
                                <input type="file" name="document">
                            </div>
                            <div class="clo-md-6">
                                <button class="btn btn-primary" type="submit">Importar</button>
                            </div>
                        </form>
                    </div>
            </div>
            <div class="col-md-2">
                <button class="btn btn-success">Exportar</button>
            </div>
        </div>
    </body>
</html>