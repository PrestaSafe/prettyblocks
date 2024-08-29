<script setup>
// PrettyBLock Core
import FormControl from './form/FormControl.vue'
import Accordion from './Accordion.vue'
import ColorInput from 'vue-color-input'
import Input from './form/Input.vue'
import Select from './form/Select.vue'
import SimpleSelect from './form/SimpleSelect.vue'
import MultiSelect from './form/MultiSelect.vue'
import HeaderDropdown from './HeaderDropdown.vue'
import Textarea from './form/Textarea.vue'
import Title from './Title.vue'
import Checkbox from './form/Checkbox.vue'
import Radio from './form/Radio.vue'
import Button from './Button.vue'
import FileUpload from './form/FileUpload.vue'
import Choices from './form/Choices.vue'
import Icon from './Icon.vue'
import Slider from './Slider.vue'
// tinyMCE 
import Editor from '@tinymce/tinymce-vue'

// vue quill or tinymce
import { QuillEditor } from '@vueup/vue-quill'
import htmlEditButton from "quill-html-edit-button";

import '@vueup/vue-quill/dist/vue-quill.snow.css';
import '@vueup/vue-quill/dist/vue-quill.bubble.css';

//  date picker 
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css' 

// UUID
import { v4 as uuidv4 } from 'uuid'

// Vue
import { defineComponent, defineProps, ref, defineEmits, onMounted } from 'vue'

defineComponent({
  FormControl,
  Accordion,
  ColorInput,
  Input,
  Select,
  SimpleSelect,
  HeaderDropdown,
  Textarea,
  Checkbox,
  Radio,
  Button,
  FileUpload,
  Icon,
  Editor,
  Choices,
  MultiSelect
})

const VueQuillHTMLEditButton = {
        name: 'htmlEditButton',
        module: htmlEditButton,
}
  
const formatDateFromString = (date) => {
  return new Date(date)
}
const props = defineProps({
  field: {
    type: Object,
    required: true
  }
})
let f = ref(props.field)
const emit = defineEmits(['updateUpload'])

const updateUpload = () => {
  emit('updateUpload')
}
const removeTinyNotifications = () => {
  setTimeout(() => {
    document.querySelectorAll('.tox-notifications-container').forEach((el) => {
      el.querySelector('button.tox-notification__dismiss').click()
    })
  }, 300)
}
let tinymce_api_key = ref(window.security_app.tinymce_api_key)
const toolbarOptions = [
  ['bold','italic', 'underline', 'strike',{ 'color': [] }, { 'background': [] }, 'link', 'video'],        // toggled buttons
  [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
  [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
  [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
  [{ 'align': [] }],

  ['clean']                                         // remove formatting button
];

</script>

<template>
  <div>
    <HeaderDropdown v-if="f.type == 'selector'" v-model="f.value" :title="f.label" :label="f.label"
      :collection="f.collection" :selector="f.selector" class="hidden md:block mb-2" />
    <Input class="my-4" v-if="f.type == 'text'" v-model="f.value" :title="f.label" placeholder="Entrez du texte" />
    <div v-if="f.type == 'color'">
      {{ f.label }}
      <div class="flex mb-4 pt-4">
        <ColorInput class="flex-auto rounded-full " v-model="f.value" position="bottom right" format="hex string" />
        <Input class="flex-auto" placeholder="Add a color ex: #123456" v-model="f.value" name="bg_color" />
      </div>
    </div>
    <FileUpload class="my-4" :default="f.default" :path="(f.path) ? f.path : '$/modules/prettyblocks/views/images'"
      @saveParent="updateUpload" v-if="f.type == 'fileupload'" v-model="f.value" :id="uuidv4()" :title="f.label" />
    <Textarea class="my-4" v-if="f.type == 'textarea'" v-model="f.value" :title="f.label" :name="uuidv4()"
      placeholder="Entrez du texte" />
    <Checkbox class="my-4" v-if="f.type == 'checkbox' || f.type == 'radio'" :name="uuidv4()" v-model="f.value"
      :title="f.label" />
    
    <div class="clearfix" v-if="f.type == 'editor' && f.provider == 'tinymce'">
      <Title :title="f.label" />
      <!--  TinyMCE -->
      <Editor
        v-model="f.value"
        :api-key="tinymce_api_key"
        @init="removeTinyNotifications()"
        :init="{
          height: 500,
          menubar: 'edit view format',
          plugins: 'code fullscreen link lists',
          toolbar1: 'blocks code',
          toolbar2: 'bold italic underline bullist numlist link',
        }"
      />
    </div>
    <!--  VueQuill -->
    <div v-else-if="f.type == 'editor' && (f.provider !== 'tinymce' || f.provider == 'vuequill')" class="clearfix">
      <Title :title="f.label" />
      <div class="bg-white">
        <QuillEditor :modules="VueQuillHTMLEditButton" v-model:content="f.value"  contentType="html" theme="snow" :toolbar="toolbarOptions" />
      </div>
    </div>
    <div class="my-4" v-if="f.type == 'select'">
      <Choices :choices="f.choices" v-model="f.value" :label="f.label" />
    </div>
    
    <div class="my-4" v-if="f.type == 'multiselect'">
      <MultiSelect v-model="f.value" :label="f.label" :options="f.choices" searchable="true" mode="tags"></MultiSelect>
    </div>
    <div class="my-4" v-if="f.type == 'radio_group'">
      <FormControl class="my-4" :title="f.label">
        <Radio v-for='(group, value, key) in f.choices' :key="group" v-model="f.value" :title="group"
        :name="'radio-group-' + key" :value="value" />
      </FormControl>
    </div>

    <div class="my-4" v-if="f.type == 'datepicker'">
      <Title :title="f.label" />
      <VueDatePicker :model-value="formatDateFromString(f.value)" :enable-time-picker="false" month-name-format="short" @update:modelValue="f.value = $event" />
    </div>  

    <div class="my-4" v-if="f.type == 'slider'">
        <Title :title="f.label" />
        <Slider v-model="f.value" v-bind="f.options" />
    </div> 

    <p class="text-gray-500 italic" v-if="f.legend">{{ f.legend }}</p>
  </div>
</template>

