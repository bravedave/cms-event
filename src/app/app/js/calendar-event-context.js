/**
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
 * */

( _ => {
  $(document).on( 'calendar-event-context', (e, event) => {
    if (!event.data.feed.data) return;

    let data = JSON.parse( event.data.feed.data);
    // console.log(typeof data);
    // console.log(event);
    if (!/^(dav|sales-calendar|user-calendar)$/.test( data.type)) return;

    let sendData = {}

    if ( 'sales-calendar' == data.type) {
      let id = String(event.data.event.id)
        .replace(/@.*$/, '')
        .replace(/^property-diary-/, '');

      if (Number(id) > 0) {
        e.stopPropagation();e.preventDefault();

        _brayworth_.hideContexts();

        let _context = _brayworth_.context();

        _context.append( $('<a href="#">delete</a>').on( 'click', function( e) {
          e.stopPropagation(); e.preventDefault();

          _context.close();

          _.ask.alert({
            text: 'Are you sure ?',
            title: 'Confirm Delete',
            buttons: {
              yes: function (e) {

                $(this).modal('hide');

                _.post({
                  url : _.url('event'),
                  data : {
                    action: 'appointment-delete',
                    id : id

                  },

                }).then( d => {
                  if ( 'ack' == d.response) {
                    $(document).trigger('load-active-feeds');

                  }
                  else {
                    _.growl( d);

                  }

                });

              }

            }

          });

        }));

        _context.open( event.originalEvent);

      }

    }

  });

  // $(document).ready(() => console.log( 'calendar-event-context loaded'));

})( _brayworth_);

