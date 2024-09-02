const addPrettyButton = (btnGroupClass = '.btn-group-action', endpoint, idEndpointTarget) => {
    let btnGroups = document.querySelectorAll(btnGroupClass);
    btnGroups.forEach(btnGroup => {
        let idTarget = 0;
        idTarget = parseInt(btnGroup.closest('tr').dataset.productId);
        let newElement = document.createElement('a');
        newElement.target = '_blank';
        newElement.innerHTML = "<img width='30' src='" + prettyblocks_logo + "' alt='edit in prettyblocks'>";
        newElement.dataset.endpoint = endpoint;
        newElement.dataset.idEndpoint = idTarget;
        newElement.dataset.action = "open_in_prettyblocks";


        let btnGroupInner = btnGroup.querySelector('.btn-group');
        if (btnGroupInner) {
            btnGroupInner.appendChild(newElement);
        } else {
            btnGroup.appendChild(newElement);
        }
    });
}



document.addEventListener('DOMContentLoaded', function () {

    if (document.body.classList.contains('adminproducts')) {
        addPrettyButton('.btn-group-action', 'product', '[data-product-id]');
    }


    let prettyBlocksButtons = document.querySelectorAll('[data-action="open_in_prettyblocks"]');

    prettyBlocksButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();

            let endpoint = button.dataset.endpoint;
            let idEndpoint = button.dataset.idEndpoint;
            let action = button.dataset.action;
            let url = prettyblocks_route_generator;


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
        });
    });


})