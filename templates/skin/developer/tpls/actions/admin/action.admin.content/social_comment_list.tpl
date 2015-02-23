{extends file="_index.tpl"}

{block name="layout_vars"}
    {$sMainMenuItem='content'}
{/block}

{block name="content-bar"}

{/block}

{block name="content-body"}
    <style>
        .social-form .control-label {
            padding-top: 8px;
        }

    </style>
    <div class="span12 social-form">
    <div class="b-wbox">
    <div class="b-wbox-header">
        <h3 class="b-wbox-header-title">
            {$aLang.plugin.gc.admin_social_page_title}
        </h3>
    </div>
    <div class="b-wbox-content">
    <div class="b-wbox-content">
    <form method="post" action="" enctype="multipart/form-data" id="social-setting" class="form-horizontal uniform">
    <input type="hidden" name="security_ls_key" value="{$LIVESTREET_SECURITY_KEY}"/>

    {* РЕЖИМ *}
    <div class="control-group" style="min-height: 112px;">
        <label for="banner_name" class="control-label">
            {$aLang.plugin.gc.admin_social_mode}:
        </label>

        <div class="controls">
            <div class="col-md-8">
                <select name="guest_comment_mode" id="guest_comment_mode">
                    <option value="both" {if $_aRequest.guest_comment_mode=='both'}selected{/if}>
                        {$aLang.plugin.gc.admin_social_mode_both}
                    </option>
                    <option value="social" {if $_aRequest.guest_comment_mode=='social'}selected{/if}>
                        {$aLang.plugin.gc.admin_social_mode_social}
                    </option>
                    <option value="mail" {if $_aRequest.guest_comment_mode=='mail'}selected{/if}>
                        {$aLang.plugin.gc.admin_social_mode_mail}
                    </option>
                </select>
                <span class="help-block">{$aLang.plugin.gc.admin_social_mode_description}</span>
            </div>
        </div>
    </div>

    {* email *}
    <div class="control-group" style="min-height: 112px;">
        <label for="banner_name" class="control-label">
            {$aLang.plugin.gc.admin_social_email}:
        </label>

        <div class="controls">
            <div class="col-md-8">
                <input type="text" name="admin_social_email" value="{$_aRequest.admin_social_email}"/>
                <span class="help-block">{$aLang.plugin.gc.admin_social_email_notice}</span>
            </div>
        </div>
    </div>

    {* ГИТХАБ *}
    <div class="control-group">
        <label for="banner_name" class="control-label">
            {$aLang.plugin.gc.github}:
        </label>

        <div class="controls">
            <div class="col-md-4">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.client_id}"
                       type="text"
                       id="github_client_id"
                       name="guest_comment_providers[github][github_client_id]"
                       value="{$_aRequest.guest_comment_providers.github.github_client_id}"/>
            </div>
            <div class="col-md-4">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.secret_key}"
                       type="text"
                       id="github_secret_key"
                       name="guest_comment_providers[github][github_secret_key]"
                       value="{$_aRequest.guest_comment_providers.github.github_secret_key}"/>
            </div>
            <div class="col-md-4">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.application_name}"
                       type="text"
                       id="application_name"
                       name="guest_comment_providers[github][application_name]"
                       value="{$_aRequest.guest_comment_providers.github.application_name}"/>
            </div>
        </div>
    </div>

    {* ОДНОКЛАССНИКИ *}
    <div class="control-group">
        <label for="banner_name" class="control-label">
            {$aLang.plugin.gc.od}:
        </label>

        <div class="controls">
            <div class="col-md-4">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.client_id}"
                       type="text"
                       id="od_client_id"
                       name="guest_comment_providers[od][od_client_id]"
                       value="{$_aRequest.guest_comment_providers.od.od_client_id}"/>
            </div>
            <div class="col-md-4">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.secret_key}"
                       type="text"
                       id="od_secret_key"
                       name="guest_comment_providers[od][od_secret_key]"
                       value="{$_aRequest.guest_comment_providers.od.od_secret_key}"/>
            </div>
            <div class="col-md-4">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.public_key}"
                       type="text"
                       id="od_public_key"
                       name="guest_comment_providers[od][od_public_key]"
                       value="{$_aRequest.guest_comment_providers.od.od_public_key}"/>
            </div>
        </div>
    </div>

    {* Вконтакт *}
    <div class="control-group">
        <label for="banner_name" class="control-label">
            {$aLang.plugin.gc.vk}:
        </label>

        <div class="controls">
            <div class="col-md-6">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.client_id}"
                       type="text"
                       id="vk_client_id"
                       name="guest_comment_providers[vk][vk_client_id]"
                       value="{$_aRequest.guest_comment_providers.vk.vk_client_id}"/>
            </div>
            <div class="col-md-6">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.secret_key}"
                       type="text"
                       id="vk_secret_key"
                       name="guest_comment_providers[vk][vk_secret_key]"
                       value="{$_aRequest.guest_comment_providers.vk.vk_secret_key}"/>
            </div>
        </div>
    </div>

    {* ФЕЙСБУК *}
    <div class="control-group">
        <label for="banner_name" class="control-label">
            {$aLang.plugin.gc.fb}:
        </label>

        <div class="controls">
            <div class="col-md-6">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.client_id}"
                       type="text"
                       id="fb_client_id"
                       name="guest_comment_providers[fb][fb_client_id]"
                       value="{$_aRequest.guest_comment_providers.fb.fb_client_id}"/>
            </div>
            <div class="col-md-6">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.secret_key}"
                       type="text"
                       id="fb_secret_key"
                       name="guest_comment_providers[fb][fb_secret_key]"
                       value="{$_aRequest.guest_comment_providers.fb.fb_secret_key}"/>
            </div>
        </div>
    </div>

    {* ТВИТТЕР *}
    {*<div class="control-group">*}
        {*<label for="banner_name" class="control-label">*}
            {*{$aLang.plugin.gc.tw}:*}
        {*</label>*}

        {*<div class="controls">*}
            {*<div class="col-md-6">*}
                {*<input class="input-wide"*}
                       {*placeholder="{$aLang.plugin.gc.client_id}"*}
                       {*type="text"*}
                       {*id="tw_client_id"*}
                       {*name="guest_comment_providers[tw][tw_client_id]"*}
                       {*value="{$_aRequest.guest_comment_providers.tw.tw_client_id}"/>*}
            {*</div>*}
            {*<div class="col-md-6">*}
                {*<input class="input-wide"*}
                       {*placeholder="{$aLang.plugin.gc.secret_key}"*}
                       {*type="text"*}
                       {*id="tw_secret_key"*}
                       {*name="guest_comment_providers[tw][tw_secret_key]"*}
                       {*value="{$_aRequest.guest_comment_providers.tw.tw_secret_key}"/>*}
            {*</div>*}
        {*</div>*}
    {*</div>*}

    {* МОЙ МИР *}
    <div class="control-group">
        <label for="banner_name" class="control-label">
            {$aLang.plugin.gc.mm}:
        </label>

        <div class="controls">
            <div class="col-md-6">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.client_id}"
                       type="text"
                       id="mm_client_id"
                       name="guest_comment_providers[mm][mm_client_id]"
                       value="{$_aRequest.guest_comment_providers.mm.mm_client_id}"/>
            </div>
            <div class="col-md-6">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.secret_key}"
                       type="text"
                       id="mm_secret_key"
                       name="guest_comment_providers[mm][mm_secret_key]"
                       value="{$_aRequest.guest_comment_providers.mm.mm_secret_key}"/>
            </div>
        </div>
    </div>

    {* ЯНДЕКС *}
    <div class="control-group">
        <label for="banner_name" class="control-label">
            {$aLang.plugin.gc.ya}:
        </label>

        <div class="controls">
            <div class="col-md-6">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.client_id}"
                       type="text"
                       id="ya_client_id"
                       name="guest_comment_providers[ya][ya_client_id]"
                       value="{$_aRequest.guest_comment_providers.ya.ya_client_id}"/>
            </div>
            <div class="col-md-6">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.secret_key}"
                       type="text"
                       id="ya_secret_key"
                       name="guest_comment_providers[ya][ya_secret_key]"
                       value="{$_aRequest.guest_comment_providers.ya.ya_secret_key}"/>
            </div>
        </div>
    </div>

    {* ГУГЛ *}
    <div class="control-group">
        <label for="banner_name" class="control-label">
            {$aLang.plugin.gc.g}:
        </label>

        <div class="controls">
            <div class="col-md-6">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.client_id}"
                       type="text"
                       id="g_client_id"
                       name="guest_comment_providers[g][g_client_id]"
                       value="{$_aRequest.guest_comment_providers.g.g_client_id}"/>
            </div>
            <div class="col-md-6">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.secret_key}"
                       type="text"
                       id="g_secret_key"
                       name="guest_comment_providers[g][g_secret_key]"
                       value="{$_aRequest.guest_comment_providers.g.g_secret_key}"/>
            </div>
        </div>
    </div>

    {* ЛИКЕНИД *}
    <div class="control-group">
        <label for="banner_name" class="control-label">
            {$aLang.plugin.gc.li}:
        </label>

        <div class="controls">
            <div class="col-md-6">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.client_id}"
                       type="text"
                       id="li_client_id"
                       name="guest_comment_providers[li][li_client_id]"
                       value="{$_aRequest.guest_comment_providers.li.li_client_id}"/>
            </div>
            <div class="col-md-6">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.secret_key}"
                       type="text"
                       id="li_secret_key"
                       name="guest_comment_providers[li][li_secret_key]"
                       value="{$_aRequest.guest_comment_providers.li.li_secret_key}"/>
            </div>
        </div>
    </div>

    {* ИНСТАГРАМ *}
    <div class="control-group">
        <label for="banner_name" class="control-label">
            {$aLang.plugin.gc.i}:
        </label>

        <div class="controls">
            <div class="col-md-6">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.client_id}"
                       type="text"
                       id="i_client_id"
                       name="guest_comment_providers[i][i_client_id]"
                       value="{$_aRequest.guest_comment_providers.i.i_client_id}"/>
            </div>
            <div class="col-md-6">
                <input class="input-wide"
                       placeholder="{$aLang.plugin.gc.secret_key}"
                       type="text"
                       id="i_secret_key"
                       name="guest_comment_providers[i][i_secret_key]"
                       value="{$_aRequest.guest_comment_providers.i.i_secret_key}"/>
            </div>
        </div>
    </div>

    <br/><br/>

    <input type="submit" name="submit_social" value="{$aLang.plugin.gc.save}"/>
    <input type="submit" name="cancel" value="{$aLang.plugin.gc.cancel}"/>

    </form>
    </div>
    </div>
    </div>
    </div>
{/block}