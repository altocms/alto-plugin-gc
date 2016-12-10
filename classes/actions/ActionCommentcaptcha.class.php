<?php
/* ---------------------------------------------------------------------------
 * @Plugin Name: Guest Comments
 * @Plugin Id: gc
 * @Plugin URI:
 * @Description:
 * @Author: andreyv
 * @Author URI: http://gladcode.ru
 * ----------------------------------------------------------------------------
 */

class PluginGc_ActionCommentcaptcha extends Action {

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->SetDefaultEvent('index');
    }


    protected function RegisterEvent() {

        $this->AddEvent('index', 'EventIndex');
    }


    public function EventIndex() {

        /** @var ModuleCaptcha_EntityCaptcha $oCaptcha */
        $oCaptcha = E::ModuleCaptcha()->GetCaptcha();
        $oCaptcha->Display();
        exit;
    }

}

// EOF