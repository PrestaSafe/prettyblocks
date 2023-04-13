window.addEventListener('load', function() {

	// Toolbar
	document.body.classList.add('pb-active')

	const loader= document.getElementById('loader');

	let toggle = document.querySelector('#toggle-prettyblocks')

	toggle.addEventListener('click', function() {
		document.body.classList.toggle('toggle-pb')
	})

	let close = document.querySelector('#modal-prettyblocks .close-pb')

	close.addEventListener('click', function() {
		document.body.classList.remove('toggle-pb')
	})


	const searchType= document.querySelector('#prettyblocks-change-search');
	const searchResults= document.querySelector('#prettyblocks-search-results');
	const searchProducts= document.querySelector('#prettyblocks-search-products');

	// Change search type
	if (null !== searchType) {
		searchType.addEventListener('change', function () {
			searchResults.innerHTML = "";
			searchProducts.value 	= "";
			loader.style.display = "none";
		});
	}

	// Search products
	if (typeof toolbarSearchUrl !== 'undefined' && searchProducts !== null && searchType !== null) {
		searchProducts.addEventListener('input', function (evt) {
			if (searchProducts.value < 3) {
				searchResults.innerHTML = "";
			} else {
				getData(searchProducts.value, searchType.value);
			}
		});
	}

	// Delete text search input
	const deleteSearchBtn = document.getElementById('prettyblocks-delete-search');
	if (deleteSearchBtn instanceof HTMLImageElement) {
		deleteSearchBtn.addEventListener('click', function () {
			searchProducts.value 	= "";
			loader.style.display 	= "none";
			searchResults.innerHTML = "";
		});
	}

})


const getData = (terms, type) => {

	if (terms.length < 3 || window.prettyToolbarIsLoading === 1) {
		return;
	}

	const loader= document.getElementById('loader');
	loader.style.display = "block";

	const searchResults  = document.querySelector('#prettyblocks-search-results');

	if (searchResults !== null) {

		window.prettyToolbarIsLoading = 1;

		fetch(toolbarSearchUrl + '&terms=' + terms + '&type=' + type, {
			method: 'GET',
			headers: new Headers({
				'accept': 'application/ld+json',
				'X-Requested-With': 'XMLHttpRequest'
			})
		})
		.then((resp) => resp.json())
		.then((res) => {
			window.prettyToolbarIsLoading = 0;
			loader.style.display = "none";
			if (res.data && res.success) {
				searchResults.innerHTML = res.data
			}
		})
	}
}

