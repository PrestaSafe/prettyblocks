window.addEventListener('load', function() {

	// Toolbar
	document.body.classList.add('pb-active')

	let toggle = document.querySelector('#toggle-prettyblocks')

	toggle.addEventListener('click', function() {
		document.body.classList.toggle('toggle-pb')
	})

	let close = document.querySelector('#modal-prettyblocks .close-pb')

	close.addEventListener('click', function() {
		document.body.classList.remove('toggle-pb')
	})


	// Change search type
	const searchType 		 = document.querySelector('#prettyblocks-change-search');
	const searchResults      = document.querySelector('#prettyblocks-search-results');

	if (null !== searchType) {
		searchType.addEventListener('change', function () {
			searchResults.innerHTML = "";
		});
	}

	// Search products
	const searchProducts   = document.querySelector('#prettyblocks-search-products');
	if (typeof toolbarSearchUrl !== 'undefined' && searchProducts !== null && searchType !== null) {
		searchProducts.addEventListener('input', function (evt) {
			getData(searchProducts.value, searchType.value);
		});
	}

})


const getData = (terms, type) => {

	if (terms.length < 3) {
		return;
	}

	const searchResults  = document.querySelector('#prettyblocks-search-results');

	if (searchResults !== null) {

		fetch(toolbarSearchUrl + '&terms=' + terms + '&type=' + type, {
			method: 'GET',
			headers: new Headers({
				'accept': 'application/ld+json',
				'X-Requested-With': 'XMLHttpRequest'
			})
		})
		.then((resp) => resp.json())
		.then((res) => {
			if (res.data && res.success) {
				searchResults.innerHTML = res.data
			}
		})
	}
}

