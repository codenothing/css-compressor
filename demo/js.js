var organize;

function forceOrder( el ) {
	if ( organize === undefined ){
		organize = document.getElementById('organize');
	}

	organize.checked = !el.checked;
	organize.disabled = el.checked;
}
