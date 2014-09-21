<?php

require_once __DIR__ . "/../AuthCommentProvider.class.php";

class VkCommentProvider extends AuthCommentProvider {

    public $sName = 'vk';
    public $sAuthUrl = 'https://oauth.vk.com/authorize?client_id=%%client_id%%&scope=%%permissions%%&redirect_uri=%%redirect%%&response_type=code&v=5.23';
    public $sTokenUrl = 'https://oauth.vk.com/access_token?client_id=%%client_id%%&client_secret=%%secret_key%%&code=%%code%%&redirect_uri=%%redirect%%';
    public $sUserInfoUrl = 'https://api.vkontakte.ru/method/getProfiles?uid=%%user_id%%&fields=sex,status,domain,bdate,photo_big&access_token=%%access_token%%';

    /**
     * Получение токена пользователя
     *
     * @return PluginGc_ModuleCommentProvider_EntityUserToken
     * @throws Exception
     */
    public function GetUserToken() {

        if (!$aData = $this->LoadTokenData(FALSE)) {
            return FALSE;
        }

        /**
         * Возвратим объект токена
         */
        $oToken = Engine::GetEntity('PluginGc_ModuleCommentProvider_EntityUserToken', array(
            'token_provider_name'    => $this->sName,
            'token_data'             => $aData->access_token,
            'token_expire'           => intval($aData->expires_in),
            'token_provider_user_id' => $aData->user_id,
        ));

        return $oToken;

    }

    /**
     * Получение дополнительных данных пользователя
     *
     * @param PluginGc_ModuleCommentProvider_EntityUserToken $oToken
     * @throws Exception
     * @return array|PluginGc_ModuleCommentProvider_EntityData
     */
    public function GetUserData(PluginGc_ModuleCommentProvider_EntityUserToken $oToken) {

        if (!$aData = $this->LoadAdditionalData(
            $oToken,
            array(
                '%%user_id%%'      => $oToken->getTokenProviderUserId(),
                '%%access_token%%' => $oToken->getTokenData()),
            FALSE)
        ) {
            return FALSE;
        }

        // Раскодируем
        $aData = json_decode($aData);

        // Сократим путь к данным и проверим его, в смысле путь
        $oData = @$aData->response[0];
        if (!$aData) {
            $this->setLastErrorCode(3);

            return FALSE;
        }

        /**
         * Получили дополнительные данные. Заполним профиль из того, что есть
         */

        return Engine::GetEntity('PluginGc_ModuleCommentProvider_EntityData', array(
            'data_provider_name' => $this->sName,
            'data_login'         => $this->sName . '_' . $oToken->getTokenProviderUserId(),
            'data_name'          => @$oData->first_name,
            'data_surname'       => @$oData->last_name,
            'data_sex'           => ((@$oData->sex && $oData->sex > 0) ? ($oData->sex == 1 ? 'woman' : 'man') : 'other'),
            'data_about'         => @$oData->status,
            'data_page'          => @$oData->domain,
            'data_birthday'      => date('Y-m-d H:i:s', strtotime(@$oData->bdate)),
            'data_mail'          => @$oData->mail,
            'data_photo'         => @$oData->photo_big,
        ));

    }




}