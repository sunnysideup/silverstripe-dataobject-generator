<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title>$Title</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <%require css('sunnysideup/dataobject-generator:css/lib/jquery-ui.min.css') %>
    <%require css('sunnysideup/dataobject-generator:css/dog.css') %>
</head>
<body>
    <header>
        <p class="start-over"><a href="$PrevLink" class="clear"><i class="material-icons">clear</i></a></p>
        <p class="back-home"><a href="/" class="back"><i class="material-icons">home</i></a></p>
        <h1>$Title</h1>
    </header>
    $Form
    <footer><p>This tool has been provided by <a href="http://www.Sunnysideup.co.nz">sunny side up</a>. Feedback welcome.</p></footer>

    <%require javascript('sunnysideup/dataobject-generator:javascript/lib/jquery.slim.min.js') %>
    <%require javascript('sunnysideup/dataobject-generator:javascript/lib/jquery-ui.min.js') %>
    <%require javascript('sunnysideup/dataobject-generator:javascript/dog.js') %>
</body>
</html>
