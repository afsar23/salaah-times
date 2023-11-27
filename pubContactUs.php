<?php
namespace Afsar\wtk;
use Afsar\wtk;

use Formr;

defined('ABSPATH') or die("Cannot access pages directly."); 

require_once 'Formr/class.formr.php';

function ContactUs() {

    global $app;

	?>
	<!-- Form display card w/ options -->
	<div class="card shadow">
		<div class="card-body">
			<div>
			<?php
			frmContactUs();
			?>
			</div>
		</div>
		<div id="response"></div>
	</div>
	<?php
}


function frmContactUs() {
    
		
    $api_url        = get_rest_url(null,"wtk/v1/contactus");		// custom password reset link
	$jsCallBack     = "postFormProcessing";

    // just add 'bootstrap' as your wrapper when you instantiate Formr
    $form1 = new Formr\Formr('bootstrap5');

    $form1->id = "frmContactUs";
    $form1->action = "javascript:submitFormWrapper(this,'".$api_url."',".$jsCallBack.");";
	
	echo $form1->form_open();
    ma_paint_form($form1);
 
    echo $form1->form_close();
    
}



/********************************************
*/
function ma_paint_form($form) {

    global $app;
/*
    if ( is_user_logged_in() ) {    // different instructions depending if user is logged in or not
        // logged in
        echo "<p>Please fill in the form below and we will endeavour to get back to you within 48 hours. 
        We will use the email you registered with to contact you.</p>";
    } else {
        echo "<p>Please complete the form below, and ensure you provide a valid email address so we can respond. 
        If you are registred user, you can login, and we will use your registered email to contact you.</p>";
    }
*/
    $param['name']  = 'f_fullname';
    $param['id']  = 'f_fullname';
    $param['label'] = 'Full Name:';
    $param['value'] = '';
    echo $form->input_text($param);
 
    if ( !is_user_logged_in() )  {    // need to capture email if not logged in
        $param['name']  = 'f_email';
        $param['id']  = 'f_email';
        $param['label'] = 'Email:';
        $param['value'] = '';
        $param['string']='';
        echo $form->input_email($param);
        //echo "<br/>";
    }

    $param['name']  = 'f_subject';
    $param['id']  = 'f_subject';
    $param['label'] = 'Subject (eg Question, Complaint, Suggestion, Registration problem, etc):';
    $param['value'] = '';
    echo $form->input_text($param);
    echo "<br/>";

    $param['name']  = 'f_message';
    $param['id']  = 'f_message';
    $param['label'] = 'Message Details (please be brief and clear):';
    $param['value'] = '';
    $param['string']='rows="3" cols="50"';
    echo $form->input_textarea($param);
    //echo "<br/>";

    if ( is_user_logged_in() )  {    // user not logged in so we must do human check!
        $num1 = rand(3,14);
        $num2 = rand(2,13);
        $param['name']  = 'f_banda';
        $param['id']  = 'f_banda';
        $param['label'] = 'Human check: '.$num1.' + '.$num2.' = ?';
        $param['value'] = '';
        $param['string']='';
        echo $form->input_text($param);
        echo $form->hidden('f_jawab',$num1 + $num2);
        //echo "<br/>";
    }

    $form->submit_button("Send Message &gt;&gt;");
    //echo "<br/>";    
}


#####################################################################################

// js to run when doc ready 
?>

<script>

function submitFormWrapper(frm,api_url, jsCallBack="") {

    var frm = $j("#frmContactUs");

    var form_data=JSON.stringify(frm.serializeObject());
    
    //alert(form_data);

    CallAPI(api_url, form_data, jsCallBack)

}

function postFormProcessing(response) {

	//alert("here!");
	//$j('#response').html(JSON.stringify(response));
	//return;
    
	if (response.status=="success") {
        // do something , eg redirect to login page?
        $j('#response').html("<div class='alert alert-success'>"+response.message+"</div>");
    } else {
        $j('#response').html("<div class='alert alert-danger'>"+response.message+"</div>");
    }
}

</script>

<?php