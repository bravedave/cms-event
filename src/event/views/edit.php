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

use strings;
use theme;

$dto = $this->data->dto;  ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
	<input type="hidden" name="id" value="<?= $dto->id ?>">
	<input type="hidden" name="action" value="diary-event-save">

	<div class="modal fade" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header <?= theme::modalHeader() ?> py-2">
					<h5 class="modal-title" id="<?= $_modal ?>Label"><?= $this->title ?></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>

				</div>

				<div class="modal-body">
					<div class="form-row mb-2">
						<label class="col-3 col-form-label" for="<?= $_uid = strings::rand() ?>">Event Name</label>

						<div class="col">
							<input type="text" name="event_name" class="form-control" id="<?= $_uid ?>" value="<?= $dto->event_name ?>" <?= $dto->system_event ? 'disabled' : '' ?>>

						</div>

					</div>

					<div class="form-row mb-2">
						<label class="col-3 col-form-label" for="<?= $_uid = strings::rand() ?>">Event Type</label>

						<div class="col">
							<select name="event_type" class="form-control" id="<?= $_uid ?>">
								<option value="enquiry" <?php if ( $dto->event_type == '' || $dto->event_type == 'enquiry') print 'selected'; ?>>Enquiry</option>
								<option value="inspect" <?php if ( $dto->event_type == 'inspect') print 'selected'; ?>>Inspection</option>

							</select>

						</div>

					</div>

					<div class="form-row mb-2">
						<div class="col-3 col-form-label" for="<?= $_uid = strings::rand() ?>">Calendar</div>

						<div class="col">
							<select name="calendar" id="<?= $_uid ?>" class="form-control">
								<option value="">not a calendar event</option>
								<?php
								foreach ( config::calendars as $k => $v) {
									printf(
										'<option value="%s" %s>%s</option>',
										$v,
										$dto && $v == $dto->calendar ? 'selected' : '',
										$k

									);

								} ?>

							</select>

						</div>

					</div>

					<div class="form-row mb-2">
						<div class="offset-3 col">
							<div class="form-check">
								<input type="checkbox" class="form-check-input" name="comment_not_required" id="<?= $_uid = strings::rand() ?>" value="1"
									<?= $dto->comment_not_required ? 'checked' : '' ?>>

								<label class="form-check-label" for="<?= $_uid ?>">
									Comment NOT Required

								</label>

							</div>

						</div>

					</div>

					<div class="form-row mb-2">
						<div class="col-3">Triggers</div>

						<div class="col">
							<div class="form-check">
								<input type="checkbox" class="form-check-input" name="prospective_seller" id="<?= $_uid = strings::rand() ?>" value="1"
									<?= $dto->prospective_seller ? 'checked' : '' ?> <?= $dto->system_event ? 'disabled' : '' ?>>

								<label class="form-check-label" for="<?= $_uid ?>">
									Mark Property as Prospective Sell

								</label>

							</div>

							<div class="form-check">
								<input class="form-check-input" type="checkbox" name="appointment_inspection" id="<?= $_uid = strings::rand() ?>" value="1" disabled
									<?= $dto->appointment_inspection ? 'checked' : '' ?>>

								<label class="form-check-label" for="<?= $_uid ?>">
									Triggers Appointment Inspections

								</label>

							</div>

						</div>

					</div>

					<div class="form-row mb-2">
						<label class="col-3 col-form-label" for="<?= $_uid = strings::rand() ?>">Order</label>

						<div class="col">
							<input type="number" id="<?= $_uid ?>" name="order" maxlength="3" class="form-control" value="<?= trim( $dto->order) ?>">

						</div>

					</div>

					<div class="form-row mb-2">
						<label class="col-3 col-form-label" for="<?= $_uid = strings::rand() ?>">Icon</label>

						<div class="col">
							<input type="text" name="icon" maxlength="2" class="form-control" id="<?= $_uid ?>" value="<?= $dto->icon ?>" <?= $dto->system_event ? 'disabled' : '' ?>>

						</div>

					</div>

					<div class="form-row mb-2">
						<div class="col text-muted">
							NOTE: If you change the event - and there are existing
							events for this event, then you will detach that event
							- probably NOT what you want to do

						</div>

					</div>

				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">close</button>
					<button type="submit" class="btn btn-primary">Save</button>

				</div>

			</div>

		</div>

	</div>

	<script>
	( _ => {
		$('#<?= $_form ?>')
		.on( 'submit', function( e) {
			let _form = $(this);
			let _data = _form.serializeFormJSON();

			_.post({
				url : _.url('<?= $this->route ?>'),
				data : _data,

			}).then( d => {
				if ( 'ack' == d.response) {
          $('#<?= $_modal ?>').trigger( 'success');

				}
				else {
					_.growl( d);

				}

				$('#<?= $_modal ?>').modal( 'hide');

			});

			// console.table( _data);

			return false;

		});

		let checkIfSystemEvent = () => {
			let sysEvents = <?= json_encode( config::system_events ) ?>;

			$('#icon')
			.prop( 'disabled', sysEvents.indexOf( $('#event_name').val()) > -1);

		}

		$(document).ready( () => {
			checkIfSystemEvent();
			$('#event_name').on( 'change', checkIfSystemEvent);

		});

	})( _brayworth_);
	</script>

</form>
