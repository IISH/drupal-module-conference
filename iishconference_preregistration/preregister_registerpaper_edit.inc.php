<?php 
/**
 * TODOEXPLAIN
 */
function preregister_registerpaper_edit_form( $form, &$form_state ) {
	$ct=0;

	// PAPER

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="step">Step 5 of ' . getSetting('steps') . '</div>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_preregister">Register a paper</span>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_personalpage">Paper info</span>',
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		);

	$form['papertitle'] = array(
		'#type' => 'textfield',
		'#title' => 'Paper title <span class="form-required" title="This field is required.">*</span>',
		'#size' => 40,
		'#maxlength' => 255,
		'#prefix' => '<div class="container-inline">', 
		'#suffix' => '</div>', 
		'#default_value' => isset( $_SESSION['storage']['preregister_registerpaper_papertitle'] ) ? $_SESSION['storage']['preregister_registerpaper_papertitle'] : NULL, 
		);

	$form['paperabstract'] = array(
		'#type' => 'textarea',
		'#title' => 'Abstract <span class="form-required" title="This field is required.">*</span>',
		'#description' => '<em>(max. 500 words)</em>',
		'#rows' => 2,
		'#default_value' => isset( $_SESSION['storage']['preregister_registerpaper_paperabstract'] ) ? $_SESSION['storage']['preregister_registerpaper_paperabstract'] : NULL, 
		);

	$form['coauthors'] = array(
		'#type' => 'textfield',
		'#title' => 'Co-authors',
		'#size' => 40,
		'#maxlength' => 255,
		'#prefix' => '<div class="container-inline bottommargin">', 
		'#suffix' => '</div>', 
		'#default_value' => isset( $_SESSION['storage']['preregister_registerpaper_coauthors'] ) ? $_SESSION['storage']['preregister_registerpaper_coauthors'] : NULL, 
		);

	if ( SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1 ) {

//network_proposal_id
		// 
		$list_of_networks = getArrayOfNetworks();
		$form['proposednetwork'] = array(
			'#title' => 'Proposed network',
			'#type' => 'select',
			'#options' => $list_of_networks,
			'#prefix' => '<div class="container-inline">', 
			'#suffix' => '</div>', 
			'#size' => 3, 
			'#required' => TRUE,
			'#default_value' => isset( $_SESSION['storage']['preregister_registerpaper_proposednetwork'] ) ? $_SESSION['storage']['preregister_registerpaper_proposednetwork'] : NULL, 
			);

	} else {
		// NO NETWORK

		$form['proposednetwork'] = array(
			'#type' => 'textfield',
			'#title' => 'Proposed network',
			'#prefix' => '<div style="display:none;">', 
			'#suffix' => '</div>', 
			'#default_value' => SettingsApi::getSetting(SettingsApi::DEFAULT_NETWORK_ID),
			);

	}

	$checkbox_value = ( isset( $_SESSION['storage']['preregister_registerpaper_partofexistingsession']['y'] ) && $_SESSION['storage']['preregister_registerpaper_partofexistingsession']['y'] === 'y' ) ? array('checked' => 'checked') : '';
	$form['partofexistingsession'] = array(
		'#type' => 'checkboxes',
		'#options' => array(
						'y' => 'Is this part of an existing session?',
						),
		'#attributes' => $checkbox_value,
		);

	$form['proposedsession'] = array(
		'#type' => 'textfield',
		'#title' => 'Proposed session',
		'#size' => 40,
		'#maxlength' => 255,
		'#prefix' => '<div id="textfields"><div class="container-inline">', 
		'#suffix' => '</div></div>', 
		'#default_value' => isset( $_SESSION['storage']['preregister_registerpaper_proposedsession'] ) ? $_SESSION['storage']['preregister_registerpaper_proposedsession'] : NULL, 

		'#states' => array(
			'visible' => array( // action to take.
					':input[name="partofexistingsession[y]"]' => array('checked' => TRUE),
				),
			),

		);

	if ( SettingsApi::getSetting(SettingsApi::SHOW_AWARD) == 1 ) {
		if ( isset( $_SESSION['storage']['preregister_personalinfo_student']['y'] ) && $_SESSION['storage']['preregister_personalinfo_student']['y'] === 'y' ) {
			$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => '<br>',
				);

			$form['award'] = array(
				'#type' => 'checkboxes',
				'#options' => array(
								'y' => 'Would you like to participant in the "' . SettingsApi::getSetting(SettingsApi::AWARD_NAME) . ' award"? &nbsp; <em>(<a href="/award" target="_blank">more about the award</a>)</em>',
								),
				'#default_value' => isset( $_SESSION['storage']['preregister_registerpaper_award'] ) ? $_SESSION['storage']['preregister_registerpaper_award'] : array(), 
				);
		}
	} else {
		// NO AWARD

		$form['award'] = array(
			'#type' => 'textfield',
			'#title' => 'Award',
			'#prefix' => '<div style="display:none;">', 
			'#suffix' => '</div>', 
			'#default_value' => 'n', 
			);

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

	$checkbox_value = ( isset( $_SESSION['storage']['preregister_registerpaper_audiovisual']["beamer"] ) && $_SESSION['storage']['preregister_registerpaper_audiovisual']["beamer"] === 'beamer' ) ? array('checked' => 'checked') : '';
	$form['audiovisual'] = array(
		'#title' => '', //t('Audio/visual equipment'),
		'#type' => 'checkboxes',
		'#description' => 'Select the equipment you will need for your presentation.',
		'#options' => array(
						'beamer' => 'Beamer',
						),
		'#attributes' => $checkbox_value,
		);

	$form['extraaudiovisual'] = array(
		'#type' => 'textarea',
		'#title' => 'Extra audio/visual request',
		'#rows' => 2,
		'#default_value' => isset( $_SESSION['storage']['preregister_registerpaper_extraaudiovisual'] ) ? $_SESSION['storage']['preregister_registerpaper_extraaudiovisual'] : NULL, 
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
function preregister_registerpaper_edit_form_validate( $form, &$form_state ) {

	if ( $form_state['clicked_button']['#value'] == $form_state['values']['submit'] ) {

		if ( trim($form_state['values']['papertitle']) == '' ) {
			form_set_error('papertitle', 'Paper title field is required.');
		}

		if ( trim($form_state['values']['paperabstract']) == '' ) {
			form_set_error('paperabstract', 'Paper abstract field is required.');
		}

		$ttt = $form_state['values']['partofexistingsession'];
		if ( $ttt['y'] === 'y' ) {
			if ( trim($form_state['values']['proposedsession']) == '' ) {
				form_set_error('proposedsession', 'Proposed session field is required if you check \'Is part of an existing session?\'.');
			}
		}

	}

}

/**
 * TODOEXPLAIN
 */
function preregister_registerpaper_edit_form_submit( $form, &$form_state ) {
	// Trigger multistep, there are more steps.
	$form_state['rebuild'] = TRUE;

	$values = $form_state['values'];

	$_SESSION['storage']['preregister_registerpaper_papertitle'] = $form_state['values']["papertitle"];
	$_SESSION['storage']['preregister_registerpaper_paperabstract'] = $form_state['values']["paperabstract"];
	$_SESSION['storage']['preregister_registerpaper_coauthors'] = $form_state['values']["coauthors"];
	$_SESSION['storage']['preregister_registerpaper_award'] = isset($form_state['values']["award"]) ? $form_state['values']["award"] : NULL;

	$_SESSION['storage']['preregister_registerpaper_partofexistingsession'] = $form_state['values']["partofexistingsession"];
	$_SESSION['storage']['preregister_registerpaper_proposedsession'] = $form_state['values']["proposedsession"];

	$_SESSION['storage']['preregister_registerpaper_proposednetwork'] = $form_state['values']["proposednetwork"];

	$_SESSION['storage']['preregister_registerpaper_audiovisual'] = $form_state['values']["audiovisual"];
	$_SESSION['storage']['preregister_registerpaper_extraaudiovisual'] = $form_state['values']["extraaudiovisual"];

	if ( $form_state['clicked_button']['#value'] == $values['submit_back'] ) {
		// BACK
		$form_state['storage']['step'] = 'preregister_typeofregistration_form';
	} elseif ( $form_state['clicked_button']['#value'] == $values['submit'] ) {
		$form_state['storage']['step'] = 'preregister_registerpaper_preview_form';
	} else {
		die('ERROR 415674: unknown button clicked ' . $form_state['clicked_button']['#value']);
	}
}
