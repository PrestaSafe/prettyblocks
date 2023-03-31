<script setup>
import { ref, onMounted, defineComponent } from 'vue'
import SortableList from './SortableList.vue'
import MenuGroup from './MenuGroup.vue'
import MenuItem from './MenuItem.vue'
import ButtonLight from './ButtonLight.vue'
import axios from 'axios'
import Block from '../scripts/block'
import ZoneSelect from './form/ZoneSelect.vue';
/* Demo data */
// import { v4 as uuidv4 } from 'uuid'
import emitter from 'tiny-emitter/instance'
import { useStore, currentZone, contextShop } from '../store/currentBlock'
import { trans } from '../scripts/trans'

defineComponent({
  SortableList,
  MenuGroup,
  MenuItem,
  ButtonLight,
  ZoneSelect
})

const loadStateConfig = async (e) => {
  let currentBlock = useStore()
  // set store cuurent block name
  await currentBlock.$patch({
    // code: e.id,
    need_reload: e.need_reload,
    id_prettyblocks: e.id_prettyblocks,
    // instance_id: e.instance_id
  })
  emitter.emit('displayBlockConfig', e);

}
// emitter.on('loadStateConfig', async (id_prettyblocks) => {
//   let block = await Block.loadById(id_prettyblocks)
//   console.log('block', block.id_prettyblocks)
//   loadStateConfig(block)
//   const store = useStore()
//      store.$patch({
//         id_prettyblocks: block.id_prettyblocks,
//         subSelected: block.code+'_'+block.id_prettyblocks
//     })
// })

let displayZoneName = ref()
const loadSubState = async (e) => {
  let currentBlock = useStore()
  // set store cuurent block name
  await currentBlock.$patch({
    need_reload: e.need_reload,
    id_prettyblocks: e.id_prettyblocks,
    subSelected: e.id
  })
  emitter.emit('displaySubState', e);

}

let groups = ref([])


emitter.on('initStates', () => {
  initStates()
})
const initStates = async () => {

  let contextStore = contextShop();
  // Attendez que l'action asynchrone getContext soit terminée
  let context = await contextStore.getContext();

  let current_zone = currentZone().name
  displayZoneName.value = current_zone
  const params = {
    ajax: true,
    action: 'GetStates',
    zone: current_zone,
    ctx_id_lang: context.id_lang,
    ctx_id_shop: context.id_shop,
    ajax_token: security_app.ajax_token
  }
  groups.value = []
  axios.get(ajax_urls.state, { params }).then((response) => response.data)
    .then((data) => {

      groups.value = Object.entries(data.blocks).map(([key, value] = block) => {
        return value.formatted;
      })
    })
}
/**
 * Push an empty State (repeater)
 */
const loadEmptyState = (e) => {
  let element = {
    id_prettyblocks: e.id_prettyblocks
  }
  let context = contextShop()
  loadSubState(element)
  const params = {
    id_prettyblocks: e.id_prettyblocks,
    action: 'getEmptyState',
    ajax: true,
    ctx_id_lang: context.id_lang,
    ctx_id_shop: context.id_shop,
    ajax_token: security_app.ajax_token
  }
  axios.get(ajax_urls.state, { params }).then((response) => response.data)
    .then((data) => {
      initStates()
      if (e.need_reload) {
        emitter.emit('reloadIframe', e.id_prettyblocks)
      }
      emitter.emit('stateUpdated', e.id_prettyblocks)
    })
}

let currentBlock = useStore()
const state = ref({
  name: "displayHome"
})
</script>

<template>
  <div id="leftPanel" class="border-r border-gray-200">
    <div class="flex flex-col h-full">
      <div class="p-2 border-b border-gray-200">
        <ZoneSelect v-model="state" />
      </div>
      <div class="overflow-y-auto flex-grow p-2 border-b border-gray-200">
        <!-- sortable component is used to sort by drag and drop -->
        <SortableList :items="groups" group="menu-group">
          <template v-slot="{ element }">
            <!-- group of element (collapsable) -->
            <MenuGroup @changeState="loadStateConfig" @pushEmptyState="loadEmptyState(element)" :id="element.id"
              :id_prettyblocks="element.id_prettyblocks" :title="element.title" :icon="element.icon" :config="true"
              :element="element" :is_parent="true">
              <SortableList :items="element.children" :group="'menu-group-' + element.id_prettyblocks"
                action="updateStatePosition">
                <template v-slot="{ element }">
                  <!-- items of the group -->
                  <MenuItem :id="element.id.toString()" :title="element.title" :icon="element.icon" :element="element"
                    :is_child="true" @click="loadSubState(element)">
                  </MenuItem>
                </template>
              </SortableList>
            </MenuGroup>
          </template>
        </SortableList>
        <ButtonLight icon="ArrowDownOnSquareStackIcon" @click="emitter.emit('toggleModal', displayZoneName)"
          class="bg-slate-200 p-2 text-center hover:bg-indigo hover:bg-opacity-10 w-full text-indigo">
          {{ trans('add_new_element') }}
        </ButtonLight>
      </div>
      <div class="p-2 text-sm text-center">
        <a class="text-indigo" href="https://prettyblocks.io/" target="_blank">PrettyBlocks</a><br>
        Made with ❤️ by <a class="text-indigo" href="https://www.prestasafe.com" target="_blank">PrestaSafe</a>
      </div>
    </div>
  </div>
</template>

<style scoped>
#leftPanel {
  transition: all 0.5s ease;
}
</style>
