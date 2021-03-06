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
use strings;	?>

<h1 class="d-none d-print-block"><?= $this->title ?></h1>

<div class="table-responsive">
  <table class="table table-sm">
    <thead class="small">
      <td colspan="3" class="text-center">order</td>
      <td>event</td>
      <td>event type</td>
      <td>calendar</td>
      <td style="width: 70px;" class="text-center">Comment Required</td>
      <td style="width: 70px;" class="text-center">icon</td>
      <td style="width: 70px;" class="text-center">prospective<br />seller</td>
      <td style="width: 70px;" class="text-center">hide</td>

    </thead>
    <tbody>
      <?php
      $daoDE = new dao\diary_events;
      while ( $dto = $this->data->dataset->dto()) {
        $hidden = dao\diary_events::isHidden( $dto);

          ?>
      <tr role="diary_event-item"
        data-id="<?= $dto->id ?>"
        data-system_event="<?= (int)$dto->system_event ?>"
        data-delete="<?= strings::url( 'diary_events/delete/' . $dto->id) ?>"
        data-order="<?= $dto->order ?>"
        data-hidden="<?= $hidden ? 'yes' : 'no' ?>">

        <td style="width: 30px;" class="text-center"><i class="bi bi-caret-up" role="move-up" title="move-up"></i></td>
        <td style="width: 30px;" class="text-center"><?= $dto->order ?></td>
        <td style="width: 30px;" class="text-center"><i class="bi bi-caret-down" role="move-down" title="move-down"></i></td>
        <td><?= $dto->event_name ?></td>
        <td><?= ( $dto->event_type ? $dto->event_type : 'enquiry') ?></td>
        <td><?php
          if ( config::calendar_global == $dto->calendar) {
            print 'global';

          }
          elseif ( config::calendar_sales == $dto->calendar) {
            print 'sales';

          }
          elseif ( config::calendar_rental == $dto->calendar) {
            print 'rental';

          }
          else {
            print '&nbsp';

          } ?></td>
        <td class="text-center"><?= ( $dto->comment_not_required ? '' : strings::html_tick ) ?></td>
        <td class="text-center"><?php
          print $daoDE->IconFor( $dto->event_name);
          ?></td>
        <td class="text-center"><?= ( $dto->prospective_seller == 1 ? 'yes' : 'no' ) ?></td>
        <td class="text-center" HideFromMe><?= ( $hidden ? strings::html_tick : '&nbsp;' ) ?></td>

      </tr>
      <?php
      }	// while ( $this->data->dto()) ?>

    </tbody>

  </table>

</div>
<script>
( _ => $(document).ready( () => {
	$('tr[role="diary_event-item"]').each( function( i, tr) {
		let _tr = $(tr);

		$('[role="move-up"]', tr).addClass('pointer').on( 'click', function( e) { e.stopPropagation(); e.preventDefault(); window.location.href = _.url( 'diary_events/moveup/' + _tr.data('id')); })
		$('[role="move-down"]', tr).addClass('pointer').on( 'click', function( e) { e.stopPropagation(); e.preventDefault(); window.location.href = _.url( 'diary_events/movedown/' + _tr.data('id')); })

		if ( _tr.data('order') == '')
			$('[role="move-up"]', tr).addClass('d-none');

		/*---[ diary event item ]---*/
		_tr.on( 'contextmenu', function( e ) {
			if ( e.shiftKey)
				return;

			e.stopPropagation(); e.preventDefault();

			_.hideContexts();
			let _me = $(this);
			let _data = _me.data();
			let _context = _.context();

			_context.append( $('<a href="#"><i class="bi bi-pencil"></i><strong>edit</strong></a>').on( 'click', function( e) {
				e.stopPropagation();e.preventDefault();

				_.get.modal( '<?= $this->route ?>/edit/' + _data.id)
				.then( modal => modal.on( 'success', e => window.location.reload()));

			}));

			let ctrl = $('<a href="#">hide</a>').on( 'click', function( e ) {
				e.stopPropagation();e.preventDefault();

				_context.close();

				_.post({
					url : _.url('diary_events'),
					data : {
						action : 'toggle-hide-event',
						id : _tr.data('id')

					}

				}).then( function( d) {
					_.growl( d);
					if ( 'ack' == d.response) {
						_tr.data('hidden', d.hidden == '1' ? 'yes' : 'no');
						$('[HideFromMe]', tr).html( d.hidden == '1' ? '<?= strings::html_tick ?>' : '&nbsp;');

					}

				});

			});

			if ( 'yes' == _tr.data('hidden')) {
				ctrl.prepend('<i class="bi bi-check"></i>');

			}

			_context.append( ctrl);

	<?php	if ( currentUser::isAdmin()) {	?>

			if ( _tr.data('system_event') != '1') {
				_context.append( '<hr />');
				_context.append( $('<a href="#"><i class="bi bi-trash"></i>delete</a>').on( 'click', function( e ) {
					e.stopPropagation();e.preventDefault();

					_context.close();

					_.modal({
						title: 'Are you Sure ?',
						text : 'delete this record',
						width: 350,
						buttons : {
							yes : function() {
								hourglass.on();
								window.location.href = _tr.data('delete');

							}

						}

					});

				}));

			}

	<?php	}	// if ( currentYser::isAdmin())	?>

			_context.open( e);

		});
		/*---[ diary event item ]---*/

	});

}))( _brayworth_);
</script>
