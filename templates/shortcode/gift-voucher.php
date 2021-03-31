<form class="admwpp-add-gift-voucher-form" onsubmit="event.preventDefault();">
    <div class="input-wrapper left-wrapper">
        <label><?php echo $title; ?></label>
        <div class="input-number">
            <span><?php echo $currency_symbol; ?></span>
            <input name="admwpp-gift-voucher-amount" type='number' min="1" autocomplete="off" val=0 required/>
            <span class='admwpp-message'></span>
        </div>
    </div>
    <div class="input-wrapper right-wrapper">
        <button class="button-link admwpp-add-gift-voucher-btn" data-options_id="<?php echo $options_id; ?>">
            <i class="fas fa-shopping-cart"></i>
            <span><?php echo $button_text; ?></span>
        </button>
    </div>
</form>
