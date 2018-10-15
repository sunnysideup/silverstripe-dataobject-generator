<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title>$Title</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <%require css('sunnysideup/dataobject-generator:css/lib/jquery-ui.min.css') %>
    <%require css('sunnysideup/dataobject-generator:css/dog.css') %>
    <%require css('sunnysideup/dataobject-generator:css/new.css') %>
</head>
<body>
    <header>
        <h1>$Title</h1>
        <a href="$PrevLink" class="clear"><i class="material-icons">clear</i><span class="text">Start over</span></a>
        <a href="/" class="home"><i class="material-icons">home</i><span class="text">Homepage</span></a>
    </header>
    $Form
    <footer><p>This tool has been provided by <a href="http://www.Sunnysideup.co.nz">sunny side up</a>. Feedback welcome.</p></footer>

    <%require javascript('sunnysideup/dataobject-generator:javascript/lib/jquery.slim.min.js') %>
    <%require javascript('sunnysideup/dataobject-generator:javascript/lib/jquery-ui.min.js') %>
    <%require javascript('sunnysideup/dataobject-generator:javascript/dog.js') %>

    <script>
        (function($){
            $(function(){
                $('select[name="Template"]').change(
                    function(e){
                        var selection = $(this).val();
                        if (typeof selection === 'string' && selection.length > 0) {
                        if (confirm('Loading a template will remove any data you have entered already.  Would you like to continue?')) {
                            // Save it!
                            var url = '$LoadTemplateLink' + selection;
                            window.location = url;
                            //$('body').fadeOut();
                        } else {
                            // Do nothing!
                        }
                    }
                });
            });
      })(window.jQuery);
    </script>
</body>
</html>
