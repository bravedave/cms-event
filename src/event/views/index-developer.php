<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace cms\event;

use strings;	?>

<ul class="nav flex-column">
	<li class="nav-item"><a class="nav-link" href="#" id="<?= $_uid = strings::rand() ?>"><i class="bi bi-calendar-plus"></i> Appointment</a></li>

</ul>
<script>
( _ => {
	$('#<?= $_uid ?>').on( 'click', function( e) {
		e.stopPropagation();e.preventDefault();

		_.get.modal( _.url( '<?= $this->route ?>/appointment'));

	});

}) (_brayworth_);
</script>

