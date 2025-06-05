WP Movies – WordPress Theme with Custom Post Types + ACF + TMDB API
Welcome to WP Movies, a WordPress theme that demonstrates the use of custom post types (CPTs), Advanced Custom Fields (ACF), and integration with the external TMDB (The Movie Database) API.

This project is preconfigured to run in a local DDEV environment and includes a sample database to make testing easy.

Repository: https://github.com/Zagaz/wp-movies.git

REQUIREMENTS

Docker
DDEV
Git
QUICK START

1. Clone the repository:

git clone https://github.com/Zagaz/wp-movies.git cd wp-movies

2. Start the DDEV environment:

ddev start

3. Import the database:

ddev import-db --src=wp-content/themes/wp-movies/wp-movies.sql

4. Install ACF plugin:

5. Access the local site:

Frontend: http://wp-movies.ddev.site Admin: http://wp-movies.ddev.site/wp-admin

Login: Username: admin Password: admin

6. Activate the theme:

Go to Appearance > Themes and activate "WP Movies"

WHAT’S INCLUDED

Custom Post Types:

Install ACF plugin

Movie (movie)
Actor (actor)
ACF Field Groups:

Movies
Actors
The fields are already included in the .sql database file and can be edited via the ACF plugin in the dashboard.

TMDB API INTEGRATION

This theme uses the TMDB API to pull movie and actor data dynamically.

DIRECTORY STRUCTURE

wp-content/ themes/ wp-movies/ functions.php - CPT registration and setup wp-movies.sql - Pre-configured database page-movies.php - Page template using the TMDB API template-parts/ - Optional reusable components

DDEV COMMANDS

Start environment: ddev start

Stop environment: ddev stop

Import DB: ddev import-db --src=wp-content/themes/wp-movies/wp-movies.sql

Export DB: ddev export-db --file=yourfilename.sql

Launch browser: ddev launch

Launch phpMyAdmin: ddev launch -p phpmyadmin