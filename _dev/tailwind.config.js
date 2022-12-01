/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        indigo: {
          DEFAULT: "#5530be",
          "50": "#8762f0",
          "100": "#7d58e6",
          "200": "#734edc",
          "300": "#6944d2",
          "400": "#5f3ac8",
          "500": "#5530be",
          "600": "#4b26b4",
          "700": "#411caa",
          "800": "#3712a0",
          "900": "#2d0896"
        }
      }
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
