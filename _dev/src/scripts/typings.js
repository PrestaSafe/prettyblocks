
import {reactive} from 'vue';


export const safePaddingsAndMargins = reactive({
    desktop: {
      top: null,
      left: null,
      right: null,
      bottom: null,
      auto: 0,
      use_custom_data: false,
    },
    tablet: {
      top: null,
      left: null,
      right: null,
      bottom: null,
      auto: 0,
      use_custom_data: false,
    },
    mobile: {
      top: null,
      left: null,
      right: null,
      bottom: null,
      auto: 0,
      use_custom_data: false,
    },
  });