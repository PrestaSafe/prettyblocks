<script setup>
import { ref, onMounted, defineComponent,onBeforeUnmount, computed, watchEffect, watch } from "vue";
import SortableList from "./SortableList.vue";
import MenuGroup from "./MenuGroup.vue";
import MenuItem from "./MenuItem.vue";
import ButtonLight from "./ButtonLight.vue";
import Button from "./Button.vue";
import { HttpClient } from "../services/HttpClient";
import Block from "../scripts/block";
import ZoneSelect from "./form/ZoneSelect.vue";
/* Demo data */
// import { v4 as uuidv4 } from 'uuid'
import emitter from "tiny-emitter/instance";
import { useStore, currentZone, contextShop, storedBlocks } from "../store/currentBlock";
import { trans } from "../scripts/trans";

import { createToaster } from "@meforma/vue-toaster";
const toaster = createToaster({
  position: "top",
});

defineComponent({
  SortableList,
  MenuGroup,
  MenuItem,
  ButtonLight,
  Button,
  ZoneSelect,
});
const prettyblocks_version = ref(security_app.prettyblocks_version);
const loadStateConfig = async (e) => {
  let currentBlock = useStore();
  // set store cuurent block name
  await currentBlock.$patch({
    // code: e.id,
    need_reload: e.need_reload,
    id_prettyblocks: e.id_prettyblocks,
    // instance_id: e.instance_id
  });

  emitter.emit("displayBlockConfig", e);
};
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

let displayZoneName = ref();
const loadSubState = async (e) => {
  let currentBlock = useStore();
  // set store cuurent block name
  await currentBlock.$patch({
    need_reload: e.need_reload,
    id_prettyblocks: e.id_prettyblocks,
    subSelected: e.id,
  });
  emitter.emit("displaySubState", e);
};

let groups = ref([]);

emitter.on("initStates", () => {
  initStates();
});
const initStates = async () => {
  let contextStore = contextShop();
  let context = await contextStore.getContext();
  let current_zone = currentZone().name;
  let piniaStored = storedBlocks();
  displayZoneName.value = current_zone;
  const params = {
    ajax: true,
    action: "GetStates",
    zone: current_zone,
    ctx_id_lang: context.id_lang,
    ctx_id_shop: context.id_shop,
    ajax_token: security_app.ajax_token,
  };
  // groups.value = []
  HttpClient.get(ajax_urls.state, params)
    .then((data) => {
      groups.value = Object.entries(data.blocks).map(([key, value] = block) => {
        return value.formatted;
      });

      piniaStored.$patch({
        blocks: data.blocks,
      });
    })
    .catch((error) => console.error(error));
};
/**
 * Push an empty State (repeater)
 */
const loadEmptyState = (e) => {
  let element = {
    id_prettyblocks: e.id_prettyblocks,
  };
  let context = contextShop();
  loadSubState(element);
  const params = {
    id_prettyblocks: e.id_prettyblocks,
    action: "getEmptyState",
    ajax: true,
    ctx_id_lang: context.id_lang,
    ctx_id_shop: context.id_shop,
    ajax_token: security_app.ajax_token,
  };
  HttpClient.get(ajax_urls.state, params)
    .then((data) => {
      initStates();
      if (e.need_reload) {
        emitter.emit("reloadIframe", e.id_prettyblocks);
      }
      emitter.emit("stateUpdated", e.id_prettyblocks);
    })
    .catch((error) => console.error(error));
};

let currentBlock = useStore();
const state = ref({
  name: "displayHome",
});

/**
  * Copy current zone
  */
const copyZone = async () => {
  let contextStore = contextShop();
  let context = await contextStore.getContext();
  let current_zone = currentZone().name;
  const params = {
    action: "CopyZone",
    zone: current_zone,
    ctx_id_lang: context.id_lang,
    ctx_id_shop: context.id_shop,
  };
  navigator.clipboard.writeText(JSON.stringify(params)).then(function() {
    console.log('Copying to clipboard was successful!' );
   
  }, function(err) {
    console.error('Could not copy text: ', err);
  });
  checkClipboardContent()
}

/**
 * Paste current zone
 */
