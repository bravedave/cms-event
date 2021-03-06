/**
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
 * */

( _ => {
  $(document).on('calendar-toolbar-created', (e, toolbar) => {
    let _toolbar = $( toolbar);
    let _parent = _toolbar.closest( '[data-date]');

    if ( _parent.length > 0) {
      let _data = _parent.data();

      let btn = $('<button class="btn btn-light" type="button">new event</button>');
      btn.on( 'click', function( e) {
        e.stopPropagation();e.preventDefault();

        _.get.modal( _.url( 'event/appointment'))
        .then( modal => {
          $('input[name="date"]', modal).val( _data.date);
          modal.on('shown.bs.modal', e => $('input[name="start"]', modal).focus());

        });

      })
      btn.prependTo( toolbar);

    }

  });

})( _brayworth_);
