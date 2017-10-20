
    #######################
    ### Names Section
    #######################
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
    <% end_if %>

    #######################
    ### Model Section
    #######################
    <% if $db %>
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

    private static \$many_many_extraFields = [];
    <% end_if %><% if $belongs_many_many %>
    private static \$belongs_many_many = [
        <% loop $belongs_many_many %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %>

    #######################
    ### Further DB Field Details
    #######################
    <% if $indexes %>
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
        <% loop $required_fields %>'$Key'<% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $searchable_fields %>
    private static \$searchable_fields = [
        <% loop $searchable_fields %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %>

    #######################
    ### Field Names and Presentation Section
    #######################
    <% if $field_labels %>
    private static \$field_labels = [
        <% loop $field_labels %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $field_labels_right %>
    private static \$field_labels_right = [
        <% loop $field_labels_right %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %><% if $summary_fields %>
    private static \$summary_fields = [
        <% loop $summary_fields %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% end_if %>

    #######################
    ### Casting Section
    #######################
    <% if $casting %>
    private static \$casting = [
        <% loop $casting %>'$Key' => <% if $UnquotedValue %>$UnquotedValue<% else %>'$Value'<% end_if %><% if $Last %><% else %>,
        <% end_if %><% end_loop %>
    ];
    <% loop $casting %>
        public function get{$Key}()
        {
            return DBField::create_field('$Value', 'FooBar To Be Completed');
        }
    <% end_loop %>
    <% end_if %>

    #######################
    ### can Section
    #######################
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


    #######################
    ### write Section
    #######################



    <% if $required_fields %>
    public function validate()
    {
        \$result = parent::validate();
        \$fieldLabels = \$this->FieldLabels();
        \$indexes = \$this->Config()->get('indexes');
        foreach (\$this->Config()->get('required_fields') as \$field) {
            \$value = \$this->\$field;
            if(! \$value) {
                \$fieldWithoutID = \$field;
                if(substr(\$fieldWithoutID, -2) === 'ID') {
                    \$fieldWithoutID = substr(\$fieldWithoutID, 0, -2);
                }
                \$myName = isset(\$fieldLabels[\$fieldWithoutID]) ? \$fieldLabels[\$fieldWithoutID] : \$fieldWithoutID;
                \$result->error(
                    _t(
                        '{$Name}.'.\$field.'_REQUIRED',
                        \$myName.' is required'
                    ),
                    'REQUIRED_{$Name}_'.\$field
                );
            }
            if (isset(\$indexes[\$field]) && isset(\$indexes[\$field]['type']) && \$indexes[\$field]['type'] === 'unique') {
                \$id = (empty(\$this->ID) ? 0 : \$this->ID);
                \$count = $Name::get()
                    ->filter(array(\$field => \$value))
                    ->exclude(array('ID' => \$id))
                    ->count();
                if(\$count > 0) {
                    \$myName = \$fieldLabels['\$field'];
                    \$result->error(
                        _t(
                            '{$Name}.'.\$field.'_UNIQUE',
                            \$myName.' needs to be unique'
                        ),
                        'UNIQUE_{$Name}_'.\$field
                    );
                }
            }
        }

        return \$result;
    }
    <% end_if %>
    public function onBeforeWrite()
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


    #######################
    ### Import / Export Section
    #######################

    public function getExportFields()
    {
        //..
        return parent::getExportFields();
    }
