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
import { storeToRefs } from 'pinia'
/* Demo data */
// import { v4 as uuidv4 } from 'uuid'
import emitter from "tiny-emitter/instance";
import { useStore, useCurrentZone, contextShop, storedBlocks, usePrettyBlocksContext } from "../store/pinia";
import { trans } from "../scripts/trans";

import { createToaster } from "@meforma/vue-toaster";
const toaster = createToaster({
  position: "top",
});

const prettyblocks_env = ref(window.prettyblocks_env.PRETTYBLOCKS_REMOVE_ADS);

defineComponent({
  SortableList,
  MenuGroup,
  MenuItem,
  ButtonLight,
  Button,
  ZoneSelect,
});
 let prettyBlocksContext = usePrettyBlocksContext();
watch(() => prettyBlocksContext.currentZone, (currentZone) => {
  displayZoneName.value = currentZone.name
  initStates()
}, { deep: true })


const prettyblocks_version = ref(security_app.prettyblocks_version);
/**
 * Load block config
 */
const loadStateConfig = async (e) => {
  prettyBlocksContext.$patch({
    currentBlock: {
      id_prettyblocks: e.id_prettyblocks,
      instance_id: e.instance_id,
      code: e.code,
      subSelected: null,
      need_reload: e.need_reload,
      states: e.children
    }
  })
};


let displayZoneName = ref();
const loadSubState = async (e) => {
  // set store cuurent block name
  prettyBlocksContext.$patch({
    currentBlock: {
      need_reload: e.need_reload,
      id_prettyblocks: e.id_prettyblocks,
      subSelected: e.id,
    }
  });
};


const { blocksFormatted } = storeToRefs(prettyBlocksContext);
let groups = blocksFormatted; 

const initStates = async () => {
  prettyBlocksContext.initStates()
  
};
/**
 * Push an empty State (repeater)
 */
const loadEmptyState = async (e) => {
  let element = {
    id_prettyblocks: e.id_prettyblocks,
  };
  let context = prettyBlocksContext.psContext
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
        // emitter.emit("reloadIframe", e.id_prettyblocks);
        prettyBlocksContext.reloadIframe()
      }
      // get html block data
        prettyBlocksContext.sendPrettyBlocksEvents('reloadBlock', {id_prettyblocks: e.id_prettyblocks})
      
    })
    .catch((error) => console.error(error));
};

let currentBlock = useStore();
const state = ref({
  name: "displayHome",
});


onMounted(() => {
  document.addEventListener('DOMContentLoaded', async () => {
    setTimeout(async () => {
      checkClipboardContent();
    }, 500);
  });
})

/**
  * Copy current zone
  */
const copyZone = async () => {
  let context = await prettyBlocksContext.psContext;
  let current_zone = prettyBlocksContext.currentZone.name;
  const params = {
    action: "CopyZone",
    zone: current_zone,
    ctx_id_lang: context.id_lang,
    ctx_id_shop: context.id_shop,
  };
  localStorage.setItem('prettyblocks_clipboard', JSON.stringify(params));
  checkClipboardContent();
}

const pasteZone = async () => {
  let current_zone = prettyBlocksContext.currentZone.name;
  const storedData = localStorage.getItem('prettyblocks_clipboard');
  if (storedData) {
    const data = JSON.parse(storedData);
    if (data.hasOwnProperty('zone')) {
      let params = {
        ...data,
        zone_name_to_paste: current_zone,
        ajax_token: security_app.ajax_token,
        ajax: true,
      };
      HttpClient.post(ajax_urls.state, params).then((response) => {
        if (response.success) {
          toaster.show(response.message);
          localStorage.removeItem('prettyblocks_clipboard');
          checkClipboardContent();
          prettyBlocksContext.reloadIframe();
          prettyBlocksContext.initStates();
        }
      }).catch(error => console.error(error));
    }
  }
}

let showCopyZone = ref(false);
const checkClipboardContent = async () => {
  try {
    const storedData = localStorage.getItem('prettyblocks_clipboard');
    if (storedData) {
      const data = JSON.parse(storedData);
      showCopyZone.value = data.hasOwnProperty('zone');
    } else {
      showCopyZone.value = false;
    }
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
  let current_zone = prettyBlocksContext.currentZone.name;  
  let context = await prettyBlocksContext.psContext;
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
            window.location.reload()
          }
    })
    .catch(error => console.error(error));
}

prettyBlocksContext.on('iframeLoaded', () => {
  setTimeout(() => {
    checkClipboardContent();
  }, 1000);

});
</script>

<template>
  <div id="leftPanel" class="border-r border-gray-200">
    <div class="flex flex-col h-full">
      <div class="p-2 border-b border-gray-200">
        <div class="flex items-center space-around">
          <div class="flex-grow">
            <ZoneSelect v-model="state" />
          </div>
          <div class="pl-2 mt-[6px]">
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
              :icon_path="element.icon_path"
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
                    :id_prettyblocks="element.id_prettyblocks"
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
          @click="prettyBlocksContext.emit('toggleModal')"
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
        ><br />
        <a v-if="!prettyblocks_env" href="https://prettyblocks.io/pro" class="text-red-500" target="_blank">{{ trans('get_pro') }}</a>
      </div>
    </div>
  </div>
</template>

<style scoped>
#leftPanel {
  transition: all 0.5s ease;
}
</style>
