jQuery(document).ready(function($){
	jQuery('.edd_price_options input').on( 'change',function() {
		jQuery('.edd-ul-price-variation-images li').hide();
		jQuery('.edd-ul-price-variation-images li[data-id='+jQuery(this).filter(':checked').val()+']').show();
	});
});