var transactionalEmailOptionsHandler = function(evt){
	var sl = Event.element(evt);

	var row     = $('row_monkey_general_mandrill_apikey');
	var element = $('monkey_general_mandrill_apikey');

	if(sl.getValue().toString() === 'mandrill'){
		row.show();
		element.disabled = false;
	}else{
		row.hide();
		element.disabled = true;
	}
}

document.observe("dom:loaded", function() {

	if($('monkey_general_transactional_emails')){
		if($('monkey_general_transactional_emails').getValue().toString() != 'mandrill'){
			$('row_monkey_general_mandrill_apikey').hide();
			$('monkey_general_mandrill_apikey').disabled = true;
		}
		Element.observe('monkey_general_transactional_emails', 'change', transactionalEmailOptionsHandler);
	}

});
