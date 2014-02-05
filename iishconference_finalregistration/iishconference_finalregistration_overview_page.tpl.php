<div id="final-registration-overview">
	<span class="final-registration-overview-header">
		<?php print t('Order description:'); ?>
	</span>

	<ul>
		<li><?php print LoggedInUserDetails::getParticipant()->getFeeAmount(); ?></li>

		<?php foreach (LoggedInUserDetails::getParticipant()->getExtras() as $extra) : ?>
			<li><?php print $extra; ?></li>
		<?php endforeach; ?>

		<li>
			<span class="final-registration-overview-total">
				<?php print t('Total amount:'); ?>
				<?php print LoggedInUserDetails::getParticipant()->getTotalAmout(); ?>
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
			<li><?php print check_markup(check_plain(LoggedInUserDetails::getUser()->getAddress())); ?></li>
		</ul>
	<?php endif; ?>
</div>

<?php print drupal_render($variables['form']['back']); ?>

<?php if (LoggedInUserDetails::getParticipant()->getTotalAmout() == 0) : ?>
	<?php unset($variables['form']['payway']); ?>
	<?php unset($variables['form']['bank_transfer']); ?>

	<?php print drupal_render_children($variables['form']); ?>
<?php else : ?>
	<?php unset($variables['form']['confirm']); ?>

	<div id="payment-buttons">
		<?php if (!$variables['bank_transfer_open']) : ?>
			<?php unset($variables['form']['bank_transfer']); ?>
		<?php endif; ?>

		<?php print drupal_render_children($variables['form']); ?>
	</div>
<?php endif; ?>

<div>
    <?php print drupal_render($variables['email-addresses']); ?>
</div>

