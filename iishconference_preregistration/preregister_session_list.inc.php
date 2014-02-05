<?php 
/**
 * TODOEXPLAIN
 */
function preregister_session_list_form( $form, &$form_state ) {
	$ct=0;

	// SESSION LIST
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="step">Step 5 of ' . getSetting('steps') . '</div>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_preregister">List of sessions added by you</span>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<br>',
		);

	$counter = 0;
	foreach (getArrayOfSessionsAddedByParticipant(getIdLoggedInUser()) as $nr => $name) {
		if ( $counter == 0 ) {

			// add new session
			$form['submit_session_' . $nr] = array(
				'#type' => 'submit',
				'#value' => $name,
				'#suffix' => '<br><br>', 
				'#prefix' => '', 
			);

		} else {
			// edit remove session

			if ( $counter == 1) {
				$prefix = ' &nbsp;or<br><br>';
			} else {
				$prefix = '<br>';
			}

			$form['submit_session_' . $nr] = array(
				'#name' => 'submit_session_' . $nr,
				'#type' => 'submit',
				'#value' => 'Edit session',
				'#prefix' => $prefix, 
//				'#suffix' => '', 
				'#suffix' => ' ' . $name . '<br><br>',
			);

//			$form['remove_session_' . $nr] = array(
//				'#name' => 'remove_session_' . $nr,
//				'#type' => 'submit',
//				'#value' => 'Remove',
//				'#suffix' => ' ' . $name . '<br><br>',
//				'#prefix' => '',
//			);

		}

		$counter++;
	}

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
		'#value' => 'Back to step 4',
		);

	// hide next button if no session(s)
//	if ( countNrOfSessionsForOrganizer(getIdLoggedInUser()) >= 1 ) {
		$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '&nbsp; &nbsp; &nbsp;',
			);

		$form['submit_next'] = array(
			'#type' => 'submit',
			'#value' => 'Go to step 7 (Chair / Discussant pool)',
		);
//	}

	return $form;
}

/**
 * TODOEXPLAIN
 */
function preregister_session_list_form_submit( $form, &$form_state ) {
	// Trigger multistep, there are more steps.
	$form_state['rebuild'] = TRUE;

	$values = $form_state['values'];

	if ( !isset($values['submit_back']) ) {
		$values['submit_back'] = '';
	}
	if ( !isset($values['submit_next']) ) {
		$values['submit_next'] = '';
	}

	if ( $form_state['clicked_button']['#value'] == $values['submit_back'] ) {
		// BACK
		$form_state['storage']['step'] = 'preregister_typeofregistration_form';
	} elseif ( $form_state['clicked_button']['#value'] == $values['submit_next'] ) {
		$form_state['storage']['step'] = 'preregister_chairdiscussantpool_edit_form';
	} else {
		$needleEdit = 'edit-submit-session-';

		if ( strpos($form_state['clicked_button']['#id'], $needleEdit) !== false ) {
			$sessionId = $form_state['clicked_button']['#id'];
			$sessionId = str_replace($needleEdit, '', $sessionId);

			// SET SESSION ID
			$_SESSION['storage']['preregistersession_sessionid'] = $sessionId;

			// PRELOAD SESSION VALUES
			loadSessionData($sessionId);

			// go to session edit form
			$form_state['storage']['step'] = 'preregister_session_edit_form';
		} else {
			die('ERROR 85695874: unknown button clicked ' . $form_state['clicked_button']['#id'] . ' - ' . $form_state['clicked_button']['#value']);
		}
	}
}
