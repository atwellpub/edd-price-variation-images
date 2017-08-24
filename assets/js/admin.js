jQuery(document).ready(function ($) {

	var orig_send_to_editor = window.send_to_editor;

	jQuery('#upload_image_button').live('click',function(){
		var key = jQuery(this).parent().parent().find('.edd_variation_images').data('key');

		tb_show('', 'media-upload.php?type=image&type=image&amp;TB_iframe=true');
		jQuery('#tab-type_url').hide();
		
		//temporarily redefine send_to_editor()
		window.send_to_editor = function(html) {
			imgurl = jQuery('img',html).attr('src');
			
			if (!imgurl) {
				var array = html.match("src=\"(.*?)\"");
				imgurl = array[1];
			}
			
			jQuery('.edd_variation_images[data-key='+key+']').val(imgurl);
			jQuery('.edd_variation_images[data-key='+key+']').parent().parent().find('.edd_variation_image').attr('src',imgurl);

			tb_remove();
			
			window.send_to_editor = orig_send_to_editor;
		}
	  
		return false;
	 });
	 

});