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

        if (!class_exists('KCAPTCHA', false)) {
            F::IncludeLib('kcaptcha/kcaptcha.php');
        }
        $oCaptcha = new KCAPTCHA();
        $this->Session_Set('comment_captcha_keystring', $oCaptcha->getKeyString());
        exit;
    }
}

// EOF