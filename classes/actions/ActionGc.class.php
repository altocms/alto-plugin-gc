<?php
/* ---------------------------------------------------------------------------
 * @Plugin Name: Guest Comments
 * @Plugin Id: gc
 * @Plugin URI:
 * @Description:
 * @Author: andreyv
 * @Author URI: http://gladcode.ru
 * ----------------------------------------------------------------------------
 */

class PluginGc_ActionGc extends Action {

    public function Init() {
        // TODO: Implement Init() method.
    }


    protected function RegisterEvent() {

        // Перенаправим запросы всех провайдеров в один экшен
        foreach (Config::Get('plugin.gc.providers') as $sProviderName => $aProviderData) {
            $this->AddEvent($sProviderName, 'EventAuth');
        }
    }


    /**
     * Получает подтверждение из социальной сети, что пользователь валидный и
     * пишет в сессию пользователю разрешение на комментирвоание топика
     *
     * @return bool
     */
    public function EventAuth() {

        /** @var string $sProviderName Наименование провайдера авторизации */
        $sProviderName = Router::GetActionEvent();

        /** @var AuthProvider $oProvider Текущий провайдер */
        if (!($sProviderName && $oProvider = E::Module('PluginGc\CommentProvider')->GetProviderByName($sProviderName))) {
            return $this->_NotFound();
        }

        // Куда возвращаемся?
        $sReturnPath = E::Module('Session')->Get('return_path');

        // Получим токен пользователя
        /** @var PluginGc_ModuleCommentProvider_EntityUserToken $oToken */
        $oToken = $oProvider->getToken();
        if (!$oToken || !@$oToken->getTokenData()) {
            // Пользователь отказался (
            Router::Location($sReturnPath ? $sReturnPath : '');

            return TRUE;
        }

        // Если пользователь есть, авторизуем его и уходим. Но здесь может быть два варианта:
        // Если ид. пользователя отдается с токеном, то второй запрос не формируем, для проверки
        // пользователя хватит и одного. Если же ид. не получили, например от одноклассников, то
        // здесь считаем, что пользователя нет и проверку на его наличие будем делать только
        // после получения полных данных от социальной сети

        // Сначала ищем пользователя по токену
        if ($oUserFindByToken = E::Module('PluginGc\CommentProvider')->GetUserByTokenData($oToken)) {
            $this->_AuthUser($oUserFindByToken);
            Router::Location($this->_ReturnPath());

            return TRUE;
        }

        // Теперь по идентификатору пользователя, который может быть в токене
        if ($oToken->getTokenProviderUserId() && $oUser = E::Module('PluginGc\CommentProvider')->GetUserByToken($oToken)) {
            // Вот и всё
            $this->_AuthUser($oUser);
            Router::Location($this->_ReturnPath());

            return TRUE;
        } else {
            // Пользователь первый раз авторизуется на нашем сайте и его необходимо создать
            // Или в токене не было ссылок ид пользователя для поиска.
            $oUserData = $oProvider->GetUserData($oToken);

            // Обновим токен и поищем пользователя
            if ($oUserData && !$oToken->getTokenProviderUserId()) {
                $oToken->setTokenProviderUserId(str_replace($oUserData->getDataProviderName() . '_', '', $oUserData->getDataLogin()));

                if ($oUser = E::Module('PluginGc\CommentProvider')->GetUserByToken($oToken)) {
                    $this->_AuthUser($oUser);

                    Router::Location($this->_ReturnPath());

                    return TRUE;
                }
            }

            // Зафиксируем пользователя
            if ($oUserData && $oToken) {

                E::Module('PluginGc\CommentProvider')->SaveUserData($oUserData, $oToken);

                $this->_AuthUser($oToken);
            }
        }

        Router::Location($this->_ReturnPath());

        return TRUE;
    }

    private function _ReturnPath() {

        $sReturnPath = E::Module('Session')->Get('return_path');

        return $sReturnPath ? $sReturnPath : '';
    }


    /**
     * Возвращаемся назад и разрешаем пользователю комментировать топик.
     *
     * @param PluginGc_ModuleCommentProvider_EntityUserToken $oToken
     */
    protected function _AuthUser($oToken) {

        E::Module('Session')->Set('comment_token_id', $oToken->getTokenId());
        E::Module('Session')->SetCookie('comment_token_hash', md5(Config::Get('plugin.gc.salt') . $oToken->getTokenData()), 'P7D', false);
//        E::Module('Message')->AddNoticeSingle(E::Module('Lang')->Get('plugin.gc.ok'), E::Module('Lang')->Get('attention'), TRUE);
    }

}

// EOF