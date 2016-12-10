<?php

/**
 * Vote.class.php
 * Файл модуля Vote плагина Gc
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Av
 */
class PluginGc_ModuleVote extends PluginGc_Inherits_ModuleVote {

    /**
     * Проверяет, не забанен ли этот адрес
     *
     * @param string $sIp Ip Адрес
     * @return mixed
     */
    public function IpIsBanned($sIp) {

        return $this->oMapper->IpIsBanned($sIp);
    }

}

// EOF