<?php 
/**
 * TODOEXPLAIN
 */
function preregister_personalinfo_preview_form( $form, &$form_state ) {
	$ct=0;

	// PERSONAL INFO

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="step">Step 3 of ' . getSetting('steps') . '</div>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_personalpage">Please check your personal info and go to the next screen</span>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">First name </span>',
		'#prefix' => '<div class="container-inline">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => isset( $_SESSION['storage']['preregister_personalinfo_firstname'] ) ? $_SESSION['storage']['preregister_personalinfo_firstname'] : NULL, 
		'#suffix' => '</div>', 
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">Last name </span>',
		'#prefix' => '<div class="container-inline">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => isset( $_SESSION['storage']['preregister_personalinfo_lastname'] ) ? $_SESSION['storage']['preregister_personalinfo_lastname'] : NULL, 
		'#suffix' => '</div>', 
		);

	$gender_options = getArrayOfGender();

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">Gender </span>',
		'#prefix' => '<div class="container-inline">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => isset( $_SESSION['storage']['preregister_personalinfo_gender'] ) ? $gender_options[$_SESSION['storage']['preregister_personalinfo_gender']] : NULL, 
		'#suffix' => '</div>', 
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">Organisation </span>',
		'#prefix' => '<div class="container-inline">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => isset( $_SESSION['storage']['preregister_personalinfo_organisation'] ) ? $_SESSION['storage']['preregister_personalinfo_organisation'] : NULL, 
		'#suffix' => '</div>', 
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">Department </span>',
		'#prefix' => '<div class="container-inline">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => isset( $_SESSION['storage']['preregister_personalinfo_department'] ) ? $_SESSION['storage']['preregister_personalinfo_department'] : NULL, 
		'#suffix' => '</div>', 
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">E-mail </span>',
		'#prefix' => '<div class="container-inline">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => trim($_SESSION["conference"]["user_email"]), 
		'#suffix' => '</div>', 
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">(PhD) Student? </span>',
		'#prefix' => '<div class="container-inline">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => ( isset( $_SESSION['storage']['preregister_personalinfo_student']['y'] ) && $_SESSION['storage']['preregister_personalinfo_student']['y'] === 'y' ) ? 'yes' : 'no', 
		'#suffix' => '</div>', 
		);

	if ( getSetting('show_cv') == 1 ) {

		$a = str_replace("\n", "<br>\n", $_SESSION['storage']['preregister_personalinfo_cv']);
		$a = show_more($a, 300, 'divcv');

		$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<span class="label_personalpage">Curriculum Vitae </span>',
			'#prefix' => '<div class="container-inline">', 
			'#suffix' => '</div>', 
			);
		$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => $a,
			'#prefix' => '<div class="container-inline">', 
			'#suffix' => '</div>', 
			);

	}

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">City </span>',
		'#prefix' => '<div class="container-inline">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => isset( $_SESSION['storage']['preregister_personalinfo_city'] ) ? $_SESSION['storage']['preregister_personalinfo_city'] : NULL, 
		'#suffix' => '</div>', 
		);

	$list_of_countries = getArrayOfCountries();

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">Country </span>',
		'#prefix' => '<div class="container-inline">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => isset( $_SESSION['storage']['preregister_personalinfo_country'] ) ? $list_of_countries[$_SESSION['storage']['preregister_personalinfo_country']] : NULL, 
		'#suffix' => '</div>', 
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">Phone number </span>',
		'#prefix' => '<div class="container-inline">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => isset( $_SESSION['storage']['preregister_personalinfo_phone'] ) ? $_SESSION['storage']['preregister_personalinfo_phone'] : NULL, 
		'#suffix' => '</div>', 
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">Mobile number </span>',
		'#prefix' => '<div class="container-inline">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => isset( $_SESSION['storage']['preregister_personalinfo_mobile'] ) ? $_SESSION['storage']['preregister_personalinfo_mobile'] : NULL, 
		'#suffix' => '</div>', 
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

	// + + + + + + + + + + + + + + + + + + + + + + + +

	// 
	$form['submit_back'] = array(
		'#type' => 'submit',
		'#value' => 'Back',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '&nbsp; &nbsp; &nbsp;',
		);

	$form['submit'] = array(
		'#type' => 'submit',
		'#value' => 'Next',
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
function preregister_personalinfo_preview_form_submit( $form, &$form_state ) {

	// Trigger multistep, there are more steps.
	$form_state['rebuild'] = TRUE;

	$values = $form_state['values'];

	if ( $form_state['clicked_button']['#value'] == $values['submit_back'] ) {
		// BACK
		$form_state['storage']['step'] = 'preregister_personalinfo_edit_form';
	} elseif ( $form_state['clicked_button']['#value'] == $values['submit'] ) {
		// NEXT
		$form_state['storage']['step'] = 'preregister_typeofregistration_form';
	} else {
		die('ERROR 325684: unknown button clicked ' . $form_state['clicked_button']['#value']);
	}
}
