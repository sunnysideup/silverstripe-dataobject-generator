<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title>$Title</title>

    <script src="$jQueryLink"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <style type="text/css">
html {background-color: #eee;}
body {
    font-family: sans-serif;
    color: #333;
    margin: 50px auto 500px auto;
    max-width: 600px;
    padding: 40px;
    border: 1px solid #ccc;
    border-radius: 10px;
    background-color: #fff;
}
a {color: green;}
a.remove, a.clear {color: red;}
div.add-and-remove {padding-top: 0.5em;}
a.add {float: right;}
a.add.first-add {float: left;}
a.remove {float: left;}
h2 {
    font-family: serif;
    padding-top: 1em;
    padding-bottom: 0.3em;
    margin: 0;
    clear: both;
}
div.field.dropdown,
div.field.text {
    width: calc((600px / 2) - 2em);
}
div.mykey {
    float: left;
}
div.myvalue {
    float: right;
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
select {width: calc( 100% );}

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
div.Actions input {width: 100%;}
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
.start-over {text-align: right;float: right; width: 20px; padding-top: 0; margin-top: 0}

.InnerComposite {padding: 0.5em 0;}

fieldset > h2:first-child {
    padding-top: 0;
    margin-top: 0;
}

    </style>
</head>
<body>
    <% if $PrevLink %>
    <p class="start-over"><a href="$PrevLink" class="clear"><i class="material-icons">clear</i></a></p>
    <% end_if %>

    $Form

    <script type="text/javascript">
        jQuery('document').ready(
            function() {
                jQuery('.InnerComposite').hide();
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


            }
        );
    </script>
</body>
</html>
