
import { usePrettyBlocksContext } from '../store/pinia';
import { HttpClient } from '../services/HttpClient';


export function getZoneDetailsByDom(domElement) {
    return {
        name: domElement.getAttribute('data-zone-name'),
        alias: domElement.getAttribute('data-zone-alias') || '',
        priority: domElement.getAttribute('data-zone-priority') || false,
    }
}

export async function getBlockRender(id_prettyblocks) {
    let prettyBlocksContext = usePrettyBlocksContext()
    let responseData = {}
    const params = {
        ajax: true,
        id_prettyblocks: id_prettyblocks,
        action: 'GetBlockRender',
        ctx_id_lang: prettyBlocksContext.psContext.id_lang,
        ctx_id_shop: prettyBlocksContext.psContext.id_shop,
        ajax_token: security_app.ajax_token
    }
    try {
        const data = await HttpClient.get(ajax_urls.block_url, params);
        responseData = data.html;
    } catch (error) {
        console.log('Error fetching block render:', error);
        responseData = ''; // Set empty string as fallback
    }

    return responseData;
}

export async function updateTitleComponent(newValue, id_block = null, field = null, index = null) {
    if (!id_block) {
        id_block = newValue.html.closest('[data-id-prettyblocks]').getAttribute('data-id-prettyblocks')
    }
    if (!field) {
        field = newValue.html.getAttribute('data-field')
    }
    if (!index) {
        index = null
    }

    let prettyBlocksContext = usePrettyBlocksContext()
    let context = prettyBlocksContext.psContext
    let data = {
        id_prettyblocks: id_block,
        element: newValue,
        ctx_id_lang: context.id_lang,
        ctx_id_shop: context.id_shop,
        field: field,
        ajax: true,
        index: index,
        action: 'updateTitleComponent',
        ajax_token: security_app.ajax_token
    }

    try {
        const response = await HttpClient.post(ajax_urls.api, data);
        prettyBlocksContext.displayMessage(response.message);
    } catch (error) {
        console.error(error);
    }

}