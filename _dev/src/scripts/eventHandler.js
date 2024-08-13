/*
 *  RECEPETION HANDLER ON FRONT OFFICE
 */
import { usePrettyBlocksContext } from '../store/pinia'
import { getBlockRender, updateTitleComponent } from './helper'
import { trans } from './trans'


/**
 * Event Handler for PrettyBlocks FRONT
 * @param {Event} event 
 */
export const eventHandler = async (event) => {
    let prettyBlocksContext = usePrettyBlocksContext()
            if (event.data.type == 'zones') {
                let zones = event.data.data
                prettyBlocksContext.$patch({
                    zones: zones
                })
            }
           
            if(event.data.type == 'setNewUrl' || event.data.type == 'setForceNewUrl')
                {  
                    let context = event.data.params.context
                    let custom_url = event.data.params.url
                    let force_reload = event.data.type == 'setForceNewUrl' ? true : false
                            
                    
                    prettyBlocksContext.$patch({
                        psContext: {
                        ...prettyBlocksContext.psContext,
                        ...context,
                        current_url: custom_url
                    }
                });
                if(force_reload) {
                    window.location.reload()
                }else{
                    prettyBlocksContext.changeUrl(custom_url)
                }
            }
          
          

            if (event.data.type == 'updateTitleComponent') {
                let params = event.data.data.params
                updateTitleComponent(JSON.parse(event.data.data.value), params.id_prettyblocks, params.field, params.index)

            }
            // if(event.data.type == 'moveBlockToZone')
            // {
            //     let id_prettyblocks = event.data.params.id_prettyblocks
            //     let zone_name = event.data.params.zone_name
            //     let context = contextShop()
            //     HttpClient.post(ajax_urls.api, {
            //         action: 'moveBlockToZone',
            //         id_prettyblocks: id_prettyblocks,
            //         zone_name: zone_name,
            //         ajax: true,
            //         ajax_token: security_app.ajax_token,
            //         ctx_id_lang: context.id_lang,
            //         ctx_id_shop: context.id_shop,
            //     }).then((response) => {
            //         if (response.success) {
            //             toaster.show(response.message);
            //             // emitter.emit('initStates')
            //         }
            //     })

            // }


            

     
            if (event.data.type == 'reloadBlock') {
                let id_prettyblocks = event.data.data.data.id_prettyblocks  
                getBlockRender(id_prettyblocks).then((html) => {

                    prettyBlocksContext.sendPrettyBlocksEvents('updateHTMLBlock', {
                        id_prettyblocks: id_prettyblocks,
                        html: html
                    })

                })

            }
            if (event.data.type == 'setContext') {

                let iwindow = event.data.data.data
                await prettyBlocksContext.$patch({
                    psContext: {
                        id_lang: iwindow.id_lang,
                        id_shop: iwindow.id_shop,
                        shop_name: iwindow.shop_name,
                        // current_url: iwindow.current_url,
                        href: iwindow.href,
                    }
                })
            }

}

