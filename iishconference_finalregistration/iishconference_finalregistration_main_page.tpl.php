<?php if (array_key_exists('days_present', $variables['form']) && (SettingsApi::getSetting(SettingsApi::PAYMENT_SHOW_DAYS_SESSION_PLANNED) == 1)) : ?>
	<?php if (count($variables['session-days']) > 0) : ?>
		<div id="session-days-hint">
			<span class="hint-message">
			  <?php print t('Please note that you are scheduled for sessions on the following days:'); ?>
			</span>
			<table class="hint-days">
				<?php foreach ($variables['session-days'] as $i => $day) : ?>
					<?php if ($i % 2 == 0) : ?>
						<tr>
						<td><?php print $day; ?></td>
					<?php else : ?>
						<td><?php print $day; ?></td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</table>
		</div>
	<?php endif; ?>
<?php endif; ?>

<?php if (array_key_exists('days_present', $variables['form'])) : ?>
	<div id="final-registration-welcome">
		<?php print t('Welcome @name,', array('@name' => LoggedInUserDetails::getUser())); ?>
		<br /><br />
		<?php print t('This is the first page of the \'Final Registration and Payment\' procedure.
			Please enter which days you will be present and the total conference fee will be computed automatically.
			You can pay with your CreditCard/iDeal or via bank transfer.
			If the process is completely finished, (including payment) you will receive a confirmation email from our payment provider and a confirmation email from the @conference.',
			array('@conference' => CachedConferenceApi::getEventDate()->getEvent()->getShortName()));
		?>
	</div>
<?php endif; ?>

<?php print drupal_render_children($variables['form']); ?>

<?php print ConferenceMisc::getInfoBlock(); ?>