<?php

require_once __DIR__ . "/../AuthCommentProvider.class.php";

class FbCommentProvider extends AuthCommentProvider {

    public $sName = 'fb';
    public $sAuthUrl = 'https://www.facebook.com/dialog/oauth?client_id=%%client_id%%&redirect_uri=%%redirect%%&response_type=code&scope=%%permissions%%';
    public $sTokenUrl = 'https://graph.facebook.com/oauth/access_token?client_id=%%client_id%%&redirect_uri=%%redirect%%&client_secret=%%secret_key%%&code=%%code%%';
    public $sUserInfoUrl = 'https://graph.facebook.com/me?access_token=%%access_token%%';

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
            'token_expire'           => $aData->expires?time()+$aData->expires:0,
            'token_provider_user_id' => 0,
        ));

        return $oToken;
    }

    public function GetUserData(PluginGc_ModuleCommentProvider_EntityUserToken $oToken) {

        if (!$aData = $this->LoadAdditionalData(
            $oToken,
            array(
                '%%public_key%%'   => Config::Get('plugin.ar.providers.od.od_public_key'),
                '%%access_token%%' => $oToken->getTokenData(),
                '%%signature%%'    => md5("application_key=" . Config::Get('plugin.ar.providers.od.od_public_key') . "method=users.getCurrentUser" . md5($oToken->getTokenData() . Config::Get('plugin.ar.providers.od.od_secret_key')))
            ), FALSE)
        ) {
            return FALSE;
        }

        // Раскодируем
        $oData = json_decode($aData);

        /**
         * Получили дополнительные данные. Заполним профиль из того, что есть
         */

        return Engine::GetEntity('PluginGc_ModuleCommentProvider_EntityData', array(
            'data_provider_name' => $this->sName,
            'data_login'         => $this->sName . '_' . $oData->id,
            'data_name'          => @$oData->first_name,
            'data_surname'       => @$oData->last_name,
            'data_sex'           => ((@$oData->gender == 'male') ? 'man' : ($oData->gender == 'female' ? 'woman' : 'other')),
            'data_about'         => @$oData->bio ? @$oData->bio : '',
            'data_page'          => @$oData->id,
            'data_birthday'      => date('Y-m-d H:i:s', strtotime(@$oData->birthday)),
            'data_mail'          => @$oData->email,
            'data_photo'         => "https://graph.facebook.com/{$oData->id}/picture?type=large",
        ));

    }


}