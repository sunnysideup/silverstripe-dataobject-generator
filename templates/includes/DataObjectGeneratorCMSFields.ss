    public function getCMSFields()
    {
        \$fields = parent::getCMSFields();

        //do first??
        \$rightFieldDescriptions = \$this->Config()->get('field_labels_right');
        foreach(\$rightFieldDescriptions as \$field => \$desc) {
           \$formField = \$fields->DataFieldByName(\$field);
           if(! \$formField) {
            \$formField = \$fields->DataFieldByName(\$field.'ID');
           }
           if(\$formField) {
               \$formField->setDescription(\$desc);
           }
        }
        //...

        return \$fields;
    }
