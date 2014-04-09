<div class="iishconference_container">
	<span class="iishconference_container_header"><?php print $variables['paper']->getTitle(); ?></span>

	<div>
		<span class="iishconference_container_label"><?php print t('Uploaded paper '); ?></span>

		<?php if (is_null($variables['paper']->getFileSize()) || ($variables['paper']->getFileSize() === 0)) : ?>
			<?php print t('No paper uploaded yet'); ?>
		<?php else : ?>
			<a href="<?php print $variables['paperDownloadLink']; ?>"><?php print $variables['paper']->getFileName(); ?></a>
			(<?php print ConferenceMisc::getReadableFileSize($variables['paper']->getFileSize()); ?>)
		<?php endif; ?>

		<div>
			<form enctype="multipart/form-data" action="<?php print $variables['actionUrl'] ?>" method="post"
			      id="upload-paper" accept-charset="UTF-8">

				<div class="form-item form-type-managed-file form-item-upload-paper">
					<label for="upload-paper"><?php print t('Upload paper'); ?> </label>

					<div id="upload-paper" class="form-managed-file">
						<input type="file" id="paper-file" name="paper-file" size="22" class="form-file">
					</div>
					<div class="description">
						<?php print t('The file can\'t be larger than @size. Only files with the following extensions are allowed: @extensions.',
							array('@size' => $variables['maxSize'], '@extensions' => $variables['extensions'])); ?>
					</div>
				</div>

				<input type="hidden" id="paper-id" name="paper-id"
				       value="<?php print $variables['paper']->getId(); ?>"/>
				<input type="hidden" id="back-url" name="back-url" value="<?php print$url =
					(!empty($_SERVER['HTTPS'])) ? "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] :
						"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']; ?>"/>

				<input type="submit" id="upload-paper" name="upload-paper"
				       value="<?php print t('Upload new/replace paper'); ?>" class="form-submit"/>
			</form>

			<div>
				<?php print drupal_render($variables['form']); ?>
				<?php print l(t('Go back to your personal page'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'personal-page'); ?>
			</div>
		</div>
	</div>
</div>