Placeholders are mapped to the <a href="<?php echo untrailingslashit( CAPR_URI ) ?>/assets/images/codeable-review-object-dump.png" target="_blank">review object properties</a>, and accessed using dot notation, <strong>Examples:</strong>
<ul>
	<li>
		<strong><code>{task_title}</code></strong> = Task Title of the review
	</li>
	<li>
		<strong><code>{timestamp}</code></strong> = Date & time of the review
	</li>
	<li>
		<strong><code>{reviewer.full_name}</code></strong> = Client's name
	</li>
	<li>
		<strong><code>{reviewer.avatar.medium_url}</code></strong> = Client's medium size avatar URL
	</li>
</ul>