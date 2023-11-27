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

    $api_url        = get_rest_url(null,"wtk/v1/uploadfile");		// custom user registration end point
	$jsCallBack     = "postFormProcessing";

	?>
		<div id="response"></div>
		<form id="importform" action="javascript:;" onsubmit="submitForm(this,'<?=$api_url?>',<?=$jsCallBack?>);"> 
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

		function frmUploadSubmit(frm, restURL, jsCallBack) {

			event.preventDefault();
			alert("Here!");
			var file = $('input#csv')[0].files[0];
			var formData = new FormData();

			formData.append( 'file', file );

			// append any other formData
			// e.g. any id fields, etc as necessary here

			$.ajax({
				url: restURL,
				data: formData,
				processData: false,
				contentType: false,
				method: 'POST',
				cache: false,
				beforeSend: function ( xhr ) {
				  xhr.setRequestHeader( 'X-WP-Nonce', pluginConfig.restNonce );
				}
			})
			.done(function(data) {
				// handle success
			})
				.fail(function(jqXHR, textStatus, errorThrown) {
				// handle failure
			});
		}
			
	</script>	
	
<?php
