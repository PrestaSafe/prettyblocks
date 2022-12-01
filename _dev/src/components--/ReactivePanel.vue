<script>
import {ref} from 'vue'
export default {
    emits: ['editModule'],
    setup(props, context)
    {
        let html = ref()
        const editModule = (e) => {
            context.emit('editModule',e)
        }

        const loadHomeSimulator = () => {
            fetch(window.ajax_urls.simulate_home)
            .then((response) => { return response.text() })
            .then((text) => { html.value = text })
        }
        loadHomeSimulator()
        return {
            editModule,
            loadHomeSimulator,
            html
        }

    }
}
</script>

<template>
    <div>
        {{ html }}
        <header  @click="editModule" style="background: #CCC;" class="p-3 rounded-pill text-center">
            Top bar
        </header>
        <div  @click="editModule" style="background: #CCC;" class="p-3 rounded-pill text-center my-4">
            Slider
        </div>

        <div  @click="editModule" style="background: #CCC;" class="p-3 rounded-pill text-center my-4">
            Thumbnail
        </div>
        
        <div  @click="editModule" style="background: #CCC;" class="p-3 rounded-pill text-center my-4">
            Featured product
        </div>

        <div @click="editModule"  style="background: #CCC;" class="p-3 rounded-pill text-center my-4">
            Banner
        </div>

         <div  @click="editModule" style="background: #CCC;" class="p-3 rounded-pill text-center my-4">
            Home Category
        </div>

         <div style="background: #CCC;" class="p-3 rounded-pill text-center my-4">
            Brand
        </div>
    </div>

</template>

<style>
    
</style>