<?php 
// GLOBAL VARIABLES REQUIRED

//$conference_settings = array();

$conference_settings['live'] = 0;

$conference_settings['db_connection'] = 'conference';
$conference_settings['date_id'] = 1;
$conference_settings['event_id'] = 1;
$conference_settings['admin_email'] = '';
$conference_settings['jira_email'] = '';

$conference_settings['pathForMenu'] = 'conference-user/';
$conference_settings['pathForAdminMenu'] = $conference_settings['pathForMenu'] . 'admin/';
$conference_settings['base_name'] = 'http:// ... /' . $conference_settings['pathForMenu'];
$conference_settings['urlconfirmlostpassword'] = 'confirm-lost-password';
$conference_settings['urlpersonalpage'] = 'personal-page';
$conference_settings['urlchangepassword'] = 'change-password';
$conference_settings['urllogin'] = 'login';

$conference_settings['email_fromname'] = '';
$conference_settings['email_fromemail'] = '';
$conference_settings['password_criteria'] = '<br>The new password must be at least 8 characters long and contain at least one lowercase character, one upper case character and one digit.';
$conference_settings['equipment_beamer_id'] = 5;
$conference_settings['organizer_id'] = 7;
$conference_settings['author_id'] = 8;
$conference_settings['coauthor_id'] = 9;
$conference_settings['bcc_debug'] = '';
$conference_settings['bcc_registration'] = '';
$conference_settings['required'] = '<span class="form-required" title="This field is required.">*</span>';
$conference_settings['general_questions'] = 'General questions please contact:';
$conference_settings['steps'] = 9;

$conference_settings['multiple_papers_per_author'] = 0;

$conference_settings['show_award'] = 0;
$conference_settings['award_name'] = '';

$conference_settings['show_network'] = 1;
$conference_settings['default_network'] = '';

$conference_settings['code'] = 'UNKNOWN';
$conference_settings['code_year'] = 'UNKNOWN 2014';
$conference_settings['long_code_year'] = 'UNKNOWN Conference 2014';

$conference_settings['show_languagecoachpupil'] = 0;
$conference_settings['show_cv'] = 0;

$conference_settings['hide_add_single_paper_after'] = '2022-12-31';
$conference_settings['hide_add_session_after'] = '2022-12-31';

$conference_settings['volunteering_chair'] = 0;
$conference_settings['volunteering_discussant'] = 0;
$conference_settings['volunteering_languagecoach'] = 0;
$conference_settings['volunteering_languagepupil'] = 0;

$conference_settings['email_template_password'] = 0;
$conference_settings['email_template_new_password'] = 0;
$conference_settings['email_template_normal_registration'] = 0;
$conference_settings['email_template_session_registration_new'] = 0;
$conference_settings['email_template_session_registration_existing'] = 0;

$conference_settings['massmail_subject'] = "";
$conference_settings['massmail_body'] = "";

$conference_settings['onlineprogram_header'] = "Preliminary Program";
$conference_settings['onlineprogram_underconstruction'] = "Under construction";
$conference_settings['onlineprogram_live'] = 0;

$conference_settings['mailstudentcard_id'] = 0;

