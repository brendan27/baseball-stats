Baseball Stats
==============

Baseball/Softball team manager, schedule manager and statistic tracker. Wordpress plugin

I created this plugin for a local client (http://lethmixedslowpitch.com) and at this moment it is designed specifically for them. My goal for this project is to make the plugin customizable for any baseball/softball league to be able to use and customize to their needs. This is also my first real use of Github so I will be learning as I go! Any help will be greatly appreciated as I hope to give back to the Wordpress community and hopefully collaborate with some great developers through this project.

TODO (lots):

* Remove instances of "LMSA" and make this customizable or just generic.
* Add ability to specify number of divisions (this is hardcoded in right now)
* Should add some kind of caching so we don't perform so many calculations for print_standings(). We could regenerate the cache each time a game is updated.
* Update the location of the update_pts() function call. (should not be in print_standings() and should instead be called when a game is updated)

* More...

To Use
======

The following shortcodes are used to display content:

* [print_teams division="A"] - attribute: division is required (http://lethmixedslowpitch.com/teams/)
* [print_scores division="A"] - attribute: division is required (http://lethmixedslowpitch.com/games/)
* [print_standings division="A"] - attribute: division is required (http://lethmixedslowpitch.com/standings/)
* [division_leaders] - no required attributes. This shortcode displays a table of leaders for each division. (http://lethmixedslowpitch.com - sidebar)
* [submit_score email_to="email@website.com"] - attribute: email_to defaults to the WP site admin. (http://lethmixedslowpitch.com/games/submit-score/)
