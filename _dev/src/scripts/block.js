import emitter from 'tiny-emitter/instance'
import { contextShop, useStore } from '../store/currentBlock'
import { HttpClient } from "../services/HttpClient";
import { ref } from 'vue'
export default class Block {
    id_prettyblocks = 0;
    instance_id = null;
    code = null;
    subSelected = null;
    need_reload = true
    states = ref([])
    id_shop = 0;
    id_lang = 0;

      
    constructor(element) {
        let context = contextShop()
        this.id_prettyblocks = element.id_prettyblocks
        this.instance_id = element.instance_id
        this.code = element.code 
        this.subSelected = element.subSelected 
        this.need_reload = element.need_reload 
        this.id_shop = context.id_shop
        this.id_lang = context.id_lang

    }

    async loadBlockConfig() {
        let currentID = (this.id_prettyblocks != 0) ? this.id_prettyblocks : this.getCurrentBlock().id_prettyblocks
        const params = {
            id_prettyblocks: currentID,
            action: 'getBlockConfig',
            ajax: true,
            ctx_id_lang: this.id_lang,
            ctx_id_shop: this.id_shop,
            ajax_token: security_app.ajax_token

        }
        let data = await HttpClient.post(ajax_urls.state, params);
        this.states = data.state.repeater_db
        return data
    }
    get id()
    {
        return this.id_prettyblocks
    }

    getCurrentBlock()
    {
        return useStore()
    }

    async saveConfig(configState) {

        const params = {
            id_prettyblocks: this.id_prettyblocks,
            action: 'updateBlockConfig',
            state: JSON.stringify(configState),
            subSelected: this.subSelected,
            ajax: true,
            ctx_id_lang: this.id_lang,
            ctx_id_shop: this.id_shop,
            ajax_token: security_app.ajax_token
        }
        let data = await HttpClient.post(ajax_urls.state, params);
        return data
        
    }
    loadStates(states)
    {
        this.states.value = states
    }


    getStates()
    {
        return this.states.value
    }
    
    setStateByKey(key, value)
    {
        this.states.value[key] = value
    }

    getSubSelectedKey()
    {
        let key_formatted = 0
        this.subSelected = this.getCurrentBlock().subSelected
        key_formatted = this.subSelected.split('-')[1]
        return key_formatted
    }

    async updateSubSelectItem(state)
    {
         // let currentBlock = useStore()
        const params = {
            id_prettyblocks: this.id_prettyblocks,
            action: 'updateState',
            state: JSON.stringify(state.value),
            subSelected: this.getSubSelectedKey(),
            ajax: true, 
            ctx_id_lang: this.id_lang,
            ctx_id_shop: this.id_shop,
            ajax_token: security_app.ajax_token
        }
        let data = await HttpClient.post(ajax_urls.state, params);

        return data
    }

    save()
    {
        
    }

    getStateByKey(key)
    {
        return this.states.value[key]
    }

    reloadIframe()
    {
        emitter.emit('reloadIframe')
    }

    displayStates(){
        emitter.emit('displayStates')
    }

    focusOnIframe()
    {
        emitter.emit('scrollInIframe', this.id_prettyblocks)
    }

    static async loadById(id_prettyblocks)
    {
        let context = contextShop()
        let id_lang = context.id_lang
        let id_shop = context.id_shop
        
        const params = {
            id_prettyblocks: id_prettyblocks,
            action: 'loadBlockById',
            ajax: true,
            ctx_id_lang: id_lang,
            ctx_id_shop: id_shop,
            ajax_token: security_app.ajax_token
        }
        let data = await HttpClient.get(ajax_urls.state, params);
        let block = new Block(data)
        block.loadStates(data.repeater_db)
        return block
    }

} 