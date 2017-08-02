<?php

<% with FinalData %>

class $Name extends $Extends {
    <% if $singular_name %>
    private static \$singular_name = '$singular_name';

    function i18n_singular_name()
    {
        return _t('{$Name}.SINGULAR_NAME', '$singular_name');
    }
    <% end_if %><% if $plural_name %>
    private static \$plural_name = '$plural_name';

    function i18n_plural_name()
    {
        return _t('{$Name}.PLURAL_NAME', '$plural_name');
    }
    <% end_if %><% if $db %>
    private static \$db = [
        <% loop $db %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $belongs_to %>
    private static \$belongs_to = [
        <% loop $belongs_to %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $has_one %>
    private static \$has_one = [
        <% loop $has_one %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $has_many %>
    private static \$has_many = [
        <% loop $has_many %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $many_many %>
    private static \$many_many = [
        <% loop $many_many %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $belongs_many_many %>
    private static \$belongs_many_many = [
        <% loop $belongs_many_many %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $indexes %>
    private static \$indexes = [
        <% loop $indexes %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $default_sort %>
    private static \$default_sort = [
        <% loop $default_sort %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $required_fields %>
    private static \$required_fields = [
        <% loop $required_fields %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $summary_fields %>
    private static \$summary_fields = [
        <% loop $summary_fields %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $field_labels %>
    private static \$field_labels = [
        <% loop $field_labels %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
<% end_if %><% if $field_labels_right %>
    private static \$field_labels_right = [
        <% loop $field_labels_right %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $searchable_fields %>
    private static \$searchable_fields = [
        <% loop $searchable_fields %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $casting %>
    private static \$casting = [
        <% loop $casting %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $ModelAdmin %>
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

    public function getCMSFields()
    {
        \$fields = parent::getCMSFields();
        <% if $field_labels_right %>
        //do first
        \$rightFieldDescriptions = \$this->Config()->get('field_labels_right');
        foreach(\$rightFieldDescriptions as \$field => \$desc) {
            \$field = \$fields->getDataFieldByName(\$field);
            if(\$field) {
                \$field->setDescription(\$desc);
            }
        }
        <% end_if %>
        //...

        return \$fields;
    }

    public function getExportFields()
    {
        //..
        return parent::getExportFields();
    }

    <% if $canCreate %>
    function canCreate(\$member = null)
    {
        return $canCreate.RAW
    }
    <% end_if %><% if $canView %>
    function canView(\$member = null)
    {
        return $canView.RAW
    }
    <% end_if %><% if $canEdit %>
    function canEdit(\$member = null)
    {
        return $canEdit.RAW
    }
    <% end_if %><% if $canDelete %>
    function canDelete(\$member = null)
    {
        return $canDelete.RAW
    }
    <% end_if %>
    public function onBeforWrite()
    {
        parent::onBeforeWrite();
        //...
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        //...
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        //...
    }
    <% if $casting %><% loop $casting %>
    public function get{$Key}()
    {
        return DBField::create_field('$Value', 'FooBar To Be Completed');
    }
    ><% end_loop %><% end_if %>
    <% if $required_fields %>
    protected function validate()
    {
        \$result = parent::validate();
        \$fieldLabels = \$this->FieldLabels();
        foreach(\$this->Config()->get('required_fields') as \$field => \$type) {
            \$value = \$this->\$field;
            if(! \$value)
                \$myName = \$fieldLabels['\$field'];
                \$result->error(
                    _t(
                        '{$Up.Name}.'.\$field.'_REQUIRED',
                        \$myName.' is required'
                    ),
                    'REQUIRED_{$Up.Name}_'.\$field
                );
            }
            if(\$type === 'unique') {
                \$id = (empty(\$this->ID) ? 0 : \$this->ID);
                \$count = $Name::get()
                    ->filter(array(\$field => \$value))
                    ->exclude(array('ID' => \$id))
                    ->count();
                if(\$count > 0) {
                    \$myName = \$fieldLabels['\$field'];
                    \$result->error(
                        _t(
                            '{$Up.Name}.'.\$field.'_UNIQUE',
                            \$myName.' needs to be unique'
                        ),
                        'UNIQUE_{$Up.Name}_'.\$field
                    );
                }
            }
        }

        return \$result;
    }
    <% end_if %>
}


<% end_with %>
