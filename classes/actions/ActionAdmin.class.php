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
class PluginGc_ActionAdmin extends PluginGc_Inherit_ActionAdmin {

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

        $this->_setTitle($this->Lang_Get('action.admin.comments_title'));
        $this->SetTemplateAction('content/comments_list');

        $sCmd = $this->GetPost('cmd');
        if ($sCmd == 'delete') {
            $this->_commentDelete();
        }

        // * Передан ли номер страницы
        $nPage = $this->_getPageNum();

        $aResult = $this->Comment_GetCommentsByFilter(array(), array('comment_delete' => 'asc'), $nPage, Config::Get('admin.items_per_page'));
        $aPaging = $this->Viewer_MakePaging($aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4,
            Router::GetPath('admin') . 'content-comments/');

        $this->Viewer_Assign('aComments', $aResult['collection']);
        $this->Viewer_Assign('aPaging', $aPaging);
    }


    protected function EventAdminSocialCommentDelete() {

        $this->Viewer_SetResponseAjax('json');

        if ($sCommentId = getRequest('comment_id', FALSE)) {
            // * Комментарий существует?

            if (!($oComment = $this->Comment_GetCommentById($sCommentId))) {
                $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                return;
            }
            // * Есть права на удаление комментария?
            if (!$oComment->isDeletable()) {
                $this->Message_AddErrorSingle($this->Lang_Get('not_access'), $this->Lang_Get('error'));
                return;
            }
            // * Устанавливаем пометку о том, что комментарий удален
            $oComment->setDelete(($oComment->getDelete() + 1) % 2);

            $this->Hook_Run('comment_delete_before', array('oComment' => $oComment));

            if (!$this->Comment_UpdateCommentStatus($oComment)) {
                $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                return;
            }

            $this->Hook_Run('comment_delete_after', array('oComment' => $oComment));

            // * Формируем текст ответа
            if ($bState = (bool)$oComment->getDelete()) {
                $sMsg = $this->Lang_Get('comment_delete_ok');
            } else {
                $sMsg = $this->Lang_Get('comment_repair_ok');
            }

            // * Показываем сообщение и передаем переменные в ajax ответ
            $this->Message_AddNoticeSingle($sMsg, $this->Lang_Get('attention'));

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

        $this->Viewer_Assign('sPageTitle', $this->Lang_Get('plugin.gc.admin_social_page_title'));
        $this->Viewer_Assign('sMainMenuItem', 'content');
        $this->Viewer_AddHtmlTitle($this->Lang_Get('plugin.gc.admin_social_page_title'));
        $this->SetTemplateAction('content/social_comment_list');

        /** @var ModuleUser_EntityUser $oGuestUser */
        $oGuestUser = $this->User_GetUserByLogin(Config::Get('plugin.gc.guest_login'));

        if (getRequest('submit_social')) {

            // Проверяем email
            if (($sEmail = getRequestStr('admin_social_email')) && F::CheckVal($sEmail, 'mail') && (!$this->User_GetUserByMail($sEmail) || $sEmail == $oGuestUser->getMail())) {

                // Проверяем режим работы плагина
                if (!in_array(getRequest('guest_comment_mode'), array('social', 'mail', 'both'))) {
                    $this->Message_AddErrorSingle($this->Lang_Get('admin_error_wrong_mode'), $this->Lang_Get('error'));
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
                $this->User_Update($oGuestUser);

                return FALSE;

            } else {
                $this->Message_AddErrorSingle($this->Lang_Get('plugin.gc.admin_error_wrong_email'), $this->Lang_Get('error'));
            }

        }

        $this->_setGuestcommentRequestByArray(Config::Get('plugin.gc'));

        if ($oGuestUser) {
            $_REQUEST['admin_social_email'] = $oGuestUser->getMail();
        }


        return FALSE;
    }


}