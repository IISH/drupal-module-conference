<?php 
/**
 * TODOEXPLAIN
 */
function preregister_session_remove_form( $form, &$form_state ) {
	$ct=0;

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_preregister">Please confirm deletion of session</span>',
		);

	$arrSession = getSessionDetailsAsArray($_SESSION['storage']['preregistersession_sessionid'], array("session_name"));
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => 'Session: ' . $arrSession["session_name"],
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<br><br>',
		);

	$form['submit_back'] = array(
		'#type' => 'submit',
		'#value' => 'Back',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '&nbsp; &nbsp; &nbsp;',
		);

	$form['submit_remove'] = array(
		'#type' => 'submit',
		'#value' => 'Delete',
		);

	return $form;
}

/**
 * TODOEXPLAIN
 */
function preregister_session_remove_form_submit( $form, &$form_state ) {
	// Trigger multistep, there are more steps.
	$form_state['rebuild'] = TRUE;

	$values = $form_state['values'];

	if ( $form_state['clicked_button']['#value'] == $values['submit_back'] ) {
		// BACK
		$form_state['storage']['step'] = 'preregister_session_list_form';
	} elseif ( $form_state['clicked_button']['#value'] == $values['submit_remove'] ) {
		// REMOVE SESSION
		removeSessionAddedBy( $_SESSION['storage']['preregistersession_sessionid'], getIdLoggedInUser() );

		$form_state['storage']['step'] = 'preregister_session_list_form';
	}
}

