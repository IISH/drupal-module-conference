<?php 
/**
 * Primary form builder.
 */
function preregister_form( $form, &$form_state ) {
	$user_id = getIdLoggedInUser();
	if ( $user_id == '' || $user_id == '0' ) {
		$loggedin = false;
		$firstpage = 'preregister_login_form';
	} else {
		$loggedin = true;
// TODOLATER bij submit van een form onthou de naam van de huidige form
		$firstpage = 'preregister_personalinfo_edit_form';
	}


    // load eca settings
    $eca_dbsettings = loadEcaSettings();

	// Check if user is already registered for the current conference, if so, show message no changes possible
	if ( $loggedin && isUserRegistered($user_id) ) {

		$form['ct1'] = array(
			'#type' => 'markup',
			'#markup' => '<span class="eca_warning">You are already pre-registered for the ' . getSetting('long_code_year') . ' conference. It is not allowed to modify online your data after your data has been checked by the conference organization. If you would like to make some changes please send an e-mail to ' . getSetting('code') . '. Please go to your <a href="personal-page">personal page</a> to check the data.</span>',
		);

    // Check if preregistration is closed
    } elseif ( isset( $eca_dbsettings["preregistration_closes_on"] ) && $eca_dbsettings["preregistration_closes_on"] != '' && date("Y-m-d") >= $eca_dbsettings["preregistration_closes_on"] ) {

        $form['ct1'] = array(
            '#type' => 'markup',
            '#markup' => '<span class="eca_warning">' . $eca_dbsettings["preregistration_closes_on_message"] . '</span>',
        );

    // Check if preregistration has started
    } elseif ( isset( $eca_dbsettings["preregistration_starts_on"] ) && $eca_dbsettings["preregistration_starts_on"] != '' && date("Y-m-d") < $eca_dbsettings["preregistration_starts_on"] ) {

        $form['ct1'] = array(
            '#type' => 'markup',
            '#markup' => '<span class="eca_warning">' . $eca_dbsettings["preregistration_starts_on_message"] . '</span>',
        );

    } else {

		// Initialize.
		if ( $form_state['rebuild'] ) {
			// Don't hang on to submitted data in form state input.
			$form_state['input'] = array();
		}
		if ( empty($form_state['storage'] ) ) {
			// No step has been set so start with the first.
			$form_state['storage'] = array(
				'step' => $firstpage,
			);
		}

		// Return the form for the current step.
		$function = $form_state['storage']['step'];

		if ( $loggedin ) {
			if ( !isset($_SESSION['storage']['naw_downloaded']) ) {
				// load NAW data
				loadData($user_id, $form_state);
				$_SESSION['storage']['naw_downloaded'] = '1';
			}
		}

		$form = $function( $form, $form_state );
	}

	return $form;
}

/**
 * Primary validate handler.
 */
function preregister_form_validate( $form, &$form_state ) {
if ( isset( $form_state['storage'] ) ) {
	// Call step validate handler if it exists.
	if ( function_exists( $form_state['storage']['step'] . '_validate') ) {
		$function = $form_state['storage']['step'] . '_validate';
		$function($form, $form_state );
	}
}
	return;
}

/**
 * Primary submit handler.
 */
function preregister_form_submit( $form, &$form_state ) {
	$values = $form_state['values'];
	if (isset( $values['back'] ) && $values['op'] == $values['back'] ) {
		// Moving back in form.
		$step = $form_state['storage']['step'];
		// Call current step submit handler if it exists to unset step form data.
		if (function_exists( $step . '_submit') ) {
			$function = $step . '_submit';
			$function( $form, $form_state );
		}
		// Remove the last saved step so we use it next.
		$last_step = array_pop( $form_state['storage']['steps'] );
		$form_state['storage']['step'] = $last_step;
	} else {
		// Record step.
		$step = $form_state['storage']['step'];
		$form_state['storage']['steps'][] = $step;
		// Call step submit handler if it exists.
		if (function_exists( $step . '_submit') ) {
			$function = $step . '_submit';
			$function( $form, $form_state );
		}
	}
	return;
}

