<?php 
/**
 * TODOEXPLAIN
 */
function preregister_login_form( $form, &$form_state ) {
	$ct=0;

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="step">Step 1 of ' . getSetting('steps') . '</div>',
		);

	// EXISTING USERS
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="login_halfwidth rightmargin">',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_login">Existing users</span>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="bottommargin">Please enter your e-mail address and password.</div>',
		);

	$default_email_value = '';
	$default_email_value_newusers = '';

	if ( !isset( $_SESSION["conference"]["login_default_email_existingusers"] ) ) {
		$_SESSION["conference"]["login_default_email_existingusers"] = '';
	}
	if ( !isset( $_SESSION["conference"]["login_default_email_newusers"] ) ) {
		$_SESSION["conference"]["login_default_email_newusers"] = '';
	}

	// check if page submitted
	// button 1 = post, button 2 = get ???
	if ( strtolower($_SERVER['REQUEST_METHOD']) == "post" || strtolower($_SERVER['REQUEST_METHOD']) == "get" ) {
		$default_email_value = $_SESSION["conference"]["login_default_email_existingusers"];
		$default_email_value_newusers = $_SESSION["conference"]["login_default_email_newusers"];

	} else {

		if ( isset( $_SESSION["conference"]["user_email"] ) ) {
			$default_email_value = trim($_SESSION["conference"]["user_email"]);
		}

	}

	$form['email'] = array(
		'#type' => 'textfield',
		'#title' => 'E-mail',
		'#size' => 20,
		'#maxlength' => 100,
		'#prefix' => '<div class="container-inline bottommargin">', 
		'#suffix' => '</div>', 
		'#default_value' => $default_email_value, 
		);

	$form['password'] = array(
		'#type' => 'password',
		'#title' => 'Password',
		'#size' => 20,
		'#maxlength' => 50,
		'#prefix' => '<div class="container-inline bottommargin">', 
		'#suffix' => '</div>',
		);

	$form['submit_button_next'] = array(
		'#type' => 'submit',
		'#value' => 'Log in'
		);

	// lost password url
	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="largertopmargin"><a href="/' . getSetting('pathForMenu') . 'lost-password">Lost password</a></div>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

	// + + + + + + + + + + + + +

	// NEW USERS

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="login_halfwidth">',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="header_login">New users</span>',
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<div class="bottommargin">Please enter your e-mail address.</div>',
		);

	$form['email_newusers'] = array(
		'#type' => 'textfield',
		'#title' => 'E-mail',
		'#size' => 20,
		'#maxlength' => 100,
		'#prefix' => '<div class="container-inline bottommargin">', 
		'#suffix' => '</div>', 
		'#default_value' => $default_email_value_newusers, 
		);

	$form['submit_button_new'] = array(
		'#type' => 'submit',
		'#value' => 'New user'
		);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '</div>',
		);

	$form['ct'.$ct++] = goToErrorMessage(14);

	$form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => "<div class=\"eca_warning\">
<br>
<strong>Comments</strong><br>
<ol>
	<li>Please disable (or minimize the size of) the cache in your browser (Internet Explorer, Firefox, Chrome)</li>
	<li>Use the back/next buttons in the form, do NOT use the browser back button</li>
	<li>Prepare your abstract beforehand. Do NOT type your abstract in the form field, but COPY it into the form field.</li>
	<li>Please mail all errors to: " . encryptEmailAddress(getSetting('jira_email')) . "</li>
</ol>
</div>",
		);

	return $form;
}

/**
 * TODOEXPLAIN
 */
