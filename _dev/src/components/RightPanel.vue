<script setup>
import { defineComponent, onMounted, ref, reactive, watch, computed, toRaw, nextTick } from "vue";
import Select from "./form/Select.vue";
import SimpleSelect from "./form/SimpleSelect.vue";
import HeaderDropdown from "./HeaderDropdown.vue";
import Input from "./form/Input.vue";
import Textarea from "./form/Textarea.vue";
import Checkbox from "./form/Checkbox.vue";
import Radio from "./form/Radio.vue";
import FormControl from "./form/FormControl.vue";
import Button from "./Button.vue";
import FileUpload from "./form/FileUpload.vue";
import Icon from "./Icon.vue";
import PanelThemeSettings from "./PanelThemeSettings.vue";
import { usePrettyBlocksContext } from "../store/pinia";

import Title from "./Title.vue";
import Loader from "./Loader.vue";
import Modal from "./Modal.vue";
import Accordion from "./Accordion.vue";
import { createToaster } from "@meforma/vue-toaster";
import { v4 as uuidv4 } from "uuid";
import Editor from "@tinymce/tinymce-vue";
import Block from "../scripts/block";
import ColorInput from "vue-color-input";
import FieldRepeater from "./FieldRepeater.vue";
import { trans } from "../scripts/trans";
import { storeToRefs } from 'pinia'

import SpacingSection from "./_partials/SpacingSection.vue";
const toaster = createToaster({
  position: "top",
});

defineComponent({
  Select,
  Input,
  Textarea,
  Checkbox,
  Radio,
  FormControl,
  Button,
  HeaderDropdown,
  PanelThemeSettings,
  Icon,
  FileUpload,
  Loader,
  Modal,
  Accordion,
  uuidv4,
  SimpleSelect,
  Editor,
  ColorInput,
  FieldRepeater,
  Title,
});

let prettyBlocksContext = usePrettyBlocksContext() 
const { currentBlock } = storeToRefs(prettyBlocksContext)
const subSelected = computed(() => currentBlock.value.subSelected)
const saveContext = computed(() => prettyBlocksContext.saveContext)
const showPanel = computed(() => saveContext.value === 'settings');


const state = ref(false);
const config = ref(false);
const showLoader = ref(false);
const blockLoaded = ref(false);


// load block config in right panel
watch(currentBlock, (newVal) => {
  if (newVal.subSelected == null) {
    loadBlockConfig(newVal)
  }else{
    getSubState(newVal)
  }
},{deep: true})





const loadBlock = async (element) => {
  let block = new Block(element);
  blockLoaded.value = block;
};


const loadBlockConfig = async (element) => {
  let block = new Block(element);
  blockLoaded.value = block;
  let res = await block.loadBlockConfig();
  config.value = res.config;
  prettyBlocksContext.$patch({
      saveContext:ref('config')
  })
};

const saveConfig = async (success = true) => {
  let block = currentBlock.value;
  if (!block) {
    return alert("No block loaded");
  }

  let res = await prettyBlocksContext.saveConfig(config.value);

  if (res.message) {
    if (success) {
      prettyBlocksContext.displayMessage(res.message);
    }
    // state.value = false
  }
   if (block.need_reload) {
      prettyBlocksContext.reloadIframe()
    } else {
       prettyBlocksContext.sendPrettyBlocksEvents('reloadBlock', {id_prettyblocks: block.id_prettyblocks})
    }
    prettyBlocksContext.initStates()
};

const getSubState = async (element) => {
  state.value = []
  prettyBlocksContext.$patch({
      saveContext: ref('subState')
  })
  let key =  prettyBlocksContext.currentBlock.subSelected.split('-')[1]
  state.value = {...prettyBlocksContext.currentBlock.states[key]}
};

const hidePanelSettings = () => {
  showPanel.value = false;
  // emitter.emit("hideSettings");
};

const showPanelSettings = () => {
  state.value = false;
  config.value = false;
  showPanel.value = true;
};

