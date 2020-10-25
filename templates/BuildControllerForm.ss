<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title>$Title</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="$ResolveResource(client/css/lib/jquery-ui.min.css)" />
    <link rel="stylesheet" href="$ResolveResource(client/css/dog.css)" />
    <link rel="stylesheet" href="$ResolveResource(client/css/new.css)" />
</head>
<body>
    <header>
        <h1>$Title</h1>
        <a href="$PrevLink" class="clear"><i class="material-icons">backspace</i><span class="text">Start over</span></a>
        <a href="/" class="home"><i class="material-icons">home</i><span class="text">Homepage</span></a>
    </header>
    $Form
    <footer><p>This tool has been provided by <a href="http://www.Sunnysideup.co.nz">sunny side up</a>. Feedback welcome.</p></footer>

    <script src="$ResolveResource(client/javascript/lib/jquery.slim.min.js)"></script>
    <script src="$ResolveResource(client/javascript/lib/jquery-ui.min.js)"></script>
    <script src="$ResolveResource(client/javascript/dog.js)"></script>

    <script>
(
  function ($) {
    $(
      function () {
        $('select[name="Template"]').change(
          function (e) {
            var selection = $(this).val()
            if (typeof selection === 'string' && selection.length > 0) {
              if (window.confirm('Loading a template will remove any data you have entered already.  Would you like to continue?')) {
                // Save it!
                var url = '$LoadTemplateLink' + selection
                window.location = url
                // $('body').fadeOut();
              }
            }
          }
        )
      }
    )
  }
)(window.jQuery)

    </script>
</body>
</html>
