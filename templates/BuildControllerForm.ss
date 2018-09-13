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
        (function($){
            const css = `
            div.field.dropdown, div.field.text { width: calc(50% - 3em); }
            div.mykey { float: none; }
            div.myvalue { float: none; }
            div.myvalue { margin-left: 40px; }
            .btn-remove { margin-left: 1em; }
            .btn-add, .btn-remove { cursor: pointer; height: 2.5em; color: #fff; text-shadow: 1px 1px rgba(0,0,0,.5); border-width: 1px; border-style: solid; border-radius: 4px; box-shadow: 1px 1px 2px rgba(255,255,255,.4) inset, -1px -1px 2px rgba(0,0,0,.3) inset; }
            .btn-add > .material-icons, .btn-remove > .material-icons { vertical-align: middle; }
            .btn-add { background: #3c6; border-color: #afc #063 #063 #afc; }
            .btn-remove { background: #f66; border-color: #faa #633 #633 #faa; }
            .InnerComposite { display: none; }
            .InnerComposite.active { display: flex; }
            `;

            $(function(){
                $('<style>', { text: css }).appendTo(document.head);

                $('.InnerComposite').filter(function(){
                    let hasAny = false;
                    $(this).find('.mykey:input, .myvalue:input').each(function(){
                        hasAny |= !!$(this).val();
                    });
                    return hasAny;
                }).addClass('active');

                function showHideAddButton(outer){
                    let remaining = $(outer).find('.InnerComposite:not(.active):first');
                    $(outer).find('.btn-add').toggle(remaining.length > 0);
                };

                $('.OuterComposite.multiple').each(function(){
                    $(this).children('.InnerComposite').each(function(i){
                        $('<button type="button" class="btn-remove" title="Remove"><i class="material-icons">remove_circle_outline</i></button>')
                            .appendTo(this)
                            .click(
                                i,
                                function(e){
                                    // TODO: use front-end framework e.g. React, Angular, Vue, Ember
                                    let pos = e.data;
                                    let me = $(this).closest('.InnerComposite');
                                    let parent = me.closest('.OuterComposite');
                                    let children = parent.find('.InnerComposite');
                                    //console.log(pos, me, parent);
                                    let array = [];
                                    children.each(function(){
                                        if ($(this).hasClass('active')) {
                                            array.push({ key: $(this).find('.mykey:input').val(), value: $(this).find('.myvalue:input').val() });
                                        }
                                    });
                                    array.splice(pos, 1);
                                    array.forEach(function(e, i){
                                        let c = children.eq(i);
                                        c.find('.mykey:input').val(e.key);
                                        c.find('.myvalue:input').val(e.value);
                                    });
                                    //console.log(array);
                                    for (let i = array.length, len = children.length; i < len; i++) {
                                        let c = children.eq(i);
                                        c.find('.mykey:input').val('');
                                        c.find('.myvalue:input').val('');
                                        c.removeClass('active');
                                    }
                                    showHideAddButton(parent);
                                });
                        });
                });

                $('.OuterComposite.multiple').each(function(){
                    $('<button type="button" class="btn-add" title="Add"><i class="material-icons">add_circle_outline</i></button>')
                        .appendTo(this)
                        .click(
                            this,
                            function(e){
                                $(e.data).find('.InnerComposite:not(.active):first').addClass('active');
                                showHideAddButton(e.data);
                            });
                });

                $('select[name="Template"]').change(
                    function(e){
                        var selection = $(this).val();
                        if (typeof selection === 'string' && selection.length > 0) {
                            if (confirm('Loading a template will remove any data you have entered already.  Would you like to continue?')) {
                                // Save it!
                                var url = '$LoadTemplateLink' + selection;
                                window.location = url;
                                $('body').fadeOut();
                            } else {
                                // Do nothing!
                            }
                        }
                    });
            });
        })(jQuery);
    </script>
</body>
</html>
