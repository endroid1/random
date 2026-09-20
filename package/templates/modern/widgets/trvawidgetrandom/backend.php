<?php
class widgettrvawidgetrandomBackend extends cmsWidget {

    public function getOptionsForm() {
        $this->addJS('templates/modern/widgets/trvawidgetrandom/admin.js');
        return parent::getOptionsForm();
    }

}