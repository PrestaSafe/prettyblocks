import { toolbar } from './toolbar'
import { getZoneDetailsByDom }  from './helper'

/**
 * THIS EVENT HANDLER IS THE CONTROLLER BETWEEN PRETTYBLOCKS AND THE PRESTASHOP BACKEND
 */

const getContext = () => {
     
    return{
        id_lang: prestashop.language.id,
        id_shop: prestashop.modules.prettyblocks.id_shop,
        shop_name: prestashop.modules.prettyblocks.shop_name,
        current_url: prestashop.modules.prettyblocks.shop_current_url,
        href: window.location.href
    }
}

// received data from prettyblocks
let eventHandler = (event) => {

    if(event.data.type == 'getContext')
    {
        let context = getContext()
        return event.source.postMessage({ type: 'setContext', 
            data: { data: context } }, '*'); 
    }

    if (event.data.type == 'initIframe') {
        event.source.postMessage({ type: 'iframeInit', data: null }, '*');
        return loadToolBar(event)
        
    }
    if(event.data.type == 'reloadBlock')
    {
        let id_prettyblocks = event.data
        return reloadBlock(id_prettyblocks, event)
    }
    if(event.data.type == 'selectBlock')
    {
        let id_prettyblocks = event.data.data.id_prettyblocks
        return selectBlock(id_prettyblocks,event)
    }

    // focus on zone in iframe
    if (event.data.type == 'focusOnZone') {
        let zone_name = event.data.data
        document.querySelectorAll('.border-dotted').forEach((div) => {
            div.classList.remove('border-dotted')
        })
        let el = document.querySelector('[data-prettyblocks-zone="' + zone_name + '"]')
        
        el.classList.add('border-dotted')
        return el.scrollIntoView({
            alignToTop: true,
            behavior: 'smooth',
            // block: 'top'
        })

    }

    // focus on block in iframe
    if (event.data.type == 'focusOnBlock') {
        let id_prettyblocks = event.data.data
        return focusBlock(id_prettyblocks)

    }
    if (event.data.type == 'getCurrentDocumentUrl') {
        return event.source.postMessage({ type: 'currentDocumentUrl', data: document.location.href }, '*');
    }
    // update HTML block
    if (event.data.type == 'updateHTMLBlock') {
        let id_prettyblocks = event.data.data.id_prettyblocks
        let data = event.data.data.html
        let domBlock = document.querySelector('[data-id-prettyblocks="' + id_prettyblocks + '"]')
        domBlock.innerHTML = data
        document.dispatchEvent(new CustomEvent('updatePrettyBlocks', { 
            detail: { block: {
                id_prettyblocks: id_prettyblocks
             }}
        }));
        return loadToolBar(event)
    }

    if (event.data.type == 'scrollInIframe') {
        return focusBlock(event.data.data)
    }
    if (event.data.type == 'getZones') {
        let els = document.querySelectorAll('[data-zone-name]')
        let zones = []
        let zone_name = ''

        els.forEach((el) => {
            zone_name = el.getAttribute('data-zone-name')
            let current_zone = {
                name: el.getAttribute('data-zone-name'),
                alias: el.getAttribute('data-zone-alias') || '',
                priority: el.getAttribute('data-zone-priority') || 'false',
            }

            if (!zones.some(zone => zone.name === zone_name)) {
                zones.push(current_zone)
            }
        })
        return event.source.postMessage({ type: 'zones', data: zones }, '*');
    }
    // unsubscribe()

    
}



/**
 * Select block in pretty block interface
 * @param {*} id_prettyblocks 
 * @param {*} event 
 * @returns 
 * @todo fix hover element
 */
const selectBlock = (id_prettyblocks, event) => {
        let el = focusBlock(id_prettyblocks)

        let zone_name = el.closest('[data-prettyblocks-zone]').getAttribute('data-prettyblocks-zone')
        let zoneElement = getZoneDetailsByDom(document.getQuerySelector('[data-zone-name="' + zone_name + '"]'))
        let params = {
            id_prettyblocks: id_prettyblocks,
            zone: zoneElement
        }
        return event.source.postMessage({ type: 'focusBlock', data: params }, '*');

}
const reloadBlock = (id_prettyblocks, event) => {
    return event.source.postMessage({ type: 'reloadBlock', data: id_prettyblocks }, '*');
}
const focusBlock = (id_prettyblocks) => {
    let doc = document
    let el = doc.querySelector('[data-id-prettyblocks="' + id_prettyblocks + '"]')

    if (doc.body.contains(el) && !el.classList.contains('border-dotted')) {
        el.scrollIntoView({
            alignToTop: false,
            behavior: 'smooth',
            // block: 'center'
        })
        let tr = doc.querySelectorAll('[data-block]')
        tr.forEach(bl => {
            bl.classList.remove('border-dotted')
        })
        el.classList.add('border-dotted')

    }
    return el
}
const loadToolBar = (event) => {
    const tb = new toolbar( document.querySelectorAll('.ptb-title'), document, window);
    tb.on('change', async (value) => {
        let params = {
            id_prettyblocks: value.html.closest('[data-id-prettyblocks]').getAttribute('data-id-prettyblocks'),
            field: value.html.getAttribute('data-field'),
            index: value.html.hasAttribute('data-index') ? value.html.getAttribute('data-index') : null
        }
        event.source.postMessage({ type: 'updateTitleComponent', 
            data: { params: params, value: JSON.stringify(value) } }, '*'); 
    })
} 

const moveBlockToZone = (event) => {
    let blockDragged = null;
    const blocks = document.querySelectorAll('[data-block]');
    const zones = document.querySelectorAll('[data-prettyblocks-zone]');

    blocks.forEach(block => {
        // block.setAttribute('draggable', true);
        block.addEventListener('dragstart', (e) => {
            e.preventDefault();
            // blockDragged = block;
            // zones.forEach(zone => {
            //     zone.classList.add('ondrag');
            // });
        });

        block.addEventListener('dragend', (e) => {
            e.preventDefault()
            // zones.forEach(zone => {
            //     zone.classList.remove('ondrag');
            // });
            // blockDragged = null;
        });

       


    });

}

document.addEventListener('DOMContentLoaded', (event) => {

 
      
    window.addEventListener("message", eventHandler, false)

    // Sélectionnez tous les liens de la page
    const links = document.querySelectorAll('a');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            let href = link.getAttribute('href')
            if (href && href !== '#') {

              let context = getContext()
              let params = {
                context: context,
                url: href,
              }
              window.parent.postMessage({ type: 'setNewUrl', params: params }, '*');
            }
            });
    });
        
    window.navigation.addEventListener("navigate", (event) => {
        let url = event.destination.url;
        if (url !== 'about:blank') {
            event.preventDefault(); // Empêche la navigation vers la nouvelle URL

            let context = getContext();
            let params = {
                context: context,
                url: url,
            };
            window.parent.postMessage({ type: 'setForceNewUrl', params: params }, '*');
        }
    });
});
   
