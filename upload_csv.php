<?php
namespace Afsar\wtk;
use Afsar\wtk;

/*
Mohammed Afsar
Single Page Application for the Plugin Application
Supported by a customer API registration routes
Uses the w2ui library
*/

defined('ABSPATH') or die("Cannot access pages directly.");   

######################################################################################


function UploadCSV() {

    $api_url        = get_rest_url(null,"wtk/v1/import_csv");		// custom user registration end point
	$jsCallBack     = "postFormProcessing";


	echo '<a href="' . get_rest_url(null,"wp/v2/posts"). '">Test Wordpress API - '.get_rest_url(null,"wp/v2/posts").'</a><br/>';
	
	?>
		<div id="response"></div>
		<form id="importform" action="javascript:;" onsubmit="submitFormCustom(this,'<?=$api_url?>',<?=$jsCallBack?>);"> 
		<?php 
			wp_nonce_field(wtkNonceKey(), '_wpnonce');
		?>
		<input id="csv" name="file" type="file" accept=".csv" required />
		<button type="submit" class="btn btn-primary">Upload</button>
		
		</form>
	<?php
	
}		

?>

	<script>

		function postFormProcessing(response) {
			if (response.status=="success") {
				// do something , eg redirect to login page?
				$j('#response').html("<div class='alert alert-success'>"+response.message+"</div>");
				if (response.hasOwnProperty('redirect')) {
					location.href = response.redirect;
				}
			} else {
				$j('#response').html("<div class='alert alert-danger'>"+response.message+"</div>");
			}
		}	

		function submitFormCustom(frm, restURL, jsCallBack) {			

			event.preventDefault();
			var file = $('input#csv')[0].files[0];
			var formData = new FormData();

			formData.append( 'file', file );

			// append any other formData
			// e.g. any id fields, etc as necessary here

			$j.ajax({
				url: restURL,
				data: formData,
				processData: false,
				contentType: false,  //'multipart/form-data',
				method: 'POST',
				cache: false,
				beforeSend: function ( xhr ) {
				  //xhr.setRequestHeader( 'X-WP-Nonce', pluginConfig.restNonce );
				  xhr.setRequestHeader( 'Authorization', 'Bearer ' + getCookie('jwt_token') );
				}
			})
			.done(function(data) {
				console.log(data);
				msg = prettifyJSON(data);
				$j('#response').html("<div class='alert alert-success'>" + msg + "</div>");
				//if (response.hasOwnProperty('redirect')) {
				//	location.href = response.redirect;
				//}
				//} else {
				//	$j('#response').html("<div class='alert alert-danger'>"+response.message+"</div>");
				//}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR);
				$j('#response').html("<div class='alert alert-danger'>"+textStatus+"</div>");
			});
		}
			
	</script>	
	
<?php
