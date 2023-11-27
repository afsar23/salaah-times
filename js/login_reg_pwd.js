/*******
*
*
*/

	jQuery(document).ready(function() {
		jQuery(".tab_content_login").hide();
		jQuery("ul.tabs_login li:first").addClass("active_login").show();
		jQuery(".tab_content_login:first").show();
		jQuery("ul.tabs_login li").click(function() {
			jQuery("ul.tabs_login li").removeClass("active_login");
			jQuery(this).addClass("active_login");
			jQuery(".tab_content_login").hide();
			var activeTab = jQuery(this).find("a").attr("href");
			jQuery(activeTab).show();
			return false;
		});

	});

