(doc['geoLocation'].empty || (doc['geoLocation'].lat == 0 && doc['geoLocation'].lon == 0) ) ? ( _source + [ distance: 0 ] ) : ( _source + [ distance: doc['geoLocation'].distanceInKm(lat.toFloat(),lon.toFloat()) ] )
