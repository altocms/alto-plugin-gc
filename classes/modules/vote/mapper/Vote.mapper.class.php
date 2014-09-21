<?php

/**
 * Vote.mapper.class.php
 * Файл маппера для модуля Vote плагина Gc
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Av
 * @version     0.0.1 от 20.08.14 10:27
 */
class PluginGc_ModuleVote_MapperVote extends PluginGc_Inherit_ModuleVote_MapperVote {

    /**
     * Проверяет забанен ли пользователь или нет
     *
     * @param $sIp
     * @return bool
     */
    public function IpIsBanned($sIp) {

        $sql = "SELECT id FROM ?_adminips WHERE
                    INET_ATON(?) >= ip1 AND INET_ATON(?) <= ip2
                    AND banactive = ?d
                    AND banline > ?";

        $aRows = $this->oDb->select($sql, $sIp, $sIp, 1, date('Y-m-d H:i:s'));

        if ($aRows) {
            return TRUE;
        }

        return FALSE;

    }

}