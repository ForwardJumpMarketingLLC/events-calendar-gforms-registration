=== Events Calendar GForms Registration ===
Contributors: tajensen
Tags: the events calendar, events calendar, gravity forms, event registration, event register
Requires at least: 4.7
Tested up to: 4.8.1
Stable tag: 0.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Use Gravity Forms to handle registration for The Events Calendar events.

== Description ==

This plugin allows you to use Gravity Forms to handle registration for The Events Calendar events. Forms can be reused for multiple events without conflict.

== Requirements ==

* PHP 7.0+
* [Gravity Forms](http://www.gravityforms.com/)
* [The Events Calendar](https://wordpress.org/plugins/the-events-calendar/)

== Installation ==

Install this plugin using the WordPress plugin installer. Ensure that Gravity Forms and The Events Calendar plugins are active before activating this plugin.

== Setup ==

1. First create a new Gravity Form. Make sure your form has one field that you can use to track the number of registrants. For example, this could be a "name" field if you only need to register one person per form submission.

1. Next, create a new event. You will see a new meta box on the event edit screen that allows you to select an event registration form. Select the form and the form field that will be used to track the number of people who have registered. For complex registration requirements, you may need to add additional form fields. For example, if your event can accommodate 20 adults and 10 children, then you should select a form field to track each of those registrant types. Enter a number to limit the number of registrations that the form can accept.

1. Lastly, configure the Form Options. You can enter a notice that will be displayed within the form. This can be helpful if you want to notify users about how many reservations remain. Form notices will look like this:
![Event form notice screenshot](assets/images/front-end-form-notice.png "Event form notice screenshot")

_Optional_: You can include basic event information in notification emails by adding the merge tag `{event_info}` to the Gravity Forms notification template.

== Customization ==

Refer to the [Readme on GitHub](https://github.com/ForwardJumpMarketingLLC/events-calendar-gforms-registration/blob/master/README.md#customization).

== Screenshots ==

1. Screenshot of configuring a registration form on the event edit screen.
2. Screenshot of the form notice that informs users of the available registration slots.
3. Screenshot of the position of the registration form.

