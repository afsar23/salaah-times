// define global object variable
var gAfsar = {};
var $j = jQuery;


async function submitForm(frm,api_url, jsCallBack="") {
    // get form data
    
	var this_form = $j(frm);

	//alert(JSON.stringify(frm));
    var form_data=JSON.stringify(this_form.serializeObject());
     //$("#last_api_call").html(prettifyJSON(form_data));
      //return;

	CallAPI(api_url, form_data,jsCallBack);
	//postFormDataAsJson(api_url, form_data,jsCallBack);

}


async function CallAPI(api_url, form_data,jsCallBack) {

	var url = api_url;

    //alert("Nonce='" + wpApiSettings.nonce + "'");
	//alert(form_data);
	result = {};

	fetch(url, {
			method: 'POST', // or 'PUT'
			headers: {
				"Content-Type"		: "application/json",
				"Authorization" 	: 'Bearer ' + getCookie('jwt_token'),
				//"X-WP-Nonce"		: wpApiSettings.nonce,
			},
			body: form_data,
		}).then( async(response) => {
				const result = await response.json();
				// the call back function will normally live in the page script containing the form being processed
				//alert(JSON.stringify(result));
				if (typeof jsCallBack === 'function') {
					//alert('Callback!');
					//alert(JSON.stringify(result));
					jsCallBack(result);
				}         
			}
		).catch(err => {    
				if (typeof jsCallBack === 'function') {
					//alert('Callback!');
					//alert(JSON.stringify(err));
					jsCallBack(err);
				}  
			}
		);

}


function HandleApiResponse(api_url, input_data=null,jsCallBack) {

  fetch(api_url, {
    method: 'POST', // or 'PUT'
    headers: {
      'Content-Type'  : 'application/json',
      'Authorization' : 'Bearer ' + getCookie('jwt_token'),
    },
    body: input_data,
  })
  .then( async(response) => {
		const result = await response.json();
		jsCallBack(result);       
    })
  .catch(error => {   
    jsCallBack(error);
  });

}



function prettifyJSON(j) {
  return '<pre class="formatted_json" style="margin:0px;padding:1px">' + JSON.stringify(j,null,2) + '</pre>';
}

// remove any prompt messages
function clearResponse(){
    $j('#response').html('');
}

// function to make form values to json format
$j.fn.serializeObject = function(){

    var o = {};
    var a = this.serializeArray();
    $j.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

// function to set cookie
function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

// get or read cookie
function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' '){
            c = c.substring(1);
        }

        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}