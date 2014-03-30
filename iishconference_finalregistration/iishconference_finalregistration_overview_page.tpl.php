<div id="final-registration-overview">
	<span class="final-registration-overview-header">
		<?php print t('Order description:'); ?>
	</span>

	<ul>
		<li><?php print $variables['fee-amount']; ?></li>

		<?php foreach ($variables['extras'] as $extra) : ?>
			<li><?php print $extra; ?></li>
		<?php endforeach; ?>

		<?php if (SettingsApi::getSetting(SettingsApi::SHOW_ACCOMPANYING_PERSONS) == 1) : ?>
			<?php foreach ($variables['accompanying-persons'] as $accompanyingPerson) : ?>
				<li><?php print $accompanyingPerson . ' ' . $variables['fee-amount-accompanying-person']; ?></li>
			<?php endforeach; ?>
		<?php endif; ?>

		<li>
			<span class="final-registration-overview-total">
				<?php print t('Total amount:'); ?>
				<?php print ConferenceMisc::getReadableAmount($variables['total-amount']); ?>
			</span>
		</li>
	</ul>

	<span class="final-registration-overview-header">
		<?php print t('You have indicated to be present on the following days:'); ?>
	</span>

	<ul>
		<?php foreach (LoggedInUserDetails::getUser()->getDaysPresent() as $day) : ?>
			<li><?php print $day; ?></li>
		<?php endforeach; ?>
	</ul>

	<?php if (LoggedInUserDetails::getParticipant()->getInvitationLetter()) : ?>
		<span class="final-registration-overview-header">
			<?php print t('In addition, you also indicated that an invitation letter should be send to:'); ?>
		</span>

		<ul>
			<li><?php print ConferenceMisc::getCleanHTML(LoggedInUserDetails::getUser()->getAddress()); ?></li>
		</ul>
	<?php endif; ?>
</div>

<?php print drupal_render($variables['form']['back']); ?>

<?php if ($variables['total-amount'] == 0) : ?>
	<?php unset($variables['form']['payway']); ?>
	<?php unset($variables['form']['bank_transfer']); ?>

	<?php print drupal_render_children($variables['form']); ?>
<?php else : ?>
	<?php unset($variables['form']['confirm']); ?>

	<div id="payment-buttons">
		<?php if (!$variables['bank_transfer_open']) : ?>
			<?php unset($variables['form']['bank_transfer']); ?>

			<span class="eca_warning">
				<?php print t('It is no longer possible to pay via bank transfer, please make an online payment.'); ?>
			</span>
		<?php endif; ?>

		<?php print drupal_render_children($variables['form']); ?>
	</div>
<?php endif; ?>

<?php print ConferenceMisc::getInfoBlock(); ?>

