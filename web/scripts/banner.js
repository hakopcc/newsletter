function bannerLoadXMLDoc(url) {
    // branch for native XMLHttpRequest object
    if (window.XMLHttpRequest) {
        req = new XMLHttpRequest();
        req.onreadystatechange = bannerProcessReqChange;
        req.open("GET", url, true);
        req.send(null);
        // branch for IE/Windows ActiveX version
    } else if (window.ActiveXObject) {
        req = new ActiveXObject("Microsoft.XMLHTTP");
        if (req) {
            req.onreadystatechange = bannerProcessReqChange;
            req.open("GET", url, true);
            req.send();
        }
    }
}

function bannerProcessReqChange() {
    // only if req shows "complete"
    if (req.readyState == 4) {
        // only if "OK"
        if (req.status == 200) {
            // ...processing statements go here
            response = req.responseXML.documentElement;
            if (response) {
                var result = [];
                for (i = 0; i < 50; i++) {
                    result[i] = response.getElementsByTagName('block')[0].firstChild.data * i
                }
                bannerLoadResult('', result);
            }
        } else {
            alert("There was a problem retrieving the XML data:\n" + req.statusText);
        }
    }
}

function bannerLoadResult(url, result) {
    if (result != '') {
        // Response mode
        for (i = 0; i < result.length; i++) {
            this.obj.options[this.obj.options.length] = new Option(result[i], result[i]);
        }
    } else if (url != '') {
        // Input mode
        return (bannerLoadXMLDoc(url));
    }
}

function bannerCheckType(type) {
    if (type < 50) {
        bannerDisableTextForm();

        //reset language fields for image banners
        $('#caption').css('display', '');
        $('#file').css('display', '');
    } else if (type >= 50) {
        bannerDisableImagesForm();

        //reset text fields for sponsored links
        $('#caption').css('display', '');
        $('#description1').css('display', '');
        $('#description2').css('display', '');
    }

    $("#imageSizeLabel").html(BannerLevels.getLabelForLevel(type));
}

function bannerDisableImagesForm() {
    $("#tour-title").css("display", "none");
    $("#banner_with_images").css("display", "none");
    $("#banner_with_text").css("display", "");
    $('#script_banner').css("display", "none");
    $('#display_url').css("display", "");
}

function bannerDisableTextForm() {
    $("#tour-title").css("display", "");
    $("#banner_with_images").css("display", "");
    $("#banner_with_text").css("display", "none");
    $('#script_banner').css("display", "");
    $('#display_url').css("display", "none");
}

function fillBannerCategorySelect(host, obj, value, form, domain_id, from) {
    this.obj = obj;

    while (obj.options.length >= 1) {
        deleteIndex = obj.options.length - 1;
        obj.options[deleteIndex] = null;
    }

    url = host + '/includes/code/fill_banner_category.php?section=' + value + '&domain_id=' + domain_id + '&from=' + from;

    if (value.length) {
        loadBannerResult(url, '');
        if (value == "general" || value == "global") {
            obj.disabled = true;
        } else {
            obj.disabled = false;
        }
    }
}

function loadBannerXMLDoc(url) {
    // branch for native XMLHttpRequest object
    if (window.XMLHttpRequest) {
        req = new XMLHttpRequest();
        req.onreadystatechange = processBannerReqChange;
        req.open("GET", url, true);
        req.send(null);
        // branch for IE/Windows ActiveX version
    } else if (window.ActiveXObject) {
        req = new ActiveXObject("Microsoft.XMLHTTP");
        if (req) {
            req.onreadystatechange = processBannerReqChange;
            req.open("GET", url, true);
            req.send();
        }
    }
}

function processBannerReqChange() {
    // only if req shows "complete"
    if (req.readyState == 4) {
        // only if "OK"
        if (req.status == 200) {
            // ...processing statements go here
            response = req.responseXML.documentElement;
            if (response) {
                var result = [];
                for (i = 0; i < response.getElementsByTagName('id').length; i++) {
                    result[i] = {
                        'id':   response.getElementsByTagName('id')[i].firstChild.data,
                        'name': response.getElementsByTagName('name')[i].firstChild.data
                    };
                }
                loadBannerResult('', result);
            }
        } else {
            alert("There was a problem retrieving the XML data:\n" + req.statusText);
        }
    }
}

function loadBannerResult(url, result) {
    if (result != '') {
        // Response mode
        for (i = 0; i < result.length; i++) {
            this.obj.options[this.obj.options.length] = new Option(result[i].name, result[i].id);
        }
    } else if (url != '') {
        // Input mode
        return (loadBannerXMLDoc(url));
    }
}
