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

    $(document).ready( () => {
      $('#<?= $_form ?>')
      .on( 'submit', function( e) {
        let _form = $(this);
        let _data = _form.serializeFormJSON();
        let _modalBody = $('.modal-body', _form);

        // console.table( _data);

        return false;
      });
    });

  })( _brayworth_);
  </script>
</form>