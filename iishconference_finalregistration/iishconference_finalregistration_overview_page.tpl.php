<div id="final-registration-overview">
	<span class="final-registration-overview-header">
		<?php print iish_t('Order description:'); ?>
	</span>

	<ul>
		<li><?php print $variables['fee-amount-description']; ?></li>

		<?php foreach ($variables['extras'] as $extra) : ?>
			<li><?php print $extra; ?></li>
		<?php endforeach; ?>

		<?php if (SettingsApi::getSetting(SettingsApi::SHOW_ACCOMPANYING_PERSONS) == 1) : ?>
			<?php foreach ($variables['accompanying-persons'] as $accompanyingPerson) : ?>
				<li><?php print $accompanyingPerson . ' ' .
						$variables['fee-amount-accompanying-person-description']; ?></li>
			<?php endforeach; ?>
		<?php endif; ?>

		<li>
			<span class="final-registration-overview-total">
				<?php print iish_t('Total amount') . ':'; ?>
				<?php print ConferenceMisc::getReadableAmount($variables['total-amount']); ?>

				<?php if ($variables['payment_on_site_open']) : ?>
					<?php print '(' . iish_t('If payed on site') . ':'; ?>
					<?php print ConferenceMisc::getReadableAmount($variables['total-amount-pay-on-site']) . ')'; ?>
				<?php endif; ?>
			</span>
		</li>
	</ul>

	<?php if (SettingsApi::getSetting(SettingsApi::SHOW_DAYS) == 1) : ?>
		<span class="final-registration-overview-header">
			<?php print iish_t('You have indicated to be present on the following days:'); ?>
		</span>

		<ul>
			<?php foreach ($variables['days'] as $day) : ?>
				<li><?php print $day; ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ($variables['invitation-letter']) : ?>
		<span class="final-registration-overview-header">
			<?php print iish_t('In addition, you also indicated that an invitation letter should be send to:'); ?>
		</span>

		<ul>
			<li><?php print ConferenceMisc::getCleanHTML($variables['address']); ?></li>
		</ul>
	<?php endif; ?>

	<?php if (strlen(trim(SettingsApi::getSetting(SettingsApi::GENERAL_TERMS_CONDITIONS_LINK))) > 0) : ?>
		<?php print drupal_render($variables['form']['terms_and_conditions']); ?>
	<?php endif; ?>
</div>

<?php print drupal_render($variables['form']['back']); ?>

<?php if ($variables['total-amount'] == 0) : ?>
	<?php unset($variables['form']['payway']); ?>
	<?php unset($variables['form']['bank_transfer']); ?>
	<?php unset($variables['form']['on_site']); ?>

	<?php print drupal_render_children($variables['form']); ?>
<?php else : ?>
	<?php unset($variables['form']['confirm']); ?>

	<div id="payment-buttons">
		<?php if (SettingsApi::getSetting(SettingsApi::BANK_TRANSFER_ALLOWED) == 1) : ?>
			<?php if (!$variables['bank_transfer_open']) : ?>
				<?php unset($variables['form']['bank_transfer']); ?>

				<span class="eca_warning">
                    <?php print iish_t('It is no longer possible to pay via bank transfer, please make an online payment.'); ?>
                </span>
			<?php endif; ?>
		<?php endif; ?>

        <?php if (!$variables['payment_on_site_open']) : ?>
            <?php unset($variables['form']['on_site']); ?>
        <?php endif; ?>

		<?php print drupal_render_children($variables['form']); ?>
	</div>
<?php endif; ?>

<?php print ConferenceMisc::getInfoBlock(); ?>

