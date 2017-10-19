<?php

<% with $FinalData %>

class $Name extends $Extends
{

<% include DataObjectGeneratorBaseFields %>


    #######################
    ### CMS Edit Section
    #######################

    <% if $ModelAdmin %>
    public function CMSEditLink()
    {
        \$controller = singleton("$ModelAdmin");

        return \$controller->Link().\$this->ClassName."/EditForm/field/".\$this->ClassName."/item/".\$this->ID."/edit";
    }

    public function CMSAddLink()
    {
        \$controller = singleton("$ModelAdmin");

        return \$controller->Link().\$this->ClassName."/EditForm/field/".\$this->ClassName."/item/new";
    }
    <% end_if %>

<% include DataObjectGeneratorCMSFields %>

}


<% end_with %>
