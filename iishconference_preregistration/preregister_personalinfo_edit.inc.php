<?php 
/**
 * TODOEXPLAIN
 */
function preregister_personalinfo_edit_form( $form, &$form_state ) {
	$ct=0;

	// PERSONAL INFO

	$tmpIs = ( isset($_SESSION['storage']['isexistinguser']) ? $_SESSION['storage']['isexistinguser'] : 'NULL');

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => "
<style>
<!--
#edit-email {
	border: 0px;
}
// -->
</style>
",
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="step">Step 2 of ' . getSetting('steps') . '</div>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_personalpage">Personal info</span>',
		);

	$form['firstname'] = array(
		'#type' => 'textfield',
		'#title' => 'First name',
		'#size' => 40,
		'#maxlength' => 255,
		'#required' => TRUE,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#default_value' => isset( $_SESSION['storage']['preregister_personalinfo_firstname'] ) ? $_SESSION['storage']['preregister_personalinfo_firstname'] : NULL, 
		);

	$form['lastname'] = array(
		'#type' => 'textfield',
		'#title' => 'Last name',
		'#size' => 40,
		'#maxlength' => 255,
		'#required' => TRUE,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#default_value' => isset( $_SESSION['storage']['preregister_personalinfo_lastname'] ) ? $_SESSION['storage']['preregister_personalinfo_lastname'] : NULL, 
		);

	$gender_options = getArrayOfGender();

	$form['gender'] = array(
		'#title' => 'Gender',
		'#type' => 'select',
		'#options' => $gender_options,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#default_value' => isset( $_SESSION['storage']['preregister_personalinfo_gender'] ) ? $_SESSION['storage']['preregister_personalinfo_gender'] : NULL, 
		);

	$form['organisation'] = array(
		'#type' => 'textfield',
		'#title' => 'Organisation',
		'#size' => 40,
		'#maxlength' => 255,
		'#required' => TRUE,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#default_value' => isset( $_SESSION['storage']['preregister_personalinfo_organisation'] ) ? $_SESSION['storage']['preregister_personalinfo_organisation'] : NULL, 
		);

	$form['department'] = array(
		'#type' => 'textfield',
		'#title' => 'Department',
		'#size' => 40,
		'#maxlength' => 255,
		'#required' => TRUE,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#default_value' => isset( $_SESSION['storage']['preregister_personalinfo_department'] ) ? $_SESSION['storage']['preregister_personalinfo_department'] : NULL, 
		);

	$form['email'] = array(
		'#type' => 'textfield',
		'#title' => 'E-mail',
		'#size' => 40,
		'#maxlength' => 100,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#default_value' => trim($_SESSION["conference"]["user_email"]), 
		'#attributes' => array('readonly' => 'readonly'), 
		);

	$form['student'] = array(
		'#type' => 'checkboxes',
		'#options' => array(
						'y' => 'Please check if you are a (PhD) student',
						),
		'#default_value' => isset( $_SESSION['storage']['preregister_personalinfo_student'] ) ? $_SESSION['storage']['preregister_personalinfo_student'] : array(), 
		);

	if ( getSetting('show_cv') == 1 ) {
		$form['cv'] = array(
			'#type' => 'textarea',
			'#title' => 'Curriculum Vitae',
			'#description' => '<em>(max. 200 words)</em>',
			'#rows' => 2,
			'#default_value' => isset( $_SESSION['storage']['preregister_personalinfo_cv'] ) ? $_SESSION['storage']['preregister_personalinfo_cv'] : NULL, 
			);
	}

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

	// + + + + + + + + + + + + + + + + + + + + + + + +

	// ADDRESS

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_personalpage">Address</span>',
		);

	$form['city'] = array(
		'#type' => 'textfield',
		'#title' => 'City',
		'#size' => 40,
		'#maxlength' => 255,
		'#required' => TRUE,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#default_value' => isset( $_SESSION['storage']['preregister_personalinfo_city'] ) ? $_SESSION['storage']['preregister_personalinfo_city'] : NULL, 
		);

	$list_of_countries = getArrayOfCountries();

	$form['country'] = array(
		'#title' => 'Country',
		'#type' => 'select',
		'#options' => $list_of_countries,
		'#required' => TRUE,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#default_value' => isset( $_SESSION['storage']['preregister_personalinfo_country'] ) ? $_SESSION['storage']['preregister_personalinfo_country'] : NULL, 
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

	// + + + + + + + + + + + + + + + + + + + + + + + +

	// COMMUNICATION MEANS

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_personalpage">Communication Means</span>',
		);

	$form['phone'] = array(
		'#type' => 'textfield',
		'#title' => 'Phone number',
		'#size' => 40,
		'#maxlength' => 100,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#default_value' => isset( $_SESSION['storage']['preregister_personalinfo_phone'] ) ? $_SESSION['storage']['preregister_personalinfo_phone'] : NULL, 
		);

	$form['mobile'] = array(
		'#type' => 'textfield',
		'#title' => 'Mobile number',
		'#size' => 40,
		'#maxlength' => 100,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#default_value' => isset( $_SESSION['storage']['preregister_personalinfo_mobile'] ) ? $_SESSION['storage']['preregister_personalinfo_mobile'] : NULL, 
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="extra_uitleg">Please enter international numbers (including country prefix etc.)</span>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

	// + + + + + + + + + + + + + + + + + + + + + + + +

	$form['submit'] = array(
		'#type' => 'submit',
		'#value' => 'Next'
		);

	// EXISTING USERS
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<br><br><br><br>',
		);

	return $form;
}

/**
 * TODOEXPLAIN
 */
function preregister_personalinfo_edit_form_submit( $form, &$form_state ) {
	// Trigger multistep, there are more steps.
	$form_state['rebuild'] = TRUE;

	$_SESSION['storage']['preregister_personalinfo_firstname'] = $form_state['values']["firstname"];
	$_SESSION['storage']['preregister_personalinfo_lastname'] = $form_state['values']["lastname"];
	$_SESSION['storage']['preregister_personalinfo_gender'] = $form_state['values']["gender"];
	$_SESSION['storage']['preregister_personalinfo_organisation'] = $form_state['values']["organisation"];
	$_SESSION['storage']['preregister_personalinfo_department'] = $form_state['values']["department"];
	$_SESSION['storage']['preregister_personalinfo_city'] = $form_state['values']["city"];
	$_SESSION['storage']['preregister_personalinfo_country'] = $form_state['values']["country"];
	$_SESSION['storage']['preregister_personalinfo_phone'] = $form_state['values']["phone"];
	$_SESSION['storage']['preregister_personalinfo_mobile'] = $form_state['values']["mobile"];
	$_SESSION['storage']['preregister_personalinfo_student'] = $form_state['values']["student"];
	if ( getSetting('show_cv') == 1 ) {
		$_SESSION['storage']['preregister_personalinfo_cv'] = $form_state['values']["cv"];
	}

	$form_state['storage']['step'] = 'preregister_personalinfo_preview_form';
}

