<?php

class backendSubscriptions extends cmsBackend {

    protected $useOptions = true;

    public $useDefaultOptionsAction = true;

    public function actionIndex(){
        $this->redirectToAction('options');
    }

}
