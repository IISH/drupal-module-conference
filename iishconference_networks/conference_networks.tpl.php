<?php foreach ($variables['networks'] as $network) : ?>
	<h3><?php print l($network->getName(), '/' . SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) .
			NetworkApi::getNetworkName(false, true) . '/' . $network->getId()); ?></h3>

	<div class="network">
		<p><?php print ConferenceMisc::getCleanHTML($network->getComment()); ?></p>
		<span class="chair-title"><?php print t('Chairs') . ':'; ?></span>

		<?php foreach ($network->getChairs() as $chair) : ?>
			<div class="chair">
				<span class="chair-name"><?php print $chair->getFullName(); ?></span>
				<span class="chair-location"><?php print $chair->getLocationDetails(); ?></span>
				<span class="chair-email"><?php print l($chair->getEmail(), 'mailto:' . $chair->getEmail(),
						array('absolute' => true)); ?></span>
				<span class="mailto"></span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endforeach; ?>
