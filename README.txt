= USE =

 *  Get a free Google Maps API Key from Google (http://www.google.com/apis/maps/).  
 *  Enter the key globally right after installing the extension. 
 *  Add the frontend plugin Simple Map or Frontend User Map to a page and set options in the flexform or per TypoScript.

= FE Plugins =

== Simple Map ==
 *  Shows just one user on the map. Configurable by TS and Flexform
 *  Example TS:
plugin.tx_wecmap_pi1 {
	apiKey = 
	height = 500
	width = 500
	controls.mapControlSize = (large|small|zoomonly)
	controls.showOverviewMap = 1
	controls.showMapType = 1
	controls.showScale = 1
	title = TS title
	description = TS desc
	street = 1234 Happy Place
	city = Happy City
	zip = 12345
	state = HS
	country = Happy Country
}

== FE User Map ==
 * Shows all FE Users or only members of certain groups on the map
 * Layered markers, i.e. the lowest zoom only shows a few markers in countries with users, higher zoom levels show more and more markers until all users are finally shown. Improves speed over showing them all at once.
 * Example TS
plugin.tx_wecmap_pi2 {
	apiKey = 
	height = 500
	width = 500
	controls.mapControlSize = (large|small|zoomonly)
	controls.showOverviewMap = 1
	controls.showMapType = 1
	controls.showScale = 1
	userGroups = 2,3,5
}

Order of precedence for configuration: Flexform first, then TS, then global settings (API Key)

= BE Modules =

== Geocode Cache ==
 * Allows to delete and edit the tx_wecmap_cache table directly

== Map FE Users ==
 * Proof of concept for showing all FE Users in the Backend
 * Provides link to directly edit a user's record
 * Needs the API key to be specified globally from the Extension Manager

== Map in BE records ==
 * Shows a map for every BE user record
 * Needs the API key to be specified globally from the Extension Manager

= Design =

 *  Frontend plugin requests a map and passes all relevant addresses to the main map class.  There are two current frontend plugins and one BE module to demonstrate functionality (see above).
 *  The map class is responsible for actually rendering the HTML and Javascript for the map, but does not perform address lookups.  Instead, it passes addresses to the caching/lookup class
 *  The caching/lookup class creates a hash of the current address, and checks the database to see if that address has already been looked up.  If so, it returns lat/long.  If not, it begins the lookup service.
 *  The lookup service starts with the highest quality service (probably the fastest/cheapest) and continues through all available services until a match has been found.  When it finds a match, the lat/long are returned for storage in the cache. Geocode services implemented are Google, Yahoo, Geocoder, and Worldkit, in that order.