<?php

/**
 * HookGc.class.php
 * Файл хука плагина Gc
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Gc
 * @version     0.0.1 от 03.09.2014 10:47
 */
class PluginGc_HookGc extends Hook {
    /**
     * Регистрация хуков
     */
    public function RegisterHook() {
        // Устанавливает разрешение на гостевое комментирование топика
        if (!E::IsUser()) {
            /** @var string $sPluginMode Режим работы плагина */
            $sPluginMode = Config::Get('plugin.gc.mode');

            // Комменты через социальные сети
            if (in_array($sPluginMode, array('social', 'both'))) {
                $this->AddHook('template_form_add_comment_begin', 'TemplateHookAddSocial');
                $this->AddHook('template_comment_social_inject', 'TemplateAddSocialIcons');
            }

            // Комменты через почту
            if (in_array($sPluginMode, array('mail', 'both'))) {
                $this->AddHook('template_form_add_comment_end', 'TemplateHookAddMailForm');
            }

        }


        if (E::IsAdmin()) {
            $this->AddHook('template_admin_menu_content', 'AdminMenuInject', __CLASS__);
        }

    }

    /**
     * Доабвление ссылки в меню админки
     *
     * @return string
     */
    public function AdminMenuInject() {

        return $this->Viewer_Fetch(Plugin::GetTemplatePath('gc') . '/tpls/injects/inject.admin.menu.tpl');

    }


    /**
     * Возвращает HTML со списком провайдеров
     * @return string
     */
    private function GetSocialIcons() {
        /** @var ModuleViewer $oLocalViewer */
        $oLocalViewer = $this->Viewer_GetLocalViewer();

        $sMenu = '';
        foreach (Config::Get('plugin.gc.providers') as $sProviderName => $aProviderData) {
            /** @var AuthProvider $oProvider */
            $oProvider = $this->PluginGc_CommentProvider_GetProviderByName($sProviderName);
            if ($oProvider) {
                $oLocalViewer->Assign('sAuthUrl', $oProvider->sAuthUrl);
                $oLocalViewer->Assign('sProviderName', $sProviderName);
                $sMenu .= $oLocalViewer->Fetch(Plugin::GetTemplatePath('gc') . '/tpls/injects/inject.social.buttons.tpl');
            }
        }


        return $sMenu;
    }

    /**
     * Добавляет иконки соцюсетей на страницу профиля
     *
     * @return string
     */
    public function TemplateAddSocialIcons() {

        $this->Session_Set('return_path', Router::GetPathWebCurrent());

        return
            $this->Lang_Get('plugin.gc.auth_by_social' . ($this->PluginGc_CommentProvider_ValidateCommentRight() ? 'good' : '')) .
            '<ul class="settings-social">' . $this->GetSocialIcons() . '</ul>';
    }

    public function TemplateHookAddMailForm() {

        /** @var ModuleViewer $oLocalViewer */
        $oLocalViewer = $this->Viewer_GetLocalViewer();

        if ($this->PluginGc_CommentProvider_ValidateCommentRight()) {
            $oLocalViewer->Assign('right_comment', TRUE);
        }

        return $oLocalViewer->Fetch(Plugin::GetTemplatePath('gc') . '/tpls/injects/inject.guest-form.tpl');
    }

    public function TemplateHookAddSocial() {

        /** @var ModuleViewer $oLocalViewer */
        $oLocalViewer = $this->Viewer_GetLocalViewer();


        return $oLocalViewer->Fetch(Plugin::GetTemplatePath('gc') . '/tpls/injects/inject.social.tpl');
    }

}
