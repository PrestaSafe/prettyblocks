window.addEventListener('load', function() {
	document.body.classList.add('pb-active')

	let toggle = document.querySelector('#toggle-prettyblocks')
	console.log(toggle);
	toggle.addEventListener('click', function() {
		document.body.classList.toggle('toggle-pb')
	})

	let close = document.querySelector('#modal-prettyblocks .close-pb')

	close.addEventListener('click', function() {
		document.body.classList.remove('toggle-pb')
	})
})

