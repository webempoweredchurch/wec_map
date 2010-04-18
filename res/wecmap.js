//
// Copyright notice
//
// (c) 2005-2009 Christian Technology Ministries International Inc.
// All rights reserved
//
// This file is part of the Web-Empowered Church (WEC)
// (http://WebEmpoweredChurch.org) ministry of Christian Technology Ministries 
// International (http://CTMIinc.org). The WEC is developing TYPO3-based
// (http://typo3.org) free software for churches around the world. Our desire
// is to use the Internet to help offer new life through Jesus Christ. Please
// see http://WebEmpoweredChurch.org/Jesus.
//
// You can redistribute this file and/or modify it under the terms of the
// GNU General Public License as published by the Free Software Foundation;
// either version 2 of the License, or (at your option) any later version.
//
// The GNU General Public License can be found at
// http://www.gnu.org/copyleft/gpl.html.
//
// This file is distributed in the hope that it will be useful for ministry,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// This copyright notice MUST APPEAR in all copies of the file!
//

// Global object that holds all data about the markers, maps, icons etc
// and can be used multiple times on the page
var WecMap = {
	maps: [],
	markers: [],
	markerManagers: [],
	bubbleData: [],
	directions: [],
	directionsFromData: [],
	directionsToData: [],
	icons: [],
	locale: 'en',
	
	// must be called when the map is initialized
	init: function(map) {
		if (!(this.icons[map] instanceof Array)) { this.icons[map] = []; }
		if (!(this.directionsFromData[map] instanceof Array)) { this.directionsFromData[map] = []; }
		if (!(this.directionsToData[map] instanceof Array)) { this.directionsToData[map] = []; }
		if (!(this.icons[map] instanceof Array)) { this.icons[map] = []; }
		if (!(this.bubbleData[map] instanceof Array)) { this.bubbleData[map] = []; }
		if (!(this.markers[map] instanceof Array)) { this.markers[map] = []; }
		var height = document.getElementById(map).style.height;
		var width = document.getElementById(map).style.width;
		var mapsize = new GSize(parseInt(width, 10), parseInt(height, 10));
		
		this.maps[map] = new GMap2(document.getElementById(map), {size: mapsize});
		this.markerManagers[map] = new GMarkerManager(this.maps[map]);
	},

	// fetches the GMap2 object
	get: function(map) {
		return this.maps[map];
	},

	// adds markers (all markers from a specified group of a map) to the marker Manager
	addMarkersToManager: function(map, groupId, minZoom, maxZoom) {
		this.markerManagers[map].addMarkers(this.markers[map][groupId], minZoom, maxZoom);
		this.markerManagers[map].refresh();
	},

	// jumps to a specific marker (determined by groupId and markerId) and zoomlevel on the map
	jumpTo: function(map, groupId, markerId, zoom) {
		var marker = this.markers[map][groupId][markerId];
		if (zoom) {	
			this.maps[map].setZoom(zoom);
		}
		this.maps[map].panTo(marker.getPoint());
		setTimeout('GEvent.trigger(WecMap.markers["' + map + '"]["' + groupId + '"]["' + markerId + '"], "click")', 300);
		return false;
	},

	// loads directions on a map
	setDirections: function(map, fromAddr, toAddr) {
		window['gdir_' + map].load('from: ' + fromAddr + ' to: ' + toAddr, { locale: this.locale });
		this.maps[map].closeInfoWindow();
		return false;
	},

	// adds an icon that might be used on a marker object later on
	addIcon: function(map, iconId, image, shadow, iconSize, shadowSize, iconAnchor, infoWindowAnchor) {
		var icon = new GIcon();
		icon.image = image;
		icon.shadow = shadow;
		icon.iconSize = iconSize;
		icon.shadowSize = shadowSize;
		icon.iconAnchor = iconAnchor;
		icon.infoWindowAnchor = infoWindowAnchor;
		if (!iconId) {
			var iconId = 'default';
		}
		this.icons[map][iconId] = icon;
	},

	// adds the content (as an array for each tab) and the labels of the tabs, that will be used with the add Marker call
	addBubble: function(map, groupId, markerId, labels, content) {
		if (!(this.bubbleData[map][groupId] instanceof Array)) { this.bubbleData[map][groupId] = []; }
		for (var i = 0; i < content.length; i++) {
			content[i] = '<div id="' + map + '_marker_' + groupId + '_' + markerId + '" class="marker">' + content[i] + '</div>';
		}
		this.bubbleData[map][groupId][markerId] = {
			labels: labels,
			content: content
		};
	},

	// adds a GMarker object with all the data for the bubble and the precise location. The marker is added via
	// the markermanager (see addMarkersToManagers), here it basically just created and added to the array 
	addMarker: function(map, markerId, latlng, iconId, dirTitle, groupId, address) {
		if (!iconId) {
			var iconId = 'default';
		}
		var icon = this.icons[map][iconId];
		var point = new GLatLng(latlng[0], latlng[1]);
		var marker = new GMarker(point, icon);
		var tabs = [];
		var tabLabels = this.bubbleData[map][groupId][markerId].labels;
		var text = this.bubbleData[map][groupId][markerId].content;
		if (text) {
			for (var i = 0; i < text.length; i++) {
				tabs.push(new GInfoWindowTab(tabLabels[i], text[i]));
			}
			if (dirTitle) {
				if (!(this.directionsFromData[map][groupId] instanceof Array)) {
					this.directionsFromData[map][groupId] = [];
				}
				if (!(this.directionsToData[map][groupId] instanceof Array)) {
					this.directionsToData[map][groupId] = [];
				}

				var dirText = text[0] + '<br /><div id="' + map + '_todirform_' + groupId + '_' + markerId + '" class="todirform"><form action="#" onsubmit="return WecMap.setDirections(\'' + map + '\', [' + point.y + ', ' + point.x + '], document.getElementById(\'tx-wecmap-directions-to-' + map + '\').value, \'' + dirTitle + '\');">';
				dirText += '<label class="startendaddress" for="tx-wecmap-directions-to-' + map + '">' + this.labels.endaddress + '</label><input type="text" name="daddr" value="' + address + '" id="tx-wecmap-directions-to-' + map + '" />';
				dirText += '<input type="submit" name="submit" value="Go" /></form></div>';
				this.directionsFromData[map][groupId][markerId] = dirText;

				dirText = text[0] + '<br /><div id="' + map + '_fromdirform_' + groupId + '_' + markerId + '" class="fromdirform"><form action="#" onsubmit="return WecMap.setDirections(\'' + map + '\', document.getElementById(\'tx-wecmap-directions-from-' + map + '\').value, [' + point.y + ', ' + point.x + '], \'' + dirTitle + '\');">';
				dirText += '<label class="startendaddress" for="tx-wecmap-directions-from-' + map + '">' + this.labels.startaddress + '</label><input type="text" name="saddr" value="' + address + '" id="tx-wecmap-directions-from-' + map + '" />';
				dirText += '<input type="submit" name="submit" value="Go" /></form></div>';
				this.directionsToData[map][groupId][markerId] = dirText;
			}
			marker.bindInfoWindowTabsHtml(tabs);
		}
		if (!(this.markers[map][groupId] instanceof Array)) { this.markers[map][groupId] = []; }
		this.markers[map][groupId][markerId] = marker;
		return marker;
	},

	// opens up the directions tab window to a marker
	openDirectionsToHere: function(map, groupId, markerId) {
		var tabs = [];
		var tabLabels = this.bubbleData[map][groupId][markerId].labels;
		var text = this.bubbleData[map][groupId][markerId].content;
		var dirs = this.directionsToData[map][groupId][markerId];
		for (var i = 0; i < text.length; i++) {
			if (i == 0) {
				tabs.push(new GInfoWindowTab(tabLabels[i], dirs));
			} else {
				tabs.push(new GInfoWindowTab(tabLabels[i], text[i]));
			}
		};
		this.markers[map][groupId][markerId].openInfoWindowTabsHtml(tabs);
		return false;
	},
	
	// opens up the directions tab window from a marker
	openDirectionsFromHere: function(map, groupId, markerId) {
		var tabs = [];
		var tabLabels = this.bubbleData[map][groupId][markerId].labels;
		var text = this.bubbleData[map][groupId][markerId].content;
		var dirs = this.directionsFromData[map][groupId][markerId];
		for (var i = 0; i < text.length; i++) {
			if (i == 0) {
				tabs.push(new GInfoWindowTab(tabLabels[i], dirs));
			} else {
				tabs.push(new GInfoWindowTab(tabLabels[i], text[i]));
			}
		};
		this.markers[map][groupId][markerId].openInfoWindowTabsHtml(tabs);
		return false;
	}
}