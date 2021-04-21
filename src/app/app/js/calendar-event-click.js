/**
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
 * */

( _ => {
  $(document).on( 'calendar-event-click', (e, event) => {
    let id = String( event.data.event.id)
      .replace( /@.*$/, '')
      .replace( /^property-diary-/, '');

      // console.log( 'click', id);

    if ( Number( id) > 0) {
      _.post({
        url : _.url('event'),
        data : {
          action : 'property-diary-get-by-id',
          id : id

        },

      }).then( d => {
        if ( 'ack' == d.response) {
          let start = _.dayjs( d.data.date_start);
          let end = _.dayjs( d.data.date_end);

          _.get.modal(_.url('event/appointment'))
            .then(modal => {

              let form = modal.closest( 'form');

              $('input[name="id"]', form).val(d.data.id);
              $('input[name="date"]', form).val(start.format( 'YYYY-MM-DD'));

              $('input[name="start"]', form).val(start.format( 'h:m a'));
              CheckTimeFormat.call( $('input[name="start"]', form)[0]);

              $('input[name="end"]', form).val(end.format( 'h:m a'));
              CheckTimeFormat.call( $('input[name="end"]', form)[0]);

              $('input[name="people_id"]', form).val(d.data.people_id);
              $('input[name="people_name"]', form).val(d.data.people_name);
              $('input[name="property_id"]', form).val(d.data.property_id);
              $('input[name="address_street"]', form).val(d.data.address_street);
              $('select[name="event"]', form).val(d.data.event_name);
              if (d.data.location != d.data.address_street) {
                $('input[name="location"]', form).val(d.data.location);

              }
              $('textarea[name="notes"]', form).val(d.data.comments);

              $('input[name="attendants\[\]"]', modal).prop( 'checked', false);
              if ( !!d.data.attendants) {
                let attendants = JSON.parse( d.data.attendants);
                $.each( attendants, ( i, attendant) => {
                  // console.log( attendant);
                  $('input[name="attendants\[\]"][value="' + attendant + '"]', modal).prop( 'checked', true);

                });

              }

              /**--- ---[notify users restore]--- ---*/
              $('input[name="notify_users\[\]"]', modal).prop( 'checked', false);
              if (!!d.data.notify_users) {
                let notify_users = JSON.parse(d.data.notify_users);
                $.each(notify_users, (i, user) => $('input[name="notify_users\[\]"][value="' + user + '"]', modal).prop( 'checked', true));

              }
              $('textarea[name="notify_message"]', form).val(d.data.notify_message);
              /**--- ---[end: notify users restore]--- ---*/

              $('input[name="target_user"][value="' + d.data.target_user + '"]', modal).prop('checked', true);

              if ( 1 == d.data.notify_reminder) {
                $('input[name="notify_reminder"]', modal).prop('checked', true);

              }

              modal
              .off('shown.bs.modal')
              .on('shown.bs.modal', e => $('input[name="start"]', modal).focus());

              modal.on('success', e => $(document).trigger('load-active-feeds'));

            });

          // console.table( d.data);

        }
        else {
          _.growl( d);

        }

      });

    }

    // console.log( id);
    // console.log( event);

  });

})( _brayworth_);
