<script>
import emitter from 'tiny-emitter/instance';

export default {
  mounted() {
    emitter.emit('show-right', false);
  },
  setup(props, context) {
    return {}
  }
}
</script>

<template>
  <div>
    <ul class="flex">
      <li class="mr-6">
        <a class="text-blue-500 hover:text-blue-800" href="#">Active</a>
      </li>
      <li class="mr-6">
        <a class="text-blue-500 hover:text-blue-800" href="#">Link</a>
      </li>
      <li class="mr-6">
        <a class="text-blue-500 hover:text-blue-800" href="#">Link</a>
      </li>
      <li class="mr-6">
        <a class="text-gray-400 cursor-not-allowed" href="#">Disabled</a>
      </li>
    </ul>
  </div>
</template>
