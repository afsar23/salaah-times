/*******
*
*
*/
var $ = jQuery.noConflict();
// acts as a kind of global properties collection to overcome scoping issues in call back functions
glbAfsar = defineGlobalObjects(); 		// attaches methods/properties to the glbAfsar object so they can accessed from any context


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function defineGlobalObjects() {
	
	obs = {
		PopupMessage 	: 	function(msg) {
			w2popup.open({ 
				name	: 'pop_waiter',
				width   : 500, 
				height  : 120, 
				modal   : true,
				body    : '<div class="w2ui-centered"><div style="padding: 10px;">'+
						  '        <div class="w2ui-spinner" '+
						  '            style="width: 22px; height: 22px; position: relative; top: 6px;"></div>'+
						  '  ' + msg + 
						  '</div></div>'
			});
		},
		
		now : function() {
			return w2utils.formatTime((new Date()), 'hh24:mi:ss');
		},
		
		results: []
		
	};	


	return obs;
}



// supplementary common functions

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




function lookupText(vid,vlist) {
	try {
		var found =  vlist.find(function(vitem) {
			return vitem.id == vid;
		});
		if (typeof found === 'undefined') return '';
		return found.text;
	}
	catch(e) {
		return '';
	}
}




 /**
 * sends a request to the specified url from a form. this will change the window location.
 * @param {string} path the path to send the post request to
 * @param {object} params the paramiters to add to the url
 * @param {string} [method=post] the method to use on the form
 */

function testpost(path, pdata, recid) {			

	var req = "{\"cmd\":\"get\",\"recid\":\""+ recid + "\"}";
	
	pdata = {postData: pdata, request: req };	
		
	var out = "URL = " + path + "</br/>pdata = " + JSON.stringify(pdata) + "<br/><h4>results</h4>";

	$('#testpost').html('<pre>'+ out +'</pre>');	
	
	$.post(path,pdata).done(function(response) {
		try {
			$('#testpost').append('<pre>' + JSON.stringify(response) + '</pre>');	
		} catch(err) {
			$('#testpost').append('<pre>'+response+'</pre>');											
		}
	});
	
}










// add unique recid column to grid records that don't have a primary key defined
function addRecId(records) {
	var rec = records;
	for (var i=0; i<records.length; i++) {
		rec[i]['recid'] = i+1;
	}
	return rec;
}

// remove recid column from grid records that don't need a primary key / recid before saving
function removeRecId(records) {
	var rec = records;
	for (var i=0; i<records.length; i++) {
		delete rec[i]['recid'];
	}
	return rec;
}


function showField(frm,fldid,blnShow,setReq) {
	
	try {
		
		var fld = document.getElementById(fldid);	
		
		if (!blnShow) frm.record[fldid]='';			
		frm.set(fldid,{ required: (blnShow && setReq) });  
		
		fld.readOnly = (blnShow) ? false : true;
		fld.disabled = (blnShow) ? false : true;
		
		var lbl = document.getElementById('lbl_'+fldid);
		if (blnShow) {
			$('#'+'lbl_'+fldid).removeClass("dimmed");
		} else {
			$('#'+'lbl_'+fldid).addClass("dimmed");
		} 		
	}
	catch(err) {
		w2alert(fldid + '\n' + err.message);
	}
}


function myurl(script, args) {
	
	var url = cfg.plugin_url + script; 
	
	if (url.indexOf("?")>=0) {
		url += '&' + args;
	} else {
		url += '?' + args;
	}
								
	return url;
}

function jDate(sdate,dmy=false) {
	
	if (sdate==null) return "  ";
	//if (typeof sdate === 'undefined') return "";
	
	if (dmy==true) {
		var d = sdate.split("-");
		m = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
		return new Date(d[2],m.findIndex(function(e) { return (e==d[1]);}),d[0]);
	} else {
		return new Date(sdate);
	}
}	


// disp key value pairs
function disp_record(obj) {
	try {
		var rtn = "<table><tbody>";
		for(key in obj){
			if ((obj[key]!=null) && (key!='w2ui'))  {
				rtn += "<tr><td>" + key + "</td><td>" + obj[key] + "</td></tr>";
			}
		}
		rtn += "</tbody></table>";
	} catch(e) {
		rtn = e.message;
	}		
	return rtn;	
}

