<?php

require_once __DIR__ . "/../AuthCommentProvider.class.php";

class MmCommentProvider extends AuthCommentProvider {

    public $sName = 'mm';
    public $sAuthUrl = 'https://connect.mail.ru/oauth/authorize?client_id=%%client_id%%&response_type=code&redirect_uri=%%redirect%%&scope=%%permissions%%';
    public $sTokenUrl = 'https://connect.mail.ru/oauth/token?client_id=%%client_id%%&redirect_uri=%%redirect%%&client_secret=%%secret_key%%&code=%%code%%&grant_type=authorization_code';
    public $sUserInfoUrl = 'http://www.appsmail.ru/platform/api?method=users.getInfo&secure=1&app_id=%%client_id%%&session_key=%%access_token%%&sig=%%signature%%';

    public $sPermissionsGutter = ' ';

    /**
     * Расчет сигнатуры
     *
     * @see http://api.mail.ru/docs/guides/restapi/
     * @param array $request_params
     * @param       $secret_key
     * @return string
     */
    protected function GetSignature(array $request_params, $secret_key) {
        ksort($request_params);
        $params = '';
        foreach ($request_params as $key => $value) {
            $params .= "$key=$value";
        }

        return md5($params . $secret_key);
    }

    /**
     * Формирует строку параметров из массива
     *
     * @param array $request_params
     * @param       $sSignature
     * @return string
     */
    protected function BuildParamsString(array $request_params, $sSignature) {
        ksort($request_params);
        $params = '?';
        foreach ($request_params as $key => $value) {
            $params .= "$key=$value&";
        }

        return $params . 'sig=' . $sSignature;
    }

    /**
     * Получение токена пользователя
     *
     * @return PluginGc_ModuleCommentProvider_EntityUserToken
     * @throws Exception
     */
    public function GetUserToken() {

        if (!$aData = $this->LoadTokenData(TRUE)) {
            return FALSE;
        }

        /**
         * Возвратим объект токена
         */
        $oToken = Engine::GetEntity('PluginGc_ModuleCommentProvider_EntityUserToken', array(
            'token_provider_name'    => $this->sName,
            'token_data'             => $aData->access_token,
            'token_expire' => $aData->expires_in ? time() + $aData->expires_in : 0,
            'token_provider_user_id' => 0,
        ));

        return $oToken;
    }

    public function GetUserData(PluginGc_ModuleCommentProvider_EntityUserToken $oToken) {

        if (!$aData = $this->LoadAdditionalData(
            $oToken,
            array(
                '%%access_token%%' => $oToken->getTokenData(),
                '%%signature%%'    => md5("app_id={$this->sClientId}method=users.getInfosecure=1session_key={$oToken->getTokenData()}{$this->sSecretKey}")
            ), FALSE)
        ) {
            return FALSE;
        }

        // Раскодируем
        $oData = json_decode($aData);

        $oData = $oData[0];

        /**
         * Получили дополнительные данные. Заполним профиль из того, что есть
         */

        return Engine::GetEntity('PluginGc_ModuleCommentProvider_EntityData', array(
            'data_provider_name' => $this->sName,
            'data_login'         => $this->sName . '_' . $oData->uid,
            'data_name'          => @$oData->first_name,
            'data_surname'       => @$oData->last_name,
            'data_sex'           => ((@$oData->sex == '0') ? 'man' : ($oData->sex == '1' ? 'woman' : 'other')),
            'data_about'         => @$oData->status_text ? @$oData->status_text : '',
            'data_page'          => str_replace('/', '', str_replace('http://my.mail.ru/mail/', '', $oData->link)),
            'data_birthday'      => date('Y-m-d H:i:s', strtotime(@$oData->birthday)),
            'data_mail'          => @$oData->email,
            'data_photo' => @$oData->has_pic ? @$oData->pic_big : '',
        ));

    }


}