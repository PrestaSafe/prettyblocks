
const addPrettyButton = (btnGroupClass = '.btn-group-action', endpoint, idEndpointTarget) => {
    let btnGroups = document.querySelectorAll(btnGroupClass);
    btnGroups.forEach(btnGroup => {
        let id_cms = parseInt(btnGroup.closest('tr').querySelector(idEndpointTarget).textContent);

        // Début de la sélection
        let newElement = document.createElement('a');
        newElement.innerHTML = "<img width='30' src='"+prettyblocks_logo+"' alt='edit in prettyblocks'>";
        newElement.dataset.endpoint = endpoint;
        newElement.dataset.idEndpoint = id_cms;
        newElement.dataset.action = "open_in_prettyblocks";
        // Fin de la sélection
        btnGroup.prepend(newElement);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    
// CMS LIST Button
if (document.body.classList.contains('admincmscontent')) {
    addPrettyButton('.btn-group-action', 'cms', '.column-id_cms');
}

// Product LIST Button
if (document.body.classList.contains('adminproducts')) {
    addPrettyButton('.btn-group-action', 'product', '.column-id_product');
}
if (document.body.classList.contains('admincategories')) {
    addPrettyButton('.btn-group-action', 'category', '.column-id_category');
}


// Début de la sélection
let prettyBlocksButtons = document.querySelectorAll('[data-action="open_in_prettyblocks"]');
// Fin de la sélection
prettyBlocksButtons.forEach(button => {
    button.addEventListener('click', function(event) {
        event.preventDefault();
        // Ajoutez ici le code à exécuter lors du clic sur le bouton
        let endpoint = button.dataset.endpoint;
        let idEndpoint = button.dataset.idEndpoint;
        let action = button.dataset.action;
        let url = prettyblocks_route_generator;

        // Début de la sélection
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                endpoint: endpoint,
                id: idEndpoint,
            }),
        })
        .then(response => response.json())
        .then(data => window.open(data.url, '_self'));
        // Fin de la sélection
    });
});


})