<script setup>
import draggable from "vuedraggable";
import { HttpClient } from "../services/HttpClient";
import emitter from "tiny-emitter/instance";
import { contextShop, useStore } from "../store/currentBlock";
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({
  position: "top",
});

defineProps({
  items: Array,
  group: String,
  action: {
    type: String,
  },
  type: String,
});

/**
 * OnStart event
 * Change Current Block on store
 */
const onStart = async (evt, group) => {
  let id_prettyblocks = group[evt.oldDraggableIndex].id_prettyblocks;
  let currentBlock = useStore();
  await currentBlock.$patch({
    id_prettyblocks: id_prettyblocks,
  });
  // emitter.emit('displayState', id_prettyblocks)
};

/**
 * Save positions in DB after
 */
const onEnd = async (event, items) => {
  let context = contextShop();
  if (items[0].is_child) {
    const params = {
      items: items,
      action: "updateStatePosition",
      ajax: true,
      ctx_id_lang: context.id_lang,
      ctx_id_shop: context.id_shop,
      ajax_token: security_app.ajax_token,
    };
    await HttpClient.post(ajax_urls.state, params);
    emitter.emit("initStates");
    emitter.emit("displayBlockConfig", items[0]);
    emitter.emit("stateUpdated", items[0].id_prettyblocks);
    if (items[0].need_reload) {
      emitter.emit("reloadIframe", items[0].id_prettyblocks);
    }
  }

  if (items[0].is_parent) {
    const params = {
      items: items,
      action: "updateStateParentPosition",
      ajax: true,
      ajax_token: security_app.ajax_token,
    };
    await HttpClient.post(ajax_urls.state, params);
    emitter.emit("initStates");
    console.log('items 0', items[0].id_prettyblocks)
    emitter.emit("reloadIframe", items[0].id_prettyblocks);
  }

  toaster.show("Position modifiée avec succèss", {
    position: "top",
  });
};
</script>

<template>
  <draggable
    :list="items"
    :group="group"
    item-key="id"
    @end="onEnd($event, items)"
    @start="onStart($event, items)"
    class="list-group"
    tag="ul"
    animation="200"
    handle=".handle"
    ghost-class="ghost"
  >
    <template #item="{ element }">
      <div>
        <slot :element="element"></slot>
      </div>
    </template>
  </draggable>
</template>
