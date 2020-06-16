<?php
<% with $FinalData %>

<% include DataObjectGeneratorClassHeader %>

class $Name.ShortName extends $Extends.ShortName
{

<% include DataObjectGeneratorBaseFields %>


<% include DataObjectGeneratorCMSFields %>

}

/**
 * The class below should be moved to its own file and folder and given its own namespace (src/Admin/).
 */
class MyModelAdmin extends ModelAdmin
{
    private static \$url_segment = 'mymodeladmin';

    private static \$menu_title = 'My Model Admin';

    private static \$menu_icon_class = 'font-icon-box';

    private static \$$managed_models = [
        namespace {$NameSpace}\\{$Name.ShortName},
    ];
}


<% end_with %>
