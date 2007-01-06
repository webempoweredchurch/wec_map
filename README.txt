Use
-Get a Google Maps API Key from Google (free).  The biggest current limitation of the extension is that API Keys only work for single directory on a site.  Hopefully Google will open this up in the future.  In the meantime, this means using RealURL is painful because a different key is required for every page that has a map.
-In your Typoscript page template, add the the key to your head section...
page.headerData.10 = TEXT
page.headerData.10.value = <script src="http://maps.google.com/maps?file=api&v=1&key=abcd" type="text/javascript"></script>
-Add the frontend plugin Simple Map or Frontend User Map to a page.

Design
-4 layer approach to a map.
1) Frontend plugin requests a map and passes all relevant addresses to the main map class.  There are two current frontend plugins to demonstrate functionality.
1a) Simple Map displays a Flexform for address entry.  This address is drawn on the map.
1b) Frontend User Map tries to draw a map containing all frontend users of your website.
2) The map class is responsible for actually rendering the HTML and Javascript for the map, but does not perform address lookups.  Instead, it passes addresses to the caching/lookup class
3) The caching/lookup class creates a hash of the current address, and checks the database to see if that address has already been looked up.  If so, it returns lat/long.  If not, it begins the lookup service.
4) The lookup service starts with the highest quality service (probably the fastest/cheapest) and continues through all available services until a match has been found.  When it finds a match, the lat/long are returned for storage in the cache.
4a).  The only service currently implemented is the free Geocoder.us service.  It performs a lookup using a RESTful interface.

Known issues / To Do List
-No support for non-standard marker icons.  Planned for future development.
-Currently supports Google Maps only.  Future plans for mapping API that supports Yahoo, Google, etc.
-Map centers at last marker it was given and has a static zoom level.  Need to add support for intelligent centering and zooming.
-Expose more of the Google API (controls, etc) to the top level plugins.
-Find a workaround for the single directory key limitation.
