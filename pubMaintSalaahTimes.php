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
require_once plugin_dir_path( __FILE__ ) . 'upload_csv.php';
	

// invoked by shortcode in pubControler script
function tabMaintSalaahTimes() {
	
	$grd_url = get_rest_url(null,"wtk/v1/listdata?table=salaahtimes"); 
	$frm_url = get_rest_url(null,"wtk/v1/maintdata?table=salaahtimes"); 
	
	
	UploadCSV();
	
	echo '		
		<hr/>
		<div id="grdgeneric" style="width: 100%; height: 450px;"></div>
		';
		
	?>
	
	<script>
	
		$('document').ready(function(){

			init2wuiSettings();		
			$('#grdgeneric').w2grid(grdConfig());
			
		});
		
		
		function grdConfig() {
			
			var grd = {  
			
				name	: 'grd_salaahtimes', 
				//method	: 'GET',		// for load once
				method	: 'POST',		// for dynamic server side load/refresh
				header  : '<b>Manage Salaaah Times</b>',
				url     : '<?php echo $grd_url; ?>',		// comment out if loading once from server
				httpHeaders: {
					"Content-Type"		: "application/json",
					"Authorization" 	: 'Bearer ' + getCookie('jwt_token'),
					//"X-WP-Nonce"		: wpApiSettings.nonce,
				},
				limit	: 30,
				autoload: false,
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
					toolbarSearch  : true,   // indicates if toolbar search controls are visible
					toolbarAdd     : false,   // indicates if toolbar add new button is visible
					toolbarEdit    : true,   // indicates if toolbar edit button is visible
					toolbarDelete  : false,   // indicates if toolbar delete button is visible
					toolbarSave    : false,   // indicates if toolbar save button is visible
					selectionBorder: true,   // display border around selection (for selectType = 'cell')
					recordTitles   : true,   // indicates if to define titles for records
					skipRecords    : false,    // indicates if skip records should be visible
					toolbarInput   : false,      // hides search input on the toolbar
					searchAll 	   : false       // hides 'All Fields' option in the search dropdown					
				},
			
				//style 	: 'padding:5px; background-color:yellow',
				columns	: [  			
					{ field: 'id', text: 'ID', size:'60px' }, 
					{ field: 'masjid_id', text: 'Masjid Id', size:'220px', sortable:true},
					{ field: 'd_date', text: 'Date', size:'90px', sortable:true},
					{ field: 'fajr_jamah', text: 'Fajr', render: function (rec, fld) {
							var html = '<div>'+ w2utils.formatTime(fld.value, 'hh24:mi') + '</div>';
							return html;
							}, editable: { type: 'time' }},
					{ field: 'zuhr_jamah', text: 'Zhur', editable: { type: 'time' }},
					{ field: 'asr_jamah', text: 'Asr', editable: { type: 'time' }},
					{ field: 'maghrib_jamah', text: 'Maghrib', editable: { type: 'time' }},
					{ field: 'isha_jamah', text: 'Isha', editable: { type: 'time' }},
				],
				multiSort : true,
				sortData: [
					{ field: 'masjid_id', direction: 'asc' },
					{ field: 'd_date', direction: 'asc' },					
				],	
				
				onAdd: function (event) {
					//showForm({});	
				},
				
				onEdit: function (event) {
					//testpost(w2ui['grd_clients'].url, w2ui['grd_clients'].postData,0);
					console.log(this.records[this.getSelection(true)]);
					//showForm(this.records[this.getSelection(true)]);
				},
				onDelete: function (event) {
					if (event.detail.force == true) {
						console.log(event);
						console.log(this.records[this.getSelection(true)]);
						this.delete(function (data) {	
							console.log(data);
							if (data.status == 'success') {
								var recid = data.record.recid;
								w2ui['grd_salaahtimes'].reload(function() {
									w2ui['grd_salaahtimes'].selectNone();
									w2ui['grd_salaahtimes'].select(recid);
									w2ui['grd_salaahtimes'].scrollIntoView(w2ui['grd_salaahtimes'].get(recid,true));										
								});
								this.close();;
							} else {
								w2alert(data.message);
								alert('here');
							}
						});
					}
				},
				
				
				onDblClick: function (event) {
					event.preventDefault;
					//showForm(this.records[this.getSelection(true)]);
				},
				
				
			};
			
			//w2alert("here!");
			return grd
			
		}


		function showForm(recid) {
			
			if (w2ui.frm_usergroup) {
				w2ui.frm_usergroup.destroy();
			}

			new w2form(frmUserGroupConfig(recid));			
			w2popup.open({
				name	: 'mypopup',
				title   : 'Form in a Popup',
				body    : '<div id="form" style="width: 100%; height: 100%;"></div>',
				style   : 'padding: 15px 0px 0px 0px',
				width   : 500,
				height  : 280,
				showMax : true,
				async onToggle(event) {
					await event.complete
					w2ui.frm_usergroup.resize();
				},
				onClose : function(event) {
					//console.log("onClose!");
					//console.log(event);
					//console.log(this);
				}
			})
			.then((event) => {
				w2ui.frm_usergroup.render('#form')
			});

		}

		function frmUserGroupConfig(rec) {
			
			var frmConfig = {
				name     : 'frm_usergroup',
				//url      : '<?php echo $frm_url; ?>' + '?table=usergroups',					/// change this!!!!
				style    : 'border: 0px; background-color: transparent',
				//msgRefresh : function() {return 'Wait...'; },
				msgSaving : 'Saving...',  
				focus: 'usergroup',
				fields: [				
					{ name: 'id'
						, type: 'int'
						, html: { caption: 'ID', span:'8 wrapper_id', attr: 'disabled=true'}
					},
					{ name: 'usergroup'
						, type: 'text'
						, html: { caption: 'User Group', span:'8 usergroup wrapper_usergroup mainfield' }
						, options: { attr: ['width=300px'] }
						, required: true 
					},
					{ name: 'seqno'
						, type: 'int'
						, html: { caption: 'Seq No', span:'8 wrapper_seqno' }
						, required: true  
					},
				],
				recid: 'id',
				record: rec,
				actions: {
					
					"save": function () {	// save method automatically triggers validation
						
						//this.postData.recordCopy = JSON.parse(JSON.stringify(this.record));		// quickest way to clone an object				
						
						//this.postData.recordCopy.date_joined = w2utils.formatDate(new Date(this.record.date_joined),'yyyy-mm-dd');
						//this.postData.recordCopy.date_left = w2utils.formatDate(new Date(this.record.date_left),'yyyy-mm-dd');
						this.url = '<?php echo $frm_url; ?>';
						this.save(function (data) {	
							console.log(data);
							if (data.status == 'success') {
								var recid = data.record.recid;
								w2ui['grd_usergroups'].reload(function() {
									w2ui['grd_usergroups'].selectNone();
									w2ui['grd_usergroups'].select(recid);
									w2ui['grd_usergroups'].scrollIntoView(w2ui['grd_usergroups'].get(recid,true));										
								});
								w2popup.close('mypopup');
							} else {
								w2alert(data.message);
								alert('here');
							}
						});
						this.url = null;
					},
					
					"cancel": function () {
						w2popup.close('mypopup');
					},
				},				
									
				onLoad: function(event) {
					event.onComplete = function() {
						rec = this.record;	
						//alert("Loaded:/n"+JSON.stringify(rec));
						//rec.date_joined = w2utils.formatDate(new Date(rec.date_joined),"dd-Mon-yyyy");
						//rec.date_left = w2utils.formatDate(new Date(rec.date_left),"dd-Mon-yyyy");		
					};
				},
										
				
			};
			
			return frmConfig;								
		
		}
				

	</script>	

	<?php			

}  // end 


