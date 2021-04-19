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

use currentUser;
use strings;

if ( $this->data->dto) {
	printf(
		'<h5><a href="%s">Edit Diary Events #%s</a></h5>',
		strings::url( $this->route . '/edit/' . $this->data->dto->id),
		$this->data->dto->id

	);

}
else {
	printf(
		'<h5><a href="%s">Diary Events</a></h5>',
		strings::url( $this->route)

	);


}	// if ( $this->dto)

if ( currentUser::isAdmin()) {	?>
<ul class="nav flex-column">
	<li class="nav-item"><a class="nav-link" href="#" id="<?=$_uid = strings::rand() ?>"><i class="bi bi-plus"></i> Add new Event</a></li>
	<script>
	( _ => {
		$('#<?= $_uid ?>').on( 'click', e => {
			e.stopPropagation();e.preventDefault();

			_.get.modal( '<?= $this->route ?>/edit/')
			.then( modal => modal.on( 'success', e => window.location.reload()));

		});

	}) (_brayworth_);
	</script>

</ul>
<?php }	// if ( currentUser::isAdmin())	?>

<?php if ( currentUser::diaryEventOrder()) {	?>

<div class="alert alert-warning">
	<h4 class="alert-heading">Diary Event Order</h4>
	note: the order displayed here is affected by your diary-event-order which is : <strong><?= currentUser::diaryEventOrder() ?></strong>
</div>

<?php }	// if ( currentUser::diaryEventOrder())	?>
