<script setup>
import draggable from "vuedraggable";
import { HttpClient } from "../services/HttpClient";
import emitter from "tiny-emitter/instance";
import { contextShop, useStore, usePrettyBlocksContext } from "../store/pinia";
import { createToaster } from "@meforma/vue-toaster";
import { trans } from "../scripts/trans";

const prettyBlocksContext = usePrettyBlocksContext()

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

  await prettyBlocksContext.$patch({
    currentBlock: {
      id_prettyblocks: id_prettyblocks,
    }
  });
  // emitter.emit('displayState', id_prettyblocks)
};

/**
 * Save positions in DB after
 */
const onEnd = async (event, items) => {
  let context = prettyBlocksContext.psContext
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
    // emitter.emit("initStates");
    await prettyBlocksContext.initStates()
    if (items[0].need_reload) {
      prettyBlocksContext.reloadIframe()
    }else{
      prettyBlocksContext.sendPrettyBlocksEvents('reloadBlock', {id_prettyblocks: items[0].id_prettyblocks})
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
    prettyBlocksContext.reloadIframe()
  }

  prettyBlocksContext.displayMessage(trans('position_updated'));
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
