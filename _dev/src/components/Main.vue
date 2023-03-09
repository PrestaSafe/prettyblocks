<script>
import RightColumn from "./RightColumn.vue"
import LeftColumn from "./LeftColumn.vue"
import emitter from 'tiny-emitter/instance';

export default {
  components: {
    RightColumn,
    LeftColumn,
  },
  data() {
    return {
      showRight: false,
      iframe: false,
    };
  },
  mounted: function () {
    this.iframe = document.getElementById('website-iframe');

    let that = this
    emitter.on('show-right', function (arg1) {
      that.showRight = arg1;
    });
  },
  methods: {
    successSaved: function () {
      this.$toast.success('Sauvegarde OK', {
        position: "top"
      })
    },
    reloadIframe: function () {
      this.iframe.src = this.iframe.src
    }
  }
};
</script>

<template>
  <!-- This example requires Tailwind CSS v2.0+ -->
  <div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-1 py-1 sm:px-6">
      <div class="float-left">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
          Modifier votre site
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
          site en cours: www.example.com
        </p>
      </div>
      <div class="float-right">
        <button type="button" @click="successSaved()"
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          <!-- Heroicon name: solid/check -->
          <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
            aria-hidden="true">
            <path fill-rule="evenodd"
              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
              clip-rule="evenodd" />
          </svg>
          Publish
        </button>
      </div>
      <div class="float-right px-4">
        <button type="button" @click="reloadIframe()"
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
          <!-- Heroicon name: solid/check -->
          <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Reload
        </button>
      </div>
    </div>
    <div class="flex mx-auto pt-4">
      <div class="w-96 text-left pl-4 ">
        <p class="text-indigo-900 mb-4">Modèle</p>
        <h1 class="text-4xl mb-8">Home page</h1>
        <RightColumn :showRight="showRight" />
        <LeftColumn :showRight="showRight" />
      </div>
      <div class="w-full border h-full ">
        <iframe id="website-iframe" sandbox="allow-same-origin  allow-scripts allow-forms" class="w-full h-screen px-8"
          src="https://cartzillaps.alwaysdata.net/" frameborder="0"></iframe>
      </div>
    </div>
  </div>
</template>
