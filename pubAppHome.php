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

	$cities = SelectList("	select id, text from (select '' id, concat('All Cities', ' (', count(1), ')') text, count(1) tot from ".prefix("masajid")."
							union all
							select city as id, concat(city, ' (', count(1), ')') text, count(1) tot
							from ".prefix("masajid")."
							group by city) u
							order by tot desc"
						);	

	echo '<div id="searchForm"></div>';
	MasjidSearchForm($cities);
	echo '<div id="mosqueresults"></div>';

	echo '<a href="' . get_rest_url(null,"wp/v2/posts"). '">Test Wordpress API - '.get_rest_url(null,"wp/v2/posts").'</a><br/>';
	
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
					{ field: '_wpnonce', hidden:false, type:'text'}
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
									async function(response) {
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
				$j('#mosqueresults').html("<div class='alert alert-danger'>"+prettifyJSON(response)+"</div>");
				return;
			}
			
			var html = "<h6>Mosques matching your search: <b>" + response.total + "</b></h6><hr/>";

			var i = 0;
			var matches = response.total;
			while (i < matches) {
				console.log(i);
				var r = response.records[i];
				html += '<b>' + r.masjid_name + '</b>';
				html += '<br/>' + r.address + ',<a href="http://google.com/maps?q='+r.postcode+'">' + r.postcode + '</a><hr/>';
				i++;
				continue;
				
				
				html += '<div class="card shadow">';
				html += '   <div class="card-header">';
				html += '       <span class="card-title">Masjid Name: <b>'+ r.masjid_name +'</b></span>';
				html += '   </div>';
				html += '   <div class="card-body">';
				html += '		<table>';
				html += '		<tr><td>Address</td><td>' + r.address + '</td></tr>';
				html += '		<tr><td>City</td><td>' + r.city + '</td></tr>';
				html += '		<tr><td>Postcode</td><td><a href="http://google.com/maps?q='+r.postcode+'">' + r.postcode + '</a></td></tr>';
				html += '		<tr><td>What3Words</td><td><a href="https://what3words.com/'+r.what3words+'">' + r.what3words + '</a></td></tr>';
				html += '		<tr><td>Telpho</td><td>' + r.phone_no + '</td></tr>';
				html += '		</table>';
				html += '   </div>';
				html += '   <div class="card-footer">';
				html += '       </div>';
				html += '   </div>';
				html += '</div>';
				html += '<br/>';
				i++;
			}			
				
			//html += "<div class='alert alert-success'>"+prettifyJSON(response)+"</div>";
			$j('#mosqueresults').html(html);

		}	



		</script>		
	<?php
	
}


