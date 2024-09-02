<template>
  <div class="fixed top-0 left-0 w-full h-full flex justify-center items-center" v-if="showModal">
    <div class="fixed top-0 left-0 w-full h-full bg-black opacity-40 z-50"></div>
    <div class="bg-white p-8 rounded-md relative z-50">
      <span class="absolute top-4 right-4 text-gray-600 cursor-pointer" @click="closeModal">&times;</span>
      <h2 class="text-2xl font-bold mb-4">Sélectionnez les langues</h2>
      <div v-for="language in languages" :key="language.id_lang">
        <Checkbox class="my-2" v-model="selectedLanguages[language.id_lang]" :title="language.name" :name="language.name" />
      </div>
      <button class="bg-indigo text-white px-4 py-2 rounded-md mt-4" @click="submitSelection">Valider</button>
    </div>
  </div>
</template>

<script>
import Checkbox from '../components/form/Checkbox.vue';
import { usePrettyBlocksContext } from '../store/pinia'
import { HttpClient } from '../services/HttpClient';
import { createToaster } from "@meforma/vue-toaster";
import { trans } from '../scripts/trans'
const toaster = createToaster({
  position: "top",
});

export default {
  props: ['languages', 'id_prettyblocks'],
  components: { Checkbox },
  data() {
    const initialSelectedLanguages = {};
    this.languages.forEach((language) => {
      initialSelectedLanguages[language.id_lang] = false;
    });

    return {
      showModal: true,
      selectedLanguages: {...initialSelectedLanguages},
    };
  },
  methods: {
    closeModal() {
      this.showModal = false;
      this.$emit('closeModal');
    },
    submitSelection() {
      const selectedLanguages = Object.keys(this.selectedLanguages)
          .filter((key) => this.selectedLanguages[key])
          .map(Number);

      const prettyBlocksContext = usePrettyBlocksContext();
      const id_shop = prettyBlocksContext.psContext.id_shop;

      const params = {
        action: 'duplicateState',
        ajax: true,
        ajax_token: security_app.ajax_token,
        ctx_id_shop: id_shop,
        id_prettyblocks: this.id_prettyblocks,
        selectedLanguages: selectedLanguages,
      };

      HttpClient.get(ajax_urls.state, params)
          .then((response) => {
            prettyBlocksContext.displayMessage(response.message);
            prettyBlocksContext.reloadIframe()
            prettyBlocksContext.initStates();
          })
          .catch((error) => {
            console.error(trans('error_console'), error);
            prettyBlocksContext.displayError(trans('duplicate_state_error'));
          });

      this.closeModal();
    },
  },
};
</script>
