<div id="checkout_method_bar">
    <div id="payment_tab_button" class="checkout_tab_button active"
         onclick="show_checkout_area('payment')">
        <h2><?php echo $this->__("Payment") ?></h2>
        <span id="payment_detail">&nbsp;<?php echo $this->__("No Payment") ?></span>
    </div>
    <div id="shipping_tab_button" class="checkout_tab_button"
         onclick="show_checkout_area('shipping')">
        <h2><?php echo $this->__("Shipping") ?></h2>
        <span id="shipping_detail">&nbsp;<?php echo $this->__("No Shipping") ?></span>
    </div>
    <div id="discount_tab_button" class="checkout_tab_button"
         onclick="show_checkout_area('discount')">
        <h2><?php echo $this->__("Discount") ?></h2>
        <span id="discount_detail">&nbsp;<?php echo $this->__("No Discount") ?></span>
    </div>
</div>
<ul id="method_area">
    <li id="billing_method_area" class="checkout_area">
        <div id="order-billing_method"
             class="active"><?php echo $this->getChildHtml('billing_method') ?></div>
    </li>
    <li id="shipping_method_area" class="checkout_area" style="display: none">
        <div id="order-shipping_method"
             class=""><?php echo $this->getChildHtml('shipping_method') ?></div>
    </li>
    <li id="coupon_area" class="checkout_area" style="display: none">
        <div id="order-coupons" class=""><?php echo $this->getChildHtml('coupons') ?></div>
        <div id="order-giftcards"
             class=""> <?php if (Mage::getEdition() == "Enterprise") echo $this->getChildHtml('giftcards') ?></div>
        <div id="order-storecredit"
             class=""><?php if (Mage::getStoreConfig('customer/enterprise_customerbalance/is_enabled') == 1) echo($this->getChildHtml('storecredit')); ?></div>

        <form action="" id="co-payment-form">
            <fieldset>
                <?php echo $this->getChildChildHtml('methods_additional', '', true, true) ?>
                <?php echo $this->getChildHtml('methods') ?>
            </fieldset>
        </form>
    </li>
</ul>
<div id="order-totals" class="order-totals">
    <?php echo $this->getChildHtml('totals') ?>
</div>