{extends file="/ui/KGOUIListItem/KGOUIListItem.tpl"}

{block name="kgoui_list_item_content"}
    {if $this|option:'featured'}
        {field_is_set_wrapper field="hero" class="kgoui_list_item_hero"}
    {else}
        {field_is_set_wrapper field="thumbnail" class="kgoui_list_item_thumbnail"}
    {/if}

    <div class="kgoui_list_item_textblock{if $this|option:'fixedHeight'} kgo_ellipsis{/if}">
        {if $this|option:'featured' && !$this|field_is_set:'hero'}
            {field_is_set_wrapper field="thumbnail" class="kgoui_list_item_thumbnail"}
        {/if}
        {field_is_set_wrapper field="label" class="kgoui_list_item_label"}
        <span class="kgoui_list_item_title">{$this|field:'title'}</span>
        <span class="kgo_screen_reader_only">{$this->getScreenReaderStatusText()}</span>
        {field_is_set_wrapper field="subtitle" class="kgoui_list_item_subtitle"}
    </div>
{/block}
