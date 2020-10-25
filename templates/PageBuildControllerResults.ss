<?php

<% with $FinalData %>

<% include DataObjectGeneratorClassHeader %>

class $Name.FullName extends $Extends.ShortName
{

    #######################
    ### SiteTree Specific Section
    #######################
    <% if $Name %>
    private static \$icon = 'app/images/treeicons/$Name';
    <% end_if %><% if $description %>
    private static \$description = '$description';
    <% end_if %><% if $can_create %>
    private static \$can_create = $can_create
    <% end_if %><% if $can_be_root %>
    private static \$can_be_root = $can_be_root
    <% end_if %><% if $allowed_children %>
    private static \$allowed_children = [
        <% loop $allowed_children %>$Value.ShortName::class<% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $default_child %>
    private static \$default_child = $default_child.ShortName::class;
    <% end_if %><% if $default_parent %>
    private static \$default_parent = $default_parent.ShortName::class;
    <% end_if %><% if $hide_ancestor %>
    private static \$hide_ancestor = $hide_ancestor.ShortName::class;
    <% end_if %>

<% include DataObjectGeneratorBaseFields %>


    #######################
    ### CMS Edit Section
    #######################

<% include DataObjectGeneratorCMSFields %>

}

/**
 * The class below should be moved to its own file and folder (src/Control/).
 */

class {$Name}Controller extends {$Extends.ShortName}Controller
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
