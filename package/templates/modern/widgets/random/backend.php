<?php
class widgetRandomBackend extends cmsWidget {

    public function getOptionsForm() {
        $this->addJS($this->getJavascriptFileName('admin'));
        return parent::getOptionsForm();
    }

}