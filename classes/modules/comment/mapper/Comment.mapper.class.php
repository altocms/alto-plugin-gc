<?php

/**
 * Comment.mapper.class.php
 * Файл маппера для модуля Comment плагина Gc
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Gc
 * @version     0.0.1 от 03.09.2014 13:26
 */
class PluginGc_ModuleComment_MapperComment extends PluginGc_Inherit_ModuleComment_MapperComment {

    public function AddComment(ModuleComment_EntityComment $oComment) {

        $sql = "INSERT INTO ?_comment
          (
              comment_pid,
              target_id,
              target_type,
              target_parent_id,
              user_id,
              comment_text,
              comment_date,
              comment_user_ip,
              comment_publish,
              comment_text_hash,
              comment_guest_login,
              comment_guest_mail,
              comment_token_id,
              comment_social,
              comment_social_id,
              comment_image
          )
          VALUES (
              ?, ?d, ?, ?d, ?d, ?, ?, ?, ?d, ?, ?, ?, ?d, ?, ?, ?
          )
        ";
        $iId = $this->oDb->query(
            $sql, $oComment->getPid(), $oComment->getTargetId(), $oComment->getTargetType(),
            $oComment->getTargetParentId(), $oComment->getUserId(), $oComment->getText(), $oComment->getDate(),
            $oComment->getUserIp(), $oComment->getPublish(), $oComment->getTextHash(), $oComment->getGuestLogin(),
            $oComment->getGuestMail(), $oComment->getTokenId(), $oComment->getSocial(), $oComment->getSocialId(), $oComment->getImage()
        );
        return $iId ? $iId : false;
    }

    /**
     * Получает список комментариев по фильтру
     *
     * @param array $aFilter         Фильтр выборки
     * @param array $aOrder          Сортировка
     * @param int   $iCount          Возвращает общее количество элментов
     * @param int   $iCurrPage       Номер текущей страницы
     * @param int   $iPerPage        Количество элементов на одну страницу
     *
     * @return array
     */
    public function GetCommentsByFilter($aFilter, $aOrder, &$iCount, $iCurrPage, $iPerPage) {

        $aOrderAllow = array('comment_id', 'comment_pid', 'comment_rating', 'comment_date', 'comment_delete');
        $sOrder = '';
        if (is_array($aOrder) && $aOrder) {
            foreach ($aOrder as $key => $value) {
                if (!in_array($key, $aOrderAllow)) {
                    unset($aOrder[$key]);
                } elseif (in_array($value, array('asc', 'desc'))) {
                    $sOrder .= " {$key} {$value},";
                }
            }
            $sOrder = trim($sOrder, ',');
        }
        if ($sOrder == '') {
            $sOrder = ' comment_id desc ';
        }

        if (isset($aFilter['target_type']) && !is_array($aFilter['target_type'])) {
            $aFilter['target_type'] = array($aFilter['target_type']);
        }

        $sql = "SELECT
					comment_id
				FROM
					?_comment
				WHERE
					1 = 1
					{ AND comment_id = ?d }
					{ AND user_id = ?d }
					{ AND target_parent_id = ?d }
					{ AND target_id = ?d }
					{ AND target_type IN (?a) }
					{ AND comment_delete = ?d }
					{ AND comment_publish = ?d }
				ORDER by {$sOrder}
				LIMIT ?d, ?d ;
					";
        $aResult = array();
        $aRows = $this->oDb->selectPage(
            $iCount, $sql,
            isset($aFilter['id']) ? $aFilter['id'] : DBSIMPLE_SKIP,
            isset($aFilter['user_id']) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
            isset($aFilter['target_parent_id']) ? $aFilter['target_parent_id'] : DBSIMPLE_SKIP,
            isset($aFilter['target_id']) ? $aFilter['target_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['target_type']) && count($aFilter['target_type'])) ? $aFilter['target_type']
                : DBSIMPLE_SKIP,
            isset($aFilter['delete']) ? $aFilter['delete'] : DBSIMPLE_SKIP,
            isset($aFilter['publish']) ? $aFilter['publish'] : DBSIMPLE_SKIP,
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aResult[] = $aRow['comment_id'];
            }
        }
        return $aResult;
    }
}