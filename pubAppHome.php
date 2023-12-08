<?php
namespace Afsar\wtk;
use Afsar\wtk;

/*
user interface related to manage users
*/

defined('ABSPATH') or die("Cannot access pages directly.");   

######################################################################################





// any files to include?

function appHome($pg_atts) {
	
	//echo "<h3>Search Mosques</h3>";

	$cities = SelectList("	select '' id, concat('All Cities', ' (', count(1), ')') text from ".prefix("masajid")."
							union all
							select city as id, concat(city, ' (', count(1), ')') text
							from ".prefix("masajid")."
							group by city
							order by id"
						);	

	echo '<div id="searchForm"></div>';
	MasjidSearchForm($cities);
	echo '<div id="mosqueresults"></div>';

	//$cities = (object)($cities);
	//echo  printable($cities);
}




function MasjidSearchForm($cities) {
	
	?>
		<script>	

		w2SearchForm();
		
		function w2SearchForm() {  //$(function () {
			//alert("herr!");
			var api_url = '<?php echo get_rest_url(null,"wtk/v1/mosques");?>';		
			jsCallBack = 'DisplayResults';
			
			mosquecities = <?php echo json_encode($cities);?>;
			let myForm = new w2form({
				box: '#searchForm',
				name: 'searchForm',
				header: '<b>Search Mosques</b>',	
				fields : [
					{ field: 'masjid_name', html: {label: 'Masjid Name'}, type: 'text' },
					{ field: 'postcode', html: {label: 'Postcode'},  type: 'text' },
					{ field: 'city', html: {label: 'Town/City'},   type: 'list',
						options: { items: mosquecities }
					},
					{ field: '_wpnonce', hidden:true, type:'text'}
				],
				record: {
					_wpnonce: '<?php echo wp_create_nonce(wtkNonceKey());?>'
				},
				actions: {
					search: {
						text: 'Search',
						//class: 'w2ui-btn-green',
						//style: 'text-transform: uppercase',
						onClick(event) {
							if (this.validate().length == 0) {
								HandleApiResponse(api_url, JSON.stringify(this.getCleanRecord()),				
									function(response) {
										DisplayResults(response);
									}
								);
							}
						}
					},					
					reset: {
						text: 'Reset',
						onClick(event) {
							this.clear();
						}
					}					
				}				
			});
		//});
		}
		
		function DisplayResults(response) {

			if (response.status=="error") {
				$j('#mosqueresults').html("<div class='alert alert-danger'>"+response.message+"</div>");
				return;
			}
			
			//present the results
			var html = "Mosques matching your search: " + response.total + "<br/>";

				var i = 0, len = response.total;
				if (len>4) { len = 4; }				
				while (i++ < len) {
					var r = response.records[i];
					//html += '<br/>';
					html += '<div class="card shadow">';
					html += '   <div class="card-header">';
					html += '       <span class="card-title">Masjid Name: <b>'+ r.masjid_name +'</b></span>';
					html += '   </div>';
					html += '   <div class="card-body">';
					html += '		<table>';
					html += '		<tr><td>Address</td><td>' + r.address + '</td></tr>';
					html += '		<tr><td>City</td><td>' + r.city + '</td></tr>';
					html += '		<tr><td>Postcode</td><td>' + r.postcode + '</td></tr>';
					html += '		<tr><td>What3Words</td><td>' + r.what3words + '</td></tr>';
					html += '		<tr><td>Telpho</td><td>' + r.phone_no + '</td></tr>';
					html += '		</table>';
					html += '   </div>';
					html += '   <div class="card-footer">';
					html += '       </div>';
					html += '   </div>';
					html += '</div>';
					html += '<br/>';
				}			
				
			html += "<div class='alert alert-success'>"+prettifyJSON(response)+"</div>";
			$j('#mosqueresults').html(html);

		}	



		</script>		
	<?php
	
}


