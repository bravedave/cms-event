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
use theme;  ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="action" value="appointment-post">
  <input type="hidden" name="id">
  <input type="hidden" name="people_id">
  <input type="hidden" name="people_email"><!-- not saved, required to activate send invite -->
  <input type="hidden" name="property_id">
  <input type="hidden" name="multiday" value="0">
  <div class="modal fade" data-backdrop="static" tabindex="-1" role="dialog" id="<?= $_modal = strings::rand() ?>" aria-labelledby="<?= $_modal ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header <?= theme::modalHeader() ?> py-2">
          <h5 class="modal-title" id="<?= $_modal ?>Label"><?= $this->title ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <div class="accordion" id="<?= $_accordion = strings::rand() ?>">
            <div id="<?= $_accordion ?>_appointment" class="collapse show" aria-labelledby="<?= $_accordion ?>_appointment_heading" data-parent="#<?= $_accordion ?>">
              <div class="form-row mb-2"><!-- date, start time, end time -->
                <div class="col mb-2 mb-md-0">
                  <div class="input-group">
                    <input type="date" class="form-control" name="date" required>

                    <div class="input-group-append d-none" data-set="date_end" data-frame="multiday">
                      <div class="input-group-text">-</div>
                    </div>

                    <input type="date" class="form-control d-none" name="date_end" data-set="date_end" data-frame="multiday">

                  </div>

                </div>

                <div class="col-md" data-set="date_end" data-frame="oneday">
                  <div class="form-row">
                    <div class="col">
                      <div class="input-group">
                        <input type="text" class="form-control" name="start" required>

                        <div class="input-group-append">
                          <div class="input-group-text">-</div>
                        </div>

                        <input type="text" class="form-control" name="end" required>

                      </div>

                    </div>

                  </div>

                </div>

              </div>

              <div class="form-row mb-2"><!-- activity -->
                <div class="col-form-label col-md-3">Activity</div>

                <div class="col">
                  <select name="event" class="form-control" required>
                    <option></option>
                    <?php foreach ($this->data->events as $e) {
                      printf(
                        '<option value="%s" data-multiday="%s">%s</option>',
                        $e->event,
                        $e->multi_day,
                        $e->event

                      );

                    } ?>

                  </select>

                </div>

              </div>

              <div class="form-row mb-2"><!-- person -->
                <div class="col-form-label col-md-3">Person</div>

                <div class="col">
                  <input type="text" name="people_name" class="form-control">

                </div>

              </div>

              <div class="form-row mb-2 d-none" envelope><!-- invite person -->
                <div class="offset-md-3 col">
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="people_invite_on_save" value="yes" id="<?= $uid = strings::rand() ?>">

                    <label class="form-check-label" for="<?= $uid ?>">send invite on save</label>

                  </div>

                </div>

              </div>

              <div class="form-row mb-2"><!-- address_street -->
                <div class="col-form-label col-md-3">Property</div>

                <div class="col">
                  <input type="text" name="address_street" class="form-control">

                </div>

              </div>

              <div class="form-row mb-2"><!-- location -->
                <div class="col-form-label col-md-3">Location</div>

                <div class="col">
                  <input type="text" name="location" class="form-control">

                </div>

              </div>

              <div class="form-row mb-2"><!-- notes -->
                <div class="col">
                  <textarea name="notes" class="form-control" rows="3" placeholder="notes ..."></textarea>

                </div>

              </div>

            </div>

            <div class="card"><!-- attendees -->
              <div id="<?= $_accordion ?>_people_heading">
                <h2 class="mb-0">
                  <button class="btn btn-light btn-block text-left collapsed" type="button"
                    id="<?= $_accordion ?>_people_button"
                    data-toggle="collapse"
                    data-target="#<?= $_accordion ?>_people"
                    aria-expanded="false"
                    aria-controls="<?= $_accordion ?>_people"></button>

                </h2>

              </div>

              <div id="<?= $_accordion ?>_people" class="collapse" aria-labelledby="<?= $_accordion ?>_people_heading" data-parent="#<?= $_accordion ?>">
                <div class="card-body">
                  <div class="form-row mb-2">
                    <div class="col">
                      <?php
                      $col2 = false;
                      $i = 0;

                      foreach ($this->data->users as $user) {
                        if ( !$col2 && $i++ >= count($this->data->users)/2) {
                          print '</div><div class="col">';
                          $col2 = true;

                        }

                        ?>
                        <div class="form-check">
                          <input type="checkbox" class="form-check-input" name="attendants[]"
                            <?php // if ( currentUser::id() == $user->id) print 'checked'; ?>
                            value="<?= $user->id ?>"
                            data-name="<?= $user->name ?>"
                            id="<?= $uid = strings::rand() ?>">

                          <label class="form-check-label" for="<?= $uid ?>">
                            <?= $user->name ?>

                          </label>

                        </div>

                      <?php
                      } ?>

                    </div>

                  </div>

                  <div class="form-row">
                    <div class="col text-right">
                      <button type="button" class="btn btn-outline-primary"
                        data-toggle="collapse" data-target="#<?= $_accordion ?>_appointment"
                        aria-expanded="true" aria-controls="<?= $_accordion ?>_appointment"
                      >done</button>

                    </div>

                  </div>

                </div>

              </div>

            </div>

            <div class="card"><!-- target_user -->
              <div id="<?= $_accordion ?>_target_user_heading">
                <h2 class="mb-0">
                  <button class="btn btn-light btn-block text-left collapsed" type="button"
                    id="<?= $_accordion ?>_target_user_button"
                    data-toggle="collapse"
                    data-target="#<?= $_accordion ?>_target_user"
                    aria-expanded="false"
                    aria-controls="<?= $_accordion ?>_target_user"></button>

                </h2>

              </div>

              <div id="<?= $_accordion ?>_target_user" class="collapse" aria-labelledby="<?= $_accordion ?>_target_user_heading" data-parent="#<?= $_accordion ?>">
                <div class="card-body">
                  <div class="alert alert-warning alert-dismissible d-none" role="alert">
                    Please Select target user ..
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>

                    </button>

                  </div>

                  <div class="form-row mb-2">
                    <div class="col">
                      <?php
                      $col2 = false;
                      $i = 0;

                      foreach ($this->data->users as $user) {
                        if ( !$col2 && $i++ >= count($this->data->users)/2) {
                          print '</div><div class="col">';
                          $col2 = true;

                        }

                        ?>
                        <div class="form-check">
                          <input type="radio" class="form-check-input" name="target_user"
                            <?php if ( currentUser::id() == $user->id) print 'checked'; ?>
                            value="<?= $user->id ?>"
                            data-name="<?= $user->name ?>"
                            id="<?= $uid = strings::rand() ?>">

                          <label class="form-check-label" for="<?= $uid ?>">
                            <?= $user->name ?>

                          </label>

                        </div>

                      <?php
                      } ?>

                    </div>

                  </div>

                  <div class="form-row mb-2 d-none" id="<?= $_uidNotifyUser = strings::rand() ?>">
                    <div class="col">
                      <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="notify_target_user" value="yes" id="<?= $uid = strings::rand() ?>">

                        <label class="form-check-label" for="<?= $uid ?>">
                          Notify User

                        </label>

                      </div>

                    </div>

                  </div>

                  <div class="form-row">
                    <div class="col text-right">
                      <button type="button" class="btn btn-outline-primary"
                        data-toggle="collapse" data-target="#<?= $_accordion ?>_appointment"
                        aria-expanded="true" aria-controls="<?= $_accordion ?>_appointment"
                      >done</button>

                    </div>

                  </div>

                </div>

              </div>

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
    $('input[name="start"]', '#<?= $_form ?>').on( 'change', function(e) {
      CheckTimeFormat.call( this);

      let s = $(this).val();
      if ( s == '' ) return;

      let j = timeHandler( s);
      // console.log( j.toString());

      j.Minutes += 30;

      $('input[name="end"]', '#<?= $_form ?>').val(j.toString());

    });

    // console.log( $('input[name="start"]', '#<?= $_form ?>'));

    $('input[name="end"]', '#<?= $_form ?>').on( 'change', CheckTimeFormat);

    $('select[name="event"]', '#<?= $_form ?>').on( 'change', function(e) {
      let _me = $(this);
      let option = $('option[value="' + _me.val() + '"]', this);
      if ( option.length > 0) {
        let _opt_data = option.data();

        // console.log( option);
        $('input[name="multiday"]', '#<?= $_form ?>').val( _opt_data.multiday);
        $('input[name="start"], input[name="end"]', '#<?= $_form ?>').prop( 'required', '1' != _opt_data.multiday);

        $('[data-set="date_end"]', '#<?= $_form ?>').each( (i, el) => {
          let _me = $(el);
          let _data = _me.data();

          if ( '1' == _opt_data.multiday) {
            'oneday' == _data.frame ? _me.addClass( 'd-none') : _me.removeClass( 'd-none');

          }
          else {
            'oneday' == _data.frame ? _me.removeClass( 'd-none') : _me.addClass( 'd-none');

          }

        });

      }

    });

    $('input[name="target_user"]', '#<?= $_form ?>').on( 'change', function( e) {
      if ( Number( $('input[name="id"]', '#<?= $_form ?>').val()) > 0) {
        $('#<?= $_uidNotifyUser ?>').addClass( 'd-none');
        $('input[name="notify_target_user"]', '#<?= $_form ?>').prop('checked', false);

      }
      else {
        let _me = $(this);
        if ( <?= (int)currentUser::id() ?> == _me.val()) {
          $('#<?= $_uidNotifyUser ?>').addClass( 'd-none');
          $('input[name="notify_target_user"]', '#<?= $_form ?>').prop('checked', false);

        }
        else {
          $('#<?= $_uidNotifyUser ?>').removeClass( 'd-none');

        }

      }

    });

    $('#<?= $_accordion ?>')
    .on( 'check-visibility', e => {
      if (!( $('#<?= $_accordion ?>_appointment').is(':visible') || $('#<?= $_accordion ?>_people').is(':visible') || $('#<?= $_accordion ?>_target_user').is(':visible') )) {
        $('#<?= $_accordion ?>_appointment').collapse( 'show');

      }

    });

    $('#<?= $_accordion ?>_people')
    .on( 'hide.bs.collapse', function(e) {
      $(this).trigger( 'reconcile');

    })
    .on( 'hidden.bs.collapse', function(e) {
      $('#<?= $_accordion ?>').trigger( 'check-visibility');

    })
    .on( 'reconcile', function(e) {
      let a = []
      $('input[name="attendants[]"]:checked', this).each( (i, sel) => {
        let _sel = $(sel);
        let _data = _sel.data();

        a.push( _data.name);

      });

      if ( a.length > 0) {
        $('#<?= $_accordion ?>_people_button').html( '<span class="text-monospace">att.</span>' + a.join(', '));

      }
      else {
        $('#<?= $_accordion ?>_people_button').html( 'select attendees');

      }

    });

    $('#<?= $_accordion ?>_target_user')
    .on( 'hide.bs.collapse', function(e) {
      $(this).trigger( 'reconcile');

    })
    .on( 'hidden.bs.collapse', function(e) {
      $('#<?= $_accordion ?>').trigger( 'check-visibility');

    })
    .on( 'reconcile', function(e) {
      let s = 'select target';
      let tu = $('input[name="target_user"]:checked', this);
      if (tu.length > 0) {
        let _data = tu.data();
        s = '<span class="text-monospace">tm..</span>' + _data.name;

      }

      $('#<?= $_accordion ?>_target_user_button').html( s);

    })
    .on( 'show-warning', function(e) {
      $('.alert', this).removeClass('d-none').addClass('fade show');

    });

    $('#<?= $_form ?>')
    .on( 'activate-invite', function(e) {
      let _form = $(this);
      let _data = _form.serializeFormJSON();

      if (Number(_data.id) < 1) { // only for new appointments
        if (Number(_data.people_id) > 0) {  // only for valid people
          if (String(_data.people_email).isEmail()) {  // only for people with valid email address
            $('input[name="people_invite_on_save"]', '#<?= $_form ?>').closest('[envelope]').removeClass('d-none');

          }
          else {
            $('input[name="people_invite_on_save"]', '#<?= $_form ?>').closest('[envelope]').addClass('d-none');
            console.log( 'not inviting - no email');

          }

        }
        else {
          $('input[name="people_invite_on_save"]', '#<?= $_form ?>').closest('[envelope]').addClass('d-none');
          console.log( 'not inviting - invalid person');

        }

      }
      else {
        $('input[name="people_invite_on_save"]', '#<?= $_form ?>').closest('[envelope]').addClass('d-none');
        console.log( 'not inviting - appointment is not new');

      }

    })
    .on( 'send-invite', function(e) {
      let _form = $(this);
      let _data = _form.serializeFormJSON();

      let _date = _.dayjs( _data.date + ' ' + _data.start);
      let format = 'dddd MMM D at ha';

      if ( _date.isValid() && _date.unix() > 0) {
        format = 'dddd MMM D';  // it's a valid date
        if ( _date.hour() > 0) {
          format = 'dddd MMM D [at] ha';
          if ( Number( _date.minute()) > 0) {
            format = 'dddd MMM D [at] h:m a';

          }

        }

      }

      let notes = [
        '<strong>Appointment Details</strong>',
        '',
        'Date/time : ' + _date.format(format)

      ];

      if ( '' != _data.location) {
        notes.push('Location : ' + _data.location);

      }

      if ( '' != _data.address_street) {
        notes.push('Property : ' + _data.address_street);

      }

      if ( '' != _data.notes) {
        notes.push('Notes :');
        notes.push(_data.notes);

      }

      let em = {
        to : _.email.rfc922({ name:_data.people_name, email:_data.people_email}),
        subject : 'Appointment - ' + _date.format( format),
        message : String(notes.join("<br>"))

      }

      if ( !!_.email && !!_.email.activate) {
        let emc = new EmailClass(em);
        _.email.activate( emc);
        if ( String( _data.people_mobile).IsMobilePhone()) {
          emc.ccSMSPush({ name:_data.people_name, mobile:_data.people_mobile});

        }

      }
      else {
        console.table( em);
        _.ask.warning({title:'alert',text:'no email program to run'})

      }

    })
    .on( 'submit', function( e) {
      let _form = $(this);
      let _data = _form.serializeFormJSON();

      let tu = $('input[name="target_user"]:checked', this);
      if (tu.length > 0) {
        _.post({
          url : _.url('<?= $this->route ?>'),
          data : _data,

        }).then( d => {
          if ( 'ack' == d.response) {
            $('#<?= $_modal ?>').trigger('success');
            if ($('input[name="people_invite_on_save"]', '#<?= $_form ?>').prop('checked')) {
              _form.trigger('send-invite'); // before closing modal

            }

          }
          else {
            _.growl( d);

          }

          $('#<?= $_modal ?>').modal('hide');

        });

      }
      else {
        $('#<?= $_accordion ?>_target_user')
        .trigger('show-warning')
        .collapse('show');

      }

      // console.table( _data);

      return false;
    });

    $('#<?= $_modal ?>')
    .on( 'shown.bs.modal', e => {
      $('input[name="date"]', '#<?= $_form ?>').focus();

    })

    $(document).ready( () => {

      (() => {
        return ('undefined' == typeof _.search || 'undefined' == typeof _.search.people || 'undefined' == typeof _.search.address) ?
          _.get.script( _.url("<?= $this->route ?>/js")) :
          Promise.resolve();

      })().then( () => {
        let loc = $('input[name="location"]', '#<?= $_form ?>');
        $('input[name="address_street"]', '#<?= $_form ?>')
        .autofill({
          autoFocus: true,
          source: _.search.address,
          select: ( e, ui) => {
            let o = ui.item;
            // console.table( o);

            $('input[name="property_id"]', '#<?= $_form ?>').val( o.id);
            loc.attr( 'placeholder', o.street);

          },

        })
        .on( 'keyup', function(e) { $(this).trigger('update-placeholder'); })
        .on( 'update-placeholder', function(e) {
          let _me = $(this);
          loc.attr( 'placeholder', _me.val());

        })
        .trigger('update-placeholder');

        $('input[name="people_name"]', '#<?= $_form ?>')
        .autofill({
          autoFocus: true,
          source: _.search.people,
          select: ( e, ui) => {
            let o = ui.item;

            // console.log(o);

            $('input[name="people_id"]', '#<?= $_form ?>').val( o.id);
            $('input[name="people_email"]', '#<?= $_form ?>').val( o.email);
            $('input[name="people_mobile"]', '#<?= $_form ?>').val( o.mobile);
            $('#<?= $_form ?>').trigger( 'activate-invite');

          },

        });

      });

      $('#<?= $_accordion ?>_people, #<?= $_accordion ?>_target_user').trigger( 'reconcile');

    });

  })( _brayworth_);
  </script>
</form>