function preregister_login_form_validate( $form, &$form_state ) {
	// EXISTING USERS
	if ( $form_state['clicked_button']['#value'] == $form_state['values']['submit_button_next'] ) {

		$email_existing_users_trimmed = trim($form_state['values']['email']);
		if ( $email_existing_users_trimmed == '' ) {
			form_set_error('email', 'E-mail field is required.');
		} elseif ( !valid_email_address( $email_existing_users_trimmed ) ) {
			form_set_error('email', 'The e-mail address appears to be invalid.');
		}

		$password_trimmed = trim($form_state['values']['password']);
		if ( $password_trimmed == '' ) {
			form_set_error('password', 'Password field is required.');
		}
		$_SESSION["conference"]["login_default_email_newusers"] = '';

	// NEW USERS
	} elseif ( $form_state['clicked_button']['#value'] == $form_state['values']['submit_button_new'] ) {

		$email_new_users_trimmed = trim($form_state['values']['email_newusers']);
		if ( $email_new_users_trimmed == '' ) {
			form_set_error('email_newusers', 'E-mail field is required.');
		} elseif ( !valid_email_address( $email_new_users_trimmed ) ) {
			form_set_error('email_newusers', 'The e-mail address appears to be invalid.');
		}

		$_SESSION["conference"]["login_default_email_existingusers"] = '';
	// ELSE
	} else {
		die('ERROR 658412: Unknown button in login form');
	}

}

/**
 * TODOEXPLAIN
 */
function preregister_login_form_submit( $form, &$form_state ) {
	// Trigger multistep, there are more steps.
	$form_state['rebuild'] = TRUE;

	// EXISTING USERS
	if ( $form_state['clicked_button']['#value'] == $form_state['values']['submit_button_next'] ) {

		// load eca settings
		$eca_dbsettings = loadEcaSettings();
		$eca_salt = $eca_dbsettings["salt"];

		$email_trimmed = trim($form_state['values']['email']);
		$password_trimmed = trim($form_state['values']['password']);
		$_SESSION["conference"]["login_default_email_existingusers"] = $email_trimmed;

		db_set_active( getSetting('db_connection') );

		$result = db_select('users', 'n')
			->fields('n')
			->condition('email', $email_trimmed, '=')
			->orderBy('user_id', 'DESC')
			->execute()
			->fetchAssoc();

		$user_status = 1;

		if ( $result ) {

			// calculate password hash
			$password_hash = encryptPassword($password_trimmed, $eca_salt, $result['salt']);

			// check if dbpassword = hash(password)
			if ( $password_hash != $result["password"] ) {
				$user_status = 0; // not equal
			} elseif ( $result["enabled"] == 0 ) {
				$user_status = 2; // disabled
			} elseif ( $result["deleted"] == 1 ) {
				$user_status = 3; // deleted
			}

		} else {
			$user_status = 0;
		}

		db_set_active();

		if ( $user_status == 1 ) {

			$_SESSION["conference"]["user_id"] = $result["user_id"];
			$_SESSION["conference"]["user_email"] = trim($form_state['values']['email']);
			$_SESSION['storage']['isexistinguser'] = 1;
			//
			$form_state['storage']['step'] = 'preregister_personalinfo_edit_form';
		} else {

			switch ( $user_status ) {
				case 2:
					drupal_set_message("Account is disabled.", 'error');
					break;
				case 3:
					drupal_set_message("Account is deleted", 'error');
					break;
				default:
					drupal_set_message("Incorrect email/password combination.", 'error');
			}
		}

	} elseif ( $form_state['clicked_button']['#value'] == $form_state['values']['submit_button_new'] ) {

		$email_trimmed = trim($form_state['values']['email_newusers']);
		$_SESSION["conference"]["login_default_email_newusers"] = $email_trimmed;

		db_set_active( getSetting('db_connection') );

		$result = db_select('users', 'n')
			->fields('n')
			->condition('email', $email_trimmed, '=')
			->orderBy('user_id', 'DESC')
			->execute()
			->fetchAssoc();

		$user_status = 1;

		$user_found = 0;
		if ( $result ) {
			$user_found = 1;
		}

		db_set_active();

		if ( $user_found == 1 ) {
			drupal_set_message("E-mail already registered in our database.<br>Please login with your e-mail/password combination.<br>If you have forgotten your password please go to 'Lost password'.", 'error');
		} else {
			$_SESSION["conference"]["user_email"] = $email_trimmed;
			$_SESSION['storage']['isexistinguser'] = 0;

			//
			$form_state['storage']['step'] = 'preregister_personalinfo_edit_form';
		}
	}
}
