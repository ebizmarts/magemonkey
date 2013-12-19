/*!
 * Ebizmarts_MageMonkey
 * @copyright   Copyright Ebizmarts
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
(function () {
    function markVisited(productID) {
        new Ajax.Request('../index.php/ebizautoresponder/autoresponder/markVisitedProducts?product_id=' + productID, {
            method: 'get',
            onSuccess: function (transport) {
            }
        });
    }

    var cb = function () {
        var $product = $$('input[name^=product]').first(),
            productID = '';
        if ($product) {
            productID = $product.value;
            new Ajax.Request('/ebizautoresponder/autoresponder/getVisitedProductsConfig?product_id=' + productID, {
                method: 'get',
                onSuccess: function (transport) {
                    if (transport.responseJSON.time > -1) {
                        markVisited.delay(transport.responseJSON.time, productID);
                    }
                }
            });
        }
    }
    if (document.loaded) {
        cb();
    } else {
        document.observe('dom:loaded', cb);
    }
    // window.markVisited = markVisited;
})();