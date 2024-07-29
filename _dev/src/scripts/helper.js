
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