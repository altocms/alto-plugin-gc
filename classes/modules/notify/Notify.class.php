<?php

/**
 * Notify.class.php
 * Файл модуля Notify плагина Gc
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Gc
 */
class PluginGc_ModuleNotify extends PluginGc_Inherits_ModuleNotify {

    /**
     * Глушим пересылку гостям
     *
     * @param ModuleUser_EntityUser $oUserTo
     * @param ModuleTopic_EntityTopic $oTopic
     * @param ModuleComment_EntityComment $oComment
     * @param ModuleUser_EntityUser $oUserComment
     *
     * @return bool
     */
    public function SendCommentReplyToAuthorParentComment(
        ModuleUser_EntityUser $oUserTo, ModuleTopic_EntityTopic $oTopic, ModuleComment_EntityComment $oComment,
        ModuleUser_EntityUser $oUserComment
    ) {
        if ($oUserComment->getLogin() == Config::Get('plugin.gc.guest_login') || $oUserTo->getId() == Config::Get('plugin.gc.guest_login') ) {
            return TRUE;
        }

        return parent::SendCommentReplyToAuthorParentComment($oUserTo, $oTopic, $oComment, $oUserComment);
    }

}

// EOF