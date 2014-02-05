<?php 
/**
 * TODOEXPLAIN
 */
function preregister_registerpaper_preview_form( $form, &$form_state ) {
	$ct=0;

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="step">Step 6 of ' . getSetting('steps') . '</div>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_personalpage">Please check the data and go to the next screen</span>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_personalpage">Paper info</span>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">Paper title </span>',
		'#prefix' => '<div class="container-inline">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => $_SESSION['storage']['preregister_registerpaper_papertitle'],
		'#suffix' => '</div>', 
		);

	$a = str_replace("\n", "<br>\n", $_SESSION['storage']['preregister_registerpaper_paperabstract']);
	$a = show_more($a, 300, 'ABCDEF');

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">Abstract </span>',
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => $a,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">Co-authors </span>',
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => $_SESSION['storage']['preregister_registerpaper_coauthors'],
		);

	if ( getSetting('show_network') == 1 ) {
		// proposed network
		$list_of_networks = getArrayOfNetworks();
		$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<span class="label_personalpage">Proposed network: </span>',
			'#prefix' => '<div class="container-inline bottommargin">', 
			);
		$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => $list_of_networks[$_SESSION['storage']['preregister_registerpaper_proposednetwork']],
			'#suffix' => '</div>', 
			);
	}

	$bottomMargin = '';
	if ( $_SESSION['storage']['preregister_registerpaper_partofexistingsession']['y'] !== 'y' ) {
		$bottomMargin = 'bottommargin';
	}

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">Is this part of an existing session? </span>',
		'#prefix' => '<div class="container-inline ' . $bottomMargin . '">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => ( $_SESSION['storage']['preregister_registerpaper_partofexistingsession']['y'] === 'y' ) ? 'yes' : 'no',
		'#suffix' => '</div>', 
		);

	if ( $_SESSION['storage']['preregister_registerpaper_partofexistingsession']['y'] === 'y' ) {
		$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<span class="label_personalpage">Proposed session </span>',
			'#prefix' => '<div class="container-inline bottommargin">', 
			);
		$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => $_SESSION['storage']['preregister_registerpaper_proposedsession'],
			'#suffix' => '</div>', 
			);
	}

	if ( getSetting('show_award') == 1 ) {
		if ( isset( $_SESSION['storage']['preregister_personalinfo_student']['y'] ) && $_SESSION['storage']['preregister_personalinfo_student']['y'] === 'y' ) {
			$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => '<span class="label_personalpage">Would you like to participate in the "' . getSetting('award_name') . ' award"? </span>',
				'#prefix' => '<div class="container-inline">', 
				);
			$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => ( isset($_SESSION['storage']['preregister_registerpaper_award']['y']) && $_SESSION['storage']['preregister_registerpaper_award']['y'] === 'y' ) ? 'yes' : 'no',
				'#suffix' => '</div>', 
				);
		}
	}

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

	// AUDIO VISUAL EQUIPMENT

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_personalpage">Audio/visual equipment</span>',
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">Beamer </span>',
		'#prefix' => '<div class="container-inline">', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => ( $_SESSION['storage']['preregister_registerpaper_audiovisual']["beamer"] === 'beamer' ) ? 'yes' : 'no',
		'#suffix' => '</div>', 
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="label_personalpage">Extra audio/visual request </span>',
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		);
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => str_replace("\n", "<br>\n", $_SESSION['storage']['preregister_registerpaper_extraaudiovisual']),
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

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
		'#value' => 'Go to step 7 (Chair / Discussant pool)',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '&nbsp; &nbsp; &nbsp;',
		);

	$form['submit_nextstep_addsession'] = array(
		'#type' => 'submit',
		'#value' => 'or go to step \'add session\'',
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
function preregister_registerpaper_preview_form_submit( $form, &$form_state ) {
	$form_state['rebuild'] = TRUE;

	$values = $form_state['values'];

	if ( $form_state['clicked_button']['#value'] == $values['submit_back'] ) {
		// BACK
		$form_state['storage']['step'] = 'preregister_registerpaper_edit_form';
	} elseif ( $form_state['clicked_button']['#value'] == $values['submit'] || $form_state['clicked_button']['#value'] == $values['submit_nextstep_addsession'] ) {
		// save/update naw/papers/participantdate
		saveUpdateParticipant();
		saveUpdateParticipantDate();
		saveUpdatePaper();

		if ( $form_state['clicked_button']['#value'] == $values['submit_nextstep_addsession'] ) {
			$form_state['storage']['step'] = 'preregister_session_list_form';
		} else {
			$form_state['storage']['step'] = 'preregister_chairdiscussantpool_edit_form';
		}
	} else {
		die('ERROR 474174: unknown button clicked ' . $form_state['clicked_button']['#value']);
	}
}

