<?php

/**
 * Implements hook_form()
 */
function preregister_paper_form($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$participant = $state->getParticipant();

	$data = $state->getMultiPageData();
	$paper = $data['paper'];

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// PAPER

	$form['paper'] = array(
		'#type'  => 'fieldset',
		'#title' => iish_t('Register a paper'),
	);

	$form['paper']['papertitle'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('Paper title'),
		'#required'      => true,
		'#size'          => 40,
		'#maxlength'     => 255,
		'#default_value' => $paper->getTitle(),
	);

	$form['paper']['paperabstract'] = array(
		'#type'          => 'textarea',
		'#title'         => iish_t('Abstract'),
		'#required'      => true,
		'#description'   => '<em>' . iish_t('(max. 500 words)') . '</em>',
		'#rows'          => 2,
		'#default_value' => $paper->getAbstr(),
	);

	$form['paper']['coauthors'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('Co-authors'),
		'#size'          => 40,
		'#maxlength'     => 255,
		'#default_value' => $paper->getCoAuthors(),
	);

	if (PreRegistrationUtils::useSessions()) {
		$form['paper']['session'] = array(
			'#type'          => 'select',
			'#title'         => iish_t('Proposed session'),
			'#options'       => CachedConferenceApi::getSessionsKeyValue(),
			'#empty_option'  => '- ' . iish_t('Select a session') . ' -',
			'#default_value' => $paper->getSessionId(),
			'#attributes'    => array('class' => array('iishconference_new_line')),
		);
	}
	else {
		$form['paper']['proposednetwork'] = array(
			'#type'          => 'select',
			'#title'         => iish_t('Proposed network'),
			'#options'       => CRUDApiClient::getAsKeyValueArray(CachedConferenceApi::getNetworks()),
			'#size'          => 4,
			'#required'      => true,
			'#default_value' => $paper->getNetworkProposalId(),
		);

		PreRegistrationUtils::hideAndSetDefaultNetwork($form['paper']['proposednetwork']);

		$form['paper']['partofexistingsession'] = array(
			'#type'          => 'checkbox',
			'#title'         => iish_t('Is this part of an existing session?'),
			'#default_value' => (
					($paper->getSessionProposal() !== null) &&
					(strlen(trim($paper->getSessionProposal())) > 0)
				),
		);

		$form['paper']['proposedsession'] = array(
			'#type'          => 'textfield',
			'#title'         => iish_t('Proposed session'),
			'#size'          => 40,
			'#maxlength'     => 255,
			'#default_value' => $paper->getSessionProposal(),
			'#states'        => array(
				'visible' => array(
					':input[name="partofexistingsession"]' => array('checked' => true),
				),
			),
		);
	}

	if ((SettingsApi::getSetting(SettingsApi::SHOW_AWARD) == 1) && $participant->getStudent()) {
		$form['paper']['award'] = array(
			'#type'          => 'checkbox',
			'#title'         => iish_t('Would you like to participate in the "@awardName"?',
					array('@awardName' => SettingsApi::getSetting(SettingsApi::AWARD_NAME))) . '&nbsp; <em>(' .
				l(iish_t('more about the award'), 'award', array('attributes' => array('target' => '_blank')))
				. ')</em>',
			'#default_value' => $participant->getAward(),
		);
	}

  // + + + + + + + + + + + + + + + + + + + + + + + +
  // KEYWORDS

  $numKeywordsFromListMap = SettingsApi::getMapOfValues(SettingsApi::getSetting(SettingsApi::NUM_PAPER_KEYWORDS_FROM_LIST));
  $numKeywordsFreeMap = SettingsApi::getMapOfValues(SettingsApi::getSetting(SettingsApi::NUM_PAPER_KEYWORDS_FREE));

  foreach (KeywordApi::getGroups() as $group) {
    if (intval($numKeywordsFromListMap[$group]) > 0 || intval($numKeywordsFreeMap[$group]) > 0) {
      $form['keywords_' . $group] = [
        '#type' => 'fieldset',
        '#title' => iish_t(ConferenceMisc::replaceKeyword('Keywords', $group)),
      ];

      $numKeywordsFromList = intval($numKeywordsFromListMap[$group]);
      $numKeywordsFree = intval($numKeywordsFreeMap[$group]);

      $allKeywords = PaperKeywordApi::getKeywordsForPaperInGroup($paper, $group);
      $allPredefinedKeywords = array();
      foreach (CachedConferenceApi::getKeywords() as $keyword) {
        if ($keyword->getGroupName() == $group) {
          $allPredefinedKeywords[] = $keyword;
        }
      }

      $allPredefinedKeywordsPlain =
        CRUDApiClient::getForMethod($allPredefinedKeywords, 'getKeyword');
      $keywordsFromList = [];
      $keywordsFree = [];
      foreach ($allKeywords as $keyword) {
        if (array_search($keyword->getKeyword(), $allPredefinedKeywordsPlain) !== FALSE) {
          $keywordsFromList[] = $keyword->getKeyword();
        }
        else {
          $keywordsFree[] = $keyword->getKeyword();
        }
      }

      if ($numKeywordsFromList > 0) {
        $options = CRUDApiClient::getAsKeyValueArray($allPredefinedKeywords);
        asort($options);
        $defaultValues = [];
        foreach ($options as $id => $keyword) {
          if (array_search($keyword, $keywordsFromList) !== FALSE) {
            $defaultValues[] = $id;
          }
        }

        $title = ($numKeywordsFromList === 1)
          ? iish_t(ConferenceMisc::replaceKeyword('Predefined keyword ', $group))
          : iish_t(ConferenceMisc::replaceKeyword('Predefined keywords ', $group));

        $form['keywords_' . $group]['list_' . $group] = [
          '#type' => 'select',
          '#title' => $title,
          '#multiple' => $numKeywordsFromList > 1,
          '#size' => 4,
          '#options' => $options,
          '#default_value' => $defaultValues,
          '#attributes' => ['class' => ['iishconference_new_line']],
          '#description' => ($numKeywordsFromList > 1) ? iish_t(ConferenceMisc::replaceKeyword('Select up to @num keywords', $group), [
            '@num' => $numKeywordsFromList
          ]) : NULL,
        ];
      }

      if ($numKeywordsFree > 0) {
        // Always show add least one text field for users to enter a keyword
        if (!isset($form_state['num_free_keywords_' . $group])) {
          $form_state['num_free_keywords_' . $group] = max(1, count($keywordsFree));
        }

        $form['keywords_' . $group]['free_keywords_' . $group] = [
          '#type' => 'container',
          '#prefix' => '<div id="free-keywords-wrapper-' . $group . '">',
          '#suffix' => '</div>',
        ];

        $title = ($numKeywordsFree === 1)
          ? iish_t(ConferenceMisc::replaceKeyword('Enter other keyword', $group))
          : iish_t(ConferenceMisc::replaceKeyword('Enter other keywords (single keyword per line)', $group));
        $description = ($numKeywordsFree > 1)
          ? iish_t(ConferenceMisc::replaceKeyword('Please leave this field empty if you have no keywords.', $group))
          : NULL;
        $form['keywords_' . $group]['free_keywords_' . $group]['free_keyword_' . $group]['#tree'] = TRUE;

        // Display all keywords previously stored, unless the user deliberately removed some
        foreach ($keywordsFree as $i => $keyword) {
          if ($i <= ($form_state['num_free_keywords_' . $group] - 1)) {
            $form['keywords_' . $group]['free_keywords_' . $group]['free_keyword_' . $group][$i] = [
              '#type' => 'textfield',
              '#size' => 40,
              '#maxlength' => 100,
              '#default_value' => $keyword,
              '#title' => ($i === 0) ? $title : NULL,
              '#description' => ($i === ($form_state['num_free_keywords_' . $group] - 1)) ? $description : NULL,
              '#attributes' => ['class' => ['iishconference_new_line']],
            ];
          }
        }

        // Now display all additional empty text fields to enter keywords, as many as requested by the user
        for ($i = count($keywordsFree); $i < $form_state['num_free_keywords_' . $group]; $i++) {
          $form['keywords_' . $group]['free_keywords_' . $group]['free_keyword_' . $group][$i] = [
            '#type' => 'textfield',
            '#size' => 40,
            '#maxlength' => 100,
            '#title' => ($i === 0) ? $title : NULL,
            '#description' => ($i === ($form_state['num_free_keywords_' . $group] - 1)) ? $description : NULL,
            '#attributes' => ['class' => ['iishconference_new_line']],
          ];
        }

        // Only allow a maximum number of free keywords
        if ($form_state['num_free_keywords_' . $group] < $numKeywordsFree) {
          $form['keywords_' . $group]['free_keywords_' . $group]['add_keyword_' . $group] = [
            '#type' => 'submit',
            '#name' => 'add_keyword_' . $group,
            '#value' => iish_t(ConferenceMisc::replaceKeyword('Add one more keyword', $group)),
            '#submit' => ['preregister_paper_add_keyword'],
            '#limit_validation_errors' => [],
            '#ajax' => [
              'callback' => 'preregister_paper_keyword_callback',
              'wrapper' => 'free-keywords-wrapper-' . $group,
              'progress' => [
                'type' => 'throbber',
                'message' => iish_t('Please wait...'),
              ],
            ],
          ];
        }

        // Always display add least one text field to enter keywords
        if ($form_state['num_free_keywords_' . $group] > 1) {
          $form['keywords_' . $group]['free_keywords_' . $group]['remove_keyword_' . $group] = [
            '#type' => 'submit',
            '#name' => 'remove_keyword_' . $group,
            '#value' => iish_t(ConferenceMisc::replaceKeyword('Remove the last keyword', $group)),
            '#submit' => ['preregister_paper_remove_keyword'],
            '#limit_validation_errors' => [],
            '#ajax' => [
              'callback' => 'preregister_paper_keyword_callback',
              'wrapper' => 'free-keywords-wrapper-' . $group,
              'progress' => [
                'type' => 'throbber',
                'message' => iish_t('Please wait...'),
              ],
            ],
          ];
        }
      }
    }
  }

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// AUDIO VISUAL EQUIPMENT

	if (SettingsApi::getSetting(SettingsApi::SHOW_EQUIPMENT) == 1) {
		$equipment = CachedConferenceApi::getEquipment();

		$form['equipment'] = array(
			'#type'  => 'fieldset',
			'#title' => iish_t('Audio/visual equipment'),
		);

		if (is_array($equipment) && (count($equipment) > 0)) {
			$equipmentOptions = CRUDApiClient::getAsKeyValueArray($equipment);

			$form['equipment']['audiovisual'] = array(
				'#type'          => 'checkboxes',
				'#description'   => iish_t('Select the equipment you will need for your presentation.'),
				'#options'       => $equipmentOptions,
				'#default_value' => $paper->getEquipmentIds(),
			);
		}

		$form['equipment']['extraaudiovisual'] = array(
			'#type'          => 'textarea',
			'#title'         => iish_t('Extra audio/visual request'),
			'#description'   => iish_t('Every room has a beamer and powerpoint available.'),
			'#rows'          => 2,
			'#default_value' => $paper->getEquipmentComment(),
		);
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +

	$form['submit_back'] = array(
		'#type'                    => 'submit',
		'#name'                    => 'submit_back',
		'#value'                   => iish_t('Back'),
		'#submit'                  => array('preregister_form_submit'),
		'#limit_validation_errors' => array(),
	);

	$form['submit'] = array(
		'#type'  => 'submit',
		'#name'  => 'submit',
		'#value' => iish_t('Save paper'),
	);

	// We can only remove a paper if it has been persisted
	if ($paper->isUpdate()) {
		$form['submit_remove'] = array(
			'#type'                    => 'submit',
			'#name'                    => 'submit_remove',
			'#value'                   => iish_t('Remove paper'),
			'#submit'                  => array('preregister_form_submit'),
			'#limit_validation_errors' => array(),
			'#attributes'              => array('onclick' =>
				                                    'if (!confirm("' .
				                                    iish_t('Are you sure you want to remove this paper?') .
				                                    '")) { return false; }'),
		);
	}

	return $form;
}

/**
 * Implements hook_form_validate()
 */
function preregister_paper_form_validate($form, &$form_state) {
	if (!PreRegistrationUtils::useSessions() && $form_state['values']['partofexistingsession']) {
		if (strlen(trim($form_state['values']['proposedsession'])) === 0) {
			form_set_error('proposedsession',
				iish_t('Proposed session field is required if you check \'Is part of an existing session?\'.'));
		}
	}

  $numKeywordsFromListMap = SettingsApi::getMapOfValues(SettingsApi::getSetting(SettingsApi::NUM_PAPER_KEYWORDS_FROM_LIST));
  foreach (KeywordApi::getGroups() as $group) {
    $maxKeywords = intval($numKeywordsFromListMap[$group]);
    if (($maxKeywords > 0) && (sizeof($form_state['values']['list_' . $group]) > $maxKeywords)) {
      form_set_error('list_' . $group,
        iish_t(ConferenceMisc::replaceKeyword('You can only select up to @maxSize keywords from the list!', $group), array(
          '@maxSize' => $maxKeywords
        )));
    }
  }
}

/**
 * Implements hook_form_submit()
 */
function preregister_paper_form_submit($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$user = $state->getUser();
	$participant = $state->getParticipant();

	$data = $state->getMultiPageData();
	$paper = $data['paper'];

	// First save the paper
	$paper->setUser($user);
	$paper->setTitle($form_state['values']['papertitle']);
	$paper->setAbstr($form_state['values']['paperabstract']);
	$paper->setCoAuthors($form_state['values']['coauthors']);

	// Either save a session or save a network proposal
	$firstSessionId = $paper->getSessionId();
	if (PreRegistrationUtils::useSessions()) {
		$paper->setSession($form_state['values']['session']);
	}
	else {
		$paper->setNetworkProposal($form_state['values']['proposednetwork']); // TODO: QUESTION MARK ???
		$paper->setSessionProposal($form_state['values']['proposedsession']);
	}

  // Save keyword(s) into the database
  $numKeywordsFromListMap = SettingsApi::getMapOfValues(SettingsApi::getSetting(SettingsApi::NUM_PAPER_KEYWORDS_FROM_LIST));
  $numKeywordsFreeMap = SettingsApi::getMapOfValues(SettingsApi::getSetting(SettingsApi::NUM_PAPER_KEYWORDS_FREE));

  $keywords = array();
  foreach (KeywordApi::getGroups() as $group) {
    if (intval($numKeywordsFreeMap[$group]) > 0) {
      foreach ($form_state['values']['free_keyword_' . $group] as $keyword) {
        $keyword = trim($keyword);
        if (strlen($keyword) > 0) {
          $keywords[] = array($group, $keyword);
        }
      }

      // Reset the number of additional persons in form state
      unset($form_state['num_free_keywords_'. $group]);
    }
    if (intval($numKeywordsFromListMap[$group]) > 0) {
      foreach (CachedConferenceApi::getKeywords() as $keyword) {
        if (is_array($form_state['values']['list_' . $group]) && array_search($keyword->getId(), $form_state['values']['list_' . $group]) !== FALSE) {
          $keywords[] = array($group, $keyword->getKeyword());
        }
        else {
          if (!is_array($form_state['values']['list_' . $group]) && ($keyword->getId() == $form_state['values']['list_' . $group])) {
            $keywords[] = array($group, $keyword->getKeyword());
          }
        }
      }
    }
  }
  $paper->setKeywords($keywords);

	// Save equipment
	if (SettingsApi::getSetting(SettingsApi::SHOW_EQUIPMENT) == 1) {
		$allEquipment = CachedConferenceApi::getEquipment();
		if (is_array($allEquipment) && (count($allEquipment) > 0)) {
			$equipment = array();
			foreach ($allEquipment as $equipmentInstance) {
				$value = $form_state['values']['audiovisual'][$equipmentInstance->getId()];
				if ($equipmentInstance->getId() == $value) {
					$equipment[] = $equipmentInstance->getId();
				}
			}
			$paper->setEquipment($equipment);
		}

		$paper->setEquipmentComment($form_state['values']['extraaudiovisual']);
	}

	$paper->save();

	// Then save the participant
	if ((SettingsApi::getSetting(SettingsApi::SHOW_AWARD) == 1) && $participant->getStudent()) {
		$participant->setAward($form_state['values']['award']);
		$participant->save();
	}

	// If we can add a paper to a session, then also create a session participant registration
	if (PreRegistrationUtils::useSessions()) {
		// We changed the session, remove session registration from the first registration
		if (($paper->getSessionId() !== null) &&
			($firstSessionId !== null) &&
			($paper->getSessionId() != $firstSessionId)
		) {
			$prevSessionParticipant = PreRegistrationUtils::getSessionParticipantsOfUserWithSessionAndType(
				$state, $firstSessionId, ParticipantTypeApi::AUTHOR_ID
			);

			$prevSessionParticipant->delete();
		}

		$sessionParticipant = PreRegistrationUtils::getSessionParticipantsOfUserWithSessionAndType(
			$state, $paper->getSessionId(), ParticipantTypeApi::AUTHOR_ID
		);

		// We added a session, but have no session participant yet
		if (($paper->getSessionId() !== null) && ($sessionParticipant === null)) {
			$sessionParticipant = new SessionParticipantApi();
			$sessionParticipant->setUser($user);
			$sessionParticipant->setSession($paper->getSessionId());
			$sessionParticipant->setType(ParticipantTypeApi::AUTHOR_ID);
			$sessionParticipant->save();
		}

		// Or maybe we removed the session, but still have the session participant
		if (($paper->getSessionId() === null) && ($sessionParticipant !== null)) {
			$sessionParticipant->delete();
		}
	}

	// Move back to the 'type of registration' page, clean cached data
	$state->setMultiPageData(array());

  // Reset the number of additional keywords in form state
  foreach (KeywordApi::getGroups() as $group) {
    unset($form_state['num_free_keywords_'. $group]);
  }

	return PreRegistrationPage::TYPE_OF_REGISTRATION;
}

/**
 * What is the previous page?
 */
function preregister_paper_form_back($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$state->setMultiPageData(array());

  // Reset the number of additional keywords in form state
  foreach (KeywordApi::getGroups() as $group) {
    unset($form_state['num_free_keywords_'. $group]);
  }

	return PreRegistrationPage::TYPE_OF_REGISTRATION;
}

/**
 * Remove the paper
 */
function preregister_paper_form_remove($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$multiPageData = $state->getMultiPageData();

	$paper = $multiPageData['paper'];
	$paper->delete();

	// If we added the removed paper to a session, then we should also remove the session participant registration
	if (PreRegistrationUtils::useSessions() && ($paper->getSessionId() !== null)) {
		$sessionParticipant = PreRegistrationUtils::getSessionParticipantsOfUserWithSessionAndType(
			$state, $paper->getSessionId(), ParticipantTypeApi::AUTHOR_ID
		);

		if ($sessionParticipant !== null) {
			$sessionParticipant->delete();
		}
	}

	$state->setMultiPageData(array());

  // Reset the number of additional keywords in form state
  foreach (KeywordApi::getGroups() as $group) {
    unset($form_state['num_free_keywords_'. $group]);
  }

	return PreRegistrationPage::TYPE_OF_REGISTRATION;
}

function preregister_paper_add_keyword($form, &$form_state) {
  $group = str_replace('add_keyword_', '', $form_state['triggering_element']['#name']);

  $numKeywordsFreeMap = SettingsApi::getMapOfValues(SettingsApi::getSetting(SettingsApi::NUM_PAPER_KEYWORDS_FREE));

  if ($form_state['num_free_keywords_' . $group] < intval($numKeywordsFreeMap[$group])) {
    $form_state['num_free_keywords_' . $group]++;
    $form_state['rebuild'] = TRUE;
  }
}

function preregister_paper_remove_keyword($form, &$form_state) {
  $group = str_replace('remove_keyword_', '', $form_state['triggering_element']['#name']);

  if ($form_state['num_free_keywords_' . $group] > 1) {
    $form_state['num_free_keywords_' . $group]--;
    $form_state['rebuild'] = TRUE;
  }
}

function preregister_paper_keyword_callback($form, &$form_state) {
  $group = str_replace('add_keyword_', '', $form_state['triggering_element']['#name']);
  $group = str_replace('remove_keyword_', '', $group);

  return $form['keywords_' . $group]['free_keywords_' . $group];
}
