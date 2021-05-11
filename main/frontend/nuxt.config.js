import colors from "vuetify/es5/util/colors";

export default {

  // Server configuration,
  server: {
    host: '0' // default: localhost
  },

  // Environment variables.
  env: {
    backendURL: process.env.BACKEND_URL,
    widgetURL: process.env.WIDGET_URL,
    marketingHomeURL: process.env.MARKETING_HOME_URL,
    version: process.env.VERSION
  },

  // Global page headers: https://go.nuxtjs.dev/config-head
  head: {
    titleTemplate: "%s - Feedbeggar",
    title: "Feedbeggar",

    htmlAttrs: {
      lang: "en"
    },
    meta: [
      { charset: "utf-8" },
      { name: "viewport", content: "width=device-width, initial-scale=1" },
      { hid: "description", name: "description", content: "" }
    ],
    link: [{ rel: "icon", type: "image/x-icon", href: "/favicon.ico" }]
  },

  // Global CSS: https://go.nuxtjs.dev/config-css
  css: [],

  ssr: true,

  // Plugins to run before rendering page: https://go.nuxtjs.dev/config-plugins
  plugins: [],

  // Auto import components: https://go.nuxtjs.dev/config-components
  components: true,

  // Modules for dev and build (recommended): https://go.nuxtjs.dev/config-modules
  buildModules: [
    // https://go.nuxtjs.dev/typescript
    "@nuxt/typescript-build",
    // https://go.nuxtjs.dev/vuetify
    "@nuxtjs/vuetify"
  ],

  // Modules: https://go.nuxtjs.dev/config-modules
  modules: ["@nuxtjs/axios", "@nuxtjs/auth-next", 'cookie-universal-nuxt',],

  // Axios.
  axios: {
    credentials: true,
    baseURL: process.env.BACKEND_URL,
  },

  // Protect routes from being accessed by unauthenticated users.
  router: {
    middleware: ["auth"]
  },

  // Authentication strategies.
  auth: {
    strategies: {
      laravelSanctum: {
        provider: "laravel/sanctum",
        url: process.env.BACKEND_URL,
        endpoints: {
          login: {
            url: "/login"
          }
        }
      }
    },
  },

  // Vuetify module configuration: https://go.nuxtjs.dev/config-vuetify
  vuetify: {
    customVariables: ["~/assets/variables.scss"],
    theme: {
      dark: true,
      themes: {
        dark: {
          primary: "#5CE52B",
          accent: "#92EF72",
          secondary: "#41B519",
          info: "#76AB64",
          warning: colors.amber.base,
          error: colors.deepOrange.accent4,
          success: colors.green.accent3
        }
      }
    }
  },

  // Build Configuration: https://go.nuxtjs.dev/config-build
  build: {}
};
