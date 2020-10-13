<div class="alertChange hide">
    <i class="fa fa-exclamation-triangle"></i>
    <p><?=str_replace("[yes]", "<a href=\"javascript:;\" class=\"alert-widgets-action-button confirm-Edit-Custom-Widget\">", str_replace("[/yes]", "</a>", str_replace("[no]", "<a href=\"javascript:;\" class=\"alert-widgets-action-button\" data-dismiss=\"modal\">", str_replace("[/no]" , "</a>", system_showText(LANG_SITEMGR_EDIT_CUSTOM_WIDGET)))));?></p>
</div>