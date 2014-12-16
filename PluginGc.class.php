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

/** Запрещаем напрямую через браузер обращение к этому файлу.  */
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
 * @method void Viewer_AppendStyle
 * @method void Viewer_AppendScript
 * @method void Viewer_Assign
 *
 * @version     0.0.1 от 03.09.2014 10:01
 */
class PluginGc extends Plugin {

    /** @var array $aDelegates Объявление делегирований */
    protected $aDelegates = array(
        'template' => array(

            // Комментарий к топику
            'comments/comment.tree.tpl' => '_comments/comment.tree.tpl',
            'comments/comment.single.tpl' => '_comments/comment.single.tpl',

            // Активность
            'actions/stream/action.stream.events.tpl' => '_actions/stream/action.stream.events.tpl',

            // Виджет последних комментариев
            'widgets/widget.stream_comment.tpl' => '_widgets/widget.stream_comment.tpl',

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
            $this->ExportSQL(dirname(__FILE__) . '/sql/install.sql');
        }

        if (!$this->isFieldExists('prefix_comment', 'comment_image')) {
            $this->ExportSQL(dirname(__FILE__) . '/sql/update-to-1.1.sql');
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
        $this->Viewer_Assign("sTemplatePathGc", Plugin::GetTemplatePath(__CLASS__));
        $this->Viewer_AppendStyle(Plugin::GetTemplatePath(__CLASS__) . "assets/css/style.css"); // Добавление своего CSS
        $this->Viewer_AppendScript(Plugin::GetTemplatePath(__CLASS__) . "assets/js/script.js"); // Добавление своего JS
    }

}
