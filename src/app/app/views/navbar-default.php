<?php
/**
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
 */	?>

<style>
@media screen and (max-width: 767px) {
  .navbar-brand {max-width: 70%;}
}
@media screen and (min-width: 768px) {
  .navbar-brand {min-width: 30%;}
}
</style>
<nav class="navbar navbar-expand-md navbar-dark bg-primary sticky-top" role="navigation" >
	<div class="container-fluid">
    <div class="navbar-brand text-truncate"><?= $this->data->title	?></div>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#<?= $_uid = strings::rand() ?>"
      aria-controls="<?= $_uid ?>" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>

    </button>

    <div class="collapse navbar-collapse" id="<?= $_uid ?>">
      <ul class="navbar-nav ml-auto">
        <!-- start : the appointment can be implemented -->
        <li class="nav-item"><a class="nav-link" title="appointment" href="#" id="<?= $_uid = strings::rand() ?>">New Event</a></li>
        <script>( _ => $('#<?= $_uid ?>').on( 'click', e => _.get.modal( _.url( 'event/appointment')))) (_brayworth_);</script>
        <!-- end : the appointment can be implemented -->

        <li class="nav-item dropdown">
          <a class="nav-link pb-0 dropdown-toggle" href="#" id="navbarDropdown" role="button" aria-label="dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <?= dvc\icon::get( dvc\icon::gear ) ?>

          </a>

          <div class="dropdown-menu" aria-labelledby="navbarDropdown">

            <a class="dropdown-item" href="<?= strings::url('event') ?>">Diary Events</a>

            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="<?= strings::url('people') ?>">People</a>
            <a class="dropdown-item" href="<?= strings::url('properties') ?>">Properties</a>
            <a class="dropdown-item" href="<?= strings::url('property_diary') ?>">Property Diary</a>
            <a class="dropdown-item" href="<?= strings::url('users') ?>">Users</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="<?= strings::url('beds') ?>">Beds</a>
            <a class="dropdown-item" href="<?= strings::url('baths') ?>">Baths</a>
            <a class="dropdown-item" href="<?= strings::url('property_type') ?>">Property Type</a>
            <a class="dropdown-item" href="<?= strings::url('postcodes') ?>">Postcodes</a>

          </div>

        </li>

        <li class="nav-item">
          <a class="nav-link" href="<?= strings::url() ?>">
            <?= dvc\icon::get( dvc\icon::house ) ?>
            <span class="sr-only">home</span>

          </a>

        </li>

        <li class="nav-item">
          <a class="nav-link" href="https://github.com/bravedave/">
            <?= dvc\icon::get( dvc\icon::github ) ?>
            <span class="sr-only">github</span>

          </a>

        </li>

      </ul>

    </div>

  </div>

</nav>
