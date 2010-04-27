var checkbox1, checkbox2;

function forceOrder(el){
	if ( checkbox1 === undefined ){
		checkbox1 = document.getElementById('orderimportant1');
		checkbox2 = document.getElementById('orderimportant2');
	}

	checkbox1.checked = !el.checked;
	checkbox1.disabled = el.checked;
	checkbox2.checked = !el.checked;
	checkbox2.disabled = el.checked;
}
