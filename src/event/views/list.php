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
	<table class="table table-sm" id="<?= $_table = strings::rand() ?>">
    <thead class="small">
      <td style="width: 70px;" class="text-center">order</td>
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
      <tr
        data-id="<?= $dto->id ?>"
        data-system_event="<?= (int)$dto->system_event ?>"
        data-order="<?= $dto->order ?>"
        data-hidden="<?= $hidden ? 'yes' : 'no' ?>">

        <td class="text-center" order><?= $dto->order ?></td>
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
        <td class="text-center"><?= $daoDE->IconFor( $dto->event_name) ?></td>
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
	$('#<?= $_table ?> > tbody > tr').each( function( i, tr) {
		let _tr = $(tr);

		/*---[ diary event item ]---*/
		_tr
    .addClass( 'pointer')
    .on( 'click', function( e) {
			let _me = $(this);
      _me.trigger( 'edit');

    })
		.on( 'contextmenu', function( e ) {
			if ( e.shiftKey)
				return;

			e.stopPropagation(); e.preventDefault();

			_.hideContexts();
			let _me = $(this);
			let _data = _me.data();
			let _context = _.context();

			_context.append( $('<a href="#"><i class="bi bi-pencil"></i><strong>edit</strong></a>').on( 'click', function( e) {
				e.stopPropagation();e.preventDefault();

				_context.close();
				_me.trigger( 'edit');

			}));

			let ctrl = $('<a href="#">hide</a>').on( 'click', function( e ) {
				e.stopPropagation();e.preventDefault();

				_context.close();

				_.post({
					url : _.url('<?= $this->route ?>'),
					data : {
						action : 'toggle-hide-event',
						id : _data.id

					}

				}).then( function( d) {
					_.growl( d);
					if ( 'ack' == d.response) {
						_me.data('hidden', d.hidden == '1' ? 'yes' : 'no');
						$('[HideFromMe]', _me).html( d.hidden == '1' ? '<?= strings::html_tick ?>' : '&nbsp;');

					}

				});

			});

			if ( 'yes' == _data.hidden) ctrl.prepend('<i class="bi bi-check"></i>');

			_context.append( ctrl);

      if ( '' != _data.order) {
        _context.append( $('<a href="#"><i class="bi bi-caret-up"></i>move up</a>').on( 'click', function( e) {
          e.stopPropagation();e.preventDefault();

          _context.close();
          _me.trigger( 'move-up');

        }));

      }

			_context.append( $('<a href="#"><i class="bi bi-caret-down"></i>move down</a>').on( 'click', function( e) {
				e.stopPropagation();e.preventDefault();

				_context.close();
				_me.trigger( 'move-down');

			}));

			<?php	if ( currentUser::isAdmin()) {	?>

				if ( _data.system_event != '1') {
					_context.append( '<hr>');
					_context.append( $('<a href="#"><i class="bi bi-trash"></i>delete</a>').on( 'click', function( e) {
						e.stopPropagation();e.preventDefault();

						_context.close();
						_me.trigger( 'delete');

					}));

				}

			<?php	}	// if ( currentYser::isAdmin())	?>

			_context.open( e);

		})
    .on( 'delete', function(e) {
      let _tr = $(this);

      _.ask.alert({
        text: 'Are you sure ?',
        title: 'Confirm Delete',
        buttons : {
          yes : function(e) {

            $(this).modal('hide');
            _tr.trigger( 'delete-confirmed');

          }

        }

      });

    })
    .on( 'delete-confirmed', function(e) {
      let _tr = $(this);
      let _data = _tr.data();

      _.post({
        url : _.url('<?= $this->route ?>'),
        data : {
          action : 'delete',
          id : _data.id

        },

      }).then( d => {
        if ( 'ack' == d.response) {
          _tr.remove();
          $('#<?= $_table ?>').trigger('update-line-numbers');

        }
        else {
          _.growl( d);

        }

      });

    })
    .on( 'edit', function(e) {
      let _tr = $(this);
      let _data = _tr.data();

			_.get.modal( '<?= $this->route ?>/edit/' + _data.id)
			.then( modal => modal.on( 'success', e => window.location.reload()));

    })
    .on( 'move-down', function(e) {
      let _tr = $(this);
      let _data = _tr.data();

      _.post({
        url : _.url('<?= $this->route ?>'),
        data : {
          action : 'move',
          id : _data.id,
          direction : 'down'

        },

      }).then( d => {
        if ( 'ack' == d.response) {
          _tr.data('order', d.order);

          $('[order]', _tr).html(d.order);

          _.table.sortOn( '#<?= $_table ?>', 'order', 'string', 'asc');

        }
        else {
          _.growl( d);

        }

      });

    })
    .on( 'move-up', function(e) {
      let _tr = $(this);
      let _data = _tr.data();

      _.post({
        url : _.url('<?= $this->route ?>'),
        data : {
          action : 'move',
          id : _data.id,
          direction : 'up'

        },

      }).then( d => {
        if ( 'ack' == d.response) {
          _tr.data('order', d.order);

          $('[order]', _tr).html(d.order);

          _.table.sortOn( '#<?= $_table ?>', 'order', 'string', 'asc');

        }
        else {
          _.growl( d);

        }

      });

    });
		/*---[ diary event item ]---*/

	});

}))( _brayworth_);
</script>
