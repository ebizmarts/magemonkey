function getCampaign() {
    var urlparams = location.search.substr(1).split('&');
    var params = new Array();
    for (var i = 0; i < urlparams.length; i++) {
        var param = urlparams[i].split('=');
        var key = param[0];
        var val = param[1];
        if (key && val) {
            params[key] = val;
        }
    }

    if (params['mc_cid']) {
        createCookie('magemonkey_campaign_id', params['mc_cid'], 3600);
    }
    if (params['mc_eid']) {
        createCookie('magemonkey_email_id', params['mc_eid'], 3600);
    }
}

function createCookie(name, value, expirationInSec) {
    var now = new Date();
    var expire = new Date(now.getTime() + (expirationInSec * 1000));//[(1 * 365 * 24 * 60) * 60000] == 1 year  -- (Years * Days * Hours * Minutes) * 60000
    Mage.Cookies.expires = expire;
    Mage.Cookies.set(name,value);
}

if (document.loaded) {
    getCampaign;
} else {
    document.observe('dom:loaded', getCampaign);
}
