/**
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
 * example, check for reminders script
 *
 * */

( _ => {
  let remindThePunter = () => {
    let timeout = 22000;  // 22 seconds

    if ( _.isWindowHidden()) {
      setTimeout(remindThePunter, timeout);
      return;

    }

    let bucket = () => {
      let d = $('body > div.reminder-toast-bucket');
      if ( d.length > 0) return d[0];

      // console.log( 'create toast bucket');
      d = $('<div style="top:1em;right:1em;z-index:1200;position:absolute;" class="reminder-toast-bucket"></div>').appendTo('body');
      return d[0];

    };

    $('> div.toast', bucket()).each( (i,el) => $(el).attr('found', 'no'));

    _.post({
      url: _.url('event'),
      data: {
        action: 'get-reminders'
      },

    }).then(d => {
      if ( 'ack' == d.response) {
        $.each( d.data, (e,reminder) => {
          let toast = $('> div.toast[reminder="' + reminder.id + '"]', bucket());

          if ( toast.length == 0) {
            toast = $('<div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="false"></div>');
            let header = $('<div class="toast-header"></div>').appendTo( toast);
            header.append( '<i class="bi bi-square-fill text-primary"></i>');
            header.append( '<strong class="ml-1 mr-auto">Reminder</strong>');
            $('<small remindertime></small>').appendTo(header);

            header.append( '<button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close"><span aria-hidden="true">&times;</span></button>');

            $('<div class="toast-body"></div>').appendTo( toast);

          }

          let x = _.dayjs(reminder.date_start);
          $('[remindertime]', toast).html(x.format('h:mm a'));

          $('.toast-body', toast).html(reminder.subject);

          toast
          .attr('found', 'yes')
          .attr('reminder', reminder.id)
          .data('reminder', reminder)
          .on('hide.bs.toast', function(e) {
            let _me = $(this);
            let _data = _me.data();

            _.post({
              url : _.url('event'),
              data : {
                action: 'reminder-dismiss',
                id : _data.reminder.id

              },

            }).then( d => _.growl( d));

          });

          toast.appendTo(bucket());
          toast.toast('show');
          // console.log( reminder);

        });

        // console.log(d.data);

      }
      else {
        console.log(d)

      }

    });

    $('> div.toast[found="no"]', bucket()).each((i, el) => $(el).remove());

    setTimeout(remindThePunter, timeout);

  };

  $(document).ready( remindThePunter);

})( _brayworth_);