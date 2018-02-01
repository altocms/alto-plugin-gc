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

/* Запрещаем напрямую через браузер обращение к этому файлу */
if (!class_exists('Plugin')) {
    die('Hacking attempt!');
}

/**
 * PluginGc.class.php
 * Файл основного класса плагина Gc
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Gc
 *
 */
class PluginGc extends Plugin {

    /** @var array $aDelegates Объявление делегирований */
    protected $aDelegates = array(
        'template' => array(

            // Комментарий к топику
            'comments/comment.tree.tpl' => '_comments/comment.tree.tpl',
            'comments/comment.single.tpl' => '_comments/comment.single.tpl',
            'comment_tree.tpl' => '_comments/comment.tree.tpl',
            'comment.tpl' => '_comments/comment.single.tpl',

            // Активность
            'actions/stream/action.stream.events.tpl' => '_actions/stream/action.stream.events.tpl',

            // Виджет последних комментариев
            'widgets/widget.stream_comment.tpl' => '_widgets/widget.stream_comment.tpl',
            'widgets/widget.toolbar_comment.tpl' => '_widgets/widget.toolbar_comment.tpl',

            // Письма
            'emails/ru/email.comment_new.tpl'  ,
            'emails/ru/email.comment_reply.tpl',
            'emails/en/email.comment_new.tpl'  ,
            'emails/en/email.comment_reply.tpl',

            // Админка
            'actions/admin/action.admin.content/comments.tpl',
            'actions/admin/action.admin.content/comments_list.tpl',

        ),
    );

    /** @var array $aInherits Объявление переопределений (модули, мапперы и сущности) */
    protected $aInherits = array(
        'actions' => array(
            'ActionBlog',
            'ActionAdmin',
        ),
        'modules' => array(
            'ModuleVote',
            'ModuleNotify',
            'ModuleUser',
            'ModuleUploader',
        ),
        'entity'  => array(
            'ModuleComment_EntityComment',
        ),
        'mapper'  => array(
            'ModuleComment_MapperComment',
            'ModuleVote_MapperVote',
        ),
    );

    /**
     * Активация плагина
     * @return bool
     */
    public function Activate() {

        if (!$this->isFieldExists('prefix_comment', 'comment_guest_login')) {
            $this->ExportSQL(__DIR__ . '/install/db/init.sql');
        }

        if (!$this->isFieldExists('prefix_comment', 'comment_image')) {
            $this->ExportSQL(__DIR__ . '/install/db/update-to-1.1.sql');
        }

        return TRUE;
    }

    /**
     * Деактивация плагина
     * @return bool
     */
    public function Deactivate() {

        return TRUE;
    }

    /**
     * Инициализация плагина
     */
    public function Init() {

        E::Module('Viewer')->Assign('sTemplatePathGc', Plugin::GetTemplateDir(__CLASS__));
        E::Module('Viewer')->AppendStyle(Plugin::GetTemplateDir(__CLASS__) . 'assets/css/plugin.gc.css'); // Добавление своего CSS
        E::Module('Viewer')->AppendScript(Plugin::GetTemplateDir(__CLASS__) . 'assets/js/plugin.gc.js'); // Добавление своего JS

        return TRUE;
    }

}

// EOF