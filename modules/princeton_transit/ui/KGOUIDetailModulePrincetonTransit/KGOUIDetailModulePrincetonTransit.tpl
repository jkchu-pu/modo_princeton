{object_wrapper}
    {if $this|field_is_set:'serviceImage'}
        <div class="kgoui_detail_module_princeton_transit_service_image">
            {if $this|field_is_set:'serviceURL'}
                <a href="{$this|field:'serviceURL'}"{if $this|option:'external'} target="_blank"{/if}>
            {/if}

            {$this|field:'serviceImage'}

            {if $this|field_is_set:'serviceURL'}
                </a>
            {/if}
        </div>
   {/if}

    <div class="kgo_inset kgoui_detail_header">
        {field_is_set_wrapper field="title" tag="h1" class="kgoui_detail_title"}

        {field_is_set_wrapper field="description" class="kgo_secondary_text"}

        {include region="status"}
    </div>

    {include region="content"}
{/object_wrapper}
