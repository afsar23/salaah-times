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


function TabulatorExample() {
	
	?>
	
	<script type="text/javascript" src="https://oss.sheetjs.com/sheetjs/xlsx.full.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>
	
    <div id="grid" xstyle="height: 450px"></div>
    
	<div>
		<button id="refresh-data">Refresh Data</button>
		<button id="download-csv" class="primary">Download CSV</button>
		<button id="download-json">Download JSON</button>
		<button id="download-xlsx">Download XLSX</button>
		<button id="download-pdf">Download PDF</button>
		<button id="download-html">Download HTML</button>
	</div>
	<div id="tabGrid" xstyle="height: 450px"></div>

	<?php
	
}
################## enqueue the below as an external file in the script footer ######
?>
	<script>

	$j('document').ready(function(){
		<?php
			$api_url = get_rest_url(null,"wtk/v1/listdata?table=salaahtimes"); 
			//echo printable($api_url);
		?>
		
		api_url = '<?php echo $api_url; ?>';
		//load2wuigrid(api_url);
		//alert(api_url);
		loadtabulatorgrid(api_url);
		
	});

	
	function loadtabulatorgrid(api_url) {

		// tabulator
		// var tabGrid = TabulatorGrid('<?php echo $api_url; ?>'+'_tab');
		var tabGrid = TabulatorGrid(api_url);
	
		function TabulatorGrid(api_url) {

			//Build Tabulator
			var tabgrid = new Tabulator("#tabGrid", {
				ajaxURL:api_url,
				ajaxConfig:{
					method:"GET", //set request type to Position
					headers: {
						"Content-type": 'application/json; charset=utf-8', //set specific content type
						"Authorization" 	: 'Bearer ' + getCookie('jwt_token'),
						//"X-WP-Nonce"		: wpApiSettings.nonce,				
					},
				},				
				ajaxResponse:function(api_url, params, response){
					//url - the URL of the request
					//params - the parameters passed with the request
					//response - the JSON object returned in the body of the response.
					//alert(prettifyJSON(response));
					if (response.status == 'success') {
						return response; //return the records property of a response json object
					} else {
						w2alert("<h6>Error!!!</h6>" + response.message);
						return response;
					}
				},
				pagination:true, //enable pagination
				paginationMode:"remote", //enable remote pagination
				paginationInitialPage:1, //optional parameter to set the initial page to load   
				paginationSize:30,
				paginationSizeSelector:[10, 50, 1000],
				paginationCounter:"rows",
				movableColumns:true,
								
				//responsiveLayout:true,
				//layout: 'fitDataTable',
				//layout: "fitDataTable",
				//layout:"fitDataFill",
				//layout:"fitColumns", //fit columns to width of table (optional)
				autoColumns:false,
				xheight:800,
				maxHeight:"100%",
				xminHeight:"300",

				index: "id",   // primary key identifier for each row
				/*
				groupBy:function(data){
						//data - the data object for the row being grouped
						return data.gender + " - " + data.curr_location; //groups by data and age
					},
				*/
				
				columns	: [  			
					{ field: 'id', title: 'ID', size:'60px' }, 
					{ field: 'mosque_id', title: 'Mosque Id', size:'220px', headerSort:true},
					{ field: 'd_date', title: 'Date', size:'90px', headerSort:true},
					{ field: 'fajr_jamah', title: 'Fajr'},
					{ field: 'zuhr_jamah', title: 'Zhur'},
					{ field: 'asr_jamah', title: 'Asr'},
					{ field: 'maghrib_jamah', title: 'Maghrib'},
					{ field: 'isha_jamah', title: 'Isha'},
				],				
				
				
				
				
				placeholder:"No data found",
				ajaxLoaderLoading: "<div style='display:inline-block;paddding:0px;'>Please wait...</div>",
				//ajaxLoaderError: "<div style='display:inline-block;padding:0px;'>Ooopsy!</div>",
				//groupBy:"agent_id",

			});

			// fix header mis-alignment after sorting when horizontal scroll present
			tabgrid.on( "headerClick", function(e, column){
			tabgrid.columnManager.scrollHorizontal( 'left' ); // new line to restore header scroll position
			tabgrid.scrollToColumn( column.getField(), "middle", false ); //scroll column which is clicked to the middle if not already visible
			});

			return tabgrid;

		}

		function scrollto() {
			tabGrid.selectRow(4284);
			tabGrid.scrollToRow(4284, "middle", false)
			.then(function(){
				//run code after row has been scrolled to
			   // alert("scrolled ok");
			})
			.catch(function(error){
				//handle error scrolling to row
				//alert("scroll failed");
			});
		}

		function ajaxLoading() {
			
		}
			
		//trigger download of data.csv file
		document.getElementById("refresh-data").addEventListener("click", function(){
			tabGrid.setData();
		});
		
		//trigger download of data.csv file
		document.getElementById("download-csv").addEventListener("click", function(){
			tabGrid.download("csv", "data.csv");
		});

		//trigger download of data.json file
		document.getElementById("download-json").addEventListener("click", function(){
			tabGrid.download("json", "data.json");
		});

		//trigger download of data.xlsx file
		document.getElementById("download-xlsx").addEventListener("click", function(){
			tabGrid.download("xlsx", "data.xlsx", {sheetName:"My Data"});
		});

		//trigger download of data.pdf file
		document.getElementById("download-pdf").addEventListener("click", function(){
			tabGrid.download("pdf", "data.pdf", {
				orientation:"portrait", //set page orientation to portrait
				title:"Example Report", //add title to report
			});
		});

		//trigger download of data.html file
		document.getElementById("download-html").addEventListener("click", function(){
			tabGrid.download("html", "data.html", {style:true});
		});

		// end tabulator
	}
	
	
	</script>

<?php


