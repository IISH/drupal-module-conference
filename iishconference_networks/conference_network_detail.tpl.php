<p><?php print ConferenceMisc::getCleanHTML($network->getComment()); ?></p>
<span class="chair-title"><?php print t('Chairs') . ':'; ?></span>

<?php foreach ($network->getChairs() as $chair) : ?>
	<div class="chair">
		<span class="chair-name"><?php print $chair->getFullName(); ?></span>
		<span class="chair-location"><?php print $chair->getLocationDetails(); ?></span>
		<span class="chair-email"><?php print l($chair->getEmail(), 'mailto:' . $chair->getEmail(), array('absolute' => true)); ?></span>
		<span class="mailto"></span>
	</div>
<?php endforeach; ?>

<p><?php print ConferenceMisc::getCleanHTML($network->getLongDescription()); ?></p>