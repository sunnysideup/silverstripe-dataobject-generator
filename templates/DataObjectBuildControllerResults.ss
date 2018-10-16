<?php
<% with $FinalData %>

<% include DataObjectGeneratorClassHeader %>

class $Name extends $Extends.ShortName
{

<% include DataObjectGeneratorBaseFields %>


    #######################
    ### CMS Edit Section
    #######################

    <% if $ModelAdmin %>
    public function CMSEditLink()
    {
        \$controller = singleton($ModelAdmin::class);

        return \$controller->Link().\$this->ClassName."/EditForm/field/".\$this->ClassName."/item/".\$this->ID."/edit";
    }

    public function CMSAddLink()
    {
        \$controller = singleton($ModelAdmin::class);

        return \$controller->Link().\$this->ClassName."/EditForm/field/".\$this->ClassName."/item/new";
    }
    <% end_if %>

<% include DataObjectGeneratorCMSFields %>

}


<% end_with %>
