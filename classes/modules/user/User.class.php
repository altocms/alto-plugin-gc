<?php

/**
 * User.class.php
 * Файл модуля User плагина Gc
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Ar
 */
class PluginGc_ModuleUser extends PluginGc_Inherits_ModuleUser {

    public function UploadAvatar($sFile, $oUser, $aSize = array()) {

        if (!F::File_Exists($sFile)) {
            return FALSE;
        }
        if (!$aSize) {
            $oImg = E::Module('Img')->CropSquare($sFile, TRUE);
        } else {
            if (!isset($aSize['w'])) {
                $aSize['w'] = $aSize['x2'] - $aSize['x1'];
            }
            if (!isset($aSize['h'])) {
                $aSize['h'] = $aSize['y2'] - $aSize['y1'];
            }
            $oImg = E::Module('Img')->Crop($sFile, $aSize['w'], $aSize['h'], $aSize['x1'], $aSize['y1']);
        }

        $sExtension = E::Module('Uploader')->GetExtension($sFile);

        $sName = pathinfo($sFile, PATHINFO_FILENAME);

        // Сохраняем аватар во временный файл
        if ($sTmpFile = $oImg->Save(F::File_UploadUniqname($sExtension))) {

            // Файл, куда будет записан аватар
            $sAvatar = E::Module('Uploader')->GetUserAvatarDir($oUser->GetId()) . $sName . '.' . $sExtension;

            // Окончательная запись файла только через модуль Uploader
            if ($xStoredFile = E::Module('Uploader')->Store($sTmpFile, $sAvatar)) {
                if (is_object($xStoredFile)) {
                    return $xStoredFile->GetUrl();
                } else {
                    return E::Module('Uploader')->Dir2Url($xStoredFile);
                }
            }
        }

        // * В случае ошибки, возвращаем false
        E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('system_error'));

        return FALSE;
    }

}

// EOF