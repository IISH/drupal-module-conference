<div class="container-inline bottommargin">
	<?php print drupal_render($variables['form']); ?>
</div>

<?php if (!isset($_GET['session'])) : ?>
	<div class="programme_day showing">
		<?php print $variables['curShowing']; ?>
	</div>

	<?php if ($eventDate->isLastDate() && $downloadPaperIsOpen) : ?>
		<div class="download-icon-info">
			<span class="download-icon"></span>
			<?php print iish_t('Click on the icon to download the paper'); ?>
		</div>
		<div class="clear"></div>
	<?php endif; ?>

	<?php if ($eventDate->isLastDate() && LoggedInUserDetails::isAParticipant()) : ?>
		<div class="favorite-icon-info">
			<span class="favorite">&#9733;</span>
			<?php print iish_t('Click on the icon to add the session to your favorites list'); ?>
		</div>
		<div class="clear"></div>
	<?php endif; ?>
<?php endif; ?>

<table class="programme">
	<tbody>
	<tr>
		<td class="programme noprint">
			<?php foreach ($variables['days'] as $day) : ?>
				<a href="?day=<?php print $day->getId(); ?>"><?php print $day->getDayFormatted('D j F'); ?></a>
				<br/>
				<?php foreach ($variables['date-times'] as $timeSlot) : ?>
					<?php if ($timeSlot->getDayId() == $day->getId()) : ?>
						<nobr>
							&nbsp; &nbsp;
							<a href="?day=<?php print $day->getId(); ?>&amp;time=<?php print $timeSlot->getId(); ?>">
								<?php print $timeSlot->getPeriod(true); ?>
							</a>
						</nobr>
						<br/>
					<?php endif; ?>
				<?php endforeach; ?>
				<br/>
			<?php endforeach; ?>

			<a href="?day=0"><?php print iish_t('All days'); ?></a>

            <?php if (LoggedInUserDetails::isAParticipant() && $eventDate->isLastDate()) : ?>
	            <br/>
	            <br/>
                <a href="?favorites=yes"><span class="favorite on">&#9733;</span> <?php print iish_t('Favourite sessions'); ?></a>
            <?php endif; ?>
		</td>

		<td class="programme">
			<?php if (count($variables['programme']) == 0) : ?>
				<span class="eca_warning"><?php print iish_t('Nothing found. Please modify your search criteria.'); ?></span>
			<?php else : ?>
                <?php if (isset($_GET['session'])) : ?>
                    <a href="<?php print $variables['back-url-query']; ?>">
                        <?php print iish_t('Go back'); ?>
                    </a>
                    <br/><br/>
                <?php endif; ?>

				<?php foreach ($variables['programme'] as $i => $session) : ?>
					<?php if (($i == 0) || ($session['timeId'] != $variables['programme'][$i - 1]['timeId'])) : ?>
						<div class="programme_day">
							<?php print date('l j F Y', strtotime($session['day'])); ?>
							<?php print str_replace('  ', ' ', str_replace('-', ' - ', $session['period'])); ?>
						</div>
					<?php endif; ?>

					<strong>
                        <?php if (LoggedInUserDetails::isAParticipant() && $eventDate->isLastDate()) : ?>
                            <?php $favoriteClass = (in_array($session['sessionId'], $favoriteSessions)) ? 'favorite on' : 'favorite'; ?>
                            <span class="<?php print $favoriteClass; ?>" data-session="<?php print $session['sessionId']; ?>">&#9733;</span>
                        <?php endif; ?>

						<a href="?room=<?php print $session['roomId']; ?>"><?php print $session['roomNumber']; ?></a>-<?php print $session['indexNumber']; ?>
						-
						<?php if (SettingsApi::getSetting(SettingsApi::SHOW_SESSION_CODES) == 1): ?>
							<?php print $session['sessionCode']; ?> :
						<?php endif; ?>

                        <?php if (isset($_GET['session'])) : ?>
                            <?php print $variables['highlight']->highlight($session['sessionName']); ?>
                        <?php elseif (is_int($variables['networkId'])) : ?>
                            <a href="?day=<?php print $session['dayId']; ?>&amp;time=<?php print $session['timeId']; ?>&amp;session=<?php print $session['sessionId'] ?>&amp;network=<?php print $variables['networkId'] ?>">
                                <?php print $variables['highlight']->highlight($session['sessionName']); ?>
                            </a>
                        <?php elseif ($variables['textsearch'] !== null) : ?>
                            <a href="?day=<?php print $session['dayId']; ?>&amp;time=<?php print $session['timeId']; ?>&amp;session=<?php print $session['sessionId'] ?>&amp;textsearch=<?php print $variables['textsearch'] ?>">
                                <?php print $variables['highlight']->highlight($session['sessionName']); ?>
                            </a>
                        <?php elseif (is_int($variables['roomId'])) : ?>
                            <a href="?day=<?php print $session['dayId']; ?>&amp;time=<?php print $session['timeId']; ?>&amp;session=<?php print $session['sessionId'] ?>&amp;room=<?php print $variables['roomId'] ?>">
                                <?php print $variables['highlight']->highlight($session['sessionName']); ?>
                            </a>
                        <?php else : ?>
                            <a href="?day=<?php print $session['dayId']; ?>&amp;time=<?php print $session['timeId']; ?>&amp;session=<?php print $session['sessionId'] ?>">
                                <?php print $variables['highlight']->highlight($session['sessionName']); ?>
                            </a>
                        <?php endif; ?>
					</strong>

					<br/>

					<strong><?php print $session['roomName']; ?></strong>

					<br/>

					<table class="programme">
						<tbody>
						<tr>
							<?php $noPlaceForNetwork = 1; ?>
							<?php if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1): ?>
								<?php $noPlaceForNetwork = 0; ?>
								<td width="50%" class="programme">
                                    <span class="programme_key">
                                        <?php print (count($session['networks']) > 1) ?
                                            t('Networks') . ':' : t('Network') . ':'; ?>
                                    </span>

									<?php foreach ($session['networks'] as $j => $network) : ?>
										<a href="?network=<?php print $network['networkId']; ?>"><?php print $network['networkName']; ?></a>
										<?php if (count($session['networks']) !==
											$j + 1
										) : ?>, <?php endif; ?>
									<?php endforeach; ?>
								</td>
							<?php endif; ?>

							<?php
								$alwaysHide = SettingsApi::getSetting(SettingsApi::HIDE_ALWAYS_IN_ONLINE_PROGRAMME);
								$typesToHide = SettingsApi::getArrayOfValues($alwaysHide);

								$participantsWithPaper = array();
								$participantsWithoutPaper = array();
								foreach ($session['participants'] as $participant) {
									if (    ($participant['typeId'] != ParticipantTypeApi::CO_AUTHOR_ID) &&
											(array_search($participant['typeId'], $typesToHide) === false)) {
										if (array_key_exists('paperId', $participant)) {
											$participantsWithPaper[] = $participant;
										}
										else {
											$participantsWithoutPaper[] = $participant;
										}
									}
								}
							?>

							<?php foreach ($variables['types'] as $j => $type) :
								$participants = array();

								while ((count($participantsWithoutPaper) > 0) &&
									($participantsWithoutPaper[0]['type'] == $type->getType())) {
									$participants[] = array_shift($participantsWithoutPaper);
								}

								print '<td class="programme">';

								$hideIfEmpty = SettingsApi::getSetting(SettingsApi::HIDE_IF_EMPTY_IN_ONLINE_PROGRAMME);
								$typesToHide = SettingsApi::getArrayOfValues($hideIfEmpty);
								if (count($participants) === 0) {
									if (array_search($type->getId(), $typesToHide) === false) {
										print '<span class="programme_key">' . $type . 's: </span> -';
									}
									else {
										print '&nbsp;';
									}
								}
								else if (count($participants) == 1) {
									print '<span class="programme_key">' .$type . ': </span>' .
										$variables['highlight']->highlight($participants[0]['participantName']);
								}
								else {
									$names = array();
									foreach ($participants as $participant) {
										$names[] = $variables['highlight']->highlight($participant['participantName']);
									}

									print '<span class="programme_key">' . $type . 's: </span>' . implode(', ', $names);
								}

								print '</td>';
								if (($j % 2) == $noPlaceForNetwork) {
									print '</tr><tr>';
								}
							endforeach; ?>
						</tr>
						</tbody>
					</table>

					<?php foreach ($participantsWithPaper as $participant) : ?>
                        <span class="programme_key">
                            <?php print $variables['highlight']->highlight($participant['participantName']); ?>
                            <?php if (!is_null($participant['coAuthors']) && (strlen(trim($participant['coAuthors'])) > 0)) : ?>
                                ,
                                <?php print $variables['highlight']->highlight($participant['coAuthors']); ?>
                            <?php endif; ?>
                            :
                        </span>

                        <?php print $variables['highlight']->highlight($participant['paperName']); ?>

						<?php if ($eventDate->isLastDate() && $downloadPaperIsOpen) : ?>
							<?php if ($participant['hasDownload']) : ?>
								&nbsp;
								<a href="<?php print $variables['paperDownloadLinkStart'] . $participant['paperId']; ?>"
									alt="<?php print iish_t('Download paper'); ?>"
									title="<?php print iish_t('Download paper'); ?>">
										<span class="download-icon"></span>
								</a>
							<?php endif; ?>
						<?php endif; ?>

						<br />

                        <?php if (isset($_GET['session'])) : ?>
                            <div class="programme_paper_abstract">
                                <?php print ConferenceMisc::getHTMLForLongText($participant['paperAbstract']); ?>
                            </div>
                            <br />
                        <?php endif; ?>
					<?php endforeach; ?>

					<br /><br />

					<?php if ((($i + 1) < count($variables['programme'])) &&
						($session['timeId'] != $variables['programme'][$i + 1]['timeId'])
					) : ?>
						<hr class="programme_hr"/>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</td>
	</tr>
	</tbody>
</table>