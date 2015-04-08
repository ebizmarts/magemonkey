removeManageNewsletter = function () {
    var newsletter = $$('div.block-content ul li a[href*="newsletter/manage"]');
    if (newsletter.length) {
        newsletter.first().up().remove();
    }
}

document.observe("dom:loaded", function () {

    var monkeyEnabled = $$('div.block-content ul li a[href*="monkey/customer_account/index"]');

    if (monkeyEnabled.length) {
        removeManageNewsletter();

        //If in Dashboard, change "edit" link for "Newsletters"
        var editLink = $$('div.my-account a[href*="newsletter/manage"]');
        if (editLink.length) {
            editLink.first().writeAttribute('href', monkeyEnabled.first().readAttribute('href'));
        }
    }

});
