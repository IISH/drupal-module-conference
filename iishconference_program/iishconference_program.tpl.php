<?php $underConstruction = iish_t('Under construction'); ?>
<?php if ($underConstruction != '') : ?>
	<h1><?php print $underConstruction; ?></h1>
<?php endif; ?>

<div class="container-inline bottommargin">
	<?php print drupal_render($variables['form']); ?>
</div>

<?php if (!isset($_GET['paper'])) : ?>
	<div class="program_day showing">
		<?php print $variables['curShowing']; ?>
	</div>

	<?php // TODO GCU moet op een andere manier ?>
	<?php if (SettingsApi::getSetting(SettingsApi::DOWNLOAD_PAPER_LASTDATE) == '' || SettingsApi::getSetting(SettingsApi::DOWNLOAD_PAPER_LASTDATE) >= date("Y-m-d")) : ?>
		<div class="download-icon-info">
			<span class="download-icon"></span>
			<?php print iish_t('Click on the icon to download the paper'); ?>
		</div>
	<?php endif; ?>

	<div class="clear"></div>
<?php endif; ?>

<table class="program">
	<tbody>
	<tr>
		<td class="program noprint">
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
		</td>

		<td class="program">
			<?php if (isset($_GET['paper'])) : ?>
				<a href="<?php print $variables['back-url-query']; ?>">
					<?php print iish_t('Go back'); ?>
				</a>
				<br/><br/>

				<strong><?php print $variables['paper']; ?></strong>
				<br/><br/>

				<?php if (!is_null($variables['paper']->getCoAuthors()) &&
					(strlen($variables['paper']->getCoAuthors()) > 0)
				) : ?>
					<strong><?php print iish_t('Co-author(s)'); ?>:</strong>
					<?php print $variables['paper']->getCoAuthors(); ?>
					<br/>
				<?php endif; ?>

				<strong><?php print iish_t('Author'); ?>:</strong>
				<?php print $variables['paper']->getUser(); ?>
				<br/><br/>

				<?php print nl2br(check_plain($variables['paper']->getAbstr())); ?>
				<br/>

				<?php // TODO GCU moet op een andere manier ?>
				<?php if (SettingsApi::getSetting(SettingsApi::DOWNLOAD_PAPER_LASTDATE) == '' || SettingsApi::getSetting(SettingsApi::DOWNLOAD_PAPER_LASTDATE) >= date("Y-m-d")) : ?>
					<?php if (!is_null($variables['paper']->getFileSize()) && ($variables['paper']->getFileSize() > 0)) : ?>
						<strong><?php print iish_t('Download paper'); ?>:</strong>
						<a href="<?php print $variables['paperDownloadLinkStart'] . $variables['paper']->getId(); ?>">
							<?php print $variables['paper']->getFileName(); ?>
						</a>
						(<?php print ConferenceMisc::getReadableFileSize($variables['paper']->getFileSize()); ?>)
						<br/>
					<?php endif; ?>
				<?php endif; ?>
			<?php elseif (count($variables['program']) == 0) : ?>
				<span class="eca_warning"><?php print iish_t('Nothing found. Please modify your search criteria.'); ?></span>
			<?php
			else : ?>
				<?php foreach ($variables['program'] as $i => $session) : ?>
					<?php if (($i == 0) || ($session['timeId'] != $variables['program'][$i - 1]['timeId'])) : ?>
						<div class="program_day">
							<?php print date('l j F Y', strtotime($session['day'])); ?>
							<?php print str_replace('  ', ' ', str_replace('-', ' - ', $session['period'])); ?>
						</div>
					<?php endif; ?>

					<strong>
						<a href="?room=<?php print $session['roomId']; ?>"><?php print $session['roomNumber']; ?></a>-<?php print $session['indexNumber']; ?>
						-
						<?php if (SettingsApi::getSetting(SettingsApi::SHOW_SESSION_CODES) == 1): ?>
							<?php print $session['sessionCode']; ?> :
						<?php endif; ?>
						<?php print $variables['highlight']->highlight($session['sessionName']); ?>
					</strong>

					<br/>

					<strong><?php print $session['roomName']; ?></strong>

					<br/>

					<table class="program">
						<tbody>
						<tr>
							<?php $noPlaceForNetwork = 1; ?>
							<?php if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1): ?>
								<?php $noPlaceForNetwork = 0; ?>
								<td width="50%" class="program">
									<?php print (count($session['networks']) > 1) ?
										NetworkApi::getNetworkName(false) . ':' :
										NetworkApi::getNetworkName() . ':'; ?>

									<?php foreach ($session['networks'] as $j => $network) : ?>
										<a href="?network=<?php print $network['networkId']; ?>"><?php print $network['networkName']; ?></a>
										<?php if (count($session['networks']) !==
											$j + 1
										) : ?>, <?php endif; ?>
									<?php endforeach; ?>
								</td>
							<?php endif; ?>

							<?php
								$alwaysHide = SettingsApi::getSetting(SettingsApi::HIDE_ALWAYS_IN_ONLINE_PROGRAM);
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

								print '<td class="program">';

								$hideIfEmpty = SettingsApi::getSetting(SettingsApi::HIDE_IF_EMPTY_IN_ONLINE_PROGRAM);
								$typesToHide = SettingsApi::getArrayOfValues($hideIfEmpty);
								if (count($participants) === 0) {
									if (array_search($type->getId(), $typesToHide) === false) {
										print $type . 's: -';
									}
									else {
										print '&nbsp;';
									}
								}
								else if (count($participants) == 1) {
									print $type . ': ' .
										$variables['highlight']->highlight($participants[0]['participantName']);
								}
								else {
									$names = array();
									foreach ($participants as $participant) {
										$names[] = $variables['highlight']->highlight($participant['participantName']);
									}

									print $type . 's: ' . implode(', ', $names);
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
						<?php print $variables['highlight']->highlight($participant['participantName']); ?><?php if (!is_null($participant['coAuthors']) &&
							(strlen(trim($participant['coAuthors'])) > 0)
						) : ?>, <?php print $variables['highlight']->highlight($participant['coAuthors']); ?><?php endif; ?>:

						<?php if (is_int($variables['networkId'])) : ?>
							<a href="?day=<?php print $session['dayId']; ?>&amp;time=<?php print $session['timeId']; ?>&amp;paper=<?php print $participant['paperId'] ?>&amp;network=<?php print $variables['networkId'] ?>">
								<?php print $variables['highlight']->highlight($participant['paperName']); ?>
							</a>
						<?php elseif ($variables['textsearch'] !== null) : ?>
							<a href="?day=<?php print $session['dayId']; ?>&amp;time=<?php print $session['timeId']; ?>&amp;paper=<?php print $participant['paperId'] ?>&amp;textsearch=<?php print $variables['textsearch'] ?>">
								<?php print $variables['highlight']->highlight($participant['paperName']); ?>
							</a>
						<?php elseif (is_int($variables['roomId'])) : ?>
							<a href="?day=<?php print $session['dayId']; ?>&amp;time=<?php print $session['timeId']; ?>&amp;paper=<?php print $participant['paperId'] ?>&amp;room=<?php print $variables['roomId'] ?>">
								<?php print $variables['highlight']->highlight($participant['paperName']); ?>
							</a>
						<?php else : ?>
							<a href="?day=<?php print $session['dayId']; ?>&amp;time=<?php print $session['timeId']; ?>&amp;paper=<?php print $participant['paperId'] ?>">
								<?php print $variables['highlight']->highlight($participant['paperName']); ?>
							</a>
						<?php endif; ?>

						<?php // TODO GCU moet op een andere manier ?>
						<?php if (SettingsApi::getSetting(SettingsApi::DOWNLOAD_PAPER_LASTDATE) == '' || SettingsApi::getSetting(SettingsApi::DOWNLOAD_PAPER_LASTDATE) >= date("Y-m-d")) : ?>
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
					<?php endforeach; ?>

					<br /><br />

					<?php if ((($i + 1) < count($variables['program'])) &&
						($session['timeId'] != $variables['program'][$i + 1]['timeId'])
					) : ?>
						<hr class="program_hr"/>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</td>
	</tr>
	</tbody>
</table>