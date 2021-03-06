{extends file='./comments.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="#" class="btn btn-primary disabled"><i class="icon icon-plus"></i></a>
    </div>
{/block}

{block name="content-body"}

<div class="span12">

    <div class="b-wbox">
        <div class="b-wbox-content nopadding">
            <table class="table table-striped table-condensed topics-list">
                <thead>
                <tr>
                    <th class="span1">ID</th>
                    <th>User</th>
                    <th>Text</th>
                    <th>Target</th>
                    <th>Date</th>
                    <th>Votes</th>
                    <th>Deleted</th>
                    <th class="span2"></th>
                </tr>
                </thead>

                <tbody>
                    {foreach $aComments as $oComment}
                        {$oTarget = $oComment->GetTarget()}
                    <tr class="comment-line">
                        <td class="number">{$oComment->GetId()}</td>
                        <td>
                            {if !is_null($oComment->getGuestLogin())}
                                <strong>
                                    {$aLang.plugin.gc.guest}:&nbsp;{$oComment->getGuestLogin()}</strong>
                                    {if $oComment->getGuestMail()}
                                        /{$oComment->getGuestMail()}
                                    {/if}
                                    {if !is_null($oComment->getTokenId())}
                                        /{$oComment->getGuestSocialLink()}
                                    {/if}

                            {else}
                                <a href="{router page='admin'}users-list/profile/{$oComment->GetUser()->GetId()}/">{$oComment->GetUser()->getDisplayName()}</a>
                            {/if}
                        </td>
                        <td class="name">
                            {$oComment->GetText()}
                        </td>
                        <td>
                            {$oComment->GetTargetType()}
                            {if $oTarget}
                                : {if $oTarget->GetTitle()}
                                    {if $oTarget->GetUrlFull()}
                                        <a href="{$oTarget->GetUrlFull()}">{$oTarget->GetTitle()}</a>
                                    {else}
                                        {$oTarget->GetTitle()}
                                    {/if}
                                {/if}
                            {/if}
                        </td>
                        <td class="center">{$oComment->GetCommentDate()}</td>
                        <td class="number">{$oComment->GetCommentCountVote()}</td>
                        <td class="number">{if $oComment->GetCommentDelete()}{$aLang.action.admin.word_yes}{/if}</td>
                        <td class="center">
                            {if $oTarget}
                            <a href="{$oTarget->getUrl()}#comment{$oComment->getId()}"
                               title="{$aLang.plugin.gc.comment_edit}">
                                <i class="icon icon-note"></i></a>
                            {/if}
                            <a href="#" title="{$aLang.plugin.gc.comment_delete}"
                               onclick="ls.gc.comment_del($(this), '{$aLang.plugin.gc.comment_del_confirm}','{$oComment->getId()}'); return false;">
                                <i class="icon icon-trash"></i></a>

                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>

    {include file="inc.paging.tpl"}

</div>

{/block}