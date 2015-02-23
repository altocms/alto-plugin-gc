<div {if $right_comment}style="display: none;" id="guest-comment-mail-panel"{/if}>
<div class="row">
    <div class="col-md-12">
        <div class="bg bg-warning wsw mb15">
            {$aLang.plugin.gc.guest_mail_comment}
        </div>
    </div>
</div>

    <table class="gc-table">
        <tr>
            <td class="gc-inputs">
                <input type="text"
                       id="guest_mail"
                       placeholder="{$aLang.plugin.gc.guest_mail}"
                       name="guest_mail"
                       value="{$sGuestMail}"
                       class="input-text form-control"/>
                <br/>
                <input type="text"
                       id="guest_login"
                       placeholder="{$aLang.plugin.gc.guest_login}"
                       name="guest_login"
                       value="{$sGuestLogin}"
                       class="input-text form-control"/>
            </td>
            <td>
                <script>
                    $(function () {
                        $('.comment-image').attr('src', "{router page='commentcaptcha'}?n=" + Math.random());
                        ls.hook.add('ls_comments_load_after', function () {
                            $('#guest_mail').add('#guest_login').add('#input-comment-captcha').val('');
                            $('.comment-image').attr('src', "{router page='commentcaptcha'}?n=" + Math.random());
                        });
                    })
                </script>
                <div class="form-group captcha-input">
                    <div class="input-group">
                        <label for="input-comment-captcha" class="input-group-addon">
                            <img src="" onclick="this.src='{router page='commentcaptcha'}?n='+Math.random();" class="comment-image"/>
                        </label>

                    </div>
                </div>
            </td>
            <td>
                <input type="text" name="comment-captcha" id="input-comment-captcha" value=""
                       maxlength="3" class="form-control captcha-input input-text" required/>
            </td>
        </tr>
    </table>
    <br/><br/>

</div>
{if $right_comment}
    <a href="#"
       class="btn btn-light btn-big corner-no pull-right"
       onclick="$('#guest-comment-mail-panel').slideToggle(); return false;">
        {$aLang.plugin.gc.open_mail}
    </a>
    <br/>
    <br/>
{/if}