<form class="admwpp-add-gift-voucher-form" onsubmit="event.preventDefault();">
    <div class="input-wrapper left-wrapper">
        <label><?php echo $title; ?></label>
        <div class="input-number">
            <span><?php echo $currency_symbol; ?></span>
            <input class="admwpp-gift-voucher-amount admwpp-gift-voucher-amount-validate" name="admwpp-gift-voucher-amount" type="number" min="<?php echo ADMWPP_MIN_VOUCHER_AMOUNT; ?>" max="<?php echo (int) ADMWPP_MAX_VOUCHER_AMOUNT; ?>" autocomplete="off" val="0" step="<?php echo ADMWPP_VOUCHER_AMOUNT_STEP; ?>" required/>
            <span class='admwpp-message'></span>
        </div>
    </div>
    <div class="input-wrapper right-wrapper">
        <button class="button-link admwpp-add-gift-voucher-btn" data-options_id="<?php echo $options_id; ?>" disabled="disabled">
            <i class="fas fa-shopping-cart"></i>
            <span><?php echo $button_text; ?></span>
        </button>
    </div>
</form>
