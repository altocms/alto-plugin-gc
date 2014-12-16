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

class PluginGc_ActionBlog extends PluginGc_Inherit_ActionBlog {

    /**
     * Получение новых комментариев
     *
     */
    protected function AjaxResponseComment() {

        // если пользовтаель авторизован, то работаем стандартно по родителю
        if (E::IsUser()) {
            parent::AjaxResponseComment();

            return;
        }

        // ЗДЕСЬ ПОЛЬЗОВАТЕЛЬ НЕ АВТОРИЗОВАН
        /** @var ModuleUser_EntityUser $oGuestUser */
        $oGuestUser = $this->User_GetUserByLogin(Config::Get('plugin.gc.guest_login'));
        if (!$oGuestUser) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));

            return;
        }

        // * Устанавливаем формат Ajax ответа
        $this->Viewer_SetResponseAjax('json');

//        // * Пользователь авторизован?
//        if (!$this->oUserCurrent) {
//            $this->Message_AddErrorSingle($this->Lang_Get('need_authorization'), $this->Lang_Get('error'));
//            return;
//        }

        // * Топик существует?
        $iTopicId = intval(F::GetRequestStr('idTarget', NULL, 'post'));
        if (!$iTopicId || !($oTopic = $this->Topic_GetTopicById($iTopicId))) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));

            return;
        }

        // * Есть доступ к комментариям этого топика? Закрытый блог?
        if (!$this->ACL_IsAllowShowBlog($oTopic->getBlog(), $oGuestUser)) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));

            return;
        }

        $idCommentLast = F::GetRequestStr('idCommentLast', NULL, 'post');
        $selfIdComment = F::GetRequestStr('selfIdComment', NULL, 'post');
        $aComments = array();

        // * Если используется постраничность, возвращаем только добавленный комментарий
        if (F::GetRequest('bUsePaging', NULL, 'post') && $selfIdComment) {
            $oComment = $this->Comment_GetCommentById($selfIdComment);
            if ($oComment && ($oComment->getTargetId() == $oTopic->getId())
                && ($oComment->getTargetType() == 'topic')
            ) {
                $oViewerLocal = $this->Viewer_GetLocalViewer();
                $oViewerLocal->Assign('oUserCurrent', $oGuestUser);
                $oViewerLocal->Assign('bOneComment', TRUE);

                $oViewerLocal->Assign('oComment', $oComment);
                $sText = $oViewerLocal->Fetch($this->Comment_GetTemplateCommentByTarget($oTopic->getId(), 'topic'));
                $aCmt = array();
                $aCmt[] = array(
                    'html' => $sText,
                    'obj'  => $oComment,
                );
            } else {
                $aCmt = array();
            }
            $aReturn['comments'] = $aCmt;
            $aReturn['iMaxIdComment'] = $selfIdComment;
        } else {
            $aReturn = $this->Comment_GetCommentsNewByTargetId($oTopic->getId(), 'topic', $idCommentLast);
        }
        $iMaxIdComment = $aReturn['iMaxIdComment'];

