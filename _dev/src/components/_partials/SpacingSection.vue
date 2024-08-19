<template>
      
        <!--  Spacing Sections   -->
        <hr class="my-2" />
        <div class="flex items-center justify-between">
            <div class="flex">
              <Title :title="trans(props.title)" /> 
            </div>
            <div class="flex">
                <Icon @click="changeDevice('desktop')" name="ComputerDesktopIcon" :class="[
              'h-9 inline p-2 rounded',
              { 'bg-black bg-opacity-10': current_device == 'desktop' },
            ]" />
                <Icon @click="changeDevice('tablet')" name="DeviceTabletIcon" :class="[
              'h-9 inline p-2 rounded',
              { 'bg-black bg-opacity-10': current_device == 'tablet' },
            ]" />
                <Icon @click="changeDevice('mobile')" name="DevicePhoneMobileIcon" :class="[
              'h-9 inline p-2 rounded',
              { 'bg-black bg-opacity-10': current_device == 'mobile' },
            ]" />
                <Icon name="QuestionMarkCircleIcon" class="h-9 inline p-2 rounded" @click="popover = !popover" />
                <Icon name="TrashIcon" class="h-9 inline p-2 text-red-500" @click="resetValues()"/>
            </div>
        </div>

        <Popover class="relative">
            <PopoverPanel static v-if="popover" class="z-10 bg-white border-blue-500 border rounded p-4">
                {{ trans(props.section_key+"_section_help") }}
            </PopoverPanel>
        </Popover>
        <hr class="my-2" />
     
    <!-- Auto slider  -->
        <div class="w-full" v-if="!(props.modelValue[props.section_key][current_device].use_custom_data ?? false)" >
              <Title :title="trans('auto_size_section')" />

            <input class=" w-full"  type="range" min="0" max="12" step="1"  @change="updateAutoPaddingDevices"  v-model="props.modelValue[props.section_key][current_device].auto">
        </div>
        
        
        <div class="my-4 flex">
            <div class="text-center">
                <!-- top Icons -->
                <Icon name="ArrowUpIcon" class="h-5 w-5 inline" />

                <Input :type="
              props.modelValue[props.section_key][current_device].use_custom_data ?? false
                ? 'text'
                : 'number'
            " :placeholder="trans('top')" v-model="props.modelValue[props.section_key][current_device].top" />
            </div>
            <!-- Margin Right -->
            <div class="text-center">
                <Icon name="ArrowRightIcon" class="h-5 w-5 inline" />
                <Input :type="
              props.modelValue[props.section_key][current_device].use_custom_data ?? false
                ? 'text'
                : 'number'
            " :placeholder="trans('right')" v-model="props.modelValue[props.section_key][current_device].right" name="padding_right" />
            </div>

            <div class="text-center">
                <Icon name="ArrowDownIcon" class="h-5 w-5 inline" />
                <Input :type="
              props.modelValue[props.section_key][current_device].use_custom_data ?? false
                ? 'text'
                : 'number'
            " class="pr-1" :placeholder="trans('bottom')" v-model="props.modelValue[props.section_key][current_device].bottom" name="padding_bottom" />
            </div>
            <div class="text-center">
                <Icon name="ArrowLeftIcon" class="h-5 w-5 inline" />
                <Input :type="
              props.modelValue[props.section_key][current_device].use_custom_data ?? false
                ? 'text'
                : 'number' " :placeholder="trans('left')" v-model="props.modelValue[props.section_key][current_device].left" name="padding_left" />
            </div>
        </div>

           <div class="flex justify-between w-full">
            <p>
                <Title :title="trans('use_custom_entry')" />
            </p>
            <Checkbox v-model="props.modelValue[props.section_key][current_device].use_custom_data" name="container" value="full" />
            <hr class="my-2" />
        </div>
</template>

<script setup>
import Input from '../form/Input.vue'
import Icon from '../Icon.vue'
import Checkbox from '../form/Checkbox.vue'
import Title from '../Title.vue'
import ColorInput from "vue-color-input";
import emitter from "tiny-emitter/instance";
import { ref, defineProps, watch, defineEmits } from 'vue'
import { safePaddingsAndMargins } from "../../scripts/typings";
import { usePrettyBlocksContext } from "../../store/pinia";
import { storeToRefs } from "pinia";

const prettyBlocksContext = usePrettyBlocksContext()

const current_device = ref(prettyBlocksContext.iframe.device);
watch(() => prettyBlocksContext.iframe.device, (newValue) => {
  current_device.value = newValue;
});
import {
    trans
} from "../../scripts/trans";
import {
    Popover,
    PopoverButton,
    PopoverPanel
} from "@headlessui/vue";

const props = defineProps({
    modelValue: Object,
    title: {
      type: String,
      default: 'paddings'
    },
    section_key: {
      type: String,
      default: 'paddings'
    }
})


const emit = defineEmits(['update:modelValue']);
const formatModelValue = () => {
  if (!props.modelValue[props.section_key]) {
    props.modelValue[props.section_key] = {};
  }

  for (const device in safePaddingsAndMargins) {
    if (!props.modelValue[props.section_key][device]) {
      props.modelValue[props.section_key][device] = {};
    }
    for (const key in safePaddingsAndMargins[device]) {
      if (props.modelValue[props.section_key][device][key] === undefined) {
        props.modelValue[props.section_key][device][key] = safePaddingsAndMargins[device][key];
      }
    }
  }
};

watch(() => props.modelValue, (newValue) => {
  formatModelValue();
  emit('update:modelValue', newValue);
}, { deep: true });

formatModelValue();

const popover = ref(false);


const changeDevice = (device) => {
  current_device.value = device;
}

const updateAutoPaddingDevices = (e) => {
  props.modelValue[props.section_key][current_device.value].top = e.target.value;
  props.modelValue[props.section_key][current_device.value].left = e.target.value;
  props.modelValue[props.section_key][current_device.value].right = e.target.value;
  props.modelValue[props.section_key][current_device.value].bottom = e.target.value;
};





const resetValues = () => {
  props.modelValue[props.section_key][current_device.value].auto = 0;
  props.modelValue[props.section_key][current_device.value].top = '';
  props.modelValue[props.section_key][current_device.value].left = '';
  props.modelValue[props.section_key][current_device.value].right = '';
  props.modelValue[props.section_key][current_device.value].bottom = '';
}



</script>
