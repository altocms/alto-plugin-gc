<?php

require_once __DIR__ . "/../AuthCommentProvider.class.php";

/**
 * VALUABLE_ACCESS - доступ ко всем методам API, кроме users.getLoggedInUser и users.getCurrentUser. Данное право выставляется также и со стороны Одноклассников. Для запроса следует отправить email с идентификатором и shortname приложения на адрес oauth@odnoklassniki.ru
 * Class OdProvider
 */
class OdCommentProvider extends AuthCommentProvider {

    public $sName = 'od';
    public $sAuthUrl = 'http://www.odnoklassniki.ru/oauth/authorize?client_id=%%client_id%%&response_type=code&redirect_uri=%%redirect%%&scope=%%permissions%%';
    public $sTokenUrl = 'http://api.odnoklassniki.ru/oauth/token.do?code=%%code%%&redirect_uri=%%redirect%%&grant_type=authorization_code&client_id=%%client_id%%&client_secret=%%secret_key%%';
    public $sUserInfoUrl = 'http://api.odnoklassniki.ru/fb.do?access_token=%%access_token%%&application_key=%%public_key%%&method=users.getCurrentUser&sig=%%signature%%';

    /**
     * Получение токена пользователя
     *
     * @return PluginGc_ModuleCommentProvider_EntityUserToken
     * @throws Exception
     */
    public function GetUserToken() {

        if (!$aData = $this->LoadTokenData()) {
            return FALSE;
        }

        /**
         * Возвратим объект токена
         */
        $oToken = Engine::GetEntity('PluginGc_ModuleCommentProvider_EntityUserToken', array(
            'token_provider_name'    => $this->sName,
            'token_data'             => $aData->access_token,
            'token_expire'           => 0,
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
            ))
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
            'data_login'         => $this->sName . '_' . $oData->uid,
            'data_name'          => @$oData->first_name,
            'data_surname'       => @$oData->last_name,
            'data_sex'           => ((@$oData->gender == 'male') ? 'man' : ($oData->gender == 'female' ? 'woman' : 'other')),
            'data_about'         => @$oData->current_status ? @$oData->current_status : '',
            'data_page'          => $oData->uid,
            'data_birthday'      => date('Y-m-d H:i:s', strtotime(@$oData->birthday)),
            'data_mail'          => '',
            'data_photo'         => @$oData->photo_id ? $oData->pic_2 : '',
        ));

    }


}