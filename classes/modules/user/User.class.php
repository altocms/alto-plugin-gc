<?php

/**
 * User.class.php
 * Файл модуля User плагина Gc
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Ar
 * @version     0.0.1 от 16.12.2014 23:50
 */
class PluginGc_ModuleUser extends PluginGc_Inherit_ModuleUser {

    public function UploadAvatar($sFile, $oUser, $aSize = array()) {

        if (!F::File_Exists($sFile)) {
            return FALSE;
        }
        if (!$aSize) {
            $oImg = $this->Img_CropSquare($sFile, TRUE);
        } else {
            if (!isset($aSize['w'])) {
                $aSize['w'] = $aSize['x2'] - $aSize['x1'];
            }
            if (!isset($aSize['h'])) {
                $aSize['h'] = $aSize['y2'] - $aSize['y1'];
            }
            $oImg = $this->Img_Crop($sFile, $aSize['w'], $aSize['h'], $aSize['x1'], $aSize['y1']);
        }

        $sExtension = $this->Uploader_GetExtension($sFile);

        $sName = pathinfo($sFile, PATHINFO_FILENAME);

        // Сохраняем аватар во временный файл
        if ($sTmpFile = $oImg->Save(F::File_UploadUniqname($sExtension))) {

            // Файл, куда будет записан аватар
            $sAvatar = $this->Uploader_GetUserAvatarDir($oUser->GetId()) . $sName . '.' . $sExtension;

            // Окончательная запись файла только через модуль Uploader
            if ($xStoredFile = $this->Uploader_Store($sTmpFile, $sAvatar)) {
                if (is_object($xStoredFile)) {
                    return $xStoredFile->GetUrl();
                } else {
                    return $this->Uploader_Dir2Url($xStoredFile);
                }
            }
        }

        // * В случае ошибки, возвращаем false
        $this->Message_AddErrorSingle($this->Lang_Get('system_error'));

        return FALSE;
    }

}