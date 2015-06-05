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

        // version_compare('1.0.2', '1.1.0', '>=') = false
        if (version_compare(ALTO_VERSION, '1.1.0-alpha', '<=')) {
            if (!class_exists('KCAPTCHA', false)) {
                F::IncludeLib('kcaptcha/kcaptcha.php');
            }
            $oCaptcha = new KCAPTCHA();
            $this->Session_Set('comment_captcha_keystring', $oCaptcha->getKeyString());
        } else {
            /** @var ModuleCaptcha_EntityCaptcha $oCaptcha */
            $oCaptcha = E::ModuleCaptcha()->GetCaptcha();
            $oCaptcha->Display();
        }
        exit;
    }
}

// EOF