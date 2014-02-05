<?php 
/**
 * TODOEXPLAIN
 */
function preregister_chairdiscussantpool_preview_form( $form, &$form_state ) {
	$ct=0;

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="step">Step 8 of ' . getSetting('steps') . '</div>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_personalpage">Please check the data and click submit to confirm your pre-registration</span>',
		);

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

	// CHAIR DISCUSSANT POOL

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_personalpage">Chair / discussant pool</span>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage"><br>I would like to volunteer as Chair: </span>',
		'#prefix' => '<div class="container-inline">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => ( $_SESSION['storage']['preregister_chairdiscussantpool_volunteerchair']['y'] === 'y' ) ? 'yes' : 'no',
		'#suffix' => '</div>', 
		);

	if ( getSetting('show_network') == 1 ) {
		if ( $_SESSION['storage']['preregister_chairdiscussantpool_volunteerchair']['y'] === 'y' ) {
			// 
			$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => 'Network(s): ' . getStringOfNetworks($_SESSION['storage']['preregister_chairdiscussantpool_volunteerchair_networks']),
				);
		}
	}

	if ( getSetting('show_network') == 1 ) {
		$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<br>',
			);
	}

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">I would like to volunteer as Discussant: </span>',
		'#prefix' => '<div class="container-inline">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => ( $_SESSION['storage']['preregister_chairdiscussantpool_volunteerdiscussant']['y'] === 'y' ) ? 'yes' : 'no',
		'#suffix' => '</div>', 
		);

if ( getSetting('show_network') == 1 ) {
	if ( $_SESSION['storage']['preregister_chairdiscussantpool_volunteerdiscussant']['y'] === 'y' ) {
		// 
		$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => 'Network(s): ' . getStringOfNetworks($_SESSION['storage']['preregister_chairdiscussantpool_volunteerdiscussant_networks']),
			);
	}
}

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

if ( getSetting('show_languagecoachpupil') == 1 ) {

	// COACH PUPIL

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_personalpage">English Language Coach</span>',
		);

	$chosenLangeuageCoach = 2;
	$arrLanguageCoaches = getArrayOfLanguageCoach();
	if ( isset( $_SESSION['storage']['preregister_chairdiscussantpool_coachpupil'] ) ) {
		$chosenLangeuageCoach = $_SESSION['storage']['preregister_chairdiscussantpool_coachpupil'];
	}

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage"><br>English Language: </span>',
		'#prefix' => '<div class="container-inline">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => $arrLanguageCoaches[$chosenLangeuageCoach],
		'#suffix' => '</div>', 
		);

	if ( $chosenLangeuageCoach == 0 || $chosenLangeuageCoach == 1 ) {
		//
		$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => 'Network(s): ' . getStringOfNetworks($_SESSION['storage']['preregister_chairdiscussantpool_coachpupil_networks']),
			);
	}

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

}

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="eca_warning heavy">It is not possible to modify your pre-registration after this step.<br><br></span>',
		);

	// SUBMIT BUTTONS

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
		'#value' => 'Submit pre-registration',
		);

	return $form;
}

/**
 * TODOEXPLAIN
 */
function preregister_chairdiscussantpool_preview_form_submit( $form, &$form_state ) {
	$form_state['rebuild'] = TRUE;

	$values = $form_state['values'];

	if ( $form_state['clicked_button']['#value'] == $values['submit_back'] ) {
		// BACK
		$form_state['storage']['step'] = 'preregister_chairdiscussantpool_edit_form';
	} elseif ( $form_state['clicked_button']['#value'] == $values['submit'] ) {
		// save/update naw/papers/participantdate
		saveUpdateParticipant();
		saveUpdateParticipantDate();
		saveUpdatePaper();

		// save/update pool/coach
		saveUpdatePool();
		saveUpdateLanguage();

		$form_state['storage']['step'] = 'preregister_completed_form';
	} else {
		die('ERROR 474198: unknown button clicked ' . $form_state['clicked_button']['#value']);
	}
}

