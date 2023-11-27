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



// invoked by short code wrapper wtk_maint_usergroups
function ApiLogs() {
	
	$api_url = get_rest_url(null,"wtk/v1/listdata"); 
	echo '<a href="'.$api_url.'">'.$api_url.'</a>';
	
	?>	
		<div id="apilogs" style="border:1px solid red; width:100%;height: 500px;"></div>
		<div id="afsardebug" xstyle="border:1px solid red; width:100%;"></div>
		
	<?php

}


################## enqueue the below as an external file in the script footer ######
?>
	<script>
		
	$('document').ready(function(){

		init2wuiSettings();
		
		// widget configuration
		let gridCfg = {
			name: 'grid',
			method	: 'POST',		// for dynamic server side load/refresh
			//header  : '<b>Manage User Groups</b>',
			url     : '<?php echo get_rest_url(null,"wtk/v1/listdata"); ?>',		// comment out if loading once from server
			httpHeaders: {
				"Content-Type"		: "application/json",
				"Authorization" 	: 'Bearer ' + getCookie('jwt_token'),
				//"X-WP-Nonce"		: wpApiSettings.nonce,
			},
			postData: {table: "apicalls", filter: "api_response is not null"},
			limit	: 5,
			autoload: true,
			recid	: 'id',
			show: {
				header         : false,  // indicates if header is visible
				toolbar        : true,  // indicates if toolbar is visible
				footer         : true,  // indicates if footer is visible
				columnHeaders  : true,   // indicates if columns is visible
				lineNumbers    : false,  // indicates if line numbers column is visible
				expandColumn   : false,  // indicates if expand column is visible
				selectColumn   : false,  // indicates if select column is visible
				emptyRecords   : true,   // indicates if empty records are visible
				toolbarReload  : true,   // indicates if toolbar reload button is visible
				toolbarColumns : false,   // indicates if toolbar columns button is visible
				toolbarSearch  : false,   // indicates if toolbar search controls are visible
				toolbarAdd     : false,   // indicates if toolbar add new button is visible
				toolbarEdit    : false,   // indicates if toolbar edit button is visible
				toolbarDelete  : false,   // indicates if toolbar delete button is visible
				toolbarSave    : false,   // indicates if toolbar save button is visible
				selectionBorder: true,   // display border around selection (for selectType = 'cell')
				recordTitles   : true,   // indicates if to define titles for records
				skipRecords    : false,    // indicates if skip records should be visible
				toolbarInput   : false,      // hides search input on the toolbar
				//searchAll 	   : false       // hides 'All Fields' option in the search dropdown					
			},
		
			//style 	: 'padding:5px; background-color:yellow',
			columns: [ 	
						{ field: "id", text: "<b>ID</b>", sortable:true},
						{ field: 'start_time', text: "<b>Start Time</b>", sortable:true},
						{ field: 'end_time', text: "<b>End Time</b>", sortable:true},
						{ field: 'api_user', text: "<b>User</b>", 
							render: (rec, fld) => { return JSON.parse(fld.value).user_login; }
						},
						{ field: 'api_request', text: "<b>Referrer</b>", 
							render: (rec, fld) => { return JSON.parse(fld.value).referer; }
						},
						{ field: 'api_request', text: "<b>Api Route</b>", 
							render: (rec, fld) => { return JSON.parse(fld.value).route; }													
						},
						{ field: 'api_request', text: "<b>Api Request</b>", 
							info: {
								render: (rec, ind, col_ind) => { return prettifyJSON(JSON.parse(rec.api_request)) }
							}						
						},
						{ field: 'api_response', text: "<b>Api Response</b>", 
							info: {
								render: (rec, ind, col_ind) => { return prettifyJSON(JSON.parse(rec.api_response)) }
							}
						}						
					],
			sortData: [	{ field: 'id', direction: 'desc' } ],
			onRequest: function(event) {
				console.log(event);
			},
			onRequest: function(event) {
				console.log("OnSubmit");
				console.log(event);
			},
			onLoad: function(event) {
				console.log(event);
				var data = event.detail.data;
				console.log(data);
				//$("#afsardebug").html(prettifyJSON(data));		
			}
		}


		let grd = new w2grid(gridCfg);
		// initialization
		grd.render('#apilogs');
		
	});
	</script>	

<?php			

