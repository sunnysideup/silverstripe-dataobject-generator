<?php

<% with FinalData %>

class $Name extends $Extends
{

    #######################
    ### SiteTree Specific Section
    #######################
    <% if $Name %>
    private static \$icon = 'mysite/images/treeicons/$Name';
    <% end_if %><% if $description %>
    private static \$description = '$description';
    <% end_if %><% if $can_create %>
    private static \$can_create = $can_create
    <% end_if %><% if $can_be_root %>
    private static \$can_be_root = $can_be_root
    <% end_if %><% if $allowed_children %>
    private static \$allowed_children = [
        <% loop $allowed_children %>'$Key'<% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $default_child %>
    private static \$default_child = '$default_child';
    <% end_if %><% if $default_parent %>
    private static \$default_parent = '$default_parent';
    <% end_if %><% if $hide_ancestor %>
    private static \$hide_ancestor = '$hide_ancestor';
    <% end_if %>

<% include DataObjectGeneratorBaseFields %>


    #######################
    ### CMS Edit Section
    #######################

    public function CMSAddLink()
    {
        return '/admin/pages/add/';
    }

<% include DataObjectGeneratorCMSFields %>

}


class {$Name}_Controller extends {$Extends}_Controller
{

    public function init()
    {
        parent::init();
    }

    public function index(\$request)
    {
        return [];
    }

}

<% end_with %>
