<script setup>
import { defineProps, ref, defineComponent } from 'vue'
import Button from './Button.vue'
import Icon from './Icon.vue'
import { currentZone } from '../store/currentBlock'
import Loader from './Loader.vue'
import axios from 'axios'
import emitter from 'tiny-emitter/instance'
import { createToaster } from "@meforma/vue-toaster";
import { contextShop } from '../store/currentBlock'

const toaster = createToaster({
    position: 'top'
});

defineProps({
    name: String,
    description: String,
    code: {
        type: String,
        required: true,
    },
    icon: {
        type: String,
        default: 'PuzzleIcon'
    },
})
defineComponent({
    Button,
    Icon,
    Loader
})
let showLoader = ref(false)

const AddOnZOne = async (code) => {
    let current_zone = currentZone()
    showLoader.value = true
    let context = contextShop()
    const params = {
        action: 'insertBlock',
        code: code,
        zone_name: current_zone.name,
        ctx_id_lang: context.id_lang,
        ctx_id_shop: context.id_shop,
        ajax_token: security_app.ajax_token
    }
    let url = ajax_urls.block_action_urls
    let res = await axios.get(url, { params })
    let data = await res.data
    emitter.emit('toggleModal', current_zone.name)
    emitter.emit('initStates')
    toaster.show('Block inséré avec succès', {
        position: "top"
    })

    emitter.emit('reloadIframe', data.id_prettyblocks);

}
</script>

<template>
    <div class="p-4 bg-gray-100 rounded-lg">
    
        <div class="mb-2 mx-auto">
            <Icon :name="icon" size="1" class="text-indigo" />
            <!-- <Loader :visible="showLoader"/> -->
        </div>
    
        <h3 class="text-lg font-bold mb-2 text-center">
            {{ name }}
        </h3>
    
        <p class="text-sm leading-6 text-gray-600">
            {{ description }}
        </p>
        <p class="text-center mt-4">
            <Button icon="PuzzleIcon" type="secondary" @click="AddOnZOne(code)">
                    Insert
                    
                  </Button>
        </p>
    
    </div>
</template>
