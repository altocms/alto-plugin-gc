<?php

/**
 * Comment.entity.class.php
 * Файл сущности для модуля Comment плагина Gc
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Gc
 */
class PluginGc_ModuleComment_EntityComment extends PluginGc_Inherits_ModuleComment_EntityComment {

    /**
     * Возвращает идентификатор записи
     *
     * @return int|null
     */
    public function getGuestSocialLink() {

        if ($iTokenId = $this->getTokenId()) {
            /** @var PluginGc_ModuleCommentProvider_EntityUserToken $oToken */
            $oToken = E::Module('PluginGc\CommentProvider')->GetUserTokenByTokenId($iTokenId);

            // Нет токена
            if (!$oToken) {
                return '';
            }

            switch ($oToken->getTokenProviderName()) {
                case 'vk':
                    $sLink = '<a href="http://vk.com/id{*}" rel="nofollow">{**}</a>';
                    break;
                case 'od':
                    $sLink = '<a href="http://www.odnoklassniki.ru/profile/{*}/" rel="nofollow">{**}</a>';
                    break;
                case 'mm':
                    $sLink = '<a href="http://my.mail.ru/mail/{*}/" rel="nofollow">{**}</a>';
                    break;
                case 'i':
                    $sLink = '<a href="http://instagram.com/{*}" rel="nofollow">{**}</a>';
                    break;
                case 'github':
                    $sLink = '<a href="https://github.com/{*}" rel="nofollow">{**}</a>';
                    break;
                case 'li':
                    $sLink = '<a href="https://www.linkedin.com/profile/view?id={*}" rel="nofollow">{**}</a>';
                    break;
                case 'ya':
                    $sLink = 'Yandex';
                    break;
                case 'g':
                    $sLink = '<a href="https://plus.google.com/{*}" rel="nofollow">{**}</a>';
                    break;
                case 'fb':
                    $sLink = '<a href="http://facebook.com/{*}" rel="nofollow">{**}</a>';
                    break;

                default:
                    $sLink = FALSE;
            }

            if ($sLink) {
                $sLink = str_replace('{*}', $oToken->getTokenProviderUserId(), $sLink);
                $sLink = str_replace('{**}', E::Module('Lang')->Get('plugin.gc.' . $oToken->getTokenProviderName()), $sLink);
            }

            return $sLink;
        }
    }

    /**
     * Возвращает идентификатор записи
     * @return int|null
     */
    public function getSocialLink() {

        if (($sSocialType = $this->getCommentSocial()) && $this->getSocialId()) {
            switch ($sSocialType) {
                case 'vk':
                    $sLink = 'http://vk.com/id{*}';
                    break;
                case 'od':
                    $sLink = 'http://www.odnoklassniki.ru/profile/{*}/';
                    break;
                case 'mm':
                    $sLink = 'http://my.mail.ru/mail/{*}/';
                    break;
                case 'i':
                    $sLink = 'http://instagram.com/{*}';
                    break;
                case 'github':
                    $sLink = 'https://github.com/{*}';
                    break;
                case 'li':
                    $sLink = 'https://www.linkedin.com/profile/view?id={*}';
                    break;
                case 'ya':
                    $sLink = 'http://yandex.ru';
                    break;
                case 'g':
                    $sLink = 'https://plus.google.com/{*}';
                    break;
                case 'fb':
                    $sLink = 'http://facebook.com/{*}';
                    break;

                default:
                    $sLink = FALSE;
            }


            if ($sLink) {
                $sLink = str_replace('{*}', $this->getSocialId(), $sLink);
            }

            return $sLink;
        }

        return FALSE;
    }

}

// EOF