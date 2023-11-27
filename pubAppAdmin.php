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


// invoked by short code wtk_main
function AppAdmin() {
	
	?>
	
	<div id="layout" style="width: '100%'; height: 600px;"></div>
   

	<?php
	
}




################## enqueue the below as an external file in the script footer ######
?>
	<script>

	$j('document').ready(function(){
		<?php
			$api_url = get_rest_url(null,"wtk/v1/listdata");
			//echo printable($api_url);
		?>

		init2wuiSettings();

		let pstyle = 'border: 1px solid #efefef; padding: 5px';
		new w2layout({
			box: '#layout',
			name: 'layout',
			panels: [
				{ type: 'top', size: 50, style: pstyle, html: 'top' },
				{ type: 'left', size: 200, style: pstyle, html: '<div id="tableslist" style="height: 500px"></div>' },
				{ type: 'main', style: pstyle, html: '<div id="grid" style="height: 500px"></div>' }
			]
		})
		
		loadTablesList();
			
	});


	function init2wuiSettings() {
		
		w2utils.settings = {
				locale            : "en-uk",
				dateFormat        : "dd-Mon-yyyy",
				timeFormat        : "hh:mi pm",
				currencyPrefix    : "£",
				currencySuffix    : "",
				currencyPrecision : 2,
				groupSymbol       : ",",
				decimalSymbol     : ".",
				shortmonths       : ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
				fullmonths        : ["January", "February", "March", "April", "May", "June", "July", "August",
									 "September", "October", "November", "December"],
				shortdays         : ["M", "T", "W", "T", "F", "S","S"],
				fulldays          : ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
				weekStarts        : "M",        // can be "M" for Monday or "S" for Sunday
				dataType          : 'HTTPJSON', // can be HTTP, HTTPJSON, RESTFULL, RESTFULLJSON, JSON
				phrases           : {},          // empty object for phrases
				dateStartYear     : 1950,       // start year for date-picker
				dateEndYear       : 2030        // end year for date picker
			};
		
	}




	function loadTablesList() {
		/*
		$j('#tableslist').w2grid({
			name   	: 'mytableslist',
			recid	: "table",
			columns: [{ field: 'table', text: 'Select' },],
			records: dbTableColumns()
		});
		*/

		var leftNav = $j('#tableslist').w2sidebar({
			box: '#sidebar',
			name: 'sidebar',
			nodes: [
				{ id: 'ile_', text: 'STANDARD', expanded: true, group: true,
					nodes: [
						{ id: 'options', text: 'Options/Settings' },
						{ id: 'users', text: 'Users' },
						{ id: 'posts', text: 'Posts and Pages' },
					]
				},
				{ id: 'ile_wtk_', text: 'CUSTOM', expanded: true, group: true,
					nodes: [
						{ id: 'usergroups', text: 'User Groups' },
						{ id: 'quotes', text: 'Quotes' },					]
				},
			],
			onClick(event) {
				//console.log(event.object.parent);
				//w2alert(event.target);
				loadw2grid(event.object.parent.id, event.target);
				//w2alert(event.target);				
			}			
		});
	}

	//////////////

	function loadw2grid(prefix,tabName) {
		
		var api_url = '<?php echo $api_url; ?>';

		tabProps = dbTableColumns(tabName);
		
		try {
			w2ui['grid'].destroy();
		} catch(e) {
			// do nothing
		}
		
		let grid = new w2grid({
			name	: 'grid',
			box		: '#grid',
			url    	: api_url,
			httpHeaders: {
				"Content-Type"		: "application/json",
				"Authorization" 	: 'Bearer ' + getCookie('jwt_token'),
				//"X-WP-Nonce"		: wpApiSettings.nonce,
			},
			method 	: 'POST',
			postData: {table: prefix + tabName},
			show: {
				toolbar: true,
				footer: true,	
				toolbarAdd    	: true,
				toolbarEdit   	: true,
				toolbarDelete 	: true,				
				toolbarSave		: true
			},
			style   : 'border: 1px solid red',
			recordHeight:72,
			recid: tabProps.recid,
			columns: tabProps.columns,
			/*
			columns: [
				{ field: 'id', text: 'ID', size: '10%', sortable: true },
				{ field: 'usergroup', text: 'User Group', size: '70%', sortable: true, editable: { type: 'text' } },
				{ field: 'seqno', text: 'Display Order', size: '20%', sortable: true , editable: { type: 'int' }},
			],
			*/
			onAdd: function (event) {
				alert('Add clicked: ');
				//editCondition(0);	
			},			
			onEdit: function (event) {
				console.log(event);
				alert('Edit clicked: ' + event.detail.recid);
				//editCondition(event.id);
			},
			//limit: 16,
			autoload: true,
			onLoad(event) {
				let data = w2utils.clone(event.detail.data)
				data.records.forEach((rec, ind) => {
					rec.recid = 'recid-' + (this.records.length + ind)
				})
				event.detail.data = data
			}			
			
			
		});		
			
	}
	


	function dbTableColumns(tabName) {
		
		var Cols = { 
			"usergroups": {
				recid:		"id",
				columns: [ 	
							{ field: "id", text: "ID"},
							{ field: 'usergroup', text: "User Group"},
							{ field: 'seqno', text: "Seq No"},
						],
	
			 },
			"options": { 
				recid:		"option_id",
				columns: [ 	
							{ field: "option_id", text: "Option ID"},
							{ field: 'option_name', text: "Option Name"},
							{ field: 'option_value', text: "Option Value"},
						],
			},
			"posts": {
				recid:		"ID",
				columns: [
							{ field: "post_date", text: "post_date"},
							{ field: "post_title", text: "post_title"},
							{ field: "post_content", text: "post_content", render: function (rec, extra) {
													return '<div style="overflow-wrap: break-word;">' + rec.post_content.replace(/(<([^>]+)>)/gi, "") + '</div>';
												}},
							{ field: "post_status", text: "post_status"},
							{ field: "guid", text: "guid"},
							{ field: "post_type", text: "post_type"},
						],
		}};					

		return Cols[tabName];
			
	}		
	</script>

<?php

