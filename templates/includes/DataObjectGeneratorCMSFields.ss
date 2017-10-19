    public function getCMSFields()
    {
        \$fields = parent::getCMSFields();
        <% if $field_labels_right %>
        //do first
        \$rightFieldDescriptions = \$this->Config()->get('field_labels_right');
        foreach(\$rightFieldDescriptions as \$field => \$desc) {
           \$field = \$fields->DataFieldByName(\$field);
           if(\$field) {
               \$field->setDescription(\$desc);
           }
        }
        <% end_if %>
        //...

        return \$fields;
    }