const pasteZone = async () => {
  let current_zone = currentZone().name;
  const clipboardData = await navigator.clipboard.readText();
  const data = JSON.parse(clipboardData);
  if (data.hasOwnProperty('zone')) {
    let params = {
      ...data,
      zone_name_to_paste: current_zone,
      ajax_token: security_app.ajax_token,
      ajax: true,
    };
    HttpClient.post(ajax_urls.state, params).then((response) => {

            if (response.success) {
              toaster.show(response.message)
              emitter.emit('reloadIframe')
              // clear clipboard if zone is pasted
             navigator.clipboard.writeText('').then(function() {
                checkClipboardContent()
              }, function(err) {
                console.error('Could not empty clipboard: ', err);
                checkClipboardContent()
              });
            }
      })
      .catch(error => console.error(error));
    }

}

let showCopyZone = ref(false);
const checkClipboardContent = async () => {
    try {
        const clipboardData = await navigator.clipboard.readText();
        const data = JSON.parse(clipboardData);
        showCopyZone.value = data.hasOwnProperty('zone');
        window.blur();
    } catch (error) {
        showCopyZone.value = false;
    }
};

/**
 * Delete all blocks in current zone
 */
const deleteAllBlocks = async () => {
  if(confirm('Warning: This will delete all blocks in this zone. Are you sure?') == false) {
    return;
  }
  let current_zone = currentZone().name;
  let contextStore = contextShop();
  let context = await contextStore.getContext();
  const params = {
    action: "DeleteAllBlocks",
    zone: current_zone,
    ajax_token: security_app.ajax_token,
    ctx_id_lang: context.id_lang,
    ctx_id_shop: context.id_shop,
    ajax: true,
  };
  HttpClient.post(ajax_urls.state, params).then((response) => {

          if (response.success) {
            toaster.show(response.message)
            emitter.emit('reloadIframe')
          }
    })
    .catch(error => console.error(error));
}


</script>

<template>
  <div id="leftPanel" class="border-r border-gray-200">
    <div class="flex flex-col h-full">
      <div class="p-2 border-b border-gray-200">
        <div class="flex items-center space-around">
          <div>
            <ZoneSelect v-model="state" />
          </div>
          <div class="pl-2 mt-[6px]" v-if="!showCopyZone">
            <ButtonLight type="secondary" icon="TrashIcon" @click="deleteAllBlocks" size="6"/>
          </div>
          <div class="mt-[6px]">
            <ButtonLight type="secondary" icon="Square2StackIcon" @click="copyZone" size="6"/>
          </div>
          <div class="mt-[6px]" v-if="showCopyZone">
            <ButtonLight type="secondary" icon="ArrowDownOnSquareStackIcon" @click="pasteZone" size="6"/>
          </div>
        </div>
      </div>
      
      <div class="overflow-y-auto flex-grow p-2 border-b border-gray-200">
        <!-- sortable component is used to sort by drag and drop -->
        <SortableList :items="groups" group="menu-group">
          <template v-slot="{ element }">
            <!-- group of element (collapsable) -->
            <MenuGroup
              @changeState="loadStateConfig"
              @pushEmptyState="loadEmptyState(element)"
              :id="element.id"
              :id_prettyblocks="element.id_prettyblocks"
              :title="element.title"
              :icon="element.icon"
              :config="true"
              :element="element"
              :is_parent="true"
            >
              <SortableList
                :items="element.children"
                :group="'menu-group-' + element.id_prettyblocks"
                action="updateStatePosition"
              >
                <template v-slot="{ element }">
                  <!-- items of the group -->
                  <MenuItem
                    :id="element.id.toString()"
                    :title="element.title"
                    :icon="element.icon"
                    :element="element"
                    :is_child="true"
                    @click="loadSubState(element)"
                  >
                  </MenuItem>
                </template>
              </SortableList>
            </MenuGroup>
          </template>
        </SortableList>
        <ButtonLight
          icon="ArrowDownOnSquareStackIcon"
          @click="emitter.emit('toggleModal', displayZoneName)"
          class="bg-slate-200 p-2 text-center hover:bg-indigo hover:bg-opacity-10 w-full text-indigo"
        >
          {{ trans("add_new_element") }}
        </ButtonLight>
      </div>
      <div class="p-2 text-sm text-center">
        <a class="text-indigo" href="https://prettyblocks.io/" target="_blank"
          >PrettyBlocks (v{{ prettyblocks_version }}) </a
        ><br />
        Made with ❤️ by
        <a class="text-indigo" href="https://www.prestasafe.com" target="_blank"
          >PrestaSafe</a
        >
      </div>
    </div>
  </div>
</template>

<style scoped>
#leftPanel {
  transition: all 0.5s ease;
}
</style>
