var marketLocationId = 0;
var marketLocationLevel = 0;

function setLocations(obj, table) {
    if (obj > 0) {
        marketLocationId = obj;
        marketLocationLevel = table;
    }

    if (marketLocationId > 0) {
        $.post(Routing.generate('marketselection_setmarket'), {
            location_id: marketLocationId,
            location_level: marketLocationLevel,
            force_location: true
        }, function (response, status) {
            console.log(response.updated);
            if (response.updated == "y") {
                window.location.reload();
            }
        });
    }
}

function removeLocations() {
    $.post(Routing.generate('marketselection_setmarket'), {
        location_id: 0,
        location_level: 0,
        force_location: true
    }, function (response, status) {
        if (response.updated == "y") {
            window.location.reload();
        }
    });
}