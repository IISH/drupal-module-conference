<?php 
/**
 * TODOEXPLAIN
 */
function preregister_completed_form( $form, &$form_state ) {
	$ct=0;
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="step">Step 9 of ' . getSetting('steps') . '</div>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="eca_remark heavy">Completed. You are now pre-registered for the ' . getSetting('long_code_year') . ' conference. In a few minutes you will receive by e-mail a copy of your pre-registration.<br><br></span>',
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="eca_warning heavy">It is not possible to modify your pre-registration anymore. If you would like to modify your registration please send an email to ' . encryptEmailAddress(getSetting('email_fromemail')) . '.<br><br></span>',
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="eca_remark heavy">Go to your <a href="/' . getSetting('pathForMenu') . getSetting('urlpersonalpage') . '">personal page</a>.<br><br></span>',
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>',
		);

	if ( getSetting('live') != 1 ) {

		$form['submit_back'] = array(
			'#type' => 'submit',
			'#value' => 'Back',
			);

	}

	// EXISTING USERS
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<br><br><br><br>',
		);

	$dateid = getSetting('date_id');
	$user_id = getIdLoggedInUser();

	$query = "SELECT participant_date_id FROM participant_date WHERE user_id=" . getIdLoggedInUser() . " AND date_id=" . $dateid;
	$participant_date_id = ifEmpty(executeQueryReturnFields($query, "participant_date_id"), 0);

	// 
	$oEmail = new class_conference_email( getSetting('email_template_normal_registration') );

	// send confirmation email to current user
	sendConfirmationEmail(getIdLoggedInUser(), $oEmail->getSubject(), $oEmail->getBody());

	// send e-mail's to session participants
	sendConfirmationEmailToSessionParticipants();

	// set als geregistreerd, verander 999 in 0
	$query = "UPDATE participant_date SET participant_state_id=0 WHERE participant_date_id=" . $participant_date_id;
	executeQuery($query);

	return $form;
}

/**
 * TODOEXPLAIN
 */
function preregister_completed_form_submit( $form, &$form_state ) {
	$form_state['rebuild'] = TRUE;

	$values = $form_state['values'];

	if ( $form_state['clicked_button']['#value'] == $values['submit_back'] ) {
		// BACK
		$form_state['storage']['step'] = 'preregister_chairdiscussantpool_preview_form';
	} else {
		die('ERROR 4752368: unknown button clicked ' . $form_state['clicked_button']['#value']);
	}
}
