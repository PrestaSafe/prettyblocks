<script setup>
import Header from './Header.vue'
import LeftPanel from './LeftPanel.vue'
import Frame from './Frame.vue'
import RightPanel from './RightPanel.vue'
import { defineComponent, ref } from '@vue/runtime-core'
import emitter from 'tiny-emitter/instance'

defineComponent({
  Header,
  LeftPanel,
  Frame,
  RightPanel
})

// left panel
let leftWidth = ref('w-80')
let hidden_left = ref('sm:block')

emitter.on('hideLeftPanelSize', (value) => {
  if (value) {
    hidden_left.value = ''
  } else {
    hidden_left.value = 'sm:block'
  }
})

emitter.on('changeLeftPanelSize', (value) => {
  if (value) {
    leftWidth.value = 'w-5/12'
  } else {
    leftWidth.value = 'w-80'
  }
})

// right panel
let rightWidth = ref('w-80')
let hidden_right = ref('sm:block')

emitter.on('hideRightPanelSize', (value) => {
  if (value) {
    hidden_right.value = ''
  } else {
    hidden_right.value = 'sm:block'
  }
})

emitter.on('changeRightPanelSize', (value) => {
  if (value) {
    rightWidth.value = 'w-5/12'
  } else {
    rightWidth.value = 'w-80'
  }
})
</script>

<template>
  <main class="flex flex-col h-screen">
    <Header />
    <div class="overflow-hidden flex flex-grow">
      <LeftPanel :class="[leftWidth, hidden_left]" class="hidden" />
      <Frame class="flex-grow" />
      <RightPanel :class="[rightWidth, hidden_right]" class="hidden" />
    </div>
  </main>
</template>
