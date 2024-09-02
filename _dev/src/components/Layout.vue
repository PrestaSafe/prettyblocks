<script setup>
import Header from './Header.vue'
import LeftPanel from './LeftPanel.vue'
import Frame from './Frame.vue'
import RightPanel from './RightPanel.vue'
import { defineComponent, ref, computed } from '@vue/runtime-core'
import { usePrettyBlocksContext } from '../store/pinia'

const prettyBlocksContext = usePrettyBlocksContext()
const rightPanel = computed(() => prettyBlocksContext.iframe.rightPanel)
const leftPanel = computed(() => prettyBlocksContext.iframe.leftPanel)
defineComponent({
  Header,
  LeftPanel,
  Frame,
  RightPanel
})

// left panel
let leftWidth = computed(() => leftPanel.value === 'extends' ? 'w-5/12' : 'w-80')
let hidden_left = computed(() => leftPanel.value === 'hide' ? '' : 'sm:block')



// right panel
let rightWidth = computed(() => rightPanel.value === 'extends' ? 'w-5/12' : 'w-80')
let hidden_right = computed(() => rightPanel.value === 'hide' ? '' : 'sm:block')




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
