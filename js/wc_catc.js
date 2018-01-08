jQuery(document).ready(function($) {
	$('body').on('click', '.product_notification_wrapper', function(e) {
			$('.product_notification_wrapper').parent().hide();
	});
	//=====================================================================
	//	Build dynamic add to cart message
	//=====================================================================
	var notificationContent = '';

	$('body').on('click', '.ajax_add_to_cart', function(){
		$('.woocommerce-message').remove();
		if ($('body').hasClass('woocommerce-wishlist'))
		{
			var imgSrc = $(this).parents('tr').find('img.attachment-shop_thumbnail').attr('src');
			var prodTitle = $(this).parents('tr').find('.product-name a').html();
		}
		else 
		{
			var imgSrc = $(this).parents('li').find('img.attachment-shop_catalog').attr('src');
			var prodTitle = $(this).parents('li').find('.product-title-link').html();
		}

		console.log(imgSrc + ' // ' + prodTitle);
		if ( typeof imgSrc != 'undefined' && typeof prodTitle != 'undefined' )
		{
			notificationContent = '<div class="woocommerce-message"><div class="product_notification_wrapper"><div class="product_notification_background" style="background-image:url(' + imgSrc + ')"></div><div class="product_notification_text">&quot;' + prodTitle + '&quot;' + addedToCartMessage +'</div></div></div>';
		}
		else 
		{
			notificationContent = false;
		}
	});

	//======================================================
	//  Display notification on ajax add to cart
	//======================================================
	$(document).on('added_to_cart', function(event, data) {
		if (notificationContent != false)
		{
			$('#content').append(notificationContent);
		}
	});
});