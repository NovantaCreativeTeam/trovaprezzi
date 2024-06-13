 {$message}
<div class="panel">
		<div class="panel-heading">
            {l s='Configuration' mod='trovaprezzi'}
        </div>
         <form method="POST" action="{$action}">
            <div class="form-wrapper">
                <div class="form-group clearfix">
                    <label class="control-label col-lg-4">{l s='Export type' mod='trovaprezzi'}</label>
                    <div class="col-lg-2">
                        <select name="tp_export_type" id="tp_export_type">
                            {foreach $export_types as $type}
                                <option values={$type['value']} {if $type['value'] == $tp_export_type}selected="selected"{/if}>
                                    {$type['name']}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                <div class="form-group clearfix">
                    <label class="control-label col-lg-4">{l s='Carrier' mod='trovaprezzi'}</label>
                    <div class="col-lg-2">
                        <select name="tp_carrier" id="tp_carrier">
                            {foreach $available_carriers as $carrier}
                                <option values={$carrier['id_carrier']} {if $carrier['id_carrier'] == $tp_carrier}selected="selected"{/if}>
                                    {$carrier['carrier_name']}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                <div class="form-group clearfix">
                <label class="control-label col-lg-4">{l s='Categories to export' mod='trovaprezzi'}</label>
                    <div class="col-lg-8">
                        {$tp_categories_tree}
                    </div>
                </div>

                <div class="form-group clearfix">
                    <label class="control-label col-lg-4">{l s='Enable Product Variants' mod='trovaprezzi'}</label>
                    <div class="col-lg-6">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="tp_product_variants_enabled" id="tp_product_variants_enabled_on" value="1" {if $tp_product_variants_enabled}checked="checked"{/if}>
                            <label for="tp_product_variants_enabled_on">{l s='Yes' d='Admin.Global'}</label>
                            <input type="radio" name="tp_product_variants_enabled" id="tp_product_variants_enabled_off" value="0" {if !$tp_product_variants_enabled}checked="checked"{/if}>
                            <label for="tp_product_variants_enabled_off">{l s='No' d='Admin.Global'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>

                <div class="form-group clearfix">
                    <label class="control-label col-lg-4">{l s='Force Unit Price' mod='trovaprezzi'}</label>
                    <div class="col-lg-6">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="tp_force_unit_price" id="tp_force_unit_price_on" value="1" {if $tp_force_unit_price}checked="checked"{/if}>
                            <label for="tp_force_unit_price_on">{l s='Yes' d='Admin.Global'}</label>
                            <input type="radio" name="tp_force_unit_price" id="tp_force_unit_price_off" value="0" {if !$tp_force_unit_price}checked="checked"{/if}>
                            <label for="tp_force_unit_price_off">{l s='No' d='Admin.Global'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>

                <div class="form-group clearfix">
                    <label class="control-label col-lg-4">{l s='Enable Trusted Program' mod='trovaprezzi'}</label>
                    <div class="col-lg-6">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="tp_trusted_program_enabled" id="tp_trusted_program_enabled_on" value="1" {if $tp_trusted_program_enabled}checked="checked"{/if}>
                            <label for="tp_trusted_program_enabled_on">{l s='Yes' d='Admin.Global'}</label>
                            <input type="radio" name="tp_trusted_program_enabled" id="tp_trusted_program_enabled_off" value="0" {if !$tp_trusted_program_enabled}checked="checked"{/if}>
                            <label for="tp_trusted_program_enabled_off">{l s='No' d='Admin.Global'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>

                <div class="form-group clearfix">
                    <label class="control-label col-lg-4">{l s='Merchant Key (for Trusted Program)' mod='trovaprezzi'}</label>
                    <div class="col-lg-6">
                        <input type="text" name="tp_merchant_key" id="tp_merchant_key" value="{$tp_merchant_key}"/>
                    </div>
                </div>

            </div>
            <div class="panel-footer">
                <button type="submit" name="submitExportConfiguration" value="1" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i> {l s='Save & Export' mod='trovaprezzi'}
                </button>
                <button type="submit" name="submitConfiguration" value="1" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i> {l s='Save' mod='trovaprezzi'}
                </button>
            </div>
         </form>
         
</div>