<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title>$Title</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <style type="text/css">
html {
    background-color: #eee;
}
body {
    font-family: sans-serif;
    color: #333;
    margin: 50px auto 500px auto;
    max-width: 600px;
    padding: 40px;
    border: 1px solid #FFCC1A;
    border-radius: 10px;
    background-color: #fff;
}

h1, h2, h3, h4 {color: #838080; clear: both; }
form { position: relative;}
a {color: green;}
header {}
header  p.back-home {margin-top: -20px; float: right; margin-right: 20px;}
header  p.start-over {margin-top: -20px; float: right;}
header  h1 {
    border-bottom: 1px solid #FFCC1A;
    background-color: #efefef;
    text-align: center;
    padding: 0.9em 0.2em 0.2em 0.2em;
    border-radius: 3px;
    font-size: 23px;
    margin-bottom: 2em;
}
a.remove, a.clear {color: red;}
div.add-and-remove {padding-top: 0.5em;}
a.add {float: right;}
a.add.first-add,
a.remove {
    float: left;
    margin-top: -2.2em;
    margin-left: -1.7em;
}
h2 {
    padding-top: 1.7em;
    padding-bottom: 0.3em;
    margin: 0;
    clear: both;
    font-size: 16px;
}
div.field.dropdown,
div.field.text {
    width: calc((600px / 2) - 2em);
}

input, select {
    width: calc( 100% - 2px - 1em);
    color: #555;
    padding: 0.5em;
    border-radius: 5px;
    border: 1px solid #ccc;
    display: block;
    margin: 0 0.5em 0 0;
    font-family: monospace;
}
select {width: calc( 100% );background-color: #efefef;}

input:focus,
select:focus {
    border-color: green;
    background-color: green;
    color: #fff;
    outline: 0;
}

div.CompositeField {
    clear: both;
}
div.Actions {
    padding-top: 2em;
}
div.Actions input {width: 100%; font-weight: bold;}
fieldset, form {
    outline: 0;
    border: 0;
    padding: 0;
    margin: 0;
}
fieldset {
    outline: 0;
    border: 0;
}

.InnerComposite {padding: 0.5em 0;}

fieldset > h2:first-child {
    padding-top: 0;
    margin-top: 0;
}
footer {
    clear: both;
    padding-top: 3em;
}
footer p  {
    text-align: center;
    padding: 0;
    margin: 0;
    color: #777;

}
footer p a {
    border-bottom: 1px solid #FFCC1A;
    text-decoration: none;
    color: #777;
}
@media only screen and (min-width: 700px) {
    div.myvalue:before {
        content: '=> ';
        font-family: monospace;
        color: #777;
        float: left;
        margin-left: -28px;
        margin-top: 0.5em;
    }
    div.mykey {
        float: left;
    }
    div.myvalue {
        float: right;
    }
}

    </style>
</head>
<body>
    <header>
        <p class="start-over"><a href="$PrevLink" class="clear"><i class="material-icons">clear</i></a></p>
        <p class="back-home"><a href="/" class="back"><i class="material-icons">home</i></a></p>
        <h1>$Title</h1>
    </header>
    $Form
    <footer><p>This tool has been provided by <a href="http://www.Sunnysideup.co.nz">sunny side up</a>. Feedback welcome.</p></footer>

    <script
        src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
        crossorigin="anonymous">
    </script>

    <script type="text/javascript">
        jQuery('document').ready(
            function() {
                jQuery('.InnerComposite')
                    .hide()
                    .each(
                        function(i, el) {
                            var show = false;
                            var keyInput = jQuery(el).find('div.mykey select, input').first();
                            var valInput = jQuery(el).find('div.myvalue select, input').first();
                            if(keyInput && keyInput.length > 0 && valInput && valInput.length > 0) {
                                var keyVal = jQuery(el).find('div.mykey select, input').first().val();
                                var valVal = jQuery(el).find('div.myvalue select, input').first().val();
                                if(keyVal.length > 0 && valVal.length > 0) {
                                    show = true;
                                }
                            }
                            else if(keyInput && keyInput.length > 0) {
                                var keyVal = jQuery(el).find('div.mykey select, input').first().val();
                                if(keyVal.length) {
                                    show = true;
                                }
                            }
                            if(show) {
                                jQuery(el).show();
                            }
                        }

                    );

                jQuery('.add-and-remove .add')
                    .show()
                    .on(
                        'click',
                        function(event){
                            event.preventDefault();
                            var outerEl = jQuery(this);
                            var outerDiv = outerEl.closest('.OuterComposite');
                            var todo = true;
                            jQuery(outerDiv)
                                .find('div.InnerComposite')
                                .each(
                                    function(i, innerEl) {
                                        if(todo === true) {
                                            var innerEl = jQuery(innerEl);
                                            if(innerEl.is(':hidden')) {
                                                innerEl.show();
                                                todo = false;
                                                if(innerEl.hasClass('pos13')) {
                                                    jQuery(outerEl).hide();
                                                }
                                                outerDiv.find('.add').removeClass('first-add');
                                            }
                                        }
                                    }
                                );
                            outerDiv.find('.remove').show();
                            return false;
                        }
                    );
                jQuery('.add-and-remove .remove')
                    .hide()
                    .on(
                        'click',
                        function(event){
                            event.preventDefault();
                            var outerEl = jQuery(this);
                            var outerDiv = outerEl.closest('.OuterComposite');
                            var todo = true;
                            var myHiddenDiv = jQuery(outerDiv)
                                .find('div.InnerComposite:visible')
                                .last();
                            myHiddenDiv.hide();
                            myHiddenDiv.find('input, select').each(
                                function(i, formEl) {
                                    jQuery(formEl).val('');
                                }
                            );
                            if(myHiddenDiv.hasClass('pos2')) {
                                jQuery(outerEl).hide();
                                outerDiv.find('.add').addClass('first-add');
                            } else {
                                outerDiv.find('.add').removeClass('first-add');
                            }
                            outerDiv.find('.add').show();
                            return false;
                        }
                    );
                jQuery('select[name="Template"]').on(
                    'change',
                    function(event) {
                        var selection = jQuery(this).val();
                        if(typeof selection === 'string' && selection.length > 0) {
                            if (confirm('Loading a template will remove any data you have entered already.  Would you like to continue?')) {
                                // Save it!
                                var url = '$LoadTemplateLink' + selection;
                                window.location = url;
                                jQuery('body').fadeOut();
                            } else {
                                // Do nothing!
                            }
                        }
                    }
                );

            }
        );
    </script>
</body>
</html>
