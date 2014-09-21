/*!
 * script.js
 * Файл скриптов плагина Gc
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Ar
 * @version     0.0.1 от 30.07.2014 21:12
 */

var ls = ls || {};

ls.gc = (function ($) {

    /**
     * Удаление комментария к топику
     *
     * @param $this jQuery|*
     * @param $sConfirmText string
     * @param iTopicId int
     * @returns {boolean}
     */
    this.comment_del = function ($this, $sConfirmText, iTopicId) {

        // Отказались удалять комментарий
        if (!confirm($sConfirmText)) return false;

        // Удаляем этот комментарий
        var url = aRouter.admin + 'social-comment-delete/';

        ls.ajax(url, {comment_id: iTopicId}, function (result) {
            $this.parents('tr.comment-line').fadeOut(100);

            ls.msg.notice(null, result.sMsg);

        });

        return false;
    };


    return this;


}).call(ls.gc || {}, jQuery);




