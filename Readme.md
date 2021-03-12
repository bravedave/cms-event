## CMS Event

> _A Diary Event Feed for bravedave/CMS_

#### An Event is
* Occurs - an event occurs at a time, it may have both a start and end time, but most events have a start time and last an assumed amount of time

#### An Appointment is
* An Event which anticipates
  * Activity
  * People/Property - involves either a person or a property or both, but at least one
  * Location - by default will occur at the property, but may occur somewhere else
  * Attendees - optionally other users can be nominated to attend
    * optionally attendees can receive an invitation to the event
  * Team Member - by default, the team member is the user who enters the event

#### Install
   ```
   composer config repositories.cms-event git https://github.com/bravedave/cms-event
   composer require bravedave/cms-event
   ```
