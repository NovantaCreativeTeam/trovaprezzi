<!-- JQuery Here -->
<script type="text/javascript">
    window.onload = function () {
        window._tt = window._tt || [];
        window._tt.push({ event: "setAccount", id: '{$tp_merchant_key}' });
        window._tt.push({ event: "setOrderId", order_id: '{$tp_orderid}' });
        window._tt.push({ event: "setEmail", email: '{$tp_email}' });
        {foreach $tp_products as $product}
            window._tt.push({ event: "addItem", sku: '{$product.product_reference}', product_name: '{$product.product_name}' });
        {/foreach}
        window._tt.push({ event: "setAmount", amount: '{$tp_total_paid}' });
        window._tt.push({ event: "orderSubmit"});
    }
</script>