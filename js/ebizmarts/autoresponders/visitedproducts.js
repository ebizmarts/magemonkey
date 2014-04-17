function markVisited(productID) {
    new Ajax.Request('/ebizautoresponder/autoresponder/markVisitedProducts?product_id='+productID, { method:'get', onSuccess: function(transport){
    }
    });
}
(function() {
    var cb = function() {
        var productID = $$('input[name^=product]')[0].value;
        new Ajax.Request('../ebizautoresponder/autoresponder/getVisitedProductsConfig?product_id='+productID, { method:'get', onSuccess: function(transport){
                if(transport.responseJSON.time > -1) {
                    markVisited.delay(transport.responseJSON.time,productID);
                }
            }
        });
    };
    if (document.loaded) {
        cb();
    } else {
        document.observe('dom:loaded', cb);
    }
})();