<?php

/**
 * HookGc.class.php
 * Файл хука плагина Gc
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Gc
 */
class PluginGc_HookGc extends Hook {

    /**
     * Регистрация хуков
     */
    public function RegisterHook() {

        // Устанавливает разрешение на гостевое комментирование топика
        if (!E::IsUser()) {
            /** @var string $sPluginMode Режим работы плагина */
            $sPluginMode = C::Get('plugin.gc.mode');

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
            $this->AddHook('template_admin_menu_settings', 'AdminMenuInject', __CLASS__);
        }
    }

    /**
     * Доабвление ссылки в меню админки
     *
     * @return string
     */
    public function AdminMenuInject() {

        return E::Module('Viewer')->Fetch(Plugin::GetTemplatePath('gc') . '/tpls/injects/inject.admin.menu.tpl');
    }


    /**
     * Возвращает HTML со списком провайдеров
     * @return string
     */
    private function GetSocialIcons() {

        $sMenu = '';
        foreach (C::Get('plugin.gc.providers') as $sProviderName => $aProviderData) {
            /** @var AuthProvider $oProvider */
            $oProvider = E::Module('PluginGc\CommentProvider')->GetProviderByName($sProviderName);
            if ($oProvider) {
                E::Module('Viewer')->Assign('sAuthUrl', $oProvider->sAuthUrl);
                E::Module('Viewer')->Assign('sProviderName', $sProviderName);
                $sMenu .= E::Module('Viewer')->Fetch(Plugin::GetTemplatePath('gc') . '/tpls/injects/inject.social.buttons.tpl');
            }
        }
        return $sMenu;
    }

    /**
     * Добавляет иконки соцсетей на страницу профиля
     *
     * @return string
     */
    public function TemplateAddSocialIcons() {

        E::Module('Session')->Set('return_path', Router::GetPathWebCurrent());

        return
            E::Module('Lang')->Get('plugin.gc.auth_by_social' . (E::Module('PluginGc\CommentProvider')->ValidateCommentRight() ? 'good' : '')) .
            '<ul class="settings-social">' . $this->GetSocialIcons() . '</ul>';
    }


    public function TemplateHookAddMailForm() {

        if (E::Module('PluginGc\CommentProvider')->ValidateCommentRight()) {
            E::Module('Viewer')->Assign('right_comment', TRUE);
        }

        return E::Module('Viewer')->Fetch(Plugin::GetTemplatePath('gc') . '/tpls/injects/inject.guest-form.tpl');
    }


    public function TemplateHookAddSocial() {

        return E::Module('Viewer')->Fetch(Plugin::GetTemplatePath('gc') . '/tpls/injects/inject.social.tpl');
    }

}

// EOF