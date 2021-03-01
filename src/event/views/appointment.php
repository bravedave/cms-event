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
use theme;  ?>

<form id="<?= $_form = strings::rand() ?>" autocomplete="off">
  <input type="hidden" name="person_id">
  <input type="hidden" name="properties_id">
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
          <div class="accordion" id="<?= $_accordion = strings::rand() ?>">
            <div id="<?= $_accordion ?>_appointment" class="collapse show" aria-labelledby="<?= $_accordion ?>_appointment_heading" data-parent="#<?= $_accordion ?>">
              <div class="form-row mb-2"><!-- date, start time, end time -->
                <div class="col mb-2 mb-md-0">
                  <input type="date" class="form-control" name="date" required>

                </div>

                <div class="col-md">
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
                        '<option>%s</option>',
                        $e->event

                      );

                    } ?>

                  </select>

                </div>

              </div>

              <div class="form-row mb-2"><!-- person -->
                <div class="col-form-label col-md-3">Person</div>

                <div class="col">
                  <input type="text" name="person_name" class="form-control">

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

            <div class="card">
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
                  <?php foreach ($this->data->users as $user) { ?>
                    <div class="form-row mb-2">
                      <div class="col">
                        <div class="form-check">
                          <input type="checkbox" class="form-check-input" name="user[]"
                            value="<?= $user->id ?>"
                            data-name="<?= $user->name ?>"
                            id="<?= $uid = strings::rand() ?>">

                          <label class="form-check-label" for="<?= $uid ?>">
                            <?= $user->name ?>

                          </label>

                        </div>

                      </div>

                    </div>

                  <?php } ?>

                  <div class="row">
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
      j.Minutes += 30;

      $('input[name="end"]', '#<?= $_form ?>').val(j.toString());

    });

    $('input[name="end"]', '#<?= $_form ?>').on( 'change', CheckTimeFormat);

    $('#<?= $_accordion ?>_people')
    .on( 'hide.bs.collapse', function(e) {
      $(this).trigger( 'reconcile');

    })
    .on( 'reconcile', function(e) {
      let a = []
      $('input[name="user[]"]:checked', this).each( (i, sel) => {
        let _sel = $(sel);
        let _data = _sel.data();

        a.push( _data.name);

      });

      if ( a.length > 0) {
        $('#<?= $_accordion ?>_people_button').html( a.join(', '));

      }
      else {
        $('#<?= $_accordion ?>_people_button').html( 'select users');

      }

    });

    $('#<?= $_form ?>')
    .on( 'submit', function( e) {
      let _form = $(this);
      let _data = _form.serializeFormJSON();
      let _modalBody = $('.modal-body', _form);

      // console.table( _data);

      return false;
    });

    $(document).ready( () => {

      (() => {
        return ('undefined' == typeof _.search || 'undefined' == typeof _.search.people || 'undefined' == typeof _.search.address) ?
          _.get.script( _.url("<?= $this->route ?>/js")) :
          Promise.resolve();

      })().then( () => {
        $('input[name="address_street"]', '#<?= $_form ?>').autofill({
          autoFocus: false,
          source: _.search.address,
          select: ( e, ui) => {
            let o = ui.item;
            console.table( o);

            $('input[name="properties_id"]', '#<?= $_form ?>').val( o.id);

            let loc = $('input[name="location"]', '#<?= $_form ?>');
            if ( '' == loc.val()) loc.val( o.street);

          },

        });

        $('input[name="person_name"]', '#<?= $_form ?>').autofill({
          autoFocus: false,
          source: _.search.people,
          select: ( e, ui) => {
            let o = ui.item;
            $('input[name="person_id"]', '#<?= $_form ?>').val( o.id);

          },

        });

      });

      $('#<?= $_accordion ?>_people').trigger( 'reconcile');

    });

  })( _brayworth_);
  </script>
</form>