prettyBlocksContext.on('saveConfig', () => {
  saveConfig()
  prettyBlocksContext.initStates()
})
prettyBlocksContext.on('saveSubState', () => {
  save()
  prettyBlocksContext.initStates()
})

/*
 * Save the SubState in BD
 *
 */
const save = async (success = true) => {
  let block = currentBlock.value;
  let data = await prettyBlocksContext.updateSubSelectItem(state);
  if (data.success) {
    if (data.message && success) {
      prettyBlocksContext.displayMessage(data.message);
    }
    if (block.need_reload) {
      prettyBlocksContext.reloadIframe()
    } else {
       prettyBlocksContext.sendPrettyBlocksEvents('reloadBlock', {id_prettyblocks: block.id_prettyblocks})
    }
  }
  prettyBlocksContext.initStates()
  config.value = false
};

</script>

<template>
  <div id="rightPanel" class="relative border-l border-gray-200">
    <Loader :visible="showLoader">{{ trans("loading") }}...</Loader>
    <Modal />

    <!-- Config panel -->
    <div
      v-if="saveContext == 'config'"
      id="configPanel"
      class="absolute top-0 left-0 overflow-y-auto w-full h-full flex flex-col p-2 bg-slate-100"
      @keyup.enter="saveConfig()"
    >
    
     
     <!-- config {{ config }} -->
     
      <template v-for="f in config" :key="f">
        <FieldRepeater @updateUpload="saveConfig()" :field="f" />
      </template>

      <hr class="my-2" />
      
      <Title :title="trans('default_settings')" />
      <hr class="my-2" />
      <!-- container -->

      <div class="my-2">
        <Checkbox
          v-model="config.default.container"
          :title="trans('use_container')"
          name="container"
        />
      </div>
      <div class="my-2">
        <Checkbox
          v-model="config.default.force_full_width"
          :title="trans('force_full_width')"
          name="force_full_width"
        />
      </div>
      <!-- cache -->
      <!-- <div class="my-2">
        <Checkbox  v-model="config.default.is_cached" :title="trans('is_cached')" name="is_cached" />
      </div> -->

      <Title :title="trans('bg_color')" /> 
      <div class="my-4 flex">
        <ColorInput
          class="flex-auto rounded-full"
          v-model="config.default.bg_color"
          format="hex string"
        />
        <Input
          class="flex-auto"
          :placeholder="trans('ex_color')"
          v-model="config.default.bg_color"
          name="bg_color"
        />
      </div>
      
      <SpacingSection v-model='config.default' section_key='paddings' title='paddings' />
      <SpacingSection v-model='config.default' section_key='margins' title='margins' />
      
      <SimpleSelect
        v-if="Object.keys(config.templates).length > 1"
        v-model="config.templateSelected"
        :availableTpl="config.templates"
        :currentTpl="config.templateSelected"
        :label="trans('choose_template')"
      />
    </div>

    <!-- State panel  -->
   
    <div
      v-if="saveContext == 'subState'"
      id="statePanel"
      class="absolute top-0 left-0 overflow-y-auto w-full h-full flex flex-col p-2 bg-slate-100"
      @keyup.enter="save()"
    >

    <!-- STATE {{ state }} -->

      <template v-for="f in state" :key="f">
        <FieldRepeater @updateUpload="save()" :field="f" />
      </template>
    </div>

    <!-- Theme settings panel  -->
    <div
      v-if="showPanel"
      id="themeSettingsPanel"
      class="absolute top-0 left-0 overflow-y-auto w-full h-full bg-slate-100"
    >

      <div class="bg-indigo text-white">
        <h2 class="ml-4 p-3 text-center">
          <Icon name="CogIcon" class="h-5 w-5 inline" />
          {{ trans("theme_settings") }}
        </h2>
      </div>
      <PanelThemeSettings />
    </div>
  </div>
</template>

<style scoped>
.v-enter-active,
.v-leave-active {
  transition: opacity 2s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}

.tox-notifications-container {
  display: none !important;
}

#rightPanel {
  transition: all 0.5s ease;
}
</style>