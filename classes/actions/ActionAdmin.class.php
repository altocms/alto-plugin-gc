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

/**
 * ActionAdmin.class.php
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 */
class PluginGc_ActionAdmin extends PluginGc_Inherits_ActionAdmin {

    /**
     * Регистрация экшенов админки
     */
    protected function RegisterEvent() {

        parent::RegisterEvent();

        $this->AddEvent('social-comment-list', 'EventAdminSocialCommentList');
        $this->AddEvent('social-comment-delete', 'EventAdminSocialCommentDelete');

    }


    protected function EventComments() {

        $this->sMainMenuItem = 'content';

        $this->_setTitle(E::Module('Lang')->Get('action.admin.comments_title'));
        $this->SetTemplateAction('content/comments_list');

        $sCmd = $this->GetPost('cmd');
        if ($sCmd == 'delete') {
            $this->_commentDelete();
        }

        // * Передан ли номер страницы
        $nPage = $this->_getPageNum();

        $aResult = E::Module('Comment')->GetCommentsByFilter(array(), array('comment_delete' => 'asc'), $nPage, Config::Get('admin.items_per_page'));
        $aPaging = E::Module('Viewer')->MakePaging($aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4,
            Router::GetPath('admin') . 'content-comments/');

        E::Module('Viewer')->Assign('aComments', $aResult['collection']);
        E::Module('Viewer')->Assign('aPaging', $aPaging);
    }


    protected function EventAdminSocialCommentDelete() {

        E::Module('Viewer')->SetResponseAjax('json');

        if ($sCommentId = getRequest('comment_id', FALSE)) {
            // * Комментарий существует?

            if (!($oComment = E::Module('Comment')->GetCommentById($sCommentId))) {
                E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('system_error'), E::Module('Lang')->Get('error'));
                return;
            }
            // * Есть права на удаление комментария?
            if (!$oComment->isDeletable()) {
                E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('not_access'), E::Module('Lang')->Get('error'));
                return;
            }
            // * Устанавливаем пометку о том, что комментарий удален
            $oComment->setDelete(($oComment->getDelete() + 1) % 2);

            $this->Hook_Run('comment_delete_before', array('oComment' => $oComment));

            if (!E::Module('Comment')->UpdateCommentStatus($oComment)) {
                E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('system_error'), E::Module('Lang')->Get('error'));
                return;
            }

            $this->Hook_Run('comment_delete_after', array('oComment' => $oComment));

            // * Формируем текст ответа
            if ($bState = (bool)$oComment->getDelete()) {
                $sMsg = E::Module('Lang')->Get('comment_delete_ok');
            } else {
                $sMsg = E::Module('Lang')->Get('comment_repair_ok');
            }

            // * Показываем сообщение и передаем переменные в ajax ответ
            E::Module('Message')->AddNoticeSingle($sMsg, E::Module('Lang')->Get('attention'));

        }

    }

    protected function _setGuestcommentRequestByArray($aData) {

        foreach ($aData as $k => $v) {
            $_REQUEST['guest_comment_' . $k] = $v;
        }
    }

    /**
     * Страница настроек плагина
     *
     * @return bool
     */
    protected function EventAdminSocialCommentList() {

        E::Module('Viewer')->Assign('sPageTitle', E::Module('Lang')->Get('plugin.gc.admin_social_page_title'));
        E::Module('Viewer')->Assign('sMainMenuItem', 'content');
        E::Module('Viewer')->AddHtmlTitle(E::Module('Lang')->Get('plugin.gc.admin_social_page_title'));
        $this->SetTemplateAction('content/social_comment_list');

        /** @var ModuleUser_EntityUser $oGuestUser */
        $oGuestUser = E::Module('User')->GetUserByLogin(Config::Get('plugin.gc.guest_login'));

        if (getRequest('submit_social')) {

            // Проверяем email
            if (($sEmail = getRequestStr('admin_social_email')) && F::CheckVal($sEmail, 'mail') && (!E::Module('User')->GetUserByMail($sEmail) || $sEmail == $oGuestUser->getMail())) {

                // Проверяем режим работы плагина
                if (!in_array(getRequest('guest_comment_mode'), array('social', 'mail', 'both'))) {
                    E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('admin_error_wrong_mode'), E::Module('Lang')->Get('error'));
                }

                $aData = array();
                foreach ($_POST as $k => $v) {
                    if (mb_strpos($k, 'guest_comment_') === 0) {

                        // Провайдеры
                        if (is_array($v) && str_replace('guest_comment_', '', $k) == 'providers') {
                            foreach ($v as $sProviderName => $aProviderData) {
                                foreach ($aProviderData as $pdKey => $pdVal) {
                                    $aData["plugin.gc.providers.{$sProviderName}.{$pdKey}"] = $pdVal;
                                }
                            }
                            continue;
                        }

                        // Другое
                        $aData['plugin.gc.' . str_replace('guest_comment_', '', $k)] = $v;
                    }
                }

                Config::WriteCustomConfig($aData);
                $this->_setGuestcommentRequestByArray($aData);

                $oGuestUser->setMail($sEmail);
                E::Module('User')->Update($oGuestUser);

                return FALSE;

            } else {
                E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.gc.admin_error_wrong_email'), E::Module('Lang')->Get('error'));
            }

        }

        $this->_setGuestcommentRequestByArray(Config::Get('plugin.gc'));

        if ($oGuestUser) {
            $_REQUEST['admin_social_email'] = $oGuestUser->getMail();
        }


        return FALSE;
    }

}

// EOF