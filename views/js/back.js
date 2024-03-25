@ -0,0 +1,99 @@
const addPrettyButton = (btnGroupClass = '.btn-group-action', endpoint, idEndpointTarget) => {
    let btnGroups = document.querySelectorAll(btnGroupClass);
    btnGroups.forEach(btnGroup => {
        let idTarget = 0;

        // Si idEndpointTarget est une fonction, on l'exécute pour obtenir l'idTarget
        if (typeof idEndpointTarget === 'function') {
            idTarget = idEndpointTarget(btnGroup);
        } else {
            // Sinon, on garde la logique existante
            if (ps8) {
                idTarget = parseInt(btnGroup.closest('tr').querySelector(idEndpointTarget).textContent);
            }
            if (ps17) {
                idTarget = parseInt(btnGroup.closest('tr').getAttribute(idEndpointTarget));
            }
        }
        
        let newElement = document.createElement('a');
        newElement.innerHTML = "<img style='float: right; padding-top: 6px;' width='30' src='"+prettyblocks_logo+"' alt='edit in prettyblocks'>";
        newElement.dataset.endpoint = endpoint;
        newElement.dataset.idEndpoint = idTarget;
        newElement.dataset.action = "open_in_prettyblocks";

        btnGroup.prepend(newElement);
    });
}


  
document.addEventListener('DOMContentLoaded', function() {
    // Exemples d'utilisation
    
// CMS LIST Button
if (document.body.classList.contains('admincmscontent')) {
    if(ps8){
        addPrettyButton('.btn-group-action', 'cms', '.column-id_cms');
    }
    if(ps17){
        addPrettyButton('.btn-group-action', 'cms',  (btnGroup) => {
            if (btnGroup.closest('tr').querySelector('.column-id_cms') !== null) {
                let id_cms = btnGroup.closest('tr').querySelector('.column-id_cms').textContent;
                return parseInt(id_cms);
            }
          
        });
        
    }
}

// Product LIST Button
if (document.body.classList.contains('adminproducts')) {
    
    if(ps8){
        addPrettyButton('.btn-group-action', 'product', '.column-id_product');
    }
    if(ps17){
        addPrettyButton('.btn-group-action', 'product', '[data-product-id]');
    }
}
// categories
if (document.body.classList.contains('admincategories')) {
    addPrettyButton('.btn-group-action', 'category', (btnGroup) => {
        return parseInt(btnGroup.closest('tr').querySelector('.column-id_category').textContent);
    });
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