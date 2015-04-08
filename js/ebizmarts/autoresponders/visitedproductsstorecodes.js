/*!
 * Ebizmarts_MageMonkey
 * @copyright   Copyright Ebizmarts
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
(function () {
    function getUrl() {
        var path = window.location.toString();
        var myUrl = path.split('/')
        var max = 4;
        var pos = myUrl.indexOf('index.php');
        if (pos != -1) {
            max = 5;
        }
        return myUrl.slice(0, max).join('/') + '/ebizautoresponder/autoresponder/';
    }

    function markVisited(productID) {
        new Ajax.Request(getUrl() + 'markVisitedProducts?product_id=' + productID, {
            method: 'get',
            onSuccess: function (transport) {
            }
        });
    }

    var cb = function () {
        var $product = $$('#product_addtocart_form input[name^=product]').first(),
            productID = '';
        if ($product) {
            productID = $product.value;
            new Ajax.Request(getUrl() + 'getVisitedProductsConfig?product_id=' + productID, {
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