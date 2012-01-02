removeManageNewsletter = function(){
	var newsletter = $$('div.block-content ul li a[href*="newsletter/manage"]');
	if(newsletter.length){
		newsletter.first().up().remove();
	}
}

document.observe("dom:loaded", function() {

	var monkeyEnabled = $$('div.block-content ul li a[href*="monkey/customer_account/index"]').length;

	if(monkeyEnabled){
		removeManageNewsletter();
	}

});