//        $oTopicRead = Engine::GetEntity('Topic_TopicRead');
//        $oTopicRead->setTopicId($oTopic->getId());
//        $oTopicRead->setUserId($this->oUserCurrent->getId());
//        $oTopicRead->setCommentCountLast($oTopic->getCountComment());
//        $oTopicRead->setCommentIdLast($iMaxIdComment);
//        $oTopicRead->setDateRead(F::Now());
//        $this->Topic_SetTopicRead($oTopicRead);

        $aCmts = $aReturn['comments'];
        if ($aCmts && is_array($aCmts)) {
            foreach ($aCmts as $aCmt) {
                $aComments[] = array(
                    'html'     => $aCmt['html'],
                    'idParent' => $aCmt['obj']->getPid(),
                    'id'       => $aCmt['obj']->getId(),
                );
            }
        }

        $this->Viewer_AssignAjax('iMaxIdComment', $iMaxIdComment);
        $this->Viewer_AssignAjax('aComments', $aComments);
    }


    /**
     * Обработка добавление комментария к топику
     *
     */
    protected function SubmitComment() {

        // если пользовтаель авторизован, то работаем стандартно по родителю
        if (E::IsUser()) {
            parent::SubmitComment();

            return;
        }

        // * А не забанен ли
        if ($this->Vote_IpIsBanned(F::GetUserIp())) {
            $this->Message_AddErrorSingle($this->Lang_Get('plugin.gc.user_is_banned'), $this->Lang_Get('error'));

            return;
        }


        // ЗДЕСЬ ПОЛЬЗОВАТЕЛЬ НЕ АВТОРИЗОВАН
        /** @var ModuleUser_EntityUser $oGuestUser */
        $oGuestUser = $this->User_GetUserByLogin(Config::Get('plugin.gc.guest_login'));
        if (!$oGuestUser) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));

            return;
        }

        // Если нет валидного токена соцсети, смотрим через капчу с email
        /** @var PluginGc_ModuleCommentProvider_EntityUserToken $oToken */
        if (!($oToken = $this->PluginGc_CommentProvider_ValidateCommentRight())) {

            if (!isset($_SESSION['comment_captcha_keystring']) || mb_strtolower($_SESSION['comment_captcha_keystring']) != mb_strtolower(getRequest('comment-captcha', FALSE))) {
                $this->Message_AddErrorSingle($this->Lang_Get('plugin.gc.error_captcha'), $this->Lang_Get('error'));

                return;
            }

            if (!F::CheckVal($sGuestLogin = getRequest('guest_login', FALSE), 'text', 2, 100)) {
                $this->Message_AddErrorSingle($this->Lang_Get('plugin.gc.error_guest_login'), $this->Lang_Get('error'));

                return;
            }

            if (!F::CheckVal($sGuestMail = getRequest('guest_mail', FALSE), 'mail')) {
                $this->Message_AddErrorSingle($this->Lang_Get('plugin.gc.error_guest_mail'), $this->Lang_Get('error'));

                return;
            }

            // Через email
            $bViaMail = TRUE;

        } else {

            $bViaMail = TRUE;

            if (!isset($_SESSION['comment_captcha_keystring']) || mb_strtolower($_SESSION['comment_captcha_keystring']) != mb_strtolower(getRequest('comment-captcha', FALSE))) {
                $bViaMail = FALSE;
            }

            if (!F::CheckVal($sGuestLogin = getRequest('guest_login', FALSE), 'text', 2, 100)) {
                $bViaMail = FALSE;
            }

            if (!F::CheckVal($sGuestMail = getRequest('guest_mail', FALSE), 'mail')) {
                $bViaMail = FALSE;
            }

        }




        // Проверяем топик
        if (!($oTopic = $this->Topic_GetTopicById(F::GetRequestStr('cmt_target_id')))) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));

            return;
        }

        // * Возможность постить коммент в топик в черновиках
        if (!$oTopic->getPublish()) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));

            return;
        }

        // * Проверяем разрешено ли постить комменты
        if (!$this->ACL_CanPostComment($oGuestUser, $oTopic)) {
            $this->Message_AddErrorSingle($this->Lang_Get('topic_comment_acl'), $this->Lang_Get('error'));

            return;
        }

        // * Проверяем разрешено ли постить комменты по времени
        if (!$this->ACL_CanPostCommentTime($oGuestUser)) {
            $this->Message_AddErrorSingle($this->Lang_Get('topic_comment_limit'), $this->Lang_Get('error'));

            return;
        }

        // * Проверяем запрет на добавления коммента автором топика
        if ($oTopic->getForbidComment()) {
            $this->Message_AddErrorSingle($this->Lang_Get('topic_comment_notallow'), $this->Lang_Get('error'));

            return;
        }

        // * Проверяем текст комментария
        $sText = $this->Text_Parser(F::GetRequestStr('comment_text'));
        if (!F::CheckVal($sText, 'text', Config::Val('module.comment.min_length', 2), Config::Val('module.comment.max_length', 10000))) {
            $this->Message_AddErrorSingle($this->Lang_Get('topic_comment_add_text_error'), $this->Lang_Get('error'));

            return;
        }

        // * Проверям на какой коммент отвечаем
        if (!$this->isPost('reply')) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));

            return;
        }
        $oCommentParent = NULL;
        $iParentId = intval(F::GetRequest('reply'));
        if ($iParentId != 0) {
            // * Проверяем существует ли комментарий на который отвечаем
            if (!($oCommentParent = $this->Comment_GetCommentById($iParentId))) {
                $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));

                return;
            }
            // * Проверяем из одного топика ли новый коммент и тот на который отвечаем
            if ($oCommentParent->getTargetId() != $oTopic->getId()) {
                $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));

                return;
            }
        } else {

            // * Корневой комментарий
            $iParentId = NULL;
        }

        // * Проверка на дублирующий коммент
        if ($this->Comment_GetCommentUnique($oTopic->getId(), 'topic', $oGuestUser->getId(), $iParentId, md5($sText))) {
            $this->Message_AddErrorSingle($this->Lang_Get('topic_comment_spam'), $this->Lang_Get('error'));

            return;
        }

        // * Создаём коммент
        $oCommentNew = Engine::GetEntity('Comment');
        $oCommentNew->setTargetId($oTopic->getId());
        $oCommentNew->setTargetType('topic');
        $oCommentNew->setTargetParentId($oTopic->getBlog()->getId());
        $oCommentNew->setUserId($oGuestUser->getId());
        $oCommentNew->setText($sText);
        $oCommentNew->setDate(F::Now());
        $oCommentNew->setUserIp(F::GetUserIp());
        $oCommentNew->setPid($iParentId);
        $oCommentNew->setTextHash(md5($sText));
        $oCommentNew->setPublish($oTopic->getPublish());

        if ($bViaMail) {
            $oCommentNew->setGuestLogin($sGuestLogin);
            $oCommentNew->setGuestMail($sGuestMail);
        } else {
            $oCommentNew->setGuestLogin($oToken->getTokenUserLogin());
            $oCommentNew->setGuestMail($oToken->getTokenUserEmail());
            $oCommentNew->setTokenId($oToken->getTokenId());
            $oCommentNew->setSocial($oToken->getTokenProviderName());
            $oCommentNew->setSocialId($oToken->getTokenProviderUserId());
            $oCommentNew->setImage($oToken->getTokenImage());

        }

        // * Добавляем коммент
        $this->Hook_Run(
            'comment_add_before',
            array('oCommentNew' => $oCommentNew, 'oCommentParent' => $oCommentParent, 'oTopic' => $oTopic)
        );
        if ($this->Comment_AddComment($oCommentNew)) {
            $this->Hook_Run(
                'comment_add_after',
                array('oCommentNew' => $oCommentNew, 'oCommentParent' => $oCommentParent, 'oTopic' => $oTopic)
            );

            $this->Viewer_AssignAjax('sCommentId', $oCommentNew->getId());
            if ($oTopic->getPublish()) {

                // * Добавляем коммент в прямой эфир если топик не в черновиках
                // @todo Прямой эфир
                $oCommentOnline = Engine::GetEntity('Comment_CommentOnline');
                $oCommentOnline->setTargetId($oCommentNew->getTargetId());
                $oCommentOnline->setTargetType($oCommentNew->getTargetType());
                $oCommentOnline->setTargetParentId($oCommentNew->getTargetParentId());
                $oCommentOnline->setCommentId($oCommentNew->getId());

                $this->Comment_AddCommentOnline($oCommentOnline);
            }

            // * Список емайлов на которые не нужно отправлять уведомление
            $aExcludeMail = array($oGuestUser->getMail());

            // * Отправляем уведомление тому на чей коммент ответили
            if ($oCommentParent && $oCommentParent->getUserId() != $oTopic->getUserId()
                && $oCommentNew->getUserId() != $oCommentParent->getUserId()
            ) {
                $oUserAuthorComment = $oCommentParent->getUser();
                $aExcludeMail[] = $oUserAuthorComment->getMail();
                // @todo Отправка сообщения об анонимном коммнтарии
                $this->Notify_SendCommentReplyToAuthorParentComment(
                    $oUserAuthorComment, $oTopic, $oCommentNew, $oGuestUser
                );
            }

            // * Отправка уведомления автору топика
            $this->Subscribe_Send(
                'topic_new_comment', $oTopic->getId(), 'comment_new.tpl',
                $this->Lang_Get('notify_subject_comment_new'),
                array('oTopic' => $oTopic, 'oComment' => $oCommentNew, 'oUserComment' => $oGuestUser),
                $aExcludeMail, 'gc'
            );

            // * Добавляем событие в ленту
            // @todo Добавление события в ленту
            $this->Stream_Write(
                $oCommentNew->getUserId(), 'add_comment', $oCommentNew->getId(),
                $oTopic->getPublish() && !$oTopic->getBlog()->IsPrivate()
            );
        } else {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));
        }
    }
}