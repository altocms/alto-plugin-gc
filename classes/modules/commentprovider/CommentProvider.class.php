<?php

/**
 * AuthProvider
 * Файл модуля CommentProvider.class.php плагина Gc
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Gc
 */
class PluginGc_ModuleCommentProvider extends ModuleORM {

    /**
     * провайдеры авторизации
     *
     * @var AuthProvider[]
     */
    protected $aProviders = array();

    protected function _makeProviderClass($sProviderName, $aProviderData)
    {
        /** @noinspection PhpIncludeInspection */
        include_once __DIR__ . '/../../../lib/providers/' . ucwords($sProviderName) . '.class.php';

        /** @var string $sProviderClassName Имя класса провайдера */
        $sProviderClassName = ucwords($sProviderName) . 'CommentProvider';
        $oProvider = new $sProviderClassName(
            $sProviderName,
            $aProviderData,
            TRUE
        );
        return $oProvider;
    }

    /**
     * Инициализация модуля
     */
    public function Init() {

        parent::Init();

        foreach (Config::Get('plugin.gc.providers') as $sProviderName => $aProviderData) {

            if (isset($this->aProviders[$sProviderName]))
                continue;

              if (!isset($aProviderData[$sProviderName . '_' . 'client_id']) || empty($aProviderData[$sProviderName . '_' . 'client_id'])) {
                  continue;
            }

            // Проверим секретный ключ
            if (!isset($aProviderData[$sProviderName . '_' . 'secret_key']) || empty($aProviderData[$sProviderName . '_' . 'secret_key'])) {
                continue;
            }

            $oProvider = $this->_makeProviderClass($sProviderName, $aProviderData);
            /**
             * Проверяем, не было ли  ошибки создания объекта авторизации
             */
            if ($oProvider && !$oProvider->getLastError()) {
                $this->aProviders[$sProviderName] = $oProvider;
            }
        }
    }

    /**
     * Получение пользователя по данным токена
     *
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     *
     * @return bool|ModuleUser_EntityUser
     */
    public function GetUserByTokenData($oToken) {

        /** @var array|PluginAr_ModuleAuthProvider_EntityUserToken $oResult */
        $oResult = E::Module('PluginGc\CommentProvider')->GetUserTokenItemsByFilter(array(
            'token_data' => $oToken->getTokenData(),
        ));

        if ($oResult) {
            $oResult = array_shift($oResult);

            // Обновим значение токена, если необходимо
            if ($oResult->getTokenExpire() != $oToken->getTokenExpire()) {
                $oResult->setTokenExpire($oToken->getTokenExpire());
                $oResult->Update();
            }
            return $oResult;
        }

        return FALSE;
    }

    /**
     * Получение пользователя по токену
     *
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     *
     * @return bool|ModuleUser_EntityUser
     */
    public function GetUserByToken($oToken) {

        /** @var PluginAr_ModuleAuthProvider_EntityUserToken|array $oResult */
        $oResult = E::Module('PluginGc\CommentProvider')->GetUserTokenItemsByFilter(array(
            'token_provider_user_id' => $oToken->getTokenProviderUserId(),
            'token_provider_name'    => $oToken->getTokenProviderName(),
        ));

        if ($oResult) {
            $oResult = array_shift($oResult);

            // Обновим значение токена, если необходимо
            if ($oResult->getTokenData() != $oToken->getTokenData()) {
                $oResult->setTokenData($oToken->getTokenData());
                $oResult->setTokenExpire($oToken->getTokenExpire());
                $oResult->Update();
            }

            return $oResult;
        }

        return FALSE;
    }

    /**
     * Получение провайдера по его имени
     *
     * @param $sProviderName
     *
     * @return AuthProvider|bool
     */
    public function GetProviderByName($sProviderName) {

        return isset($this->aProviders[$sProviderName]) ? $this->aProviders[$sProviderName] : FALSE;
    }

    /**
     * Возвращает массив провайдеров
     *
     * @return AuthProvider[]
     */
    public function GetProviders() {

        return $this->aProviders;
    }

    /**
     * Сохраняет данные пользхователя
     *
     * @param PluginGc_ModuleCommentProvider_EntityData $oUserData
     * @param $oToken
     */
    public function SaveUserData($oUserData, $oToken) {

        $oToken->setTokenUserEmail($oUserData->getDataMail());

        $sUserName = trim($oUserData->getDataName() . ' ' .$oUserData->getDataSurname());
        $oToken->setTokenUserLogin($sUserName?$sUserName:E::Module('Lang')->Get('plugin.gc.guest'));

        $sUserLogoUrl = E::Module('Uploader')->UploadRemote($oUserData->getDataPhoto());
        $sUserLogoUrlNew = FALSE;
        if ($sUserLogoUrl) {
            $sFileTmp = E::Module('Img')->TransformFile($sUserLogoUrl, 'topic', array());
            if ($sFileTmp) {
                $sDirUpload = E::Module('Uploader')->GetUserImageDir(0);
                $sFileImage = E::Module('Uploader')->Uniqname($sDirUpload, F::File_GetExtension($sFileTmp, true));
                if ($xStoredFile = E::Module('Uploader')->Store($sFileTmp, $sFileImage)) {
                    if (is_object($xStoredFile)) {
                        $sUserLogoUrlNew = $xStoredFile->GetUrl();
                    } else {
                        $sUserLogoUrlNew = E::Module('Uploader')->Dir2Url($xStoredFile);
                    }
                }
            }
        }

        $oToken->setTokenImage($sUserLogoUrlNew);
        $oToken->Add();
    }

    /**
     * Проверяет право пользователя проводить комментирвоание топика
     */
    public function ValidateCommentRight() {

        $sTokenId = E::Module('Session')->Get('comment_token_id');
        $sTokenHash = E::Module('Session')->GetCookie('comment_token_hash');

        if ($sTokenId && $sTokenHash) {

            /** @var PluginGc_ModuleCommentProvider_EntityUserToken $oToken */
            $oToken = E::Module('PluginGc\CommentProvider')->GetUserTokenByTokenId($sTokenId);

            if ($oToken && md5(Config::Get('plugin.gc.salt') . $oToken->getTokenData()) == $sTokenHash) {
                return $oToken;
            }
        }

        return FALSE;
    }

    /**
     * @param $sUrl
     * @param $oUser
     *
     * @return bool
     */
    public function UploadUserImageByUrl($sUrl, $oUser) {

        if ($sFileTmp = E::Module('Uploader')->UploadRemote($sUrl)) {
            if ($sFileUrl = E::Module('User')->UploadAvatar($sFileTmp, $oUser, array())) {
                return $sFileUrl;
            }
        }

        return FALSE;
    }

}

// EOF